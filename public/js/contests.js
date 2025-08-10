document.addEventListener('DOMContentLoaded', function() {
    // Initialize contests page
    initializeContests();
    
    // Tab switching functionality
    handleTabSwitching();
    
    // Search functionality
    handleSearch();
    
    // Filter functionality
    handleFilters();
    
    // Modal functionality
    handleModals();
    
    // Contest actions
    handleContestActions();
});

function initializeContests() {
    // Load contests based on current tab
    const activeTab = document.querySelector('.tab-btn.active');
    if (activeTab) {
        loadContests(activeTab.dataset.status);
    }
}

function handleTabSwitching() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all tabs
            tabButtons.forEach(btn => btn.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Load contests for this tab
            const status = this.dataset.status;
            loadContests(status);
        });
    });
}

function handleSearch() {
    const searchInput = document.querySelector('.search-input');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                const query = this.value.trim();
                const activeStatus = document.querySelector('.tab-btn.active')?.dataset.status || 'all';
                loadContests(activeStatus, query);
            }, 300);
        });
    }
}

function handleFilters() {
    const filterSelects = document.querySelectorAll('.dropdown select');
    
    filterSelects.forEach(select => {
        select.addEventListener('change', function() {
            const activeStatus = document.querySelector('.tab-btn.active')?.dataset.status || 'all';
            const searchQuery = document.querySelector('.search-input')?.value.trim() || '';
            loadContests(activeStatus, searchQuery, getFilters());
        });
    });
}

function getFilters() {
    const filters = {};
    const filterSelects = document.querySelectorAll('.dropdown select');
    
    filterSelects.forEach(select => {
        if (select.value) {
            filters[select.name] = select.value;
        }
    });
    
    return filters;
}

function handleModals() {
    // Create contest modal
    const createBtn = document.getElementById('createContestBtn');
    const modal = document.getElementById('createContestModal');
    const closeBtn = modal?.querySelector('.close');
    
    if (createBtn && modal) {
        createBtn.addEventListener('click', function() {
            modal.style.display = 'block';
        });
        
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        }
        
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
    
    // Handle form submission
    const createForm = document.getElementById('createContestForm');
    if (createForm) {
        createForm.addEventListener('submit', handleCreateContest);
    }
}

function handleCreateContest(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const contestData = {};
    
    // Collect form data
    for (let [key, value] of formData.entries()) {
        contestData[key] = value;
    }
    
    // Validate form
    if (!validateContestForm(contestData)) {
        return;
    }
    
    // Show loading state
    const submitBtn = event.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Đang tạo...';
    submitBtn.disabled = true;
    
    // Submit contest
    fetch('/api/contests/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(contestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            document.getElementById('createContestModal').style.display = 'none';
            
            // Reset form
            event.target.reset();
            
            // Show success message
            showNotification('Contest đã được tạo thành công!', 'success');
            
            // Reload contests
            const activeStatus = document.querySelector('.tab-btn.active')?.dataset.status || 'all';
            loadContests(activeStatus);
        } else {
            showNotification(data.message || 'Có lỗi xảy ra khi tạo contest', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi tạo contest', 'error');
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function validateContestForm(data) {
    const errors = [];
    
    if (!data.title || data.title.trim().length < 3) {
        errors.push('Tên contest phải có ít nhất 3 ký tự');
    }
    
    if (!data.description || data.description.trim().length < 10) {
        errors.push('Mô tả contest phải có ít nhất 10 ký tự');
    }
    
    if (!data.start_time) {
        errors.push('Vui lòng chọn thời gian bắt đầu');
    }
    
    if (!data.end_time) {
        errors.push('Vui lòng chọn thời gian kết thúc');
    }
    
    if (data.start_time && data.end_time) {
        const startTime = new Date(data.start_time);
        const endTime = new Date(data.end_time);
        const now = new Date();
        
        if (startTime <= now) {
            errors.push('Thời gian bắt đầu phải sau thời điểm hiện tại');
        }
        
        if (endTime <= startTime) {
            errors.push('Thời gian kết thúc phải sau thời gian bắt đầu');
        }
    }
    
    if (errors.length > 0) {
        showNotification(errors.join('<br>'), 'error');
        return false;
    }
    
    return true;
}

function handleContestActions() {
    document.addEventListener('click', function(event) {
        const target = event.target;
        
        // Join contest
        if (target.classList.contains('join-contest-btn')) {
            const contestId = target.dataset.contestId;
            joinContest(contestId);
        }
        
        // Leave contest
        if (target.classList.contains('leave-contest-btn')) {
            const contestId = target.dataset.contestId;
            leaveContest(contestId);
        }
        
        // View contest details
        if (target.classList.contains('view-contest-btn')) {
            const contestId = target.dataset.contestId;
            window.location.href = `/contests/${contestId}`;
        }
        
        // View leaderboard
        if (target.classList.contains('leaderboard-btn')) {
            const contestId = target.dataset.contestId;
            window.location.href = `/contests/${contestId}/leaderboard`;
        }
    });
}

function joinContest(contestId) {
    if (!contestId) return;
    
    const btn = document.querySelector(`[data-contest-id="${contestId}"].join-contest-btn`);
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Đang tham gia...';
    }
    
    fetch(`/api/contests/${contestId}/join`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã tham gia contest thành công!', 'success');
            
            // Update button
            if (btn) {
                btn.textContent = 'Rời khỏi';
                btn.classList.remove('join-contest-btn', 'btn-success');
                btn.classList.add('leave-contest-btn', 'btn-secondary');
                btn.disabled = false;
            }
            
            // Update participant count
            updateParticipantCount(contestId, 1);
        } else {
            showNotification(data.message || 'Có lỗi xảy ra khi tham gia contest', 'error');
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Tham gia';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi tham gia contest', 'error');
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Tham gia';
        }
    });
}

