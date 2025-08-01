<?php
require_once 'config/config.php';
require_once 'config/databaseConnect.php';

try {
    $db = Database::getInstance();
    
    $existingCount = $db->selectOne("SELECT COUNT(*) as count FROM users WHERE username LIKE 'test_user_%'");
    
    if ($existingCount['count'] > 0) {
        echo "Test users already exist. Total: " . $existingCount['count'] . "\n";
        echo "Deleting existing test users...\n";
        $db->delete("DELETE FROM users WHERE username LIKE 'test_user_%'");
    }
    
    echo "Creating 50 test users for pagination testing...\n";
    
    for ($i = 1; $i <= 50; $i++) {
        $username = "test_user_" . str_pad($i, 3, '0', STR_PAD_LEFT);
        $email = "test" . $i . "@example.com";
        $firstName = "Test";
        $lastName = "User " . $i;
        $password = password_hash("password123", PASSWORD_DEFAULT);
        $problemsSolved = rand(0, 100);
        $totalSubmissions = rand($problemsSolved, $problemsSolved * 3);
        $rating = rand(-1, 4000);
        
        if ($i % 7 == 0) {
            $rating = -1;
        }
        
        $sql = "INSERT INTO users (
            username, email, first_name, last_name, password, 
            total_problems_solved, total_submissions, rating, 
            is_active, role, created_at
        ) VALUES (
            :username, :email, :first_name, :last_name, :password,
            :problems_solved, :total_submissions, :rating,
            1, 'user', NOW()
        )";
        
        $params = [
            'username' => $username,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'password' => $password,
            'problems_solved' => $problemsSolved,
            'total_submissions' => $totalSubmissions,
            'rating' => $rating
        ];
        
        $db->insert($sql, $params);
        
        if ($i % 10 == 0) {
            echo "Created $i users...\n";
        }
    }
    
    echo "Successfully created 50 test users!\n";
    echo "Now you can test pagination at: http://localhost/CodeJubge/leaderboard\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
