<?php

class LeaderboardController {
    private $userModel;
    
    public function __construct() {
        require_once MODEL_PATH . '/UserModel.php';
        $this->userModel = new UserModel();
    }
    
    public function index() {
        try {
            global $BADGES;
            require_once APP_PATH . '/helpers/LeaderboardHelper.php';
            
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = 20;
            $offset = ($page - 1) * $limit;
            
            $rankFilter = isset($_GET['rank']) && !empty($_GET['rank']) ? $_GET['rank'] : 'all';
            
            if (!LeaderboardHelper::isValidRankFilter($rankFilter)) {
                $rankFilter = 'all';
            }
            
            $leaderboardData = $this->userModel->getLeaderboard($limit, $offset, $rankFilter);
            $totalUsers = $this->userModel->getTotalActiveUsers($rankFilter);
            $totalPages = ceil($totalUsers / $limit);
            
            $rankTiers = LeaderboardHelper::formatRankTiers();
            
            $data = [
                'leaderboard' => $leaderboardData,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalUsers' => $totalUsers,
                'rankTiers' => $rankTiers,
                'currentRankFilter' => $rankFilter,
                'hasNextPage' => $page < $totalPages,
                'hasPrevPage' => $page > 1,
                'BADGES' => $BADGES
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
            global $BADGES;
            require_once APP_PATH . '/helpers/LeaderboardHelper.php';
            
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? min(100, max(10, intval($_GET['limit']))) : 20;
            $offset = ($page - 1) * $limit;
            $rankFilter = isset($_GET['rank']) && !empty($_GET['rank']) ? $_GET['rank'] : 'all';
            
            if (!LeaderboardHelper::isValidRankFilter($rankFilter)) {
                $rankFilter = 'all';
            }
            
            $leaderboardData = $this->userModel->getLeaderboard($limit, $offset, $rankFilter);
            $totalUsers = $this->userModel->getTotalActiveUsers($rankFilter);
            
            $rankTiers = LeaderboardHelper::formatRankTiers();
            
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
}