<?php
require_once CORE_PATH . '/Controller.php';
require_once MODEL_PATH . '/UserModel.php';
require_once APP_PATH . '/helpers/AvatarHelper.php';
require_once APP_PATH . '/helpers/NotificationHelper.php';
require_once APP_PATH . '/helpers/RatingHelper.php';

class ProfileController extends Controller
{
    private $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            NotificationHelper::error('Bạn cần đăng nhập để xem trang cá nhân');
            header('Location: /login');
            exit;
        }
        
        $currentUser = $this->userModel->getUserById($_SESSION['user_id']);
        $userBadges = $this->userModel->getUserBadges($_SESSION['user_id']);
        
        if (!$currentUser) {
            NotificationHelper::error('Không tìm thấy thông tin người dùng');
            header('Location: /login');
            exit;
        }
        
        if (!$userBadges) {
            $userBadges = [];
        }
        
        $title = 'Trang cá nhân - ' . $currentUser['first_name'] . ' ' . $currentUser['last_name'];
        $description = 'Trang cá nhân của ' . $currentUser['username'] . ' trên CodeJudge';
        
        include VIEW_PATH . '/profile.php';
    }
    

    public function show($userId)
    {
        if (!$userId || !is_numeric($userId)) {
            NotificationHelper::error('ID người dùng không hợp lệ');
            header('Location: /home');
            exit;
        }
        
        $profileUser = $this->userModel->getUserById($userId);
        $userBadges = $this->userModel->getUserBadges($userId);
        
        if (!$profileUser) {
            NotificationHelper::error('Không tìm thấy người dùng');
            header('Location: /home');
            exit;
        }
        
        if (!$userBadges) {
            $userBadges = [];
        }
        
        $isOwnProfile = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $userId;
        
        $currentUser = null;
        if (isset($_SESSION['user_id'])) {
            $currentUser = $this->userModel->getUserById($_SESSION['user_id']);
        }
        
        $title = 'Trang cá nhân - ' . $profileUser['first_name'] . ' ' . $profileUser['last_name'];
        $description = 'Trang cá nhân của ' . $profileUser['username'] . ' trên CodeJudge';
        
        include VIEW_PATH . '/profile.php';
    }
    
    public function update()
    {
        error_log("Profile Update - Session: " . print_r($_SESSION, true));
        
        if (!isset($_SESSION['user_id'])) {
            NotificationHelper::error('Bạn cần đăng nhập');
            header('Location: /login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            NotificationHelper::error('Method không hợp lệ');
            header('Location: /profile');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $updateData = [];
        
        error_log("Profile Update - POST data: " . print_r($_POST, true));
        error_log("Profile Update - FILES data: " . print_r($_FILES, true));
        
        if (empty($_POST) && empty($_FILES)) {
            NotificationHelper::error('Không có dữ liệu để cập nhật');
            header('Location: /profile');
            exit;
        }
        
        if (isset($_POST['first_name'])) {
            $firstName = trim($_POST['first_name']);
            if (empty($firstName)) {
                NotificationHelper::error('Tên không được để trống');
                header('Location: /profile');
                exit;
            }
            $updateData['first_name'] = $firstName;
        }
        
        if (isset($_POST['last_name'])) {
            $lastName = trim($_POST['last_name']);
            if (empty($lastName)) {
                NotificationHelper::error('Họ không được để trống');
                header('Location: /profile');
                exit;
            }
            $updateData['last_name'] = $lastName;
        }
        
        if (isset($_POST['username'])) {
            $username = trim($_POST['username']);
            if (empty($username)) {
                NotificationHelper::error('Username không được để trống');
                header('Location: /profile');
                exit;
            }
            if ($this->isUsernameExistsExcludingUser($username, $userId)) {
                NotificationHelper::error('Username đã được sử dụng');
                header('Location: /profile');
                exit;
            }
            $updateData['username'] = $username;
        }
        
        if (isset($_POST['email'])) {
            $email = trim($_POST['email']);
            if (empty($email)) {
                NotificationHelper::error('Email không được để trống');
                header('Location: /profile');
                exit;
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                NotificationHelper::error('Email không hợp lệ');
                header('Location: /profile');
                exit;
            }
            if ($this->isEmailExistsExcludingUser($email, $userId)) {
                NotificationHelper::error('Email đã được sử dụng');
                header('Location: /profile');
                exit;
            }
            $updateData['email'] = $email;
        }
        
        if (isset($_POST['bio'])) {
            $updateData['bio'] = trim($_POST['bio']);
        }
        
        if (isset($_POST['github_url'])) {
            $githubUrl = trim($_POST['github_url']);
            if (!empty($githubUrl) && !filter_var($githubUrl, FILTER_VALIDATE_URL)) {
                NotificationHelper::error('GitHub URL không hợp lệ');
                header('Location: /profile');
                exit;
            }
            $updateData['github_url'] = $githubUrl;
        }
        
        if (isset($_POST['linkedin_url'])) {
            $linkedinUrl = trim($_POST['linkedin_url']);
            if (!empty($linkedinUrl) && !filter_var($linkedinUrl, FILTER_VALIDATE_URL)) {
                NotificationHelper::error('LinkedIn URL không hợp lệ');
                header('Location: /profile');
                exit;
            }
            $updateData['linkedin_url'] = $linkedinUrl;
        }
        
        if (isset($_POST['website_url'])) {
            $websiteUrl = trim($_POST['website_url']);
            if (!empty($websiteUrl) && !filter_var($websiteUrl, FILTER_VALIDATE_URL)) {
                NotificationHelper::error('Website URL không hợp lệ');
                header('Location: /profile');
                exit;
            }
            $updateData['website_url'] = $websiteUrl;
        }
        
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            try {
                $file = $_FILES['avatar'];
                
                $maxSize = 2048000;
                if ($file['size'] > $maxSize) {
                    throw new Exception('File ảnh quá lớn. Vui lòng chọn file nhỏ hơn 2MB.');
                }
                
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileInfo = getimagesize($file['tmp_name']);
                
                if (!$fileInfo || !in_array($fileInfo['mime'], $allowedTypes)) {
                    throw new Exception('Định dạng file không được hỗ trợ. Chỉ chấp nhận JPG, PNG, GIF, WebP.');
                }
                
                if ($fileInfo === false) {
                    throw new Exception('File không phải là ảnh hợp lệ.');
                }
                
                $avatarBase64 = AvatarHelper::processUploadedImage($_FILES['avatar']);
                $updateData['avatar'] = $avatarBase64;
                
                NotificationHelper::success('Upload ảnh đại diện thành công!');
                
            } catch (Exception $e) {
                NotificationHelper::error('Lỗi upload avatar: ' . $e->getMessage());
                header('Location: /profile');
                exit;
            }
        } elseif (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File quá lớn (vượt quá giới hạn server).',
                UPLOAD_ERR_FORM_SIZE => 'File quá lớn (vượt quá giới hạn form).',
                UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần.',
                UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm để upload.',
                UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file lên server.',
                UPLOAD_ERR_EXTENSION => 'Upload bị dừng bởi extension.'
            ];
            
            $errorMessage = $errorMessages[$_FILES['avatar']['error']] ?? 'Lỗi không xác định khi upload file.';
            NotificationHelper::error('Lỗi upload: ' . $errorMessage);
            header('Location: /profile');
            exit;
        }
        
        if (!empty($updateData)) {
            error_log("Profile Update - Update data: " . print_r($updateData, true));
            
            $result = $this->userModel->updateUser($userId, $updateData);
            
            error_log(message: "Profile Update - Result: " . print_r($result, true));
            
            if ($result['success']) {
                if (isset($updateData['first_name']) || isset($updateData['last_name']) || isset($updateData['username']) || isset($updateData['email'])) {
                    $_SESSION['user'] = $this->userModel->getUserById($userId);
                }
                
                if (isset($updateData['avatar'])) {
                    NotificationHelper::success('Cập nhật ảnh đại diện thành công!');
                } else {
                    NotificationHelper::success('Cập nhật thông tin cá nhân thành công!');
                }
            } else {
                NotificationHelper::error($result['message']);
            }
        } else {
            NotificationHelper::warning('Không có thông tin nào được thay đổi.');
        }
        
        header('Location: /profile');
        exit;
    }
    
    private function isUsernameExistsExcludingUser($username, $excludeUserId)
    {
        try {
            $query = "SELECT id FROM users WHERE username = :username AND id != :id";
            $result = $this->userModel->getDb()->selectOne($query, [
                'username' => $username,
                'id' => $excludeUserId
            ]);
            return $result !== null;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function isEmailExistsExcludingUser($email, $excludeUserId)
    {
        try {
            $query = "SELECT id FROM users WHERE email = :email AND id != :id";
            $result = $this->userModel->getDb()->selectOne($query, [
                'email' => $email,
                'id' => $excludeUserId
            ]);
            return $result !== null;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
