<?php
$isLoggedIn = isset($_SESSION['user_id']);
$user = $isLoggedIn ? $_SESSION['user'] : null;

$title = 'Contests - CodeJudge';
$description = 'Tham gia các cuộc thi lập trình và thử thách bản thân với những bài toán thú vị';

ob_start();
?>

<div class="contests-header">
    <div class="contests-header-left">
        <h1>Contests</h1>
        <p>Tham gia các cuộc thi lập trình và thử thách bản thân với những bài toán thú vị</p>
    </div>
    <div class="contests-header-right">
        <?php if ($isLoggedIn): ?>
            <button class="btn btn-primary" id="createContestBtn">
                <i class="fas fa-plus"></i> Tạo Contest
            </button>
        <?php endif; ?>
        <a href="/contests/calendar" class="btn btn-secondary">
            <i class="fas fa-calendar"></i> Lịch Contest
        </a>
    </div>
</div>

<div class="contests-tabs">
    <button class="tab-btn active" data-status="all">
        <i class="fas fa-list"></i> Tất cả
    </button>
    <button class="tab-btn" data-status="upcoming">
        <i class="fas fa-clock"></i> Sắp diễn ra
    </button>
    <button class="tab-btn" data-status="live">
        <i class="fas fa-play-circle"></i> Đang diễn ra
    </button>
    <button class="tab-btn" data-status="finished">
        <i class="fas fa-flag-checkered"></i> Đã kết thúc
    </button>
    <?php if ($isLoggedIn): ?>
    <button class="tab-btn" data-status="joined">
        <i class="fas fa-user-check"></i> Đã tham gia
    </button>
    <button class="tab-btn" data-status="created">
        <i class="fas fa-user-edit"></i> Tôi tạo
    </button>
    <?php endif; ?>
</div>

<div class="contests-filters">
    <div class="search-container">
        <input type="text" class="search-input" placeholder="Tìm kiếm contest...">
        <i class="fas fa-search search-icon"></i>
    </div>
    <div class="filter-dropdown">
        <div class="dropdown">
            <select name="difficulty" id="difficultyFilter">
                <option value="">Tất cả độ khó</option>
                <option value="easy">Dễ</option>
                <option value="medium">Trung bình</option>
                <option value="hard">Khó</option>
            </select>
        </div>
        <div class="dropdown">
            <select name="duration" id="durationFilter">
                <option value="">Thời gian</option>
                <option value="short">< 2 giờ</option>
                <option value="medium">2-5 giờ</option>
                <option value="long">> 5 giờ</option>
            </select>
        </div>
        <div class="dropdown">
            <select name="sort" id="sortFilter">
                <option value="start_time_desc">Mới nhất</option>
                <option value="start_time_asc">Cũ nhất</option>
                <option value="participants_desc">Nhiều người tham gia</option>
                <option value="title_asc">Tên A-Z</option>
            </select>
        </div>
    </div>
</div>


<div class="contests-empty" style="display:none">
    <i class="fas fa-trophy"></i>
    <h3>Chưa có contest nào</h3>
    <p>Hiện tại chưa có contest nào. Hãy tạo contest đầu tiên!</p>
    <button class="btn-create-contests" id="createFirstContestBtn">
        <i class="fas fa-plus"></i> Tạo Contest Đầu Tiên
    </button>
</div>
<div class="contests-grid">
    <div class="text-center">
        <i class="fas fa-spinner fa-spin fa-2x"></i>
        <p>Đang tải contests...</p>
    </div>
</div>

<div class="pagination" id="contestsPagination" style="display: none;">
</div>

