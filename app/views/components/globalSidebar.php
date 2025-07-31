<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
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
                <a href="/home" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/' || $_SERVER['REQUEST_URI'] === '/home' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Trang Chủ</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/problems" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/problems') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-code"></i>
                    <span class="nav-text">Bài Tập</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/contests" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/contests') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-trophy"></i>
                    <span class="nav-text">Cuộc Thi</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/submissions" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/submissions') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-file-code"></i>
                    <span class="nav-text">Bài Nộp</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/leaderboard" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/leaderboard') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i>
                    <span class="nav-text">Bảng Xếp Hạng</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/discussions" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/discussions') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-comments"></i>
                    <span class="nav-text">Thảo luận</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/learning" class="nav-link <?= strpos($_SERVER['REQUEST_URI'], '/learning') === 0 ? 'active' : '' ?>">
                    <i class="fas fa-graduation-cap"></i>
                    <span class="nav-text">Học tập</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <div class="user-info">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="user-profile-section">
                        <?php
                        if (!isset($currentUser)) {
                            require_once APP_PATH . '/models/UserModel.php';
                            require_once APP_PATH . '/helpers/AvatarHelper.php';
                            $userModel = new UserModel();
                            $currentUser = $userModel->getUserById($_SESSION['user_id']);
                        }
                        ?>
                        <a href="/profile" class="user-link">
                            <div class="user-avatar">
                                <?php if ($currentUser && $currentUser['avatar']): ?>
                                    <img src="<?= AvatarHelper::base64ToImageSrc($currentUser['avatar']) ?>" alt="Avatar" class="avatar-img">
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                            </div>
                            <div class="user-details">
                                <span class="username"><?= $currentUser ? htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) : 'Người dùng' ?></span>
                                <span class="user-role">Rating: <?= isset($currentUser['rating']) && $currentUser['rating'] != -1 ? $currentUser['rating'] : 'Chưa có xếp hạng' ?></span>
                            </div>
                        </a>
                        <a href="/logout" class="logout-btn" title="Đăng xuất">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="/login" class="btn btn-primary">Đăng nhập</a>
                        <a href="/register" class="btn btn-secondary">Đăng ký</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</div>
