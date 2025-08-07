<?php
require_once ROOT_PATH . '/app/core/Controller.php';
require_once ROOT_PATH . '/config/databaseConnect.php';
require_once ROOT_PATH . '/config/config.php';

class SubmissionController extends Controller
{
    private $db;
    
    public function __construct()
    {
        $this->db = getConnection();
    }
    
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $status = $_GET['status'] ?? '';
        $language = $_GET['language'] ?? '';
        $problemId = $_GET['problem_id'] ?? '';
        
        try {
            // Build where clause
            $whereConditions = ['s.user_id = :user_id'];
            $params = ['user_id' => $userId];
            
            if (!empty($status)) {
                $whereConditions[] = 's.status = :status';
                $params['status'] = $status;
            }
            
            if (!empty($language)) {
                $whereConditions[] = 's.language = :language';
                $params['language'] = $language;
            }
            
            if (!empty($problemId)) {
                $whereConditions[] = 's.problem_id = :problem_id';
                $params['problem_id'] = $problemId;
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $whereConditions);
            
            $countSql = "SELECT COUNT(*) as total 
                         FROM submissions s 
                         INNER JOIN problems p ON s.problem_id = p.id 
                         $whereClause";
            $countStmt = $this->db->prepare($countSql);
            $countStmt->execute($params);
            $totalSubmissions = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $sql = "SELECT 
                        s.id,
                        s.problem_id,
                        s.language,
                        s.status,
                        s.runtime,
                        s.memory_used,
                        s.score,
                        s.test_cases_passed,
                        s.total_test_cases,
                        s.submitted_at,
                        p.title as problem_title,
                        p.slug as problem_slug,
                        p.difficulty
                    FROM submissions s
                    INNER JOIN problems p ON s.problem_id = p.id
                    $whereClause
                    ORDER BY s.submitted_at DESC
                    LIMIT :limit OFFSET :offset";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(array_merge($params, ['limit' => $limit, 'offset' => $offset]));
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stats = $this->getUserSubmissionStats($userId);
            
            $problemsSql = "SELECT DISTINCT p.id, p.title 
                           FROM problems p 
                           INNER JOIN submissions s ON p.id = s.problem_id 
                           WHERE s.user_id = :user_id 
                           ORDER BY p.title";
            $problemsStmt = $this->db->prepare($problemsSql);
            $problemsStmt->execute(['user_id' => $userId]);
            $problems = $problemsStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $totalPages = ceil($totalSubmissions / $limit);
            
            $data = [
                'submissions' => $submissions,
                'currentPage' => $page,
                'totalPages' => $totalPages,
                'totalSubmissions' => $totalSubmissions,
                'stats' => $stats,
                'problems' => $problems,
                'filters' => [
                    'status' => $status,
                    'language' => $language,
                    'problem_id' => $problemId
                ]
            ];
            
            $this->renderPage('submissions', 'Lịch sử nộp bài - CodeJudge', 'Xem lại tất cả submissions của bạn trên CodeJudge', $data);
            
        } catch (Exception $e) {
            error_log("Submissions error: " . $e->getMessage());
            $this->renderPage('submissions', 'Lịch sử nộp bài - CodeJudge', 'Xem lại tất cả submissions của bạn trên CodeJudge', [
                'submissions' => [],
                'currentPage' => 1,
                'totalPages' => 0,
                'totalSubmissions' => 0,
                'stats' => [],
                'problems' => [],
                'filters' => ['status' => '', 'language' => '', 'problem_id' => ''],
                'error' => 'Có lỗi xảy ra khi tải dữ liệu'
            ]);
        }
    }
    
    public function show($submissionId)
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        try {
            $sql = "SELECT 
                        s.*,
                        p.title as problem_title,
                        p.slug as problem_slug,
                        p.difficulty,
                        p.description as problem_description
                    FROM submissions s
                    INNER JOIN problems p ON s.problem_id = p.id
                    WHERE s.id = :submission_id AND s.user_id = :user_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'submission_id' => $submissionId,
                'user_id' => $userId
            ]);
            
            $submission = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$submission) {
                http_response_code(404);
                $this->renderPage('404', '404 - Không tìm thấy submission');
                return;
            }
            
            $data = ['submission' => $submission];
            $this->renderPage('submission_detail', "Submission #{$submissionId} - CodeJudge", '', $data);
            
        } catch (Exception $e) {
            error_log("Submission detail error: " . $e->getMessage());
            http_response_code(500);
            $this->renderPage('404', '500 - Lỗi hệ thống');
        }
    }
    
    public function getUserSubmissionStats($userId)
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_submissions,
                        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_submissions,
                        COUNT(DISTINCT CASE WHEN status = 'accepted' THEN problem_id END) as problems_solved,
                        COUNT(DISTINCT problem_id) as problems_attempted,
                        AVG(CASE WHEN status = 'accepted' THEN runtime END) as avg_runtime,
                        AVG(CASE WHEN status = 'accepted' THEN memory_used END) as avg_memory
                    FROM submissions 
                    WHERE user_id = :user_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['user_id' => $userId]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $langSql = "SELECT 
                            language,
                            COUNT(*) as count,
                            SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted
                        FROM submissions 
                        WHERE user_id = :user_id 
                        GROUP BY language 
                        ORDER BY count DESC";
            
            $langStmt = $this->db->prepare($langSql);
            $langStmt->execute(['user_id' => $userId]);
            $languageStats = $langStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $statusSql = "SELECT 
                             status,
                             COUNT(*) as count
                         FROM submissions 
                         WHERE user_id = :user_id 
                         GROUP BY status 
                         ORDER BY count DESC";
            
            $statusStmt = $this->db->prepare($statusSql);
            $statusStmt->execute(['user_id' => $userId]);
            $statusStats = $statusStmt->fetchAll(PDO::FETCH_ASSOC);
            
            return [
                'general' => $stats,
                'languages' => $languageStats,
                'status' => $statusStats
            ];
            
        } catch (Exception $e) {
            error_log("Stats error: " . $e->getMessage());
            return [
                'general' => [],
                'languages' => [],
                'status' => []
            ];
        }
    }
}
?>
