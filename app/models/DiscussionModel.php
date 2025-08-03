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
            $params['current_user_id'] = null;
            $params['current_user_id_like'] = null;
        }
        
        if ($filter !== 'all' && !empty($filter)) {
            global $DISCUSS_CATEGORIES;
            
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
        
        if ($filter !== 'all' && !empty($filter)) {
            global $DISCUSS_CATEGORIES;
            
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
            $this->db->beginTransaction();
            
            $currentCountStmt = $this->db->prepare("SELECT likes_count FROM discussions WHERE id = ?");
            $currentCountStmt->execute([$discussionId]);
            $currentData = $currentCountStmt->fetch(PDO::FETCH_ASSOC);
            $currentCount = $currentData ? (int)$currentData['likes_count'] : 0;
            
            $checkStmt = $this->db->prepare("SELECT id FROM discussion_likes WHERE user_id = ? AND discussion_id = ?");
            $checkStmt->execute([$userId, $discussionId]);
            $existingLike = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingLike) {
                $deleteStmt = $this->db->prepare("DELETE FROM discussion_likes WHERE user_id = ? AND discussion_id = ?");
                $deleteStmt->execute([$userId, $discussionId]);
                
                $newCount = max(0, $currentCount - 1);
                $updateStmt = $this->db->prepare("UPDATE discussions SET likes_count = ? WHERE id = ?");
                $updateStmt->execute([$newCount, $discussionId]);
                
                $action = 'unliked';
            } else {
                $insertStmt = $this->db->prepare("INSERT INTO discussion_likes (user_id, discussion_id, created_at) VALUES (?, ?, NOW())");
                $insertStmt->execute([$userId, $discussionId]);
                
                $newCount = $currentCount + 1;
                $updateStmt = $this->db->prepare("UPDATE discussions SET likes_count = ? WHERE id = ?");
                $updateStmt->execute([$newCount, $discussionId]);
                
                $action = 'liked';
            }
            
            $this->db->commit();
            
            return $action;
            
        } catch (PDOException $e) {
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
        $currentUserId = $_SESSION['user_id'] ?? null;
        
        $sql = "SELECT 
                    dr.id,
                    dr.discussion_id,
                    dr.parent_id,
                    dr.author_id,
                    dr.content,
                    dr.is_solution,
                    dr.likes_count,
                    dr.created_at,
                    dr.updated_at,
                    u.username,
                    u.first_name,
                    u.last_name,
                    u.avatar,
                    u.badges,
                    CASE WHEN dl.id IS NOT NULL THEN 1 ELSE 0 END as user_liked
                FROM discussion_replies dr
                INNER JOIN users u ON dr.author_id = u.id
                LEFT JOIN discussion_likes dl ON dr.id = dl.reply_id AND dl.user_id = :current_user_id
                WHERE dr.discussion_id = :discussion_id AND u.is_active = 1
                ORDER BY dr.is_solution DESC, dr.created_at ASC
                LIMIT :limit OFFSET :offset";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
            $stmt->bindValue(':current_user_id', $currentUserId, PDO::PARAM_INT);
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
    
    public function createReply($discussionId, $authorId, $content, $parentId = null)
    {
        $sql = "INSERT INTO discussion_replies (discussion_id, author_id, content, parent_id, created_at, updated_at)
                VALUES (:discussion_id, :author_id, :content, :parent_id, NOW(), NOW())";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
            $stmt->bindValue(':author_id', $authorId, PDO::PARAM_INT);
            $stmt->bindValue(':content', $content);
            $stmt->bindValue(':parent_id', $parentId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                $replyId = $this->db->lastInsertId();
                
                $updateSql = "UPDATE discussions SET 
                             replies_count = replies_count + 1,
                             last_reply_by = :author_id,
                             last_reply_at = NOW(),
                             updated_at = NOW()
                             WHERE id = :discussion_id";
                
                $updateStmt = $this->db->prepare($updateSql);
                $updateStmt->bindValue(':author_id', $authorId, PDO::PARAM_INT);
                $updateStmt->bindValue(':discussion_id', $discussionId, PDO::PARAM_INT);
                $updateStmt->execute();
                
                return $replyId;
            }
            
            return false;
            
        } catch (PDOException $e) {
            error_log("Error creating reply: " . $e->getMessage());
            return false;
        }
    }
    
    public function toggleReplyLike($userId, $replyId)
    {
        try {
            $this->db->beginTransaction();
            
            $currentCountStmt = $this->db->prepare("SELECT likes_count FROM discussion_replies WHERE id = ?");
            $currentCountStmt->execute([$replyId]);
            $currentData = $currentCountStmt->fetch(PDO::FETCH_ASSOC);
            $currentCount = $currentData ? (int)$currentData['likes_count'] : 0;
            
            $checkStmt = $this->db->prepare("SELECT id FROM discussion_likes WHERE user_id = ? AND reply_id = ?");
            $checkStmt->execute([$userId, $replyId]);
            $existingLike = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingLike) {
                $deleteStmt = $this->db->prepare("DELETE FROM discussion_likes WHERE user_id = ? AND reply_id = ?");
                $deleteStmt->execute([$userId, $replyId]);
                $newCount = max(0, $currentCount - 1);
                $updateStmt = $this->db->prepare("UPDATE discussion_replies SET likes_count = ? WHERE id = ?");
                $updateStmt->execute([$newCount, $replyId]);
                
                $action = 'unliked';
            } else {
                $insertStmt = $this->db->prepare("INSERT INTO discussion_likes (user_id, reply_id, created_at) VALUES (?, ?, NOW())");
                $insertStmt->execute([$userId, $replyId]);
                $newCount = $currentCount + 1;
                $updateStmt = $this->db->prepare("UPDATE discussion_replies SET likes_count = ? WHERE id = ?");
                $updateStmt->execute([$newCount, $replyId]);
                
                $action = 'liked';
            }
            
            $this->db->commit();
            
            return [
                'action' => $action,
                'likes_count' => $newCount
            ];
            
        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error toggling reply like: " . $e->getMessage());
            return false;
        }
    }
    
    public function markReplyAsSolution($userId, $replyId)
    {
        try {
            $sql = "SELECT dr.discussion_id, d.author_id as discussion_author_id
                    FROM discussion_replies dr
                    INNER JOIN discussions d ON dr.discussion_id = d.id
                    WHERE dr.id = :reply_id";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':reply_id', $replyId, PDO::PARAM_INT);
            $stmt->execute();
            
            $info = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$info) {
                return false;
            }
            
            $userRole = $_SESSION['role'] ?? 'user';
            if ($userId != $info['discussion_author_id'] && !in_array($userRole, ['admin', 'moderator'])) {
                return false;
            }
            
            $unmarkSql = "UPDATE discussion_replies SET is_solution = 0 WHERE discussion_id = :discussion_id";
            $unmarkStmt = $this->db->prepare($unmarkSql);
            $unmarkStmt->bindValue(':discussion_id', $info['discussion_id'], PDO::PARAM_INT);
            $unmarkStmt->execute();
            
            $markSql = "UPDATE discussion_replies SET is_solution = 1 WHERE id = :reply_id";
            $markStmt = $this->db->prepare($markSql);
            $markStmt->bindValue(':reply_id', $replyId, PDO::PARAM_INT);
            $markStmt->execute();
            
            $updateDiscussionSql = "UPDATE discussions SET is_solved = 1 WHERE id = :discussion_id";
            $updateDiscussionStmt = $this->db->prepare($updateDiscussionSql);
            $updateDiscussionStmt->bindValue(':discussion_id', $info['discussion_id'], PDO::PARAM_INT);
            $updateDiscussionStmt->execute();
            
            return true;
            
        } catch (PDOException $e) {
            error_log("Error marking reply as solution: " . $e->getMessage());
            return false;
        }
    }
}
