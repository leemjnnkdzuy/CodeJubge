<?php

class UserModel
{

    private $db;
    private $table = 'users';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getDb()
    {
        return $this->db;
    }

    public function createUser($userData)
    {
        try {
            $validation = $this->validateUserData($userData);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'message' => $validation['message']
                ];
            }
            
            if ($this->isEmailExists($userData['email'])) {
                return [
                    'success' => false,
                    'message' => 'Email đã được sử dụng'
                ];
            }
            
            if ($this->isUsernameExists($userData['username'])) {
                return [
                    'success' => false,
                    'message' => 'Username đã được sử dụng'
                ];
            }
            
            $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
            
            require_once APP_PATH . '/helpers/AvatarHelper.php';
            $defaultAvatar = AvatarHelper::getDefaultAvatarBase64();
            
            $insertData = [
                'first_name' => $userData['firstName'],
                'last_name' => $userData['lastName'],
                'username' => $userData['username'],
                'email' => $userData['email'],
                'password' => $hashedPassword,
                'avatar' => $defaultAvatar,
                'badges' => '[]',
                'role' => 'user',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $query = "INSERT INTO {$this->table} (first_name, last_name, username, email, password, avatar, badges, role, is_active, created_at, updated_at) 
                     VALUES (:first_name, :last_name, :username, :email, :password, :avatar, :badges, :role, :is_active, :created_at, :updated_at)";
            
            $userId = $this->db->insert($query, $insertData);
            
            if ($userId) {
                return [
                    'success' => true,
                    'message' => 'Tài khoản được tạo thành công',
                    'user_id' => $userId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi tạo tài khoản'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    public function loginUser($email, $password)
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE email = :email AND is_active = 1";
            $user = $this->db->selectOne($query, ['email' => $email]);
            
            if ($user && password_verify($password, $user['password'])) {
                $this->updateLastLogin($user['id']);
                $currentStreak = $this->updateLoginStreak($user['id']);
                
                $userData = $this->sanitizeUserData($user);
                $userData['login_streak'] = $currentStreak;
                
                return [
                    'success' => true,
                    'message' => 'Đăng nhập thành công',
                    'user' => $userData
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Email hoặc mật khẩu không đúng'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    public function getUserById($userId): ?array
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id AND is_active = 1";
            $user = $this->db->selectOne($query, ['id' => $userId]);
            
            if ($user) {
                return $this->sanitizeUserData($user);
            }
            
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function updateUser($userId, $userData): array
    {
        try {
            $allowedFields = ['first_name', 'last_name', 'bio', 'avatar', 'github_url', 'linkedin_url', 'website_url', 'youtube_url', 'facebook_url', 'instagram_url'];
            $updateFields = [];
            $params = ['id' => $userId];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $userData)) {
                    $updateFields[] = "{$field} = :{$field}";
                    $params[$field] = $userData[$field];
                }
            }
            
            if (empty($updateFields)) {
                return [
                    'success' => false,
                    'message' => 'Không có dữ liệu để cập nhật'
                ];
            }
            
            // Username và Email không cho phép thay đổi - removed validation
            
            $updateFields[] = "updated_at = :updated_at";
            $params['updated_at'] = date('Y-m-d H:i:s');
            
            $query = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE id = :id";
            
            $affected = $this->db->update($query, $params);
            
            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => 'Cập nhật thông tin thành công'
                ];
            } else {
                // Check if user exists to distinguish between no changes and actual error
                $userExists = $this->getUserById($userId);
                if (!$userExists) {
                    return [
                        'success' => false,
                        'message' => 'Người dùng không tồn tại'
                    ];
                }
                
                return [
                    'success' => true,
                    'message' => 'Không có thông tin nào được thay đổi'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword): array
    {
        try {
            $user = $this->getUserById($userId);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Người dùng không tồn tại'
                ];
            }
            
            $query = "SELECT password FROM {$this->table} WHERE id = :id";
            $currentUser = $this->db->selectOne($query, ['id' => $userId]);
            
            if (!password_verify($currentPassword, $currentUser['password'])) {
                return [
                    'success' => false,
                    'message' => 'Mật khẩu hiện tại không đúng'
                ];
            }
            
            if (strlen($newPassword) < 8) {
                return [
                    'success' => false,
                    'message' => 'Mật khẩu mới phải có ít nhất 8 ký tự'
                ];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = "UPDATE {$this->table} SET password = :password, updated_at = :updated_at WHERE id = :id";
            
            $affected = $this->db->update($query, [
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $userId
            ]);
            
            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => 'Đổi mật khẩu thành công'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi đổi mật khẩu'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }
    
    public function resetPassword($email, $newPassword = null)
    {
        try {
            $user = $this->getUserByEmail($email);
            if (!$user) {
                return [
                    'success' => false,
                    'message' => 'Email không tồn tại'
                ];
            }
            
            if (!$newPassword) {
                $newPassword = $this->generateRandomPassword();
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = "UPDATE {$this->table} SET password = :password, updated_at = :updated_at WHERE email = :email";
            
            $affected = $this->db->update($query, [
                'password' => $hashedPassword,
                'updated_at' => date('Y-m-d H:i:s'),
                'email' => $email
            ]);
            
            if ($affected > 0) {
                return [
                    'success' => true,
                    'message' => 'Reset mật khẩu thành công',
                    'new_password' => $newPassword
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Có lỗi xảy ra khi reset mật khẩu'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ];
        }
    }

    public function getUserByEmail($email)
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE email = :email AND is_active = 1";
            $user = $this->db->selectOne($query, ['email' => $email]);
            
            if ($user) {
                return $this->sanitizeUserData($user);
            }
            
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function getAllUsers($limit = 50, $offset = 0)
    {
        try {
            $query = "SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
            $users = $this->db->select($query, [
                'limit' => $limit,
                'offset' => $offset
            ]);
            
            $sanitizedUsers = [];
            foreach ($users as $user) {
                $sanitizedUsers[] = $this->sanitizeUserData($user);
            }
            
            return $sanitizedUsers;
            
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function getTotalUsers()
    {
        try {
            $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE is_active = 1";
            $result = $this->db->selectOne($query);
            return $result['total'] ?? 0;
            
        } catch (Exception $e) {
            return 0;
        }
    }

    private function isEmailExists($email, $excludeUserId = null)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";
        $params = ['email' => $email];
        
        if ($excludeUserId) {
            $query .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeUserId;
        }
        
        $result = $this->db->selectOne($query, $params);
        return $result['count'] > 0;
    }

    private function isUsernameExists($username, $excludeUserId = null)
    {
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = :username";
        $params = ['username' => $username];
        
        if ($excludeUserId) {
            $query .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeUserId;
        }
        
        $result = $this->db->selectOne($query, $params);
        return $result['count'] > 0;
    }
    
    private function validateUserData($userData)
    {
        $required = ['firstName', 'lastName', 'username', 'email', 'password'];
        
        foreach ($required as $field) {
            if (!isset($userData[$field]) || empty(trim($userData[$field]))) {
                return [
                    'valid' => false,
                    'message' => ucfirst($field) . ' là bắt buộc'
                ];
            }
        }
        
        if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            return [
                'valid' => false,
                'message' => 'Email không hợp lệ'
            ];
        }
        
        if (strlen($userData['password']) < 8) {
            return [
                'valid' => false,
                'message' => 'Mật khẩu phải có ít nhất 8 ký tự'
            ];
        }
        
        if (strlen($userData['username']) < 3) {
            return [
                'valid' => false,
                'message' => 'Username phải có ít nhất 3 ký tự'
            ];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $userData['username'])) {
            return [
                'valid' => false,
                'message' => 'Username chỉ được chứa chữ cái, số và dấu gạch dưới'
            ];
        }
        
        return ['valid' => true];
    }

    private function sanitizeUserData($user)
    {
        unset($user['password']);
        return $user;
    }

    private function updateLastLogin($userId)
    {
        try {
            $query = "UPDATE {$this->table} SET last_login = :last_login WHERE id = :id";
            $this->db->update($query, [
                'last_login' => date('Y-m-d H:i:s'),
                'id' => $userId
            ]);
        } catch (Exception $e) {
        }
    }
    
    private function generateRandomPassword($length = 12)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        return substr(str_shuffle($chars), 0, $length);
    }
    
    public function getUserBadges($userId): array
    {
        try {
            $query = "SELECT badges FROM {$this->table} WHERE id = :id";
            $result = $this->db->selectOne($query, ['id' => $userId]);
            
            if ($result && $result['badges']) {
                return json_decode($result['badges'], true) ?: [];
            }
            return [];
        } catch (Exception $e) {
            return [];
        }
    }
    
    public function addBadgeToUser($userId, $badgeKey): bool
    {
        try {
            $currentBadges = $this->getUserBadges($userId);
            
            if (!in_array($badgeKey, $currentBadges)) {
                $currentBadges[] = $badgeKey;
                $badgesJson = json_encode($currentBadges);
                
                $query = "UPDATE {$this->table} SET badges = :badges, updated_at = :updated_at WHERE id = :id";
                $this->db->update($query, [
                    'badges' => $badgesJson,
                    'updated_at' => date('Y-m-d H:i:s'),
                    'id' => $userId
                ]);
                
                return true;
            }
            return false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    public function updateLoginStreak($userId)
    {
        try {
            $query = "SELECT login_streak, last_login_date FROM {$this->table} WHERE id = :id";
            $user = $this->db->selectOne($query, ['id' => $userId]);
            
            $today = date('Y-m-d');
            $currentStreak = $user['login_streak'] ?? 0;
            $lastLoginDate = $user['last_login_date'];
            
            if ($lastLoginDate && $lastLoginDate === $today) {
                return $currentStreak;
            }
            
            $yesterday = date('Y-m-d', strtotime('-1 day'));
            
            if ($lastLoginDate === $yesterday) {
                $currentStreak++;
            } else {
                $currentStreak = 1;
            }
            
            $updateQuery = "UPDATE {$this->table} SET 
                          login_streak = :streak, 
                          last_login_date = :date, 
                          last_login = :timestamp,
                          updated_at = :updated_at 
                          WHERE id = :id";
            
            $this->db->update($updateQuery, [
                'streak' => $currentStreak,
                'date' => $today,
                'timestamp' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'id' => $userId
            ]);
            
            $this->checkStreakBadges($userId, $currentStreak);
            
            return $currentStreak;
        } catch (Exception $e) {
            return 0;
        }
    }
    
    private function checkStreakBadges($userId, $streak)
    {
        $badgesToCheck = [
            7 => '7_Day_Login_Streak',
            30 => '30_Day_Login_Streak',
            100 => '100_Day_Login_Streak',
            365 => 'Year_Long_Login_Streak'
        ];
        
        foreach ($badgesToCheck as $requiredStreak => $badgeKey) {
            if ($streak >= $requiredStreak) {
                $this->addBadgeToUser($userId, $badgeKey);
            }
        }
    }
    
    private function isEmailExistsExcludingUser($email, $excludeUserId)
    {
        try {
            $query = "SELECT id FROM {$this->table} WHERE email = :email AND id != :id";
            $result = $this->db->selectOne($query, [
                'email' => $email,
                'id' => $excludeUserId
            ]);
            
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }
    
    private function isUsernameExistsExcludingUser($username, $excludeUserId)
    {
        try {
            $query = "SELECT id FROM {$this->table} WHERE username = :username AND id != :id";
            $result = $this->db->selectOne($query, [
                'username' => $username,
                'id' => $excludeUserId
            ]);
            
            return $result !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getLeaderboard($limit = 20, $offset = 0, $rankFilter = 'all') {
        require_once APP_PATH . '/helpers/LeaderboardHelper.php';
        return LeaderboardHelper::getLeaderboardData($this->db, $limit, $offset, $rankFilter);
    }

    public function getTotalActiveUsers($rankFilter = 'all') {
        require_once APP_PATH . '/helpers/LeaderboardHelper.php';
        return LeaderboardHelper::getTotalFilteredUsers($this->db, $rankFilter);
    }
    
    private function getRankTierFromRating($userRating) {
        global $RANKING;
        
        if ($userRating == -1) {
            return 'Unranked';
        }
        
        foreach ($RANKING as $key => $rank) {
            if ($userRating >= $rank['min_rating'] && $userRating <= $rank['max_rating']) {
                return $key;
            }
        }
        
        return 'Unranked';
    }
    
    public function getRankTierStats() {
        try {
            global $RANKING;
            
            $cases = [];
            $orderCases = [];
            $i = 1;
            
            foreach ($RANKING as $key => $rank) {
                if ($key === 'Unranked') {
                    $cases[] = "WHEN rating = -1 THEN '{$key}'";
                } else if ($rank['max_rating'] >= 999999) {
                    $cases[] = "WHEN rating >= {$rank['min_rating']} THEN '{$key}'";
                } else {
                    $cases[] = "WHEN rating BETWEEN {$rank['min_rating']} AND {$rank['max_rating']} THEN '{$key}'";
                }
                $orderCases[] = "WHEN '{$key}' THEN {$i}";
                $i++;
            }
            
            $casesSql = implode(' ', $cases);
            $orderCasesSql = implode(' ', $orderCases);
            
            $query = "
                SELECT 
                    CASE 
                        {$casesSql}
                        ELSE 'Unranked'
                    END as tier,
                    COUNT(*) as count
                FROM {$this->table}
                WHERE is_active = 1 AND role = 'user'
                GROUP BY tier
                ORDER BY 
                    CASE tier
                        {$orderCasesSql}
                        ELSE 999
                    END
            ";
            
            return $this->db->select($query);
            
        } catch (Exception $e) {
            error_log("Error getting rank tier stats: " . $e->getMessage());
            return [];
        }
    }
    
    public function getUserByUsername($username)
    {
        try {
            $query = "SELECT * FROM {$this->table} WHERE username = :username";
            $user = $this->db->selectOne($query, ['username' => $username]);
            
            if ($user) {
                return $this->sanitizeUserData($user);
            }
            
            return null;
            
        } catch (Exception $e) {
            return null;
        }
    }
    
    public function deleteUser($userId)
    {
        try {
            // First check if user exists
            $user = $this->getUserById($userId);
            if (!$user) {
                return false;
            }
            
            // Prevent deleting admin users
            if ($user['role'] === 'admin') {
                return false;
            }
            
            // Delete user
            $query = "DELETE FROM {$this->table} WHERE id = :id";
            return $this->db->delete($query, ['id' => $userId]);
            
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateUserAdmin($userId, $userData)
    {
        try {
            $allowedFields = ['first_name', 'last_name', 'username', 'email', 'role', 'is_active'];
            $updateFields = [];
            $params = ['id' => $userId];
            
            foreach ($allowedFields as $field) {
                if (array_key_exists($field, $userData)) {
                    $updateFields[] = "{$field} = :{$field}";
                    $params[$field] = $userData[$field];
                }
            }
            
            if (empty($updateFields)) {
                return false;
            }
            
            $updateFields[] = "updated_at = :updated_at";
            $params['updated_at'] = date('Y-m-d H:i:s');
            
            $query = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE id = :id";
            
            return $this->db->update($query, $params) > 0;
            
        } catch (Exception $e) {
            error_log("Error updating user admin: " . $e->getMessage());
            return false;
        }
    }
}

?>