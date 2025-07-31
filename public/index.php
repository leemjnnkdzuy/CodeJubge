<?php
require_once '../config/config.php';
require_once '../config/databaseConnect.php';

spl_autoload_register(function ($class) {
    $paths = [
        CORE_PATH . '/' . $class . '.php',
        CONTROLLER_PATH . '/' . $class . '.php',
        MODEL_PATH . '/' . $class . '.php',
        APP_PATH . '/helpers/' . $class . '.php'
    ];
    
    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

if (!is_dir(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0755, true);
}

try {
    $app = new App();
    $app->run();
} catch (Exception $e) {
    http_response_code(500);
    if (ini_get('display_errors')) {
        echo '<h1>Application Error</h1>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    } else {
        echo '<h1>500 - Internal Server Error</h1>';
        echo '<p>Something went wrong. Please try again later.</p>';
    }
}
?>