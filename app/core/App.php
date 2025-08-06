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
        require_once CONTROLLER_PATH . '/pagesController.php';
        $controller = new pagesController();
        $controller->show404();
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
