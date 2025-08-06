<?php
$current_uri = $_SERVER['REQUEST_URI'];
$current_page = basename($current_uri);
?>

<div class="admin-sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle" id="sidebarToggle">
            <i class='bx bx-menu'></i>
        </button>
        <div class="logo">
            <a href="/admin" class="logo-link">
                <span class="logo-text">CodeJudge Admin</span>
            </a>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li class="nav-item">
                <a href="/admin" class="nav-link <?= $current_uri === '/admin' ? 'active' : '' ?>">
                    <i class='bx bxs-dashboard'></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/users" class="nav-link <?= strpos($current_uri, '/admin/users') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-user-account'></i>
                    <span class="nav-text">Quản lý Users</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/problems" class="nav-link <?= strpos($current_uri, '/admin/problems') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-brain'></i>
                    <span class="nav-text">Quản lý Problems</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/submissions" class="nav-link <?= strpos($current_uri, '/admin/submissions') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-file-doc'></i>
                    <span class="nav-text">Submissions</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/contests" class="nav-link <?= strpos($current_uri, '/admin/contests') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-trophy'></i>
                    <span class="nav-text">Contests</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/test-cases" class="nav-link <?= strpos($current_uri, '/admin/test-cases') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-bug'></i>
                    <span class="nav-text">Test Cases</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/discussions" class="nav-link <?= strpos($current_uri, '/admin/discussions') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-conversation'></i>
                    <span class="nav-text">Discussions</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/notifications" class="nav-link <?= strpos($current_uri, '/admin/notifications') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-bell'></i>
                    <span class="nav-text">Notifications</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/analytics" class="nav-link <?= strpos($current_uri, '/admin/analytics') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-bar-chart-alt-2'></i>
                    <span class="nav-text">Analytics</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a href="/admin/settings" class="nav-link <?= strpos($current_uri, '/admin/settings') !== false ? 'active' : '' ?>">
                    <i class='bx bxs-cog'></i>
                    <span class="nav-text">Settings</span>
                </a>
            </li>
        </ul>
        
        <div class="sidebar-footer">
            <ul class="nav-list">
                <li class="nav-item">
                    <a href="/" class="nav-link" target="_blank">
                        <i class='bx bx-globe'></i>
                        <span class="nav-text">View Site</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/logout" class="nav-link">
                        <i class='bx bx-log-out'></i>
                        <span class="nav-text">Logout</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mainContent = document.getElementById('adminMainContent');
    
    // Toggle sidebar
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        
        // Update main content class
        if (sidebar.classList.contains('collapsed')) {
            mainContent.classList.add('sidebar-collapsed');
        } else {
            mainContent.classList.remove('sidebar-collapsed');
        }
        
        // Save state to localStorage
        localStorage.setItem('adminSidebarCollapsed', sidebar.classList.contains('collapsed'));
    });
    
    // Restore sidebar state
    const isCollapsed = localStorage.getItem('adminSidebarCollapsed') === 'true';
    if (isCollapsed) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('sidebar-collapsed');
    }
});
</script>
