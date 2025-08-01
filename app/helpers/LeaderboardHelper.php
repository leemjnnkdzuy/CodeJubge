<?php
class LeaderboardHelper {
    
    private static $rankingConfig = null;
    private static $rankTiersFormatted = null;
    private static function initRankingConfig() {
        if (self::$rankingConfig === null) {
            global $RANKING;
            
            if (isset($RANKING) && is_array($RANKING)) {
                self::$rankingConfig = $RANKING;
            } else {
                self::$rankingConfig = [
                    "Unranked" => [
                        "order" => 0,
                        "name" => "Chưa Xếp Hạng",
                        "min_rating" => -1,
                        "max_rating" => -1,
                        "color" => "#6c757d",
                        "icon" => "rank_Unranked.png"
                    ]
                ];
            }
        }
    }

    public static function getRankByRating($rating) {
        self::initRankingConfig();
        
        if ($rating === null || $rating === -1) {
            return 'Unranked';
        }
        
        $rating = intval($rating);
        
        foreach (self::$rankingConfig as $rankKey => $rankData) {
            if ($rankKey === 'Unranked') continue;
            
            if ($rating >= $rankData['min_rating'] && $rating <= $rankData['max_rating']) {
                return $rankKey;
            }
        }
        
        return 'Unranked';
    }
    
    public static function getRankInfo($rankKey) {
        self::initRankingConfig();
        return self::$rankingConfig[$rankKey] ?? self::$rankingConfig['Unranked'];
    }

    public static function formatRating($rating) {
        if ($rating === null || $rating === -1) {
            return 'Chưa có xếp hạng';
        }
        return number_format($rating);
    }

    public static function getRatingColor($rating) {
        $rankKey = self::getRankByRating($rating);
        $rankInfo = self::getRankInfo($rankKey);
        return $rankInfo['color'];
    }

    public static function getRatingLevel($rating) {
        $rankKey = self::getRankByRating($rating);
        $rankInfo = self::getRankInfo($rankKey);
        return $rankInfo['name'];
    }

    public static function getRatingProgress($rating) {
        if ($rating === null || $rating === -1) {
            return 0;
        }
        
        $rankKey = self::getRankByRating($rating);
        $rankInfo = self::getRankInfo($rankKey);
        
        if ($rankKey === 'Unranked') {
            return 0;
        }
        
        $minRating = $rankInfo['min_rating'];
        $maxRating = $rankInfo['max_rating'];
        
        if ($maxRating >= 999999) {
            return 100;
        }
        
        $progress = (($rating - $minRating) / ($maxRating - $minRating)) * 100;
        return max(0, min(100, $progress));
    }
    
    public static function formatRankTiers() {
        if (self::$rankTiersFormatted !== null) {
            return self::$rankTiersFormatted;
        }
        
        self::initRankingConfig();
        $formattedTiers = [];
        
        foreach (self::$rankingConfig as $key => $rank) {
            $formattedTiers[$key] = [
                'name' => $rank['name'],
                'range' => self::getRankRange($key),
                'image' => '/assets/' . $rank['icon'],
                'color' => $rank['color'],
                'min_rating' => $rank['min_rating'],
                'max_rating' => $rank['max_rating'],
                'order' => $rank['order']
            ];
        }
        
        uasort($formattedTiers, function($a, $b) {
            return $a['order'] <=> $b['order'];
        });
        
        self::$rankTiersFormatted = $formattedTiers;
        return $formattedTiers;
    }

    public static function getRankRange($rankKey) {
        self::initRankingConfig();
        $rankInfo = self::$rankingConfig[$rankKey] ?? self::$rankingConfig['Unranked'];
        
        if ($rankKey === 'Unranked') {
            return 'Chưa có điểm';
        }
        
        if ($rankInfo['max_rating'] >= 999999) {
            return number_format($rankInfo['min_rating']) . '+';
        }
        
        return number_format($rankInfo['min_rating']) . ' - ' . number_format($rankInfo['max_rating']);
    }

    public static function isValidRankFilter($rankFilter) {
        if ($rankFilter === 'all') return true;
        
        self::initRankingConfig();
        return isset(self::$rankingConfig[$rankFilter]);
    }

    public static function buildRankFilterClause($rankFilter) {
        if ($rankFilter === 'all') {
            return [
                'clause' => '',
                'params' => [],
                'additional_order' => '',
                'description' => 'Hiển thị tất cả người dùng'
            ];
        }
        
        if (!self::isValidRankFilter($rankFilter)) {
            return self::buildRankFilterClause('all');
        }
        
        self::initRankingConfig();
        $rank = self::$rankingConfig[$rankFilter];
        
        if ($rankFilter === 'Unranked') {
            return [
                'clause' => ' AND u.rating = -1',
                'params' => [],
                'additional_order' => '',
                'description' => 'Hiển thị người dùng chưa có xếp hạng'
            ];
        }
        
        if ($rank['max_rating'] >= 999999) {
            return [
                'clause' => ' AND u.rating >= :min_rating',
                'params' => ['min_rating' => $rank['min_rating']],
                'additional_order' => ', u.rating DESC',
                'description' => "Hiển thị người dùng từ " . number_format($rank['min_rating']) . " điểm trở lên"
            ];
        }
        
        return [
            'clause' => ' AND u.rating BETWEEN :min_rating AND :max_rating',
            'params' => [
                'min_rating' => $rank['min_rating'],
                'max_rating' => $rank['max_rating']
            ],
            'additional_order' => ', u.rating DESC',
            'description' => "Hiển thị người dùng từ " . number_format($rank['min_rating']) . " đến " . number_format($rank['max_rating']) . " điểm"
        ];
    }

