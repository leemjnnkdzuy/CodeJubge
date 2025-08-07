<div class="admin-page-header">
    <div class="admin-page-title">
        <h2>Quản lý Users</h2>
        <p>Xem và quản lý tất cả người dùng trong hệ thống</p>
    </div>
    <div class="admin-page-actions">
        <button class="create-user-btn">
            <i class='bx bx-plus'></i>
            Thêm User
        </button>
    </div>
</div>

<div class="admin-filters">
    <div class="admin-search">
        <input type="text" placeholder="Tìm kiếm user..." class="search-input">
        <i class='bx bx-search'></i>
    </div>
    <select class="filter-select">
        <option value="">Tất cả vai trò</option>
        <option value="user">User</option>
        <option value="admin">Admin</option>
        <option value="moderator">Moderator</option>
    </select>
    <select class="filter-select">
        <option value="">Tất cả trạng thái</option>
        <option value="active">Hoạt động</option>
        <option value="inactive">Không hoạt động</option>
    </select>
</div>

<div class="admin-table-container">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Username</th>
                <th>Email</th>
                <th>Vai trò</th>
                <th>Trạng thái</th>
                <th>Problems Solved</th>
                <th>Rating</th>
                <th>Ngày tạo</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($users) && is_array($users)): ?>
                <?php 
                require_once APP_PATH . '/helpers/AvatarHelper.php';
                foreach ($users as $user): 
                $userAvatar = AvatarHelper::base64ToImageSrc($user['avatar'] ?? '');
                $userInitials = AvatarHelper::getInitials($user['first_name'] . ' ' . $user['last_name']);
                ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td>
                        <div class="user-info">
                            <div class="user-avatar">
                                <?php if (!empty($user['avatar'])): ?>
                                    <img src="<?= $userAvatar ?>" alt="Avatar" class="avatar-image">
                                <?php else: ?>
                                    <div class="avatar-initials"><?= $userInitials ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="user-details">
                                <div class="user-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="username">@<?= htmlspecialchars($user['username']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="role-badge role-<?= htmlspecialchars($user['role']) ?>">
                            <?= ucfirst(htmlspecialchars($user['role'])) ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?= $user['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $user['is_active'] ? 'Hoạt động' : 'Không hoạt động' ?>
                        </span>
                    </td>
                    <td><?= number_format($user['total_problems_solved'] ?? 0) ?></td>
                    <td>
                        <?php if ($user['rating'] == -1): ?>
                            <span class="text-muted">Chưa xếp hạng</span>
                        <?php else: ?>
                            <span class="rating"><?= number_format($user['rating']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-edit" title="Chỉnh sửa" data-user-id="<?= htmlspecialchars($user['id']) ?>">
                                <i class='bx bx-edit'></i>
                            </button>
                            <a href="<?php echo SITE_URL . '/user/' . htmlspecialchars($user['username']); ?>" class="btn-action btn-view" title="Xem chi tiết" data-user-id="<?= htmlspecialchars($user['id']) ?>" target="_blank">
                                <i class='bx bx-show'></i>
                            </a>
                            <?php if ($user['role'] !== 'admin'): ?>
                            <button class="btn-action btn-delete" title="Xóa" data-user-id="<?= htmlspecialchars($user['id']) ?>">
                                <i class='bx bx-trash'></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" class="no-data">Không có dữ liệu user</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div id="userModal" class="edit-profile-modal">
	<div class="edit-profile-modal-content">
		<div class="edit-profile-modal-header">
			<h2 id="modalTitle">Chỉnh sửa thông tin User</h2>
			<button class="edit-profile-modal-close" onclick="closeEditModal()">&times;</button>
		</div>
		
		<form class="edit-profile-form" id="userForm" action="/admin/users" method="POST" enctype="multipart/form-data">
			<div class="form-group">
				<label for="first_name">
					<i class='bx bx-user'></i>
					Tên
				</label>
				<input type="text" id="firstName" name="firstName" class="form-input" required placeholder="Nhập tên">
			</div>
			
			<div class="form-group">
				<label for="last_name">
					<i class='bx bx-user'></i>
					Họ
				</label>
				<input type="text" id="lastName" name="lastName" class="form-input" required placeholder="Nhập họ">
			</div>
			
			<div class="form-group">
				<label for="username">
					<i class='bx bx-at'></i>
					Username
				</label>
				<input type="text" id="username" name="username" class="form-input" required placeholder="Nhập username">
				<small class="form-note">Username phải là duy nhất</small>
			</div>
			
			<div class="form-group">
				<label for="email">
					<i class='bx bx-envelope'></i>
					Email
				</label>
				<input type="email" id="email" name="email" class="form-input" required placeholder="Nhập email">
				<small class="form-note">Email phải là duy nhất</small>
			</div>
			
			<div id="passwordGroup" class="form-group">
				<label for="password">
					<i class='bx bx-lock'></i>
					Mật khẩu
				</label>
				<input type="password" id="password" name="password" class="form-input" placeholder="Nhập mật khẩu mới (để trống nếu không đổi)">
				<small class="form-note">Để trống nếu không muốn thay đổi mật khẩu</small>
			</div>

			<div class="form-group full-width">
				<label for="bio">
					<i class='bx bx-text'></i>
					Giới thiệu
				</label>
				<textarea id="bio" name="bio" rows="3" placeholder="Viết vài dòng về user này..."></textarea>
			</div>
			
			<div class="form-section full-width">
				<h3 class="section-subtitle">
					<i class='bx bx-link'></i>
					Liên kết mạng xã hội
				</h3>
				<div class="social-links-grid">
					<div class="form-group">
						<label for="github_url">
							<i class='bx bxl-github'></i>
							GitHub URL
						</label>
						<input type="url" id="github_url" name="github_url" placeholder="https://github.com/username">
					</div>
					
					<div class="form-group">
						<label for="linkedin_url">
							<i class='bx bxl-linkedin'></i>
							LinkedIn URL
						</label>
						<input type="url" id="linkedin_url" name="linkedin_url" placeholder="https://linkedin.com/in/username">
					</div>
					
					<div class="form-group">
						<label for="website_url">
							<i class='bx bx-link'></i>
							Website URL
						</label>
						<input type="url" id="website_url" name="website_url" placeholder="https://yourwebsite.com">
					</div>
					
					<div class="form-group">
						<label for="youtube_url">
							<i class='bx bxl-youtube'></i>
							YouTube URL
						</label>
						<input type="url" id="youtube_url" name="youtube_url" placeholder="https://youtube.com/channel/your-channel">
					</div>
					
					<div class="form-group">
						<label for="facebook_url">
							<i class='bx bxl-facebook'></i>
							Facebook URL
						</label>
						<input type="url" id="facebook_url" name="facebook_url" placeholder="https://facebook.com/your-profile">
					</div>
					
					<div class="form-group">
						<label for="instagram_url">
							<i class='bx bxl-instagram'></i>
							Instagram URL
						</label>
						<input type="url" id="instagram_url" name="instagram_url" placeholder="https://instagram.com/your-username">
					</div>
				</div>
			</div>
			
			<div class="form-group full-width">
				<label>
					<i class='bx bx-camera'></i>
					Ảnh đại diện
				</label>
				<div class="avatar-edit-section">
					<div class="current-avatar">
						<img src="/assets/default_avatar.png" alt="Current Avatar" id="currentAvatarPreview" class="current-avatar-image">
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

			<div class="form-section badges-section full-width">
				<h3 class="section-subtitle">
					<div>
					    <i class='bx bx-badge'></i>
    					Badges
					</div>
					<button type="button" class="badges-toggle-btn" id="badgesToggle">
						<span class="toggle-text">Show more</span>
						<i class='bx bx-chevron-down toggle-icon'></i>
					</button>
				</h3>
				<div class="badges-grid" id="badgesGrid">
					<?php
					global $BADGES;
					$badgeCount = 0;
					$maxInitialBadges = 12;
					
					foreach ($BADGES as $badgeKey => $badgeData): 
						$badgeCount++;
						$isHidden = $badgeCount > $maxInitialBadges;
						$hiddenClass = $isHidden ? 'badge-hidden' : '';
					?>
					<div class="badge-item selectable <?= $hiddenClass ?>" data-badge="<?= $badgeKey ?>">
						<img src="/assets/<?= $badgeData['File'] ?>" alt="<?= $badgeData['title'] ?>" class="badge-icon" title="<?= $badgeData['description'] ?>">
						<div class="badge-title"><?= $badgeData['title'] ?></div>
					</div>
					<?php endforeach; ?>
				</div>
			</div>
			
			<div class="admin-fields">
				<div class="form-group">
					<label for="role">
						<i class='bx bx-shield'></i>
						Vai trò
					</label>
					<select id="role" name="role" class="form-input">
						<option value="user">User</option>
						<option value="moderator">Moderator</option>
						<option value="admin">Admin</option>
					</select>
				</div>
				
				<div class="form-group">
					<label>
						<i class='bx bx-check-circle'></i>
						Trạng thái
					</label>
					<div class="checkbox-wrapper">
						<input type="checkbox" id="isActive" name="isActive" checked>
						<label for="isActive">Tài khoản hoạt động</label>
					</div>
				</div>

				<div class="form-group">
					<label for="rating">
						<i class='bx bx-star'></i>
						Rating
					</label>
					<input type="number" id="rating" name="rating" class="form-input" placeholder="Rating (-1 = chưa xếp hạng)" min="-1" value="-1">
					<small class="form-note">-1 = chưa xếp hạng</small>
				</div>
			</div>
			
			<div class="form-actions">
				<button type="button" class="auth-btn login-btn" onclick="closeEditModal()">
					<i class='bx bx-x'></i>
					Hủy
				</button>
				<button type="submit" class="auth-btn signup-btn">
					<i class='bx bx-check'></i>
					Lưu thay đổi
				</button>
			</div>
		</form>
	</div>
</div>

<div id="deleteModal" class="edit-profile-modal delete-modal">
    <div class="edit-profile-modal-content delete-modal-content">
        <div class="edit-profile-modal-header delete-modal-header">
            <h2 id="deleteModalTitle">Xác nhận xóa User</h2>
            <button class="edit-profile-modal-close delete-modal-close" onclick="closeDeleteModal()">&times;</button>
        </div>
        <div class="delete-modal-body">
            <div class="delete-warning">
                <div class="delete-warning-icon">
                    <i class='bx bx-error-circle' style="font-size: 3rem; color: #000000ff;"></i>
                </div>
                <div class="delete-message">
                    <h3>Bạn có chắc chắn muốn xóa user này?</h3>
                    <p class="delete-user-info">
                        <strong id="deleteUserName"></strong>
                    </p>
                </div>
                <div class="delete-note">
                    <p><strong>Cảnh báo:</strong> Hành động này không thể hoàn tác và sẽ xóa toàn bộ dữ liệu liên quan:</p>
                    <ul>
                        <li>Tất cả bài nộp (submissions)</li>
                        <li>Lịch sử giải bài</li>
                        <li>Thảo luận và bình luận</li>
                        <li>Dữ liệu profile và thống kê</li>
                    </ul>
                </div>
            </div>
            <div class="delete-actions">
                <button id="cancelDelete" class="auth-btn secondary-btn">
                    <i class='bx bx-x'></i>
                    Hủy
                </button>
                <button id="confirmDelete" class="auth-btn danger-btn">
                    <i class='bx bx-trash'></i>
                    Xóa vĩnh viễn
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/js/adminUsers.js"></script>