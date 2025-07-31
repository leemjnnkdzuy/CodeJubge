<?php
/**
 * Base Controller Class
 */

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
    
    protected function layout($layoutName, $content, $data = [])
    {
        $data['content'] = $content;
        $this->view('layouts/' . $layoutName, $data);
    }
    
    protected function redirect($url)
    {
        App::redirect($url);
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
}
?>
