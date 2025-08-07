<?php
require_once 'config/config.php';
require_once 'config/databaseConnect.php';

$db = Database::getInstance();
$pdo = $db->getConnection();
$result = $pdo->query('DESCRIBE submissions');

echo "Submissions table structure:\n";
while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
    echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']}\n";
}
?>
