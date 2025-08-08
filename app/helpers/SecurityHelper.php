<?php

class SecurityHelper
{
    private static $dangerousFunctions = [
        // File operations
        'fopen', 'fwrite', 'fread', 'file_get_contents', 'file_put_contents',
        'unlink', 'rmdir', 'mkdir', 'chmod', 'chown', 'rename',
        
        // System operations
        'system', 'exec', 'shell_exec', 'passthru', 'popen', 'proc_open',
        'escapeshellarg', 'escapeshellcmd',
        
        // Network operations
        'curl_exec', 'socket_create', 'fsockopen', 'gethostbyname',
        
        // Dangerous include/require
        'include', 'require', 'include_once', 'require_once',
        
        // Exit/die
        'exit', 'die'
    ];
    
    private static $dangerousCppPatterns = [
        '/system\s*\(/',
        '/popen\s*\(/',
        '/exec[vl]?\s*\(/',
        
        '/fopen\s*\(/',
        '/freopen\s*\(/',
        '/remove\s*\(/',
        '/rename\s*\(/',
        
        '/socket\s*\(/',
        '/bind\s*\(/',
        '/listen\s*\(/',
        '/accept\s*\(/',
        '/connect\s*\(/',
        
        '/malloc\s*\(/',
        '/calloc\s*\(/',
        '/realloc\s*\(/',
        '/free\s*\(/',
        
        '/__asm__/',
        '/asm\s*\{/',
        
        '/#\s*include\s*<\s*(windows|winapi|shellapi)/',
        '/#\s*pragma\s/',
        
        '/fork\s*\(/',
        '/pthread_create\s*\(/',
        
        '/signal\s*\(/',
        '/raise\s*\(/',
        '/kill\s*\(/',
        
        '/while\s*\(\s*true\s*\)/',
        '/while\s*\(\s*1\s*\)/',
        '/for\s*\(\s*;\s*;\s*\)/',
        
        '/#\s*include\s*"/',
    ];
    
    private static $allowedCppHeaders = [
        'iostream', 'vector', 'string', 'algorithm', 'map', 'set', 'queue',
        'stack', 'deque', 'list', 'utility', 'iterator', 'functional',
        'numeric', 'cmath', 'cstdio', 'cstdlib', 'cstring', 'climits',
        'cfloat', 'cctype', 'cassert', 'ctime', 'sstream', 'iomanip',
        'bitset', 'array', 'tuple', 'memory', 'limits', 'random'
    ];

    public static function validateCppCode($code)
    {
        $issues = [];
        
        foreach (self::$dangerousCppPatterns as $pattern) {
            if (preg_match($pattern, $code)) {
                $issues[] = "Potentially dangerous code pattern detected";
                break;
            }
        }
        
        if (preg_match_all('/#\s*include\s*<([^>]+)>/', $code, $matches)) {
            foreach ($matches[1] as $header) {
                $header = trim($header);
                if (!in_array($header, self::$allowedCppHeaders)) {
                    $issues[] = "Unsafe header included: " . $header;
                }
            }
        }
        
        if (preg_match('/#\s*include\s*"/', $code)) {
            $issues[] = "Local file includes are not allowed";
        }
        
        $loopDepth = 0;
        $maxLoopDepth = 0;
        $tokens = explode("\n", $code);
        
        foreach ($tokens as $line) {
            if (preg_match('/\b(for|while)\s*\(/', $line)) {
                $loopDepth++;
                $maxLoopDepth = max($maxLoopDepth, $loopDepth);
            }
            if (strpos($line, '}') !== false) {
                $loopDepth = max(0, $loopDepth - 1);
            }
        }
        
        if ($maxLoopDepth > 3) {
            $issues[] = "Excessive nested loops detected (max 3 levels allowed)";
        }
        
        if (strlen($code) > 50000) {
            $issues[] = "Code too long (max 50KB allowed)";
        }
        
        return [
            'safe' => empty($issues),
            'issues' => $issues
        ];
    }
    
    public static function sanitizeFilename($filename)
    {
        $filename = basename($filename);
        
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        if (empty($filename) || strlen($filename) > 100) {
            $filename = 'file_' . uniqid();
        }
        
        return $filename;
    }
    
