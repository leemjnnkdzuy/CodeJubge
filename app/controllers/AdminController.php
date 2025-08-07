<?php
require_once CORE_PATH . '/Controller.php';
require_once MODEL_PATH . '/UserModel.php';
require_once MODEL_PATH . '/ProblemModel.php';
require_once APP_PATH . '/helpers/NotificationHelper.php';

class AdminController extends Controller
{
    public function __construct()
    {
        $currentAction = $this->getCurrentAction();
        if ($currentAction !== 'login') {
            if (!$this->isLoggedIn()) {
                $this->redirect('/admin/login');
            }
            
            if (!$this->isAdmin()) {
                $this->redirectWithMessage('/', 'Bạn không có quyền truy cập trang admin.');
            }
        }
    }
    
    private function getCurrentAction()
    {
        $uri = $_SERVER['REQUEST_URI'];
        if (strpos($uri, '/admin/login') !== false) {
            return 'login';
        }
        return 'other';
    }
    
    protected function getDatabase()
    {
        require_once __DIR__ . '/../../config/databaseConnect.php';
        $database = Database::getInstance();
        return $database->getConnection();
    }
    
    private function isAdmin()
    {
        return isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin';
    }
    
    public function index()
    {
        $stats = $this->getDashboardStats();
        
        $data = [
            'title' => 'Admin Dashboard - CodeJudge',
            'pageTitle' => 'Dashboard',
            'breadcrumb' => [
                ['title' => 'Dashboard']
            ],
            'stats' => $stats
        ];
        
        $this->renderAdminPage('admin/dashboard', $data);
    }
    
    public function login()
    {
        if ($this->isLoggedIn() && $this->isAdmin()) {
            $this->redirect('/admin');
        }
        
        if ($this->isPostRequest()) {
            $postData = $this->getPostData(['email', 'password']);
            $remember = isset($_POST['remember']);
            
            if ($postData['email'] && $postData['password']) {
                $userModel = new UserModel();
                $result = $userModel->loginUser($postData['email'], $postData['password']);
                
                if ($result['success']) {
                    if ($result['user']['role'] !== 'admin') {
                        NotificationHelper::error('Bạn không có quyền truy cập trang admin.');
                        $this->renderPage('admin/login', 'Admin Login - CodeJudge', 'Đăng nhập vào trang quản trị');
                        return;
                    }
                    
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['user'] = $result['user'];
                    
                    if ($remember) {
                        setcookie('remember_token', $result['user']['id'], time() + (30 * 24 * 60 * 60), '/');
                    }
                    
                    $this->redirectWithMessage('/admin', 'Đăng nhập admin thành công!');
                } else {
                    NotificationHelper::error($result['message']);
                }
            } else {
                NotificationHelper::error('Vui lòng nhập đầy đủ email và mật khẩu');
            }
        }
        
        $this->renderPage('admin/login', 'Admin Login - CodeJudge', 'Đăng nhập vào trang quản trị');
    }
    
    public function users()
    {
        $userModel = new UserModel();
        $users = $userModel->getAllUsers();
        
        $data = [
            'title' => 'Quản lý Users - Admin - CodeJudge',
            'pageTitle' => 'Quản lý Users',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Quản lý Users']
            ],
            'users' => $users
        ];
        