function leaveContest(contestId) {
    if (!contestId) return;
    
    if (!confirm('Bạn có chắc chắn muốn rời khỏi contest này?')) {
        return;
    }
    
    const btn = document.querySelector(`[data-contest-id="${contestId}"].leave-contest-btn`);
    if (btn) {
        btn.disabled = true;
        btn.textContent = 'Đang rời...';
    }
    
    fetch(`/api/contests/${contestId}/leave`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Đã rời khỏi contest!', 'success');
            
            // Update button
            if (btn) {
                btn.textContent = 'Tham gia';
                btn.classList.remove('leave-contest-btn', 'btn-secondary');
                btn.classList.add('join-contest-btn', 'btn-success');
                btn.disabled = false;
            }
            
            // Update participant count
            updateParticipantCount(contestId, -1);
        } else {
            showNotification(data.message || 'Có lỗi xảy ra khi rời contest', 'error');
            if (btn) {
                btn.disabled = false;
                btn.textContent = 'Rời khỏi';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi rời contest', 'error');
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Rời khỏi';
        }
    });
}

function updateParticipantCount(contestId, change) {
    const participantElement = document.querySelector(`[data-contest-id="${contestId}"] .contest-participants`);
    if (participantElement) {
        const currentText = participantElement.textContent;
        const currentCount = parseInt(currentText.match(/\d+/)?.[0] || '0');
        const newCount = Math.max(0, currentCount + change);
        participantElement.textContent = participantElement.textContent.replace(/\d+/, newCount);
    }
}

