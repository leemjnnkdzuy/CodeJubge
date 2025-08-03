DROP DATABASE IF EXISTS `code_judge`;

CREATE DATABASE `code_judge` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `code_judge`;

-- ==================================================
-- BẢNG USERS - Quản lý người dùng
-- ==================================================
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin', 'moderator') DEFAULT 'user',
    `is_active` BOOLEAN DEFAULT TRUE,
    `avatar` LONGTEXT NULL,
    `bio` TEXT NULL,
    `github_url` VARCHAR(255) NULL,
    `linkedin_url` VARCHAR(255) NULL,
    `website_url` VARCHAR(255) NULL,
    `youtube_url` VARCHAR(255) NULL,
    `facebook_url` VARCHAR(255) NULL,
    `instagram_url` VARCHAR(255) NULL,
    `badges` JSON NULL DEFAULT '[]',
    `total_problems_solved` INT DEFAULT 0,
    `total_submissions` INT DEFAULT 0,
    `rating` INT DEFAULT -1,
    `login_streak` INT DEFAULT 0,
    `last_login_date` DATE NULL,
    `last_login` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX `idx_email` (`email`),
    INDEX `idx_username` (`username`),
    INDEX `idx_role` (`role`),
    INDEX `idx_rating` (`rating`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG PROBLEMS - Quản lý bài tập
-- ==================================================
CREATE TABLE `problems` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `slug` VARCHAR(200) NOT NULL UNIQUE,
    `description` TEXT NOT NULL,
    `input_format` TEXT NULL,
    `output_format` TEXT NULL,
    `constraints` TEXT NULL,
    `sample_input` TEXT NULL,
    `sample_output` TEXT NULL,
    `difficulty` ENUM('easy', 'medium', 'hard') DEFAULT 'easy',
    `category` VARCHAR(50) NULL,
    `problem_types` JSON NOT NULL DEFAULT '[]',
    `time_limit` INT DEFAULT 1000,
    `memory_limit` INT DEFAULT 256,
    `created_by` INT NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `solved_count` INT DEFAULT 0,
    `attempt_count` INT DEFAULT 0,
    `acceptance_rate` DECIMAL(5,2) GENERATED ALWAYS AS (
        CASE 
            WHEN attempt_count > 0 THEN ROUND((solved_count / attempt_count) * 100, 2)
            ELSE 0 
        END
    ) STORED,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_difficulty` (`difficulty`),
    INDEX `idx_category` (`category`),
    INDEX `idx_slug` (`slug`),
    INDEX `idx_created_by` (`created_by`),
    INDEX `idx_acceptance_rate` (`acceptance_rate`),
    INDEX `idx_solved_count` (`solved_count`),
    FULLTEXT INDEX `idx_search` (`title`, `description`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG TEST_CASES - Test cases cho từng bài tập
-- ==================================================
CREATE TABLE `test_cases` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `problem_id` INT NOT NULL,
    `input` TEXT NOT NULL,
    `expected_output` TEXT NOT NULL,
    `is_sample` BOOLEAN DEFAULT FALSE,
    `points` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`problem_id`) REFERENCES `problems`(`id`) ON DELETE CASCADE,
    INDEX `idx_problem_id` (`problem_id`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG SUBMISSIONS - Lưu trữ bài nộp
-- ==================================================
CREATE TABLE `submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `problem_id` INT NOT NULL,
    `language` VARCHAR(20) NOT NULL,
    `code` TEXT NOT NULL,
    `status` ENUM('pending', 'running', 'accepted', 'wrong_answer', 'time_limit', 'memory_limit', 'runtime_error', 'compile_error') DEFAULT 'pending',
    `runtime` INT NULL,
    `memory_used` INT NULL,
    `score` DECIMAL(5,2) DEFAULT 0.00,
    `test_cases_passed` INT DEFAULT 0,
    `total_test_cases` INT DEFAULT 0,
    `error_message` TEXT NULL,
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`problem_id`) REFERENCES `problems`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_problem_id` (`problem_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_submitted_at` (`submitted_at`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG USER_PROBLEMS - Quan hệ user và problem đã giải
-- ==================================================
CREATE TABLE `user_problems` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `problem_id` INT NOT NULL,
    `status` ENUM('attempted', 'solved') DEFAULT 'attempted',
    `best_submission_id` INT NULL,
    `attempts` INT DEFAULT 0,
    `first_solved_at` TIMESTAMP NULL,
    `last_attempt_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `unique_user_problem` (`user_id`, `problem_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`problem_id`) REFERENCES `problems`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`best_submission_id`) REFERENCES `submissions`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_problem_id` (`problem_id`),
    INDEX `idx_status` (`status`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG CONTESTS - Quản lý cuộc thi
-- ==================================================
CREATE TABLE `contests` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT NULL,
    `start_time` TIMESTAMP NOT NULL,
    `end_time` TIMESTAMP NOT NULL,
    `duration` INT NOT NULL,
    `created_by` INT NOT NULL,
    `is_public` BOOLEAN DEFAULT TRUE,
    `max_participants` INT NULL,
    `registration_deadline` TIMESTAMP NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_start_time` (`start_time`),
    INDEX `idx_end_time` (`end_time`),
    INDEX `idx_created_by` (`created_by`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG CONTEST_PROBLEMS - Problems trong contest
-- ==================================================
CREATE TABLE `contest_problems` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `contest_id` INT NOT NULL,
    `problem_id` INT NOT NULL,
    `points` INT DEFAULT 100,
    `order_index` INT DEFAULT 0,
    
    UNIQUE KEY `unique_contest_problem` (`contest_id`, `problem_id`),
    FOREIGN KEY (`contest_id`) REFERENCES `contests`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`problem_id`) REFERENCES `problems`(`id`) ON DELETE CASCADE,
    INDEX `idx_contest_id` (`contest_id`),
    INDEX `idx_problem_id` (`problem_id`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG CONTEST_PARTICIPANTS - Người tham gia contest
-- ==================================================
CREATE TABLE `contest_participants` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `contest_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `registered_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `started_at` TIMESTAMP NULL,
    `finished_at` TIMESTAMP NULL,
    `total_score` DECIMAL(8,2) DEFAULT 0.00,
    `rank` INT NULL,
    
    UNIQUE KEY `unique_contest_participant` (`contest_id`, `user_id`),
    FOREIGN KEY (`contest_id`) REFERENCES `contests`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_contest_id` (`contest_id`),
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_total_score` (`total_score`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG SESSIONS - Quản lý phiên đăng nhập
-- ==================================================
CREATE TABLE `sessions` (
    `id` VARCHAR(128) PRIMARY KEY,
    `user_id` INT NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `payload` TEXT NOT NULL,
    `last_activity` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG NOTIFICATIONS - Thông báo
-- ==================================================
CREATE TABLE `notifications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `type` VARCHAR(50) NOT NULL,
    `title` VARCHAR(200) NOT NULL,
    `message` TEXT NOT NULL,
    `data` JSON NULL,
    `is_read` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB;

-- ==================================================
-- TẠO ADMIN USER MẶC ĐỊNH
-- ==================================================
INSERT INTO `users` (
    `first_name`, 
    `last_name`, 
    `username`, 
    `email`, 
    `password`, 
    `role`, 
    `created_at`, 
    `updated_at`
) VALUES (
    'Admin',
    'System',
    'admin',
    'admin@codejudge.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin',
    NOW(),
    NOW()
);

-- ==================================================
-- TẠO MỘT SỐ BÀI TẬP MẪU
-- ==================================================
INSERT INTO `problems` (
    `title`,
    `slug`, 
    `description`,
    `input_format`,
    `output_format`,
    `sample_input`,
    `sample_output`,
    `difficulty`,
    `category`,
    `problem_types`,
    `created_by`
) VALUES 
(
    'Hello World',
    'hello-world',
    'Viết chương trình in ra "Hello, World!"',
    'Không có input',
    'In ra "Hello, World!"',
    '',
    'Hello, World!',
    'easy',
    'Beginner',
    '["String"]',
    1
),
(
    'Tổng hai số',
    'sum-two-numbers',
    'Cho hai số nguyên a và b, tính tổng a + b',
    'Dòng đầu chứa hai số nguyên a và b (-10^9 ≤ a, b ≤ 10^9)',
    'In ra tổng của a và b',
    '3 5',
    '8',
    'easy',
    'Math',
    '["Math", "Array"]',
    1
),
(
    'Fibonacci',
    'fibonacci-sequence',
    'Tính số Fibonacci thứ n',
    'Một số nguyên n (1 ≤ n ≤ 50)',
    'In ra số Fibonacci thứ n',
    '10',
    '55',
    'medium',
    'Dynamic Programming',
    '["Dynamic_Programming", "Math", "Recursion"]',
    1
),
(
    'Two Sum',
    'two-sum',
    'Cho một mảng số nguyên và một số target, tìm hai chỉ số sao cho tổng của hai phần tử tại hai chỉ số đó bằng target',
    'Dòng đầu: n và target. Dòng thứ hai: n số nguyên',
    'In ra hai chỉ số (0-indexed)',
    '4 9\n2 7 11 15',
    '0 1',
    'easy',
    'Array',
    '["Array", "Hash_Table", "Two_Pointers"]',
    1
),
(
    'Longest Substring Without Repeating Characters',
    'longest-substring-without-repeating',
    'Tìm độ dài của chuỗi con dài nhất không có ký tự lặp lại',
    'Một chuỗi s',
    'In ra độ dài của chuỗi con dài nhất',
    'abcabcbb',
    '3',
    'medium',
    'String',
    '["String", "Sliding_Window", "Hash_Table"]',
    1
),
(
    'Median of Two Sorted Arrays',
    'median-two-sorted-arrays',
    'Tìm median của hai mảng đã sắp xếp',
    'Dòng 1: n1 n2. Dòng 2: n1 số của mảng 1. Dòng 3: n2 số của mảng 2',
    'In ra median',
    '2 1\n1 3\n2',
    '2.0',
    'hard',
    'Array',
    '["Array", "Binary_Search", "Divide_and_Conquer"]',
    1
);

-- ==================================================
-- TẠO TEST CASES CHO CÁC BÀI TẬP MẪU
-- ==================================================
-- Test cases cho Hello World
INSERT INTO `test_cases` (`problem_id`, `input`, `expected_output`, `is_sample`) VALUES
(1, '', 'Hello, World!', TRUE);

-- Test cases cho Tổng hai số
INSERT INTO `test_cases` (`problem_id`, `input`, `expected_output`, `is_sample`) VALUES
(2, '3 5', '8', TRUE),
(2, '0 0', '0', FALSE),
(2, '-1 1', '0', FALSE),
(2, '1000000000 1000000000', '2000000000', FALSE);

-- Test cases cho Fibonacci
INSERT INTO `test_cases` (`problem_id`, `input`, `expected_output`, `is_sample`) VALUES
(3, '1', '1', FALSE),
(3, '2', '1', FALSE),
(3, '5', '5', FALSE),
(3, '10', '55', TRUE);

-- Test cases cho Two Sum
INSERT INTO `test_cases` (`problem_id`, `input`, `expected_output`, `is_sample`) VALUES
(4, '4 9\n2 7 11 15', '0 1', TRUE),
(4, '3 6\n3 2 4', '1 2', FALSE),
(4, '2 6\n3 3', '0 1', FALSE);

-- Test cases cho Longest Substring
INSERT INTO `test_cases` (`problem_id`, `input`, `expected_output`, `is_sample`) VALUES
(5, 'abcabcbb', '3', TRUE),
(5, 'bbbbb', '1', FALSE),
(5, 'pwwkew', '3', FALSE),
(5, '', '0', FALSE);

-- Test cases cho Median
INSERT INTO `test_cases` (`problem_id`, `input`, `expected_output`, `is_sample`) VALUES
(6, '2 1\n1 3\n2', '2.0', TRUE),
(6, '2 2\n1 2\n3 4', '2.5', FALSE);

-- ==================================================
-- STORED PROCEDURES
-- ==================================================

DELIMITER //

-- Procedure cập nhật thống kê user sau khi submit
CREATE PROCEDURE UpdateUserStats(
    IN p_user_id INT,
    IN p_problem_id INT,
    IN p_status VARCHAR(20)
)
BEGIN
    DECLARE v_is_first_solve BOOLEAN DEFAULT FALSE;
    
    IF p_status = 'accepted' THEN
        SELECT COUNT(*) = 0 INTO v_is_first_solve
        FROM user_problems 
        WHERE user_id = p_user_id AND problem_id = p_problem_id AND status = 'solved';
        
        INSERT INTO user_problems (user_id, problem_id, status, attempts, first_solved_at)
        VALUES (p_user_id, p_problem_id, 'solved', 1, NOW())
        ON DUPLICATE KEY UPDATE
            status = 'solved',
            attempts = attempts + 1,
            first_solved_at = COALESCE(first_solved_at, NOW());
        
        IF v_is_first_solve THEN
            UPDATE users SET 
                total_problems_solved = total_problems_solved + 1,
                total_submissions = total_submissions + 1
            WHERE id = p_user_id;
            
            UPDATE problems SET 
                solved_count = solved_count + 1,
                attempt_count = attempt_count + 1
            WHERE id = p_problem_id;
        ELSE
            UPDATE users SET total_submissions = total_submissions + 1 WHERE id = p_user_id;
            UPDATE problems SET attempt_count = attempt_count + 1 WHERE id = p_problem_id;
        END IF;
    ELSE
        INSERT INTO user_problems (user_id, problem_id, status, attempts)
        VALUES (p_user_id, p_problem_id, 'attempted', 1)
        ON DUPLICATE KEY UPDATE
            attempts = attempts + 1;
            
        UPDATE users SET total_submissions = total_submissions + 1 WHERE id = p_user_id;
        UPDATE problems SET attempt_count = attempt_count + 1 WHERE id = p_problem_id;
    END IF;
END //

DELIMITER ;

-- ==================================================
-- VIEWS
-- ==================================================

-- View leaderboard
CREATE VIEW leaderboard_view AS
SELECT 
    u.id,
    u.username,
    u.first_name,
    u.last_name,
    u.total_problems_solved,
    u.total_submissions,
    u.rating,
    RANK() OVER (ORDER BY u.total_problems_solved DESC, u.rating DESC) as rank_position
FROM users u
WHERE u.is_active = 1
ORDER BY u.total_problems_solved DESC, u.rating DESC;

-- View problem statistics
CREATE VIEW problem_stats_view AS
SELECT 
    p.id,
    p.title,
    p.difficulty,
    p.solved_count,
    p.attempt_count,
    CASE 
        WHEN p.attempt_count > 0 THEN ROUND((p.solved_count / p.attempt_count) * 100, 2)
        ELSE 0 
    END as success_rate
FROM problems p
WHERE p.is_active = 1;

-- ==================================================
-- TRIGGERS
-- ==================================================

DELIMITER //

-- Trigger cập nhật updated_at khi update users
CREATE TRIGGER users_updated_at_trigger
    BEFORE UPDATE ON users
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //

-- Trigger cập nhật updated_at khi update problems
CREATE TRIGGER problems_updated_at_trigger
    BEFORE UPDATE ON problems
    FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //

DELIMITER ;

-- ==================================================
-- INDEXES BỔ SUNG CHO PERFORMANCE
-- ==================================================

-- Composite indexes cho queries thường dùng
CREATE INDEX idx_user_problems_user_status ON user_problems(user_id, status);
CREATE INDEX idx_submissions_user_problem ON submissions(user_id, problem_id);
CREATE INDEX idx_submissions_status_time ON submissions(status, submitted_at);

-- ==================================================
-- BẢNG DISCUSSIONS - Quản lý thảo luận
-- ==================================================
CREATE TABLE `discussions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL UNIQUE,
    `content` TEXT NOT NULL,
    `author_id` INT NOT NULL,
    `category` VARCHAR(50) DEFAULT 'general',
    `tags` JSON NOT NULL DEFAULT '[]',
    `is_pinned` BOOLEAN DEFAULT FALSE,
    `is_solved` BOOLEAN DEFAULT FALSE,
    `is_locked` BOOLEAN DEFAULT FALSE,
    `likes_count` INT DEFAULT 0,
    `replies_count` INT DEFAULT 0,
    `last_reply_at` TIMESTAMP NULL,
    `last_reply_by` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`last_reply_by`) REFERENCES `users`(`id`) ON DELETE SET NULL,
    CONSTRAINT `chk_category` CHECK (`category` IN ('general', 'problems', 'competitions', 'learning_resources', 'feedback_and_suggestions', 'questions_and_answers', 'events', 'questions', 'other', 'algorithm', 'data-structure', 'math', 'beginner', 'contest', 'help')),
    INDEX `idx_author_id` (`author_id`),
    INDEX `idx_category` (`category`),
    INDEX `idx_is_pinned` (`is_pinned`),
    INDEX `idx_is_solved` (`is_solved`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_last_reply_at` (`last_reply_at`),
    INDEX `idx_likes_count` (`likes_count`),
    FULLTEXT INDEX `idx_search_discussions` (`title`, `content`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG DISCUSSION_REPLIES - Phản hồi thảo luận
-- ==================================================
CREATE TABLE `discussion_replies` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `discussion_id` INT NOT NULL,
    `parent_id` INT NULL,
    `author_id` INT NOT NULL,
    `content` TEXT NOT NULL,
    `is_solution` BOOLEAN DEFAULT FALSE,
    `likes_count` INT DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (`discussion_id`) REFERENCES `discussions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`parent_id`) REFERENCES `discussion_replies`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_discussion_id` (`discussion_id`),
    INDEX `idx_parent_id` (`parent_id`),
    INDEX `idx_author_id` (`author_id`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_is_solution` (`is_solution`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG DISCUSSION_LIKES - Lượt thích thảo luận
-- ==================================================
CREATE TABLE `discussion_likes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `discussion_id` INT NULL,
    `reply_id` INT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `unique_discussion_like` (`user_id`, `discussion_id`),
    UNIQUE KEY `unique_reply_like` (`user_id`, `reply_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`discussion_id`) REFERENCES `discussions`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`reply_id`) REFERENCES `discussion_replies`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_discussion_id` (`discussion_id`),
    INDEX `idx_reply_id` (`reply_id`)
) ENGINE=InnoDB;

-- ==================================================
-- BẢNG DISCUSSION_BOOKMARKS - Bookmark thảo luận
-- ==================================================
CREATE TABLE `discussion_bookmarks` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `discussion_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY `unique_discussion_bookmark` (`user_id`, `discussion_id`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`discussion_id`) REFERENCES `discussions`(`id`) ON DELETE CASCADE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_discussion_id` (`discussion_id`)
) ENGINE=InnoDB;

-- ==================================================
-- STORED PROCEDURES CHO DISCUSSIONS
-- ==================================================

DELIMITER //

-- Procedure tạo thảo luận mới
CREATE PROCEDURE CreateDiscussion(
    IN p_title VARCHAR(255),
    IN p_slug VARCHAR(255),
    IN p_content TEXT,
    IN p_author_id INT,
    IN p_category VARCHAR(50),
    IN p_tags JSON
)
BEGIN
    -- Validate category
    IF p_category NOT IN ('general', 'problems', 'competitions', 'learning_resources', 'feedback_and_suggestions', 'questions_and_answers', 'events', 'questions', 'other', 'algorithm', 'data-structure', 'math', 'beginner', 'contest', 'help') THEN
        SET p_category = 'general';
    END IF;
    
    INSERT INTO discussions (title, slug, content, author_id, category, tags)
    VALUES (p_title, p_slug, p_content, p_author_id, p_category, p_tags);
    
    SELECT LAST_INSERT_ID() as discussion_id;
END //

-- Procedure cập nhật thống kê khi có reply mới
CREATE PROCEDURE AddDiscussionReply(
    IN p_discussion_id INT,
    IN p_parent_id INT,
    IN p_author_id INT,
    IN p_content TEXT
)
BEGIN
    DECLARE reply_id INT;
    
    INSERT INTO discussion_replies (discussion_id, parent_id, author_id, content)
    VALUES (p_discussion_id, p_parent_id, p_author_id, p_content);
    
    SET reply_id = LAST_INSERT_ID();
    
    UPDATE discussions SET 
        replies_count = replies_count + 1,
        last_reply_at = NOW(),
        last_reply_by = p_author_id
    WHERE id = p_discussion_id;
    
    SELECT reply_id;
END //

-- Procedure toggle like thảo luận
CREATE PROCEDURE ToggleDiscussionLike(
    IN p_user_id INT,
    IN p_discussion_id INT
)
BEGIN
    DECLARE like_exists INT DEFAULT 0;
    
    SELECT COUNT(*) INTO like_exists
    FROM discussion_likes
    WHERE user_id = p_user_id AND discussion_id = p_discussion_id;
    
    IF like_exists > 0 THEN
        DELETE FROM discussion_likes 
        WHERE user_id = p_user_id AND discussion_id = p_discussion_id;
        
        UPDATE discussions SET likes_count = likes_count - 1 WHERE id = p_discussion_id;
        SELECT 'unliked' as action;
    ELSE
        INSERT INTO discussion_likes (user_id, discussion_id) 
        VALUES (p_user_id, p_discussion_id);
        
        UPDATE discussions SET likes_count = likes_count + 1 WHERE id = p_discussion_id;
        SELECT 'liked' as action;
    END IF;
END //

DELIMITER ;

-- ==================================================
-- VIEWS CHO DISCUSSIONS
-- ==================================================

-- View discussions với thông tin tác giả
CREATE VIEW discussions_with_author AS
SELECT 
    d.id,
    d.title,
    d.slug,
    d.content,
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
    lr.username as last_reply_username
FROM discussions d
INNER JOIN users u ON d.author_id = u.id
LEFT JOIN users lr ON d.last_reply_by = lr.id
WHERE u.is_active = 1;

-- View top discussions
CREATE VIEW top_discussions AS
SELECT 
    d.*,
    u.username,
    u.first_name,
    u.last_name,
    u.avatar,
    (d.likes_count * 2 + d.replies_count) as popularity_score
FROM discussions d
INNER JOIN users u ON d.author_id = u.id
WHERE u.is_active = 1
ORDER BY popularity_score DESC;

-- ==================================================
-- TRIGGERS CHO DISCUSSIONS
-- ==================================================

DELIMITER //

-- Trigger cập nhật thống kê khi xóa reply
CREATE TRIGGER discussion_reply_delete_trigger
    AFTER DELETE ON discussion_replies
    FOR EACH ROW
BEGIN
    UPDATE discussions SET 
        replies_count = replies_count - 1
    WHERE id = OLD.discussion_id;
END //

-- Trigger cập nhật thống kê khi xóa like
CREATE TRIGGER discussion_like_delete_trigger
    AFTER DELETE ON discussion_likes
    FOR EACH ROW
BEGIN
    IF OLD.discussion_id IS NOT NULL THEN
        UPDATE discussions SET 
            likes_count = likes_count - 1
        WHERE id = OLD.discussion_id;
    END IF;
    
    IF OLD.reply_id IS NOT NULL THEN
        UPDATE discussion_replies SET 
            likes_count = likes_count - 1
        WHERE id = OLD.reply_id;
    END IF;
END //

DELIMITER ;

-- ==================================================
-- DỮ LIỆU MẪU CHO DISCUSSIONS
-- ==================================================

-- Tạo một số thảo luận mẫu
INSERT INTO discussions (title, slug, content, author_id, category, tags, is_pinned, likes_count, replies_count) VALUES
(
    'Chào mừng đến với diễn đàn CodeJudge!',
    'welcome-to-codejudge-forum',
    'Chào mừng các bạn đến với diễn đàn thảo luận của CodeJudge! Đây là nơi các bạn có thể thảo luận về các bài toán lập trình, chia sẻ kinh nghiệm và học hỏi lẫn nhau.\n\nMột số quy tắc cơ bản:\n1. Tôn trọng lẫn nhau\n2. Không spam\n3. Sử dụng tiêu đề mô tả rõ ràng\n4. Tag đúng category cho bài viết',
    1,
    'general',
    '["announcement", "rules", "welcome"]',
    TRUE,
    25,
    5
),
(
    'Làm thế nào để tối ưu hóa thuật toán sắp xếp?',
    'how-to-optimize-sorting-algorithms',
    'Mình đang tìm hiểu về các thuật toán sắp xếp và muốn biết cách tối ưu hóa chúng. Hiện tại mình đang sử dụng Quick Sort nhưng trong một số trường hợp performance không được tốt lắm.\n\nCó ai có kinh nghiệm về việc này không? Chia sẻ với mình nhé!',
    1,
    'problems',
    '["sorting", "optimization", "quicksort", "performance"]',
    FALSE,
    12,
    8
),
(
    'Binary Search Tree vs AVL Tree - Khi nào nên dùng?',
    'bst-vs-avl-tree-when-to-use',
    'Mình đang học về cấu trúc dữ liệu cây và băn khoăn không biết khi nào nên sử dụng BST thông thường và khi nào nên sử dụng AVL Tree.\n\nCó ai có thể giải thích rõ hơn về trade-offs giữa hai loại cây này không?',
    1,
    'learning_resources',
    '["binary-search-tree", "avl-tree", "balanced-tree", "data-structure"]',
    FALSE,
    8,
    6
),
(
    'Dynamic Programming - Những pattern cơ bản cần biết',
    'dynamic-programming-basic-patterns',
    'Mình vừa bắt đầu học Dynamic Programming và thấy có rất nhiều dạng bài khác nhau. Có ai có thể chia sẻ những pattern cơ bản nhất mà người mới học DP cần nắm vững không?\n\nCảm ơn mọi người!',
    1,
    'questions_and_answers',
    '["dynamic-programming", "patterns", "beginner", "tutorial"]',
    FALSE,
    18,
    12
),
(
    'Cách debug hiệu quả khi code thi đấu',
    'effective-debugging-competitive-programming',
    'Trong lúc thi đấu, việc debug code rất quan trọng nhưng cũng tốn thời gian. Mình muốn hỏi các cao thủ có tips gì để debug nhanh và hiệu quả không?\n\nShare kinh nghiệm với mình nhé!',
    1,
    'competitions',
    '["debugging", "competitive-programming", "tips", "contest"]',
    FALSE,
    15,
    9
),
(
    'Thông báo: Cuộc thi lập trình CodeJudge Championship 2025',
    'codejudge-championship-2025-announcement',
    'Chúng tôi vui mừng thông báo về cuộc thi lập trình lớn nhất năm - CodeJudge Championship 2025!\n\n🏆 Giải thưởng lên đến 50 triệu VNĐ\n📅 Thời gian: 15/08/2025 - 17/08/2025\n👥 Mở rồng cho tất cả thành viên\n\nĐăng ký ngay tại link: codejudge.com/championship2025',
    1,
    'events',
    '["championship", "contest", "2025", "announcement"]',
    TRUE,
    45,
    23
),
(
    'Góp ý cải thiện hệ thống judge',
    'feedback-improve-judge-system',
    'Mình có một số góp ý để cải thiện hệ thống judge của CodeJudge:\n\n1. Thêm support cho Python 3.11\n2. Tăng memory limit cho một số bài\n3. Thêm partial scoring\n4. Custom checker cho bài output không unique\n\nMọi người có ý kiến gì không?',
    1,
    'feedback_and_suggestions',
    '["judge-system", "improvement", "features", "feedback"]',
    FALSE,
    22,
    15
),
(
    'Tài nguyên học thuật toán miễn phí tốt nhất',
    'best-free-algorithm-learning-resources',
    'Chia sẻ với mọi người một số tài nguyên học thuật toán miễn phí mà mình đã sử dụng:\n\n📚 Sách: Introduction to Algorithms (CLRS)\n🌐 Website: GeeksforGeeks, LeetCode\n📺 YouTube: Abdul Bari, MIT OpenCourseWare\n💻 Practice: CodeJudge, Codeforces\n\nMọi người có thêm gợi ý nào không?',
    1,
    'learning_resources',
    '["algorithms", "free-resources", "books", "websites", "learning"]',
    FALSE,
    67,
    31
);

-- Tạo một số replies mẫu
INSERT INTO discussion_replies (discussion_id, author_id, content) VALUES
(1, 1, 'Cảm ơn admin đã tạo ra diễn đàn tuyệt vời này! Mình rất mong được học hỏi từ mọi người.'),
(2, 1, 'Về Quick Sort, bạn có thể thử implement 3-way partitioning để xử lý trường hợp có nhiều phần tử trùng lặp. Ngoài ra, có thể kết hợp với Insertion Sort cho mảng nhỏ.'),
(3, 1, 'BST thông thường đơn giản hơn và phù hợp khi dữ liệu insert theo thứ tự random. AVL Tree tốt hơn khi cần đảm bảo worst-case O(log n) cho tất cả operations.'),
(4, 1, 'Một số pattern cơ bản: 1D DP (Fibonacci, Climbing Stairs), 2D DP (Grid problems), Subsequence DP (LCS, LIS). Bắt đầu từ những bài đơn giản nhé!'),
(5, 1, 'Tip quan trọng nhất: test với các test case edge cases ngay từ đầu. Và luôn in ra intermediate results để check logic.');

-- ==================================================
-- INDEXES BỔ SUNG CHO DISCUSSIONS
-- ==================================================

-- Composite indexes cho queries thường dùng
CREATE INDEX idx_discussions_category_created ON discussions(category, created_at DESC);
CREATE INDEX idx_discussions_pinned_created ON discussions(is_pinned DESC, created_at DESC);
CREATE INDEX idx_discussions_popularity ON discussions(likes_count DESC, replies_count DESC);
CREATE INDEX idx_discussion_replies_discussion_created ON discussion_replies(discussion_id, created_at DESC);
