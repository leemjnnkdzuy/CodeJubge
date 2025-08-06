<div class="admin-page-header">
    <div class="admin-page-title">
        <h2>Submissions</h2>
        <p>Xem tất cả submissions gần đây</p>
    </div>
</div>

<div class="admin-filters">
    <div class="admin-search">
        <input type="text" placeholder="Tìm kiếm user hoặc problem..." class="search-input">
        <i class='bx bx-search'></i>
    </div>
    <select class="filter-select">
        <option value="">Tất cả trạng thái</option>
        <option value="accepted">Accepted</option>
        <option value="wrong_answer">Wrong Answer</option>
        <option value="time_limit">Time Limit</option>
        <option value="memory_limit">Memory Limit</option>
        <option value="runtime_error">Runtime Error</option>
        <option value="compile_error">Compile Error</option>
        <option value="pending">Pending</option>
    </select>
    <select class="filter-select">
        <option value="">Tất cả ngôn ngữ</option>
        <option value="python">Python</option>
        <option value="cpp">C++</option>
        <option value="java">Java</option>
        <option value="javascript">JavaScript</option>
    </select>
</div>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Problem</th>
                <th>Language</th>
                <th>Status</th>
                <th>Score</th>
                <th>Runtime</th>
                <th>Memory</th>
                <th>Test Cases</th>
                <th>Submitted At</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($submissions) && is_array($submissions)): ?>
                <?php 
                require_once APP_PATH . '/helpers/AvatarHelper.php';
                foreach ($submissions as $submission): 
                $userAvatar = AvatarHelper::base64ToImageSrc($submission['avatar'] ?? '');
                $userInitials = AvatarHelper::getInitials($submission['first_name'] . ' ' . $submission['last_name']);
                ?>
                <tr>
                    <td><?= htmlspecialchars($submission['id']) ?></td>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php if (!empty($submission['avatar'])): ?>
                                    <img src="<?= $userAvatar ?>" alt="Avatar" class="avatar-image">
                                <?php else: ?>
                                    <div class="avatar-initials"><?= $userInitials ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="user-details">
                                <div class="user-name"><?= htmlspecialchars($submission['first_name'] . ' ' . $submission['last_name']) ?></div>
                                <div class="username">@<?= htmlspecialchars($submission['username']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="problem-info">
                            <a href="/problems/<?= htmlspecialchars($submission['problem_slug']) ?>" target="_blank" class="problem-title">
                                <?= htmlspecialchars($submission['problem_title']) ?>
                            </a>
                        </div>
                    </td>
                    <td>
                        <span class="language-badge">
                            <?= ucfirst(htmlspecialchars($submission['language'])) ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?= htmlspecialchars($submission['status']) ?>">
                            <?= ucfirst(str_replace('_', ' ', htmlspecialchars($submission['status']))) ?>
                        </span>
                    </td>
                    <td>
                        <span class="score"><?= number_format($submission['score'], 2) ?></span>
                    </td>
                    <td>
                        <?php if ($submission['runtime']): ?>
                            <span class="runtime"><?= $submission['runtime'] ?>ms</span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($submission['memory_used']): ?>
                            <span class="memory"><?= number_format($submission['memory_used'] / 1024, 1) ?>MB</span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="test-cases">
                            <span class="passed"><?= $submission['test_cases_passed'] ?? 0 ?></span>
                            /
                            <span class="total"><?= $submission['total_test_cases'] ?? 0 ?></span>
                        </div>
                    </td>
                    <td>
                        <div class="submitted-at">
                            <div class="date"><?= date('d/m/Y', strtotime($submission['submitted_at'])) ?></div>
                            <div class="time"><?= date('H:i:s', strtotime($submission['submitted_at'])) ?></div>
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-view" title="Xem code">
                                <i class='bx bx-code'></i>
                            </button>
                            <button class="btn-action btn-details" title="Chi tiết">
                                <i class='bx bx-info-circle'></i>
                            </button>
                            <button class="btn-action btn-rejudge" title="Chấm lại">
                                <i class='bx bx-refresh'></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="11" class="no-data">Không có submissions</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<style>
.user-details {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.user-name {
    font-weight: 500;
    font-size: 0.875rem;
}

.username {
    font-size: 0.75rem;
    color: var(--text-muted);
    font-family: monospace;
}

.problem-info .problem-title {
    color: var(--text-primary);
    text-decoration: none;
    font-weight: 500;
}

.problem-info .problem-title:hover {
    color: var(--primary-blue);
    text-decoration: underline;
}

.language-badge {
    background: var(--medium-gray);
    color: var(--text-primary);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
}

.status-accepted {
    background: #d4edda;
    color: #28a745;
}

.status-wrong_answer {
    background: #f8d7da;
    color: #721c24;
}

.status-time_limit {
    background: #fff3cd;
    color: #856404;
}

.status-memory_limit {
    background: #fff3cd;
    color: #856404;
}

.status-runtime_error {
    background: #f8d7da;
    color: #721c24;
}

.status-compile_error {
    background: #f8d7da;
    color: #721c24;
}

.status-pending {
    background: #d1ecf1;
    color: #0c5460;
}

.score {
    font-weight: 600;
    color: var(--primary-blue);
}

.runtime {
    color: var(--secondary-green);
    font-weight: 500;
    font-size: 0.875rem;
}

.memory {
    color: var(--secondary-orange);
    font-weight: 500;
    font-size: 0.875rem;
}

.test-cases {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 0.875rem;
}

.test-cases .passed {
    font-weight: 600;
    color: var(--primary-blue);
}

.test-cases .total {
    color: var(--text-secondary);
}

.submitted-at {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-xs);
}

.submitted-at .date {
    font-weight: 500;
    font-size: 0.875rem;
}

.submitted-at .time {
    font-size: 0.75rem;
    color: var(--text-muted);
}

.btn-details {
    background: var(--primary-blue-light);
    color: var(--primary-blue);
}

.btn-details:hover {
    background: var(--primary-blue);
    color: var(--white);
}

.btn-rejudge {
    background: var(--secondary-green);
    color: var(--white);
}

.btn-rejudge:hover {
    background: #00bfa5;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--primary-blue);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-weight: 600;
    font-size: 0.75rem;
    overflow: hidden;
    position: relative;
}

.user-avatar .avatar-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.user-avatar .avatar-initials {
    font-weight: 600;
    font-size: 0.75rem;
    color: var(--white);
}

@media (max-width: 768px) {
    .admin-table {
        min-width: 1200px;
    }
    
    .user-info {
        min-width: 120px;
    }
}
</style>