function loadContests(status = 'all', search = '', filters = {}) {
    const container = document.querySelector('.contests-grid');
    if (!container) return;
    
    // Show loading state
    container.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i></div>';
    
    // Build query parameters
    const params = new URLSearchParams();
    if (status !== 'all') params.append('status', status);
    if (search) params.append('search', search);
    
    // Add filters
    Object.entries(filters).forEach(([key, value]) => {
        if (value) params.append(key, value);
    });
    
    fetch(`/api/contests?${params.toString()}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderContests(data.contests);
            } else {
                showError('Không thể tải danh sách contests');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showError('Có lỗi xảy ra khi tải contests');
        });
}

function renderContests(contests) {
    const grid = document.querySelector('.contests-grid');
    const empty = document.querySelector('.contests-empty');
    if (!grid || !empty) return;

    if (!contests || contests.length === 0) {
        grid.style.display = 'none';
        empty.style.display = '';
        // Đảm bảo nút tạo contest hoạt động
        const createFirstBtn = document.getElementById('createFirstContestBtn');
        if (createFirstBtn) {
            createFirstBtn.addEventListener('click', function() {
                document.getElementById('createContestModal').style.display = 'block';
            });
        }
        return;
    }

    empty.style.display = 'none';
    grid.style.display = '';
    grid.innerHTML = contests.map(contest => renderContestCard(contest)).join('');
}

function renderContestCard(contest) {
    const now = new Date();
    const startTime = new Date(contest.start_time);
    const endTime = new Date(contest.end_time);
    
    let status = 'upcoming';
    let statusText = 'Sắp diễn ra';
    
    if (now >= startTime && now <= endTime) {
        status = 'live';
        statusText = 'Đang diễn ra';
    } else if (now > endTime) {
        status = 'finished';
        statusText = 'Đã kết thúc';
    }
    
    const isJoined = contest.is_joined || false;
    const canJoin = status === 'upcoming' || status === 'live';
    
    let actionButtons = '';
    if (canJoin) {
        if (isJoined) {
            actionButtons = `
                <button class="btn btn-secondary btn-small leave-contest-btn" data-contest-id="${contest.id}">
                    Rời khỏi
                </button>
                <button class="btn btn-primary btn-small view-contest-btn" data-contest-id="${contest.id}">
                    Vào Contest
                </button>
            `;
        } else {
            actionButtons = `
                <button class="btn btn-success btn-small join-contest-btn" data-contest-id="${contest.id}">
                    Tham gia
                </button>
                <button class="btn btn-secondary btn-small view-contest-btn" data-contest-id="${contest.id}">
                    Xem chi tiết
                </button>
            `;
        }
    } else {
        actionButtons = `
            <button class="btn btn-secondary btn-small view-contest-btn" data-contest-id="${contest.id}">
                Xem chi tiết
            </button>
            <button class="btn btn-primary btn-small leaderboard-btn" data-contest-id="${contest.id}">
                Bảng xếp hạng
            </button>
        `;
    }
    
    return `
        <div class="contest-card" data-contest-id="${contest.id}">
            <div class="contest-card-header">
                <div class="contest-status ${status}">${statusText}</div>
                <h3 class="contest-title">${escapeHtml(contest.title)}</h3>
                <p class="contest-description">${escapeHtml(contest.description || 'Không có mô tả')}</p>
            </div>
            <div class="contest-card-body">
                <div class="contest-info">
                    <div class="contest-info-item">
                        <span class="contest-info-label">Bắt đầu</span>
                        <span class="contest-info-value">${formatDateTime(contest.start_time)}</span>
                    </div>
                    <div class="contest-info-item">
                        <span class="contest-info-label">Kết thúc</span>
                        <span class="contest-info-value">${formatDateTime(contest.end_time)}</span>
                    </div>
                    <div class="contest-info-item">
                        <span class="contest-info-label">Thời gian</span>
                        <span class="contest-info-value">${calculateDuration(contest.start_time, contest.end_time)}</span>
                    </div>
                    <div class="contest-info-item">
                        <span class="contest-info-label">Số bài</span>
                        <span class="contest-info-value">${contest.problem_count || 0} bài</span>
                    </div>
                </div>
                <div class="contest-participants">
                    <i class="fas fa-users"></i>
                    <span>${contest.participant_count || 0} người tham gia</span>
                </div>
            </div>
            <div class="contest-card-footer">
                <div class="contest-actions">
                    ${actionButtons}
                </div>
            </div>
        </div>
    `;
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    });
}

function calculateDuration(startTime, endTime) {
    const start = new Date(startTime);
    const end = new Date(endTime);
    const diffMs = end - start;
    const diffHours = Math.floor(diffMs / (1000 * 60 * 60));
    const diffMinutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
    
    if (diffHours >= 24) {
        const days = Math.floor(diffHours / 24);
        const hours = diffHours % 24;
        return `${days} ngày ${hours > 0 ? hours + ' giờ' : ''}`;
    } else if (diffHours > 0) {
        return `${diffHours} giờ ${diffMinutes > 0 ? diffMinutes + ' phút' : ''}`;
    } else {
        return `${diffMinutes} phút`;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close">&times;</button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 5000);
    
    // Handle close button
    const closeBtn = notification.querySelector('.notification-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        });
    }
}

function showError(message) {
    const container = document.querySelector('.contests-grid');
    if (container) {
        container.innerHTML = `
            <div class="text-center text-danger">
                <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                <p>${message}</p>
                <button class="btn btn-primary" onclick="location.reload()">Thử lại</button>
            </div>
        `;
    }
}