<?php
class App
{
    private $routes;
    private $currentUri;
    private $requestMethod;
    
    public function __construct()
    {
        $this->routes = require_once ROOT_PATH . '/routes/web.php';
        $this->currentUri = $this->getCurrentUri();
        $this->requestMethod = $_SERVER['REQUEST_METHOD'];
    }
    
    public function run()
    {
        try {
            $this->route();
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    private function getCurrentUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        
        if (strpos($uri, '?') !== false) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        
        $uri = trim($uri, '/');
        
        return $uri;
    }
    
    private function route()
    {
        foreach ($this->routes as $pattern => $handler) {
            $regex = str_replace('/', '\/', $pattern);
            $regex = '/^' . $regex . '$/';
            
            if (preg_match($regex, $this->currentUri, $matches)) {
                $this->callHandler($handler, array_slice($matches, 1));
                return;
            }
        }
        
        $this->show404();
    }
    
    private function callHandler($handler, $params = [])
    {
        try {
            if (strpos($handler, '@') !== false) {
                list($controllerName, $methodName) = explode('@', $handler);
                
                $controllerFile = CONTROLLER_PATH . '/' . $controllerName . '.php';
                
                if (file_exists($controllerFile)) {
                    require_once $controllerFile;
                    
                    if (class_exists($controllerName)) {
                        $controller = new $controllerName();
                        
                        if (method_exists($controller, $methodName)) {
                            call_user_func_array([$controller, $methodName], $params);
                            return;
                        }
                    }
                }
            }
            
            $this->show404();
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }
    
    private function handleError($e)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'connection') !== false) {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Không thể kết nối đến cơ sở dữ liệu. Vui lòng kiểm tra XAMPP hoặc liên hệ quản trị viên.'
            ];
        } else {
            $_SESSION['notification'] = [
                'type' => 'error',
                'message' => 'Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.'
            ];
        }
        
        error_log("Application Error: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        
        $this->showErrorPage();
    }
    
    private function showErrorPage()
    {
        http_response_code(500);
        $title = 'Lỗi Hệ Thống - CodeJudge';
        
        echo '<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . $title . '</title>
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <style>
        body { font-family: "Inter Tight", sans-serif; margin: 0; padding: 2rem; background: #f8f9fa; }
        .error-container { max-width: 600px; margin: 2rem auto; text-align: center; }
        .error-icon { font-size: 4rem; color: #e74c3c; margin-bottom: 1rem; }
        .error-title { font-size: 1.5rem; margin-bottom: 1rem; color: #333; }
        .error-message { color: #666; margin-bottom: 2rem; }
        .btn { display: inline-block; padding: 0.75rem 1.5rem; background: #3498db; color: white; text-decoration: none; border-radius: 5px; }
    </style>
</head>
<body>';
        
        if (isset($_SESSION['notification'])) {
            include VIEW_PATH . '/components/popupNotification.php';
        }
        
        echo '<div class="error-container">
            <i class="bx bx-error-circle error-icon"></i>
            <h1 class="error-title">Lỗi Hệ Thống</h1>
            <p class="error-message">Hệ thống hiện đang gặp sự cố. Vui lòng thử lại sau ít phút.</p>
            <a href="/welcome" class="btn">Quay về Trang Chủ</a>
        </div>
</body>
</html>';
        exit;
    }
    
    private function show404()
    {
        require_once CONTROLLER_PATH . '/pagesController.php';
        $controller = new pagesController();
        $controller->show404();
    }
    
    public static function redirect($url)
    {
        header('Location: ' . SITE_URL . '/' . ltrim($url, '/'));
        exit;
    }
    
    public static function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
?>
