<?php
$title = 'Thảo Luận - CodeJudge';
$description = 'Tham gia thảo luận về các bài toán lập trình và chia sẻ kiến thức';

require_once MODEL_PATH . '/DiscussionModel.php';
global $DISCUSS_CATEGORIES;

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
        <?php foreach ($DISCUSS_CATEGORIES as $key => $category): 
            $categoryKey = strtolower($key);
            $isActive = $filter === $categoryKey;
        ?>
            <button class="filter-btn <?= $isActive ? 'active' : '' ?>" data-filter="<?= $categoryKey ?>">
                <?= htmlspecialchars($category['name']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="discussions-list" id="discussionsList">
        <?php if (!empty($discussions)): ?>
            <?php 
            $renderedIds = [];
            foreach ($discussions as $discussion): 
                if (in_array($discussion['id'], $renderedIds)) {
                    continue;
                }
                $renderedIds[] = $discussion['id'];
                
                $timeAgo = getTimeAgo($discussion['created_at']);
                
                $categoryMap = [];
                foreach ($DISCUSS_CATEGORIES as $key => $category) {
                    $categoryMap[strtolower($key)] = $category['name'];
                }
                $categoryMap['general'] = 'Tổng Quát';
                $categoryMap['algorithm'] = 'Thuật Toán';
                $categoryMap['data-structure'] = 'Cấu Trúc Dữ Liệu';
                $categoryMap['math'] = 'Toán Học';
                $categoryMap['beginner'] = 'Người Mới';
                $categoryMap['contest'] = 'Cuộc Thi';
                $categoryMap['help'] = 'Trợ Giúp';
            ?>
                <div class="discussion-card <?= $discussion['is_pinned'] ? 'pinned' : '' ?> <?= $discussion['is_solved'] ? 'solved' : '' ?>" 
                     data-id="<?= $discussion['id'] ?>" data-discussion-url="/discussions/<?= $discussion['id'] ?>">
                    
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
                                <span class="discussion-time"><?= $timeAgo ?></span>
                            </div>
                        </div>
                        <div class="discussion-options">
                            <button class="discussion-menu-btn" onclick="event.stopPropagation(); toggleDiscussionMenu(<?= $discussion['id'] ?>)">
                                <i class="bx bx-dots-horizontal-rounded"></i>
                            </button>
                            <div class="discussion-dropdown" id="discussionMenu<?= $discussion['id'] ?>">
                                <button class="discussion-dropdown-item <?= ($userId && isset($discussion['is_bookmarked']) && $discussion['is_bookmarked']) ? 'bookmarked' : '' ?>" 
                                        data-discussion-id="<?= $discussion['id'] ?>"
                                        onclick="event.stopPropagation(); bookmarkDiscussion(<?= $discussion['id'] ?>)">
                                    <i class="bx <?= ($userId && isset($discussion['is_bookmarked']) && $discussion['is_bookmarked']) ? 'bxs-bookmark' : 'bx-bookmark' ?>"></i>
                                    <span><?= ($userId && isset($discussion['is_bookmarked']) && $discussion['is_bookmarked']) ? 'Đã lưu' : 'Lưu bài viết' ?></span>
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

<div id="createPostModal" class="discussions-modal">
    <div class="discussions-modal-content">
        <div class="discussions-modal-header">
            <h2>Tạo Bài Thảo Luận Mới</h2>
            <button type="button" class="discussions-modal-close" onclick="closeCreatePostModal()">
                <i class="bx bx-x"></i>
            </button>
        </div>
        
        <form id="createPostForm" class="discussions-form">
            <div class="discussions-form-group full-width">
                <label for="postTitle">Tiêu đề bài viết *</label>
                <input type="text" id="postTitle" name="title" required placeholder="Nhập tiêu đề bài viết của bạn...">
                <span class="discussions-form-note">Tiêu đề nên ngắn gọn và mô tả được nội dung bạn muốn thảo luận</span>
            </div>
            
            <div class="discussions-form-group full-width">
                <label for="postCategory">Danh mục *</label>
                <select id="postCategory" name="category" required>
                    <option value="">Chọn danh mục</option>
                    <?php foreach ($DISCUSS_CATEGORIES as $key => $category): ?>
                        <option value="<?= strtolower($key) ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="discussions-form-group full-width">
                <label for="postContent">Nội dung *</label>
                <textarea id="postContent" name="content" required placeholder="Viết nội dung bài thảo luận của bạn..." rows="8"></textarea>
                <span class="discussions-form-note">Mô tả chi tiết vấn đề, bao gồm mã nguồn nếu cần thiết</span>
            </div>
            
            <div class="discussions-form-group full-width">
                <label for="postTags">Thẻ (tùy chọn)</label>
                <input type="text" id="postTags" name="tags" placeholder="Nhập các tag, phân cách bằng dấu phẩy (ví dụ: javascript, algorithm, sorting)">
                <span class="discussions-form-note">Phân tách các tag bằng dấu phẩy. Tối đa 5 tags.</span>
            </div>
            
            <div class="discussions-form-actions">
                <button type="button" class="discussions-btn-cancel" onclick="closeCreatePostModal()">Hủy</button>
                <button type="submit" class="discussions-btn-submit">
                    <i class="bx bx-send"></i> Đăng Bài
                </button>
            </div>
        </form>
    </div>
</div>

<div id="editPostModal" class="discussions-modal">
    <div class="discussions-modal-content">
        <div class="discussions-modal-header">
            <h2><i class="bx bx-edit"></i> Chỉnh Sửa Bài Thảo Luận</h2>
            <button type="button" class="discussions-modal-close" onclick="closeEditPostModal()">
                <i class="bx bx-x"></i>
            </button>
        </div>
        
        <form id="editPostForm" class="discussions-form">
            <input type="hidden" id="editPostId" name="post_id" value="">
            
            <div class="discussions-form-group full-width">
                <label for="editPostTitle">Tiêu đề bài viết *</label>
                <input type="text" id="editPostTitle" name="title" required placeholder="Nhập tiêu đề bài viết của bạn...">
                <span class="discussions-form-note">Tiêu đề nên ngắn gọn và mô tả được nội dung bạn muốn thảo luận</span>
            </div>
            
            <div class="discussions-form-group full-width">
                <label for="editPostCategory">Danh mục *</label>
                <select id="editPostCategory" name="category" required>
                    <option value="">Chọn danh mục</option>
                    <?php foreach ($DISCUSS_CATEGORIES as $key => $category): ?>
                        <option value="<?= strtolower($key) ?>"><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="discussions-form-group full-width">
                <label for="editPostContent">Nội dung *</label>
                <textarea id="editPostContent" name="content" required placeholder="Viết nội dung bài thảo luận của bạn..." rows="8"></textarea>
                <span class="discussions-form-note">Mô tả chi tiết vấn đề, bao gồm mã nguồn nếu cần thiết</span>
            </div>
            
            <div class="discussions-form-group full-width">
                <label for="editPostTags">Thẻ (tùy chọn)</label>
                <input type="text" id="editPostTags" name="tags" placeholder="Nhập các tag, phân cách bằng dấu phẩy (ví dụ: javascript, algorithm, sorting)">
                <span class="discussions-form-note">Phân tách các tag bằng dấu phẩy. Tối đa 5 tags.</span>
            </div>
            
            <div class="discussions-form-actions">
                <button type="button" class="discussions-btn-cancel" onclick="closeEditPostModal()">Hủy</button>
                <button type="submit" class="discussions-btn-submit">
                    <i class="bx bx-save"></i> Cập Nhật
                </button>
            </div>
        </form>
    </div>
</div>

<div id="deleteConfirmModal" class="modal-confirm-delete">
    <div class="modal">
        <div class="modal-body">
            <p>Bạn có chắc chắn muốn xóa bài viết này không?</p>
            <div class="warning-text">
                <i class="bx bx-error"></i>
                <span>Hành động này không thể hoàn tác!</span>
            </div>
        </div>
        <div class="modal-actions">
            <button id="cancelDelete" class="confirm-delete-cancel-btn">
                Hủy bỏ
            </button>
            <button id="confirmDelete" class="confirm-delete-accept-btn">
                Xóa bài viết
            </button>
        </div>
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