    public static function generateSafeFilename($prefix = 'code', $extension = '.cpp')
    {
        return $prefix . '_' . uniqid() . '_' . time() . $extension;
    }
    
    public static function validateMemoryLimit($limit)
    {
        $maxAllowed = 512;
        return min(max(1, $limit), $maxAllowed);
    }
    
    public static function validateTimeLimit($limit)
    {
        $maxAllowed = 10;
        return min(max(1, $limit), $maxAllowed);
    }
    
    public static function cleanupTempFiles($tempDir, $maxAge = 3600)
    {
        if (!is_dir($tempDir)) {
            return;
        }
        
        $files = glob($tempDir . '/*');
        $now = time();
        
        foreach ($files as $file) {
            if (is_file($file)) {
                $age = $now - filemtime($file);
                if ($age > $maxAge) {
                    unlink($file);
                }
            }
        }
    }
    
    public static function escapeShellArg($arg)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return '"' . str_replace('"', '""', $arg) . '"';
        } else {
            return escapeshellarg($arg);
        }
    }
    
    public static function getSafeCppFlags()
    {
        return [
            '-std=c++17',
            '-O2',
            '-Wall',
            '-Wextra',
            '-Werror=array-bounds',
            '-fstack-protector-strong'
        ];
    }
    
    public static function validatePythonCode($code)
    {
        $issues = [];
        
        // Dangerous Python modules and functions
        $dangerousPythonPatterns = [
            '/import\s+os/',
            '/from\s+os\s+import/',
            '/import\s+sys/',
            '/import\s+subprocess/',
            '/import\s+socket/',
            '/import\s+urllib/',
            '/import\s+requests/',
            '/open\s*\(/',
            '/exec\s*\(/',
            '/eval\s*\(/',
            '/__import__\s*\(/',
            '/compile\s*\(/',
        ];
        
        foreach ($dangerousPythonPatterns as $pattern) {
            if (preg_match($pattern, $code)) {
                $issues[] = "Potentially dangerous Python code pattern detected";
                break;
            }
        }
        
        if (strlen($code) > 50000) {
            $issues[] = "Code too long (max 50KB allowed)";
        }
        
        return [
            'safe' => empty($issues),
            'issues' => $issues
        ];
    }
    
    public static function validateJavaCode($code)
    {
        $issues = [];
        
        // Dangerous Java classes and methods
        $dangerousJavaPatterns = [
            '/Runtime\.getRuntime\(\)/',
            '/ProcessBuilder/',
            '/System\.exit\s*\(/',
            '/File\s*\(/',
            '/FileInputStream/',
            '/FileOutputStream/',
            '/Socket\s*\(/',
            '/ServerSocket/',
            '/URLConnection/',
            '/Class\.forName/',
            '/Method\.invoke/',
            '/reflect\./',
        ];
        
        foreach ($dangerousJavaPatterns as $pattern) {
            if (preg_match($pattern, $code)) {
                $issues[] = "Potentially dangerous Java code pattern detected";
                break;
            }
        }
        
        if (strlen($code) > 50000) {
            $issues[] = "Code too long (max 50KB allowed)";
        }
        
        return [
            'safe' => empty($issues),
            'issues' => $issues
        ];
    }
    
    public static function validateJavaScriptCode($code)
    {
        $issues = [];
        
        // Dangerous JavaScript patterns
        $dangerousJsPatterns = [
            '/require\s*\(\s*[\'\"](fs|child_process|os|path|http|https|net|url)[\'\"]\s*\)/',
            '/eval\s*\(/',
            '/Function\s*\(/',
            '/setTimeout\s*\(/',
            '/setInterval\s*\(/',
            '/XMLHttpRequest/',
            '/fetch\s*\(/',
            '/import\s*\(/',
            '/require\s*\(/',
        ];
        
        foreach ($dangerousJsPatterns as $pattern) {
            if (preg_match($pattern, $code)) {
                $issues[] = "Potentially dangerous JavaScript code pattern detected";
                break;
            }
        }
        
        if (strlen($code) > 50000) {
            $issues[] = "Code too long (max 50KB allowed)";
        }
        
        return [
            'safe' => empty($issues),
            'issues' => $issues
        ];
    }
}
?>
