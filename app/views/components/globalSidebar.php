<?php
$current_uri = $_SERVER['REQUEST_URI'];
$current_page = basename($current_uri);
?>

<div class="global-sidebar" id="globalSidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class='bx bx-menu'></i>
        </button>
        <div class="logo">
            <a href="/" class="logo-link">
                <span class="logo-text">CodeJudge</span>
            </a>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="/home" class="nav-link <?= $current_uri === '/' || $current_uri === '/home' ? 'active' : '' ?>">
                    <i class='bx bxs-home'></i>
                    <span class="nav-text">Trang Chủ</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/problems" class="nav-link <?= strpos($current_uri, '/problems') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-brain'></i>
                    <span class="nav-text">Bài Tập</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/contests" class="nav-link <?= strpos($current_uri, '/contests') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-trophy'></i>
                    <span class="nav-text">Cuộc Thi</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/submissions" class="nav-link <?= strpos($current_uri, '/submissions') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-file-doc'></i>
                    <span class="nav-text">Bài Nộp</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/leaderboard" class="nav-link <?= strpos($current_uri, '/leaderboard') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-bar-chart-alt-2'></i>
                    <span class="nav-text">Bảng Xếp Hạng</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/discussions" class="nav-link <?= strpos($current_uri, '/discussions') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-conversation'></i>
                    <span class="nav-text">Thảo luận</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/learning" class="nav-link <?= strpos($current_uri, '/learning') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-graduation'></i>
                    <span class="nav-text">Học tập</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <ul class="nav-list">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php
                    if (!isset($currentUser)) {
                        require_once APP_PATH . '/models/UserModel.php';
                        require_once APP_PATH . '/helpers/AvatarHelper.php';
                        $userModel = new UserModel();
                        $currentUser = $userModel->getUserById($_SESSION['user_id']);
                    }
                    ?>
                    <li class="nav-item user-profile-item">
                        <a href="/profile" class="nav-link user-profile-link <?= strpos($current_uri, '/profile') !== false ? 'active' : '' ?>">
                            <div class="user-avatar-sidebar">
                                <?php if ($currentUser && $currentUser['avatar']): ?>
                                    <img src="<?= AvatarHelper::base64ToImageSrc($currentUser['avatar']) ?>" alt="Avatar" class="avatar-image">
                                <?php else: ?>
                                    <div class="avatar-initials">
                                        <?= AvatarHelper::getInitials($currentUser ? $currentUser['first_name'] . ' ' . $currentUser['last_name'] : 'U') ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <span class="nav-text user-name"><?= $currentUser ? htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) : 'Người dùng' ?></span>
                        </a>
                    </li>
                    <?php if (isset($currentUser['role']) && $currentUser['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a href="/admin" class="nav-link">
                            <i class='bx bxs-cog'></i>
                            <span class="nav-text">Admin Panel</span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a href="/logout" class="nav-link">
                            <i class='bx bx-log-out'></i>
                            <span class="nav-text">Đăng xuất</span>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a href="/login" class="nav-link">
                            <i class='bx bx-log-in'></i>
                            <span class="nav-text">Đăng nhập</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="/register" class="nav-link">
                            <i class='bx bx-user-plus'></i>
                            <span class="nav-text">Đăng ký</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('globalSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.getElementById('mainContent');
    
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        
        if (sidebar.classList.contains('collapsed')) {
            mainContent.classList.add('sidebar-collapsed');
        } else {
            mainContent.classList.remove('sidebar-collapsed');
        }
        
        localStorage.setItem('globalSidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
    
    const isCollapsed = localStorage.getItem('globalSidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
    }
});
</script>