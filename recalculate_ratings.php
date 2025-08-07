<?php
/**
 * Script to recalculate all user ratings
 * Run this script to reset and recalculate ratings for all users
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/databaseConnect.php';
require_once __DIR__ . '/app/helpers/RatingHelper.php';

echo "=== CodeJudge Rating Recalculation Script ===\n\n";

try {
    echo "Starting rating recalculation...\n";
    
    // Get initial statistics
    echo "Getting initial statistics...\n";
    $initialStats = RatingHelper::getRatingStatistics();
    echo "Total rated users before: " . $initialStats['total_rated_users'] . "\n";
    echo "Average rating before: " . $initialStats['average_rating'] . "\n\n";
    
    // Recalculate all ratings
    echo "Recalculating all user ratings...\n";
    $success = RatingHelper::recalculateAllRatings();
    
    if ($success) {
        echo "✅ Rating recalculation completed successfully!\n\n";
        
        // Get final statistics
        echo "Getting final statistics...\n";
        $finalStats = RatingHelper::getRatingStatistics();
        echo "Total rated users after: " . $finalStats['total_rated_users'] . "\n";
        echo "Average rating after: " . $finalStats['average_rating'] . "\n\n";
        
        // Show rank distribution
        echo "=== Rank Distribution ===\n";
        foreach ($finalStats['rank_distribution'] as $rank => $data) {
            if ($data['user_count'] > 0) {
                echo sprintf("%-20s: %d users\n", $data['rank_info']['name'], $data['user_count']);
            }
        }
        
        echo "\n=== Top 10 Users by Rating ===\n";
        $topUsers = RatingHelper::getTopRatedUsers(10);
        foreach ($topUsers as $i => $user) {
            echo sprintf("#%d %-20s Rating: %d (%s)\n", 
                $i + 1, 
                $user['username'], 
                $user['rating'], 
                $user['rank_info']['name']
            );
        }
        
    } else {
        echo "❌ Rating recalculation failed!\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Recalculation completed successfully! ===\n";
?>