    public static function buildOrderClause($rankFilter) {

        $baseOrder = "u.total_problems_solved DESC, CASE WHEN u.rating = -1 THEN 0 ELSE u.rating END DESC, u.id ASC";
        
        if ($rankFilter === 'all') {
            return $baseOrder;
        }
        
        $filterInfo = self::buildRankFilterClause($rankFilter);
        
        if (!empty($filterInfo['additional_order'])) {
            return str_replace(
                "u.total_problems_solved DESC", 
                "u.total_problems_solved DESC" . $filterInfo['additional_order'], 
                $baseOrder
            );
        }
        
        return $baseOrder;
    }

    public static function getFilterInfo($rankFilter) {
        if ($rankFilter === 'all') {
            return [
                'name' => 'Tất Cả Hạng',
                'description' => 'Hiển thị toàn bộ người dùng',
                'count_description' => 'Tổng số người dùng',
                'color' => 'var(--primary-blue)',
                'icon' => 'bx-trophy',
                'is_all_filter' => true
            ];
        }
        
        self::initRankingConfig();
        if (!isset(self::$rankingConfig[$rankFilter])) {
            return self::getFilterInfo('all');
        }
        
        $rank = self::$rankingConfig[$rankFilter];
        return [
            'name' => $rank['name'],
            'description' => self::getRankRange($rankFilter),
            'count_description' => "Số người dùng hạng {$rank['name']}",
            'color' => $rank['color'],
            'icon' => 'bx-medal',
            'is_all_filter' => false,
            'rank_data' => $rank
        ];
    }

    public static function getLeaderboardData($db, $limit = 50, $offset = 0, $rankFilter = 'all') {
        try {
            // Build filter conditions
            $whereClause = "WHERE u.is_active = 1 AND u.role = 'user'";
            $params = [];
            
            $filterData = self::buildRankFilterClause($rankFilter);
            $whereClause .= $filterData['clause'];
            $params = array_merge($params, $filterData['params']);
            
            $orderClause = self::buildOrderClause($rankFilter);
            
            $query = "
                SELECT 
                    u.id,
                    u.username,
                    u.first_name,
                    u.last_name,
                    u.avatar,
                    u.total_problems_solved,
                    u.total_submissions,
                    u.rating,
                    u.badges,
                    u.created_at,
                    RANK() OVER (ORDER BY {$orderClause}) as user_rank
                FROM users u
                {$whereClause}
                ORDER BY {$orderClause}
                LIMIT :limit OFFSET :offset
            ";
            
            $params['limit'] = $limit;
            $params['offset'] = $offset;
            
            $results = $db->select($query, $params);
            
            return self::processLeaderboardResults($results, $offset);
            
        } catch (Exception $e) {
            error_log("Error in getLeaderboardData: " . $e->getMessage());
            return [];
        }
    }

    private static function processLeaderboardResults($results, $offset = 0) {
        $leaderboard = [];
        
        foreach ($results as $index => $user) {
            $rating = $user['rating'] ?? -1;
            $rankTier = self::getRankByRating($rating);
            $rankInfo = self::getRankInfo($rankTier);
            
            $badges = [];
            if (!empty($user['badges'])) {
                $decodedBadges = json_decode($user['badges'], true);
                if (is_array($decodedBadges)) {
                    $badges = $decodedBadges;
                }
            }
            
            $avatarSrc = '';
            if (!empty($user['avatar'])) {
                require_once APP_PATH . '/helpers/AvatarHelper.php';
                $avatarSrc = AvatarHelper::base64ToImageSrc($user['avatar']);
            }
            
            $leaderboard[] = [
                'rank' => $user['user_rank'] ?? ($offset + $index + 1),
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
                'first_name' => $user['first_name'] ?? '',
                'last_name' => $user['last_name'] ?? '',
                'avatar' => $user['avatar'] ?? '',
                'avatar_src' => $avatarSrc,
                'problems_solved' => intval($user['total_problems_solved'] ?? 0),
                'total_submissions' => intval($user['total_submissions'] ?? 0),
                'rating' => $rating,
                'rating_formatted' => self::formatRating($rating),
                'rating_color' => self::getRatingColor($rating),
                'rating_progress' => self::getRatingProgress($rating),
                'badges' => $badges,
                'badges_count' => count($badges),
                'rank_tier' => $rankTier,
                'rank_info' => $rankInfo,
                'rank_name' => $rankInfo['name'],
                'rank_color' => $rankInfo['color'],
                'rank_icon' => $rankInfo['icon'],
                'created_at' => $user['created_at'] ?? null,
                'is_unranked' => ($rating === -1),
                'profile_url' => "/user/{$user['username']}"
            ];
        }
        
        return $leaderboard;
    }

