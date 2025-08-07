class DiscussionsManager {
    constructor() {
        this.currentPage = window.discussionsData?.currentPage || 1;
        this.isLoading = false;
        this.hasMore = window.discussionsData?.hasMore !== false;
        this.currentFilter = window.discussionsData?.currentFilter || 'all';
        this.currentSearch = window.discussionsData?.currentSearch || '';
        this.observer = null;
        this.loadedDiscussionIds = new Set();
        
        this.initialize();
    }
    
    initialize() {
        this.collectExistingDiscussions();
        
        this.setupSearchAndFilters();
        
        if (this.hasMore && this.currentPage === 1) {
            this.setupInfiniteScroll();
        }
    }
    
    collectExistingDiscussions() {
        const existingCards = document.querySelectorAll('.discussion-card[data-id]');
        const seenIds = new Set();
        
        existingCards.forEach((card, index) => {
            const id = parseInt(card.getAttribute('data-id'));
            if (id) {
                if (seenIds.has(id)) {
                    card.remove();
                } else {
                    seenIds.add(id);
                    this.loadedDiscussionIds.add(id);
                }
            }
        });
    }
    
    setupSearchAndFilters() {
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        
        if (searchInput && searchBtn) {
            // Update button state based on input content
            searchInput.addEventListener('input', (e) => {
                const isEmpty = !e.target.value.trim();
                searchBtn.disabled = isEmpty;
                searchBtn.classList.toggle('disabled', isEmpty);
            });
            
            // Search when click button
            searchBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const searchValue = searchInput.value.trim();
                // Always perform search when button is clicked
                this.navigateToNewPage(this.currentFilter, searchValue);
            });
            
            // Search when press Enter
            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const searchValue = e.target.value.trim();
                    // Always perform search when Enter is pressed
                    this.navigateToNewPage(this.currentFilter, searchValue);
                }
            });
            
            // Initialize button state
            const isEmpty = !searchInput.value.trim();
            searchBtn.disabled = isEmpty;
            searchBtn.classList.toggle('disabled', isEmpty);
        }
        
        const filterBtns = document.querySelectorAll('.filter-btn');
        filterBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const newFilter = btn.dataset.filter;
                if (newFilter !== this.currentFilter) {
                    this.navigateToNewPage(newFilter, this.currentSearch);
                }
            });
        });
    }
    
    setupInfiniteScroll() {
        if (this.observer) {
            this.observer.disconnect();
        }
        
        const trigger = document.getElementById('loadingTrigger');
        if (!trigger) {
            return;
        }
        
        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && this.hasMore && !this.isLoading) {
                    this.loadMoreDiscussions();
                }
            });
        }, {
            rootMargin: '200px',
            threshold: 0.1
        });
        
        this.observer.observe(trigger);
    }
    
    navigateToNewPage(filter, search) {
        const params = new URLSearchParams();
        
        if (filter && filter !== 'all') {
            params.set('filter', filter);
        }
        if (search && search.trim()) {
            params.set('search', search.trim());
        }
        
        const url = '/discussions' + (params.toString() ? '?' + params.toString() : '');
        window.location.href = url;
    }
    
    async loadMoreDiscussions() {
        if (this.isLoading || !this.hasMore) {
            return;
        }
        
        this.isLoading = true;
        this.showLoadingIndicator(true);
        
        try {
            const nextPage = this.currentPage + 1;
            
            const params = new URLSearchParams({
                page: nextPage,
                limit: 10,
                filter: this.currentFilter,
                search: this.currentSearch
            });
            
            const response = await fetch(`/api/discussions?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.discussions && Array.isArray(data.discussions)) {
                const newDiscussions = this.filterNewDiscussions(data.discussions);
                
                if (newDiscussions.length > 0) {
                    this.renderNewDiscussions(newDiscussions);
                    this.currentPage = nextPage;
                }
                
                this.hasMore = data.has_more === true && newDiscussions.length > 0;
                
                if (!this.hasMore) {
                    this.destroyInfiniteScroll();
                }
            } else {
                this.hasMore = false;
                this.destroyInfiniteScroll();
            }
            
        } catch (error) {
            this.showNotification('Không thể tải thêm bài viết. Vui lòng thử lại.', 'error');
            this.hasMore = false;
            this.destroyInfiniteScroll();
        } finally {
            this.isLoading = false;
            this.showLoadingIndicator(false);
        }
    }
    
    filterNewDiscussions(discussions) {
        return discussions.filter(discussion => {
            const id = parseInt(discussion.id);
            return !this.loadedDiscussionIds.has(id);
        });
    }
    
    renderNewDiscussions(discussions) {
        const discussionsList = document.getElementById('discussionsList');
        if (!discussionsList) {
            return;
        }
        
        const fragment = document.createDocumentFragment();
        
        discussions.forEach(discussion => {
            const id = parseInt(discussion.id);
            
            if (this.loadedDiscussionIds.has(id)) {
                return;
            }
            
            const element = this.createDiscussionElement(discussion);
            if (element) {
                fragment.appendChild(element);
                this.loadedDiscussionIds.add(id);
            }
        });
        
        discussionsList.appendChild(fragment);
    }
    
    createDiscussionElement(discussion) {
        const categoryMap = {
            'general': 'Tổng Quát',
            'algorithm': 'Thuật Toán',
            'data-structure': 'Cấu Trúc Dữ Liệu',
            'math': 'Toán Học',
            'beginner': 'Người Mới',
            'contest': 'Cuộc Thi',
            'help': 'Trợ Giúp'
        };
        
        const div = document.createElement('div');
        div.className = `discussion-card ${discussion.is_pinned ? 'pinned' : ''} ${discussion.is_solved ? 'solved' : ''}`;
        div.setAttribute('data-id', discussion.id);
        
        const authorData = discussion.author || discussion;
        const firstName = authorData.first_name || '';
        const lastName = authorData.last_name || '';
        const username = authorData.username || '';
        const avatar = authorData.avatar || '/assets/default-avatar.png';
        
        let tagsHtml = '';
        if (discussion.tags && Array.isArray(discussion.tags)) {
            tagsHtml = discussion.tags.slice(0, 3).map(tag => 
                `<span class="discussion-tag">${this.escapeHtml(tag)}</span>`
            ).join('');
        }
        
        const currentUserId = window.currentUserId || null;
        const isAuthor = currentUserId && discussion.author_id == currentUserId;
        
        div.innerHTML = `
            ${discussion.is_pinned ? '<div class="pinned-indicator"><i class="bx bx-pin"></i> Bài viết được ghim</div>' : ''}
            ${discussion.is_solved ? '<div class="solved-indicator"><i class="bx bx-check-circle"></i> Đã được giải quyết</div>' : ''}
            
            <div class="discussion-header">
                <img src="${this.escapeHtml(avatar)}" 
                     alt="${this.escapeHtml(username)}" class="discussion-avatar"
                     onerror="this.src='/assets/default-avatar.png'">
                <div class="discussion-meta">
                    <h3 class="discussion-title">${this.escapeHtml(discussion.title)}</h3>
                    <div class="discussion-author">
                        <span>${this.escapeHtml(firstName + ' ' + lastName)}</span>
                        <span class="discussion-time">${discussion.time_ago || 'Unknown'}</span>
                    </div>
                </div>
                <div class="discussion-options">
                    <button class="discussion-menu-btn" onclick="event.stopPropagation(); toggleDiscussionMenu(${discussion.id})">
                        <i class="bx bx-dots-horizontal-rounded"></i>
                    </button>
                    <div class="discussion-dropdown" id="discussionMenu${discussion.id}">
                        <button class="discussion-dropdown-item ${discussion.is_bookmarked ? 'bookmarked' : ''}" 
                                data-discussion-id="${discussion.id}"
                                onclick="event.stopPropagation(); bookmarkDiscussion(${discussion.id})">
                            <i class="bx ${discussion.is_bookmarked ? 'bxs-bookmark' : 'bx-bookmark'}"></i>
                            <span>${discussion.is_bookmarked ? 'Đã lưu' : 'Lưu bài viết'}</span>
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
                    ${this.escapeHtml(discussion.content || '').substring(0, 200)}${(discussion.content || '').length > 200 ? '...' : ''}
                </div>
                
                <div class="discussion-tags">
                    <span class="discussion-tag ${discussion.category}">${categoryMap[discussion.category] || discussion.category}</span>
                    ${tagsHtml}
                </div>
            </div>
            
            <div class="discussion-footer">
                <div class="discussion-stats">
                    <div class="discussion-stat likes ${discussion.user_liked ? 'liked' : ''}" 
                         data-discussion-id="${discussion.id}"
                         title="${discussion.user_liked ? 'Bỏ thích' : 'Thích'}">
                        <i class="bx ${discussion.user_liked ? 'bxs-heart' : 'bx-heart'}"></i>
                        <span>${discussion.likes_count || 0}</span>
                    </div>
                    <div class="discussion-stat replies">
                        <i class="bx bx-message-rounded"></i>
                        <span>${discussion.replies_count || 0}</span>
                    </div>
                </div>
                <div class="discussion-actions">
                    <button class="action-btn" onclick="event.stopPropagation(); shareDiscussion(${discussion.id})" title="Chia sẻ">
                        <i class="bx bx-share"></i>
                    </button>
                </div>
            </div>
        `;
        
        div.addEventListener('click', () => {
            window.location.href = `/discussions/${discussion.id}`;
        });
        
        return div;
    }
    
    escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    showLoadingIndicator(show) {
        let indicator = document.getElementById('loadingIndicator');
        
        if (show && !indicator) {
            indicator = document.createElement('div');
            indicator.id = 'loadingIndicator';
            indicator.className = 'loading-indicator';
            indicator.innerHTML = `
                <div class="loading-content">
                    <i class="bx bx-loader-alt bx-spin"></i>
                    <span>Đang tải thêm bài viết...</span>
                </div>
            `;
            
            const trigger = document.getElementById('loadingTrigger');
            if (trigger) {
                trigger.parentNode.insertBefore(indicator, trigger);
            }
        }
        
        if (indicator) {
            indicator.style.display = show ? 'block' : 'none';
        }
    }
    
    showNotification(message, type = 'info') {
        if (typeof showNotification === 'function') {
            showNotification(type, message);
            return;
        }
        
        alert(message);
    }
    
    destroyInfiniteScroll() {
        if (this.observer) {
            this.observer.disconnect();
            this.observer = null;
        }
    }
    
    destroy() {
        this.destroyInfiniteScroll();
        this.loadedDiscussionIds.clear();
    }
}

function openDiscussion(id) {
    window.location.href = `/discussions/${id}`;
}

function toggleDiscussionMenu(discussionId) {
    const menu = document.getElementById(`discussionMenu${discussionId}`);
    if (!menu) return;
    
    const button = menu.closest('.discussion-options')?.querySelector('.discussion-menu-btn');
    
    document.querySelectorAll('.discussion-dropdown').forEach(m => {
        if (m !== menu) m.classList.remove('show');
    });
    document.querySelectorAll('.discussion-menu-btn').forEach(btn => {
        if (btn !== button) btn.classList.remove('active');
    });
    
    const isShowing = menu.classList.toggle('show');
    if (button) {
        button.classList.toggle('active', isShowing);
    }
}

async function editDiscussion(id) {
    try {
        const response = await fetch(`/api/discussions/${id}/edit`);
        if (!response.ok) {
            throw new Error('Không thể tải dữ liệu bài viết');
        }
        
        const discussion = await response.json();
        
        // Show modal first
        showEditPostModal();
        
        // Use longer delay and check if elements exist
        setTimeout(() => {
            populateEditForm(discussion);
        }, 100);
        
        } catch (error) {
            showNotification('Có lỗi xảy ra khi tải dữ liệu bài viết', 'error');
        }
    }function populateEditForm(discussion) {
    const checkElements = () => {
        const elements = {
            id: document.getElementById('editPostId'),
            title: document.getElementById('editPostTitle'),
            category: document.getElementById('editPostCategory'),
            content: document.getElementById('editPostContent'),
            tags: document.getElementById('editPostTags')
        };
        
        const allExist = Object.values(elements).every(el => el !== null);
        
        if (allExist) {
            elements.id.value = discussion.id;
            elements.title.value = discussion.title || '';
            elements.content.value = discussion.content || '';
            elements.tags.value = discussion.tags ? discussion.tags.join(', ') : '';
            
            if (discussion.category) {
                elements.category.value = discussion.category;
                
                setTimeout(() => {
                    if (elements.category.value !== discussion.category) {
                        elements.category.value = discussion.category;
                    }
                }, 10);
                
                elements.category.dispatchEvent(new Event('change'));
            }
            
            window.originalFormData = {
                title: discussion.title || '',
                category: discussion.category || '',
                content: discussion.content || '',
                tags: discussion.tags ? discussion.tags.join(', ') : ''
            };
            
            setTimeout(() => {
                setupEditPostForm();
            }, 50);
        } else {
            setTimeout(checkElements, 50);
        }
    };
    
    checkElements();
}

function showEditPostModal() {
    const modal = document.getElementById('editPostModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
        
        const form = document.getElementById('editPostForm');
        if (form) {
            form.reset();
        }
    }
}

function closeEditPostModal() {
    const modal = document.getElementById('editPostModal');
    if (modal) {
        modal.classList.remove('show');
        setTimeout(() => {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }, 300);
        
        document.getElementById('editPostForm').reset();
    }
}

function setupEditPostForm() {
    const form = document.getElementById('editPostForm');
    if (!form) return;
    
    const currentValues = {
        id: document.getElementById('editPostId')?.value || '',
        title: document.getElementById('editPostTitle')?.value || '',
        category: document.getElementById('editPostCategory')?.value || '',
        content: document.getElementById('editPostContent')?.value || '',
        tags: document.getElementById('editPostTags')?.value || ''
    };
    
    const newForm = form.cloneNode(true);
    form.parentNode.replaceChild(newForm, form);
    
    setTimeout(() => {
        const elements = {
            id: document.getElementById('editPostId'),
            title: document.getElementById('editPostTitle'),
            category: document.getElementById('editPostCategory'),
            content: document.getElementById('editPostContent'),
            tags: document.getElementById('editPostTags')
        };
        
        if (elements.id) elements.id.value = currentValues.id;
        if (elements.title) elements.title.value = currentValues.title;
        if (elements.category) {
            elements.category.value = currentValues.category;
        }
        if (elements.content) elements.content.value = currentValues.content;
        if (elements.tags) elements.tags.value = currentValues.tags;
    }, 10);
    
    const submitBtn = newForm.querySelector('.discussions-btn-submit');
    
    function checkForChanges() {
        if (!window.originalFormData) {
            return false;
        }
        
        const currentData = {
            title: document.getElementById('editPostTitle')?.value || '',
            category: document.getElementById('editPostCategory')?.value || '',
            content: document.getElementById('editPostContent')?.value || '',
            tags: document.getElementById('editPostTags')?.value || ''
        };
        
        const hasChanges = (
            currentData.title.trim() !== window.originalFormData.title.trim() ||
            currentData.category !== window.originalFormData.category ||
            currentData.content.trim() !== window.originalFormData.content.trim() ||
            currentData.tags.trim() !== window.originalFormData.tags.trim()
        );
        
        if (submitBtn) {
            submitBtn.disabled = !hasChanges;
            submitBtn.style.opacity = hasChanges ? '1' : '0.6';
            submitBtn.style.cursor = hasChanges ? 'pointer' : 'not-allowed';
        }
        
        return hasChanges;
    }
    
    const fields = ['editPostTitle', 'editPostCategory', 'editPostContent', 'editPostTags'];
    fields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', checkForChanges);
            field.addEventListener('change', checkForChanges);
            field.addEventListener('keyup', checkForChanges);
        }
    });
    
    setTimeout(() => {
        checkForChanges();
    }, 300);
    
    newForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!checkForChanges()) {
            showNotification('Không có thay đổi nào để cập nhật', 'warning');
            return;
        }
        
        const originalText = submitBtn.innerHTML;
        
        try {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<div class="discussions-loading-spinner"></div> Đang cập nhật...';
            
            const formData = new FormData(newForm);
            const data = {
                id: formData.get('post_id'),
                title: formData.get('title'),
                category: formData.get('category'),
                content: formData.get('content'),
                tags: formData.get('tags') ? formData.get('tags').split(',').map(tag => tag.trim()).filter(tag => tag) : []
            };
            
            const response = await fetch(`/api/discussions/${data.id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
            
            if (!response.ok) {
                throw new Error('Có lỗi xảy ra khi cập nhật bài viết');
            }
            
            const result = await response.json();
            
            showNotification('Cập nhật bài viết thành công!', 'success');
            closeEditPostModal();
            
            setTimeout(() => {
                window.location.reload();
            }, 1000);
            
        } catch (error) {
            showNotification(error.message || 'Có lỗi xảy ra khi cập nhật bài viết', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
}

async function deleteDiscussion(id) {
    showDeleteModal(id);
}

function showDeleteModal(discussionId) {
    const modal = document.getElementById('deleteConfirmModal');
    if (!modal) {
        return;
    }
    
    modal.style.display = 'flex';
    modal.classList.add('show');
    
    document.body.style.overflow = 'hidden';
    
    setupDeleteModalHandlers(discussionId);
}

function hideDeleteModal() {
    const modal = document.getElementById('deleteConfirmModal');
    if (!modal) return;
    
    modal.classList.remove('show');
    
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 300);
    
    cleanupDeleteModalHandlers();
}

function setupDeleteModalHandlers(discussionId) {
    const confirmBtn = document.getElementById('confirmDelete');
    const cancelBtn = document.getElementById('cancelDelete');
    const modal = document.getElementById('deleteConfirmModal');
    
    window.deleteModalHandlers = {
        confirm: () => handleConfirmDelete(discussionId),
        cancel: () => handleCancelDelete(),
        overlay: (e) => handleOverlayClick(e, modal)
    };
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', window.deleteModalHandlers.confirm);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', window.deleteModalHandlers.cancel);
    }
    
    if (modal) {
        modal.addEventListener('click', window.deleteModalHandlers.overlay);
    }
    
    document.addEventListener('keydown', handleEscapeKey);
}

function cleanupDeleteModalHandlers() {
    const confirmBtn = document.getElementById('confirmDelete');
    const cancelBtn = document.getElementById('cancelDelete');
    const modal = document.getElementById('deleteConfirmModal');
    
    if (confirmBtn && window.deleteModalHandlers) {
        confirmBtn.removeEventListener('click', window.deleteModalHandlers.confirm);
    }
    
    if (cancelBtn && window.deleteModalHandlers) {
        cancelBtn.removeEventListener('click', window.deleteModalHandlers.cancel);
    }
    
    if (modal && window.deleteModalHandlers) {
        modal.removeEventListener('click', window.deleteModalHandlers.overlay);
    }
    
    document.removeEventListener('keydown', handleEscapeKey);
    
    delete window.deleteModalHandlers;
}

function handleConfirmDelete(discussionId) {
    performDeleteDiscussion(discussionId);
}

function handleCancelDelete() {
    hideDeleteModal();
}

function handleOverlayClick(e, modal) {
    if (e.target === modal) {
        hideDeleteModal();
    }
}

function handleEscapeKey(e) {
    if (e.key === 'Escape') {
        hideDeleteModal();
    }
}

async function performDeleteDiscussion(id) {
    const confirmBtn = document.getElementById('confirmDelete');
    
    try {
        if (confirmBtn) {
            confirmBtn.classList.add('loading');
            confirmBtn.innerHTML = '<i class="bx bx-loader-alt"></i> Đang xóa...';
            confirmBtn.disabled = true;
        }
        
        const response = await fetch(`/api/discussions/${id}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            hideDeleteModal();
            
            const discussionCard = document.querySelector(`[data-id="${id}"]`);
            if (discussionCard) {
                discussionCard.style.transition = 'all 0.5s ease';
                discussionCard.style.opacity = '0';
                discussionCard.style.transform = 'translateX(-100%) scale(0.8)';
                
                setTimeout(() => {
                    discussionCard.remove();
                    showNotification('Đã xóa bài viết thành công!', 'success');
                }, 500);
                
                if (window.discussionsManager && window.discussionsManager.loadedDiscussionIds) {
                    window.discussionsManager.loadedDiscussionIds.delete(parseInt(id));
                }
            }
        } else {
            hideDeleteModal();
            showNotification(result.message || 'Có lỗi xảy ra khi xóa bài viết!', 'error');
        }
    } catch (error) {
        hideDeleteModal();
        showNotification('Có lỗi xảy ra khi xóa bài viết!', 'error');
    } finally {
        if (confirmBtn) {
            confirmBtn.classList.remove('loading');
            confirmBtn.innerHTML = '<i class="bx bx-trash"></i> Xóa bài viết';
            confirmBtn.disabled = false;
        }
    }
}

async function bookmarkDiscussion(id) {
    try {
        const response = await fetch('/api/discussions/bookmark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ discussion_id: id })
        });
        
        const result = await response.json();
        
        // Update UI to reflect bookmark status
        const bookmarkBtn = document.querySelector(`#discussionMenu${id} .discussion-dropdown-item[data-discussion-id="${id}"]`);
        if (bookmarkBtn) {
            const icon = bookmarkBtn.querySelector('i');
            const text = bookmarkBtn.querySelector('span');
            
            if (result.action === 'bookmarked') {
                // Change to bookmarked state (yellow/gold)
                bookmarkBtn.classList.add('bookmarked');
                if (icon) {
                    icon.className = 'bx bxs-bookmark'; // Solid bookmark icon
                }
                if (text) {
                    text.textContent = 'Đã lưu';
                }
                showNotification('Đã lưu bài viết', 'success');
            } else {
                // Change to unbookmarked state
                bookmarkBtn.classList.remove('bookmarked');
                if (icon) {
                    icon.className = 'bx bx-bookmark'; // Outline bookmark icon
                }
                if (text) {
                    text.textContent = 'Lưu bài viết';
                }
                showNotification('Đã bỏ lưu bài viết', 'info');
            }
        }
    } catch (error) {
        showNotification('Có lỗi xảy ra', 'error');
    }
}

function shareDiscussion(id) {
    const url = `${window.location.origin}/discussions/${id}`;
    
    if (navigator.share) {
        navigator.share({
            title: 'Chia sẻ thảo luận',
            url: url
        });
    } else {
        navigator.clipboard.writeText(url).then(() => {
            showNotification('Đã sao chép link vào clipboard', 'success');
        }).catch(() => {
            showNotification('Không thể sao chép link', 'error');
        });
    }
}

class LikeManager {
    constructor() {
        this.setupEventDelegation();
    }
    
    setupEventDelegation() {
        document.addEventListener('click', (event) => {
            const likeStat = event.target.closest('.discussion-stat.likes');
            if (likeStat) {
                this.handleLikeClick(event);
            }
        });
    }
    
    handleLikeClick(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const likeStat = event.target.closest('.discussion-stat.likes');
        const discussionId = likeStat.getAttribute('data-discussion-id');
        
        if (discussionId) {
            this.toggleLike(discussionId);
        }
    }
    
    async toggleLike(discussionId) {
        const likeStat = document.querySelector(`.discussion-stat.likes[data-discussion-id="${discussionId}"]`);
        if (!likeStat) return;
        
        const icon = likeStat.querySelector('i');
        const countSpan = likeStat.querySelector('span');
        
        likeStat.style.pointerEvents = 'none';
        
        try {
            const response = await fetch('/api/discussions/like', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ discussion_id: discussionId })
            });
            
            const result = await response.json();
            
            if (result.success) {
                const isLiked = result.action === 'liked';
                const newCount = result.likes_count || 0;
                
                if (isLiked) {
                    likeStat.classList.add('liked');
                    icon.className = 'bx bxs-heart';
                    likeStat.title = 'Bỏ thích';
                    
                    icon.style.transform = 'scale(1.3)';
                    setTimeout(() => {
                        icon.style.transform = 'scale(1)';
                    }, 200);
                } else {
                    likeStat.classList.remove('liked');
                    icon.className = 'bx bx-heart';
                    likeStat.title = 'Thích';
                }
                
                countSpan.textContent = newCount;
            }
        } catch (error) {
            showNotification('Không thể thực hiện hành động này', 'error');
        } finally {
            likeStat.style.pointerEvents = 'auto';
        }
    }
}

