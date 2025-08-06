<div class="admin-page-header">
    <div class="admin-page-title">
        <h2>Quản lý Problems</h2>
        <p>Xem và quản lý tất cả bài tập trong hệ thống</p>
    </div>
    <div class="admin-page-actions">
        <button class="btn btn-primary">
            <i class='bx bx-plus'></i>
            Thêm Problem
        </button>
    </div>
</div>

<div class="admin-filters">
    <div class="admin-search">
        <input type="text" placeholder="Tìm kiếm problem..." class="search-input">
        <i class='bx bx-search'></i>
    </div>
    <select class="filter-select">
        <option value="">Tất cả độ khó</option>
        <option value="easy">Easy</option>
        <option value="medium">Medium</option>
        <option value="hard">Hard</option>
    </select>
    <select class="filter-select">
        <option value="">Tất cả trạng thái</option>
        <option value="active">Hoạt động</option>
        <option value="inactive">Không hoạt động</option>
    </select>
</div>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tiêu đề</th>
                <th>Slug</th>
                <th>Độ khó</th>
                <th>Category</th>
                <th>Solved/Attempts</th>
                <th>Acceptance Rate</th>
                <th>Trạng thái</th>
                <th>Tạo bởi</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($problems) && is_array($problems)): ?>
                <?php foreach ($problems as $problem): ?>
                <tr>
                    <td><?= htmlspecialchars($problem['id']) ?></td>
                    <td>
                        <div class="problem-title">
                            <a href="/problems/<?= htmlspecialchars($problem['slug']) ?>" target="_blank">
                                <?= htmlspecialchars($problem['title']) ?>
                            </a>
                        </div>
                    </td>
                    <td>
                        <span class="problem-slug"><?= htmlspecialchars($problem['slug']) ?></span>
                    </td>
                    <td>
                        <span class="difficulty-badge difficulty-<?= htmlspecialchars($problem['difficulty']) ?>">
                            <?= ucfirst(htmlspecialchars($problem['difficulty'])) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($problem['category'] ?? 'N/A') ?></td>
                    <td>
                        <div class="solve-stats">
                            <span class="solved"><?= number_format($problem['solved_count'] ?? 0) ?></span>
                            /
                            <span class="attempts"><?= number_format($problem['attempt_count'] ?? 0) ?></span>
                        </div>
                    </td>
                    <td>
                        <?php
                        $acceptance = $problem['acceptance_rate'] ?? 0;
                        $acceptanceClass = '';
                        if ($acceptance >= 70) $acceptanceClass = 'high';
                        elseif ($acceptance >= 40) $acceptanceClass = 'medium';
                        else $acceptanceClass = 'low';
                        ?>
                        <span class="acceptance-rate acceptance-<?= $acceptanceClass ?>">
                            <?= number_format($acceptance, 1) ?>%
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?= $problem['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $problem['is_active'] ? 'Hoạt động' : 'Không hoạt động' ?>
                        </span>
                    </td>
                    <td>
                        <?php if (isset($problem['creator_name'])): ?>
                            <?= htmlspecialchars($problem['creator_name']) ?>
                        <?php else: ?>
                            <span class="text-muted">N/A</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($problem['created_at'])) ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-edit" title="Chỉnh sửa">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn-action btn-view" title="Xem chi tiết">
                                <i class='bx bx-show'></i>
                            </button>
                            <button class="btn-action btn-test" title="Test cases">
                                <i class='bx bx-bug'></i>
                            </button>
                            <button class="btn-action btn-delete" title="Xóa">
                                <i class='bx bx-trash'></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" class="no-data">Không có dữ liệu problem</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.problem-title a {
    color: var(--text-primary);
    text-decoration: none;
    font-weight: 500;
}

.problem-title a:hover {
    color: var(--primary-blue);
    text-decoration: underline;
}

.problem-slug {
    font-family: monospace;
    font-size: 0.875rem;
    color: var(--text-secondary);
    background: var(--light-gray);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
}

.difficulty-badge {
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.difficulty-easy {
    background: #d4edda;
    color: #28a745;
}

.difficulty-medium {
    background: #fff3cd;
    color: #856404;
}

.difficulty-hard {
    background: #f8d7da;
    color: #721c24;
}

.solve-stats {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 0.875rem;
}

.solved {
    font-weight: 600;
    color: var(--primary-blue);
}

.attempts {
    color: var(--text-secondary);
}

.acceptance-rate {
    font-weight: 600;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.875rem;
}

.acceptance-high {
    background: #d4edda;
    color: #28a745;
}

.acceptance-medium {
    background: #fff3cd;
    color: #856404;
}

.acceptance-low {
    background: #f8d7da;
    color: #721c24;
}

.btn-test {
    background: var(--secondary-purple);
    color: var(--white);
}

.btn-test:hover {
    background: #4527a0;
}

@media (max-width: 768px) {
    .admin-table {
        min-width: 1000px;
    }
}
</style>
