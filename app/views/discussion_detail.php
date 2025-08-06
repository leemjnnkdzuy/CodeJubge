<?php
require_once MODEL_PATH . '/DiscussionModel.php';
require_once ROOT_PATH . '/config/config.php';

date_default_timezone_set('Asia/Ho_Chi_Minh');

function getTimeAgo($datetime) {
    $time = time() - strtotime($datetime);
    
    if ($time < 60) return 'vừa xong';
    if ($time < 3600) return floor($time/60) . ' phút trước';
    if ($time < 86400) return floor($time/3600) . ' giờ trước';
    if ($time < 2592000) return floor($time/86400) . ' ngày trước';
    
    return date('d/m/Y H:i', strtotime($datetime));
}

function getCategoryInfo($category) {
    global $DISCUSS_CATEGORIES;
    
    $categoryKey = ucfirst(str_replace('-', '_', $category));
    
    if (isset($DISCUSS_CATEGORIES[$categoryKey])) {
        return [
            'name' => $DISCUSS_CATEGORIES[$categoryKey]['name'],
            'icon' => $DISCUSS_CATEGORIES[$categoryKey]['icon']
        ];
    }
    
    if (isset($DISCUSS_CATEGORIES['Other'])) {
        return [
            'name' => $DISCUSS_CATEGORIES['Other']['name'],
            'icon' => $DISCUSS_CATEGORIES['Other']['icon']
        ];
    }
    
    return [
        'name' => ucfirst($category),
        'icon' => 'bx-category'
    ];
}

function getCategoryDisplayName($category) {
    $categoryInfo = getCategoryInfo($category);
    return $categoryInfo['name'];
}

function getAvatarUrl($avatar) {
    if (!$avatar || empty($avatar)) {
        return '/assets/default_avatar.png';
    }
    
    if (strpos($avatar, 'data:image') === 0) {
        return $avatar;
    }
    
    if (strpos($avatar, 'http') === 0) {
        return $avatar;
    }
    
    return '/assets/' . $avatar;
}

$userId = $_SESSION['user_id'] ?? null;
$userRole = $_SESSION['role'] ?? 'user';

$additionalCSS = ['/css/discussionDetailStyle.css'];

$content = ob_start();
?>

