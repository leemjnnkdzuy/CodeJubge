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
    
    public function createUser()
    {
        if (!$this->isPostRequest()) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $postData = json_decode(file_get_contents('php://input'), true);
        
        if (!$postData) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            return;
        }
        
        $userModel = new UserModel();
        
        // Validate required fields
        $requiredFields = ['firstName', 'lastName', 'username', 'email', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Trường {$field} là bắt buộc"]);
                return;
            }
        }
        
        // Check if username or email already exists
        if ($userModel->getUserByUsername($postData['username'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username đã tồn tại']);
            return;
        }
        
        if ($userModel->getUserByEmail($postData['email'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
            return;
        }
        
        // Create user data
        $userData = [
            'firstName' => $postData['firstName'],
            'lastName' => $postData['lastName'],
            'username' => $postData['username'],
            'email' => $postData['email'],
            'password' => $postData['password'],
            'role' => $postData['role'] ?? 'user',
            'is_active' => isset($postData['isActive']) ? 1 : 0
        ];
        
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
        if (!$this->isPutRequest()) {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            return;
        }
        
        $postData = json_decode(file_get_contents('php://input'), true);
        
        if (!$postData) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
            return;
        }
        
        $userModel = new UserModel();
        
        // Check if user exists
        $existingUser = $userModel->getUserById($userId);
        if (!$existingUser) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User không tồn tại']);
            return;
        }
        
        // Validate required fields
        $requiredFields = ['firstName', 'lastName', 'username', 'email'];
        foreach ($requiredFields as $field) {
            if (empty($postData[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Trường {$field} là bắt buộc"]);
                return;
            }
        }
        
        // Check if username or email already exists (excluding current user)
        $usernameUser = $userModel->getUserByUsername($postData['username']);
        if ($usernameUser && $usernameUser['id'] != $userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Username đã tồn tại']);
            return;
        }
        
        $emailUser = $userModel->getUserByEmail($postData['email']);
        if ($emailUser && $emailUser['id'] != $userId) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Email đã tồn tại']);
            return;
        }
        
        // Update user data
        $userData = [
            'first_name' => $postData['firstName'],
            'last_name' => $postData['lastName'],
            'username' => $postData['username'],
            'email' => $postData['email'],
            'role' => $postData['role'] ?? 'user',
            'is_active' => isset($postData['isActive']) ? 1 : 0
        ];
        
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
        
        // Check if user exists
        $existingUser = $userModel->getUserById($userId);
        if (!$existingUser) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'User không tồn tại']);
            return;
        }
        
        // Prevent deleting admin users
        if ($existingUser['role'] === 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Không thể xóa tài khoản admin']);
            return;
        }
        
        // Prevent self-deletion
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
        $problems = $problemModel->getProblems(['limit' => 100]); // Get more problems for admin view
        
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
    
    public function submissions()
    {
        // Get all submissions with user and problem info
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
    
    private function renderAdminPage($view, $data = [])
    {
        extract($data);
        ob_start();
        include VIEW_PATH . '/' . $view . '.php';
        $content = ob_get_clean();
        include VIEW_PATH . '/layouts/pagesAdminWithSidebar.php';
    }
}
