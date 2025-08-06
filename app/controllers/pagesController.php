<?php
require_once CORE_PATH . '/Controller.php';
require_once MODEL_PATH . '/UserModel.php';
require_once APP_PATH . '/helpers/NotificationHelper.php';

class pagesController extends Controller
{
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
        
        if ($this->isPostRequest()) {
            $postData = $this->getPostData(['email', 'password']);
            $remember = isset($_POST['remember']);
            
            if ($postData['email'] && $postData['password']) {
                $userModel = new UserModel();
                $result = $userModel->loginUser($postData['email'], $postData['password']);
                
                if ($result['success']) {
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['user'] = $result['user'];
                    
                    if ($remember) {
                        setcookie('remember_token', $result['user']['id'], time() + (30 * 24 * 60 * 60), '/');
                    }
                    
                    $this->redirectWithMessage('/home', 'Đăng nhập thành công! Chào mừng bạn trở lại!');
                } else {
                    NotificationHelper::error($result['message']);
                }
            } else {
                NotificationHelper::error('Vui lòng nhập đầy đủ email và mật khẩu');
            }
        }
        
        $this->renderPage('login', 'Đăng nhập - CodeJudge', 'Đăng nhập vào tài khoản CodeJudge của bạn');
    }
    
    public function register()
    {
        $this->redirectIfLoggedIn();
        
        if ($this->isPostRequest()) {
            $postData = $this->getPostData([
                'firstName', 'lastName', 'username', 'email', 'password', 'confirmPassword'
            ]);
            $terms = isset($_POST['terms']);
            $newsletter = isset($_POST['newsletter']);
            
            if (!$terms) {
                NotificationHelper::error('Bạn phải đồng ý với điều khoản dịch vụ');
            } 
            elseif ($postData['password'] !== $postData['confirmPassword']) {
                NotificationHelper::error('Mật khẩu xác nhận không khớp');
            } 
            else {
                $userData = [
                    'firstName' => $postData['firstName'],
                    'lastName' => $postData['lastName'],
                    'username' => $postData['username'],
                    'email' => $postData['email'],
                    'password' => $postData['password']
                ];
                
                $userModel = new UserModel();
                $result = $userModel->createUser($userData);
                
                if ($result['success']) {
                    $this->redirectWithMessage(SITE_URL . '/login', 
                        $result['message'] . ' Bạn có thể đăng nhập ngay bây giờ.');
                } else {
                    NotificationHelper::error($result['message']);
                }
            }
        }
        
        $this->renderPage('signup', 'Đăng ký - CodeJudge', 'Tạo tài khoản CodeJudge của bạn');
    }
    
    public function forgotPassword()
    {
        $this->redirectIfLoggedIn();
        
        if ($this->isPostRequest()) {
            $email = $this->getInput('email');
            
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
        
        $this->renderPage('fogotPassword', 'Quên mật khẩu - CodeJudge', 'Khôi phục mật khẩu tài khoản CodeJudge');
    }
    
    public function logout($redirectUrl = '/welcome', $successMessage = 'Đăng xuất thành công')
    {
        parent::logout($redirectUrl, $successMessage);
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
        
        $this->renderPage('problem_detail', $title, $description, [
            'problem' => $problem,
            'userSubmissions' => $userSubmissions,
            'exampleSubmissions' => $exampleSubmissions
        ]);
    }
    
    public function show404()
    {
        if (isset($_SESSION['notification'])) {
            unset($_SESSION['notification']);
        }
        
        http_response_code(404);
        $this->renderPage('404', '404 - Không Tìm Thấy Trang');
    }
    
    public function privacy()
    {
        include VIEW_PATH . '/privacy.php';
    }
    
    public function terms()
    {
        include VIEW_PATH . '/terms.php';
    }
    
    public function cookies()
    {
        include VIEW_PATH . '/cookies.php';
    }
    
    public function apiReference()
    {
        include VIEW_PATH . '/api-reference.php';
    }
    
    public function contact()
    {
        include VIEW_PATH . '/contact.php';
    }
    
    public function languages()
    {
        include VIEW_PATH . '/languages.php';
    }

    public function leaderboard()
    {
        include VIEW_PATH . '/leaderboard.php';
    }
    
    public function discussions()
    {
        include VIEW_PATH . '/discussions.php';
    }
}
?>
