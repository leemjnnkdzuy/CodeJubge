document.addEventListener("DOMContentLoaded", function () {
    // Initialize Monaco Editor if available
    let editor;
    const codeEditor = document.getElementById("codeEditor");
    const languageSelect = document.getElementById("languageSelect");
    const runBtn = document.getElementById("runCode");
    const submitBtn = document.getElementById("submitCode");
    const consoleOutput = document.getElementById("consoleOutput");
    const clearConsoleBtn = document.getElementById("clearConsole");
    
    // Check if Monaco Editor is available
    if (typeof require !== 'undefined' && document.getElementById('monaco-editor')) {
        require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
        
        require(['vs/editor/editor.main'], function () {
            editor = monaco.editor.create(document.getElementById('monaco-editor'), {
                value: getLanguageTemplate(languageSelect?.value || 'python'),
                language: languageSelect?.value || 'python',
                theme: 'vs-light',
                fontSize: 14,
                lineNumbers: 'on',
                minimap: { enabled: true },
                automaticLayout: true,
                scrollBeyondLastLine: false,
                wordWrap: 'on',
                tabSize: 4,
                insertSpaces: true
            });
        });
    } else if (typeof CodeMirror !== 'undefined' && codeEditor) {
        // Fallback to CodeMirror
        editor = CodeMirror.fromTextArea(codeEditor, {
            lineNumbers: true,
            mode: 'python',
            theme: 'material',
            indentUnit: 4,
            indentWithTabs: false,
            autoCloseBrackets: true,
            matchBrackets: true,
            lineWrapping: true,
            scrollbarStyle: "simple"
        });
        
        editor.setSize(null, 400);
    }
    
    const languageTemplates = {
        python: `# Viết code Python của bạn ở đây
def solution():
    # TODO: Implement your solution
    pass

if __name__ == "__main__":
    solution()`,
        
        cpp: `#include <iostream>
#include <vector>
#include <string>
using namespace std;

int main() {
    // TODO: Implement your solution
    
    return 0;
}`,
        
        java: `import java.util.*;
import java.io.*;

public class Solution {
    public static void main(String[] args) {
        Scanner scanner = new Scanner(System.in);
        
        // TODO: Implement your solution
        
        scanner.close();
    }
}`,
        
        javascript: `// Viết code JavaScript của bạn ở đây
function solution() {
    // TODO: Implement your solution
}

// Đọc input
const readline = require('readline');
const rl = readline.createInterface({
    input: process.stdin,
    output: process.stdout
});

solution();`,
        
        c: `#include <stdio.h>
#include <stdlib.h>
#include <string.h>

int main() {
    // TODO: Implement your solution
    
    return 0;
}`
    };
    
    const languageModes = {
        python: 'python',
        cpp: 'text/x-c++src',
        java: 'text/x-java',
        javascript: 'javascript',
        c: 'text/x-csrc'
    };
    
    function getLanguageTemplate(language) {
        return languageTemplates[language] || languageTemplates.python;
    }
    
    // Tab switching functionality
    document.querySelectorAll('.problem-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Update active tab
            document.querySelectorAll('.problem-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // Update active content
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            const targetTab = document.getElementById(tabName + '-tab');
            if (targetTab) {
                targetTab.classList.add('active');
            }
        });
    });
    
    // Editor tab switching
    document.querySelectorAll('.editor-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            // Update active tab
            document.querySelectorAll('.editor-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            // TODO: Implement editor tab content switching
        });
    });
    
    // Language selector
    if (languageSelect) {
        languageSelect.addEventListener('change', function() {
            const language = this.value;
            const template = getLanguageTemplate(language);
            
            if (editor) {
                if (typeof monaco !== 'undefined') {
                    monaco.editor.setModelLanguage(editor.getModel(), language);
                    editor.setValue(template);
                } else if (editor.setOption) {
                    // CodeMirror
                    editor.setValue(template);
                    editor.setOption('mode', languageModes[language]);
                }
            } else if (codeEditor) {
                codeEditor.value = template;
            }
            
            // Auto-save language preference
            autoSave();
        });
        
        // Trigger initial change
        languageSelect.dispatchEvent(new Event('change'));
    }
    
    function getCurrentCode() {
        if (editor) {
            if (typeof monaco !== 'undefined') {
                return editor.getValue();
            } else if (editor.getValue) {
                // CodeMirror
                return editor.getValue();
            }
        }
        return codeEditor?.value || '';
    }
    
    // Run code functionality
    // Run code functionality
    if (runBtn) {
        runBtn.addEventListener('click', function() {
            const code = getCurrentCode().trim();
            const language = languageSelect?.value || 'python';
            
            if (!code) {
                showConsoleMessage('Vui lòng nhập code trước khi chạy thử.', 'error');
                return;
            }
            
            runBtn.disabled = true;
            runBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Đang chạy...';
            
            runCode(code, language)
                .then(result => {
                    showTestResults(result);
                })
                .catch(error => {
                    showConsoleMessage('Có lỗi xảy ra khi chạy code: ' + error.message, 'error');
                })
                .finally(() => {
                    runBtn.disabled = false;
                    runBtn.innerHTML = '<i class="bx bx-play"></i> Chạy thử';
                });
        });
    }
    
    // Submit code functionality
    if (submitBtn) {
        submitBtn.addEventListener('click', function() {
            const code = getCurrentCode().trim();
            const language = languageSelect?.value || 'python';
            
            if (!code) {
                showConsoleMessage('Vui lòng nhập code trước khi nộp bài.', 'error');
                return;
            }
            
            if (!confirm('Bạn có chắc chắn muốn nộp bài này không?')) {
                return;
            }
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Đang nộp...';
            
            submitCode(code, language)
                .then(result => {
                    showSubmissionResult(result);
                })
                .catch(error => {
                    showConsoleMessage('Có lỗi xảy ra khi nộp bài: ' + error.message, 'error');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bx bx-send"></i> Nộp bài';
                });
        });
    }
    
    // Clear console functionality
    if (clearConsoleBtn) {
        clearConsoleBtn.addEventListener('click', function() {
            if (consoleOutput) {
                consoleOutput.innerHTML = `
                    <div class="console-message">
                        <i class="bx bx-info-circle"></i>
                        Console đã được xóa.
                    </div>
                `;
            }
        });
    }
    
    async function runCode(code, language) {
        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        return {
            success: true,
            output: "Sample output from your code\nTest case 1: ✅ PASSED\nTest case 2: ✅ PASSED",
            execution_time: 45,
            memory_usage: 12.5,
            error: null
        };
    }
    
    async function submitCode(code, language) {
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        const pathParts = window.location.pathname.split('/');
        const problemSlug = pathParts[pathParts.length - 1];
        
        return {
            success: true,
            submission_id: 12345,
            status: 'accepted',
            test_cases_passed: 10,
            total_test_cases: 10,
            execution_time: 42,
            memory_usage: 11.8,
            message: 'Chúc mừng! Bài làm của bạn đã được chấp nhận.'
        };
    }
    
    function showTestResults(result) {
        if (!consoleOutput) return;
        
        if (result.success) {
            consoleOutput.innerHTML = `
                <div class="test-case">
                    <div class="test-case-header">
                        <i class="bx bx-check-circle" style="color: #28a745;"></i>
                        <strong>Chạy thành công</strong>
                    </div>
                    <div class="test-case-content">
                        <div class="test-result">
                            <strong>Thời gian:</strong> ${result.execution_time}ms
                        </div>
                        <div class="test-result">
                            <strong>Bộ nhớ:</strong> ${result.memory_usage}MB
                        </div>
                        <div class="test-result">
                            <strong>Kết quả:</strong>
                            <pre>${result.output}</pre>
                        </div>
                    </div>
                </div>
            `;
        } else {
            consoleOutput.innerHTML = `
                <div class="test-case test-error">
                    <div class="test-case-header">
                        <i class="bx bx-x-circle" style="color: #dc3545;"></i>
                        <strong>Lỗi khi chạy</strong>
                    </div>
                    <div class="test-case-content">
                        <div class="test-result">
                            <pre>${result.error || 'Có lỗi không xác định xảy ra.'}</pre>
                        </div>
                    </div>
                </div>
            `;
        }
    }
    
    function showSubmissionResult(result) {
        let statusClass = 'success';
        let statusIcon = 'bx-check-circle';
        let statusColor = '#28a745';
        
        if (result.status !== 'accepted') {
            statusClass = 'error';
            statusIcon = 'bx-x-circle';
            statusColor = '#dc3545';
        }
        
        const resultHtml = `
            <div class="test-case submission-result ${statusClass}">
                <div class="test-case-header" style="color: ${statusColor}">
                    <i class="bx ${statusIcon}"></i>
                    <strong>${result.message}</strong>
                </div>
                <div class="test-case-content">
                    <div class="test-result">
                        <strong>Test cases:</strong> ${result.test_cases_passed}/${result.total_test_cases}
                    </div>
                    <div class="test-result">
                        <strong>Thời gian:</strong> ${result.execution_time}ms
                    </div>
                    <div class="test-result">
                        <strong>Bộ nhớ:</strong> ${result.memory_usage}MB
                    </div>
                    <div class="test-result">
                        <strong>Submission ID:</strong> #${result.submission_id}
                    </div>
                </div>
            </div>
        `;
        
        if (consoleOutput) {
            consoleOutput.innerHTML = resultHtml;
        }
        
        showNotification(resultHtml);
        
        if (result.success) {
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        }
    }
    
    function showConsoleMessage(message, type = 'info') {
        if (!consoleOutput) return;
        
        const iconMap = {
            info: 'bx-info-circle',
            error: 'bx-error-circle',
            success: 'bx-check-circle',
            warning: 'bx-error'
        };
        
        const colorMap = {
            info: '#007bff',
            error: '#dc3545',
            success: '#28a745',
            warning: '#ffc107'
        };
        
        consoleOutput.innerHTML = `
            <div class="console-message" style="border-left-color: ${colorMap[type]};">
                <i class="bx ${iconMap[type]}" style="color: ${colorMap[type]};"></i>
                ${message}
            </div>
        `;
    }
    
    function showNotification(html) {
        let notificationContainer = document.getElementById('notification-container');
        if (!notificationContainer) {
            notificationContainer = document.createElement('div');
            notificationContainer.id = 'notification-container';
            notificationContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 1000;
                max-width: 400px;
            `;
            document.body.appendChild(notificationContainer);
        }
        
        const notification = document.createElement('div');
        notification.style.cssText = `
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            border-left: 4px solid var(--primary-blue);
            animation: slideIn 0.3s ease;
        `;
        
        notification.innerHTML = html;
        notificationContainer.appendChild(notification);
        
        // Add animation styles if not already added
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                @keyframes slideIn {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            if (runBtn) runBtn.click();
        }
        
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'Enter') {
            e.preventDefault();
            if (submitBtn) submitBtn.click();
        }
    });
    
    // Auto-save functionality
    function autoSave() {
        const code = getCurrentCode();
        const language = languageSelect?.value || 'python';
        const problemSlug = window.location.pathname.split('/').pop();
        
        if (problemSlug && problemSlug !== '') {
            localStorage.setItem(`problem_${problemSlug}_code`, code);
            localStorage.setItem(`problem_${problemSlug}_language`, language);
        }
    }
    
    function loadSavedCode() {
        const problemSlug = window.location.pathname.split('/').pop();
        if (!problemSlug || problemSlug === '') return;
        
        const savedCode = localStorage.getItem(`problem_${problemSlug}_code`);
        const savedLanguage = localStorage.getItem(`problem_${problemSlug}_language`);
        
        if (savedLanguage && languageSelect) {
            languageSelect.value = savedLanguage;
        }
        
        if (savedCode) {
            if (editor) {
                if (typeof monaco !== 'undefined') {
                    editor.setValue(savedCode);
                } else if (editor.setValue) {
                    // CodeMirror
                    editor.setValue(savedCode);
                }
            } else if (codeEditor) {
                codeEditor.value = savedCode;
            }
        }
    }
    
    let saveTimeout;
    function scheduleAutoSave() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(autoSave, 1000);
    }
    
    // Set up auto-save listeners
    if (editor) {
        if (typeof monaco !== 'undefined') {
            // Monaco editor change listener will be set up after editor is created
            setTimeout(() => {
                if (editor && editor.onDidChangeModelContent) {
                    editor.onDidChangeModelContent(scheduleAutoSave);
                }
            }, 1000);
        } else if (editor.on) {
            // CodeMirror
            editor.on('change', scheduleAutoSave);
        }
    } else if (codeEditor) {
        codeEditor.addEventListener('input', scheduleAutoSave);
    }
    
    if (languageSelect) {
        languageSelect.addEventListener('change', autoSave);
    }
    
    // Load saved code after everything is initialized
    setTimeout(loadSavedCode, 500);
    
    // Initialize console with welcome message
    if (consoleOutput) {
        showConsoleMessage('Nhấn "Chạy thử" để kiểm tra code của bạn với test case mẫu.');
    }
});
