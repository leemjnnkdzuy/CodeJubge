<?php
require_once CORE_PATH . '/Controller.php';
require_once MODEL_PATH . '/UserModel.php';
require_once MODEL_PATH . '/ProblemModel.php';
require_once APP_PATH . '/helpers/NotificationHelper.php';
require_once APP_PATH . '/helpers/SecurityHelper.php';

class ContestController extends Controller
{
    private $contestModel;
    private $userModel;
    private $problemModel;
    
    public function __construct()
    {
        require_once MODEL_PATH . '/ContestModel.php';
        $this->contestModel = new ContestModel();
        $this->userModel = new UserModel();
        $this->problemModel = new ProblemModel();
    }
    
    /**
     * Display contests page
     */
    public function index()
    {
        include VIEW_PATH . '/contests.php';
    }
    
    /**
     * API endpoint to get contests list
     */
    public function api()
    {
        try {
            $status = $_GET['status'] ?? 'all';
            $search = trim($_GET['search'] ?? '');
            $difficulty = $_GET['difficulty'] ?? '';
            $duration = $_GET['duration'] ?? '';
            $sort = $_GET['sort'] ?? 'start_time_desc';
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 12;
            $offset = ($page - 1) * $limit;
            
            $userId = $_SESSION['user_id'] ?? null;
            
            $filters = [
                'status' => $status,
                'search' => $search,
                'difficulty' => $difficulty,
                'duration' => $duration,
                'sort' => $sort,
                'user_id' => $userId
            ];
            
            $contests = $this->contestModel->getContests($filters, $limit, $offset);
            $totalContests = $this->contestModel->getContestsCount($filters);
            $totalPages = ceil($totalContests / $limit);
            
            $this->jsonResponse([
                'success' => true,
                'contests' => $contests,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'total_contests' => $totalContests,
                    'has_next' => $page < $totalPages,
                    'has_prev' => $page > 1
                ]
            ]);
            
        } catch (Exception $e) {
            error_log("Contest API Error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tải contests'
            ], 500);
        }
    }
    
    /**
     * Show contest details
     */
    public function show($contestId)
    {
        try {
            if (!$contestId || !is_numeric($contestId)) {
                $this->notFound();
                return;
            }
            
            $contest = $this->contestModel->getContestById($contestId);
            if (!$contest) {
                $this->notFound();
                return;
            }
            
            $userId = $_SESSION['user_id'] ?? null;
            $isParticipant = false;
            $isCreator = false;
            
            if ($userId) {
                $isParticipant = $this->contestModel->isParticipant($contestId, $userId);
                $isCreator = ($contest['created_by'] == $userId);
            }
            
            // Get contest problems
            $problems = $this->contestModel->getContestProblems($contestId);
            
            // Get contest leaderboard (top 10)
            $leaderboard = $this->contestModel->getContestLeaderboard($contestId, 10);
            
            // Get contest statistics
            $stats = $this->contestModel->getContestStats($contestId);
            
            $data = [
                'contest' => $contest,
                'problems' => $problems,
                'leaderboard' => $leaderboard,
                'stats' => $stats,
                'isParticipant' => $isParticipant,
                'isCreator' => $isCreator,
                'userId' => $userId
            ];
            
            extract($data);
            include VIEW_PATH . '/contest_detail.php';
            
        } catch (Exception $e) {
            error_log("Contest Show Error: " . $e->getMessage());
            $this->redirectWithMessage('/', 'Có lỗi xảy ra khi tải contest', 'error');
        }
    }
    
    /**
     * Show contest leaderboard
     */
    public function leaderboard($contestId)
    {
        try {
            if (!$contestId || !is_numeric($contestId)) {
                $this->notFound();
                return;
            }
            
            $contest = $this->contestModel->getContestById($contestId);
            if (!$contest) {
                $this->notFound();
                return;
            }
            
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 50;
            $offset = ($page - 1) * $limit;
            
            // Get leaderboard data
            $leaderboard = $this->contestModel->getContestLeaderboard($contestId, $limit, $offset);
            $totalParticipants = $this->contestModel->getContestParticipantsCount($contestId);
            $totalPages = ceil($totalParticipants / $limit);
            
            $data = [
                'contest' => $contest,
                'leaderboard' => $leaderboard,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalParticipants' => $totalParticipants,
                'hasNextPage' => $page < $totalPages,
                'hasPrevPage' => $page > 1
            ];
            
            extract($data);
            include VIEW_PATH . '/contest_leaderboard.php';
            
        } catch (Exception $e) {
            error_log("Contest Leaderboard Error: " . $e->getMessage());
            $this->redirectWithMessage('/contests', 'Có lỗi xảy ra khi tải bảng xếp hạng', 'error');
        }
    }
    
    /**
     * Create new contest
     */
    public function create()
    {
        $this->requireAuth();
        
        if (!$this->isPostRequest()) {
            $this->methodNotAllowed();
            return;
        }
        
        try {
            $data = $this->getJsonData();
            
            // Validate input
            $validation = $this->validateContestData($data);
            if (!$validation['valid']) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $validation['message']
                ], 400);
                return;
            }
            
            // Create contest
            $contestData = [
                'title' => trim($data['title']),
                'description' => trim($data['description'] ?? ''),
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'difficulty' => $data['difficulty'] ?? 'medium',
                'type' => $data['type'] ?? 'public',
                'rules' => trim($data['rules'] ?? ''),
                'created_by' => $_SESSION['user_id']
            ];
            
            $contestId = $this->contestModel->createContest($contestData);
            
            if ($contestId) {
                // Log activity
                error_log("Contest created: ID {$contestId} by user {$_SESSION['user_id']}");
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Contest đã được tạo thành công!',
                    'contest_id' => $contestId
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo contest'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("Contest Create Error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo contest'
            ], 500);
        }
    }
    
    /**
     * Join contest
     */
    public function join($contestId)
    {
        $this->requireAuth();
        
        if (!$this->isPostRequest()) {
            $this->methodNotAllowed();
            return;
        }
        
        try {
            if (!$contestId || !is_numeric($contestId)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Contest không hợp lệ'
                ], 400);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            $contest = $this->contestModel->getContestById($contestId);
            
            if (!$contest) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Contest không tồn tại'
                ], 404);
                return;
            }
            
            // Check if already joined
            if ($this->contestModel->isParticipant($contestId, $userId)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Bạn đã tham gia contest này rồi'
                ], 400);
                return;
            }
            
            // Check if contest has ended
            if (strtotime($contest['end_time']) < time()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Contest đã kết thúc'
                ], 400);
                return;
            }
            
            // Join contest
            $result = $this->contestModel->joinContest($contestId, $userId);
            
            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Tham gia contest thành công!'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tham gia contest'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("Contest Join Error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tham gia contest'
            ], 500);
        }
    }
    
    /**
     * Leave contest
     */
    public function leave($contestId)
    {
        $this->requireAuth();
        
        if (!$this->isPostRequest()) {
            $this->methodNotAllowed();
            return;
        }
        
        try {
            if (!$contestId || !is_numeric($contestId)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Contest không hợp lệ'
                ], 400);
                return;
            }
            
            $userId = $_SESSION['user_id'];
            $contest = $this->contestModel->getContestById($contestId);
            
            if (!$contest) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Contest không tồn tại'
                ], 404);
                return;
            }
            
            // Check if joined
            if (!$this->contestModel->isParticipant($contestId, $userId)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Bạn chưa tham gia contest này'
                ], 400);
                return;
            }
            
            // Check if contest has started
            if (strtotime($contest['start_time']) <= time()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Không thể rời contest đã bắt đầu'
                ], 400);
                return;
            }
            
            // Leave contest
            $result = $this->contestModel->leaveContest($contestId, $userId);
            
            if ($result) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Đã rời khỏi contest!'
                ]);
            } else {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi rời contest'
                ], 500);
            }
            
        } catch (Exception $e) {
            error_log("Contest Leave Error: " . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi rời contest'
            ], 500);
        }
    }
    
    /**
     * Send JSON response
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
    
    /**
     * Get JSON data from request body
     */
    protected function getJsonData()
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Send 404 Not Found response
     */
    protected function notFound()
    {
        http_response_code(404);
        include VIEW_PATH . '/404.php';
        exit;
    }
    
    /**
     * Send 405 Method Not Allowed response
     */
    protected function methodNotAllowed()
    {
        http_response_code(405);
        $this->jsonResponse([
            'success' => false,
            'message' => 'Method not allowed'
        ], 405);
    }
    
    /**
     * Validate contest data
     */
    private function validateContestData($data)
    {
        if (empty($data['title']) || strlen(trim($data['title'])) < 3) {
            return ['valid' => false, 'message' => 'Tên contest phải có ít nhất 3 ký tự'];
        }
        
        if (strlen(trim($data['title'])) > 255) {
            return ['valid' => false, 'message' => 'Tên contest không được vượt quá 255 ký tự'];
        }
        
        if (!empty($data['description']) && strlen(trim($data['description'])) > 1000) {
            return ['valid' => false, 'message' => 'Mô tả không được vượt quá 1000 ký tự'];
        }
        
        if (empty($data['start_time']) || empty($data['end_time'])) {
            return ['valid' => false, 'message' => 'Vui lòng chọn thời gian bắt đầu và kết thúc'];
        }
        
        $startTime = strtotime($data['start_time']);
        $endTime = strtotime($data['end_time']);
        $now = time();
        
        if ($startTime === false || $endTime === false) {
            return ['valid' => false, 'message' => 'Thời gian không hợp lệ'];
        }
        
        if ($startTime <= $now) {
            return ['valid' => false, 'message' => 'Thời gian bắt đầu phải sau thời điểm hiện tại'];
        }
        
        if ($endTime <= $startTime) {
            return ['valid' => false, 'message' => 'Thời gian kết thúc phải sau thời gian bắt đầu'];
        }
        
        // Minimum duration: 30 minutes
        if (($endTime - $startTime) < 1800) {
            return ['valid' => false, 'message' => 'Contest phải có thời gian tối thiểu 30 phút'];
        }
        
        // Maximum duration: 30 days
        if (($endTime - $startTime) > (30 * 24 * 3600)) {
            return ['valid' => false, 'message' => 'Contest không được kéo dài quá 30 ngày'];
        }
        
        $allowedDifficulties = ['easy', 'medium', 'hard'];
        if (!empty($data['difficulty']) && !in_array($data['difficulty'], $allowedDifficulties)) {
            return ['valid' => false, 'message' => 'Độ khó không hợp lệ'];
        }
        
        $allowedTypes = ['public', 'private', 'invite_only'];
        if (!empty($data['type']) && !in_array($data['type'], $allowedTypes)) {
            return ['valid' => false, 'message' => 'Loại contest không hợp lệ'];
        }
        
        return ['valid' => true];
    }
}
