class DiscussionsManager {
    constructor() {
        this.currentPage = window.discussionsData?.currentPage || 1;
        this.isLoading = false;
        this.hasMore = window.discussionsData?.hasMore !== false;
        this.currentFilter = window.discussionsData?.currentFilter || 'all';
        this.currentSearch = window.discussionsData?.currentSearch || '';
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        if (this.currentPage === 1 && this.hasMore) {
            this.setupInfiniteScroll();
        }
    }
    
    setupEventListeners() {
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        
        const toggleSearchButton = () => {
            const isEmpty = !searchInput.value.trim();
            searchBtn.disabled = isEmpty;
            if (isEmpty) {
                searchBtn.classList.add('disabled');
            } else {
                searchBtn.classList.remove('disabled');
            }
        };
        
        toggleSearchButton();
        
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            toggleSearchButton();
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.currentSearch = e.target.value;
                this.navigateToPage();
            }, 300);
        });
        
        searchBtn.addEventListener('click', (e) => {
            if (searchBtn.disabled) {
                e.preventDefault();
                return;
            }
            
            this.currentSearch = searchInput.value;
            this.navigateToPage();
        });
        
        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (!searchBtn.disabled) {
                    this.currentSearch = searchInput.value;
                    this.navigateToPage();
                }
            }
        });
        
        const filterBtns = document.querySelectorAll('.filter-btn');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                filterBtns.forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
                
                this.currentFilter = e.target.dataset.filter;
                this.navigateToPage();
            });
        });
    }
    
    setupInfiniteScroll() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.isLoading && this.hasMore) {
                    this.loadMoreDiscussions();
                }
            });
        }, {
            rootMargin: '100px'
        });
        
        const trigger = document.getElementById('loadingTrigger');
        if (trigger) {
            observer.observe(trigger);
        }
    }
    
    navigateToPage() {
        const params = new URLSearchParams();
        if (this.currentFilter !== 'all') {
            params.append('filter', this.currentFilter);
        }
        if (this.currentSearch) {
            params.append('search', this.currentSearch);
        }
        
        const url = '/discussions' + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
    }
    
    async loadMoreDiscussions() {
        if (this.isLoading || !this.hasMore) return;
        
        this.isLoading = true;
        this.showLoading();
        
        try {
            const nextPage = this.currentPage + 1;
            const params = new URLSearchParams({
                page: nextPage,
                filter: this.currentFilter,
                search: this.currentSearch
            });
            
            const response = await fetch(`/discussions/api?${params.toString()}`);
            
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.error || 'Failed to load discussions');
            }
            
            const discussions = data.data.discussions;
            
            if (discussions.length === 0) {
                this.hasMore = false;
                this.hideLoading();
                return;
            }
            
            this.renderDiscussions(discussions);
            this.currentPage = nextPage;
            this.hasMore = data.data.pagination.has_more;
            
        } catch (error) {
            console.error('Error loading discussions:', error);
            this.showError('Không thể tải thêm thảo luận. Vui lòng thử lại.');
        } finally {
            this.isLoading = false;
            this.hideLoading();
        }
    }
    
    renderDiscussions(discussions) {
        const discussionsList = document.getElementById('discussionsList');
        
        discussions.forEach(discussion => {
            const discussionElement = this.createDiscussionElement(discussion);
            discussionsList.appendChild(discussionElement);
        });
    }
    
    createDiscussionElement(discussion) {
        const div = document.createElement('div');
        div.className = `discussion-card ${discussion.is_pinned ? 'pinned' : ''} ${discussion.is_solved ? 'solved' : ''}`;
        div.setAttribute('data-id', discussion.id);
        div.onclick = () => this.openDiscussion(discussion.id);
        
        const categoryMap = {
            'general': 'Tổng Quát',
            'algorithm': 'Thuật Toán',
            'data-structure': 'Cấu Trúc Dữ Liệu',
            'math': 'Toán Học',
            'beginner': 'Người Mới',
            'contest': 'Cuộc Thi',
            'help': 'Trợ Giúp'
        };
        
        const currentUserId = window.currentUserId || null;
        const isAuthor = currentUserId && discussion.author_id == currentUserId;
        
        div.innerHTML = `
            ${discussion.is_pinned ? '<div class="pinned-indicator"><i class="bx bx-pin"></i> Bài viết được ghim</div>' : ''}
            ${discussion.is_solved ? '<div class="solved-indicator"><i class="bx bx-check-circle"></i> Đã được giải quyết</div>' : ''}
            
            <div class="discussion-header">
                <img src="${discussion.author.avatar}" alt="${discussion.author.username}" class="discussion-avatar">
                <div class="discussion-meta">
                    <h3 class="discussion-title">${this.escapeHtml(discussion.title)}</h3>
                    <div class="discussion-author">
                        <span>${this.escapeHtml(discussion.author.first_name + ' ' + discussion.author.last_name)}</span>
                        <div class="discussion-badges">
                            ${discussion.author.badges.map(badge => `<span class="author-badge">${this.escapeHtml(badge)}</span>`).join('')}
                        </div>
                        <span class="discussion-time">• ${discussion.time_ago}</span>
                    </div>
                </div>
                <div class="discussion-options">
                    <button class="discussion-menu-btn" onclick="event.stopPropagation(); toggleDiscussionMenu(${discussion.id})">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                    </button>
                    <div class="discussion-dropdown" id="discussionMenu${discussion.id}">
                        <button class="discussion-dropdown-item" onclick="event.stopPropagation(); bookmarkDiscussion(${discussion.id})">
                            <i class="bx bx-bookmark"></i>
                            <span>Lưu bài viết</span>
                        </button>
                        ${isAuthor ? `
                            <button class="discussion-dropdown-item edit" onclick="event.stopPropagation(); editDiscussion(${discussion.id})">
                                <i class="bx bx-edit"></i>
                                <span>Chỉnh sửa</span>
                            </button>
                            <button class="discussion-dropdown-item delete" onclick="event.stopPropagation(); deleteDiscussion(${discussion.id})">
                                <i class="bx bx-trash"></i>
                                <span>Xóa bài viết</span>
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
            
            <div class="discussion-content">
                <div class="discussion-text">
                    ${this.escapeHtml(discussion.content)}
                </div>
                
                <div class="discussion-tags">
                    <span class="discussion-tag ${discussion.category}">${categoryMap[discussion.category] || discussion.category}</span>
                    ${discussion.tags.slice(0, 3).map(tag => `<span class="discussion-tag">${this.escapeHtml(tag)}</span>`).join('')}
                </div>
            </div>
            
            <div class="discussion-footer">
                <div class="discussion-stats">
                    <div class="discussion-stat likes ${discussion.user_liked ? 'liked' : ''}" 
                         data-discussion-id="${discussion.id}"
                         title="${discussion.user_liked ? 'Bỏ thích' : 'Thích'}">
                        <i class="bx ${discussion.user_liked ? 'bxs-heart' : 'bx-heart'}"></i>
                        <span>${discussion.likes_count}</span>
                    </div>
                    <div class="discussion-stat replies">
                        <i class="bx bx-message-rounded"></i>
                        <span>${discussion.replies_count}</span>
                    </div>
                </div>
                <div class="discussion-actions">
                    <button class="action-btn" onclick="event.stopPropagation(); shareDiscussion(${discussion.id})" title="Chia sẻ">
                        <i class="bx bx-share"></i>
                    </button>
                </div>
            </div>
        `;
        
        return div;
    }
    
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    showLoading() {
        const discussionsList = document.getElementById('discussionsList');
        const existingSpinner = document.getElementById('loadingSpinner');
        
        if (!existingSpinner) {
            const spinner = document.createElement('div');
            spinner.id = 'loadingSpinner';
            spinner.className = 'loading-spinner';
            spinner.innerHTML = '<div class="spinner"></div>Đang tải thêm...';
            discussionsList.appendChild(spinner);
        }
    }
    
    hideLoading() {
        const spinner = document.getElementById('loadingSpinner');
        if (spinner) {
            spinner.remove();
        }
    }
    
    showError(message) {
        const discussionsList = document.getElementById('discussionsList');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'empty-state';
        errorDiv.innerHTML = `
            <i class="bx bx-error"></i>
            <h3>Có lỗi xảy ra</h3>
            <p>${message}</p>
            <button class="new-post-btn" onclick="window.location.reload()">Thử lại</button>
        `;
        discussionsList.appendChild(errorDiv);
    }
    
    openDiscussion(id) {
        window.location.href = `/discussions/${id}`;
    }
}

function toggleDiscussionMenu(discussionId) {
    const menu = document.getElementById(`discussionMenu${discussionId}`);
    const button = menu.closest('.discussion-options').querySelector('.discussion-menu-btn');
    const allMenus = document.querySelectorAll('.discussion-dropdown');
    const allButtons = document.querySelectorAll('.discussion-menu-btn');
    
    // Close all other menus and remove active class from other buttons
    allMenus.forEach(m => {
        if (m.id !== `discussionMenu${discussionId}`) {
            m.classList.remove('show');
        }
    });
    
    allButtons.forEach(btn => {
        if (btn !== button) {
            btn.classList.remove('active');
        }
    });
    
    // Toggle current menu and button active state
    const isShowing = menu.classList.toggle('show');
    if (isShowing) {
        button.classList.add('active');
    } else {
        button.classList.remove('active');
    }
}

document.addEventListener('click', function(event) {
    if (!event.target.closest('.discussion-options')) {
        const allMenus = document.querySelectorAll('.discussion-dropdown');
        const allButtons = document.querySelectorAll('.discussion-menu-btn');
        
        allMenus.forEach(menu => menu.classList.remove('show'));
        allButtons.forEach(button => button.classList.remove('active'));
    }
});

function editDiscussion(id) {
    window.location.href = `/discussions/${id}/edit`;
}

async function deleteDiscussion(id) {
    if (!confirm('Bạn có chắc chắn muốn xóa bài viết này không? Hành động này không thể hoàn tác.')) {
        return;
    }
    
    try {
        const response = await fetch(`/discussions/${id}/delete`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            }
        });
        
        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }
        
        const data = await response.json();
        
        if (data.success) {
            const card = document.querySelector(`[data-id="${id}"]`);
            if (card) {
                card.style.animation = 'fadeOut 0.3s ease';
                setTimeout(() => {
                    card.remove();
                }, 300);
            }
            showNotification('Bài viết đã được xóa thành công!', 'success');
        } else {
            showNotification(data.message || 'Có lỗi xảy ra khi xóa bài viết!', 'error');
        }
    } catch (error) {
        console.error('Error deleting discussion:', error);
        showNotification('Có lỗi xảy ra khi xóa bài viết!', 'error');
    }
}

function createNewPost() {
    window.location.href = '/discussions/create';
}

// Like/Unlike Management System
class LikeManager {
    constructor() {
        this.activeRequests = new Map(); // Track active requests by discussion ID
        this.retryAttempts = new Map(); // Track retry attempts
        this.maxRetries = 3;
        this.setupEventDelegation();
    }
    
    setupEventDelegation() {
        // Remove any existing listeners first
        document.removeEventListener('click', this.handleLikeClick);
        
        // Add single event listener to document
        this.handleLikeClick = this.handleLikeClick.bind(this);
        document.addEventListener('click', this.handleLikeClick);
    }
    
    handleLikeClick(event) {
        const likeElement = event.target.closest('.discussion-stat.likes');
        if (!likeElement) return;
        
        event.preventDefault();
        event.stopPropagation();
        event.stopImmediatePropagation();
        
        const discussionId = likeElement.getAttribute('data-discussion-id');
        if (!discussionId) {
            return;
        }
        
        this.toggleLike(discussionId);
    }
    
    async toggleLike(discussionId) {
        // Prevent concurrent requests for the same discussion
        if (this.activeRequests.has(discussionId)) {
            return false;
        }
        
        // Mark request as active
        this.activeRequests.set(discussionId, true);
        
        try {
            // Find the discussion card
            const card = this.findDiscussionCard(discussionId);
            if (!card) {
                throw new Error(`Discussion card ${discussionId} not found`);
            }
            
            // Get current like state from UI
            const likeStat = card.querySelector('.discussion-stat.likes');
            if (!likeStat) {
                throw new Error(`Like element not found for discussion ${discussionId}`);
            }
            
            const wasLiked = likeStat.classList.contains('liked');
            const currentCount = parseInt(likeStat.querySelector('span').textContent) || 0;
            
            // Make API request
            const response = await this.makeAPIRequest(discussionId);
            
            if (response.success) {
                // Update UI with server data
                const serverLiked = response.action === 'liked';
                const serverCount = response.likes_count;
                
                this.updateLikeUI(likeStat, serverLiked, serverCount);
                return true;
            } else {
                return false;
            }
            
        } catch (error) {
            return false;
        } finally {
            // Always remove from active requests
            this.activeRequests.delete(discussionId);
        }
    }
    
    findDiscussionCard(discussionId) {
        // Try multiple selectors for robustness
        const selectors = [
            `.discussion-card[data-id="${discussionId}"]`,
            `[data-id="${discussionId}"].discussion-card`,
            `#discussion-${discussionId}`,
        ];
        
        let foundElements = [];
        
        for (const selector of selectors) {
            try {
                const elements = document.querySelectorAll(selector);
                if (elements.length > 0) {
                    foundElements = foundElements.concat(Array.from(elements));
                }
            } catch (e) {
                // Some selectors might not be supported in all browsers
                continue;
            }
        }
        
        // Check for duplicates
        const uniqueElements = [...new Set(foundElements)];
        
        if (uniqueElements.length > 0) {
            return uniqueElements[0];
        }
        
        return null;
    }
    
    updateLikeUI(likeStat, isLiked, count) {
        const icon = likeStat.querySelector('i');
        const countSpan = likeStat.querySelector('span');
        
        if (!icon || !countSpan) {
            return;
        }
        
        // Update visual state
        if (isLiked) {
            likeStat.classList.add('liked');
            icon.className = 'bx bxs-heart';
            likeStat.title = 'Bỏ thích';
        } else {
            likeStat.classList.remove('liked');
            icon.className = 'bx bx-heart';
            likeStat.title = 'Thích';
        }
        
        // Update count
        countSpan.textContent = Math.max(0, count);
        
        // Add visual feedback
        this.addLikeAnimation(icon);
    }
    
    addLikeAnimation(icon) {
        // Add a subtle animation for feedback
        icon.style.animation = 'likeAnimation 0.3s ease';
        setTimeout(() => {
            icon.style.animation = '';
        }, 300);
    }
    
    async makeAPIRequest(discussionId) {
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 10000); // 10 second timeout
        
        try {
            const response = await fetch('/discussions/like', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    discussion_id: parseInt(discussionId)
                }),
                signal: controller.signal
            });
            
            clearTimeout(timeoutId);
            
            // Handle authentication redirect
            if (response.status === 401) {
                window.location.href = '/login';
                return { success: false, error: 'Authentication required' };
            }
            
            // Parse response
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || `HTTP ${response.status}`);
            }
            
            return data;
            
        } catch (error) {
            clearTimeout(timeoutId);
            
            if (error.name === 'AbortError') {
                throw new Error('Request timed out');
            }
            
            throw error;
        }
    }
}

