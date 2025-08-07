let editor;
let languageTemplates = {};
let problemId;

document.addEventListener("DOMContentLoaded", function () {
    problemId = window.problemId || document.querySelector('[data-problem-id]')?.dataset.problemId || null;
    
    if (window.languageTemplatesFromPHP) {
        languageTemplates = window.languageTemplatesFromPHP;
    } else {
        languageTemplates = {
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
    }
    
    // Initialize Monaco Editor
    if (typeof require !== 'undefined' && document.getElementById('monaco-editor')) {
        require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.44.0/min/vs' }});
        
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
            
            // Set up auto-save listener for Monaco
            editor.onDidChangeModelContent(() => {
                scheduleAutoSave();
            });
        });
    }
    
    // Tab switching functionality
    document.querySelectorAll('.problem-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            
            document.querySelectorAll('.problem-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            const targetTab = document.getElementById(tabName + '-tab');
            if (targetTab) {
                targetTab.classList.add('active');
            }
        });
    });
    
    // Language selector functionality
    const languageSelect = document.getElementById('languageSelect');
    if (languageSelect) {
        languageSelect.addEventListener('change', function() {
            const language = this.value;
            const template = languageTemplates[language] || '';
            
            if (editor && typeof monaco !== 'undefined') {
                monaco.editor.setModelLanguage(editor.getModel(), language);
                editor.setValue(template);
            }
            
            autoSave();
        });
    }
    
    // Run code functionality
    document.getElementById('runCode').addEventListener('click', async function() {
        const code = editor ? editor.getValue() : '';
        const language = languageSelect ? languageSelect.value : 'python';
        
        if (!code.trim()) {
            alert('Vui lòng nhập code trước khi chạy thử!');
            return;
        }
        
        const consoleOutput = document.getElementById('consoleOutput');
        consoleOutput.innerHTML = '<div class="console-message"><i class="bx bx-loader bx-spin"></i> Đang chạy code...</div>';
        
        try {
            const sampleInput = document.querySelector('.sample-code')?.textContent || '';
            
            const response = await fetch('/api/run-code', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    code: code,
                    language: language,
                    input: sampleInput
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                const expectedOutput = document.querySelectorAll('.sample-code')[1]?.textContent.trim() || '';
                const actualOutput = result.output.trim();
                const isPassed = expectedOutput === actualOutput;
                
                consoleOutput.innerHTML = `
                    <div class="test-case">
                        <div class="test-case-header">
                            <i class="bx ${isPassed ? 'bx-check-circle' : 'bx-x-circle'}" style="color: ${isPassed ? '#28a745' : '#dc3545'};"></i>
                            Test Case 1: ${isPassed ? 'Passed' : 'Failed'}
                        </div>
                        <div class="test-case-content">
                            <div class="test-result"><strong>Input:</strong> ${sampleInput}</div>
                            <div class="test-result"><strong>Expected:</strong> ${expectedOutput}</div>
                            <div class="test-result"><strong>Actual:</strong> ${actualOutput}</div>
                            <div class="test-result"><strong>Runtime:</strong> ${result.execution_time}ms</div>
                            <div class="test-result"><strong>Memory:</strong> ${result.memory_usage}MB</div>
                        </div>
                    </div>
                `;
            } else {
                consoleOutput.innerHTML = `
                    <div class="console-message" style="background: #f8d7da; color: #721c24;">
                        <i class="bx bx-error"></i>
                        <strong>Lỗi:</strong> ${result.error}
                    </div>
                `;
            }
        } catch (error) {
            consoleOutput.innerHTML = `
                <div class="console-message" style="background: #f8d7da; color: #721c24;">
                    <i class="bx bx-error"></i>
                    <strong>Lỗi kết nối:</strong> ${error.message}
                </div>
            `;
        }
    });
    
    // Submit code functionality - show popup
    document.getElementById('submitCode').addEventListener('click', function() {
        const code = editor ? editor.getValue() : '';
        const language = languageSelect ? languageSelect.value : 'python';
        
        if (!code.trim()) {
            alert('Vui lòng nhập code trước khi nộp bài!');
            return;
        }
        
        // Update popup content
        const popupLanguageEl = document.getElementById('popupLanguage');
        const popupLineCountEl = document.getElementById('popupLineCount');
        
        if (popupLanguageEl && languageSelect) {
            popupLanguageEl.textContent = languageSelect.selectedOptions[0].text;
        }
        if (popupLineCountEl && editor) {
            popupLineCountEl.textContent = editor.getModel().getLineCount();
        }
        
        // Show popup
        const submitPopup = document.getElementById('submitPopup');
        if (submitPopup) {
            submitPopup.classList.add('show');
        }
    });
    
    // Handle popup close button
    const closeSubmitPopup = document.getElementById('closeSubmitPopup');
    if (closeSubmitPopup) {
        closeSubmitPopup.addEventListener('click', function() {
            document.getElementById('submitPopup').classList.remove('show');
        });
    }
    
    // Handle popup overlay click to close
    const submitPopup = document.getElementById('submitPopup');
    if (submitPopup) {
        submitPopup.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('show');
            }
        });
    }
    
    // Handle popup cancel button
    const cancelSubmit = document.getElementById('cancelSubmit');
    if (cancelSubmit) {
        cancelSubmit.addEventListener('click', function() {
            document.getElementById('submitPopup').classList.remove('show');
        });
    }
    
    // Handle popup confirm button
    const confirmSubmit = document.getElementById('confirmSubmit');
    if (confirmSubmit) {
        confirmSubmit.addEventListener('click', async function() {
            // Hide popup
            document.getElementById('submitPopup').classList.remove('show');
            
            const code = editor ? editor.getValue() : '';
            const language = languageSelect ? languageSelect.value : 'python';
            
            if (!code.trim()) {
                alert('Vui lòng nhập code trước khi nộp bài!');
                return;
            }
            
            // Show loading message
            const consoleOutput = document.getElementById('consoleOutput');
            consoleOutput.innerHTML = `
                <div class="console-message" style="background: #fff3cd; color: #856404;">
                    <i class="bx bx-loader bx-spin"></i>
                    Code đã được nộp thành công! Đang chấm bài...
                </div>
            `;
            
            try {
                const response = await fetch('/api/submit-solution', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        code: code,
                        language: language,
                        problem_id: problemId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    const statusColor = result.status === 'Accepted' ? '#d4edda' : '#f8d7da';
                    const statusTextColor = result.status === 'Accepted' ? '#155724' : '#721c24';
                    const statusIcon = result.status === 'Accepted' ? 'bx-check-circle' : 'bx-x-circle';
                    
                    let submissionDetailsHtml = `
                        <div class="submission-details">
                            <div><strong>Test cases passed:</strong> ${result.test_cases_passed}/${result.total_test_cases}</div>
                            <div><strong>Execution time:</strong> ${result.execution_time}ms</div>
                            <div><strong>Memory usage:</strong> ${result.memory_usage}MB</div>
                    `;
                    
                    // Add rating info if submission was accepted and rating was updated
                    if (result.status === 'Accepted' && result.user_rating !== undefined) {
                        const ratingGain = result.test_cases_passed; // Each test case = 1 point
                        submissionDetailsHtml += `
                            <div style="border-top: 1px solid #dee2e6; padding-top: 8px; margin-top: 8px;">
                                <div><strong>Rating:</strong> ${result.user_rating} <span style="color: #28a745;">(+${ratingGain})</span></div>
                        `;
                        
                        if (result.user_rank) {
                            submissionDetailsHtml += `
                                <div><strong>Rank:</strong> <span style="color: ${result.user_rank.color};">${result.user_rank.name}</span></div>
                            `;
                        }
                        
                        submissionDetailsHtml += `</div>`;
                    }
                    
                    submissionDetailsHtml += `</div>`;
                    
                    consoleOutput.innerHTML = `
                        <div class="console-message" style="background: ${statusColor}; color: ${statusTextColor};">
                            <i class="bx ${statusIcon}"></i>
                            <strong>Kết quả:</strong> ${result.status}
                        </div>
                        ${submissionDetailsHtml}
                    `;
                    
                    // Show celebration message if rating increased
                    if (result.status === 'Accepted' && result.user_rating !== undefined) {
                        showRatingCelebration(result.test_cases_passed, result.user_rating, result.user_rank);
                    }
                    
                    // Reload submissions tab if accepted
                    if (result.status === 'Accepted') {
                        setTimeout(() => {
                            const submissionsTab = document.querySelector('.problem-tab[data-tab="submissions"]');
                            if (submissionsTab) {
                                submissionsTab.click();
                                setTimeout(() => {
                                    location.reload();
                                }, 500);
                            }
                        }, 2000);
                    }
                } else {
                    consoleOutput.innerHTML = `
                        <div class="console-message" style="background: #f8d7da; color: #721c24;">
                            <i class="bx bx-error"></i>
                            <strong>Lỗi nộp bài:</strong> ${result.error}
                        </div>
                    `;
                }
            } catch (error) {
                consoleOutput.innerHTML = `
                    <div class="console-message" style="background: #f8d7da; color: #721c24;">
                        <i class="bx bx-error"></i>
                        <strong>Lỗi kết nối:</strong> ${error.message}
                    </div>
                `;
            }
        });
    }
    
    // Clear console functionality
    const clearConsole = document.getElementById('clearConsole');
    if (clearConsole) {
        clearConsole.addEventListener('click', function() {
            const consoleOutput = document.getElementById('consoleOutput');
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
    
    // Rating celebration function
    function showRatingCelebration(ratingGain, newRating, rankInfo) {
        // Create celebration popup
        const celebrationPopup = document.createElement('div');
        celebrationPopup.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            z-index: 10000;
            text-align: center;
            min-width: 300px;
            animation: celebrationSlideIn 0.5s ease-out;
        `;
        
        celebrationPopup.innerHTML = `
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎉</div>
            <h3 style="margin: 0 0 1rem 0; font-size: 1.5rem;">Chúc mừng!</h3>
            <p style="margin: 0 0 1rem 0; font-size: 1.1rem;">
                Bạn đã nhận được <strong>+${ratingGain} điểm rating!</strong>
            </p>
            <p style="margin: 0 0 1rem 0;">
                Rating hiện tại: <strong>${newRating}</strong>
            </p>
            ${rankInfo ? `
                <p style="margin: 0; padding: 0.5rem 1rem; background: rgba(255,255,255,0.2); border-radius: 8px; display: inline-block;">
                    Rank: <strong style="color: ${rankInfo.color};">${rankInfo.name}</strong>
                </p>
            ` : ''}
        `;
        
        // Add celebration styles if not already added
        if (!document.getElementById('celebration-styles')) {
            const style = document.createElement('style');
            style.id = 'celebration-styles';
            style.textContent = `
                @keyframes celebrationSlideIn {
                    from { 
                        transform: translate(-50%, -50%) scale(0.7) rotateY(180deg);
                        opacity: 0;
                    }
                    to { 
                        transform: translate(-50%, -50%) scale(1) rotateY(0deg);
                        opacity: 1;
                    }
                }
                
                @keyframes celebrationSlideOut {
                    from { 
                        transform: translate(-50%, -50%) scale(1);
                        opacity: 1;
                    }
                    to { 
                        transform: translate(-50%, -50%) scale(0.7);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        // Add to body
        document.body.appendChild(celebrationPopup);
        
        // Auto remove after 4 seconds
        setTimeout(() => {
            celebrationPopup.style.animation = 'celebrationSlideOut 0.3s ease-in forwards';
            setTimeout(() => {
                if (celebrationPopup.parentNode) {
                    celebrationPopup.remove();
                }
            }, 300);
        }, 4000);
        
        // Click to dismiss
        celebrationPopup.addEventListener('click', () => {
            celebrationPopup.style.animation = 'celebrationSlideOut 0.3s ease-in forwards';
            setTimeout(() => {
                if (celebrationPopup.parentNode) {
                    celebrationPopup.remove();
                }
            }, 300);
        });
    }
    
    // Auto-save functionality
    function autoSave() {
        const code = editor ? editor.getValue() : '';
        const language = languageSelect ? languageSelect.value : 'python';
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
            languageSelect.dispatchEvent(new Event('change'));
        }
        
        if (savedCode && editor) {
            setTimeout(() => {
                if (editor && editor.setValue) {
                    editor.setValue(savedCode);
                }
            }, 500);
        }
    }
    
    let saveTimeout;
    function scheduleAutoSave() {
        clearTimeout(saveTimeout);
        saveTimeout = setTimeout(autoSave, 1000);
    }
    
    // Load saved code after initialization
    setTimeout(loadSavedCode, 1000);
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            const runBtn = document.getElementById('runCode');
            if (runBtn) runBtn.click();
        }
        
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'Enter') {
            e.preventDefault();
            const submitBtn = document.getElementById('submitCode');
            if (submitBtn) submitBtn.click();
        }
    });
    
    // Function to show rating celebration
    function showRatingCelebration(pointsGained, newRating, rankInfo) {
        // Create celebration element
        const celebration = document.createElement('div');
        celebration.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            text-align: center;
            z-index: 10000;
            animation: celebrationPop 0.5s ease-out;
            min-width: 300px;
        `;
        
        celebration.innerHTML = `
            <div style="font-size: 3em; margin-bottom: 10px;">🎉</div>
            <h3 style="margin: 0 0 15px 0; font-size: 1.5em;">Chúc mừng!</h3>
            <p style="margin: 5px 0; font-size: 1.1em;">+${pointsGained} Rating điểm</p>
            <p style="margin: 5px 0; font-size: 1.1em;">Rating hiện tại: <strong>${newRating}</strong></p>
            ${rankInfo ? `<p style="margin: 5px 0; color: ${rankInfo.color};">Rank: <strong>${rankInfo.name}</strong></p>` : ''}
            <button onclick="this.parentElement.remove()" style="
                margin-top: 15px;
                padding: 8px 20px;
                background: rgba(255, 255, 255, 0.2);
                border: 1px solid rgba(255, 255, 255, 0.3);
                border-radius: 5px;
                color: white;
                cursor: pointer;
                transition: all 0.3s ease;
            " onmouseover="this.style.background='rgba(255, 255, 255, 0.3)'" 
               onmouseout="this.style.background='rgba(255, 255, 255, 0.2)'">
                Đóng
            </button>
        `;
        
        // Add animation styles if not already added
        if (!document.getElementById('celebration-styles')) {
            const style = document.createElement('style');
            style.id = 'celebration-styles';
            style.textContent = `
                @keyframes celebrationPop {
                    0% { 
                        transform: translate(-50%, -50%) scale(0.5); 
                        opacity: 0; 
                    }
                    50% { 
                        transform: translate(-50%, -50%) scale(1.1); 
                    }
                    100% { 
                        transform: translate(-50%, -50%) scale(1); 
                        opacity: 1; 
                    }
                }
            `;
            document.head.appendChild(style);
        }
        
        document.body.appendChild(celebration);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (celebration.parentElement) {
                celebration.remove();
            }
        }, 5000);
    }
});
