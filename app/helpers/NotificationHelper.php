<?php
class NotificationHelper 
{

    public static function success($message) 
    {
        $_SESSION['notification'] = [
            'type' => 'success',
            'message' => $message
        ];
    }

    public static function error($message) 
    {
        $_SESSION['notification'] = [
            'type' => 'error',
            'message' => $message
        ];
    }
    
    public static function warning($message) 
    {
        $_SESSION['notification'] = [
            'type' => 'warning',
            'message' => $message
        ];
    }
    
    public static function info($message) 
    {
        $_SESSION['notification'] = [
            'type' => 'info',
            'message' => $message
        ];
    }
    
    public static function has() 
    {
        return isset($_SESSION['notification']);
    }
    
    public static function get() 
    {
        return $_SESSION['notification'] ?? null;
    }
    
    public static function clear() 
    {
        unset($_SESSION['notification']);
    }
}
?>
