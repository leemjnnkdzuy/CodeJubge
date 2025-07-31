document.addEventListener("DOMContentLoaded", function () {
    const codeEditor = document.getElementById("codeEditor");
    const languageSelect = document.getElementById("languageSelect");
    const runBtn = document.getElementById("runCode");
    const submitBtn = document.getElementById("submitCode");
    const testResults = document.getElementById("testResults");
    const resultsContent = document.getElementById("resultsContent");
    
    let editor;
    
    if (typeof CodeMirror !== 'undefined') {
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
    
    languageSelect.addEventListener('change', function() {
        const selectedLanguage = this.value;
        const template = languageTemplates[selectedLanguage];
        
        if (editor) {
            editor.setValue(template);
            editor.setOption('mode', languageModes[selectedLanguage]);
        } else {
            codeEditor.value = template;
        }
    });
    
    languageSelect.dispatchEvent(new Event('change'));
    
    function getCurrentCode() {
        return editor ? editor.getValue() : codeEditor.value;
    }
    
    runBtn.addEventListener('click', function() {
        const code = getCurrentCode().trim();
        const language = languageSelect.value;
        
        if (!code) {
            showError('Vui lòng nhập code trước khi chạy thử.');
            return;
        }
        
        runBtn.disabled = true;
        runBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Đang chạy...';
        
        runCode(code, language)
            .then(result => {
                showTestResults(result);
            })
            .catch(error => {
                showError('Có lỗi xảy ra khi chạy code: ' + error.message);
            })
            .finally(() => {
                runBtn.disabled = false;
                runBtn.innerHTML = '<i class="bx bx-play"></i> Chạy thử';
            });
    });
    
    submitBtn.addEventListener('click', function() {
        const code = getCurrentCode().trim();
        const language = languageSelect.value;
        
        if (!code) {
            showError('Vui lòng nhập code trước khi nộp bài.');
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
                showError('Có lỗi xảy ra khi nộp bài: ' + error.message);
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bx bx-send"></i> Nộp bài';
            });
    });
    
    async function runCode(code, language) {
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
        if (result.success) {
            resultsContent.innerHTML = `
                <div class="test-success">
                    <div class="result-header">
                        <i class="bx bx-check-circle"></i>
                        <strong>Chạy thành công</strong>
                    </div>
                    <div class="result-stats">
                        <span><i class="bx bx-time"></i> Thời gian: ${result.execution_time}ms</span>
                        <span><i class="bx bx-memory-card"></i> Bộ nhớ: ${result.memory_usage}MB</span>
                    </div>
                    <div class="result-output">
                        <h4>Kết quả:</h4>
                        <pre>${result.output}</pre>
                    </div>
                </div>
            `;
        } else {
            resultsContent.innerHTML = `
                <div class="test-error">
                    <div class="result-header">
                        <i class="bx bx-x-circle"></i>
                        <strong>Lỗi khi chạy</strong>
                    </div>
                    <div class="result-output">
                        <pre>${result.error || 'Có lỗi không xác định xảy ra.'}</pre>
                    </div>
                </div>
            `;
        }
        
        testResults.style.display = 'block';
        testResults.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
            <div class="submission-result ${statusClass}">
                <div class="result-header" style="color: ${statusColor}">
                    <i class="bx ${statusIcon}"></i>
                    <strong>${result.message}</strong>
                </div>
                <div class="result-details">
                    <div class="result-stats">
                        <span>Test cases: ${result.test_cases_passed}/${result.total_test_cases}</span>
                        <span><i class="bx bx-time"></i> ${result.execution_time}ms</span>
                        <span><i class="bx bx-memory-card"></i> ${result.memory_usage}MB</span>
                    </div>
                    <div class="submission-id">
                        Submission ID: #${result.submission_id}
                    </div>
                </div>
            </div>
        `;
        
        showNotification(resultHtml);
        
        if (result.success) {
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        }
    }
    
    function showError(message) {
        resultsContent.innerHTML = `
            <div class="test-error">
                <div class="result-header">
                    <i class="bx bx-error-circle"></i>
                    <strong>Lỗi</strong>
                </div>
                <div class="result-output">
                    <p>${message}</p>
                </div>
            </div>
        `;
        
        testResults.style.display = 'block';
        testResults.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
            border-left: 4px solid #007bff;
            animation: slideIn 0.3s ease;
        `;
        
        notification.innerHTML = html;
        notificationContainer.appendChild(notification);
        
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
        
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            runBtn.click();
        }
        
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'Enter') {
            e.preventDefault();
            submitBtn.click();
        }
    });
    
    function autoSave() {
        const code = getCurrentCode();
        const language = languageSelect.value;
        const problemSlug = window.location.pathname.split('/').pop();
        
        localStorage.setItem(`problem_${problemSlug}_code`, code);
        localStorage.setItem(`problem_${problemSlug}_language`, language);
    }
    
    function loadSavedCode() {
        const problemSlug = window.location.pathname.split('/').pop();
        const savedCode = localStorage.getItem(`problem_${problemSlug}_code`);
        const savedLanguage = localStorage.getItem(`problem_${problemSlug}_language`);
        
        if (savedLanguage) {
            languageSelect.value = savedLanguage;
        }
        
        if (savedCode) {
            if (editor) {
                editor.setValue(savedCode);
            } else {
                codeEditor.value = savedCode;
            }
        }
    }
    
    let saveTimeout;
    function scheduleAutoSave() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(autoSave, 1000);
    }
    
    if (editor) {
        editor.on('change', scheduleAutoSave);
    } else {
        codeEditor.addEventListener('input', scheduleAutoSave);
    }
    
    languageSelect.addEventListener('change', autoSave);
    
    loadSavedCode();
});