<div class="discussion-detail-container">
    <a href="/discussions" class="back-button">
        <i class='bx bx-arrow-back'></i>
        Quay lại danh sách thảo luận
    </a>

    <div class="discussion-header">
        <div class="discussion-meta">
            <img src="<?= getAvatarUrl($discussion['avatar']) ?>" alt="<?= htmlspecialchars($discussion['username']) ?>" class="author-avatar">
            <div class="author-info">
                <h4><?= htmlspecialchars($discussion['first_name'] . ' ' . $discussion['last_name']) ?></h4>
                <div class="time-ago">@<?= htmlspecialchars($discussion['username']) ?> • <?= getTimeAgo($discussion['created_at']) ?></div>
            </div>
            <div class="discussion-badges">
                <?php if ($discussion['is_pinned']): ?>
                    <span class="badge pinned">Ghim</span>
                <?php endif; ?>
                <?php if ($discussion['is_solved']): ?>
                    <span class="badge solved">Đã giải quyết</span>
                <?php endif; ?>
                <?php 
                $categoryInfo = getCategoryInfo($discussion['category']);
                ?>
                <span class="badge category">
                    <i class='bx <?= $categoryInfo['icon'] ?>'></i>
                    <?= $categoryInfo['name'] ?>
                </span>
            </div>
        </div>

        <h1 class="discussion-title"><?= htmlspecialchars($discussion['title']) ?></h1>

        <?php if (!empty($discussion['tags'])): ?>
            <div class="discussion-tags">
                <?php foreach ($discussion['tags'] as $tag): ?>
                    <span class="tag"><?= htmlspecialchars($tag) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="discussion-content">
            <?= nl2br(htmlspecialchars($discussion['content'])) ?>
        </div>

        <div class="discussion-actions">
            <div class="discussion-stats">
                <div class="discussion-stat likes like-btn" data-discussion-id="<?= $discussion['id'] ?>">
                    <i class='bx bx-heart'></i>
                    <span class="likes-count"><?= $discussion['likes_count'] ?></span>
                </div>
                
                <div class="discussion-stat replies">
                    <i class='bx bx-message-dots'></i>
                    <span><?= count($replies) ?></span>
                </div>
            </div>
            
            <div class="discussion-action-buttons">
                <button class="action-btn bookmark-btn" data-discussion-id="<?= $discussion['id'] ?>">
                    <i class='bx bx-bookmark'></i>
                </button>

                <?php if ($userId && ($userId == $discussion['author_id'] || $userRole === 'admin' || $userRole === 'moderator')): ?>
                    <a href="/discussions/<?= $discussion['id'] ?>/edit" class="action-btn">
                        <i class='bx bx-edit'></i>
                    </a>
                <?php endif; ?>

                <button class="action-btn share-btn" onclick="shareDiscussion()">
                    <i class='bx bx-share'></i>
                </button>
            </div>
        </div>
    </div>

    <div class="replies-section">
        <div class="replies-header">
            <h2>Phản hồi (<?= count($replies) ?>)</h2>
            <?php if ($userId && !empty($replies)): ?>
                <button class="reply-btn" onclick="toggleReplyForm()">
                    Trả lời
                </button>
            <?php endif; ?>
        </div>

        <?php if ($userId): ?>
            <div class="reply-form" id="replyForm">
                <h3>Viết phản hồi của bạn</h3>
                <form id="replyFormSubmit">
                    <textarea class="reply-textarea" id="replyContent" placeholder="Nhập phản hồi của bạn..." required></textarea>
                    <div class="reply-form-actions">
                        <button type="button" class="btn-cancel" onclick="toggleReplyForm()">Hủy</button>
                        <button type="submit" class="btn-submit">Gửi phản hồi</button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="replies-list" id="repliesList">
            <?php if (!empty($replies)): ?>
                <?php foreach ($replies as $reply): ?>
                    <div class="reply-item <?= $reply['is_solution'] ? 'solution' : '' ?>" data-reply-id="<?= $reply['id'] ?>">
                        <div class="reply-header">
                            <img src="<?= getAvatarUrl($reply['avatar']) ?>" alt="<?= htmlspecialchars($reply['username']) ?>" class="reply-avatar">
                            <div class="reply-author-info">
                                <h5><?= htmlspecialchars($reply['first_name'] . ' ' . $reply['last_name']) ?></h5>
                                <div class="reply-time">@<?= htmlspecialchars($reply['username']) ?> • <?= getTimeAgo($reply['created_at']) ?></div>
                            </div>
                            
                            <?php if ($userId && ($userId == $reply['author_id'] || $userId == $discussion['author_id'] || $userRole === 'admin' || $userRole === 'moderator')): ?>
                                <div class="reply-actions-menu">
                                    <button class="reply-menu-btn" onclick="toggleReplyMenu(<?= $reply['id'] ?>)">
                                        <i class='bx bx-dots-horizontal-rounded'></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="reply-content">
                            <?= nl2br(htmlspecialchars($reply['content'])) ?>
                        </div>

                        <div class="reply-footer">
                            <div class="reply-stats">
                                <div class="reply-stat likes reply-like-btn <?= $reply['user_liked'] ? 'liked' : '' ?>" data-reply-id="<?= $reply['id'] ?>">
                                    <i class='bx <?= $reply['user_liked'] ? 'bxs-heart' : 'bx-heart' ?>'></i>
                                    <span class="reply-likes-count"><?= $reply['likes_count'] ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-replies">
                    <i class='bx bx-message-dots'></i>
                    <h3>Chưa có phản hồi nào</h3>
                    <p>Hãy là người đầu tiên tham gia thảo luận này!</p>
                    <?php if ($userId): ?>
                        <button class="reply-btn" onclick="toggleReplyForm()">
                            Viết phản hồi đầu tiên
                        </button>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
const DISCUSSION_ID = <?= $discussion['id'] ?>;
const USER_ID = <?= $userId ? $userId : 'null' ?>;

function toggleReplyForm() {
    const form = document.getElementById('replyForm');
    form.classList.toggle('active');
    
    if (form.classList.contains('active')) {
        document.getElementById('replyContent').focus();
    }
}

document.getElementById('replyFormSubmit')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!USER_ID) {
        alert('Bạn cần đăng nhập để trả lời!');
        return;
    }
    
    const content = document.getElementById('replyContent').value.trim();
    if (!content) {
        alert('Vui lòng nhập nội dung phản hồi!');
        return;
    }
    
    try {
        const submitBtn = this.querySelector('.btn-submit');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Đang gửi...';
        submitBtn.disabled = true;
        
        const response = await fetch('/api/discussions/replies', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                discussion_id: DISCUSSION_ID,
                content: content
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.location.reload();
        } else {
            alert(result.message || 'Có lỗi xảy ra khi gửi phản hồi!');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi gửi phản hồi!');
    } finally {
        const submitBtn = this.querySelector('.btn-submit');
        submitBtn.textContent = 'Gửi phản hồi';
        submitBtn.disabled = false;
    }
});

