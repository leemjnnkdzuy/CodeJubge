class ProblemManager {
    constructor() {
        this.init();
    }

    init() {
        this.bindEventListeners();
        this.initializeComponents();
    }

    bindEventListeners() {
        const addBtn = document.querySelector('.create-problem-btn');
        if (addBtn) {
            addBtn.addEventListener('click', () => {
                window.location.href = '/admin/problems/create';
            });
        }

        document.querySelectorAll('.btn-edit').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                const problemId = row.cells[0].textContent;
                window.location.href = `/admin/problems/${problemId}/edit`;
            });
        });

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                const problemId = row.cells[0].textContent;
                const problemTitle = row.cells[1].textContent.trim();
                this.deleteProblem(problemId, problemTitle);
            });
        });

        document.querySelectorAll('.btn-view').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const row = e.target.closest('tr');
                const slug = row.querySelector('.problem-slug').textContent;
                window.open(`/problems/${slug}`, '_blank');
            });
        });

        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.addEventListener('input', this.debounce((e) => {
                this.filterProblems();
            }, 300));
        }

        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', () => {
                this.filterProblems();
            });
        });
    }

    initializeComponents() {
        console.log('Problem Manager initialized');
    }

    async deleteProblem(problemId, problemTitle) {
        const modal = this.createConfirmModal(
            'Xác nhận xóa', 
            `Bạn có chắc chắn muốn xóa problem "${problemTitle}"?`,
            'Xóa',
            'Hủy'
        );
        
        const confirmed = await this.showModal(modal);
        if (!confirmed) return;

        try {
            const response = await fetch(`/admin/problems/${problemId}/delete`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
            });

            const result = await response.json();

            if (result.success && result.redirect) {
                window.location.href = result.redirect;
            } else if (result.success) {
                window.location.reload();
            }
        } catch (error) {
            console.error('Error deleting problem:', error);
            window.location.reload();
        }
    }

    filterProblems() {
        const searchTerm = document.querySelector('.search-input').value.toLowerCase();
        const difficultyFilter = document.querySelector('.filter-select').value;
        const statusFilter = document.querySelectorAll('.filter-select')[1].value;

        const rows = document.querySelectorAll('.admin-table tbody tr');

        rows.forEach(row => {
            if (row.querySelector('.no-data')) return;

            const title = row.cells[1].textContent.toLowerCase();
            const difficulty = row.cells[3].textContent.toLowerCase();
            const status = row.cells[7].textContent.toLowerCase();

            let shouldShow = true;

            if (searchTerm && !title.includes(searchTerm)) {
                shouldShow = false;
            }

            if (difficultyFilter && difficulty !== difficultyFilter) {
                shouldShow = false;
            }

            if (statusFilter) {
                const isActive = status.includes('hoạt động');
                if (statusFilter === 'active' && !isActive) {
                    shouldShow = false;
                }
                if (statusFilter === 'inactive' && isActive) {
                    shouldShow = false;
                }
            }

            row.style.display = shouldShow ? '' : 'none';
        });
    }

    createConfirmModal(title, message, confirmText, cancelText) {
        const modal = document.createElement('div');
        modal.className = 'custom-modal-overlay';
        modal.innerHTML = `
            <div class="custom-modal">
                <div class="modal-header">
                    <h3>${title}</h3>
                </div>
                <div class="modal-body">
                    <p>${message}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn-cancel">${cancelText}</button>
                    <button class="btn-confirm">${confirmText}</button>
                </div>
            </div>
        `;

        const style = document.createElement('style');
        style.textContent = `
            .custom-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 10000;
            }
            .custom-modal {
                background: white;
                border-radius: 8px;
                padding: 24px;
                min-width: 400px;
                max-width: 90vw;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            }
            .modal-header h3 {
                margin: 0 0 16px 0;
                color: #333;
            }
            .modal-body p {
                margin: 0 0 24px 0;
                color: #666;
                line-height: 1.5;
            }
            .modal-footer {
                display: flex;
                gap: 12px;
                justify-content: flex-end;
            }
            .btn-cancel, .btn-confirm {
                padding: 8px 16px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                font-size: 14px;
            }
            .btn-cancel {
                background: #f5f5f5;
                color: #333;
            }
            .btn-cancel:hover {
                background: #e5e5e5;
            }
            .btn-confirm {
                background: #dc3545;
                color: white;
            }
            .btn-confirm:hover {
                background: #c82333;
            }
        `;
        document.head.appendChild(style);

        return modal;
    }

    showModal(modal) {
        return new Promise((resolve) => {
            document.body.appendChild(modal);
            
            const confirmBtn = modal.querySelector('.btn-confirm');
            const cancelBtn = modal.querySelector('.btn-cancel');
            
            const cleanup = () => {
                document.body.removeChild(modal);
            };
            
            confirmBtn.addEventListener('click', () => {
                cleanup();
                resolve(true);
            });
            
            cancelBtn.addEventListener('click', () => {
                cleanup();
                resolve(false);
            });
            
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    cleanup();
                    resolve(false);
                }
            });
        });
    }

    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
}

