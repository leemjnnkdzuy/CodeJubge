<?php

class LeaderboardController {
    private $userModel;
    
    public function __construct() {
        require_once MODEL_PATH . '/UserModel.php';
        $this->userModel = new UserModel();
    }
    
    public function index() {
        try {
            global $RANKING;
            
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = 50;
            $offset = ($page - 1) * $limit;
            
            $rankFilter = isset($_GET['rank']) && !empty($_GET['rank']) ? $_GET['rank'] : 'all';
            
            $leaderboardData = $this->userModel->getLeaderboard($limit, $offset, $rankFilter);
            $totalUsers = $this->userModel->getTotalActiveUsers();
            $totalPages = ceil($totalUsers / $limit);
            
            require_once APP_PATH . '/helpers/AvatarHelper.php';
            foreach ($leaderboardData as &$user) {
                $user['avatar_src'] = AvatarHelper::base64ToImageSrc($user['avatar']);
            }
            
            $rankTiers = $this->formatRankingSystem($RANKING);
            
            $data = [
                'leaderboard' => $leaderboardData,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalUsers' => $totalUsers,
                'rankTiers' => $rankTiers,
                'currentRankFilter' => $rankFilter,
                'hasNextPage' => $page < $totalPages,
                'hasPrevPage' => $page > 1
            ];
            
            extract($data);
            include VIEW_PATH . '/leaderboard.php';
            
        } catch (Exception $e) {
            error_log("Leaderboard Error: " . $e->getMessage());
            $_SESSION['error'] = "Có lỗi xảy ra khi tải bảng xếp hạng.";
            header('Location: /');
            exit;
        }
    }
    
    public function api() {
        header('Content-Type: application/json');
        
        try {
            global $RANKING;
            
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? min(100, max(10, intval($_GET['limit']))) : 50;
            $offset = ($page - 1) * $limit;
            $rankFilter = isset($_GET['rank']) && !empty($_GET['rank']) ? $_GET['rank'] : 'all';
            
            $leaderboardData = $this->userModel->getLeaderboard($limit, $offset, $rankFilter);
            $totalUsers = $this->userModel->getTotalActiveUsers();
            
            require_once APP_PATH . '/helpers/AvatarHelper.php';
            foreach ($leaderboardData as &$user) {
                $user['avatar_src'] = AvatarHelper::base64ToImageSrc($user['avatar']);
            }
            
            $rankTiers = $this->formatRankingSystem($RANKING);
            
            echo json_encode([
                'success' => true,
                'data' => $leaderboardData,
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => ceil($totalUsers / $limit),
                    'total_users' => $totalUsers,
                    'limit' => $limit
                ],
                'rank_tiers' => $rankTiers
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Có lỗi xảy ra khi tải dữ liệu'
            ]);
        }
        exit;
    }
    
    private function formatRankingSystem($ranking) {
        $rankTiers = [];
        $colors = [
            'Unranked' => '#6c757d',
        	'Iron' => '#6c757d',
			'Bronze' => '#cd7f32',
			'Silver' => '#666666ff',
			'Gold' => '#ffd700',
			'Platinum' => '#00a78bff',
			'Diamond' => '#b30fffff',
			'Ascendant' => '#00c462ff',
			'Immortal' => '#7e0000ff',
			'Radiant' => '#fff345ff'
        ];
        
        foreach ($ranking as $key => $rank) {
            $tierName = explode('_', $key)[0];
            $color = $colors[$tierName] ?? '#6c757d';
            
            $rangeText = '';
            if ($rank['start_point'] == -100000000000000) {
                $rangeText = 'Chưa có điểm';
            } elseif ($rank['end_point'] == 100000000000000) {
                $rangeText = $rank['start_point'] . '+';
            } else {
                $rangeText = $rank['start_point'] . ' - ' . $rank['end_point'];
            }
            
            $rankTiers[$key] = [
                'name' => $rank['name'],
                'range' => $rangeText,
                'image' => '/assets/' . $rank['icon'],
                'color' => $color,
                'start_point' => $rank['start_point'],
                'end_point' => $rank['end_point']
            ];
        }
        
        return $rankTiers;
    }
}