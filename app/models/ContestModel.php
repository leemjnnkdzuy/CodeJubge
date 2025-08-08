<?php

class ContestModel
{
    private $db;
    private $table = 'contests';
    private $participantsTable = 'contest_participants';
    private $problemsTable = 'contest_problems';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get contests with filters
     */
    public function getContests($filters = [], $limit = 12, $offset = 0)
    {
        try {
            $sql = "
                SELECT c.*,
                       u.username as creator_username,
                       u.first_name as creator_first_name,
                       u.last_name as creator_last_name,
                       COUNT(DISTINCT cp.user_id) as participant_count,
                       COUNT(DISTINCT cpr.id) as problem_count,
                       CASE 
                           WHEN NOW() < c.start_time THEN 'upcoming'
                           WHEN NOW() BETWEEN c.start_time AND c.end_time THEN 'live'
                           ELSE 'finished'
                       END as status
                FROM {$this->table} c
                LEFT JOIN users u ON c.created_by = u.id
                LEFT JOIN {$this->participantsTable} cp ON c.id = cp.contest_id
                LEFT JOIN {$this->problemsTable} cpr ON c.id = cpr.contest_id
                WHERE c.is_active = 1
            ";
            
            $params = [];
            
            // Apply filters
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                if ($filters['status'] === 'upcoming') {
                    $sql .= " AND NOW() < c.start_time";
                } elseif ($filters['status'] === 'live') {
                    $sql .= " AND NOW() BETWEEN c.start_time AND c.end_time";
                } elseif ($filters['status'] === 'finished') {
                    $sql .= " AND NOW() > c.end_time";
                } elseif ($filters['status'] === 'joined' && !empty($filters['user_id'])) {
                    $sql .= " AND EXISTS (
                        SELECT 1 FROM {$this->participantsTable} cp2 
                        WHERE cp2.contest_id = c.id AND cp2.user_id = ?
                    )";
                    $params[] = $filters['user_id'];
                } elseif ($filters['status'] === 'created' && !empty($filters['user_id'])) {
                    $sql .= " AND c.created_by = ?";
                    $params[] = $filters['user_id'];
                }
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
                $searchParam = '%' . $filters['search'] . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if (!empty($filters['difficulty'])) {
                $sql .= " AND c.difficulty = ?";
                $params[] = $filters['difficulty'];
            }
            
            if (!empty($filters['duration'])) {
                if ($filters['duration'] === 'short') {
                    $sql .= " AND TIMESTAMPDIFF(HOUR, c.start_time, c.end_time) < 2";
                } elseif ($filters['duration'] === 'medium') {
                    $sql .= " AND TIMESTAMPDIFF(HOUR, c.start_time, c.end_time) BETWEEN 2 AND 5";
                } elseif ($filters['duration'] === 'long') {
                    $sql .= " AND TIMESTAMPDIFF(HOUR, c.start_time, c.end_time) > 5";
                }
            }
            
            $sql .= " GROUP BY c.id";
            
            // Apply sorting
            switch ($filters['sort'] ?? 'start_time_desc') {
                case 'start_time_asc':
                    $sql .= " ORDER BY c.start_time ASC";
                    break;
                case 'participants_desc':
                    $sql .= " ORDER BY participant_count DESC, c.start_time DESC";
                    break;
                case 'title_asc':
                    $sql .= " ORDER BY c.title ASC";
                    break;
                default:
                    $sql .= " ORDER BY c.start_time DESC";
            }
            
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $contests = $this->db->select($sql, $params);
            
            // Check if user has joined each contest
            if (!empty($filters['user_id'])) {
                foreach ($contests as &$contest) {
                    $contest['is_joined'] = $this->isParticipant($contest['id'], $filters['user_id']);
                }
            }
            
            return $contests;
            
        } catch (Exception $e) {
            error_log("Get Contests Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get contests count with filters
     */
    public function getContestsCount($filters = [])
    {
        try {
            $sql = "
                SELECT COUNT(DISTINCT c.id) as total
                FROM {$this->table} c
                LEFT JOIN users u ON c.created_by = u.id
                WHERE c.is_active = 1
            ";
            
            $params = [];
            
            // Apply same filters as in getContests
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                if ($filters['status'] === 'upcoming') {
                    $sql .= " AND NOW() < c.start_time";
                } elseif ($filters['status'] === 'live') {
                    $sql .= " AND NOW() BETWEEN c.start_time AND c.end_time";
                } elseif ($filters['status'] === 'finished') {
                    $sql .= " AND NOW() > c.end_time";
                } elseif ($filters['status'] === 'joined' && !empty($filters['user_id'])) {
                    $sql .= " AND EXISTS (
                        SELECT 1 FROM {$this->participantsTable} cp 
                        WHERE cp.contest_id = c.id AND cp.user_id = ?
                    )";
                    $params[] = $filters['user_id'];
                } elseif ($filters['status'] === 'created' && !empty($filters['user_id'])) {
                    $sql .= " AND c.created_by = ?";
                    $params[] = $filters['user_id'];
                }
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (c.title LIKE ? OR c.description LIKE ?)";
                $searchParam = '%' . $filters['search'] . '%';
                $params[] = $searchParam;
                $params[] = $searchParam;
            }
            
            if (!empty($filters['difficulty'])) {
                $sql .= " AND c.difficulty = ?";
                $params[] = $filters['difficulty'];
            }
            
            if (!empty($filters['duration'])) {
                if ($filters['duration'] === 'short') {
                    $sql .= " AND TIMESTAMPDIFF(HOUR, c.start_time, c.end_time) < 2";
                } elseif ($filters['duration'] === 'medium') {
                    $sql .= " AND TIMESTAMPDIFF(HOUR, c.start_time, c.end_time) BETWEEN 2 AND 5";
                } elseif ($filters['duration'] === 'long') {
                    $sql .= " AND TIMESTAMPDIFF(HOUR, c.start_time, c.end_time) > 5";
                }
            }
            
            $result = $this->db->selectOne($sql, $params);
            return $result['total'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Get Contests Count Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get contest by ID
     */
    public function getContestById($contestId)
    {
        try {
            $sql = "
                SELECT c.*,
                       u.username as creator_username,
                       u.first_name as creator_first_name,
                       u.last_name as creator_last_name,
                       COUNT(DISTINCT cp.user_id) as participant_count,
                       COUNT(DISTINCT cpr.id) as problem_count,
                       CASE 
                           WHEN NOW() < c.start_time THEN 'upcoming'
                           WHEN NOW() BETWEEN c.start_time AND c.end_time THEN 'live'
                           ELSE 'finished'
                       END as status
                FROM {$this->table} c
                LEFT JOIN users u ON c.created_by = u.id
                LEFT JOIN {$this->participantsTable} cp ON c.id = cp.contest_id
                LEFT JOIN {$this->problemsTable} cpr ON c.id = cpr.contest_id
                WHERE c.id = ? AND c.is_active = 1
                GROUP BY c.id
            ";
            
            return $this->db->selectOne($sql, [$contestId]);
            
        } catch (Exception $e) {
            error_log("Get Contest By ID Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Create new contest
     */
    public function createContest($contestData)
    {
        try {
            $sql = "
                INSERT INTO {$this->table} (
                    title, description, start_time, end_time, duration, difficulty, 
                    type, rules, created_by, is_public, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ";
            
            // Calculate duration in seconds
            $startTime = strtotime($contestData['start_time']);
            $endTime = strtotime($contestData['end_time']);
            $duration = $endTime - $startTime;
            
            $params = [
                $contestData['title'],
                $contestData['description'],
                $contestData['start_time'],
                $contestData['end_time'],
                $duration,
                $contestData['difficulty'],
                $contestData['type'],
                $contestData['rules'],
                $contestData['created_by'],
                ($contestData['type'] === 'public' ? 1 : 0)
            ];
            
            $result = $this->db->insert($sql, $params);
            
            if ($result) {
                return $result;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Create Contest Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user is participant
     */
    public function isParticipant($contestId, $userId)
    {
        try {
            $sql = "
                SELECT COUNT(*) as count 
                FROM {$this->participantsTable} 
                WHERE contest_id = ? AND user_id = ?
            ";
            
            $result = $this->db->selectOne($sql, [$contestId, $userId]);
            return $result['count'] > 0;
            
        } catch (Exception $e) {
            error_log("Is Participant Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Join contest
     */
    public function joinContest($contestId, $userId)
    {
        try {
            $sql = "
                INSERT INTO {$this->participantsTable} (
                    contest_id, user_id, registered_at, joined_at
                ) VALUES (?, ?, NOW(), NOW())
            ";
            
            return $this->db->insert($sql, [$contestId, $userId]);
            
        } catch (Exception $e) {
            error_log("Join Contest Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Leave contest
     */
    public function leaveContest($contestId, $userId)
    {
        try {
            $sql = "
                DELETE FROM {$this->participantsTable} 
                WHERE contest_id = ? AND user_id = ?
            ";
            
            return $this->db->delete($sql, [$contestId, $userId]);
            
        } catch (Exception $e) {
            error_log("Leave Contest Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get contest problems
     */
    public function getContestProblems($contestId)
    {
        try {
            $sql = "
                SELECT p.*, cp.points, cp.order_number
                FROM problems p
                INNER JOIN {$this->problemsTable} cp ON p.id = cp.problem_id
                WHERE cp.contest_id = ? AND p.is_active = 1
                ORDER BY cp.order_number ASC
            ";
            
            return $this->db->select($sql, [$contestId]);
            
        } catch (Exception $e) {
            error_log("Get Contest Problems Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get contest leaderboard
     */
    public function getContestLeaderboard($contestId, $limit = 50, $offset = 0)
    {
        try {
            $sql = "
                SELECT cp.*,
                       u.username,
                       u.first_name,
                       u.last_name,
                       u.avatar,
                       RANK() OVER (ORDER BY cp.total_score DESC, cp.total_time ASC) as rank_position
                FROM {$this->participantsTable} cp
                INNER JOIN users u ON cp.user_id = u.id
                WHERE cp.contest_id = ?
                ORDER BY cp.total_score DESC, cp.total_time ASC
                LIMIT ? OFFSET ?
            ";
            
            return $this->db->select($sql, [$contestId, $limit, $offset]);
            
        } catch (Exception $e) {
            error_log("Get Contest Leaderboard Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get contest participants count
     */
    public function getContestParticipantsCount($contestId)
    {
        try {
            $sql = "
                SELECT COUNT(*) as count 
                FROM {$this->participantsTable} 
                WHERE contest_id = ?
            ";
            
            $result = $this->db->selectOne($sql, [$contestId]);
            return $result['count'] ?? 0;
            
        } catch (Exception $e) {
            error_log("Get Contest Participants Count Error: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Get contest statistics
     */
    public function getContestStats($contestId)
    {
        try {
            $stats = [];
            
            // Total participants
            $stats['total_participants'] = $this->getContestParticipantsCount($contestId);
            
            // Total problems
            $sql = "SELECT COUNT(*) as count FROM {$this->problemsTable} WHERE contest_id = ?";
            $result = $this->db->selectOne($sql, [$contestId]);
            $stats['total_problems'] = $result['count'] ?? 0;
            
            // Total submissions
            $sql = "
                SELECT COUNT(*) as count 
                FROM submissions s
                INNER JOIN {$this->problemsTable} cp ON s.problem_id = cp.problem_id
                WHERE cp.contest_id = ?
            ";
            $result = $this->db->selectOne($sql, [$contestId]);
            $stats['total_submissions'] = $result['count'] ?? 0;
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Get Contest Stats Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Update contest
     */
    public function updateContest($contestId, $contestData)
    {
        try {
            $sql = "
                UPDATE {$this->table} 
                SET title = ?, description = ?, start_time = ?, end_time = ?, duration = ?, 
                    difficulty = ?, type = ?, rules = ?, is_public = ?, updated_at = NOW()
                WHERE id = ?
            ";
            
            // Calculate duration in seconds
            $startTime = strtotime($contestData['start_time']);
            $endTime = strtotime($contestData['end_time']);
            $duration = $endTime - $startTime;
            
            $params = [
                $contestData['title'],
                $contestData['description'],
                $contestData['start_time'],
                $contestData['end_time'],
                $duration,
                $contestData['difficulty'],
                $contestData['type'],
                $contestData['rules'],
                ($contestData['type'] === 'public' ? 1 : 0),
                $contestId
            ];
            
            return $this->db->update($sql, $params);
            
        } catch (Exception $e) {
            error_log("Update Contest Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Delete contest
     */
    public function deleteContest($contestId)
    {
        try {
            $sql = "UPDATE {$this->table} SET is_active = 0, updated_at = NOW() WHERE id = ?";
            return $this->db->update($sql, [$contestId]);
            
        } catch (Exception $e) {
            error_log("Delete Contest Error: " . $e->getMessage());
            return false;
        }
    }
}
