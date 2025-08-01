/**
 * LEADERBOARD JAVASCRIPT - SIMPLIFIED VERSION
 * Xử lý các tương tác trên trang leaderboard mà không ghi đè dữ liệu PHP
 */

class LeaderboardManager {
    constructor() {
        this.currentPage = 1;
        this.currentRankFilter = 'all';
        this.isLoading = false;
        this.init();
    }

    init() {
        this.bindEvents();
        this.parseURLParams();
        this.addScrollAnimation();
        
        // KHÔNG tự động load data - chỉ sử dụng data từ PHP
        console.log('LeaderboardManager initialized - using PHP data only');
    }
    
    parseURLParams() {
        const urlParams = new URLSearchParams(window.location.search);
        this.currentPage = parseInt(urlParams.get('page')) || 1;
        this.currentRankFilter = urlParams.get('rank') || 'all';
    }

    bindEvents() {
        // Click events cho rank items
        document.querySelectorAll('.rank-item').forEach(item => {
            item.addEventListener('click', (e) => {
                if (!e.target.closest('.rank-filter-btn')) {
                    const tier = item.dataset.tier;
                    this.filterByRank(tier);
                }
            });
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) return;
            
            switch(e.key) {
                case 'ArrowLeft':
                    if (this.currentPage > 1) {
                        this.changePage(this.currentPage - 1);
                    }
                    break;
                case 'ArrowRight':
                    this.changePage(this.currentPage + 1);
                    break;
                case 'Home':
                    e.preventDefault();
                    this.scrollToTop();
                    break;
                case 'r':
                    if (!e.target.matches('input, textarea')) {
                        this.refreshData();
                    }
                    break;
            }
        });

