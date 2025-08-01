<?php
$title = 'Thảo Luận - CodeJudge';
$description = 'Tham gia thảo luận về các bài toán lập trình và chia sẻ kiến thức';

require_once MODEL_PATH . '/DiscussionModel.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');

function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);

    if ($time < 60) return 'vừa xong';
    if ($time < 3600) return floor($time/60) . ' phút trước';
    if ($time < 86400) return floor($time/3600) . ' giờ trước';
    if ($time < 2592000) return floor($time/86400) . ' ngày trước';
    
    return date('d/m/Y', strtotime($datetime));
}

$discussionModel = new DiscussionModel();

$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = (int)($_GET['page'] ?? 1);
$limit = 10;

$discussions = $discussionModel->getDiscussions($page, $limit, $filter, $search);
$totalDiscussions = $discussionModel->countDiscussions($filter, $search);
$hasMore = count($discussions) >= $limit;

$userId = $_SESSION['user_id'] ?? null;
if ($userId) {
    foreach ($discussions as &$discussion) {
        $discussion['user_liked'] = $discussionModel->hasUserLiked($userId, $discussion['id']);
    }
}

$content = ob_start();
?>

<div class="discussions-container">
    <div class="discussions-header">
        <h1>Thảo Luận</h1>
        <p>Tham gia cộng đồng để thảo luận về các bài toán lập trình, chia sẻ kiến thức và học hỏi từ những lập trình viên khác</p>
    </div>

    <div class="discussions-controls">
        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="Tìm kiếm thảo luận..." value="<?= htmlspecialchars($search) ?>">
            <button id="searchBtn" class="search-btn">
                <i class='bx bx-search'></i>
            </button>
        </div>
        <button class="new-post-btn" onclick="createNewPost()">
            <i class='bx bx-plus'></i>
            Tạo Bài Mới
        </button>
    </div>

    <div class="discussions-filters">
        <button class="filter-btn <?= $filter === 'all' ? 'active' : '' ?>" data-filter="all">Tất Cả</button>
        <button class="filter-btn <?= $filter === 'pinned' ? 'active' : '' ?>" data-filter="pinned">Ghim</button>
        <button class="filter-btn <?= $filter === 'solved' ? 'active' : '' ?>" data-filter="solved">Đã Giải</button>
        <button class="filter-btn <?= $filter === 'unsolved' ? 'active' : '' ?>" data-filter="unsolved">Chưa Giải</button>
        <button class="filter-btn <?= $filter === 'algorithm' ? 'active' : '' ?>" data-filter="algorithm">Thuật Toán</button>
        <button class="filter-btn <?= $filter === 'data-structure' ? 'active' : '' ?>" data-filter="data-structure">Cấu Trúc Dữ Liệu</button>
        <button class="filter-btn <?= $filter === 'math' ? 'active' : '' ?>" data-filter="math">Toán Học</button>
        <button class="filter-btn <?= $filter === 'beginner' ? 'active' : '' ?>" data-filter="beginner">Người Mới</button>
    </div>

    <div class="discussions-list" id="discussionsList">
        <?php if (!empty($discussions)): ?>
            <?php foreach ($discussions as $discussion): ?>
                <?php
                $timeAgo = getTimeAgo($discussion['created_at']);
                $categoryMap = [
                    'general' => 'Tổng Quát',
                    'algorithm' => 'Thuật Toán',
                    'data-structure' => 'Cấu Trúc Dữ Liệu',
                    'math' => 'Toán Học',
                    'beginner' => 'Người Mới',
                    'contest' => 'Cuộc Thi',
                    'help' => 'Trợ Giúp'
                ];
                ?>
                <div class="discussion-card <?= $discussion['is_pinned'] ? 'pinned' : '' ?> <?= $discussion['is_solved'] ? 'solved' : '' ?>" 
                     data-id="<?= $discussion['id'] ?>" onclick="openDiscussion(<?= $discussion['id'] ?>)">
                    
                    <?php if ($discussion['is_pinned']): ?>
                        <div class="pinned-indicator">
                            <i class="bx bx-pin"></i> Bài viết được ghim
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($discussion['is_solved']): ?>
                        <div class="solved-indicator">
                            <i class="bx bx-check-circle"></i> Đã được giải quyết
                        </div>
                    <?php endif; ?>
                    
                    <div class="discussion-header">
                        <img src="<?= !empty($discussion['avatar']) ? htmlspecialchars($discussion['avatar']) : '/assets/default-avatar.png' ?>" 
                             alt="<?= htmlspecialchars($discussion['username']) ?>" class="discussion-avatar">
                        <div class="discussion-meta">
                            <h3 class="discussion-title"><?= htmlspecialchars($discussion['title']) ?></h3>
                            <div class="discussion-author">
                                <span><?= htmlspecialchars($discussion['first_name'] . ' ' . $discussion['last_name']) ?></span>
                                <div class="discussion-badges">
                                    <?php if (!empty($discussion['badges'])): ?>
                                        <?php foreach ($discussion['badges'] as $badge): ?>
                                            <span class="author-badge"><?= htmlspecialchars($badge) ?></span>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                                <span class="discussion-time"><?= $timeAgo ?></span>
                            </div>
                        </div>
                        <div class="discussion-options">
                            <button class="discussion-menu-btn" onclick="event.stopPropagation(); toggleDiscussionMenu(<?= $discussion['id'] ?>)">
                                <i class="bx bx-dots-horizontal-rounded"></i>
                            </button>
                            <div class="discussion-dropdown" id="discussionMenu<?= $discussion['id'] ?>">
                                <button class="discussion-dropdown-item" onclick="event.stopPropagation(); bookmarkDiscussion(<?= $discussion['id'] ?>)">
                                    <i class="bx bx-bookmark"></i>
                                    <span>Lưu bài viết</span>
                                </button>
                                <?php if ($userId && $discussion['author_id'] == $userId): ?>
                                    <button class="discussion-dropdown-item edit" onclick="event.stopPropagation(); editDiscussion(<?= $discussion['id'] ?>)">
                                        <i class="bx bx-edit"></i>
                                        <span>Chỉnh sửa</span>
                                    </button>
                                    <button class="discussion-dropdown-item delete" onclick="event.stopPropagation(); deleteDiscussion(<?= $discussion['id'] ?>)">
                                        <i class="bx bx-trash"></i>
                                        <span>Xóa bài viết</span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="discussion-content">
                        <div class="discussion-text">
                            <?= htmlspecialchars(substr($discussion['content'], 0, 200)) ?><?= strlen($discussion['content']) > 200 ? '...' : '' ?>
                        </div>
                        
                        <div class="discussion-tags">
                            <span class="discussion-tag <?= $discussion['category'] ?>"><?= $categoryMap[$discussion['category']] ?? $discussion['category'] ?></span>
                            <?php if (!empty($discussion['tags'])): ?>
                                <?php foreach (array_slice($discussion['tags'], 0, 3) as $tag): ?>
                                    <span class="discussion-tag"><?= htmlspecialchars($tag) ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="discussion-footer">
                        <div class="discussion-stats">
                            <div class="discussion-stat likes <?= $discussion['user_liked'] ? 'liked' : '' ?>" 
                                 data-discussion-id="<?= $discussion['id'] ?>"
                                 title="<?= $discussion['user_liked'] ? 'Bỏ thích' : 'Thích' ?>">
                                <i class="bx <?= $discussion['user_liked'] ? 'bxs-heart' : 'bx-heart' ?>"></i>
                                <span><?= $discussion['likes_count'] ?></span>
                            </div>
                            <div class="discussion-stat replies">
                                <i class="bx bx-message-rounded"></i>
                                <span><?= $discussion['replies_count'] ?></span>
                            </div>
                        </div>
                        <div class="discussion-actions">
                            <button class="action-btn" onclick="event.stopPropagation(); shareDiscussion(<?= $discussion['id'] ?>)" title="Chia sẻ">
                                <i class="bx bx-share"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="bx bx-message-square-dots"></i>
                <h3>Không tìm thấy thảo luận nào</h3>
                <p>Hãy thử thay đổi bộ lọc hoặc tạo một thảo luận mới</p>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="loading-trigger" id="loadingTrigger" style="height: 1px;"></div>
