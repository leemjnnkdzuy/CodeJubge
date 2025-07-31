<?php 
$viewingUser = isset($profileUser) ? $profileUser : $currentUser;
$isOwnProfile = isset($isOwnProfile) ? $isOwnProfile : true;

if (!$viewingUser) {
    header('Location: /login');
    exit;
}

$content = ob_start(); 
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar-section">
            <div class="avatar-container">
                <img src="<?= AvatarHelper::base64ToImageSrc($viewingUser['avatar']) ?>" 
                     alt="Avatar" class="profile-avatar">
                <?php if ($isOwnProfile): ?>
                <button class="avatar-edit-btn" onclick="openAvatarModal()">
                    <i class='bx bx-camera'></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-info">
            <h1 class="profile-name">
                <?= htmlspecialchars($viewingUser['first_name'] . ' ' . $viewingUser['last_name']) ?>
            </h1>
            <p class="profile-username">@<?= htmlspecialchars($viewingUser['username']) ?></p>
            
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-number"><?= isset($viewingUser['rating']) && $viewingUser['rating'] != -1 ? $viewingUser['rating'] : 'Chưa có xếp hạng' ?></span>
                    <span class="stat-label">Rating</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $viewingUser['total_problems_solved'] ?? 0 ?></span>
                    <span class="stat-label">Bài giải</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= $viewingUser['login_streak'] ?? 0 ?></span>
                    <span class="stat-label">Streak</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number"><?= count($userBadges) ?></span>
                    <span class="stat-label">Badges</span>
                </div>
            </div>
            
            <?php if ($isOwnProfile): ?>
            <button class="edit-profile-btn" onclick="openEditModal()">
                <i class='bx bx-edit'></i>
                Chỉnh sửa profile
            </button>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="profile-content">
        <div class="profile-section">
            <h2 class="section-title">Giới thiệu</h2>
            <div class="bio-content">
                <?php if (!empty($viewingUser['bio'])): ?>
                    <p><?= nl2br(htmlspecialchars($viewingUser['bio'])) ?></p>
                <?php else: ?>
                    <p class="no-content">
                        <?= $isOwnProfile ? 'Bạn chưa có giới thiệu. Hãy thêm vào!' : 'Người dùng này chưa có giới thiệu.' ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($viewingUser['github_url']) || !empty($viewingUser['linkedin_url']) || !empty($viewingUser['website_url'])): ?>
        <div class="profile-section">
            <h2 class="section-title">Liên kết</h2>
            <div class="links-container">
                <?php if (!empty($viewingUser['github_url'])): ?>
                <a href="<?= htmlspecialchars($viewingUser['github_url']) ?>" target="_blank" class="link-item">
                    <i class='bx bxl-github'></i>
                    <span>GitHub</span>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($viewingUser['linkedin_url'])): ?>
                <a href="<?= htmlspecialchars($viewingUser['linkedin_url']) ?>" target="_blank" class="link-item">
                    <i class='bx bxl-linkedin'></i>
                    <span>LinkedIn</span>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($viewingUser['website_url'])): ?>
                <a href="<?= htmlspecialchars($viewingUser['website_url']) ?>" target="_blank" class="link-item">
                    <i class='bx bx-link'></i>
                    <span>Website</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="profile-section">
            <h2 class="section-title">Huy hiệu & Thành tích</h2>
            <div class="badges-grid">
                <?php 
                global $BADGES;
                $earnedCount = 0;
                
                foreach ($BADGES as $badgeKey => $badge): 
                    $isEarned = in_array($badgeKey, $userBadges);
                    if ($isEarned) $earnedCount++;
                    $badgeClass = $isEarned ? 'earned' : 'unearned';
                    $assetPath = '/assets/' . $badge['File'];
                ?>
                <div class="badge-item <?= $badgeClass ?>" title="<?= htmlspecialchars($badge['description']) ?>">
                    <img src="<?= $assetPath ?>" alt="<?= htmlspecialchars($badge['title']) ?>" class="badge-icon">
                    <span class="badge-title"><?= htmlspecialchars($badge['title']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($earnedCount === 0): ?>
            <p class="no-content">
                <?= $isOwnProfile ? 'Bạn chưa có huy hiệu nào. Hãy hoàn thành các thử thách!' : 'Người dùng này chưa có huy hiệu nào.' ?>
            </p>
            <?php endif; ?>
        </div>
        
        <div class="profile-section">
            <h2 class="section-title">Hoạt động gần đây</h2>
            <div class="activity-timeline">
                <p class="no-content">Tính năng này sẽ được phát triển trong tương lai.</p>
            </div>
        </div>
    </div>
</div>

<?php if ($isOwnProfile): ?>
<div class="modal" id="editProfileModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Chỉnh sửa thông tin cá nhân</h2>
            <button class="modal-close" onclick="closeEditModal()">&times;</button>
        </div>
        
        <form class="edit-form" action="/profile/update" method="POST" enctype="multipart/form-data" onsubmit="debugFormSubmit(this)">
            <div class="form-group">
                <label for="first_name">Tên</label>
                <input type="text" id="first_name" name="first_name" 
                       value="<?= htmlspecialchars($viewingUser['first_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="last_name">Họ</label>
                <input type="text" id="last_name" name="last_name" 
                       value="<?= htmlspecialchars($viewingUser['last_name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" 
                       value="<?= htmlspecialchars($viewingUser['username']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($viewingUser['email']) ?>" required>
            </div>
            
            <div class="form-group full-width">
                <label for="bio">Giới thiệu</label>
                <textarea id="bio" name="bio" rows="3" placeholder="Viết vài dòng về bản thân..."><?= htmlspecialchars($viewingUser['bio'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="github_url">GitHub URL</label>
                <input type="url" id="github_url" name="github_url" 
                       value="<?= htmlspecialchars($viewingUser['github_url'] ?? '') ?>" 
                       placeholder="https://github.com/username">
            </div>
            
            <div class="form-group">
                <label for="linkedin_url">LinkedIn URL</label>
                <input type="url" id="linkedin_url" name="linkedin_url" 
                       value="<?= htmlspecialchars($viewingUser['linkedin_url'] ?? '') ?>" 
                       placeholder="https://linkedin.com/in/username">
            </div>
            
            <div class="form-group full-width">
                <label for="website_url">Website URL</label>
                <input type="url" id="website_url" name="website_url" 
                       value="<?= htmlspecialchars($viewingUser['website_url'] ?? '') ?>" 
                       placeholder="https://yourwebsite.com">
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeEditModal()">Hủy</button>
                <button type="submit" class="btn-primary">Lưu thay đổi</button>
            </div>
        </form>
    </div>
</div>

<div class="modal" id="avatarModal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Thay đổi ảnh đại diện</h2>
            <button class="modal-close" onclick="closeAvatarModal()">&times;</button>
        </div>
        
        <form class="avatar-form" action="/profile/update" method="POST" enctype="multipart/form-data" onsubmit="return validateAvatarForm(this)">
            <div class="avatar-preview">
                <img src="<?= AvatarHelper::base64ToImageSrc($viewingUser['avatar']) ?>" 
                     alt="Preview" id="avatarPreview" class="preview-image">
            </div>
            
            <div class="form-group">
                <label for="avatar" class="file-upload-label">
                    <i class='bx bx-upload'></i>
                    Chọn ảnh mới
                </label>
                <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" onchange="previewAvatar(this)" required>
                <small class="file-info">Chấp nhận: JPG, PNG, GIF, WebP. Tối đa 2MB.</small>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeAvatarModal()">Hủy</button>
                <button type="submit" class="btn-primary">Cập nhật ảnh</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function openEditModal() {
    document.getElementById('editProfileModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editProfileModal').style.display = 'none';
}

function openAvatarModal() {
    document.getElementById('avatarModal').style.display = 'flex';
}

function closeAvatarModal() {
    document.getElementById('avatarModal').style.display = 'none';
}

function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('error', 'Chỉ chấp nhận file ảnh với định dạng JPG, PNG, GIF, WebP!');
            input.value = '';
            return;
        }
        
        const maxSize = 2048000;
        if (file.size > maxSize) {
            showNotification('error', 'File ảnh quá lớn! Vui lòng chọn file nhỏ hơn 2MB.');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatarPreview').src = e.target.result;
            showNotification('success', 'Ảnh hợp lệ! Bạn có thể cập nhật ảnh đại diện.');
        };
        reader.readAsDataURL(file);
    }
}

