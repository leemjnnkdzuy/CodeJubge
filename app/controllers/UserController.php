<?php

class UserController extends Controller
{
    private $userModel;
    
    public function __construct()
    {
        $this->userModel = new UserModel();
    }
    
    public function profile()
    {
        $this->requireAuth();
        
        $userId = $_SESSION['user_id'];
        $user = $this->userModel->getUserById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy thông tin người dùng';
            $this->redirect('home');
        }
        
        $stats = $this->getUserStats($userId);
        
        $title = 'Hồ sơ - ' . $user['username'];
        $description = 'Hồ sơ cá nhân của ' . $user['first_name'] . ' ' . $user['last_name'];
        
        $this->view('profile', [
            'title' => $title,
            'description' => $description,
            'user' => $user,
            'stats' => $stats
        ]);
    }

    public function updateProfile()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            
            $userData = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'bio' => $_POST['bio'] ?? '',
                'github_url' => $_POST['github_url'] ?? '',
                'linkedin_url' => $_POST['linkedin_url'] ?? '',
                'website_url' => $_POST['website_url'] ?? ''
            ];
            
            $result = $this->userModel->updateUser($userId, $userData);
            
            if ($result['success']) {
                $_SESSION['user'] = $this->userModel->getUserById($userId);
                $_SESSION['success'] = $result['message'];
            } else {
                $_SESSION['error'] = $result['message'];
            }
        }
        
        $this->redirect('profile');
    }
    
    public function changePassword()
    {
        $this->requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
            } elseif ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Mật khẩu mới và xác nhận không khớp';
            } else {
                $result = $this->userModel->changePassword($userId, $currentPassword, $newPassword);
                
                if ($result['success']) {
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            }
        }
        
        $this->redirect('profile');
    }
    
    public function leaderboard()
    {
        try {
            $db = Database::getInstance();
            
            $query = "SELECT * FROM leaderboard_view LIMIT 100";
            $users = $db->select($query);
            
            $title = 'Bảng xếp hạng - CodeJudge';
            $description = 'Bảng xếp hạng các lập trình viên hàng đầu';
            
            $this->view('leaderboard', [
                'title' => $title,
                'description' => $description,
                'users' => $users
            ]);
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi khi tải bảng xếp hạng';
            $this->redirect('home');
        }
    }
    
    public function viewProfile($username)
    {
        try {
            $db = Database::getInstance();
            
            $query = "SELECT * FROM users WHERE username = :username AND is_active = 1";
            $user = $db->selectOne($query, ['username' => $username]);
            
            if (!$user) {
                $_SESSION['error'] = 'Không tìm thấy người dùng';
                $this->redirect('leaderboard');
                return;
            }
            
            unset($user['password']);
            
            $stats = $this->getUserStats($user['id']);
            
            $recentSubmissions = $this->getRecentSubmissions($user['id'], 10);
            
            $title = 'Hồ sơ - ' . $user['username'];
            $description = 'Hồ sơ của ' . $user['first_name'] . ' ' . $user['last_name'];
            
            $this->view('user_profile', [
                'title' => $title,
                'description' => $description,
                'user' => $user,
                'stats' => $stats,
                'recent_submissions' => $recentSubmissions
            ]);
            
        } catch (Exception $e) {
            $_SESSION['error'] = 'Có lỗi khi tải hồ sơ';
            $this->redirect('leaderboard');
        }
    }

    private function getUserStats($userId)
    {
        try {
            $db = Database::getInstance();
            
            $generalStats = $db->selectOne(
                "SELECT 
                    total_problems_solved,
                    total_submissions,
                    rating,
                    DATE(created_at) as join_date
                FROM users WHERE id = :user_id",
                ['user_id' => $userId]
            );
            
            $difficultyStats = $db->select(
                "SELECT 
                    p.difficulty,
                    COUNT(*) as solved_count
                FROM user_problems up
                JOIN problems p ON up.problem_id = p.id
                WHERE up.user_id = :user_id AND up.status = 'solved'
                GROUP BY p.difficulty",
                ['user_id' => $userId]
            );
            
            $submissionStats = $db->select(
                "SELECT 
                    status,
                    COUNT(*) as count
                FROM submissions 
                WHERE user_id = :user_id 
                GROUP BY status",
                ['user_id' => $userId]
            );
            
            $rank = $db->selectOne(
                "SELECT rank_position FROM leaderboard_view WHERE id = :user_id",
                ['user_id' => $userId]
            );
            
            return [
                'general' => $generalStats,
                'difficulty' => $difficultyStats,
                'submissions' => $submissionStats,
                'rank' => $rank['rank_position'] ?? 'N/A'
            ];
            
        } catch (Exception $e) {
            return [
                'general' => [],
                'difficulty' => [],
                'submissions' => [],
                'rank' => 'N/A'
            ];
        }
    }
    
    private function getRecentSubmissions($userId, $limit = 10)
    {
        try {
            $db = Database::getInstance();
            
            $query = "SELECT 
                        s.id,
                        s.language,
                        s.status,
                        s.runtime,
                        s.memory_used,
                        s.score,
                        s.submitted_at,
                        p.title as problem_title,
                        p.slug as problem_slug,
                        p.difficulty
                    FROM submissions s
                    JOIN problems p ON s.problem_id = p.id
                    WHERE s.user_id = :user_id
                    ORDER BY s.submitted_at DESC
                    LIMIT :limit";
            
            return $db->select($query, [
                'user_id' => $userId,
                'limit' => $limit
            ]);
            
        } catch (Exception $e) {
            return [];
        }
    }
}
?>