class ProblemFormManager {
    constructor() {
        this.testCases = [];
        this.examples = [];
        this.init();
    }

    init() {
        if (document.getElementById('problem-form')) {
            this.bindFormEvents();
            this.loadExistingData();
        }
    }

    bindFormEvents() {
        const addExampleBtn = document.getElementById('add-example');
        if (addExampleBtn) {
            addExampleBtn.addEventListener('click', () => this.addExample());
        }

        const addTestCaseBtn = document.getElementById('add-test-case');
        if (addTestCaseBtn) {
            addTestCaseBtn.addEventListener('click', () => this.addTestCase());
        }

        const form = document.getElementById('problem-form');
        if (form) {
            form.addEventListener('submit', (e) => this.handleFormSubmit(e));
        }
    }

    loadExistingData() {
        const problemData = window.problemData;
        if (problemData) {
            this.examples = problemData.examples || [];
            this.testCases = problemData.test_cases || [];
            this.renderExamples();
            this.renderTestCases();
        }
    }

    addExample() {
        this.examples.push({ input: '', output: '', explanation: '' });
        this.renderExamples();
    }

    addTestCase() {
        this.testCases.push({ input: '', expected_output: '', is_sample: false });
        this.renderTestCases();
    }

    renderExamples() {
        const container = document.getElementById('examples-container');
        if (!container) return;

        container.innerHTML = this.examples.map((example, index) => `
            <div class="example-item" data-index="${index}">
                <div class="example-header">
                    <h4>Ví dụ ${index + 1}</h4>
                    <button type="button" class="btn-remove" onclick="problemForm.removeExample(${index})">
                        <i class="bx bx-trash"></i>
                    </button>
                </div>
                <div class="example-content">
                    <div class="form-group">
                        <label>Input:</label>
                        <textarea class="form-textarea" name="example_input_${index}" 
                                  onchange="problemForm.updateExample(${index}, 'input', this.value)">${example.input}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Output:</label>
                        <textarea class="form-textarea" name="example_output_${index}"
                                  onchange="problemForm.updateExample(${index}, 'output', this.value)">${example.output}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Giải thích (tuỳ chọn):</label>
                        <textarea class="form-textarea" name="example_explanation_${index}"
                                  onchange="problemForm.updateExample(${index}, 'explanation', this.value)">${example.explanation || ''}</textarea>
                    </div>
                </div>
            </div>
        `).join('');
    }

    renderTestCases() {
        const container = document.getElementById('test-cases-container');
        if (!container) return;

        container.innerHTML = this.testCases.map((testCase, index) => `
            <div class="test-case-item" data-index="${index}">
                <div class="test-case-header">
                    <h4>Test Case ${index + 1}</h4>
                    <div class="test-case-options">
                        <label>
                            <input type="checkbox" ${testCase.is_sample ? 'checked' : ''}
                                   onchange="problemForm.updateTestCase(${index}, 'is_sample', this.checked)">
                            Sample Test Case
                        </label>
                        <button type="button" class="btn-remove" onclick="problemForm.removeTestCase(${index})">
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="test-case-content">
                    <div class="form-group">
                        <label>Input:</label>
                        <textarea class="form-textarea" name="test_input_${index}"
                                  onchange="problemForm.updateTestCase(${index}, 'input', this.value)">${testCase.input}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Expected Output:</label>
                        <textarea class="form-textarea" name="test_output_${index}"
                                  onchange="problemForm.updateTestCase(${index}, 'expected_output', this.value)">${testCase.expected_output}</textarea>
                    </div>
                </div>
            </div>
        `).join('');
    }

    updateExample(index, field, value) {
        if (this.examples[index]) {
            this.examples[index][field] = value;
        }
    }

    updateTestCase(index, field, value) {
        if (this.testCases[index]) {
            this.testCases[index][field] = value;
        }
    }

    removeExample(index) {
        this.examples.splice(index, 1);
        this.renderExamples();
    }

    removeTestCase(index) {
        this.testCases.splice(index, 1);
        this.renderTestCases();
    }

    async handleFormSubmit(e) {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        
        formData.append('examples', JSON.stringify(this.examples));
        formData.append('test_cases', JSON.stringify(this.testCases));

        try {
            const isEdit = formData.get('problem_id');
            const url = isEdit ? `/admin/problems/${formData.get('problem_id')}/update` : '/admin/problems/store';
            
            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success && result.redirect) {
                window.location.href = result.redirect;
            } else if (result.success) {
                window.location.href = '/admin/problems';
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            window.location.href = '/admin/problems';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (window.location.pathname.includes('/admin/problems')) {
        if (window.location.pathname.includes('/create') || window.location.pathname.includes('/edit')) {
            window.problemForm = new ProblemFormManager();
        } else {
            new ProblemManager();
        }
    }
});