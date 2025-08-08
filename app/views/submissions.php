<?php
$title = $title ?? 'Lịch sử nộp bài - CodeJudge';
$description = $description ?? 'Xem lại tất cả submissions của bạn trên CodeJudge';

ob_start();
?>

<div class="submissions-container">
    <div class="submissions-header">
        <div class="submissions-header-content">
            <div class="submissions-header-text">
                <h1>
                    Lịch sử nộp bài
                </h1>
                <p>Xem và quản lý tất cả submissions của bạn</p>
            </div>
        </div>
    </div>
    
    <div class="submissions-filters">
        <form method="GET" class="filters-form">
            <div class="filter-group">
                <label for="status">Trạng thái:</label>
                <select name="status" id="status" class="filter-select">
                    <option value="">Tất cả</option>
                    <option value="accepted" <?= $filters['status'] === 'accepted' ? 'selected' : '' ?>>Accepted</option>
                    <option value="wrong_answer" <?= $filters['status'] === 'wrong_answer' ? 'selected' : '' ?>>Wrong Answer</option>
                    <option value="time_limit" <?= $filters['status'] === 'time_limit' ? 'selected' : '' ?>>Time Limit</option>
                    <option value="memory_limit" <?= $filters['status'] === 'memory_limit' ? 'selected' : '' ?>>Memory Limit</option>
                    <option value="runtime_error" <?= $filters['status'] === 'runtime_error' ? 'selected' : '' ?>>Runtime Error</option>
                    <option value="compile_error" <?= $filters['status'] === 'compile_error' ? 'selected' : '' ?>>Compile Error</option>
                    <option value="pending" <?= $filters['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="language">Ngôn ngữ:</label>
                <select name="language" id="language" class="filter-select">
                    <option value="">Tất cả</option>
                    <option value="cpp" <?= $filters['language'] === 'cpp' ? 'selected' : '' ?>>C++</option>
                    <option value="python" <?= $filters['language'] === 'python' ? 'selected' : '' ?>>Python</option>
                    <option value="java" <?= $filters['language'] === 'java' ? 'selected' : '' ?>>Java</option>
                    <option value="javascript" <?= $filters['language'] === 'javascript' ? 'selected' : '' ?>>JavaScript</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="problem_id">Bài toán:</label>
                <select name="problem_id" id="problem_id" class="filter-select">
                    <option value="">Tất cả</option>
                    <?php foreach ($problems as $problem): ?>
                    <option value="<?= $problem['id'] ?>" <?= $filters['problem_id'] == $problem['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($problem['title']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="filter-btn">
                <i class='bx bx-filter'></i>
                Lọc
            </button>
            
            <?php if (!empty(array_filter($filters))): ?>
            <a href="/submissions" class="clear-filters-btn">
                <i class='bx bx-x'></i>
                Xóa filter
            </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="submissions-content">
        <?php if (isset($error)): ?>
        <div class="error-message">
            <i class='bx bx-error'></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php elseif (empty($submissions)): ?>
        <div class="no-submissions">
            <i class='bx bx-code-alt'></i>
            <h3>Chưa có submission nào</h3>
            <p>Hãy bắt đầu giải bài để xem lịch sử submissions của bạn</p>
            <a href="/problems" class="btn btn-primary">
                <i class='bx bx-play'></i>
                Giải bài ngay
            </a>
        </div>
        <?php else: ?>
        <div class="submissions-table-container">
            <table class="submissions-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Bài toán</th>
                        <th>Ngôn ngữ</th>
                        <th>Trạng thái</th>
                        <th>Score</th>
                        <th>Runtime</th>
                        <th>Memory</th>
                        <th>Test Cases</th>
                        <th>Thời gian nộp</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submissions as $submission): ?>
                    <tr class="submission-row">
                        <td class="submission-id">
                            #<?= $submission['id'] ?>
                        </td>
                        <td class="submission-problem">
                            <div class="problem-info">
                                <a href="/problems/<?= htmlspecialchars($submission['problem_slug']) ?>" class="problem-title">
                                    <?= htmlspecialchars($submission['problem_title']) ?>
                                </a>
                                <span class="difficulty difficulty-<?= strtolower($submission['difficulty']) ?>">
                                    <?= ucfirst($submission['difficulty']) ?>
                                </span>
                            </div>
                        </td>
                        <td class="submission-language">
                            <span class="language-badge language-<?= $submission['language'] ?>">
                                <?= ucfirst($submission['language']) ?>
                            </span>
                        </td>
                        <td class="submission-status">
                            <span class="status-badge status-<?= strtolower(str_replace('_', '-', $submission['status'])) ?>">
                                <?php
                                $statusMap = [
                                    'accepted' => 'Accepted',
                                    'wrong_answer' => 'Wrong Answer',
                                    'time_limit' => 'Time Limit',
                                    'memory_limit' => 'Memory Limit',
                                    'runtime_error' => 'Runtime Error',
                                    'compile_error' => 'Compile Error',
                                    'pending' => 'Pending'
                                ];
                                echo $statusMap[$submission['status']] ?? ucfirst($submission['status']);
                                ?>
                            </span>
                        </td>
                        <td class="submission-score">
                            <span class="score"><?= number_format($submission['score'], 2) ?></span>
                        </td>
                        <td class="submission-runtime">
                            <?php if ($submission['runtime']): ?>
                                <span class="runtime"><?= $submission['runtime'] ?>ms</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="submission-memory">
                            <?php if ($submission['memory_used']): ?>
                                <span class="memory"><?= round($submission['memory_used'] / 1024, 1) ?>MB</span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td class="submission-testcases">
                            <div class="test-cases">
                                <span class="passed"><?= $submission['test_cases_passed'] ?></span>
                                /
                                <span class="total"><?= $submission['total_test_cases'] ?></span>
                            </div>
                        </td>
                        <td class="submission-time">
                            <div class="submit-time">
                                <div class="date"><?= date('d/m/Y', strtotime($submission['submitted_at'])) ?></div>
                                <div class="time"><?= date('H:i:s', strtotime($submission['submitted_at'])) ?></div>
                            </div>
                        </td>
                        <td class="submission-actions">
                            <button class="btn-action btn-view" data-submission-id="<?= $submission['id'] ?>" title="Xem chi tiết">
                                <i class='bx bx-show'></i>
                            </button>
                            <a href="/problems/<?= htmlspecialchars($submission['problem_slug']) ?>" class="btn-action btn-retry" title="Thử lại">
                                <i class='bx bx-redo'></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="pagination-container">
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="?page=<?= $currentPage - 1 ?>&<?= http_build_query($filters) ?>" class="pagination-btn">
                        <i class='bx bx-chevron-left'></i>
                        Trước
                    </a>
                <?php endif; ?>
                
                <div class="pagination-info">
                    Trang <?= $currentPage ?> / <?= $totalPages ?>
                </div>
                
                <?php if ($currentPage < $totalPages): ?>
                    <a href="?page=<?= $currentPage + 1 ?>&<?= http_build_query($filters) ?>" class="pagination-btn">
                        Sau
                        <i class='bx bx-chevron-right'></i>
                    </a>
                <?php endif; ?>
            </div>
            
            <div class="pagination-summary">
                Hiển thị <?= (($currentPage - 1) * 20) + 1 ?> - <?= min($currentPage * 20, $totalSubmissions) ?> 
                trong tổng số <?= number_format($totalSubmissions) ?> submissions
            </div>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>
<script src="/js/submissions.js"></script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>