function debugFormSubmit(form) {
    console.log('Form being submitted:');
    const formData = new FormData(form);
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    showNotification('info', 'Đang cập nhật thông tin...');
    return true;
}

function validateAvatarForm(form) {
    const fileInput = form.querySelector('#avatar');
    
    if (!fileInput.files || !fileInput.files[0]) {
        showNotification('error', 'Vui lòng chọn một file ảnh!');
        return false;
    }
    
    const file = fileInput.files[0];
    
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        showNotification('error', 'Định dạng file không được hỗ trợ! Chỉ chấp nhận JPG, PNG, GIF, WebP.');
        return false;
    }
    
    const maxSize = 2048000;
    if (file.size > maxSize) {
        showNotification('error', 'File quá lớn! Vui lòng chọn file nhỏ hơn 2MB.');
        return false;
    }
    
    showNotification('info', 'Đang tải ảnh lên...');
    return true;
}

function showNotification(type, message) {
    const existing = document.getElementById('popupNotification');
    if (existing) {
        existing.remove();
    }
    
    const notification = document.createElement('div');
    notification.id = 'popupNotification';
    notification.className = `popup-notification ${type}`;
    
    let icon = '';
    switch(type) {
        case 'success':
            icon = '<i class="bx bx-check-circle"></i>';
            break;
        case 'error':
            icon = '<i class="bx bx-error-circle"></i>';
            break;
        case 'warning':
            icon = '<i class="bx bx-error"></i>';
            break;
        default:
            icon = '<i class="bx bx-info-circle"></i>';
    }
    
    notification.innerHTML = `
        <div class="notification-content">
            <div class="notification-icon">
                ${icon}
            </div>
            <div class="notification-message">
                ${message}
            </div>
            <button class="notification-close" onclick="closeNotification()">
                <i class='bx bx-x'></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    if (type !== 'info') {
        setTimeout(() => {
            closeNotification();
        }, 5000);
    }
}

function closeNotification() {
    const notification = document.getElementById('popupNotification');
    if (notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});
</script>

<style>

</style>

<?php 
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>
