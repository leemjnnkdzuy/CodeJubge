<?php
$title = htmlspecialchars($problem['title'] ?? 'Chi tiết bài tập - CodeJudge');
$description = htmlspecialchars($problem['description'] ?? 'Giải quyết bài tập lập trình với CodeJudge');

require_once ROOT_PATH . '/config/config.php';

if (!isset($SUPPORTED_LANGUAGES)) {
    $SUPPORTED_LANGUAGES = [
        'python' => [
            'name' => 'Python 3',
            'extension' => '.py',
            'template' => "def solution():\n    # Your code here\n    pass\n\n# Test your solution\nprint(solution())"
        ],
        'javascript' => [
            'name' => 'JavaScript',
            'extension' => '.js',
            'template' => "function solution() {\n    // Your code here\n}\n\n// Test your solution\nconsole.log(solution());"
        ],
        'cpp' => [
            'name' => 'C/C++',
            'extension' => '.cpp',
            'template' => "#include <iostream>\nusing namespace std;\n\nint main() {\n    // Your code here\n    return 0;\n}"
        ],
        'java' => [
            'name' => 'Java',
            'extension' => '.java',
            'template' => "public class Solution {\n    public static void main(String[] args) {\n        // Your code here\n    }\n}"
        ]
    ];
}

ob_start();
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>

