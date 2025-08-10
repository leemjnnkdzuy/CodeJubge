<?php
require_once ROOT_PATH . '/config/config.php';
require_once ROOT_PATH . '/config/databaseConnect.php';
require_once ROOT_PATH . '/app/helpers/SecurityHelper.php';
require_once ROOT_PATH . '/app/helpers/RatingHelper.php';

class ApiController
{
    private $tempDir;
    private $maxExecutionTime = 5; // seconds
    private $maxMemoryLimit = 256; // MB
    private $db;
    
    public function __construct()
    {
        // Start session if not already started
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->tempDir = ROOT_PATH . '/temp';
        if (!file_exists($this->tempDir)) {
            mkdir($this->tempDir, 0755, true);
        }
        
        // Clean up old temporary files
        SecurityHelper::cleanupTempFiles($this->tempDir);
        
        $this->db = getConnection();
    }
    
    public function runCode()
    {
        try {
            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            // Get input data
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Invalid JSON input');
            }
            
            $code = $input['code'] ?? '';
            $language = $input['language'] ?? 'cpp';
            $testInput = $input['input'] ?? '';
            
            if (empty($code)) {
                throw new Exception('Code is required');
            }
            
            // Validate language
            global $SUPPORTED_LANGUAGES;
            if (!isset($SUPPORTED_LANGUAGES[$language])) {
                throw new Exception('Unsupported language');
            }
            
            // Security validation for C++ code
            if ($language === 'cpp' || $language === 'c') {
                $validation = SecurityHelper::validateCppCode($code);
                if (!$validation['safe']) {
                    throw new Exception('Security violation: ' . implode(', ', $validation['issues']));
                }
            }
            
            $result = $this->executeCode($code, $language, $testInput);
            
            header('Content-Type: application/json');
            echo json_encode($result);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time' => 0,
                'memory_usage' => 0
            ]);
        }
    }
    
    public function submitSolution()
    {
        try {
            // Validate request method
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new Exception('Method not allowed');
            }
            
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not authenticated');
            }
            
            // Get input data
            $input = json_decode(file_get_contents('php://input'), true);
            
            if (!$input) {
                throw new Exception('Invalid JSON input');
            }
            
            $code = $input['code'] ?? '';
            $language = $input['language'] ?? 'cpp';
            $problemId = $input['problem_id'] ?? 0;
            
            if (empty($code) || empty($problemId)) {
                throw new Exception('Code and problem_id are required');
            }
            
            // Get problem details
            $problem = $this->getProblemById($problemId);
            if (!$problem) {
                throw new Exception('Problem not found');
            }
            
            // Run code against test cases
            $result = $this->judgeSubmission($code, $language, $problem);
            
            // Save submission to database
            $submissionId = $this->saveSubmission([
                'user_id' => $_SESSION['user_id'],
                'problem_id' => $problemId,
                'code' => $code,
                'language' => $language,
                'status' => $result['status'],
                'execution_time' => $result['execution_time'],
                'memory_usage' => $result['memory_usage'],
                'test_cases_passed' => $result['test_cases_passed'],
                'total_test_cases' => $result['total_test_cases']
            ]);
            
            // Update user rating if submission is accepted and has test cases passed
            if ($result['status'] === 'Accepted' && $result['test_cases_passed'] > 0) {
                RatingHelper::updateUserRating($_SESSION['user_id'], $problemId, $result['test_cases_passed']);
                
                // Get updated user rating and rank info for response
                $newRating = RatingHelper::getUserRating($_SESSION['user_id']);
                $rankInfo = RatingHelper::getUserRank($_SESSION['user_id']);
                $result['user_rating'] = $newRating;
                $result['user_rank'] = $rankInfo;
            }
            
            $result['submission_id'] = $submissionId;
            
            header('Content-Type: application/json');
            echo json_encode($result);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function getUserRating()
    {
        try {
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                throw new Exception('User not authenticated');
            }
            
            $userId = $_SESSION['user_id'];
            $rating = RatingHelper::getUserRating($userId);
            $rankInfo = RatingHelper::getUserRank($userId);
            $nextRankInfo = RatingHelper::getNextRankInfo($rating);
            $ratingHistory = RatingHelper::getUserRatingHistory($userId, 10);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'rating' => $rating,
                'rank_info' => $rankInfo,
                'next_rank_info' => $nextRankInfo,
                'rating_history' => $ratingHistory
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function getRatingLeaderboard()
    {
        try {
            $limit = $_GET['limit'] ?? 50;
            $limit = min(max(1, (int)$limit), 100); // Giới hạn từ 1-100
            
            $topUsers = RatingHelper::getTopRatedUsers($limit);
            $statistics = RatingHelper::getRatingStatistics();
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'top_users' => $topUsers,
                'statistics' => $statistics
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    public function getProblem()
    {
        try {
            $problemId = $_GET['id'] ?? 0;
            
            if (empty($problemId)) {
                throw new Exception('Problem ID is required');
            }
            
            $problem = $this->getProblemById($problemId);
            
            if (!$problem) {
                throw new Exception('Problem not found');
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'problem' => $problem
            ]);
            
        } catch (Exception $e) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    private function executeCode($code, $language, $input = '')
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        try {
            switch ($language) {
                case 'cpp':
                case 'c':
                    return $this->executeCpp($code, $input);
                case 'python':
                    return $this->executePython($code, $input);
                case 'java':
                    return $this->executeJava($code, $input);
                case 'javascript':
                    return $this->executeJavaScript($code, $input);
                default:
                    throw new Exception('Unsupported language: ' . $language);
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'execution_time' => round((microtime(true) - $startTime) * 1000, 2),
                'memory_usage' => round((memory_get_usage(true) - $startMemory) / 1024 / 1024, 2)
            ];
        }
    }
    
    private function executeCpp($code, $input = '')
    {
        $startTime = microtime(true);
        
        // Additional security check
        $validation = SecurityHelper::validateCppCode($code);
        if (!$validation['safe']) {
            throw new Exception('Security violation: ' . implode(', ', $validation['issues']));
        }
        
        // Generate unique filename
        $filename = SecurityHelper::generateSafeFilename('cpp_');
        $sourceFile = $this->tempDir . '/' . $filename . '.cpp';
        $executableFile = $this->tempDir . '/' . $filename . '.exe';
        $inputFile = $this->tempDir . '/' . $filename . '_input.txt';
        $outputFile = $this->tempDir . '/' . $filename . '_output.txt';
        $errorFile = $this->tempDir . '/' . $filename . '_error.txt';
        
        try {
            // Write source code to file
            file_put_contents($sourceFile, $code);
            
            // Write input to file
            if (!empty($input)) {
                file_put_contents($inputFile, $input);
            }
            
            // Get safe compiler flags
            $flags = implode(' ', SecurityHelper::getSafeCppFlags());
            
            // Compile C++ code
            $sourceFileEscaped = SecurityHelper::escapeShellArg($sourceFile);
            $executableFileEscaped = SecurityHelper::escapeShellArg($executableFile);
            $errorFileEscaped = SecurityHelper::escapeShellArg($errorFile);
            
            $compileCommand = sprintf(
                'g++ %s -o %s %s 2>%s',
                $flags,
                $executableFileEscaped,
                $sourceFileEscaped,
                $errorFileEscaped
            );
            
            exec($compileCommand, $compileOutput, $compileReturnCode);
            
            if ($compileReturnCode !== 0) {
                $compileError = file_get_contents($errorFile);
                throw new Exception('Compilation Error: ' . $compileError);
            }
            
            // Execute the program with timeout
            $inputRedirect = !empty($input) ? '<' . SecurityHelper::escapeShellArg($inputFile) : '';
            $outputFileEscaped = SecurityHelper::escapeShellArg($outputFile);
            
            $executeCommand = sprintf(
                '%s %s >%s 2>&1',
                SecurityHelper::escapeShellArg($executableFile),
                $inputRedirect,
                $outputFileEscaped
            );
            
            // For Windows, we'll use a simpler approach without timeout command for now
            // TODO: Implement proper timeout mechanism
            exec($executeCommand, $executeOutput, $executeReturnCode);
            
            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);
            
            // Read output
            $output = file_exists($outputFile) ? file_get_contents($outputFile) : '';
            
            // Check for timeout
            if ($executeReturnCode === 124 || $executeReturnCode === 1) { // timeout exit codes
                throw new Exception('Time Limit Exceeded');
            }
            
            // Check for runtime error
            if ($executeReturnCode !== 0) {
                throw new Exception('Runtime Error: Exit code ' . $executeReturnCode);
            }
            
            return [
                'success' => true,
                'output' => trim($output),
                'execution_time' => $executionTime,
                'memory_usage' => $this->getMemoryUsage(),
                'error' => null
            ];
            
        } finally {
            // Clean up temporary files
            $this->cleanupFiles([
                $sourceFile, 
                $executableFile, 
                $inputFile, 
                $outputFile, 
                $errorFile
            ]);
        }
    }
    
    private function executePython($code, $input = '')
    {
        // TODO: Implement Python execution
        throw new Exception('Python execution not implemented yet');
    }
    
    private function executeJava($code, $input = '')
    {
        // TODO: Implement Java execution
        throw new Exception('Java execution not implemented yet');
    }
    
    private function executeJavaScript($code, $input = '')
    {
        // TODO: Implement JavaScript execution
        throw new Exception('JavaScript execution not implemented yet');
    }
    
    private function judgeSubmission($code, $language, $problem)
    {
        $testCases = json_decode($problem['test_cases'] ?? '[]', true);
        $totalTestCases = count($testCases);
        $passedTestCases = 0;
        $maxExecutionTime = 0;
        $maxMemoryUsage = 0;
        $status = 'Accepted';
        
        if (empty($testCases)) {
            // If no test cases, just run the code with sample input
            $result = $this->executeCode($code, $language, $problem['sample_input'] ?? '');
            
            if (!$result['success']) {
                $status = $this->getErrorStatus($result['error']);
            } else {
                $passedTestCases = 1;
                $totalTestCases = 1;
            }
            
            $maxExecutionTime = $result['execution_time'] ?? 0;
            $maxMemoryUsage = $result['memory_usage'] ?? 0;
        } else {
            foreach ($testCases as $testCase) {
                $result = $this->executeCode($code, $language, $testCase['input']);
                
                if (!$result['success']) {
                    $status = $this->getErrorStatus($result['error']);
                    break;
                }
                
                $maxExecutionTime = max($maxExecutionTime, $result['execution_time']);
                $maxMemoryUsage = max($maxMemoryUsage, $result['memory_usage']);
                
                // Check if output matches expected
                if (trim($result['output']) === trim($testCase['expected_output'])) {
                    $passedTestCases++;
                } else {
                    $status = 'Wrong Answer';
                    break;
                }
            }
        }
        
        return [
            'success' => $status === 'Accepted',
            'status' => $status,
            'test_cases_passed' => $passedTestCases,
            'total_test_cases' => $totalTestCases,
            'execution_time' => $maxExecutionTime,
            'memory_usage' => $maxMemoryUsage,
            'message' => $this->getStatusMessage($status, $passedTestCases, $totalTestCases)
        ];
    }
    
    private function getErrorStatus($error)
    {
        if (strpos($error, 'Compilation Error') !== false) {
            return 'Compilation Error';
        } elseif (strpos($error, 'Time Limit Exceeded') !== false) {
            return 'Time Limit Exceeded';
        } elseif (strpos($error, 'Runtime Error') !== false) {
            return 'Runtime Error';
        } else {
            return 'Runtime Error';
        }
    }
    
    private function getStatusMessage($status, $passed, $total)
    {
        switch ($status) {
            case 'Accepted':
                return 'Chúc mừng! Bài làm của bạn đã được chấp nhận.';
            case 'Wrong Answer':
                return "Sai kết quả. Đã qua {$passed}/{$total} test cases.";
            case 'Time Limit Exceeded':
                return 'Vượt quá thời gian cho phép.';
            case 'Memory Limit Exceeded':
                return 'Vượt quá giới hạn bộ nhớ.';
            case 'Runtime Error':
                return 'Lỗi khi chạy chương trình.';
            case 'Compilation Error':
                return 'Lỗi biên dịch.';
            default:
                return 'Lỗi hệ thống.';
        }
    }
    
    private function getMemoryUsage()
    {
        // Simple memory usage estimation (in MB)
        return round(memory_get_usage(true) / 1024 / 1024, 2);
    }
    
    private function getTimeoutCommand($seconds)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows timeout command
            return 'timeout /t ' . $seconds;
        } else {
            // Linux/Unix timeout command
            return 'timeout ' . $seconds . 's';
        }
    }
    
    private function cleanupFiles($files)
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
    
    private function getProblemById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM problems WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }
    
    private function saveSubmission($data)
    {
        try {
            $sql = "INSERT INTO submissions (user_id, problem_id, code, language, status, runtime, memory_used, test_cases_passed, total_test_cases, submitted_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['user_id'],
                $data['problem_id'],
                $data['code'],
                $data['language'],
                $data['status'],
                round($data['execution_time'], 0),
                round($data['memory_usage'] * 1024 * 1024, 0),
                $data['test_cases_passed'],
                $data['total_test_cases']
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            throw new Exception('Failed to save submission');
        }
    }
    
    public function testExecuteCode($code, $language, $input = '')
    {
        return $this->executeCode($code, $language, $input);
    }
}
?>