<?php

class SecurityHelper
{
    // Dangerous functions that should be blocked
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
    
    // Dangerous C++ patterns
    private static $dangerousCppPatterns = [
        // System calls
        '/system\s*\(/',
        '/popen\s*\(/',
        '/exec[vl]?\s*\(/',
        
        // File operations
        '/fopen\s*\(/',
        '/freopen\s*\(/',
        '/remove\s*\(/',
        '/rename\s*\(/',
        
        // Network operations
        '/socket\s*\(/',
        '/bind\s*\(/',
        '/listen\s*\(/',
        '/accept\s*\(/',
        '/connect\s*\(/',
        
        // Memory operations that could be dangerous
        '/malloc\s*\(/',
        '/calloc\s*\(/',
        '/realloc\s*\(/',
        '/free\s*\(/',
        
        // Assembly code
        '/__asm__/',
        '/asm\s*\{/',
        
        // Preprocessor directives that could be dangerous
        '/#\s*include\s*<\s*(windows|winapi|shellapi)/',
        '/#\s*pragma\s/',
        
        // Fork/thread operations
        '/fork\s*\(/',
        '/pthread_create\s*\(/',
        
        // Signal handling
        '/signal\s*\(/',
        '/raise\s*\(/',
        '/kill\s*\(/',
        
        // Infinite loops patterns (simple detection)
        '/while\s*\(\s*true\s*\)/',
        '/while\s*\(\s*1\s*\)/',
        '/for\s*\(\s*;\s*;\s*\)/',
        
        // Recursive includes
        '/#\s*include\s*"/',
    ];
    
    // Safe C++ headers that are allowed
    private static $allowedCppHeaders = [
        'iostream', 'vector', 'string', 'algorithm', 'map', 'set', 'queue',
        'stack', 'deque', 'list', 'utility', 'iterator', 'functional',
        'numeric', 'cmath', 'cstdio', 'cstdlib', 'cstring', 'climits',
        'cfloat', 'cctype', 'cassert', 'ctime', 'sstream', 'iomanip',
        'bitset', 'array', 'tuple', 'memory', 'limits', 'random'
    ];
    
    /**
     * Validate C++ code for security issues
     */
    public static function validateCppCode($code)
    {
        $issues = [];
        
        // Check for dangerous patterns
        foreach (self::$dangerousCppPatterns as $pattern) {
            if (preg_match($pattern, $code)) {
                $issues[] = "Potentially dangerous code pattern detected";
                break;
            }
        }
        
        // Check includes - only allow safe headers
        if (preg_match_all('/#\s*include\s*<([^>]+)>/', $code, $matches)) {
            foreach ($matches[1] as $header) {
                $header = trim($header);
                if (!in_array($header, self::$allowedCppHeaders)) {
                    $issues[] = "Unsafe header included: " . $header;
                }
            }
        }
        
        // Check for local includes (not allowed)
        if (preg_match('/#\s*include\s*"/', $code)) {
            $issues[] = "Local file includes are not allowed";
        }
        
        // Check for excessive nested loops (potential performance issue)
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
        
        // Check code length
        if (strlen($code) > 50000) {
            $issues[] = "Code too long (max 50KB allowed)";
        }
        
        return [
            'safe' => empty($issues),
            'issues' => $issues
        ];
    }
    
    /**
     * Sanitize filename to prevent directory traversal
     */
    public static function sanitizeFilename($filename)
    {
        // Remove any path components
        $filename = basename($filename);
        
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        
        // Ensure it's not empty and not too long
        if (empty($filename) || strlen($filename) > 100) {
            $filename = 'file_' . uniqid();
        }
        
        return $filename;
    }
    
    /**
     * Generate safe temporary filename
     */
    public static function generateSafeFilename($prefix = 'code', $extension = '.cpp')
    {
        return $prefix . '_' . uniqid() . '_' . time() . $extension;
    }
    
    /**
     * Validate memory limit (in MB)
     */
    public static function validateMemoryLimit($limit)
    {
        $maxAllowed = 512; // 512 MB max
        return min(max(1, $limit), $maxAllowed);
    }
    
    /**
     * Validate time limit (in seconds)
     */
    public static function validateTimeLimit($limit)
    {
        $maxAllowed = 10; // 10 seconds max
        return min(max(1, $limit), $maxAllowed);
    }
    
    /**
     * Clean up old temporary files
     */
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
    
    /**
     * Escape shell command arguments
     */
    public static function escapeShellArg($arg)
    {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows escaping
            return '"' . str_replace('"', '""', $arg) . '"';
        } else {
            // Unix escaping
            return escapeshellarg($arg);
        }
    }
    
    /**
     * Get safe compiler flags for C++
     */
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
}
?>
