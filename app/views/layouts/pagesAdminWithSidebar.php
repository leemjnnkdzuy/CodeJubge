<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($title) ? htmlspecialchars($title) : 'Admin - CodeJudge' ?></title>
    <meta name="description" content="<?= isset($description) ? htmlspecialchars($description) : 'CodeJudge Admin Panel' ?>">
    
    <link rel="stylesheet" href="/css/globalStyle.css">
    <link rel="stylesheet" href="/css/adminStyle.css">
    <?php
        // Load page-specific CSS based on current route
        $current_uri = $_SERVER['REQUEST_URI'];
        
        if (strpos($current_uri, '/admin/users') !== false) {
            echo '<link rel="stylesheet" href="/css/adminUsersStyle.css">';
        } elseif (strpos($current_uri, '/admin/problems') !== false) {
            echo '<link rel="stylesheet" href="/css/adminProblemsStyle.css">';
        } elseif (strpos($current_uri, '/admin/submissions') !== false) {
            echo '<link rel="stylesheet" href="/css/adminSubmissionsStyle.css">';
        } elseif (strpos($current_uri, '/admin/discussions') !== false) {
            echo '<link rel="stylesheet" href="/css/adminDiscussionsStyle.css">';
        }
    ?>
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter+Tight:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://fonts.googleapis.com/css2?family=Dosis:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter Tight', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            background: var(--light-gray);
        }
    </style>
</head>
<body>
    <?php include VIEW_PATH . '/components/popupNotification.php'; ?>
    <?php include VIEW_PATH . '/components/adminSidebar.php'; ?>
    
    <div class="admin-main-content" id="adminMainContent">
        <header class="admin-header">
            <div class="admin-header-content">
                <h1><?= isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Admin Dashboard' ?></h1>
                <?php if (isset($_SESSION['user'])): ?>
                <?php
                require_once APP_PATH . '/helpers/AvatarHelper.php';
                $userAvatar = AvatarHelper::base64ToImageSrc($_SESSION['user']['avatar'] ?? '');
                $userInitials = AvatarHelper::getInitials($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']);
                ?>
                <div class="admin-user-info">
                    <div class="admin-user-avatar">
                        <?php if (!empty($_SESSION['user']['avatar'])): ?>
                            <img src="<?= $userAvatar ?>" alt="Avatar" class="avatar-image">
                        <?php else: ?>
                            <div class="avatar-initials"><?= $userInitials ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="admin-user-details">
                        <div class="admin-user-name">
                            <?= htmlspecialchars($_SESSION['user']['first_name'] . ' ' . $_SESSION['user']['last_name']) ?>
                        </div>
                        <div class="admin-user-role">
                            <?= ucfirst(htmlspecialchars($_SESSION['user']['role'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </header>
        
        <main class="admin-content">
            <?php if (isset($breadcrumb)): ?>
            <nav class="admin-breadcrumb">
                <ol>
                    <?php foreach ($breadcrumb as $item): ?>
                        <li>
                            <?php if (isset($item['url'])): ?>
                                <a href="<?= htmlspecialchars($item['url']) ?>"><?= htmlspecialchars($item['title']) ?></a>
                            <?php else: ?>
                                <?= htmlspecialchars($item['title']) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <?php endif; ?>
            
            <?= $content ?>
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('adminSidebar');
            
            if (window.innerWidth <= 768) {
                const header = document.querySelector('.admin-header-content');
                const mobileMenuBtn = document.createElement('button');
                mobileMenuBtn.innerHTML = '<i class="bx bx-menu"></i>';
                mobileMenuBtn.className = 'mobile-menu-btn';
                mobileMenuBtn.style.cssText = `
                    background: none;
                    border: none;
                    font-size: 1.5rem;
                    cursor: pointer;
                    padding: 0.5rem;
                    margin-right: 1rem;
                    color: var(--text-primary);
                `;
                
                mobileMenuBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
                
                header.insertBefore(mobileMenuBtn, header.firstChild);
                
                document.addEventListener('click', function(e) {
                    if (!sidebar.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                });
            }
        });
    </script>
</body>
</html>
