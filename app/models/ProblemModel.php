<?php

class ProblemModel {
    private PDO $pdo;
    
    public function __construct() {
        require_once __DIR__ . '/../../config/databaseConnect.php';
        $database = Database::getInstance();
        $this->pdo = $database->getConnection();
    }

    public function getProblems($filters = []): array {
        $page = $filters['page'] ?? 1;
        $limit = $filters['limit'] ?? 10;
        $offset = ($page - 1) * $limit;
        
        $where = ['p.is_active = 1'];
        $params = [];
        $joins = [];
        
        if (!empty($filters['search'])) {
            $where[] = "(p.title LIKE ? OR p.description LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['difficulty']) && is_array($filters['difficulty'])) {
            $placeholders = str_repeat('?,', count($filters['difficulty']) - 1) . '?';
            $where[] = "p.difficulty IN ($placeholders)";
            $params = array_merge($params, $filters['difficulty']);
        }
        
        if (!empty($filters['problem_types']) && is_array($filters['problem_types'])) {
            $typeConditions = [];
            foreach ($filters['problem_types'] as $type) {
                $typeConditions[] = "JSON_CONTAINS(p.problem_types, ?)";
                $params[] = '"' . $type . '"';
            }
            if (!empty($typeConditions)) {
                $where[] = '(' . implode(' OR ', $typeConditions) . ')';
            }
        }
        
        if (!empty($filters['status']) && $filters['status'] !== 'all' && !empty($filters['user_id'])) {
            $joins[] = "LEFT JOIN submissions sub ON p.id = sub.problem_id AND sub.user_id = ?";
            $params[] = $filters['user_id'];
            
            if ($filters['status'] === 'solved') {
                $where[] = "sub.status = 'accepted'";
            } elseif ($filters['status'] === 'unsolved') {
                $where[] = "sub.id IS NULL OR sub.status != 'accepted'";
            }
        }
        
        $orderBy = "p.id ASC";
        if (!empty($filters['sort'])) {
            switch ($filters['sort']) {
                case 'difficulty':
                    $orderBy = "FIELD(p.difficulty, 'easy', 'medium', 'hard'), p.title ASC";
                    break;
                case 'title':
                    $orderBy = "p.title ASC";
                    break;
            }
        }
        
        $joinsStr = implode(' ', $joins);
        $whereStr = implode(' AND ', $where);
        
        $countSql = "SELECT COUNT(DISTINCT p.id) as total 
                     FROM problems p 
                     $joinsStr 
                     WHERE $whereStr";
        
        try {
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute($params);
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $sql = "SELECT DISTINCT
                        p.*,
                        0 as submission_count,
                        0 as accepted_count,
                        0 as acceptance_rate
                    FROM problems p
                    $joinsStr
                    WHERE $whereStr
                    ORDER BY $orderBy
                    LIMIT $limit OFFSET $offset";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $problems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($problems as &$problem) {
                $problem['user_status'] = null;
            }
            
            return [
                'problems' => $problems,
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => ceil($total / $limit)
            ];
        } catch (PDOException $e) {
            error_log("Error getting problems: " . $e->getMessage());
            return [
                'problems' => [],
                'total' => 0,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => 0
            ];
        }
    }
    
    public function getProblemsStats(): array
    {
        try {
            $sql = "SELECT 
                        COUNT(*) as total_problems,
                        COUNT(CASE WHEN difficulty = 'easy' THEN 1 END) as easy_count,
                        COUNT(CASE WHEN difficulty = 'medium' THEN 1 END) as medium_count,
                        COUNT(CASE WHEN difficulty = 'hard' THEN 1 END) as hard_count
                    FROM problems 
                    WHERE is_active = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting problems stats: " . $e->getMessage());
            return [
                'total_problems' => 0,
                'easy_count' => 0,
                'medium_count' => 0,
                'hard_count' => 0
            ];
        }
    }
    
    public function getProblemBySlug($slug): array|false
    {
        try {
            $sql = "SELECT 
                        p.*,
                        0 as submission_count,
                        0 as accepted_count,
                        0 as acceptance_rate
                    FROM problems p
                    WHERE p.slug = :slug AND p.is_active = 1";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':slug', $slug);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting problem by slug: " . $e->getMessage());
            return false;
        }
    }
    
    public function getUserSubmissions($userId, $problemId, $limit = 10): array
    {
        try {
            return [];
        } catch (PDOException $e) {
            error_log("Error getting user submissions: " . $e->getMessage());
            return [];
        }
    }
    
    public function getExampleSubmissions($problemId, $limit = 5): array
    {
        try {
            return [];
        } catch (PDOException $e) {
            error_log("Error getting example submissions: " . $e->getMessage());
            return [];
        }
    }
}
?>