<div class="problem-workspace" data-problem-id="<?= htmlspecialchars($problem['id'] ?? 0) ?>">
    <div class="problem-sidebar">
        <div class="problem-tabs">
            <div class="problem-tab active" data-tab="description">
                <i class="bx bx-file-blank"></i>
                <span>Mô tả</span>
            </div>
            <div class="problem-tab" data-tab="editorial">
                <i class="bx bx-bulb"></i>
                <span>Lời giải</span>
            </div>
            <div class="problem-tab" data-tab="submissions">
                <i class="bx bx-history"></i>
                <span>Submissions</span>
            </div>
        </div>
        
        <div class="problem-content">
            <div class="tab-content active" id="description-tab">
                <div class="problem-header">
                    <h1 class="problem-title"><?= htmlspecialchars($problem['title']) ?></h1>
                    
                    <div class="problem-meta">
                        <span class="difficulty-badge difficulty-<?= strtolower($problem['difficulty']) ?>">
                            <?= ucfirst($problem['difficulty']) ?>
                        </span>
                        <?php if (!empty($problem['problem_types'])): ?>
                            <?php 
                            $types = is_string($problem['problem_types']) ? 
                                    json_decode($problem['problem_types'], true) : 
                                    $problem['problem_types'];
                            ?>
                            <?php foreach ($types as $type): ?>
                                <span class="problem-tag"><?= htmlspecialchars($type) ?></span>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <div class="problem-stats">
                        <div class="stat-item">
                            <i class="bx bx-check-circle"></i>
                            <span>Đã giải: <?= $problem['accepted_count'] ?? 0 ?></span>
                        </div>
                        <div class="stat-item">
                            <i class="bx bx-send"></i>
                            <span>Submissions: <?= $problem['submission_count'] ?? 0 ?></span>
                        </div>
                        <div class="stat-item">
                            <i class="bx bx-trending-up"></i>
                            <span>Tỷ lệ AC: <?= $problem['acceptance_rate'] ?? 0 ?>%</span>
                        </div>
                        <div class="stat-item">
                            <i class="bx bx-time"></i>
                            <span>Thời gian: <?= $problem['time_limit'] ?? 1000 ?>ms</span>
                        </div>
                        <div class="stat-item">
                            <i class="bx bx-memory-card"></i>
                            <span>Bộ nhớ: <?= $problem['memory_limit'] ?? 256 ?>MB</span>
                        </div>
                    </div>
                </div>
                
                <div class="problem-section">
                    <h2 class="section-title">
                        <i class="bx bx-file-blank"></i>
                        Mô tả đề bài
                    </h2>
                    <div class="problem-description">
                        <?= $problem['description'] ?? '' ?>
                    </div>
                </div>
                
                <?php if (!empty($problem['input_format'])): ?>
                <div class="problem-section">
                    <h2 class="section-title">
                        <i class="bx bx-import"></i>
                        Định dạng đầu vào
                    </h2>
                    <div class="problem-description">
                        <?= $problem['input_format'] ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($problem['output_format'])): ?>
                <div class="problem-section">
                    <h2 class="section-title">
                        <i class="bx bx-export"></i>
                        Định dạng đầu ra
                    </h2>
                    <div class="problem-description">
                        <?= $problem['output_format'] ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($problem['sample_input']) || !empty($problem['sample_output'])): ?>
                <div class="problem-section">
                    <h2 class="section-title">
                        <i class="bx bx-code-alt"></i>
                        Ví dụ
                    </h2>
                    
                    <?php if (!empty($problem['sample_input'])): ?>
                    <div class="sample-case">
                        <div class="sample-title">Đầu vào:</div>
                        <pre class="sample-code"><?= htmlspecialchars($problem['sample_input']) ?></pre>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($problem['sample_output'])): ?>
                    <div class="sample-case">
                        <div class="sample-title">Đầu ra:</div>
                        <pre class="sample-code"><?= htmlspecialchars($problem['sample_output']) ?></pre>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($problem['explanation'])): ?>
                <div class="problem-section">
                    <h2 class="section-title">
                        <i class="bx bx-bulb"></i>
                        Giải thích
                    </h2>
                    <div class="problem-description">
                        <?= $problem['explanation'] ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="tab-content" id="editorial-tab">
                <div class="problem-section">
                    <h2 class="section-title">
                        <i class="bx bx-bulb"></i>
                        Lời giải chi tiết
                    </h2>
                    <div class="problem-description">
                        <p>Lời giải chi tiết sẽ được cập nhật sau khi bạn nộp bài thành công.</p>
                        <div class="empty-state">
                            <i class="bx bx-lock-alt"></i>
                            <p>Hãy thử giải bài toán trước khi xem lời giải!</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-content" id="submissions-tab">
                <div class="submissions-global-header">
                    <div class="submissions-toggle">
                        <button class="btn btn-outline btn-sm single-toggle-btn" id="submissionsToggle" data-current="my-submissions">
                            <span id="toggleText">
                                <i class="bx bx-user"></i>
                                Bài nộp của bạn
                            </span>
                            <i class="bx bx-chevron-down"></i>
                        </button>
                    </div>
                </div>

                <div class="submissions-view-container">
                    <div class="problem-section submissions-view" id="my-submissions-view">
                        <?php if (!empty($userSubmissions)): ?>
                        <div class="submissions-container">
                            <?php foreach ($userSubmissions as $submission): ?>
                            <div class="submission-item status-<?= strtolower(str_replace([' ', '_'], ['_', '_'], $submission['status'])) ?>">
                                <div class="submission-header">
                                    <div class="submission-status">
                                        <?php 
                                        $statusClass = $submission['status'] === 'Accepted' ? 'passed' : 'failed';
                                        $statusIcon = $submission['status'] === 'Accepted' ? 'bx-check-circle' : 'bx-x-circle';
                                        ?>
                                        <div class="status-icon status-<?= $statusClass ?>">
                                            <i class="bx <?= $statusIcon ?>"></i>
                                        </div>
                                        <span class="status-text"><?= htmlspecialchars($submission['status']) ?></span>
                                    </div>
                                    <div class="submission-meta">
                                        <span class="submission-time">
                                            <i class="bx bx-time"></i> 
                                            <?= date('d/m/Y H:i:s', strtotime($submission['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="submission-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Ngôn ngữ:</span>
                                        <span class="detail-value language-<?= strtolower($submission['language']) ?>">
                                            <?= htmlspecialchars($submission['language']) ?>
                                        </span>
                                    </div>
                                    <?php if (isset($submission['test_cases_passed']) && isset($submission['total_test_cases'])): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Test cases:</span>
                                        <span class="detail-value">
                                            <?= $submission['test_cases_passed'] ?>/<?= $submission['total_test_cases'] ?>
                                        </span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if ($submission['status'] === 'Accepted' && ($submission['execution_time'] > 0 || $submission['memory_usage'] > 0)): ?>
                                    <div class="detail-item">
                                        <span class="detail-label">Thời gian:</span>
                                        <span class="detail-value"><?= $submission['execution_time'] ?>ms</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Bộ nhớ:</span>
                                        <span class="detail-value"><?= number_format($submission['memory_usage'], 2) ?>MB</span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($submission['error_message'])): ?>
                                    <div class="detail-item error-message">
                                        <span class="detail-label">Lỗi:</span>
                                        <span class="detail-value"><?= htmlspecialchars($submission['error_message']) ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="bx bx-code-alt"></i>
                            <p>Bạn chưa nộp bài nào cho bài tập này.</p>
                            <p>Hãy viết code và nộp bài để xem kết quả!</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="problem-section submissions-view hidden" id="all-submissions-view">
                        <div class="submissions-container all-submissions">
                            <?php if (!empty($exampleSubmissions)): ?>
                                <?php foreach ($exampleSubmissions as $submission): ?>
                                <div class="submission-item status-accepted">
                                    <div class="submission-header">
                                        <div class="submission-status">
                                            <div class="status-icon status-passed">
                                                <i class="bx bx-check-circle"></i>
                                            </div>
                                            <span class="status-text">Accepted</span>
                                        </div>
                                        <div class="submission-meta">
                                            <span class="submission-user">
                                                <i class="bx bx-user"></i>
                                                <?= htmlspecialchars($submission['username']) ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="submission-details">
                                        <div class="detail-item">
                                            <span class="detail-label">Ngôn ngữ:</span>
                                            <span class="detail-value language-<?= strtolower($submission['language']) ?>">
                                                <?= htmlspecialchars($submission['language']) ?>
                                            </span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Thời gian:</span>
                                            <span class="detail-value"><?= $submission['execution_time'] ?>ms</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Bộ nhớ:</span>
                                            <span class="detail-value"><?= number_format($submission['memory_usage'], 2) ?>MB</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">Nộp lúc:</span>
                                            <span class="detail-value">
                                                <?= date('d/m/Y H:i', strtotime($submission['created_at'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="bx bx-code-alt"></i>
                                    <p>Chưa có submission nào cho bài này.</p>
                                    <p>Hãy thử giải bài để trở thành người đầu tiên!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="editor-panel">
        <div class="editor-header">
            <div class="editor-tabs">
                <select id="languageSelect" class="language-select">
                    <?php foreach ($SUPPORTED_LANGUAGES as $key => $lang): ?>
                        <option value="<?= $key ?>"><?= $lang['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
                
            <div class="editor-controls">
                <a href="/home" class="btn btn-outline back-button">
                    <i class="bx bx-arrow-back"></i>
                    <span>Trang chủ</span>
                </a>
                
                <button class="btn btn-outline" id="runCode">
                    <i class="bx bx-play"></i>
                    Chạy thử
                </button>
                
                <button class="btn btn-primary" id="submitCode">
                    <i class="bx bx-send"></i>
                    Nộp bài
                </button>
            </div>
        </div>
        
        <div class="editor-container">
            <div id="monaco-editor"></div>
        </div>
        
        <div class="console-panel">
            <div class="console-header">
                <span class="console-title">Console</span>
                <button class="btn btn-outline btn-sm" id="clearConsole">
                    <i class="bx bx-trash"></i>
                    Xóa
                </button>
            </div>
            <div class="console-content" id="consoleOutput">
                <div class="console-message">
                    <i class="bx bx-info-circle"></i>
                    Nhấn "Chạy thử" để kiểm tra code của bạn với test case mẫu.
                </div>
            </div>
        </div>
    </div>
</div>

<div class="submit-popup-overlay" id="submitPopup">
    <div class="submit-popup">
        <div class="submit-popup-header">
            <h3 class="submit-popup-title">
                Xác nhận nộp bài
            </h3>
            <button class="submit-popup-close" id="closeSubmitPopup">
                <i class="bx bx-x"></i>
            </button>
        </div>
        
        <div class="submit-popup-body">
            <div class="submit-popup-icon">
                <i class="bx bx-code-alt"></i>
            </div>
            
            <p class="submit-popup-message">
                Bạn có chắc chắn muốn nộp bài giải cho bài tập 
                <strong><?= htmlspecialchars($problem['title']) ?></strong> không?
            </p>
            
            <div class="submit-popup-info">
                <div class="submit-info-item">
                    <i class="bx bx-code"></i>
                    <span>Ngôn ngữ: <span id="popupLanguage">Python</span></span>
                </div>
                <div class="submit-info-item">
                    <i class="bx bx-file-blank"></i>
                    <span>Số dòng code: <span id="popupLineCount">0</span></span>
                </div>
                <div class="submit-info-item">
                    <i class="bx bx-info-circle"></i>
                    <span class="submit-warning">
                        Lưu ý: Sau khi nộp bài, bạn vẫn có thể nộp lại nhiều lần.
                    </span>
                </div>
            </div>
        </div>
        
        <div class="submit-popup-footer">
            <button class="btn btn-outline" id="cancelSubmit">
                <i class="bx bx-x"></i>
                Hủy bỏ
            </button>
            <button class="btn btn-primary" id="confirmSubmit">
                <i class="bx bx-send"></i>
                Xác nhận nộp bài
            </button>
        </div>
    </div>
</div>

<script>
    window.languageTemplatesFromPHP = <?= json_encode(array_map(function($lang) { return $lang['template']; }, $SUPPORTED_LANGUAGES)) ?>;
    window.problemId = <?= $problem['id'] ?>;
</script>
<script src="/js/problemDetail.js"></script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesNothing.php';
?>
