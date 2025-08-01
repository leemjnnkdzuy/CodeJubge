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
        <div class="profile-main-info">
            <div class="avatar-container">
                <img src="<?= AvatarHelper::base64ToImageSrc($viewingUser['avatar']) ?>" 
                     alt="Avatar" class="profile-avatar">
            </div>
            
            <div class="profile-info">
                <h1 class="profile-name">
                    <?= htmlspecialchars($viewingUser['first_name'] . ' ' . $viewingUser['last_name']) ?>
                </h1>
                <p class="profile-username">@<?= htmlspecialchars($viewingUser['username']) ?></p>
                
                <?php if ($isOwnProfile): ?>
                <button class="edit-profile-btn" onclick="openEditModal()">
                    <i class='bx bx-edit'></i>
                    Chỉnh sửa profile
                </button>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="rank-display">
            <?php 
            $userRank = getUserRank($viewingUser['rating'] ?? -1);
            ?>
            <div class="rank-icon">
                <img src="/assets/<?= $userRank['icon'] ?>" alt="<?= $userRank['name'] ?>" class="rank-image">
            </div>
            <div class="rank-info">
                <span class="rank-name"><?= $userRank['name'] ?></span>
                <span class="rank-rating"><?= isset($viewingUser['rating']) && $viewingUser['rating'] != -1 ? $viewingUser['rating'] . ' CJP' : 'Chưa có xếp hạng' ?></span>
            </div>
        </div>
        
        <div class="profile-stats">
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
        
        <?php if (!empty($viewingUser['github_url']) || !empty($viewingUser['linkedin_url']) || !empty($viewingUser['website_url']) || !empty($viewingUser['youtube_url']) || !empty($viewingUser['facebook_url']) || !empty($viewingUser['instagram_url'])): ?>
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
                
                <?php if (!empty($viewingUser['youtube_url'])): ?>
                <a href="<?= htmlspecialchars($viewingUser['youtube_url']) ?>" target="_blank" class="link-item">
                    <i class='bx bxl-youtube'></i>
                    <span>YouTube</span>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($viewingUser['facebook_url'])): ?>
                <a href="<?= htmlspecialchars($viewingUser['facebook_url']) ?>" target="_blank" class="link-item">
                    <i class='bx bxl-facebook'></i>
                    <span>Facebook</span>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($viewingUser['instagram_url'])): ?>
                <a href="<?= htmlspecialchars($viewingUser['instagram_url']) ?>" target="_blank" class="link-item">
                    <i class='bx bxl-instagram'></i>
                    <span>Instagram</span>
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
        
        <form class="edit-form" action="/profile/update" method="POST" enctype="multipart/form-data" onsubmit="return submitForm(this)">
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
                       value="<?= htmlspecialchars($viewingUser['username']) ?>" 
                       disabled readonly 
                       style="background-color: #f5f5f5; cursor: not-allowed;">
                <small class="form-note">Username không thể thay đổi</small>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" 
                       value="<?= htmlspecialchars($viewingUser['email']) ?>" 
                       disabled readonly
                       style="background-color: #f5f5f5; cursor: not-allowed;">
                <small class="form-note">Email không thể thay đổi</small>
            </div>
            
            <div class="form-group full-width">
                <label for="bio">Giới thiệu</label>
                <textarea id="bio" name="bio" rows="3" placeholder="Viết vài dòng về bản thân..."><?= htmlspecialchars($viewingUser['bio'] ?? '') ?></textarea>
            </div>
            
            <div class="form-section full-width">
                <h3 class="section-subtitle">Liên kết mạng xã hội</h3>
                <div class="social-links-grid">
                    <div class="form-group">
                        <label for="github_url">
                            <i class='bx bxl-github'></i>
                            GitHub URL
                        </label>
                        <input type="url" id="github_url" name="github_url" 
                               value="<?= htmlspecialchars($viewingUser['github_url'] ?? '') ?>" 
                               placeholder="https://github.com/username">
                    </div>
                    
                    <div class="form-group">
                        <label for="linkedin_url">
                            <i class='bx bxl-linkedin'></i>
                            LinkedIn URL
                        </label>
                        <input type="url" id="linkedin_url" name="linkedin_url" 
                               value="<?= htmlspecialchars($viewingUser['linkedin_url'] ?? '') ?>" 
                               placeholder="https://linkedin.com/in/username">
                    </div>
                    
                    <div class="form-group">
                        <label for="website_url">
                            <i class='bx bx-link'></i>
                            Website URL
                        </label>
                        <input type="url" id="website_url" name="website_url" 
                               value="<?= htmlspecialchars($viewingUser['website_url'] ?? '') ?>" 
                               placeholder="https://yourwebsite.com">
                    </div>
                    
                    <div class="form-group">
                        <label for="youtube_url">
                            <i class='bx bxl-youtube'></i>
                            YouTube URL
                        </label>
                        <input type="url" id="youtube_url" name="youtube_url" 
                               value="<?= htmlspecialchars($viewingUser['youtube_url'] ?? '') ?>" 
                               placeholder="https://youtube.com/channel/your-channel">
                    </div>
                    
                    <div class="form-group">
                        <label for="facebook_url">
                            <i class='bx bxl-facebook'></i>
                            Facebook URL
                        </label>
                        <input type="url" id="facebook_url" name="facebook_url" 
                               value="<?= htmlspecialchars($viewingUser['facebook_url'] ?? '') ?>" 
                               placeholder="https://facebook.com/your-profile">
                    </div>
                    
                    <div class="form-group">
                        <label for="instagram_url">
                            <i class='bx bxl-instagram'></i>
                            Instagram URL
                        </label>
                        <input type="url" id="instagram_url" name="instagram_url" 
                               value="<?= htmlspecialchars($viewingUser['instagram_url'] ?? '') ?>" 
                               placeholder="https://instagram.com/your-username">
                    </div>
                </div>
            </div>
            
            <div class="form-group full-width">
                <label>Ảnh đại diện</label>
                <div class="avatar-edit-section">
                    <div class="current-avatar">
                        <img src="<?= AvatarHelper::base64ToImageSrc($viewingUser['avatar']) ?>" 
                             alt="Current Avatar" id="currentAvatarPreview" class="current-avatar-image">
                    </div>
                    <div class="avatar-upload">
                        <label for="avatar" class="file-upload-label">
                            <i class='bx bx-camera'></i>
                            Thay đổi ảnh đại diện
                        </label>
                        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" onchange="previewAvatarInEdit(this)">
                        <small class="file-info">Chấp nhận: JPG, PNG, GIF, WebP. Tối đa 2MB.</small>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="auth-btn login-btn" onclick="closeEditModal()">Hủy</button>
                <button type="submit" class="auth-btn signup-btn">Lưu thay đổi</button>
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
                <button type="button" class="auth-btn login-btn" onclick="closeAvatarModal()">Hủy</button>
                <button type="submit" class="auth-btn signup-btn">Cập nhật ảnh</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script src="/js/profile.js"></script>

<?php 
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>