        $this->renderAdminPage('admin/users', $data);
    }
    
    public function getUsersTableData()
    {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $userModel = new UserModel();
        $users = $userModel->getAllUsers();
        
        // Generate table HTML
        ob_start();
        require_once APP_PATH . '/helpers/AvatarHelper.php';
        ?>
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Problems Solved</th>
                    <th>Rating</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users) && is_array($users)): ?>
                    <?php foreach ($users as $user): 
                    $userAvatar = AvatarHelper::base64ToImageSrc($user['avatar'] ?? '');
                    $userInitials = AvatarHelper::getInitials($user['first_name'] . ' ' . $user['last_name']);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($user['id']) ?></td>
                        <td>
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?php if (!empty($user['avatar'])): ?>
                                        <img src="<?= $userAvatar ?>" alt="Avatar" class="avatar-image">
                                    <?php else: ?>
                                        <div class="avatar-initials"><?= $userInitials ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="user-details">
                                    <div class="user-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>@<?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="role-badge role-<?= strtolower($user['role']) ?>"><?= ucfirst($user['role']) ?></span></td>
                        <td><span class="status-badge <?= $user['is_active'] ? 'status-active' : 'status-inactive' ?>"><?= $user['is_active'] ? 'Hoạt động' : 'Không hoạt động' ?></span></td>
                        <td>0</td>
                        <td><?= $user['rating'] > 0 ? number_format($user['rating']) : 'Chưa xếp hạng' ?></td>
                        <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-action btn-edit" data-user-id="<?= $user['id'] ?>" title="Chỉnh sửa">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <a href="/user/<?= htmlspecialchars($user['username']) ?>" class="btn-action btn-view" target="_blank" title="Xem chi tiết">
                                    <i class='bx bx-show'></i>
                                </a>
                                <?php if ($user['role'] !== 'admin'): ?>
                                <button class="btn-action btn-delete" data-user-id="<?= $user['id'] ?>" title="Xóa">
                                    <i class='bx bx-trash'></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                            <i class='bx bx-user-x' style="font-size: 3rem; display: block; margin-bottom: 1rem; opacity: 0.5;"></i>
                            <p>Không có dữ liệu người dùng</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
        $tableHtml = ob_get_clean();
        
        echo json_encode([
            'success' => true,
            'html' => $tableHtml
        ]);
    }
    
    public function getUserById($userId)
    {
        if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid request']);
            return;
        }
        
        $userModel = new UserModel();
        $user = $userModel->getUserByIdAdmin($userId);
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        $user['badges'] = isset($user['badges']) && !empty($user['badges']) ? json_decode($user['badges'], true) : [];
        
        echo json_encode([
            'success' => true,
            'user' => $user
        ]);
    }
    
    public function createUser()
    {
        if (!$this->isPostRequest()) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $postData = $_POST;
        if (empty($postData)) {
            $postData = json_decode(file_get_contents('php://input'), true);
        }
        
        if (!$postData) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }
        
        $userModel = new UserModel();
        
        $requiredFields = ['firstName', 'lastName', 'username', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Trường {$field} là bắt buộc"]);
                return;
            }
        }
        
        if ($userModel->getUserByUsernameAdmin($postData['username'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username đã tồn tại']);
            return;
        }
        
        if ($userModel->getUserByEmailAdmin($postData['email'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
            return;
        }
        
        $userData = [
            'firstName' => $postData['firstName'],
            'lastName' => $postData['lastName'],
            'username' => $postData['username'],
            'email' => $postData['email'],
            'password' => $postData['password'],
            'role' => $postData['role'] ?? 'user',
            'is_active' => ($postData['isActive'] ?? '0') === '1' ? 1 : 0
        ];

        if (isset($postData['bio'])) {
            $userData['bio'] = $postData['bio'];
        }
        
        if (isset($postData['rating'])) {
            $userData['rating'] = intval($postData['rating']);
        }

        $socialFields = ['github_url', 'linkedin_url', 'website_url', 'youtube_url', 'facebook_url', 'instagram_url'];
        foreach ($socialFields as $field) {
            if (isset($postData[$field]) && !empty($postData[$field])) {
                $userData[$field] = $postData[$field];
            }
        }

        if (isset($postData['badges'])) {
            if (is_array($postData['badges'])) {
                $userData['badges'] = json_encode($postData['badges']);
            } elseif (is_string($postData['badges'])) {
                $badgesArray = json_decode($postData['badges'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($badgesArray)) {
                    $userData['badges'] = $postData['badges'];
                } else {
                    $userData['badges'] = '[]';
                }
            } else {
                $userData['badges'] = '[]';
            }
        } else {
            $userData['badges'] = '[]';
        }

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarData = $this->handleAvatarUpload($_FILES['avatar']);
            if ($avatarData) {
                $userData['avatar'] = $avatarData;
            }
        } else {
            $userData['avatar'] = $this->getDefaultAvatarBase64();
        }
        
        try {
            $result = $userModel->createUser($userData);
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'User đã được tạo thành công']);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => $result['message']]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
    
    public function updateUser($userId)
    {
        if (!$this->isPutRequest() && !$this->isPostRequest()) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        if ($this->isPostRequest()) {
            $postData = $_POST;
            $files = $_FILES;
        } else {
            $postData = json_decode(file_get_contents('php://input'), true);
            $files = [];
        }
        
        if (!$postData) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            return;
        }
        
        $userModel = new UserModel();
        
        $existingUser = $userModel->getUserByIdAdmin($userId); // Sử dụng method admin để có thể cập nhật user bị vô hiệu hóa
        if (!$existingUser) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User không tồn tại']);
            return;
        }
        
        $requiredFields = ['firstName', 'lastName', 'username', 'email'];
        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Trường {$field} là bắt buộc"]);
                return;
            }
        }
        
        $usernameUser = $userModel->getUserByUsernameAdmin($postData['username']);
        if ($usernameUser && $usernameUser['id'] != $userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username đã tồn tại']);
            return;
        }
        
        $emailUser = $userModel->getUserByEmailAdmin($postData['email']);
        if ($emailUser && $emailUser['id'] != $userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
            return;
        }
        
        $userData = [
            'first_name' => $postData['firstName'],
            'last_name' => $postData['lastName'],
            'username' => $postData['username'],
            'email' => $postData['email'],
            'role' => $postData['role'] ?? 'user',
            'is_active' => ($postData['isActive'] ?? '0') === '1' ? 1 : 0
        ];

        if (isset($postData['bio'])) {
            $userData['bio'] = $postData['bio'];
        }
        
        if (isset($postData['rating'])) {
            $userData['rating'] = intval($postData['rating']);
        }

        $socialFields = ['github_url', 'linkedin_url', 'website_url', 'youtube_url', 'facebook_url', 'instagram_url'];
        foreach ($socialFields as $field) {
            if (isset($postData[$field])) {
                $userData[$field] = !empty($postData[$field]) ? $postData[$field] : null;
            }
        }

        if (isset($postData['badges'])) {
            if (is_array($postData['badges'])) {
                $userData['badges'] = json_encode($postData['badges']);
            } elseif (is_string($postData['badges'])) {
                $badgesArray = json_decode($postData['badges'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($badgesArray)) {
                    $userData['badges'] = $postData['badges'];
                } else {
                    $userData['badges'] = '[]';
                }
            } else {
                $userData['badges'] = '[]';
            }
        } else {
            $userData['badges'] = '[]';
        }

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $avatarData = $this->handleAvatarUpload($_FILES['avatar']);
            if ($avatarData) {
                $userData['avatar'] = $avatarData;
            }
        }
        
        try {
            $result = $userModel->updateUserAdmin($userId, $userData);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User đã được cập nhật thành công']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật user']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
    
    public function deleteUser($userId)
    {
        if (!$this->isDeleteRequest()) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $userModel = new UserModel();
        
        $existingUser = $userModel->getUserByIdAdmin($userId); // Sử dụng method admin để có thể xóa user bị vô hiệu hóa
        if (!$existingUser) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User không tồn tại']);
            return;
        }
        
        if ($existingUser['role'] === 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không thể xóa tài khoản admin']);
            return;
        }
        
        if ($userId == $_SESSION['user_id']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không thể xóa tài khoản của chính mình']);
            return;
        }
        
        try {
            $result = $userModel->deleteUser($userId);
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'User đã được xóa thành công']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa user']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }
    
    private function isPutRequest()
    {
        return $_SERVER['REQUEST_METHOD'] === 'PUT';
    }
    
    private function isDeleteRequest()
    {
        return $_SERVER['REQUEST_METHOD'] === 'DELETE';
    }
    
    public function problems()
    {
        $problemModel = new ProblemModel();
        $problems = $problemModel->getProblemsForAdmin(['limit' => 100]);
        
        $data = [
            'title' => 'Quản lý Problems - Admin - CodeJudge',
            'pageTitle' => 'Quản lý Problems',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Quản lý Problems']
            ],
            'problems' => $problems
        ];
        
        $this->renderAdminPage('admin/problems', $data);
    }

    public function createProblem()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $data = [
                'title' => 'Tạo Problem - Admin - CodeJudge',
                'pageTitle' => 'Tạo Problem Mới',
                'breadcrumb' => [
                    ['title' => 'Dashboard', 'url' => '/admin'],
                    ['title' => 'Quản lý Problems', 'url' => '/admin/problems'],
                    ['title' => 'Tạo Problem']
                ]
            ];
            $this->renderAdminPage('admin/problem_form', $data);
        }
    }

    public function storeProblem()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            // Validate required fields
            $requiredFields = ['title', 'description', 'difficulty'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->respondJson(['success' => false, 'message' => "Trường {$field} là bắt buộc"]);
                    return;
                }
            }

            // Prepare data
            $data = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'difficulty' => $_POST['difficulty'],
                'category' => $_POST['category'] ?? null,
                'input_format' => $_POST['input_format'] ?? '',
                'output_format' => $_POST['output_format'] ?? '',
                'constraints' => $_POST['constraints'] ?? '',
                'time_limit' => (int)($_POST['time_limit'] ?? 1000),
                'memory_limit' => (int)($_POST['memory_limit'] ?? 128),
                'editorial' => $_POST['editorial'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
                'created_by' => $_SESSION['user']['id']
            ];

            // Parse examples
            if (!empty($_POST['examples'])) {
                $data['examples'] = json_decode($_POST['examples'], true) ?? [];
            }

            // Parse test cases
            if (!empty($_POST['test_cases'])) {
                $data['test_cases'] = json_decode($_POST['test_cases'], true) ?? [];
            }

            // Parse tags
            if (!empty($_POST['tags'])) {
                $tags = explode(',', $_POST['tags']);
                $data['tags'] = array_map('trim', $tags);
            }

            // Parse problem types
            if (!empty($_POST['problem_types'])) {
                $data['problem_types'] = json_decode($_POST['problem_types'], true) ?? [];
            }

            // Parse hints
            if (!empty($_POST['hints'])) {
                $data['hints'] = json_decode($_POST['hints'], true) ?? [];
            }

            $problemModel = new ProblemModel();
            $result = $problemModel->createProblem($data);

            if ($result['success']) {
                $this->setNotification('success', $result['message']);
                $this->respondJson(['success' => true, 'redirect' => '/admin/problems']);
            } else {
                $this->respondJson($result);
            }

        } catch (Exception $e) {
            error_log("Error in storeProblem: " . $e->getMessage());
            $this->respondJson(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo problem']);
        }
    }

    public function editProblem($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $problemModel = new ProblemModel();
            $problem = $problemModel->getProblemById($id);

            if (!$problem) {
                $this->redirectWithMessage('/admin/problems', 'Problem không tồn tại');
                return;
            }

            $data = [
                'title' => 'Sửa Problem - Admin - CodeJudge',
                'pageTitle' => 'Sửa Problem',
                'breadcrumb' => [
                    ['title' => 'Dashboard', 'url' => '/admin'],
                    ['title' => 'Quản lý Problems', 'url' => '/admin/problems'],
                    ['title' => 'Sửa Problem']
                ],
                'problem' => $problem
            ];
            $this->renderAdminPage('admin/problem_form', $data);
        }
    }

    public function updateProblem($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            // Validate required fields
            $requiredFields = ['title', 'description', 'difficulty'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->respondJson(['success' => false, 'message' => "Trường {$field} là bắt buộc"]);
                    return;
                }
            }

            // Prepare data (same as storeProblem)
            $data = [
                'title' => trim($_POST['title']),
                'description' => trim($_POST['description']),
                'difficulty' => $_POST['difficulty'],
                'category' => $_POST['category'] ?? null,
                'input_format' => $_POST['input_format'] ?? '',
                'output_format' => $_POST['output_format'] ?? '',
                'constraints' => $_POST['constraints'] ?? '',
                'time_limit' => (int)($_POST['time_limit'] ?? 1000),
                'memory_limit' => (int)($_POST['memory_limit'] ?? 128),
                'editorial' => $_POST['editorial'] ?? '',
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            // Parse examples, test cases, tags, etc. (same as storeProblem)
            if (!empty($_POST['examples'])) {
                $data['examples'] = json_decode($_POST['examples'], true) ?? [];
            }

            if (!empty($_POST['test_cases'])) {
                $data['test_cases'] = json_decode($_POST['test_cases'], true) ?? [];
            }

            if (!empty($_POST['tags'])) {
                $tags = explode(',', $_POST['tags']);
                $data['tags'] = array_map('trim', $tags);
            }

            if (!empty($_POST['problem_types'])) {
                $data['problem_types'] = json_decode($_POST['problem_types'], true) ?? [];
            }

            if (!empty($_POST['hints'])) {
                $data['hints'] = json_decode($_POST['hints'], true) ?? [];
            }

            $problemModel = new ProblemModel();
            $result = $problemModel->updateProblem($id, $data);

            if ($result['success']) {
                $this->setNotification('success', $result['message']);
                $this->respondJson(['success' => true, 'redirect' => '/admin/problems']);
            } else {
                $this->respondJson($result);
            }

        } catch (Exception $e) {
            error_log("Error in updateProblem: " . $e->getMessage());
            $this->respondJson(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật problem']);
        }
    }

    public function deleteProblem($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respondJson(['success' => false, 'message' => 'Method not allowed'], 405);
            return;
        }

        try {
            $problemModel = new ProblemModel();
            $result = $problemModel->deleteProblem($id);

            if ($result['success']) {
                $this->setNotification('success', $result['message']);
                $this->respondJson(['success' => true, 'redirect' => '/admin/problems']);
            } else {
                $this->respondJson($result);
            }

        } catch (Exception $e) {
            error_log("Error in deleteProblem: " . $e->getMessage());
            $this->respondJson(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa problem']);
        }
    }

    private function respondJson($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    public function submissions()
    {
        $submissions = $this->getSubmissions();
        
        $data = [
            'title' => 'Submissions - Admin - CodeJudge',
            'pageTitle' => 'Submissions',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Submissions']
            ],
            'submissions' => $submissions
        ];
        
        $this->renderAdminPage('admin/submissions', $data);
    }
    
    public function contests()
    {
        $data = [
            'title' => 'Quản lý Contests - Admin - CodeJudge',
            'pageTitle' => 'Quản lý Contests',
            'breadcrumb' => [
                ['title' => 'Dashboard', 'url' => '/admin'],
                ['title' => 'Quản lý Contests']
            ]
        ];
        
        $this->renderAdminPage('admin/contests', $data);
    }
    
    private function getDashboardStats()
    {
        try {
            $db = $this->getDatabase();
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM users");
            $totalUsers = $stmt->fetch()['total'];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM problems");
            $totalProblems = $stmt->fetch()['total'];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM submissions");
            $totalSubmissions = $stmt->fetch()['total'];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM submissions WHERE status = 'accepted'");
            $successfulSubmissions = $stmt->fetch()['total'];
            
            $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $recentUsers = $stmt->fetch()['total'];
            
            return [
                'totalUsers' => $totalUsers,
                'totalProblems' => $totalProblems,
                'totalSubmissions' => $totalSubmissions,
                'successfulSubmissions' => $successfulSubmissions,
                'recentUsers' => $recentUsers,
                'acceptanceRate' => $totalSubmissions > 0 ? round(($successfulSubmissions / $totalSubmissions) * 100, 2) : 0
            ];
        } catch (Exception $e) {
            return [
                'totalUsers' => 0,
                'totalProblems' => 0,
                'totalSubmissions' => 0,
                'successfulSubmissions' => 0,
                'recentUsers' => 0,
                'acceptanceRate' => 0
            ];
        }
    }
    
    private function getSubmissions()
    {
        try {
            $db = $this->getDatabase();
            
            $stmt = $db->query("
                SELECT s.*, u.username, u.first_name, u.last_name, u.avatar, p.title as problem_title, p.slug as problem_slug
                FROM submissions s
                JOIN users u ON s.user_id = u.id
                JOIN problems p ON s.problem_id = p.id
                ORDER BY s.submitted_at DESC
                LIMIT 50
            ");
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }
    
    private function handleAvatarUpload($file)
    {
        try {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 2 * 1024 * 1024;
            
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Only JPG, PNG, GIF, WebP are allowed.');
            }
            
            if ($file['size'] > $maxSize) {
                throw new Exception('File too large. Maximum size is 2MB.');
            }
            
            $imageData = file_get_contents($file['tmp_name']);
            if ($imageData === false) {
                throw new Exception('Error reading uploaded file.');
            }
            
            return base64_encode($imageData);
            
        } catch (Exception $e) {
            error_log("Avatar upload error: " . $e->getMessage());
            return null;
        }
    }
    
    private function getDefaultAvatarBase64()
    {
        $defaultAvatarPath = dirname(dirname(__DIR__)) . '/public/assets/default_avatar.png';
        
        if (file_exists($defaultAvatarPath)) {
            $imageData = file_get_contents($defaultAvatarPath);
            if ($imageData !== false) {
                return base64_encode($imageData);
            }
        }
        return null;
    }
    
    private function setNotification($type, $message)
    {
        $_SESSION['notification'] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    private function renderAdminPage($view, $data = [])
    {
        extract($data);
        ob_start();
        include VIEW_PATH . '/' . $view . '.php';
        $content = ob_get_clean();
        include VIEW_PATH . '/layouts/pagesAdminWithSidebar.php';
    }
}
