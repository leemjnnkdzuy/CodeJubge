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
-- HOÀN THÀNH
-- ==================================================
-- Database đã được tạo thành công với:
-- ✓ Bảng users với đầy đủ thông tin
-- ✓ Bảng problems và test cases
-- ✓ Bảng submissions và user_problems
-- ✓ Bảng contests và participants
-- ✓ Bảng sessions và notifications
-- ✓ Admin user mặc định (admin/password)
-- ✓ Stored procedures cho cập nhật thống kê
-- ✓ Views cho leaderboard và statistics
-- ✓ Triggers tự động cập nhật timestamps
-- ✓ Indexes tối ưu performance