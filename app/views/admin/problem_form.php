<?php
$isEdit = isset($problem);
$problemId = $isEdit ? $problem['id'] : null;
?>

<div class="admin-page-header">
    <div class="admin-page-title">
        <h2><?= $isEdit ? 'Sửa Problem' : 'Tạo Problem Mới' ?></h2>
        <p><?= $isEdit ? 'Chỉnh sửa thông tin problem' : 'Tạo một problem mới cho hệ thống' ?></p>
    </div>
    <div class="admin-page-actions">
        <a href="/admin/problems" class="btn btn-secondary">
            <i class='bx bx-arrow-back'></i>
            Quay lại
        </a>
    </div>
</div>

<div class="problem-form-container">
    <form id="problem-form" class="problem-form">
        <?php if ($isEdit): ?>
            <input type="hidden" name="problem_id" value="<?= $problemId ?>">
        <?php endif; ?>
        
        <div class="form-section">
            <h3>Thông tin cơ bản</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="title">Tiêu đề *</label>
                    <input type="text" id="title" name="title" class="form-input" required 
                           value="<?= $isEdit ? htmlspecialchars($problem['title']) : '' ?>">
                </div>
                <div class="form-group">
                    <label for="difficulty">Độ khó *</label>
                    <select id="difficulty" name="difficulty" class="form-select" required>
                        <option value="">Chọn độ khó</option>
                        <option value="easy" <?= ($isEdit && $problem['difficulty'] === 'easy') ? 'selected' : '' ?>>Easy</option>
                        <option value="medium" <?= ($isEdit && $problem['difficulty'] === 'medium') ? 'selected' : '' ?>>Medium</option>
                        <option value="hard" <?= ($isEdit && $problem['difficulty'] === 'hard') ? 'selected' : '' ?>>Hard</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="category">Danh mục</label>
                    <input type="text" id="category" name="category" class="form-input" 
                           value="<?= $isEdit ? htmlspecialchars($problem['category'] ?? '') : '' ?>"
                           placeholder="VD: Array, String, Graph...">
                </div>
            </div>
        </div>

        <!-- Description -->
        <div class="form-section">
            <h3>Mô tả bài toán</h3>
            <div class="form-group">
                <label for="description">Mô tả *</label>
                <textarea id="description" name="description" class="form-textarea" rows="10" required
                          placeholder="Mô tả chi tiết về bài toán..."><?= $isEdit ? htmlspecialchars($problem['description']) : '' ?></textarea>
            </div>
        </div>

        <!-- Input/Output Format -->
        <div class="form-section">
            <h3>Định dạng Input/Output</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="input_format">Định dạng Input</label>
                    <textarea id="input_format" name="input_format" class="form-textarea" rows="4"
                              placeholder="Mô tả định dạng input..."><?= $isEdit ? htmlspecialchars($problem['input_format'] ?? '') : '' ?></textarea>
                </div>
                <div class="form-group">
                    <label for="output_format">Định dạng Output</label>
                    <textarea id="output_format" name="output_format" class="form-textarea" rows="4"
                              placeholder="Mô tả định dạng output..."><?= $isEdit ? htmlspecialchars($problem['output_format'] ?? '') : '' ?></textarea>
                </div>
            </div>
        </div>

        <!-- Constraints -->
        <div class="form-section">
            <h3>Ràng buộc và giới hạn</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="constraints">Ràng buộc</label>
                    <textarea id="constraints" name="constraints" class="form-textarea" rows="4"
                              placeholder="Mô tả các ràng buộc của bài toán..."><?= $isEdit ? htmlspecialchars($problem['constraints'] ?? '') : '' ?></textarea>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="time_limit">Giới hạn thời gian (ms)</label>
                    <input type="number" id="time_limit" name="time_limit" class="form-input" 
                           value="<?= $isEdit ? $problem['time_limit'] : '1000' ?>" min="100" max="10000">
                </div>
                <div class="form-group">
                    <label for="memory_limit">Giới hạn bộ nhớ (MB)</label>
                    <input type="number" id="memory_limit" name="memory_limit" class="form-input" 
                           value="<?= $isEdit ? $problem['memory_limit'] : '128' ?>" min="64" max="512">
                </div>
            </div>
        </div>

        <!-- Examples -->
        <div class="form-section">
            <h3>Ví dụ</h3>
            <div class="section-header">
                <button type="button" id="add-example" class="btn btn-primary btn-sm">
                    <i class='bx bx-plus'></i>
                    Thêm ví dụ
                </button>
            </div>
            <div id="examples-container"></div>
        </div>

        <!-- Test Cases -->
        <div class="form-section">
            <h3>Test Cases</h3>
            <div class="section-header">
                <button type="button" id="add-test-case" class="btn btn-primary btn-sm">
                    <i class='bx bx-plus'></i>
                    Thêm test case
                </button>
            </div>
            <div id="test-cases-container"></div>
        </div>

        <!-- Editorial and Hints -->
        <div class="form-section">
            <h3>Editorial và gợi ý</h3>
            <div class="form-group">
                <label for="editorial">Editorial</label>
                <textarea id="editorial" name="editorial" class="form-textarea" rows="6"
                          placeholder="Hướng dẫn giải chi tiết..."><?= $isEdit ? htmlspecialchars($problem['editorial'] ?? '') : '' ?></textarea>
            </div>
        </div>

        <!-- Tags -->
        <div class="form-section">
            <h3>Tags và phân loại</h3>
            <div class="form-group">
                <label for="tags">Tags</label>
                <input type="text" id="tags" name="tags" class="form-input" 
                       value="<?= $isEdit ? implode(', ', $problem['tags'] ?? []) : '' ?>"
                       placeholder="Nhập tags, cách nhau bằng dấu phẩy">
                <small>VD: array, sorting, binary-search</small>
            </div>
        </div>

        <!-- Status -->
        <div class="form-section">
            <h3>Trạng thái</h3>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="is_active" value="1" 
                           <?= ($isEdit && $problem['is_active']) ? 'checked' : 'checked' ?>>
                    Problem hoạt động
                </label>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" onclick="window.history.back()" class="btn btn-secondary">Hủy</button>
            <button type="submit" class="btn btn-primary">
                <?= $isEdit ? 'Cập nhật Problem' : 'Tạo Problem' ?>
            </button>
        </div>
    </form>
