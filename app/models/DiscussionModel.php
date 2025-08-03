<?php

require_once __DIR__ . '/../../config/databaseConnect.php';

class DiscussionModel
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getDiscussions($page = 1, $limit = 10, $filter = 'all', $search = '', $sortBy = 'created_at', $sortOrder = 'DESC')
    {
        $offset = ($page - 1) * $limit;
        $currentUserId = $_SESSION['user_id'] ?? null;
        
        $sql = "SELECT DISTINCT
                    d.id,
                    d.title,
                    d.slug,
                    d.content,
                    d.author_id,
                    d.category,
                    d.tags,
                    d.is_pinned,
                    d.is_solved,
                    d.likes_count,
                    d.replies_count,
                    d.created_at,
                    d.updated_at,
                    d.last_reply_at,
                    u.username,
                    u.first_name,
                    u.last_name,
                    u.avatar,
                    u.badges,
                    lr.username as last_reply_username,
                    CASE WHEN db.id IS NOT NULL THEN 1 ELSE 0 END as is_bookmarked,
                    CASE WHEN dl.id IS NOT NULL THEN 1 ELSE 0 END as user_liked
                FROM discussions d
                INNER JOIN users u ON d.author_id = u.id
                LEFT JOIN users lr ON d.last_reply_by = lr.id
                LEFT JOIN discussion_bookmarks db ON d.id = db.discussion_id AND db.user_id = :current_user_id
                LEFT JOIN discussion_likes dl ON d.id = dl.discussion_id AND dl.user_id = :current_user_id_like
                WHERE u.is_active = 1";
        
        $params = [];
        
        if ($currentUserId) {
            $params['current_user_id'] = $currentUserId;
            $params['current_user_id_like'] = $currentUserId;
        } else {
            // If no user is logged in, we still need to provide the parameter
            $params['current_user_id'] = null;
            $params['current_user_id_like'] = null;
        }
        
        // Filter handling
        if ($filter !== 'all' && !empty($filter)) {
            global $DISCUSS_CATEGORIES;
            
            // Check if filter is a valid category from $DISCUSS_CATEGORIES
            $validCategories = [];
            foreach ($DISCUSS_CATEGORIES as $key => $category) {
                $validCategories[] = strtolower($key);
            }
            
            if (in_array($filter, $validCategories)) {
                $sql .= " AND d.category = :filter_category";
                $params['filter_category'] = $filter;
            }
        }
        
        if (!empty($search)) {
            $sql .= " AND (d.title LIKE :search OR d.content LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        $allowedSortColumns = ['created_at', 'updated_at', 'last_reply_at', 'likes_count', 'replies_count'];
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        $sql .= " ORDER BY d.is_pinned DESC, d.{$sortBy} {$sortOrder}";
        
        $sql .= " LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                if ($key === 'limit' || $key === 'offset') {
                    $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }
            
            $stmt->execute();
            $discussions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $ids = array_column($discussions, 'id');
            $uniqueIds = array_unique($ids);
            if (count($ids) !== count($uniqueIds)) {
                error_log("DUPLICATE DISCUSSIONS DETECTED in database query!");
                error_log("Total: " . count($ids) . ", Unique: " . count($uniqueIds));
                $duplicates = array_diff_assoc($ids, $uniqueIds);
                error_log("Duplicate IDs: " . implode(', ', $duplicates));
            }
            
            $seenIds = [];
            $discussions = array_filter($discussions, function($discussion) use (&$seenIds) {
                if (in_array($discussion['id'], $seenIds)) {
                    error_log("Removing duplicate discussion ID: " . $discussion['id']);
                    return false;
                }
                $seenIds[] = $discussion['id'];
                return true;
            });
            
            foreach ($discussions as &$discussion) {
                $discussion['tags'] = json_decode($discussion['tags'], true) ?? [];
                $discussion['badges'] = json_decode($discussion['badges'], true) ?? [];
            }
            
            return $discussions;
            
        } catch (PDOException $e) {
            error_log("Error getting discussions: " . $e->getMessage());
            return [];
        }
    }

    public function countDiscussions($filter = 'all', $search = '')
    {
        $sql = "SELECT COUNT(*) as total
                FROM discussions d
                INNER JOIN users u ON d.author_id = u.id
                WHERE u.is_active = 1";
        
        $params = [];
        
        // Filter handling
        if ($filter !== 'all' && !empty($filter)) {
            global $DISCUSS_CATEGORIES;
            
            // Check if filter is a valid category from $DISCUSS_CATEGORIES
            $validCategories = [];
            foreach ($DISCUSS_CATEGORIES as $key => $category) {
                $validCategories[] = strtolower($key);
            }
            
            if (in_array($filter, $validCategories)) {
                $sql .= " AND d.category = :filter_category";
                $params['filter_category'] = $filter;
            }
        }
        
        if (!empty($search)) {
            $sql .= " AND (d.title LIKE :search OR d.content LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total'] ?? 0;
            
        } catch (PDOException $e) {
            error_log("Error counting discussions: " . $e->getMessage());
            return 0;
        }
    }

    public function getDiscussionById($id)
    {
        $sql = "SELECT 
                    d.*,
                    u.username,
                    u.first_name,
                    u.last_name,
                    u.avatar,
                    u.badges
                FROM discussions d
                INNER JOIN users u ON d.author_id = u.id
                WHERE d.id = :id AND u.is_active = 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $discussion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($discussion) {
                $discussion['tags'] = json_decode($discussion['tags'], true) ?? [];
                $discussion['badges'] = json_decode($discussion['badges'], true) ?? [];
            }
            
            return $discussion;
            
        } catch (PDOException $e) {
            error_log("Error getting discussion by ID: " . $e->getMessage());
            return null;
        }
    }

    public function getDiscussionBySlug($slug)
    {
        $sql = "SELECT 
                    d.*,
                    u.username,
                    u.first_name,
                    u.last_name,
                    u.avatar,
                    u.badges
                FROM discussions d
                INNER JOIN users u ON d.author_id = u.id
                WHERE d.slug = :slug AND u.is_active = 1";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':slug', $slug);
            $stmt->execute();
            
            $discussion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($discussion) {
                $discussion['tags'] = json_decode($discussion['tags'], true) ?? [];
                $discussion['badges'] = json_decode($discussion['badges'], true) ?? [];
            }
            
            return $discussion;
            
        } catch (PDOException $e) {
            error_log("Error getting discussion by slug: " . $e->getMessage());
            return null;
        }
    }

    public function createDiscussion($data)
    {
        $sql = "INSERT INTO discussions (title, slug, content, author_id, category, tags)
                VALUES (:title, :slug, :content, :author_id, :category, :tags)";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':title', $data['title']);
            $stmt->bindValue(':slug', $data['slug']);
            $stmt->bindValue(':content', $data['content']);
            $stmt->bindValue(':author_id', $data['author_id'], PDO::PARAM_INT);
            $stmt->bindValue(':category', $data['category']);
            $stmt->bindValue(':tags', json_encode($data['tags'] ?? []));
            
            $stmt->execute();
            
            return $this->db->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Error creating discussion: " . $e->getMessage());
            return false;
        }
    }

    public function updateDiscussion($id, $data)
    {
        $fields = [];
        $params = ['id' => $id];
        
        $allowedFields = ['title', 'slug', 'content', 'category', 'tags', 'is_solved', 'is_locked'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = :$field";
                $params[$field] = $field === 'tags' ? json_encode($data[$field]) : $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $sql = "UPDATE discussions SET " . implode(', ', $fields) . " WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            
            foreach ($params as $key => $value) {
                if ($key === 'id') {
                    $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error updating discussion: " . $e->getMessage());
            return false;
        }
    }

    public function deleteDiscussion($id)
    {
        $sql = "DELETE FROM discussions WHERE id = :id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            
            return $stmt->execute();
            
        } catch (PDOException $e) {
            error_log("Error deleting discussion: " . $e->getMessage());
            return false;
        }
    }

    public function toggleLike($userId, $discussionId)
    {
        try {
            // Start transaction for data consistency
            $this->db->beginTransaction();
            
            // Get current likes count before any changes
            $currentCountStmt = $this->db->prepare("SELECT likes_count FROM discussions WHERE id = ?");
            $currentCountStmt->execute([$discussionId]);
            $currentData = $currentCountStmt->fetch(PDO::FETCH_ASSOC);
            $currentCount = $currentData ? (int)$currentData['likes_count'] : 0;
            
            // Check if user has already liked this discussion
            $checkStmt = $this->db->prepare("SELECT id FROM discussion_likes WHERE user_id = ? AND discussion_id = ?");
            $checkStmt->execute([$userId, $discussionId]);
            $existingLike = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingLike) {
                // User has already liked - remove like (unlike)
                $deleteStmt = $this->db->prepare("DELETE FROM discussion_likes WHERE user_id = ? AND discussion_id = ?");
                $deleteStmt->execute([$userId, $discussionId]);
                
                // Decrease likes count (ensure it doesn't go below 0)
                $newCount = max(0, $currentCount - 1);
                $updateStmt = $this->db->prepare("UPDATE discussions SET likes_count = ? WHERE id = ?");
                $updateStmt->execute([$newCount, $discussionId]);
                
                $action = 'unliked';
            } else {
                // User hasn't liked yet - add like
                $insertStmt = $this->db->prepare("INSERT INTO discussion_likes (user_id, discussion_id, created_at) VALUES (?, ?, NOW())");
                $insertStmt->execute([$userId, $discussionId]);
                
                // Increase likes count
                $newCount = $currentCount + 1;
                $updateStmt = $this->db->prepare("UPDATE discussions SET likes_count = ? WHERE id = ?");
                $updateStmt->execute([$newCount, $discussionId]);
                
                $action = 'liked';
            }
            
            // Commit transaction
            $this->db->commit();
            
            return $action;
            
        } catch (PDOException $e) {
            // Rollback transaction on error
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error toggling discussion like: " . $e->getMessage());
            return false;
        }
    }
    
    public function hasUserLiked($userId, $discussionId)
    {
        if (!$userId || !$discussionId) {
            return false;
        }
        
        try {
            $stmt = $this->db->prepare("SELECT id FROM discussion_likes WHERE user_id = ? AND discussion_id = ?");
            $stmt->execute([$userId, $discussionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
        } catch (PDOException $e) {
            error_log("Error checking user liked status: " . $e->getMessage());
            return false;
        }
    }
    
    public function getLikesCount($discussionId)
    {
        try {
            $stmt = $this->db->prepare("SELECT likes_count FROM discussions WHERE id = ?");
            $stmt->execute([$discussionId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['likes_count'] : 0;
        } catch (PDOException $e) {
            error_log("Error getting likes count: " . $e->getMessage());
            return 0;
        }
    }

    public function toggleBookmark($userId, $discussionId)
    {
        $sql = "SELECT id FROM discussion_bookmarks WHERE user_id = :user_id AND discussion_id = :discussion_id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
            $stmt->execute();
            
            $exists = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($exists) {
                $sql = "DELETE FROM discussion_bookmarks WHERE user_id = :user_id AND discussion_id = :discussion_id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
                $stmt->execute();
                
                return 'unbookmarked';
            } else {
                $sql = "INSERT INTO discussion_bookmarks (user_id, discussion_id) VALUES (:user_id, :discussion_id)";
                $stmt = $this->db->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
                $stmt->execute();
                
                return 'bookmarked';
            }
            
        } catch (PDOException $e) {
            error_log("Error toggling discussion bookmark: " . $e->getMessage());
            return false;
        }
    }

    public function hasUserBookmarked($userId, $discussionId)
    {
        $sql = "SELECT id FROM discussion_bookmarks WHERE user_id = :user_id AND discussion_id = :discussion_id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
            
        } catch (PDOException $e) {
            error_log("Error checking user bookmark: " . $e->getMessage());
            return false;
        }
    }

    public function getDiscussionReplies($discussionId, $page = 1, $limit = 20)
    {
        $offset = ($page - 1) * $limit;
        
        $sql = "SELECT 
                    dr.id,
                    dr.discussion_id,
                    dr.parent_id,
                    dr.content,
                    dr.is_solution,
                    dr.likes_count,
                    dr.created_at,
                    dr.updated_at,
                    u.username,
                    u.first_name,
                    u.last_name,
                    u.avatar,
                    u.badges
                FROM discussion_replies dr
                INNER JOIN users u ON dr.author_id = u.id
                WHERE dr.discussion_id = :discussion_id AND u.is_active = 1
                ORDER BY dr.is_solution DESC, dr.created_at ASC
                LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($replies as &$reply) {
                $reply['badges'] = json_decode($reply['badges'], true) ?? [];
            }
            
            return $replies;
            
        } catch (PDOException $e) {
            error_log("Error getting discussion replies: " . $e->getMessage());
            return [];
        }
    }

    public function generateSlug($title)
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        $originalSlug = $slug;
        $counter = 1;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    private function slugExists($slug)
    {
        $sql = "SELECT id FROM discussions WHERE slug = :slug";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':slug', $slug);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
            
        } catch (PDOException $e) {
            error_log("Error checking slug existence: " . $e->getMessage());
            return false;
        }
    }
}
