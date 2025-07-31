<?php
require_once 'config/config.php';

echo "<h2>Thiết lập Database Tự động - XÓA VÀ TẠO LẠI HOÀN TOÀN</h2>";
echo "<div style='background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
echo "<strong>⚠️ CẢNH BÁO:</strong> Script này sẽ <strong>XÓA HOÀN TOÀN</strong> database hiện tại và tạo lại từ đầu.<br>";
echo "Tất cả dữ liệu hiện có sẽ bị mất!";
echo "</div>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✓ Kết nối MySQL server thành công!</p>";
    
    $sqlFile = 'app/database/createDatabase.sql';
    
    if (!file_exists($sqlFile)) {
        throw new Exception("Không tìm thấy file SQL: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    if (empty($sql)) {
        throw new Exception("File SQL rỗng");
    }
    
    echo "<p style='color: blue;'>📁 Đang đọc file SQL: $sqlFile</p>";
    echo "<p style='color: orange;'>🗑️ Đang xóa database cũ (nếu có)...</p>";
    echo "<p style='color: blue;'>🆕 Đang tạo database mới và tất cả bảng...</p>";
    
    $statements = [];
    $delimiter = ';';
    $tempStatement = '';
    $lines = explode("\n", $sql);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        if (empty($line) || preg_match('/^\s*--/', $line)) {
            continue;
        }
        
        if (preg_match('/^DELIMITER\s+(.+)$/i', $line, $matches)) {
            $delimiter = trim($matches[1]);
            continue;
        }
        
        $tempStatement .= $line . "\n";
        
        if (substr(rtrim($line), -strlen($delimiter)) === $delimiter) {
            $statement = rtrim($tempStatement);
            if ($delimiter !== ';') {
                $statement = substr($statement, 0, -strlen($delimiter));
            } else {
                $statement = substr($statement, 0, -1);
            }
            
            if (!empty(trim($statement))) {
                $statements[] = trim($statement);
            }
            $tempStatement = '';
        }
    }
    
    if (!empty(trim($tempStatement))) {
        $statements[] = trim($tempStatement);
    }
    
    $successCount = 0;
    $errorCount = 0;
    $totalStatements = count($statements);
    
    echo "<p>Tổng số câu lệnh SQL cần thực thi: <strong>$totalStatements</strong></p>";
    
    foreach ($statements as $index => $statement) {
        try {
            if (!empty($statement)) {
                $pdo->exec($statement);
                $successCount++;
                
                if ($index % 5 == 0 || $index == $totalStatements - 1) {
                    $percent = round(($index + 1) / $totalStatements * 100);
                    echo "<p style='color: #666;'>Tiến trình: $percent% (" . ($index + 1) . "/$totalStatements)</p>";
                }
            }
        } catch (PDOException $e) {
            $errorCount++;
            echo "<p style='color: orange;'>⚠ Lỗi câu lệnh " . ($index + 1) . ": " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<p style='color: green;'>✓ Hoàn thành! Thực thi thành công $successCount câu lệnh</p>";
    
    if ($errorCount > 0) {
        echo "<p style='color: orange;'>⚠ Có $errorCount lỗi</p>";
    }
    
    $pdo->exec("USE " . DB_NAME);
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<h3>✅ Các bảng đã được tạo:</h3>";
    echo "<ul>";
    foreach ($tables as $table) {
        try {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "<li><strong>$table</strong> - $count records</li>";
        } catch (Exception $e) {
            echo "<li><strong>$table</strong> - Không thể đếm records</li>";
        }
    }
    echo "</ul>";
    
    $adminCheck = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    echo "<p><strong>👤 User admin:</strong> " . ($adminCheck > 0 ? "✓ Đã tạo" : "✗ Chưa tạo") . "</p>";
    
    $pinTableCheck = $pdo->query("SHOW TABLES LIKE 'verification_pins'")->rowCount();
    echo "<p><strong>📧 Verification system:</strong> " . ($pinTableCheck > 0 ? "✓ Đã cài đặt" : "✗ Chưa cài đặt") . "</p>";
    
    $procedureCheck = $pdo->query("SELECT COUNT(*) FROM information_schema.routines WHERE routine_schema = '" . DB_NAME . "'")->fetchColumn();
    echo "<p><strong>⚙️ Stored procedures:</strong> $procedureCheck procedures</p>";
    
    $viewCheck = $pdo->query("SELECT COUNT(*) FROM information_schema.views WHERE table_schema = '" . DB_NAME . "'")->fetchColumn();
    echo "<p><strong>👁️ Views:</strong> $viewCheck views</p>";
    
    echo "<hr>";
    echo "<div style='background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3 style='color: #155724; margin-top: 0;'>🎉 Database đã được thiết lập thành công!</h3>";
    echo "<p style='color: #155724;'>Database <strong>" . DB_NAME . "</strong> đã được tạo lại hoàn toàn với tất cả tính năng:</p>";
    echo "<ul style='color: #155724;'>";
    echo "<li>✓ User authentication với PIN verification</li>";
    echo "<li>✓ Problem management system</li>";
    echo "<li>✓ Contest system</li>";
    echo "<li>✓ Submission tracking</li>";
    echo "<li>✓ Session management</li>";
    echo "<li>✓ Email verification system</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div style='margin: 20px 0;'>";
    echo "<p><a href='test_database.php' style='background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🔍 Kiểm tra kết nối database</a></p>";
    echo "<p><a href='public/index.php' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Truy cập website</a></p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3 style='color: #721c24; margin-top: 0;'>❌ Lỗi thiết lập database</h3>";
    echo "<p style='color: #721c24;'><strong>Chi tiết lỗi:</strong> " . $e->getMessage() . "</p>";
    echo "<p style='color: #721c24;'>Vui lòng kiểm tra:</p>";
    echo "<ul style='color: #721c24;'>";
    echo "<li>XAMPP đã được khởi động chưa?</li>";
    echo "<li>MySQL service có đang chạy không?</li>";
    echo "<li>Thông tin kết nối database trong config.php có đúng không?</li>";
    echo "<li>Quyền truy cập database có đủ không?</li>";
    echo "</ul>";
    echo "</div>";
}
?>
