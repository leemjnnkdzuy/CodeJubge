<?php

class RatingHelper 
{
    /**
     * Cộng điểm rating cho user khi giải được một test case
     * Mỗi test case được giải thành công sẽ cộng 1 điểm rating
     * 
     * @param int $userId ID của user
     * @param int $problemId ID của bài toán
     * @param int $testCasesPassed Số test case đã pass
     * @return bool True nếu cập nhật thành công
     */
    public static function updateUserRating($userId, $problemId, $testCasesPassed)
    {
        $pdo = getConnection();
        
        try {
            $pdo->beginTransaction();
            
            // Kiểm tra xem user đã giải bài này chưa
            $stmt = $pdo->prepare("
                SELECT id, test_cases_passed 
                FROM submissions 
                WHERE user_id = ? AND problem_id = ? AND status = 'Accepted'
                ORDER BY created_at DESC 
                LIMIT 1
            ");
            $stmt->execute([$userId, $problemId]);
            $previousSubmission = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Tính điểm rating mới
            $ratingToAdd = 0;
            
            if (!$previousSubmission) {
                // Lần đầu giải bài này - cộng toàn bộ test case đã pass
                $ratingToAdd = $testCasesPassed;
            } else {
                // Đã giải trước đó - chỉ cộng test case mới pass hơn lần trước
                $previousTestCases = $previousSubmission['test_cases_passed'];
                if ($testCasesPassed > $previousTestCases) {
                    $ratingToAdd = $testCasesPassed - $previousTestCases;
                }
            }
            
            if ($ratingToAdd > 0) {
                // Cập nhật rating cho user
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET rating = CASE 
                        WHEN rating = -1 THEN ?
                        ELSE rating + ?
                    END
                    WHERE id = ?
                ");
                $stmt->execute([$ratingToAdd, $ratingToAdd, $userId]);
                
                // Log rating change
                self::logRatingChange($userId, $problemId, $ratingToAdd, 'test_case_passed');
            }
            
            $pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error updating user rating: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy rating hiện tại của user
     * 
     * @param int $userId ID của user
     * @return int Rating hiện tại
     */
    public static function getUserRating($userId)
    {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("SELECT rating FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (int)$result['rating'] : -1;
    }
    
    /**
     * Lấy thông tin rank của user dựa trên rating
     * 
     * @param int $rating Rating của user
     * @return array|null Thông tin rank
     */
    public static function getRankByRating($rating)
    {
        global $RANKING;
        
        if ($rating === -1) {
            return $RANKING['Unranked'];
        }
        
        foreach ($RANKING as $rankKey => $rankInfo) {
            if ($rating >= $rankInfo['min_rating'] && $rating <= $rankInfo['max_rating']) {
                return $rankInfo;
            }
        }
        
        // Nếu rating cao hơn tất cả rank, trả về rank cao nhất
        $highestRank = null;
        $highestOrder = -1;
        
        foreach ($RANKING as $rankKey => $rankInfo) {
            if ($rankInfo['order'] > $highestOrder) {
                $highestOrder = $rankInfo['order'];
                $highestRank = $rankInfo;
            }
        }
        
        return $highestRank;
    }
    
    /**
     * Lấy thông tin rank của user
     * 
     * @param int $userId ID của user
     * @return array|null Thông tin rank
     */
    public static function getUserRank($userId)
    {
        $rating = self::getUserRating($userId);
        return self::getRankByRating($rating);
    }
    
    /**
     * Tính toán rating cần thiết để lên rank tiếp theo
     * 
     * @param int $currentRating Rating hiện tại
     * @return array Thông tin về rank tiếp theo
     */
    public static function getNextRankInfo($currentRating)
    {
        global $RANKING;
        
        $currentRank = self::getRankByRating($currentRating);
        $nextRank = null;
        $ratingNeeded = 0;
        
        foreach ($RANKING as $rankKey => $rankInfo) {
            if ($rankInfo['order'] == $currentRank['order'] + 1) {
                $nextRank = $rankInfo;
                $ratingNeeded = $rankInfo['min_rating'] - $currentRating;
                break;
            }
        }
        
        return [
            'current_rank' => $currentRank,
            'next_rank' => $nextRank,
            'rating_needed' => max(0, $ratingNeeded),
            'progress_percent' => $nextRank ? 
                min(100, (($currentRating - $currentRank['min_rating']) / ($nextRank['min_rating'] - $currentRank['min_rating'])) * 100) : 100
        ];
    }
    
    /**
     * Lấy top users theo rating
     * 
     * @param int $limit Số lượng user cần lấy
     * @return array Danh sách users
     */
    public static function getTopRatedUsers($limit = 10)
    {
        $pdo = getConnection();
        
        $stmt = $pdo->prepare("
            SELECT 
                u.id,
                u.username,
                u.full_name,
                u.avatar,
                u.rating,
                u.total_problems_solved,
                RANK() OVER (ORDER BY u.rating DESC, u.total_problems_solved DESC) as rank_position
            FROM users u
            WHERE u.rating >= 0
            ORDER BY u.rating DESC, u.total_problems_solved DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Thêm thông tin rank cho mỗi user
        foreach ($users as &$user) {
            $user['rank_info'] = self::getRankByRating($user['rating']);
        }
        
        return $users;
    }
    
    /**
     * Log rating change để theo dõi lịch sử
     * 
     * @param int $userId ID của user
     * @param int $problemId ID của bài toán
     * @param int $ratingChange Số điểm thay đổi
     * @param string $reason Lý do thay đổi
     */
    private static function logRatingChange($userId, $problemId, $ratingChange, $reason)
    {
        $pdo = getConnection();
        
        try {
            // Tạo bảng rating_history nếu chưa có
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS rating_history (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    problem_id INT NULL,
                    rating_change INT NOT NULL,
                    old_rating INT NOT NULL,
                    new_rating INT NOT NULL,
                    reason VARCHAR(50) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id (user_id),
                    INDEX idx_created_at (created_at),
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                    FOREIGN KEY (problem_id) REFERENCES problems(id) ON DELETE SET NULL
                )
            ");
            
            // Lấy rating cũ và mới
            $oldRating = self::getUserRating($userId) - $ratingChange;
            $newRating = self::getUserRating($userId);
            
            // Insert log
            $stmt = $pdo->prepare("
                INSERT INTO rating_history (user_id, problem_id, rating_change, old_rating, new_rating, reason)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$userId, $problemId, $ratingChange, $oldRating, $newRating, $reason]);
            
        } catch (Exception $e) {
            error_log("Error logging rating change: " . $e->getMessage());
        }
    }
    
    /**
     * Lấy lịch sử rating của user
     * 
     * @param int $userId ID của user
     * @param int $limit Số lượng record cần lấy
     * @return array Lịch sử rating
     */
    public static function getUserRatingHistory($userId, $limit = 20)
    {
        $pdo = getConnection();
        
        try {
            $stmt = $pdo->prepare("
                SELECT 
                    rh.*,
                    p.title as problem_title,
                    p.slug as problem_slug
                FROM rating_history rh
                LEFT JOIN problems p ON rh.problem_id = p.id
                WHERE rh.user_id = ?
                ORDER BY rh.created_at DESC
                LIMIT ?
            ");
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            error_log("Error getting user rating history: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Tính toán lại rating cho tất cả users
     * Dùng khi cần recalculate toàn bộ hệ thống
     */
    public static function recalculateAllRatings()
    {
        $pdo = getConnection();
        
        try {
            $pdo->beginTransaction();
            
            // Reset tất cả rating về -1
            $pdo->exec("UPDATE users SET rating = -1");
            
            // Lấy tất cả submissions thành công theo thời gian
            $stmt = $pdo->prepare("
                SELECT 
                    user_id, 
                    problem_id, 
                    test_cases_passed,
                    created_at
                FROM submissions 
                WHERE status = 'Accepted'
                ORDER BY created_at ASC
            ");
            $stmt->execute();
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $userProblemHistory = [];
            
            foreach ($submissions as $submission) {
                $userId = $submission['user_id'];
                $problemId = $submission['problem_id'];
                $testCasesPassed = $submission['test_cases_passed'];
                
                // Kiểm tra user đã giải bài này với bao nhiêu test case trước đó
                $previousTestCases = 0;
                if (isset($userProblemHistory[$userId][$problemId])) {
                    $previousTestCases = $userProblemHistory[$userId][$problemId];
                }
                
                // Chỉ cộng điểm nếu có test case mới pass
                if ($testCasesPassed > $previousTestCases) {
                    $ratingToAdd = $testCasesPassed - $previousTestCases;
                    
                    // Cập nhật rating
                    $stmt = $pdo->prepare("
                        UPDATE users 
                        SET rating = CASE 
                            WHEN rating = -1 THEN ?
                            ELSE rating + ?
                        END
                        WHERE id = ?
                    ");
                    $stmt->execute([$ratingToAdd, $ratingToAdd, $userId]);
                    
                    // Cập nhật lịch sử
                    $userProblemHistory[$userId][$problemId] = $testCasesPassed;
                }
            }
            
            $pdo->commit();
            return true;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error recalculating ratings: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Lấy thống kê rating của toàn hệ thống
     */
    public static function getRatingStatistics()
    {
        $pdo = getConnection();
        global $RANKING;
        
        try {
            $stats = [];
            
            // Đếm số user trong mỗi rank
            foreach ($RANKING as $rankKey => $rankInfo) {
                if ($rankKey === 'Unranked') {
                    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE rating = -1");
                } else {
                    $stmt = $pdo->prepare("
                        SELECT COUNT(*) as count 
                        FROM users 
                        WHERE rating >= ? AND rating <= ?
                    ");
                    $stmt->execute([$rankInfo['min_rating'], $rankInfo['max_rating']]);
                }
                
                if ($rankKey === 'Unranked') {
                    $stmt->execute();
                }
                
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                $stats[$rankKey] = [
                    'rank_info' => $rankInfo,
                    'user_count' => $result['count']
                ];
            }
            
            // Tổng số user có rating
            $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM users WHERE rating >= 0");
            $stmt->execute();
            $totalRated = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Rating trung bình
            $stmt = $pdo->prepare("SELECT AVG(rating) as avg_rating FROM users WHERE rating >= 0");
            $stmt->execute();
            $avgRating = $stmt->fetch(PDO::FETCH_ASSOC)['avg_rating'];
            
            return [
                'rank_distribution' => $stats,
                'total_rated_users' => $totalRated,
                'average_rating' => round($avgRating, 2)
            ];
            
        } catch (Exception $e) {
            error_log("Error getting rating statistics: " . $e->getMessage());
            return [];
        }
    }
}