</div>

<div id="createPostModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="bx bx-plus-circle"></i> Tạo Bài Thảo Luận Mới</h2>
            <button type="button" class="modal-close" onclick="closeCreatePostModal()">&times;</button>
        </div>
        <form id="createPostForm" class="create-post-form">
            <div class="form-section full-width">
                <div class="form-group">
                    <label for="postTitle">Tiêu đề bài viết *</label>
                    <input type="text" id="postTitle" name="title" required placeholder="Nhập tiêu đề bài viết của bạn...">
                </div>
            </div>
            
            <div class="form-section full-width">
                <div class="form-group">
                    <label for="postCategory">Danh mục *</label>
                    <select id="postCategory" name="category" required>
                        <option value="">Chọn danh mục</option>
                        <option value="general">Tổng Quát</option>
                        <option value="algorithm">Thuật Toán</option>
                        <option value="data-structure">Cấu Trúc Dữ Liệu</option>
                        <option value="math">Toán Học</option>
                        <option value="beginner">Người Mới</option>
                        <option value="contest">Cuộc Thi</option>
                        <option value="help">Trợ Giúp</option>
                    </select>
                </div>
            </div>
            
            <div class="form-section full-width">
                <div class="form-group">
                    <label for="postContent">Nội dung *</label>
                    <textarea id="postContent" name="content" required placeholder="Viết nội dung bài thảo luận của bạn..." rows="8"></textarea>
                </div>
            </div>
            
            <div class="form-section full-width">
                <div class="form-group">
                    <label for="postTags">Tags (tùy chọn)</label>
                    <input type="text" id="postTags" name="tags" placeholder="Nhập các tag, phân cách bằng dấu phẩy (ví dụ: javascript, algorithm, sorting)">
                    <small class="form-note">Phân tách các tag bằng dấu phẩy. Tối đa 5 tags.</small>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeCreatePostModal()">Hủy</button>
                <button type="submit" class="btn-primary">
                    <i class="bx bx-send"></i> Đăng Bài
                </button>
            </div>
        </form>
    </div>
</div>

<script>
window.discussionsData = {
    currentPage: <?= $page ?>,
    hasMore: <?= $hasMore ? 'true' : 'false' ?>,
    totalDiscussions: <?= $totalDiscussions ?>,
    currentFilter: '<?= htmlspecialchars($filter) ?>',
    currentSearch: '<?= htmlspecialchars($search) ?>'
};

window.currentUserId = <?= $userId ? $userId : 'null' ?>;
</script>

<script src="/js/discussions.js"></script>
<script src="/js/sticky-controls.js"></script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>