    public static function getTotalFilteredUsers($db, $rankFilter = 'all') {
        try {
            $whereClause = "WHERE u.is_active = 1 AND u.role = 'user'";
            $params = [];
            
            $filterData = self::buildRankFilterClause($rankFilter);
            $whereClause .= $filterData['clause'];
            $params = array_merge($params, $filterData['params']);
            
            $query = "SELECT COUNT(*) as total FROM users u {$whereClause}";
            $result = $db->selectOne($query, $params);
            
            return intval($result['total'] ?? 0);
            
        } catch (Exception $e) {
            error_log("Error in getTotalFilteredUsers: " . $e->getMessage());
            return 0;
        }
    }

    public static function getRankStatistics($db) {
        try {
            self::initRankingConfig();
            
            $rankCases = [];
            foreach (self::$rankingConfig as $key => $rank) {
                if ($key === 'Unranked') {
                    $rankCases[] = "WHEN rating = -1 THEN '{$key}'";
                } elseif ($rank['max_rating'] >= 999999) {
                    $rankCases[] = "WHEN rating >= {$rank['min_rating']} THEN '{$key}'";
                } else {
                    $rankCases[] = "WHEN rating BETWEEN {$rank['min_rating']} AND {$rank['max_rating']} THEN '{$key}'";
                }
            }
            
            $rankCaseString = implode(' ', $rankCases);
            
            $query = "
                SELECT 
                    CASE {$rankCaseString} ELSE 'Unranked' END as rank_tier,
                    COUNT(*) as count
                FROM users u 
                WHERE u.is_active = 1 AND u.role = 'user'
                GROUP BY rank_tier
                ORDER BY MIN(CASE {$rankCaseString} ELSE 0 END)
            ";
            
            $results = $db->select($query);
            
            $statistics = [];
            foreach (self::$rankingConfig as $key => $rank) {
                $statistics[$key] = [
                    'rank_info' => $rank,
                    'count' => 0,
                    'percentage' => 0
                ];
            }
            
            $totalUsers = 0;
            foreach ($results as $result) {
                $rankTier = $result['rank_tier'];
                $count = intval($result['count']);
                
                if (isset($statistics[$rankTier])) {
                    $statistics[$rankTier]['count'] = $count;
                }
                $totalUsers += $count;
            }
            
            if ($totalUsers > 0) {
                foreach ($statistics as $key => &$stat) {
                    $stat['percentage'] = round(($stat['count'] / $totalUsers) * 100, 2);
                }
            }
            
            return [
                'statistics' => $statistics,
                'total_users' => $totalUsers
            ];
            
        } catch (Exception $e) {
            error_log("Error in getRankStatistics: " . $e->getMessage());
            return ['statistics' => [], 'total_users' => 0];
        }
    }

    public static function findUserPosition($db, $userId, $rankFilter = 'all') {
        try {
            $whereClause = "WHERE u.is_active = 1 AND u.role = 'user'";
            $params = ['user_id' => $userId];
            
            $filterData = self::buildRankFilterClause($rankFilter);
            $whereClause .= $filterData['clause'];
            $params = array_merge($params, $filterData['params']);
            
            $orderClause = self::buildOrderClause($rankFilter);
            
            $query = "
                SELECT user_rank FROM (
                    SELECT 
                        u.id,
                        RANK() OVER (ORDER BY {$orderClause}) as user_rank
                    FROM users u
                    {$whereClause}
                ) ranked
                WHERE id = :user_id
            ";
            
            $result = $db->selectOne($query, $params);
            return $result ? intval($result['user_rank']) : null;
            
        } catch (Exception $e) {
            error_log("Error in findUserPosition: " . $e->getMessage());
            return null;
        }
    }

    public static function warmCache() {
        self::initRankingConfig();
        self::formatRankTiers();
        
        $commonRatings = [0, 1000, 1500, 2000, 2500, 3000, 3500, 4000];
        foreach ($commonRatings as $rating) {
            self::getRankByRating($rating);
            self::getRatingColor($rating);
            self::getRatingProgress($rating);
        }
    }
}

function formatRating($rating) {
    return LeaderboardHelper::formatRating($rating);
}

function getRatingLevel($rating) {
    return LeaderboardHelper::getRatingLevel($rating);
}

function getRatingColor($rating) {
    return LeaderboardHelper::getRatingColor($rating);
}

function getRatingProgress($rating) {
    return LeaderboardHelper::getRatingProgress($rating);
}

function getUserRank($rating) {
    return LeaderboardHelper::getRankInfo(LeaderboardHelper::getRankByRating($rating));
}

?>
