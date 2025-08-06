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
                            <button class="btn-action btn-edit" title="Chỉnh sửa">
                                <i class='bx bx-edit'></i>
                            </button>
                            <button class="btn-action btn-view" title="Xem chi tiết">
                                <i class='bx bx-show'></i>
                            </button>
                            <?php if ($user['role'] !== 'admin'): ?>
                            <button class="btn-action btn-delete" title="Xóa">
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

<!-- User Modal (Create/Edit) -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalTitle">Thêm User Mới</h2>
            <button class="modal-close">&times;</button>
        </div>
        <div class="modal-body">
            <form id="userForm" class="edit-form">
                <div class="form-group">
                    <label for="firstName">
                        <i class='bx bx-user'></i>
                        Họ
                    </label>
                    <input type="text" id="firstName" name="firstName" class="form-input" required placeholder="Nhập họ">
                </div>
                
                <div class="form-group">
                    <label for="lastName">
                        <i class='bx bx-user'></i>
                        Tên
                    </label>
                    <input type="text" id="lastName" name="lastName" class="form-input" required placeholder="Nhập tên">
                </div>
                
                <div class="form-group">
                    <label for="username">
                        <i class='bx bx-at'></i>
                        Username
                    </label>
                    <input type="text" id="username" name="username" class="form-input" required placeholder="Nhập username">
                </div>
                
                <div class="form-group">
                    <label for="email">
                        <i class='bx bx-envelope'></i>
                        Email
                    </label>
                    <input type="email" id="email" name="email" class="form-input" required placeholder="Nhập email">
                </div>
                
                <div id="passwordGroup" class="form-group">
                    <label for="password">
                        <i class='bx bx-lock'></i>
                        Mật khẩu
                    </label>
                    <input type="password" id="password" name="password" class="form-input" required placeholder="Nhập mật khẩu">
                </div>
                
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
                    <div class="checkbox-wrapper">
                        <input type="checkbox" id="isActive" name="isActive" class="checkbox-input" checked>
                        <label for="isActive" class="checkbox-label">Tài khoản hoạt động</label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="auth-btn login-btn">
                        <i class='bx bx-x'></i>
                        Hủy
                    </button>
                    <button type="submit" class="auth-btn signup-btn">
                        <i class='bx bx-check'></i>
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="delete-modal">
    <div class="delete-modal-content">
        <div class="delete-modal-header">
            <h3 class="delete-modal-title">Xác nhận xóa User</h3>
            <button class="delete-modal-close">&times;</button>
        </div>
        <div class="delete-modal-body">
            <div class="delete-warning">
                <div class="delete-warning-icon">⚠️</div>
                <div class="delete-message">
                    Bạn có chắc chắn muốn xóa user <span id="deleteUserName" class="delete-user-name"></span>?
                </div>
                <div class="delete-note">
                    Hành động này không thể hoàn tác và sẽ xóa toàn bộ dữ liệu liên quan đến user này bao gồm:
                    <br>• Tất cả bài nộp (submissions)
                    <br>• Lịch sử giải bài
                    <br>• Thảo luận và bình luận
                </div>
            </div>
            <div class="delete-actions">
                <button id="cancelDelete" class="delete-btn secondary">
                    <i class='bx bx-x'></i>
                    Hủy
                </button>
                <button id="confirmDelete" class="delete-btn danger">
                    <i class='bx bx-trash'></i>
                    Xóa vĩnh viễn
                </button>
            </div>
        </div>
    </div>
</div>

<script src="/js/adminUsers.js"></script>