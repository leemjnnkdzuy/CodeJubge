<?php
class Controller
{
    protected function view($viewName, $data = [])
    {
        extract($data);
        
        $viewFile = VIEW_PATH . '/' . $viewName . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new Exception("View file not found: {$viewName}");
        }
    }
    
    protected function renderPage($viewName, $title = '', $description = '', $data = [])
    {
        if ($title) {
            $data['title'] = $title;
        }
        if ($description) {
            $data['description'] = $description;
        }
        
        $this->view($viewName, $data);
    }
    
    protected function layout($layoutName, $content, $data = [])
    {
        $data['content'] = $content;
        $this->view('layouts/' . $layoutName, $data);
    }
    
    protected function redirect($url)
    {
        App::redirect($url);
    }
    
    protected function redirectWithMessage($url, $message, $type = 'success')
    {
        if (class_exists('NotificationHelper')) {
            if ($type === 'success') {
                NotificationHelper::success($message);
            } elseif ($type === 'error') {
                NotificationHelper::error($message);
            }
        }
        
        header('Location: ' . $url);
        exit;
    }
    
    protected function json($data, $statusCode = 200)
    {
        App::json($data, $statusCode);
    }
    
    protected function getInput($key = null, $default = null)
    {
        if ($key === null) {
            return $_REQUEST;
        }
        
        return isset($_REQUEST[$key]) ? $_REQUEST[$key] : $default;
    }
    
    protected function getPostData($keys = [])
    {
        $data = [];
        if (empty($keys)) {
            return $_POST;
        }
        
        foreach ($keys as $key => $default) {
            if (is_numeric($key)) {
                $data[$default] = $_POST[$default] ?? '';
            } else {
                $data[$key] = $_POST[$key] ?? $default;
            }
        }
        
        return $data;
    }
    
    protected function isPostRequest()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    protected function validateInput($rules, $data)
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = isset($data[$field]) ? $data[$field] : null;
            
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            if (strpos($rule, 'email') !== false && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = ucfirst($field) . ' must be a valid email';
            }
            
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = (int)$matches[1];
                if (strlen($value) < $min) {
                    $errors[$field] = ucfirst($field) . " must be at least {$min} characters";
                }
            }
            
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $max = (int)$matches[1];
                if (strlen($value) > $max) {
                    $errors[$field] = ucfirst($field) . " must not exceed {$max} characters";
                }
            }
        }
        
        return $errors;
    }
    
    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
    
    protected function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            $this->redirect('login');
        }
    }
    
    protected function getCurrentUser()
    {
        if ($this->isLoggedIn()) {
            return $_SESSION['user'] ?? null;
        }
        
        return null;
    }
    
    protected function redirectIfLoggedIn($redirectUrl = '/home')
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . $redirectUrl);
            exit;
        }
        return false;
    }
    
    protected function logout($redirectUrl = '/welcome', $successMessage = 'Đăng xuất thành công')
    {
        session_unset();
        session_destroy();
        
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        session_start();
        
        // Set success message if NotificationHelper is available
        if (class_exists('NotificationHelper')) {
            NotificationHelper::success($successMessage);
        }
        
        header('Location: ' . $redirectUrl);
        exit;
    }
}
?>