document.querySelector('.like-btn')?.addEventListener('click', async function() {
    if (!USER_ID) {
        alert('Bạn cần đăng nhập để thích bài viết!');
        return;
    }
    
    try {
        const response = await fetch('/api/discussions/like', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                discussion_id: DISCUSSION_ID
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const countSpan = this.querySelector('.likes-count');
            const icon = this.querySelector('i');
            
            countSpan.textContent = result.likes_count;
            
            if (result.action === 'liked') {
                this.classList.add('liked');
                icon.className = 'bx bxs-heart';
            } else {
                this.classList.remove('liked');
                icon.className = 'bx bx-heart';
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
});

document.querySelector('.bookmark-btn')?.addEventListener('click', async function() {
    if (!USER_ID) {
        alert('Bạn cần đăng nhập để lưu bài viết!');
        return;
    }
    
    try {
        const response = await fetch('/api/discussions/bookmark', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                discussion_id: DISCUSSION_ID
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            if (result.action === 'bookmarked') {
                this.classList.add('bookmarked');
                this.innerHTML = '<i class="bx bxs-bookmark"></i>';
            } else {
                this.classList.remove('bookmarked');
                this.innerHTML = '<i class="bx bx-bookmark"></i>';
            }
        }
    } catch (error) {
        console.error('Error:', error);
    }
});

document.querySelectorAll('.reply-like-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        if (!USER_ID) {
            alert('Bạn cần đăng nhập để thích phản hồi!');
            return;
        }
        
        const replyId = this.dataset.replyId;
        
        try {
            const response = await fetch('/api/discussions/reply-like', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    reply_id: replyId
                })
            });
            
            const result = await response.json();
            
            if (result.success) {
                const countSpan = this.querySelector('.reply-likes-count');
                const icon = this.querySelector('i');
                
                countSpan.textContent = result.likes_count;
                
                if (result.action === 'liked') {
                    this.classList.add('liked');
                    icon.className = 'bx bxs-heart';
                } else {
                    this.classList.remove('liked');
                    icon.className = 'bx bx-heart';
                }
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });
});

document.querySelectorAll('.mark-solution-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const replyId = this.dataset.replyId;
        
        if (confirm('Bạn có chắc chắn muốn đánh dấu phản hồi này là giải pháp?')) {
            try {
                const response = await fetch('/api/discussions/mark-solution', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        reply_id: replyId
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Có lỗi xảy ra!');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Có lỗi xảy ra!');
            }
        }
    });
});

function shareDiscussion() {
    if (navigator.share) {
        navigator.share({
            title: <?= json_encode($discussion['title']) ?>,
            text: 'Xem thảo luận này trên CodeJudge',
            url: window.location.href
        });
    } else {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('Đã sao chép liên kết vào clipboard!');
        }).catch(() => {
            const textArea = document.createElement('textarea');
            textArea.value = window.location.href;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('Đã sao chép liên kết vào clipboard!');
        });
    }
}

function toggleReplyMenu(replyId) {
    console.log('Toggle menu for reply:', replyId);
}

document.addEventListener('DOMContentLoaded', async function() {
    if (USER_ID) {
        try {
            const response = await fetch(`/api/discussions/${DISCUSSION_ID}/user-interactions`);
            const result = await response.json();
            
            if (result.success) {
                if (result.liked) {
                    const likeBtn = document.querySelector('.like-btn');
                    if (likeBtn) {
                        likeBtn.classList.add('liked');
                        const icon = likeBtn.querySelector('i');
                        if (icon) {
                            icon.className = 'bx bxs-heart';
                        }
                    }
                }
                if (result.bookmarked) {
                    const bookmarkBtn = document.querySelector('.bookmark-btn');
                    if (bookmarkBtn) {
                        bookmarkBtn.classList.add('bookmarked');
                        bookmarkBtn.innerHTML = '<i class="bx bxs-bookmark"></i>';
                    }
                }
            }
        } catch (error) {
            console.error('Error loading user interactions:', error);
        }
    }
});
</script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>