</div>

<script>
<?php if ($isEdit): ?>
    // Pass existing data to JavaScript
    window.problemData = <?= json_encode([
        'examples' => $problem['examples'] ?? [],
        'test_cases' => $problem['test_cases'] ?? []
    ]) ?>;
<?php endif; ?>
</script>

<style>
.problem-form-container {
    max-width: 1200px;
    margin: 0 auto;
}

.form-section {
    margin-bottom: var(--spacing-lg);
}

.form-section h3 {
    margin: 0 0 var(--spacing-md) 0;
    color: var(--text-primary);
    font-size: 1.1rem;
    font-weight: 600;
}

.section-header {
    margin-bottom: var(--spacing-md);
}

.btn-sm {
    padding: var(--spacing-sm) var(--spacing-md);
    font-size: 0.875rem;
}

.example-item,
.test-case-item {
    border: 1px solid var(--border-color);
    border-radius: var(--radius-sm);
    margin-bottom: var(--spacing-md);
    overflow: hidden;
}

.example-header,
.test-case-header {
    background: var(--light-gray);
    padding: var(--spacing-sm) var(--spacing-md);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.example-header h4,
.test-case-header h4 {
    margin: 0;
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--text-primary);
}

.test-case-options {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.test-case-options label {
    display: flex;
    align-items: center;
    gap: var(--spacing-xs);
    font-size: 0.875rem;
}

.btn-remove {
    background: #dc3545;
    color: var(--white);
    border: none;
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    cursor: pointer;
    font-size: 0.75rem;
    transition: background 0.3s ease;
}

.btn-remove:hover {
    background: #c82333;
}

.example-content,
.test-case-content {
    padding: var(--spacing-md);
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .test-case-options {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--spacing-sm);
    }
}
</style>

<script src="/js/adminProblems.js"></script>