function createNewPost() {
    const modal = document.getElementById('createPostModal');
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeCreatePostModal() {
    const modal = document.getElementById('createPostModal');
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = 'auto';
        
        const form = document.getElementById('createPostForm');
        if (form) {
            form.reset();
            const errorGroups = form.querySelectorAll('.discussions-form-group.error');
            errorGroups.forEach(group => {
                group.classList.remove('error');
                const errorMsg = group.querySelector('.discussions-error-message');
                if (errorMsg) errorMsg.remove();
            });
        }
    }
}

function submitNewPost(data) {
    const submitBtn = document.querySelector('#createPostForm .discussions-btn-submit');
    if (!submitBtn) return;
    
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<div class="discussions-loading-spinner"></div> Đang đăng...';
    submitBtn.disabled = true;
    
    fetch('/api/discussions/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(data)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => {
                throw new Error(err.message || 'Có lỗi xảy ra');
            });
        }
        return response.json();
    })
    .then(result => {
        if (result.success) {
            showNotification('Đã đăng bài viết thành công!', 'success');
            closeCreatePostModal();
            
            setTimeout(() => {
                window.location.href = '/discussions';
            }, 1000);
        } else {
            throw new Error(result.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        showNotification(error.message || 'Có lỗi xảy ra khi đăng bài', 'error');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.discussionsManager) {
        if (typeof window.discussionsManager.destroy === 'function') {
            window.discussionsManager.destroy();
        }
        window.discussionsManager = null;
    }
    
    window.discussionsManager = new DiscussionsManager();
    
    if (!window.likeManager) {
        window.likeManager = new LikeManager();
    }
    
    document.addEventListener('click', function(event) {
        const discussionCard = event.target.closest('.discussion-card[data-id]');
        if (discussionCard && !event.target.closest('.discussion-stat, .action-btn, .discussion-menu-btn, .discussion-dropdown')) {
            const discussionId = discussionCard.getAttribute('data-id');
            if (discussionId) {
                window.location.href = `/discussions/${discussionId}`;
            }
        }
    });
    
    const form = document.getElementById('createPostForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(form);
            const data = {
                title: formData.get('title')?.trim(),
                category: formData.get('category'),
                content: formData.get('content')?.trim(),
                tags: formData.get('tags')?.trim()
            };
            
            let isValid = true;
            
            const titleInput = document.getElementById('postTitle');
            if (!data.title) {
                validateFieldError(titleInput, 'Vui lòng nhập tiêu đề');
                isValid = false;
            }
            
            const categorySelect = document.getElementById('postCategory');
            if (!data.category) {
                validateFieldError(categorySelect, 'Vui lòng chọn danh mục');
                isValid = false;
            }
            
            const contentInput = document.getElementById('postContent');
            if (!data.content) {
                validateFieldError(contentInput, 'Vui lòng nhập nội dung');
                isValid = false;
            }
            
            if (!isValid) {
                showNotification('Vui lòng điền đầy đủ thông tin bắt buộc!', 'error');
                return;
            }
            
            submitNewPost(data);
        });
    }
    
    function validateFieldError(field, message) {
        const parent = field.closest('.discussions-form-group');
        if (!parent) return;
        
        parent.classList.add('error');
        
        const existingError = parent.querySelector('.discussions-error-message');
        if (existingError) {
            existingError.remove();
        }
        
        const errorMsg = document.createElement('div');
        errorMsg.className = 'discussions-error-message';
        errorMsg.innerHTML = `<i class='bx bx-error-circle'></i> ${message}`;
        parent.appendChild(errorMsg);
    }
    
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.discussion-options')) {
            document.querySelectorAll('.discussion-dropdown').forEach(menu => {
                menu.classList.remove('show');
            });
            document.querySelectorAll('.discussion-menu-btn').forEach(btn => {
                btn.classList.remove('active');
            });
        }
        
        const createModal = document.getElementById('createPostModal');
        if (event.target === createModal) {
            closeCreatePostModal();
        }
        
        const editModal = document.getElementById('editPostModal');
        if (event.target === editModal) {
            closeEditPostModal();
        }
    });
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeCreatePostModal();
            closeEditPostModal();
        }
    });
});

