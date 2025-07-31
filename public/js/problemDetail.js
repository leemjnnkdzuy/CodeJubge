document.addEventListener("DOMContentLoaded", function () {
    const codeEditor = document.getElementById("codeEditor");
    const languageSelect = document.getElementById("languageSelect");
    const runBtn = document.getElementById("runCode");
    const submitBtn = document.getElementById("submitCode");
    const testResults = document.getElementById("testResults");
    const resultsContent = document.getElementById("resultsContent");
    
    let editor;
    
    // Initialize CodeMirror if available, otherwise use textarea
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
        
        // Set initial size
        editor.setSize(null, 400);
    }
    
    // Language templates
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
    
    // Language mode mapping for CodeMirror
    const languageModes = {
        python: 'python',
        cpp: 'text/x-c++src',
        java: 'text/x-java',
        javascript: 'javascript',
        c: 'text/x-csrc'
    };
    
    // Change language handler
    languageSelect.addEventListener('change', function() {
        const selectedLanguage = this.value;
        const template = languageTemplates[selectedLanguage];
        
        if (editor) {
            // Update CodeMirror
            editor.setValue(template);
            editor.setOption('mode', languageModes[selectedLanguage]);
        } else {
            // Update textarea
            codeEditor.value = template;
        }
    });
    
    // Set initial template
    languageSelect.dispatchEvent(new Event('change'));
    
    // Get current code
    function getCurrentCode() {
        return editor ? editor.getValue() : codeEditor.value;
    }
    
    // Run code handler
    runBtn.addEventListener('click', function() {
        const code = getCurrentCode().trim();
        const language = languageSelect.value;
        
        if (!code) {
            showError('Vui lòng nhập code trước khi chạy thử.');
            return;
        }
        
        // Show loading state
        runBtn.disabled = true;
        runBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Đang chạy...';
        
        // Simulate API call to run code
        runCode(code, language)
            .then(result => {
                showTestResults(result);
            })
            .catch(error => {
                showError('Có lỗi xảy ra khi chạy code: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                runBtn.disabled = false;
                runBtn.innerHTML = '<i class="bx bx-play"></i> Chạy thử';
            });
    });
    
    // Submit code handler
    submitBtn.addEventListener('click', function() {
        const code = getCurrentCode().trim();
        const language = languageSelect.value;
        
        if (!code) {
            showError('Vui lòng nhập code trước khi nộp bài.');
            return;
        }
        
        // Confirm submission
        if (!confirm('Bạn có chắc chắn muốn nộp bài này không?')) {
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Đang nộp...';
        
        // Simulate API call to submit code
        submitCode(code, language)
            .then(result => {
                showSubmissionResult(result);
            })
            .catch(error => {
                showError('Có lỗi xảy ra khi nộp bài: ' + error.message);
            })
            .finally(() => {
                // Reset button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bx bx-send"></i> Nộp bài';
            });
    });
    
    // Mock API functions (replace with actual API calls)
    async function runCode(code, language) {
        // Simulate API delay
        await new Promise(resolve => setTimeout(resolve, 1500));
        
        // Mock response
        return {
            success: true,
            output: "Sample output from your code\nTest case 1: ✅ PASSED\nTest case 2: ✅ PASSED",
            execution_time: 45,
            memory_usage: 12.5,
            error: null
        };
    }
    
    async function submitCode(code, language) {
        // Simulate API delay
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Get problem ID from URL
        const pathParts = window.location.pathname.split('/');
        const problemSlug = pathParts[pathParts.length - 1];
        
        // Mock response
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
    
    // Show test results
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
    
    // Show submission result
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
        
        // Create modal or notification
        showNotification(resultHtml);
        
        // Optionally reload page to show new submission in history
        if (result.success) {
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        }
    }
    
    // Show error message
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
    
    // Show notification (you can customize this)
    function showNotification(html) {
        // Create notification container if it doesn't exist
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
        
        // Create notification element
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
        
        // Add CSS animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            notification.remove();
        }, 5000);
    }
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to run code
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            runBtn.click();
        }
        
        // Ctrl/Cmd + Shift + Enter to submit code
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'Enter') {
            e.preventDefault();
            submitBtn.click();
        }
    });
    
    // Auto-save code to localStorage
    function autoSave() {
        const code = getCurrentCode();
        const language = languageSelect.value;
        const problemSlug = window.location.pathname.split('/').pop();
        
        localStorage.setItem(`problem_${problemSlug}_code`, code);
        localStorage.setItem(`problem_${problemSlug}_language`, language);
    }
    
    // Load saved code
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
    
    // Set up auto-save
    let saveTimeout;
    function scheduleAutoSave() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(autoSave, 1000); // Save after 1 second of inactivity
    }
    
    if (editor) {
        editor.on('change', scheduleAutoSave);
    } else {
        codeEditor.addEventListener('input', scheduleAutoSave);
    }
    
    languageSelect.addEventListener('change', autoSave);
    
    // Load saved code on page load
    loadSavedCode();
});
