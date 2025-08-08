class SubmissionViewer {
    constructor() {
        this.modal = null;
        this.editor = null;
        this.currentSubmission = null;
        this.init();
    }

    init() {
        this.createModal();
        this.attachEventListeners();
        this.loadMonacoEditor();
    }

    createModal() {
        const modalHTML = `
            <div id="submission-modal" class="submission-modal">
                <div class="submission-modal-content">
                    <div class="submission-modal-header">
                        <h2>
                            <i class='bx bx-code-alt'></i>
                            Chi tiết submission
                        </h2>
                        <button class="submission-modal-close" onclick="submissionViewer.closeModal()">
                            <i class='bx bx-x'></i>
                        </button>
                    </div>
                    <div class="submission-modal-body">
                        <div id="submission-loading" class="loading-spinner">
                            <div class="spinner"></div>
                            <p>Đang tải thông tin submission...</p>
                        </div>
                        
                        <div id="submission-content" style="display: none;">
                            <div class="submission-info-grid">
                                <div class="submission-info-item">
                                    <span class="submission-info-label">Bài toán</span>
                                    <span class="submission-info-value" id="submission-problem"></span>
                                </div>
                                <div class="submission-info-item">
                                    <span class="submission-info-label">Ngôn ngữ</span>
                                    <span class="submission-info-value" id="submission-language"></span>
                                </div>
                                <div class="submission-info-item">
                                    <span class="submission-info-label">Trạng thái</span>
                                    <span class="submission-info-value">
                                        <span id="submission-status" class="submission-status"></span>
                                    </span>
                                </div>
                                <div class="submission-info-item">
                                    <span class="submission-info-label">Thời gian chạy</span>
                                    <span class="submission-info-value" id="submission-time"></span>
                                </div>
                                <div class="submission-info-item">
                                    <span class="submission-info-label">Bộ nhớ</span>
                                    <span class="submission-info-value" id="submission-memory"></span>
                                </div>
                                <div class="submission-info-item">
                                    <span class="submission-info-label">Điểm số</span>
                                    <span class="submission-info-value" id="submission-score"></span>
                                </div>
                                <div class="submission-info-item">
                                    <span class="submission-info-label">Thời gian nộp</span>
                                    <span class="submission-info-value" id="submission-date"></span>
                                </div>
                            </div>

                            <div class="submission-code-section">
                                <div class="submission-code-header">
                                    <h3>
                                        <i class='bx bx-code-curly'></i>
                                        Source Code
                                    </h3>
                                    <button class="copy-code-btn" onclick="submissionViewer.copyCode()">
                                        <i class='bx bx-copy'></i>
                                        <span class="copy-text">Copy Code</span>
                                    </button>
                                </div>
                                <div id="monaco-editor-container"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.modal = document.getElementById('submission-modal');
    }

    attachEventListeners() {
        document.addEventListener('click', (e) => {
            const viewBtn = e.target.closest('.btn-view');
            if (viewBtn) {
                e.preventDefault();
                const submissionId = viewBtn.getAttribute('data-submission-id');
                if (submissionId) {
                    this.showSubmission(submissionId);
                }
            }
        });

        this.modal?.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal?.classList.contains('show')) {
                this.closeModal();
            }
        });
    }

    loadMonacoEditor() {
        if (window.monaco) {
            return Promise.resolve();
        }

        return new Promise((resolve, reject) => {
            require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
            require(['vs/editor/editor.main'], () => {
                resolve();
            }, (error) => {
                console.error('Failed to load Monaco Editor:', error);
                reject(error);
            });
        });
    }

    async showSubmission(submissionId) {
        this.modal.classList.add('show');
        
        document.getElementById('submission-loading').style.display = 'flex';
        document.getElementById('submission-content').style.display = 'none';

        try {
            const response = await fetch(`/api/submissions/${submissionId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const submission = await response.json();
            this.currentSubmission = submission;
            
            this.populateSubmissionInfo(submission);
            
            await this.loadMonacoEditor();
            this.setupEditor(submission);
            
            document.getElementById('submission-loading').style.display = 'none';
            document.getElementById('submission-content').style.display = 'block';
            
        } catch (error) {
            console.error('Error loading submission:', error);
            this.showError('Không thể tải thông tin submission. Vui lòng thử lại.');
        }
    }

    populateSubmissionInfo(submission) {
        document.getElementById('submission-problem').textContent = submission.problem_title || 'N/A';
        document.getElementById('submission-language').textContent = this.formatLanguage(submission.language);
        
        const statusElement = document.getElementById('submission-status');
        statusElement.textContent = this.formatStatus(submission.status);
        statusElement.className = `submission-status ${submission.status}`;
        
        document.getElementById('submission-time').textContent = submission.execution_time ? 
            `${submission.execution_time}ms` : 'N/A';
        document.getElementById('submission-memory').textContent = submission.memory_used ? 
            `${submission.memory_used}KB` : 'N/A';
        document.getElementById('submission-score').textContent = submission.score || '0';
        document.getElementById('submission-date').textContent = this.formatDate(submission.submitted_at);
    }

    setupEditor(submission) {
        const container = document.getElementById('monaco-editor-container');
        
        if (this.editor) {
            this.editor.dispose();
        }

        const language = this.getMonacoLanguage(submission.language);
        
        this.editor = monaco.editor.create(container, {
            value: submission.code || '',
            language: language,
            theme: 'vs-light',
            readOnly: true,
            automaticLayout: true,
            minimap: {
                enabled: true
            },
            fontSize: 14,
            lineNumbers: 'on',
            roundedSelection: false,
            scrollBeyondLastLine: false,
            wordWrap: 'on',
            folding: true
        });
    }

    getMonacoLanguage(language) {
        const languageMap = {
            'cpp': 'cpp',
            'c': 'c',
            'python': 'python',
            'java': 'java',
            'javascript': 'javascript',
            'js': 'javascript',
            'typescript': 'typescript',
            'ts': 'typescript',
            'php': 'php',
            'go': 'go',
            'rust': 'rust',
            'csharp': 'csharp',
            'c#': 'csharp'
        };
        
        return languageMap[language?.toLowerCase()] || 'plaintext';
    }

    formatLanguage(language) {
        const languageNames = {
            'cpp': 'C++',
            'c': 'C',
            'python': 'Python',
            'java': 'Java',
            'javascript': 'JavaScript',
            'js': 'JavaScript',
            'typescript': 'TypeScript',
            'ts': 'TypeScript',
            'php': 'PHP',
            'go': 'Go',
            'rust': 'Rust',
            'csharp': 'C#',
            'c#': 'C#'
        };
        
        return languageNames[language?.toLowerCase()] || language;
    }

    formatStatus(status) {
        const statusNames = {
            'accepted': 'Accepted',
            'wrong_answer': 'Wrong Answer',
            'time_limit': 'Time Limit Exceeded',
            'memory_limit': 'Memory Limit Exceeded',
            'runtime_error': 'Runtime Error',
            'compile_error': 'Compile Error',
            'pending': 'Pending'
        };
        
        return statusNames[status] || status;
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    }

    async copyCode() {
        if (!this.currentSubmission?.code) {
            return;
        }

        try {
            await navigator.clipboard.writeText(this.currentSubmission.code);
            
            const copyBtn = document.querySelector('.copy-code-btn');
            const copyText = copyBtn.querySelector('.copy-text');
            const copyIcon = copyBtn.querySelector('i');
            
            copyBtn.classList.add('copied');
            copyText.textContent = 'Copied!';
            copyIcon.className = 'bx bx-check';
            
            setTimeout(() => {
                copyBtn.classList.remove('copied');
                copyText.textContent = 'Copy Code';
                copyIcon.className = 'bx bx-copy';
            }, 2000);
        } catch (error) {
            console.error('Failed to copy code:', error);
        }
    }

    showError(message) {
        document.getElementById('submission-loading').innerHTML = `
            <i class='bx bx-error' style="font-size: 2rem; color: #e74c3c;"></i>
            <p style="color: #e74c3c;">${message}</p>
        `;
    }

    closeModal() {
        this.modal.classList.remove('show');
        
        // Clean up editor
        if (this.editor) {
            this.editor.dispose();
            this.editor = null;
        }
        
        this.currentSubmission = null;
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.submissionViewer = new SubmissionViewer();
});

// Fallback for immediate execution if DOM already loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.submissionViewer) {
            window.submissionViewer = new SubmissionViewer();
        }
    });
} else {
    window.submissionViewer = new SubmissionViewer();
}