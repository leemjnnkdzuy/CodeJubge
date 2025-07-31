<?php
// Prepare data for layout
$title = htmlspecialchars($problem['title'] ?? 'Chi tiết bài tập - CodeJudge');
$description = htmlspecialchars($problem['description'] ?? 'Giải quyết bài tập lập trình với CodeJudge');

// Include config để có $SUPPORTED_LANGUAGES
require_once ROOT_PATH . '/config/config.php';

// Ensure $SUPPORTED_LANGUAGES is available
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

// Start content buffering
ob_start();
?>

<!-- Monaco Editor Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs/loader.min.js"></script>

<div class="problem-workspace">
    <!-- Problem Description Panel -->
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
            <!-- Description Tab -->
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
            
            <!-- Editorial Tab -->
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
            
            <!-- Submissions Tab -->
            <div class="tab-content" id="submissions-tab">
                <?php if (!empty($userSubmissions)): ?>
                <div class="problem-section">
                    <h2 class="section-title">
                        <i class="bx bx-history"></i>
                        Lịch sử nộp bài của bạn
                    </h2>
                    <div class="submissions-container">
                        <?php foreach ($userSubmissions as $submission): ?>
                        <div class="submission-item status-<?= strtolower(str_replace(' ', '_', $submission['status'])) ?>">
                            <div class="submission-status">
                                <div class="status-icon status-<?= $submission['status'] === 'Accepted' ? 'passed' : 'failed' ?>"></div>
                                <span><?= ucfirst($submission['status']) ?></span>
                            </div>
                            <div class="submission-info">
                                <span class="submission-time"><?= date('d/m/Y H:i', strtotime($submission['created_at'])) ?></span>
                                <span class="submission-language"><?= $submission['language'] ?></span>
                                <?php if ($submission['status'] === 'Accepted'): ?>
                                <span class="submission-stats">
                                    <i class="bx bx-time"></i> <?= $submission['execution_time'] ?>ms
                                    <i class="bx bx-memory-card"></i> <?= $submission['memory_usage'] ?>MB
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="problem-section">
                    <div class="empty-state">
                        <i class="bx bx-code-alt"></i>
                        <p>Bạn chưa có submission nào cho bài này.</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Code Editor Panel -->
    <div class="editor-panel">
        <div class="editor-header">
            <div class="editor-tabs">
                <div class="editor-tab active" data-tab="code">
                    <i class="bx bx-code"></i>
                    Code
                </div>
                <div class="editor-tab" data-tab="testcase">
                    <i class="bx bx-test-tube"></i>
                    Testcase
                </div>
            </div>
            
            <div class="editor-controls">
                <select id="languageSelect" class="language-select">
                    <?php foreach ($SUPPORTED_LANGUAGES as $key => $lang): ?>
                        <option value="<?= $key ?>"><?= $lang['name'] ?></option>
                    <?php endforeach; ?>
                </select>
                
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

<!-- JavaScript -->
<script>
    // Language templates from PHP config
    const languageTemplates = <?= json_encode(array_map(function($lang) { return $lang['template']; }, $SUPPORTED_LANGUAGES)) ?>;
    
    // Initialize Monaco Editor
    require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
    
    let editor;
    
    require(['vs/editor/editor.main'], function () {
        editor = monaco.editor.create(document.getElementById('monaco-editor'), {
            value: languageTemplates.python || "# Viết code của bạn ở đây",
            language: 'python',
            theme: 'vs-light',
            fontSize: 14,
            lineNumbers: 'on',
            minimap: { enabled: true },
            automaticLayout: true,
            scrollBeyondLastLine: false,
            wordWrap: 'on'
        });
    });
    
    // Tab switching functionality
    document.querySelectorAll('.problem-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Update active tab
            document.querySelectorAll('.problem-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Update active content
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.getElementById(tabName + '-tab').classList.add('active');
        });
    });
    
    // Language selector
    document.getElementById('languageSelect').addEventListener('change', function() {
        const language = this.value;
        const template = languageTemplates[language] || '';
        
        if (editor) {
            monaco.editor.setModelLanguage(editor.getModel(), language);
            editor.setValue(template);
        }
    });
    
    // Run code functionality
    document.getElementById('runCode').addEventListener('click', function() {
        const code = editor.getValue();
        const language = document.getElementById('languageSelect').value;
        
        if (!code.trim()) {
            alert('Vui lòng nhập code trước khi chạy thử!');
            return;
        }
        
        // Show loading
        const consoleOutput = document.getElementById('consoleOutput');
        consoleOutput.innerHTML = '<div class="console-message"><i class="bx bx-loader bx-spin"></i> Đang chạy code...</div>';
        
        // TODO: Implement actual code execution API call
        // For now, simulate execution
        setTimeout(() => {
            consoleOutput.innerHTML = `
                <div class="test-case">
                    <div class="test-case-header">
                        <i class="bx bx-check-circle" style="color: #28a745;"></i>
                        Test Case 1: Passed
                    </div>
                    <div class="test-case-content">
                        <div class="test-result">Input: ${document.querySelector('.sample-code')?.textContent || 'Sample input'}</div>
                        <div class="test-result">Expected: ${document.querySelectorAll('.sample-code')[1]?.textContent || 'Sample output'}</div>
                        <div class="test-result">Actual: ${document.querySelectorAll('.sample-code')[1]?.textContent || 'Sample output'}</div>
                    </div>
                </div>
            `;
        }, 1500);
    });
    
    // Submit code functionality
    document.getElementById('submitCode').addEventListener('click', function() {
        const code = editor.getValue();
        const language = document.getElementById('languageSelect').value;
        
        if (!code.trim()) {
            alert('Vui lòng nhập code trước khi nộp bài!');
            return;
        }
        
        if (confirm('Bạn có chắc chắn muốn nộp bài này?')) {
            // TODO: Implement submission logic here
            console.log('Submitting code:', { code, language });
            
            // Show success message
            const consoleOutput = document.getElementById('consoleOutput');
            consoleOutput.innerHTML = `
                <div class="console-message" style="background: #d4edda; color: #155724;">
                    <i class="bx bx-check-circle"></i>
                    Code đã được nộp thành công! Đang chấm bài...
                </div>
            `;
        }
    });
    
    // Clear console
    document.getElementById('clearConsole').addEventListener('click', function() {
        document.getElementById('consoleOutput').innerHTML = `
            <div class="console-message">
                <i class="bx bx-info-circle"></i>
                Console đã được xóa.
            </div>
        `;
    });
</script>

<?php
// Capture content and include layout
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesNothing.php';
?>