// Global like manager instance
const likeManager = new LikeManager();

// Global function for backward compatibility - now just logs
async function likeDiscussion(id) {
    return false; // Don't execute
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Discussions page initialized with event delegation
});

async function bookmarkDiscussion(id) {
    try {
        const response = await fetch('/discussions/bookmark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ discussion_id: id })
        });
        
        if (response.status === 401) {
            window.location.href = '/login';
            return;
        }
        
        const data = await response.json();
        
        if (data.success) {
            const message = data.action === 'bookmarked' ? 'Đã lưu thảo luận!' : 'Đã bỏ lưu thảo luận!';
        }
    } catch (error) {
        console.error('Error bookmarking discussion:', error);
    }
}

function shareDiscussion(id) {
    const url = `${window.location.origin}/discussions/${id}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Thảo luận thú vị trên CodeJudge',
            url: url
        });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            alert('Đã sao chép link vào clipboard!');
        }).catch(() => {
            const textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Đã sao chép link vào clipboard!');
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.discussionsManager = new DiscussionsManager();
});

function createNewPost() {
    const modal = document.getElementById('createPostModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            document.getElementById('postTitle').focus();
        }, 100);
    }
}

function closeCreatePostModal() {
    const modal = document.getElementById('createPostModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
        
        document.getElementById('createPostForm').reset();
    }
}

document.addEventListener('click', function(event) {
    const modal = document.getElementById('createPostModal');
    if (event.target === modal) {
        closeCreatePostModal();
    }
});

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeCreatePostModal();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createPostForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const data = {
                title: formData.get('title').trim(),
                category: formData.get('category'),
                content: formData.get('content').trim(),
                tags: formData.get('tags').trim()
            };
            
            if (!data.title || !data.category || !data.content) {
                showNotification('Vui lòng điền đầy đủ thông tin bắt buộc!', 'error');
                return;
            }
            
            if (data.title.length < 10) {
                showNotification('Tiêu đề phải có ít nhất 10 ký tự!', 'error');
                return;
            }
            
            if (data.content.length < 20) {
                showNotification('Nội dung phải có ít nhất 20 ký tự!', 'error');
                return;
            }
            
            if (data.tags) {
                const tags = data.tags.split(',').map(tag => tag.trim()).filter(tag => tag);
                if (tags.length > 5) {
                    showNotification('Tối đa 5 tags!', 'error');
                    return;
                }
                data.tags = tags;
            } else {
                data.tags = [];
            }
            
            submitNewPost(data);
        });
    }
});

function submitNewPost(data) {
    const submitBtn = document.querySelector('#createPostForm .btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Đang đăng...';
    submitBtn.disabled = true;
    
    const apiUrl = '/api/discussions/create';
    
    fetch(apiUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            showNotification('Bài viết đã được đăng thành công!', 'success');
            closeCreatePostModal();
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showNotification(result.message || 'Có lỗi xảy ra khi đăng bài!', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Có lỗi xảy ra khi đăng bài!', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `popup-notification ${type}`;
    
    const iconMap = {
        success: 'bx-check-circle',
        error: 'bx-error-circle',
        warning: 'bx-error',
        info: 'bx-info-circle'
    };
    
    notification.innerHTML = `
        <div class="notification-content">
            <i class="bx ${iconMap[type]} notification-icon"></i>
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}