<?php if ($isLoggedIn): ?>
<div id="createContestModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-plus"></i> Tạo Contest Mới</h3>
            <button class="close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="createContestForm">
                <div class="form-group">
                    <label for="contestTitle" class="form-label">
                        <i class="fas fa-heading"></i> Tên Contest *
                    </label>
                    <input type="text" 
                           id="contestTitle" 
                           name="title" 
                           class="form-input" 
                           placeholder="Nhập tên contest..." 
                           required
                           maxlength="255">
                </div>
                
                <div class="form-group">
                    <label for="contestDescription" class="form-label">
                        <i class="fas fa-align-left"></i> Mô tả Contest
                    </label>
                    <textarea id="contestDescription" 
                              name="description" 
                              class="form-textarea" 
                              placeholder="Mô tả về contest này..."
                              rows="4"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="contestStartTime" class="form-label">
                            <i class="fas fa-calendar-plus"></i> Thời gian bắt đầu *
                        </label>
                        <input type="datetime-local" 
                               id="contestStartTime" 
                               name="start_time" 
                               class="form-input" 
                               required>
                    </div>
                    
                    <div class="form-group">
                        <label for="contestEndTime" class="form-label">
                            <i class="fas fa-calendar-minus"></i> Thời gian kết thúc *
                        </label>
                        <input type="datetime-local" 
                               id="contestEndTime" 
                               name="end_time" 
                               class="form-input" 
                               required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="contestDifficulty" class="form-label">
                            <i class="fas fa-layer-group"></i> Độ khó
                        </label>
                        <select id="contestDifficulty" name="difficulty" class="form-select">
                            <option value="easy">Dễ</option>
                            <option value="medium" selected>Trung bình</option>
                            <option value="hard">Khó</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="contestType" class="form-label">
                            <i class="fas fa-cog"></i> Loại Contest
                        </label>
                        <select id="contestType" name="type" class="form-select">
                            <option value="public" selected>Công khai</option>
                            <option value="private">Riêng tư</option>
                            <option value="invite_only">Chỉ mời</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contestRules" class="form-label">
                        <i class="fas fa-gavel"></i> Quy tắc Contest
                    </label>
                    <textarea id="contestRules" 
                              name="rules" 
                              class="form-textarea" 
                              placeholder="Quy tắc và hướng dẫn cho contest..."
                              rows="3"></textarea>
                </div>
                
                <div class="modal-footer" style="margin-top: 2rem; display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('createContestModal').style.display='none'">
                        <i class="fas fa-times"></i> Hủy
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Tạo Contest
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
    .contests-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: var(--spacing-lg, 1.5rem);
        min-height: 91vh;
    }

    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        min-width: 300px;
        max-width: 500px;
        padding: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        z-index: 9999;
        border-left: 4px solid;
        animation: slideIn 0.3s ease-out;
    }
    
    .notification-success { border-left-color: #28a745; }
    .notification-error { border-left-color: #dc3545; }
    .notification-warning { border-left-color: #ffc107; }
    .notification-info { border-left-color: #17a2b8; }
    
    .notification-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
    }
    
    .notification-message {
        flex: 1;
        margin-right: 1rem;
    }
    
    .notification-close {
        background: none;
        border: none;
        font-size: 1.2rem;
        cursor: pointer;
        color: #6c757d;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
    }
    
    .notification-close:hover {
        background: #f8f9fa;
        color: #495057;
    }
    
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    /* Loading and Empty States */
    .text-center {
        text-align: center;
        padding: 2rem;
        color: #6c757d;
    }
    
    .text-danger {
        color: #dc3545 !important;
    }
    
    .mb-3 {
        margin-bottom: 1rem;
    }
</style>

<script src="/js/contests.js"></script>

<script>
    // Set minimum datetime to current time for contest creation
    document.addEventListener('DOMContentLoaded', function() {
        const now = new Date();
        const minDateTime = now.toISOString().slice(0, 16);
        
        const startTimeInput = document.getElementById('contestStartTime');
        const endTimeInput = document.getElementById('contestEndTime');
        
        if (startTimeInput) {
            startTimeInput.min = minDateTime;
            
            // Set default start time to 1 hour from now
            const defaultStart = new Date(now.getTime() + 60 * 60 * 1000);
            startTimeInput.value = defaultStart.toISOString().slice(0, 16);
            
            // Update end time minimum when start time changes
            startTimeInput.addEventListener('change', function() {
                const startTime = new Date(this.value);
                const minEndTime = new Date(startTime.getTime() + 30 * 60 * 1000); // 30 minutes minimum duration
                endTimeInput.min = minEndTime.toISOString().slice(0, 16);
                
                // Set default end time to 2 hours after start
                if (!endTimeInput.value || new Date(endTimeInput.value) <= startTime) {
                    const defaultEnd = new Date(startTime.getTime() + 2 * 60 * 60 * 1000);
                    endTimeInput.value = defaultEnd.toISOString().slice(0, 16);
                }
            });
            
            // Trigger change event to set initial end time
            startTimeInput.dispatchEvent(new Event('change'));
        }
    });
</script>

<?php
$content = ob_get_clean();

// Use the pagesWithSidebar layout
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>