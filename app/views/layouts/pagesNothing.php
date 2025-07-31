<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'CodeJudge' ?></title>
    <meta name="description" content="<?= isset($description) ? htmlspecialchars($description) : 'Practice coding problems with automatic evaluation' ?>">
    
    <link rel="stylesheet" href="/css/globalStyle.css">
    <?php
    $current_uri = $_SERVER['REQUEST_URI'];
    $current_page = basename($current_uri);
    
    if ($current_page === 'login') {
        echo '<link rel="stylesheet" href="/css/loginStyle.css">';
    } elseif ($current_page === 'register') {
        echo '<link rel="stylesheet" href="/css/signUpStyle.css">';
    } elseif ($current_page === 'forgot-password') {
        echo '<link rel="stylesheet" href="/css/fogotPasswordStyle.css">';
    } elseif (strpos($current_uri, '/problems/') !== false || isset($problem)) {
        echo '<link rel="stylesheet" href="/css/problemDetailStyle.css">';
    } elseif ($current_page === 'terms') {
        echo '<link rel="stylesheet" href="/css/termsStyle.css">';
    } elseif ($current_page === 'privacy') {
        echo '<link rel="stylesheet" href="/css/privacyStyle.css">';
    } elseif ($current_page === 'cookies') {
        echo '<link rel="stylesheet" href="/css/cookiesStyle.css">';
    } elseif ($current_page === 'languages') {
        echo '<link rel="stylesheet" href="/css/languagesStyle.css">';
    } elseif (strpos($current_uri, '/docs/') !== false) {
        echo '<link rel="stylesheet" href="/css/docsStyle.css">';
    } else {
        echo '<link rel="stylesheet" href="/css/404Style.css">';
    }
    ?>
    
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    
    <style>
        body {
            font-family: 'Inter Tight', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>
    <?php include VIEW_PATH . '/components/popupNotification.php'; ?>
    <?= $content ?>
</body>
</html>
