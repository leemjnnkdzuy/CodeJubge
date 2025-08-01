<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'CodeJudge' ?></title>
    <meta name="description" content="<?= isset($description) ? htmlspecialchars($description) : 'Practice coding problems with automatic evaluation' ?>">
    
    <link rel="stylesheet" href="/css/globalStyle.css">
    <link rel="stylesheet" href="/css/globalSidebarStyle.css">
    <?php
    $current_page = basename($_SERVER['REQUEST_URI']);
    if (strpos($_SERVER['REQUEST_URI'], '/profile') === 0) {
        echo '<link rel="stylesheet" href="/css/profileStyle.css">';
    } elseif (strpos($_SERVER['REQUEST_URI'], '/user/') === 0) {
        echo '<link rel="stylesheet" href="/css/userProfileStyle.css">';
    } elseif (strpos($_SERVER['REQUEST_URI'], '/problems') === 0) {
        echo '<link rel="stylesheet" href="/css/problemsStyle.css">';
    }elseif (strpos($_SERVER['REQUEST_URI'], '/leaderboard') === 0) {
        echo ' <link rel="stylesheet" href="/css/leaderboardStyle.css">';
    } 
    else {
        echo '<link rel="stylesheet" href="/css/homeStyle.css">';
    }
    
    if (isset($additionalCSS) && is_array($additionalCSS)) {
        foreach ($additionalCSS as $cssFile) {
            echo '<link rel="stylesheet" href="' . htmlspecialchars($cssFile) . '">';
        }
    }
    ?>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include VIEW_PATH . '/components/globalSidebar.php'; ?>
    
    <div class="main-content" id="mainContent">
        <div class="page-content">
            <div class="content-wrapper">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?= $_SESSION['error'] ?>
                        <?php unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?= $_SESSION['success'] ?>
                        <?php unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <main class="main-content-area">
                    <?= $content ?? '' ?>
                </main>
            </div>
        </div>
    </div>

    <script src="js/sidebar.js"></script>
    <?php if (strpos($_SERVER['REQUEST_URI'], '/leaderboard') === 0): ?>
        <script src="js/leaderboard.js"></script>
    <?php endif; ?>
    <script>
        function checkScreenSize() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            
            if (window.innerWidth <= 768) {
                sidebar.classList.add('collapsed');
                mainContent.classList.add('sidebar-collapsed');
            } else {
                sidebar.classList.remove('collapsed');
                mainContent.classList.remove('sidebar-collapsed');
            }
        }
        
        window.addEventListener('load', checkScreenSize);
        window.addEventListener('resize', checkScreenSize);
    </script>
</body>
</html>
