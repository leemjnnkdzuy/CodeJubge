<?php
require_once CORE_PATH . '/Controller.php';
require_once MODEL_PATH . '/UserModel.php';
require_once APP_PATH . '/helpers/NotificationHelper.php';

class pagesController extends Controller
{
    private function redirectIfLoggedIn()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: /home');
            exit;
        }
        return false;
    }
    
    public function welcome()
    {
        
        include VIEW_PATH . '/welcome.php';
    }
    
    public function home()
    {
        include VIEW_PATH . '/home.php';
    }
    
    public function login()
    {
        $this->redirectIfLoggedIn();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $remember = isset($_POST['remember']);
            
            if ($email && $password) {
                $userModel = new UserModel();
                $result = $userModel->loginUser($email, $password);
                
                if ($result['success']) {
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['user'] = $result['user'];
                    NotificationHelper::success('Đăng nhập thành công! Chào mừng bạn trở lại!');
                    
                    if ($remember) {
                        setcookie('remember_token', $result['user']['id'], time() + (30 * 24 * 60 * 60), '/');
                    }
                    
                    header('Location: /home');
                    exit;
                } else {
                    NotificationHelper::error($result['message']);
                }
            } else {
                NotificationHelper::error('Vui lòng nhập đầy đủ email và mật khẩu');
            }
        }
        
        $title = 'Đăng nhập - CodeJudge';
        $description = 'Đăng nhập vào tài khoản CodeJudge của bạn';
        
        include VIEW_PATH . '/login.php';
    }
    
    public function register()
    {
        $this->redirectIfLoggedIn();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $firstName = $_POST['firstName'] ?? '';
            $lastName = $_POST['lastName'] ?? '';
            $username = $_POST['username'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirmPassword'] ?? '';
            $terms = isset($_POST['terms']);
            $newsletter = isset($_POST['newsletter']);
            
            if (!$terms) {
                NotificationHelper::error('Bạn phải đồng ý với điều khoản dịch vụ');
            } 
            elseif ($password !== $confirmPassword) {
                NotificationHelper::error('Mật khẩu xác nhận không khớp');
            } 
            else {
                $userData = [
                    'firstName' => $firstName,
                    'lastName' => $lastName,
                    'username' => $username,
                    'email' => $email,
                    'password' => $password
                ];
                
                $userModel = new UserModel();
                $result = $userModel->createUser($userData);
                
                if ($result['success']) {
                    NotificationHelper::success($result['message'] . ' Bạn có thể đăng nhập ngay bây giờ.');
                    header('Location: ' . SITE_URL . '/login');
                    exit;
                } else {
                    NotificationHelper::error($result['message']);
                }
            }
        }
        
        $title = 'Đăng ký - CodeJudge';
        $description = 'Tạo tài khoản CodeJudge của bạn';
        
        include VIEW_PATH . '/signup.php';
    }
    
    public function forgotPassword()
    {
        $this->redirectIfLoggedIn();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            
            if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $userModel = new UserModel();
                $result = $userModel->resetPassword($email);
                
                if ($result['success']) {
                    NotificationHelper::success('Mật khẩu mới đã được gửi đến email của bạn: ' . $result['new_password']);
                } else {
                    NotificationHelper::error($result['message']);
                }
            } else {
                NotificationHelper::error('Vui lòng nhập địa chỉ email hợp lệ');
            }
        }
        
        $title = 'Quên mật khẩu - CodeJudge';
        $description = 'Khôi phục mật khẩu tài khoản CodeJudge';
        
        include VIEW_PATH . '/fogotPassword.php';
    }
    
    public function logout()
    {
        session_unset();
        session_destroy();
        
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        session_start();
        NotificationHelper::success('Đăng xuất thành công');
        
        header('Location: /welcome');
        exit;
    }
    
    public function problems()
    {
        require_once MODEL_PATH . '/ProblemModel.php';
        $problemModel = new ProblemModel();
        
        $filters = [
            'page' => (int)($_GET['page'] ?? 1),
            'limit' => (int)($_GET['limit'] ?? 10),
            'search' => $_GET['search'] ?? '',
            'difficulty' => $_GET['difficulty'] ?? [],
            'problem_types' => $_GET['problem_types'] ?? [],
            'status' => $_GET['status'] ?? 'all',
            'sort' => $_GET['sort'] ?? 'id'
        ];
        
        if (isset($_SESSION['user_id'])) {
            $filters['user_id'] = $_SESSION['user_id'];
        }
        
        if (is_string($filters['difficulty'])) {
            $filters['difficulty'] = explode(',', $filters['difficulty']);
        }
        
        if (is_string($filters['problem_types'])) {
            $filters['problem_types'] = explode(',', $filters['problem_types']);
        }
        
        $result = $problemModel->getProblems($filters);
        $stats = $problemModel->getProblemsStats();
        
        global $TYPE_PROBLEM;
        
        $problems = $result['problems'];
        $pagination = [
            'current_page' => $result['page'],
            'total_pages' => $result['total_pages'],
            'total_items' => $result['total'],
            'limit' => $result['limit']
        ];
        
        include VIEW_PATH . '/problems.php';
    }
    
    public function problemDetail($slug)
    {
        require_once MODEL_PATH . '/ProblemModel.php';
        $problemModel = new ProblemModel();
        
        $problem = $problemModel->getProblemBySlug($slug);
        
        if (!$problem) {
            header('Location: /404');
            exit;
        }
        
        $userSubmissions = [];
        if (isset($_SESSION['user_id'])) {
            $userSubmissions = $problemModel->getUserSubmissions(
                $_SESSION['user_id'], 
                $problem['id']
            );
        }
        
        $exampleSubmissions = $problemModel->getExampleSubmissions($problem['id'], 5);
        
        $title = $problem['title'] . ' - CodeJudge';
        $description = substr(strip_tags($problem['description']), 0, 150) . '...';
        
        include VIEW_PATH . '/problem_detail.php';
    }
    
    public function show404()
    {
        if (isset($_SESSION['notification'])) {
            unset($_SESSION['notification']);
        }
        
        http_response_code(404);
        $title = '404 - Không Tìm Thấy Trang';
        include VIEW_PATH . '/404.php';
    }
}
?>
