<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <div class="admin-stat-card-header">
            <div class="admin-stat-card-title">Tổng Users</div>
            <div class="admin-stat-card-icon primary">
                <i class='bx bxs-user-account'></i>
            </div>
        </div>
        <div class="admin-stat-card-value"><?= number_format($stats['totalUsers']) ?></div>
        <div class="admin-stat-card-change positive">
            +<?= $stats['recentUsers'] ?> trong 30 ngày qua
        </div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-card-header">
            <div class="admin-stat-card-title">Tổng Problems</div>
            <div class="admin-stat-card-icon success">
                <i class='bx bxs-brain'></i>
            </div>
        </div>
        <div class="admin-stat-card-value"><?= number_format($stats['totalProblems']) ?></div>
        <div class="admin-stat-card-change">Bài tập có sẵn</div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-card-header">
            <div class="admin-stat-card-title">Tổng Submissions</div>
            <div class="admin-stat-card-icon warning">
                <i class='bx bxs-file-doc'></i>
            </div>
        </div>
        <div class="admin-stat-card-value"><?= number_format($stats['totalSubmissions']) ?></div>
        <div class="admin-stat-card-change">Lần nộp bài</div>
    </div>
    
    <div class="admin-stat-card">
        <div class="admin-stat-card-header">
            <div class="admin-stat-card-title">Tỷ lệ Accept</div>
            <div class="admin-stat-card-icon <?= $stats['acceptanceRate'] >= 50 ? 'success' : 'danger' ?>">
                <i class='bx bxs-trophy'></i>
            </div>
        </div>
        <div class="admin-stat-card-value"><?= $stats['acceptanceRate'] ?>%</div>
        <div class="admin-stat-card-change <?= $stats['acceptanceRate'] >= 50 ? 'positive' : 'negative' ?>">
            <?= number_format($stats['successfulSubmissions']) ?>/<?= number_format($stats['totalSubmissions']) ?> thành công
        </div>
    </div>
</div>

<div class="admin-dashboard-content">
    <div class="admin-dashboard-row">
        <div class="admin-dashboard-col-8">
            <div class="admin-table-container">
                <div class="admin-table-header">
                    <h3>Recent Activities</h3>
                </div>
                <div class="admin-table-content">
                    <p class="text-muted">Tính năng theo dõi hoạt động sẽ được cập nhật sớm...</p>
                </div>
            </div>
        </div>
        
        <div class="admin-dashboard-col-4">
            <div class="admin-table-container">
                <div class="admin-table-header">
                    <h3>Quick Actions</h3>
                </div>
                <div class="admin-quick-actions">
                    <a href="/admin/users" class="admin-quick-action">
                        <i class='bx bxs-user-account'></i>
                        <span>Quản lý Users</span>
                        <i class='bx bx-chevron-right'></i>
                    </a>
                    <a href="/admin/problems" class="admin-quick-action">
                        <i class='bx bxs-brain'></i>
                        <span>Quản lý Problems</span>
                        <i class='bx bx-chevron-right'></i>
                    </a>
                    <a href="/admin/submissions" class="admin-quick-action">
                        <i class='bx bxs-file-doc'></i>
                        <span>Xem Submissions</span>
                        <i class='bx bx-chevron-right'></i>
                    </a>
                    <a href="/admin/contests" class="admin-quick-action">
                        <i class='bx bxs-trophy'></i>
                        <span>Quản lý Contests</span>
                        <i class='bx bx-chevron-right'></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard-content {
    margin-top: var(--spacing-xl);
}

.admin-dashboard-row {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: var(--spacing-xl);
}

.admin-table-header {
    padding: var(--spacing-lg);
    border-bottom: 1px solid var(--border-color);
}

.admin-table-header h3 {
    margin: 0;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--text-primary);
}

.admin-table-content {
    padding: var(--spacing-lg);
}

.admin-quick-actions {
    padding: var(--spacing-md);
}

.admin-quick-action {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
    text-decoration: none;
    color: var(--text-primary);
    border-radius: var(--radius-md);
    transition: background-color 0.3s ease;
    margin-bottom: var(--spacing-xs);
}

.admin-quick-action:hover {
    background-color: var(--light-gray);
}

.admin-quick-action i:first-child {
    color: var(--primary-blue);
    font-size: 1.2rem;
}

.admin-quick-action span {
    flex: 1;
    font-weight: 500;
}

.admin-quick-action i:last-child {
    color: var(--text-muted);
}

.text-muted {
    color: var(--text-muted);
    font-style: italic;
}

@media (max-width: 768px) {
    .admin-dashboard-row {
        grid-template-columns: 1fr;
    }
}
</style>