if (typeof showNotification !== 'function') {
    window.showNotification = function(message, type = 'info') {
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
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 5000);
    };
    
    setupDiscussionsModal();
}

function setupDiscussionsModal() {
    const modal = document.getElementById('createPostModal');
    const modalContent = modal?.querySelector('.discussions-modal-content');
    const closeBtn = modal?.querySelector('.discussions-modal-close');
    const cancelBtn = modal?.querySelector('.discussions-btn-cancel');
    
    if (!modal) return;
    
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeCreatePostModal();
        }
    });
    
    if (modalContent) {
        modalContent.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', closeCreatePostModal);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeCreatePostModal);
    }
    
    setupFormValidation();
}

function setupFormValidation() {
    const titleInput = document.getElementById('postTitle');
    const contentInput = document.getElementById('postContent');
    const categorySelect = document.getElementById('postCategory');
    
    function validateField(field, message) {
        const parent = field.closest('.discussions-form-group');
        if (!parent) return false;
        
        if (!field.value.trim()) {
            parent.classList.add('error');
            
            const existingError = parent.querySelector('.discussions-error-message');
            if (existingError) {
                existingError.remove();
            }
            
            const errorMsg = document.createElement('div');
            errorMsg.className = 'discussions-error-message';
            errorMsg.innerHTML = `<i class='bx bx-error-circle'></i> ${message}`;
            parent.appendChild(errorMsg);
            
            return false;
        } else {
            parent.classList.remove('error');
            const existingError = parent.querySelector('.discussions-error-message');
            if (existingError) {
                existingError.remove();
            }
            
            return true;
        }
    }
    
    if (titleInput) {
        titleInput.addEventListener('blur', function() {
            validateField(this, 'Vui lòng nhập tiêu đề');
        });
    }
    
    if (contentInput) {
        contentInput.addEventListener('blur', function() {
            validateField(this, 'Vui lòng nhập nội dung');
        });
    }
    
    if (categorySelect) {
        categorySelect.addEventListener('change', function() {
            validateField(this, 'Vui lòng chọn danh mục');
        });
    }
}
