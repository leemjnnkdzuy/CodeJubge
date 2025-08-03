<?php

require_once CORE_PATH . '/Controller.php';
require_once MODEL_PATH . '/DiscussionModel.php';

class DiscussionController extends Controller
{
    private $discussionModel;
    
    public function __construct()
    {
        global $DISCUSS_CATEGORIES;
        $this->discussionModel = new DiscussionModel();
    }

    public function api()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        try {
            $page = (int)($_GET['page'] ?? 1);
            $limit = min((int)($_GET['limit'] ?? 10), 50);
            $filter = $_GET['filter'] ?? 'all';
            $search = $_GET['search'] ?? '';
            $sortBy = $_GET['sort_by'] ?? 'created_at';
            $sortOrder = $_GET['sort_order'] ?? 'DESC';
            
            $discussions = $this->discussionModel->getDiscussions($page, $limit, $filter, $search, $sortBy, $sortOrder);
            $totalDiscussions = $this->discussionModel->countDiscussions($filter, $search);
            
            $userId = $_SESSION['user_id'] ?? null;
            
            $formattedDiscussions = array_map(function($discussion) use ($userId) {
                $formattedDiscussion = [
                    'id' => (int)$discussion['id'],
                    'title' => $discussion['title'],
                    'slug' => $discussion['slug'],
                    'content' => substr($discussion['content'], 0, 200) . (strlen($discussion['content']) > 200 ? '...' : ''),
                    'category' => $discussion['category'],
                    'tags' => $discussion['tags'],
                    'is_pinned' => (bool)$discussion['is_pinned'],
                    'is_solved' => (bool)$discussion['is_solved'],
                    'likes_count' => (int)$discussion['likes_count'],
                    'replies_count' => (int)$discussion['replies_count'],
                    'created_at' => $discussion['created_at'],
                    'author_id' => (int)$discussion['author_id'],
                    'user_liked' => isset($discussion['user_liked']) ? (bool)$discussion['user_liked'] : false,
                    'is_bookmarked' => isset($discussion['is_bookmarked']) ? (bool)$discussion['is_bookmarked'] : false,
                    'author' => [
                        'username' => $discussion['username'],
                        'first_name' => $discussion['first_name'],
                        'last_name' => $discussion['last_name'],
                        'avatar' => $discussion['avatar'] ?: '/assets/default-avatar.png',
                        'badges' => $discussion['badges']
                    ],
                    'time_ago' => $this->getTimeAgo($discussion['created_at'])
                ];
                
                if ($userId) {
                    $formattedDiscussion['user_liked'] = $this->discussionModel->hasUserLiked($userId, $discussion['id']);
                }
                
                return $formattedDiscussion;
            }, $discussions);
            
            $response = [
                'success' => true,
                'discussions' => $formattedDiscussions,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $limit,
                    'total' => $totalDiscussions,
                    'total_pages' => ceil($totalDiscussions / $limit),
                    'has_more' => count($discussions) >= $limit
                ],
                'has_more' => count($discussions) >= $limit
            ];
            
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Đã xảy ra lỗi khi tải dữ liệu'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function like()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Method not allowed'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Unauthorized - Please login'
            ], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid JSON input'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $discussionId = (int)($input['discussion_id'] ?? 0);
            $userId = (int)$_SESSION['user_id'];
            
            if ($discussionId <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid discussion ID'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $discussion = $this->discussionModel->getDiscussionById($discussionId);
            if (!$discussion) {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Discussion not found'
                ], JSON_UNESCAPED_UNICODE);
                return;
            }
            
            $action = $this->discussionModel->toggleLike($userId, $discussionId);
            
            if ($action === false) {
                throw new Exception('Failed to toggle like status');
            }
            
            $newLikesCount = $this->discussionModel->getLikesCount($discussionId);
            
            echo json_encode([
                'success' => true,
                'action' => $action,
                'likes_count' => $newLikesCount,
                'discussion_id' => $discussionId
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            error_log("Like action error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Đã xảy ra lỗi khi xử lý yêu cầu'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function bookmark()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $discussionId = (int)($input['discussion_id'] ?? 0);
            
            if ($discussionId <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid discussion ID']);
                return;
            }
            
            $action = $this->discussionModel->toggleBookmark($_SESSION['user_id'], $discussionId);
            
            if ($action === false) {
                throw new Exception('Failed to toggle bookmark');
            }
            
            echo json_encode([
                'success' => true,
                'action' => $action
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Đã xảy ra lỗi'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function show($id)
    {
        $discussion = $this->discussionModel->getDiscussionById($id);
        
        if (!$discussion) {
            http_response_code(404);
            include VIEW_PATH . '/404.php';
            return;
        }
        
        $replies = $this->discussionModel->getDiscussionReplies($id);
        
        $title = htmlspecialchars($discussion['title']) . ' - CodeJudge';
        $description = htmlspecialchars(substr($discussion['content'], 0, 160));
        
        include VIEW_PATH . '/discussion_detail.php';
    }

    public function create()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $title = 'Tạo Thảo Luận Mới - CodeJudge';
        $description = 'Tạo một thảo luận mới về lập trình';
        
        include VIEW_PATH . '/create_discussion.php';
    }

    public function apiCreate()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để tạo bài viết'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dữ liệu không hợp lệ');
            }
            
            $title = trim($input['title'] ?? '');
            $content = trim($input['content'] ?? '');
            $category = $input['category'] ?? 'general';
            $tags = $input['tags'] ?? [];
            
            if (empty($title) || strlen($title) < 10) {
                throw new Exception('Tiêu đề phải có ít nhất 10 ký tự');
            }
            
            if (empty($content) || strlen($content) < 20) {
                throw new Exception('Nội dung phải có ít nhất 20 ký tự');
            }
            
            // Validate category exists in config
            global $DISCUSS_CATEGORIES;
            $validCategories = array_map('strtolower', array_keys($DISCUSS_CATEGORIES));
            $validCategories = array_merge($validCategories, ['general', 'algorithm', 'data-structure', 'math', 'beginner', 'contest', 'help']);
            
            if (!in_array($category, $validCategories)) {
                $category = 'general';
            }
            
            if (is_string($tags)) {
                $tags = array_map('trim', explode(',', $tags));
                $tags = array_filter($tags);
            }
            
            if (count($tags) > 5) {
                throw new Exception('Tối đa 5 tags');
            }
            
            $slug = $this->discussionModel->generateSlug($title);
            
            $discussionData = [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'author_id' => $_SESSION['user_id'],
                'category' => $category,
                'tags' => $tags
            ];
            
            $discussionId = $this->discussionModel->createDiscussion($discussionData);
            
            if (!$discussionId) {
                throw new Exception('Không thể tạo thảo luận');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Bài viết đã được tạo thành công!',
                'discussion_id' => $discussionId,
                'redirect_url' => "/discussions/{$discussionId}"
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Edit discussion page
     */
    public function edit($id)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $discussion = $this->discussionModel->getDiscussionById($id);
        
        if (!$discussion) {
            header('HTTP/1.0 404 Not Found');
            include VIEW_PATH . '/404.php';
            return;
        }
        
        // Check if user is the author
        if ($discussion['author_id'] != $_SESSION['user_id']) {
            header('HTTP/1.0 403 Forbidden');
            echo "Bạn không có quyền chỉnh sửa bài viết này";
            return;
        }
        
        // You can create an edit view later
        header("Location: /discussions/{$id}");
    }
    
    /**
     * Delete discussion
     */
    public function delete($id)
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $discussion = $this->discussionModel->getDiscussionById($id);
            
            if (!$discussion) {
                throw new Exception('Bài viết không tồn tại');
            }
            
            // Check if user is the author
            if ($discussion['author_id'] != $_SESSION['user_id']) {
                throw new Exception('Bạn không có quyền xóa bài viết này');
            }
            
            $deleted = $this->discussionModel->deleteDiscussion($id);
            
            if (!$deleted) {
                throw new Exception('Không thể xóa bài viết');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Bài viết đã được xóa thành công'
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function store()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            $title = trim($input['title'] ?? '');
            $content = trim($input['content'] ?? '');
            $category = $input['category'] ?? 'general';
            $tags = $input['tags'] ?? [];
            
            if (empty($title) || strlen($title) < 5) {
                throw new Exception('Tiêu đề phải có ít nhất 5 ký tự');
            }
            
            if (empty($content) || strlen($content) < 20) {
                throw new Exception('Nội dung phải có ít nhất 20 ký tự');
            }
            
            $allowedCategories = ['general', 'algorithm', 'data-structure', 'math', 'beginner', 'contest', 'help'];
            if (!in_array($category, $allowedCategories)) {
                $category = 'general';
            }
            
            $slug = $this->discussionModel->generateSlug($title);
            
            $discussionData = [
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'author_id' => $_SESSION['user_id'],
                'category' => $category,
                'tags' => $tags
            ];
            
            $discussionId = $this->discussionModel->createDiscussion($discussionData);
            
            if (!$discussionId) {
                throw new Exception('Không thể tạo thảo luận');
            }
            
            echo json_encode([
                'success' => true,
                'discussion_id' => $discussionId,
                'redirect_url' => "/discussions/{$discussionId}"
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function handleDiscussionById($id)
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if (strpos($_SERVER['REQUEST_URI'], '/edit') !== false) {
                    $this->getForEdit($id);
                } else {
                    $this->show($id);
                }
                break;
                
            case 'PUT':
                $this->update($id);
                break;
                
            case 'DELETE':
                $this->delete($id);
                break;
                
            default:
                header('Content-Type: application/json');
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                break;
        }
    }

    public function getForEdit($id)
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        try {
            $discussion = $this->discussionModel->getDiscussionById($id);
            
            if (!$discussion) {
                http_response_code(404);
                echo json_encode(['error' => 'Discussion not found']);
                return;
            }
            
            // Check if user can edit this discussion
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId || $discussion['author_id'] != $userId) {
                http_response_code(403);
                echo json_encode(['error' => 'Unauthorized']);
                return;
            }
            
            echo json_encode([
                'id' => (int)$discussion['id'],
                'title' => $discussion['title'],
                'content' => $discussion['content'],
                'category' => $discussion['category'],
                'tags' => $discussion['tags'] ?: []
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function update($id)
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'] ?? null;
            if (!$userId) {
                http_response_code(401);
                echo json_encode(['error' => 'Unauthorized']);
                return;
            }
            
            // Get current discussion to check ownership
            $currentDiscussion = $this->discussionModel->getDiscussionById($id);
            if (!$currentDiscussion || $currentDiscussion['author_id'] != $userId) {
                http_response_code(403);
                echo json_encode(['error' => 'Forbidden']);
                return;
            }
            
            // Get JSON data
            $input = json_decode(file_get_contents('php://input'), true);
            
            $title = trim($input['title'] ?? '');
            $content = trim($input['content'] ?? '');
            $category = $input['category'] ?? '';
            $tags = $input['tags'] ?? [];
            
            // Validation
            if (empty($title)) {
                throw new Exception('Tiêu đề không được để trống');
            }
            
            if (empty($content)) {
                throw new Exception('Nội dung không được để trống');
            }
            
            if (empty($category)) {
                throw new Exception('Danh mục không được để trống');
            }
            
            // Validate category exists in config
            global $DISCUSS_CATEGORIES;
            $validCategories = array_map('strtolower', array_keys($DISCUSS_CATEGORIES));
            $validCategories = array_merge($validCategories, ['general', 'algorithm', 'data-structure', 'math', 'beginner', 'contest', 'help']);
            
            if (!in_array($category, $validCategories)) {
                throw new Exception('Danh mục không hợp lệ');
            }
            
            if (strlen($title) > 200) {
                throw new Exception('Tiêu đề không được vượt quá 200 ký tự');
            }
            
            if (strlen($content) > 10000) {
                throw new Exception('Nội dung không được vượt quá 10,000 ký tự');
            }
            
            if (count($tags) > 5) {
                throw new Exception('Không được có quá 5 tags');
            }
            
            // Update discussion
            $updateData = [
                'title' => $title,
                'content' => $content,
                'category' => $category,
                'tags' => $tags
            ];
            
            $result = $this->discussionModel->updateDiscussion($id, $updateData);
            
            if (!$result) {
                throw new Exception('Có lỗi xảy ra khi cập nhật bài viết');
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Cập nhật bài viết thành công'
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    private function getTimeAgo($datetime)
    {
        date_default_timezone_set('Asia/Ho_Chi_Minh');
        
        $time = time() - strtotime($datetime);
        if ($time < 60) return 'vừa xong';
        if ($time < 3600) return floor($time/60) . ' phút trước';
        if ($time < 86400) return floor($time/3600) . ' giờ trước';
        if ($time < 2592000) return floor($time/86400) . ' ngày trước';
        
        return date('d/m/Y', strtotime($datetime));
    }
    
    public function createReply()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập để trả lời'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Dữ liệu không hợp lệ');
            }
            
            $discussionId = (int)($input['discussion_id'] ?? 0);
            $content = trim($input['content'] ?? '');
            $parentId = !empty($input['parent_id']) ? (int)$input['parent_id'] : null;
            
            if (!$discussionId) {
                throw new Exception('ID thảo luận không hợp lệ');
            }
            
            if (empty($content)) {
                throw new Exception('Nội dung phản hồi không được để trống');
            }
            
            // Check if discussion exists
            $discussion = $this->discussionModel->getDiscussionById($discussionId);
            if (!$discussion) {
                throw new Exception('Thảo luận không tồn tại');
            }
            
            $replyId = $this->discussionModel->createReply($discussionId, $_SESSION['user_id'], $content, $parentId);
            
            if ($replyId) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Phản hồi đã được tạo thành công',
                    'reply_id' => $replyId
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Không thể tạo phản hồi');
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function likeReply()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $replyId = (int)($input['reply_id'] ?? 0);
            
            if (!$replyId) {
                throw new Exception('ID phản hồi không hợp lệ');
            }
            
            $result = $this->discussionModel->toggleReplyLike($_SESSION['user_id'], $replyId);
            
            echo json_encode([
                'success' => true,
                'action' => $result['action'],
                'likes_count' => $result['likes_count']
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function markSolution()
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $input = json_decode(file_get_contents('php://input'), true);
            $replyId = (int)($input['reply_id'] ?? 0);
            
            if (!$replyId) {
                throw new Exception('ID phản hồi không hợp lệ');
            }
            
            $result = $this->discussionModel->markReplyAsSolution($_SESSION['user_id'], $replyId);
            
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Đã đánh dấu là giải pháp'
                ], JSON_UNESCAPED_UNICODE);
            } else {
                throw new Exception('Bạn không có quyền thực hiện hành động này');
            }
            
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function getUserInteractions($discussionId)
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Not logged in'], JSON_UNESCAPED_UNICODE);
            return;
        }
        
        try {
            $userId = $_SESSION['user_id'];
            $liked = $this->discussionModel->hasUserLiked($userId, $discussionId);
            $bookmarked = $this->discussionModel->hasUserBookmarked($userId, $discussionId);
            
            echo json_encode([
                'success' => true,
                'liked' => $liked,
                'bookmarked' => $bookmarked
            ], JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Server error'
            ], JSON_UNESCAPED_UNICODE);
        }
    }
}
