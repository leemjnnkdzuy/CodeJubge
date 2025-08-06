<div class="admin-page-header">
    <div class="admin-page-title">
        <h2>Quản lý Contests</h2>
        <p>Tạo và quản lý các cuộc thi lập trình</p>
    </div>
    <div class="admin-page-actions">
        <button class="btn btn-primary">
            <i class='bx bx-plus'></i>
            Tạo Contest
        </button>
    </div>
</div>

<div class="admin-filters">
    <div class="admin-search">
        <input type="text" placeholder="Tìm kiếm contest..." class="search-input">
        <i class='bx bx-search'></i>
    </div>
    <select class="filter-select">
        <option value="">Tất cả trạng thái</option>
        <option value="upcoming">Sắp diễn ra</option>
        <option value="ongoing">Đang diễn ra</option>
        <option value="ended">Đã kết thúc</option>
    </select>
    <select class="filter-select">
        <option value="">Tất cả loại</option>
        <option value="public">Public</option>
        <option value="private">Private</option>
    </select>
</div>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Thời gian bắt đầu</th>
                <th>Thời gian kết thúc</th>
                <th>Thời lượng</th>
                <th>Loại</th>
                <th>Participants</th>
                <th>Problems</th>
                <th>Trạng thái</th>
                <th>Tạo bởi</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="11" class="no-data">
                    <div class="empty-state">
                        <i class='bx bx-trophy' style="font-size: 3rem; color: var(--text-muted); margin-bottom: var(--spacing-md);"></i>
                        <h3>Chưa có contests</h3>
                        <p>Tạo contest đầu tiên để bắt đầu tổ chức cuộc thi</p>
                        <button class="btn btn-primary" style="margin-top: var(--spacing-md);">
                            <i class='bx bx-plus'></i>
                            Tạo Contest đầu tiên
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<!-- Contest Management Features Coming Soon -->
<div class="feature-preview">
    <h3>Tính năng Contest sắp ra mắt:</h3>
    <div class="feature-grid">
        <div class="feature-card">
            <i class='bx bx-time'></i>
            <h4>Scheduled Contests</h4>
            <p>Lên lịch contests tự động</p>
        </div>
        <div class="feature-card">
            <i class='bx bx-group'></i>
            <h4>Team Contests</h4>
            <p>Hỗ trợ contests theo nhóm</p>
        </div>
        <div class="feature-card">
            <i class='bx bx-bar-chart'></i>
            <h4>Real-time Leaderboard</h4>
            <p>Bảng xếp hạng theo thời gian thực</p>
        </div>
        <div class="feature-card">
            <i class='bx bx-shield'></i>
            <h4>Plagiarism Detection</h4>
            <p>Phát hiện đạo văn tự động</p>
        </div>
        <div class="feature-card">
            <i class='bx bx-trophy'></i>
            <h4>Rating System</h4>
            <p>Hệ thống xếp hạng như Codeforces</p>
        </div>
        <div class="feature-card">
            <i class='bx bx-bell'></i>
            <h4>Notifications</h4>
            <p>Thông báo tự động cho participants</p>
        </div>
    </div>
</div>

<style>
.empty-state {
    text-align: center;
    padding: var(--spacing-xxl);
}

.empty-state h3 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
}

.empty-state p {
    color: var(--text-secondary);
    margin-bottom: 0;
}

.feature-preview {
    margin-top: var(--spacing-xxl);
    padding: var(--spacing-xl);
    background: var(--white);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-light);
    border: 1px solid var(--border-color);
}

.feature-preview h3 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-lg);
    text-align: center;
}

.feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: var(--spacing-lg);
}

.feature-card {
    padding: var(--spacing-lg);
    border: 2px dashed var(--border-color);
    border-radius: var(--radius-lg);
    text-align: center;
    transition: all 0.3s ease;
}

.feature-card:hover {
    border-color: var(--primary-blue);
    background: var(--primary-blue-light);
}

.feature-card i {
    font-size: 2rem;
    color: var(--primary-blue);
    margin-bottom: var(--spacing-md);
}

.feature-card h4 {
    color: var(--text-primary);
    margin-bottom: var(--spacing-sm);
    font-size: 1.125rem;
}

.feature-card p {
    color: var(--text-secondary);
    margin: 0;
    font-size: 0.875rem;
}

/* Status badges for contests */
.contest-status {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.contest-upcoming {
    background: #d1ecf1;
    color: #0c5460;
}

.contest-ongoing {
    background: #d4edda;
    color: #28a745;
}

.contest-ended {
    background: var(--medium-gray);
    color: var(--text-primary);
}

.contest-public {
    background: var(--primary-blue-light);
    color: var(--primary-blue);
}

.contest-private {
    background: #fff3cd;
    color: #856404;
}

@media (max-width: 768px) {
    .feature-grid {
        grid-template-columns: 1fr;
    }
    
    .admin-table {
        min-width: 900px;
    }
}
</style>
