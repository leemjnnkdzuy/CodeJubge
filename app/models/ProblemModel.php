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

    public function getProblemsForAdmin($filters = []): array {
        try {
            $limit = $filters['limit'] ?? 10;
            $offset = $filters['offset'] ?? 0;
            
            $sql = "SELECT 
                        p.*,
                        u.username as creator_name
                    FROM problems p
                    LEFT JOIN users u ON p.created_by = u.id
                    ORDER BY p.id DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit, $offset]);
            
            $problems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Add default stats for each problem
            foreach ($problems as &$problem) {
                $problem['solved_count'] = 0;
                $problem['attempt_count'] = 0;  
                $problem['acceptance_rate'] = 0;
                
                // Process JSON fields
                $problem['examples'] = json_decode($problem['examples'] ?? '[]', true);
                $problem['problem_types'] = json_decode($problem['problem_types'] ?? '[]', true);
                $problem['tags'] = json_decode($problem['tags'] ?? '[]', true);
                $problem['hints'] = json_decode($problem['hints'] ?? '[]', true);
            }
            
            return $problems;
            
        } catch (PDOException $e) {
            error_log("Error getting problems for admin: " . $e->getMessage());
            return [];
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
            $sql = "SELECT s.*, u.username
                    FROM submissions s
                    JOIN users u ON s.user_id = u.id
                    WHERE s.user_id = ? AND s.problem_id = ?
                    ORDER BY s.submitted_at DESC
                    LIMIT ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $problemId, $limit]);
            
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($submissions as &$submission) {
                $submission['created_at'] = $submission['submitted_at'];
                $submission['execution_time'] = $submission['runtime'] ?? 0;
                
                $memoryBytes = $submission['memory_used'] ?? 0;
                if ($memoryBytes > 0) {
                    $submission['memory_usage'] = round($memoryBytes / (1024 * 1024), 2);
                } else {
                    $submission['memory_usage'] = 0;
                }
                
                $statusMap = [
                    'accepted' => 'Accepted',
                    'wrong_answer' => 'Wrong Answer', 
                    'time_limit' => 'Time Limit Exceeded',
                    'memory_limit' => 'Memory Limit Exceeded',
                    'runtime_error' => 'Runtime Error',
                    'compile_error' => 'Compile Error',
                    'pending' => 'Pending',
                    'running' => 'Running'
                ];
                
                $submission['status'] = $statusMap[$submission['status']] ?? ucfirst($submission['status']);
            }
            
            return $submissions;
            
        } catch (PDOException $e) {
            error_log("Error getting user submissions: " . $e->getMessage());
            return [];
        }
    }
    
    public function getExampleSubmissions($problemId, $limit = 5): array
    {
        try {
            $sql = "SELECT s.*, u.username
                    FROM submissions s
                    JOIN users u ON s.user_id = u.id
                    WHERE s.problem_id = ? AND s.status = 'accepted'
                    ORDER BY s.runtime ASC, s.memory_used ASC, s.submitted_at DESC
                    LIMIT ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$problemId, $limit]);
            
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($submissions as &$submission) {
                $submission['created_at'] = $submission['submitted_at'];
                $submission['execution_time'] = $submission['runtime'] ?? 0;
                
                $memoryBytes = $submission['memory_used'] ?? 0;
                if ($memoryBytes > 0) {
                    $submission['memory_usage'] = round($memoryBytes / (1024 * 1024), 2);
                } else {
                    $submission['memory_usage'] = 0;
                }
                
                $submission['status'] = 'Accepted';
            }
            
            return $submissions;
            
        } catch (PDOException $e) {
            error_log("Error getting example submissions: " . $e->getMessage());
            return [];
        }
    }

    public function getProblemById($id): ?array {
        try {
            $sql = "SELECT p.*, u.username as creator_name 
                   FROM problems p
                   LEFT JOIN users u ON p.created_by = u.id  
                   WHERE p.id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            
            $problem = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($problem) {
                $problem['examples'] = json_decode($problem['examples'] ?? '[]', true);
                $problem['problem_types'] = json_decode($problem['problem_types'] ?? '[]', true);
                $problem['tags'] = json_decode($problem['tags'] ?? '[]', true);
                $problem['hints'] = json_decode($problem['hints'] ?? '[]', true);
                
                $statsSQL = "SELECT 
                    COUNT(*) as submission_count,
                    SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_count
                    FROM submissions 
                    WHERE problem_id = ?";
                
                $statsStmt = $this->pdo->prepare($statsSQL);
                $statsStmt->execute([$id]);
                $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);
                
                $problem['submission_count'] = $stats['submission_count'] ?? 0;
                $problem['accepted_count'] = $stats['accepted_count'] ?? 0;
                
                if ($problem['submission_count'] > 0) {
                    $problem['acceptance_rate'] = round(($problem['accepted_count'] / $problem['submission_count']) * 100, 1);
                } else {
                    $problem['acceptance_rate'] = 0;
                }
                
                $problem['test_cases'] = $this->getTestCases($id);
            }
            
            return $problem ?: null;
            
        } catch (PDOException $e) {
            error_log("Error getting problem by ID: " . $e->getMessage());
            return null;
        }
    }

    public function getTestCases($problemId): array {
        try {
            $sql = "SELECT * FROM test_cases WHERE problem_id = ? ORDER BY id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$problemId]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting test cases: " . $e->getMessage());
            return [];
        }
    }

    public function createProblem($data): array {
        try {
            $this->pdo->beginTransaction();
            
            // Tạo slug từ title
            $slug = $this->generateSlug($data['title']);
            
            // Insert problem
            $sql = "INSERT INTO problems (
                title, slug, description, difficulty, category, 
                input_format, output_format, constraints, 
                examples, time_limit, memory_limit, 
                problem_types, tags, editorial, hints,
                is_active, created_by, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['title'],
                $slug,
                $data['description'],
                $data['difficulty'],
                $data['category'] ?? null,
                $data['input_format'] ?? '',
                $data['output_format'] ?? '',
                $data['constraints'] ?? '',
                json_encode($data['examples'] ?? []),
                $data['time_limit'] ?? 1000,
                $data['memory_limit'] ?? 128,
                json_encode($data['problem_types'] ?? []),
                json_encode($data['tags'] ?? []),
                $data['editorial'] ?? '',
                json_encode($data['hints'] ?? []),
                $data['is_active'] ?? 1,
                $data['created_by']
            ]);
            
            $problemId = $this->pdo->lastInsertId();
            
            // Insert test cases nếu có
            if (!empty($data['test_cases'])) {
                $this->insertTestCases($problemId, $data['test_cases']);
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Problem đã được tạo thành công',
                'problem_id' => $problemId,
                'slug' => $slug
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error creating problem: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo problem: ' . $e->getMessage()
            ];
        }
    }

    public function updateProblem($id, $data): array {
        try {
            $this->pdo->beginTransaction();
            
            // Update problem
            $sql = "UPDATE problems SET 
                title = ?, description = ?, difficulty = ?, category = ?,
                input_format = ?, output_format = ?, constraints = ?,
                examples = ?, time_limit = ?, memory_limit = ?,
                problem_types = ?, tags = ?, editorial = ?, hints = ?,
                is_active = ?, updated_at = NOW()
                WHERE id = ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['difficulty'],
                $data['category'] ?? null,
                $data['input_format'] ?? '',
                $data['output_format'] ?? '',
                $data['constraints'] ?? '',
                json_encode($data['examples'] ?? []),
                $data['time_limit'] ?? 1000,
                $data['memory_limit'] ?? 128,
                json_encode($data['problem_types'] ?? []),
                json_encode($data['tags'] ?? []),
                $data['editorial'] ?? '',
                json_encode($data['hints'] ?? []),
                $data['is_active'] ?? 1,
                $id
            ]);
            
            // Update test cases nếu có
            if (isset($data['test_cases'])) {
                // Xóa test cases cũ
                $this->deleteTestCases($id);
                // Insert test cases mới
                if (!empty($data['test_cases'])) {
                    $this->insertTestCases($id, $data['test_cases']);
                }
            }
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Problem đã được cập nhật thành công'
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error updating problem: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật problem: ' . $e->getMessage()
            ];
        }
    }

    public function deleteProblem($id): array {
        try {
            $this->pdo->beginTransaction();
            
            // Xóa test cases
            $this->deleteTestCases($id);
            
            // Xóa problem
            $sql = "DELETE FROM problems WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id]);
            
            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Problem đã được xóa thành công'
            ];
            
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            error_log("Error deleting problem: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa problem: ' . $e->getMessage()
            ];
        }
    }

    private function generateSlug($title): string {
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Kiểm tra slug đã tồn tại chưa
        $counter = 1;
        $originalSlug = $slug;
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    private function slugExists($slug): bool {
        $sql = "SELECT COUNT(*) FROM problems WHERE slug = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$slug]);
        return $stmt->fetchColumn() > 0;
    }

    private function insertTestCases($problemId, $testCases): void {
        $sql = "INSERT INTO test_cases (problem_id, input, expected_output, is_sample, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->pdo->prepare($sql);
        
        foreach ($testCases as $testCase) {
            $stmt->execute([
                $problemId,
                $testCase['input'],
                $testCase['expected_output'],
                $testCase['is_sample'] ?? 0
            ]);
        }
    }

    private function deleteTestCases($problemId): void {
        $sql = "DELETE FROM test_cases WHERE problem_id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$problemId]);
    }
}
?>