        // Intersection Observer cho lazy loading
        this.setupIntersectionObserver();
    }

    /**
     * Lọc leaderboard theo rank tier - Redirect thay vì AJAX
     */
    filterByRank(tier) {
        if (this.isLoading) return;
        
        // Redirect với parameter mới
        const currentUrl = new URL(window.location);
        
        if (tier === 'all') {
            currentUrl.searchParams.delete('rank');
        } else {
            currentUrl.searchParams.set('rank', tier);
        }
        
        // Reset về trang 1 khi filter
        currentUrl.searchParams.delete('page');
        
        window.location.href = currentUrl.toString();
    }

    /**
     * Thay đổi trang - Redirect thay vì AJAX
     */
    changePage(page) {
        if (this.isLoading || page < 1) return;
        
        const currentUrl = new URL(window.location);
        currentUrl.searchParams.set('page', page);
        
        window.location.href = currentUrl.toString();
    }

    /**
     * Load dữ liệu leaderboard qua AJAX - DISABLED
     */
    async loadLeaderboardData() {
        console.log('loadLeaderboardData called but disabled - using PHP data only');
        return;
        
        // AJAX code disabled to prevent duplicates
        /*
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoadingState();
        
        try {
            const params = new URLSearchParams({
                page: this.currentPage,
                rank: this.currentRankFilter,
                limit: 50
            });
            
            const response = await fetch(`/leaderboard/api?${params}`);
            const data = await response.json();
            
            if (data.success) {
                this.updateLeaderboardTable(data.data);
                this.updatePagination(data.pagination);
                this.updateStats(data.pagination);
            } else {
                this.showError('Không thể tải dữ liệu leaderboard');
            }
        } catch (error) {
            console.error('Error loading leaderboard:', error);
            this.showError('Có lỗi xảy ra khi tải dữ liệu');
        } finally {
            this.isLoading = false;
            this.hideLoadingState();
        }
        */
    }

    /**
     * Cập nhật bảng leaderboard
     */
    updateLeaderboardTable(leaderboard) {
        const tableBody = document.getElementById('leaderboardTableBody');
        if (!tableBody) return;
        
        if (leaderboard.length === 0) {
            tableBody.innerHTML = `
                <div class="no-data">
                    <i class="bx bx-trophy"></i>
                    <p>Không có dữ liệu cho rank này</p>
                </div>
            `;
            return;
        }
        
        const html = leaderboard.map((user, index) => {
            const badgesHtml = this.generateBadgesHtml(user.badges);
            const avatarSrc = user.avatar_src || this.generateDefaultAvatar(user.username);
            
            return `
                <div class="leaderboard-row" data-user-id="${user.id}" style="animation-delay: ${index * 0.1}s">
                    <div class="rank-col">
                        <div class="rank-badge rank-${user.rank_tier}">
                            #${this.formatNumber(user.rank)}
                        </div>
                    </div>
                    
                    <div class="user-col">
                        <div class="user-info">
                            <div class="user-avatar">
                                <img src="${avatarSrc}" alt="${this.escapeHtml(user.username)}">
                            </div>
                            <div class="user-details">
                                <div class="username">${this.escapeHtml(user.username)}</div>
                                <div class="full-name">${this.escapeHtml(user.full_name)}</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="problems-col">
                        <div class="problems-solved">
                            <span class="number">${this.formatNumber(user.problems_solved)}</span>
                            <span class="label">bài</span>
                        </div>
                        <div class="submissions">
                            ${this.formatNumber(user.total_submissions)} lần gửi
                        </div>
                    </div>
                    
                    <div class="rating-col">
                        <div class="rating-value ${user.rating >= 0 ? 'positive' : 'negative'}">
                            ${user.rating >= 0 ? this.formatNumber(user.rating) : 'Chưa có'}
                        </div>
                    </div>
                    
                    <div class="badges-col">
                        <div class="badges-container">
                            ${badgesHtml}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
        
        tableBody.innerHTML = html;
        
        // Bind click events cho user rows
        this.bindUserRowEvents();
    }

    /**
     * Generate HTML cho badges
     */
    generateBadgesHtml(badges) {
        if (!badges || badges.length === 0) {
            return '<div class="no-badges">Chưa có huy hiệu</div>';
        }
        
        const badgeItems = badges.slice(0, 3).map(badge => `
            <div class="badge-item">
                <img src="/assets/badges_${badge}.svg" 
                     alt="${badge}"
                     title="${this.formatBadgeName(badge)}"
                     onerror="this.src='/assets/default-badge.svg'">
            </div>
        `).join('');
        
        const moreCount = badges.length > 3 ? `<div class="badge-more">+${badges.length - 3}</div>` : '';
        
        return badgeItems + moreCount;
    }

    /**
     * Cập nhật pagination
     */
    updatePagination(pagination) {
        const paginationEl = document.querySelector('.pagination');
        if (!paginationEl) return;
        
        const { current_page, total_pages } = pagination;
        
        if (total_pages <= 1) {
            paginationEl.style.display = 'none';
            return;
        }
        
        paginationEl.style.display = 'flex';
        
        let html = '';
        
        // Previous button
        if (current_page > 1) {
            html += `
                <button class="page-btn" onclick="leaderboardManager.changePage(${current_page - 1})">
                    <i class="bx bx-chevron-left"></i> Trước
                </button>
            `;
        }
        
        // Page numbers
        html += '<div class="page-numbers">';
        const start = Math.max(1, current_page - 2);
        const end = Math.min(total_pages, current_page + 2);
        
        if (start > 1) {
            html += `<button class="page-btn" onclick="leaderboardManager.changePage(1)">1</button>`;
            if (start > 2) {
                html += '<span class="page-dots">...</span>';
            }
        }
        
        for (let i = start; i <= end; i++) {
            const activeClass = i === current_page ? 'active' : '';
            html += `<button class="page-btn ${activeClass}" onclick="leaderboardManager.changePage(${i})">${i}</button>`;
        }
        
        if (end < total_pages) {
            if (end < total_pages - 1) {
                html += '<span class="page-dots">...</span>';
            }
            html += `<button class="page-btn" onclick="leaderboardManager.changePage(${total_pages})">${total_pages}</button>`;
        }
        
        html += '</div>';
        
        // Next button
        if (current_page < total_pages) {
            html += `
                <button class="page-btn" onclick="leaderboardManager.changePage(${current_page + 1})">
                    Sau <i class="bx bx-chevron-right"></i>
                </button>
            `;
        }
        
        paginationEl.innerHTML = html;
    }

    /**
     * Cập nhật filter buttons
     */
    updateFilterButtons() {
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        const activeBtn = document.querySelector(`.filter-btn[onclick*="${this.currentRankFilter}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }
    }

    /**
     * Cập nhật rank items
     */
    updateRankItems() {
        document.querySelectorAll('.rank-item').forEach(item => {
            item.classList.remove('active');
            if (item.dataset.tier === this.currentRankFilter) {
                item.classList.add('active');
            }
        });
    }

    /**
     * Cập nhật thống kê
     */
    updateStats(pagination) {
        const totalUsersEl = document.querySelector('.total-users');
        const pageInfoEl = document.querySelector('.page-info');
        const statValueEls = document.querySelectorAll('.stat-value');
        
        if (totalUsersEl) {
            totalUsersEl.textContent = `${this.formatNumber(pagination.total_users)} thành viên`;
        }
        
        if (pageInfoEl) {
            pageInfoEl.textContent = `Trang ${pagination.current_page}/${pagination.total_pages}`;
        }
        
        if (statValueEls.length >= 2) {
            statValueEls[0].textContent = this.formatNumber(pagination.total_users);
            statValueEls[1].textContent = this.formatNumber(pagination.limit);
        }
    }

    /**
     * Hiển thị trạng thái loading
     */
    showLoadingState() {
        const tableBody = document.getElementById('leaderboardTableBody');
        if (!tableBody) return;
        
        const skeletonRows = Array.from({ length: 10 }, (_, i) => `
            <div class="leaderboard-row loading-skeleton" style="animation: none;">
                <div class="rank-col"><div style="height: 20px; border-radius: 4px;"></div></div>
                <div class="user-col"><div style="height: 40px; border-radius: 4px;"></div></div>
                <div class="problems-col"><div style="height: 30px; border-radius: 4px;"></div></div>
                <div class="rating-col"><div style="height: 20px; border-radius: 4px;"></div></div>
                <div class="badges-col"><div style="height: 24px; border-radius: 4px;"></div></div>
            </div>
        `).join('');
        
        tableBody.innerHTML = skeletonRows;
    }

    /**
     * Ẩn trạng thái loading
     */
    hideLoadingState() {
        // Loading state sẽ được thay thế bởi data thực
    }

    /**
     * Hiển thị lỗi
     */
    showError(message) {
        const tableBody = document.getElementById('leaderboardTableBody');
        if (!tableBody) return;
        
        tableBody.innerHTML = `
            <div class="no-data">
                <i class="bx bx-error"></i>
                <p>${message}</p>
                <button class="action-btn primary" onclick="leaderboardManager.refreshData()">
                    <i class="bx bx-refresh"></i> Thử lại
                </button>
            </div>
        `;
    }

    /**
     * Refresh dữ liệu - Reload trang thay vì AJAX
     */
    refreshData() {
        window.location.reload();
    }

    /**
     * Scroll to top
     */
    scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    /**
     * Cập nhật URL
     */
    updateURL() {
        const params = new URLSearchParams();
        if (this.currentPage > 1) params.set('page', this.currentPage);
        if (this.currentRankFilter !== 'all') params.set('rank', this.currentRankFilter);
        
        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        history.replaceState(null, '', newURL);
    }

    /**
     * Bind events cho user rows
     */
    bindUserRowEvents() {
        document.querySelectorAll('.leaderboard-row').forEach(row => {
            row.addEventListener('click', () => {
                const userId = row.dataset.userId;
                if (userId) {
                    window.location.href = `/profile/${userId}`;
                }
            });
        });
    }

    /**
     * Setup Intersection Observer
     */
    setupIntersectionObserver() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animationPlayState = 'running';
                }
            });
        }, { threshold: 0.1 });

        // Observe leaderboard rows when they're added
        this.observeRows = (rows) => {
            rows.forEach(row => observer.observe(row));
        };
    }

    /**
     * Add scroll animations
     */
    addScrollAnimation() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-in');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.rank-item, .leaderboard-row').forEach(el => {
            observer.observe(el);
        });
    }

    /**
     * Generate default avatar cho user không có avatar
     */
    generateDefaultAvatar(username) {
        const colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#FFB347', '#87CEEB', '#98D8C8', '#F7DC6F'
        ];
        
        const bgColor = colors[username.length % colors.length];
        const initials = username.charAt(0).toUpperCase();
        const size = 150;
        
        const svg = `<svg width="${size}" height="${size}" xmlns="http://www.w3.org/2000/svg">
            <rect width="${size}" height="${size}" fill="${bgColor}"/>
            <text x="50%" y="50%" 
                  font-family="Arial, sans-serif" 
                  font-size="${size * 0.4}" 
                  fill="white" 
                  text-anchor="middle" 
                  dominant-baseline="central">${initials}</text>
        </svg>`;
        
        return 'data:image/svg+xml;base64,' + btoa(svg);
    }

    /**
     * Utility functions
     */
    formatNumber(num) {
        return new Intl.NumberFormat('vi-VN').format(num);
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    formatBadgeName(badge) {
        return badge.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }
}

// Global functions for onclick handlers
function filterByRank(tier) {
    if (window.leaderboardManager) {
        window.leaderboardManager.filterByRank(tier);
    }
}

function changePage(page) {
    if (window.leaderboardManager) {
        window.leaderboardManager.changePage(page);
    }
}

function scrollToTop() {
    if (window.leaderboardManager) {
        window.leaderboardManager.scrollToTop();
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.leaderboardManager = new LeaderboardManager();
    
    // Parse initial state from URL
    const urlParams = new URLSearchParams(window.location.search);
    const page = parseInt(urlParams.get('page')) || 1;
    const rank = urlParams.get('rank') || 'all';
    
    if (page !== 1 || rank !== 'all') {
        window.leaderboardManager.currentPage = page;
        window.leaderboardManager.currentRankFilter = rank;
        window.leaderboardManager.updateFilterButtons();
        window.leaderboardManager.updateRankItems();
    }
});

// Handle browser back/forward - Reload thay vì AJAX
window.addEventListener('popstate', () => {
    // Đơn giản reload trang để tránh trùng lặp
    window.location.reload();
});

// CSS animations
const style = document.createElement('style');
style.textContent = `
    .animate-in {
        animation: fadeInUp 0.6s ease forwards;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);