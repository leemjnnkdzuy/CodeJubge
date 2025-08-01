<?php
?>

<header class="welcome-header">
    <nav class="welcome-navbar">
        <div class="container">
            <div class="navbar-logo">
                <a href="/" class="logo-link">
                    <span class="logo-text">CodeJudge</span>
                </a>
            </div>
            
            <div class="navbar-nav">
                <a href="/competitions" class="nav-item">Cuộc Thi</a>
                <a href="/problems" class="nav-item">Bài Toán</a>
                <a href="/leaderboard" class="nav-item">Xếp Hạng</a>
            </div>
            
            <div class="navbar-search">
                <div class="search-container">
                    <input type="text" placeholder="Tìm kiếm bài toán..." class="search-input">
                    <button class="search-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div class="navbar-auth">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="/profile" class="auth-btn profile-btn">Hồ Sơ</a>
                    <a href="/logout" class="auth-btn logout-btn">Đăng Xuất</a>
                <?php else: ?>
                    <a href="/login" class="auth-btn login-btn">Đăng Nhập</a>
                    <a href="/register" class="auth-btn signup-btn">Đăng Ký</a>
                <?php endif; ?>
            </div>
            
            <div class="mobile-menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>
</header>