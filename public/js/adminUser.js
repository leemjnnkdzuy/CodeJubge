document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const createUserBtn = document.querySelector('.create-user-btn');
    const userModal = document.getElementById('userModal');
    const modalTitle = document.getElementById('modalTitle');
    const userForm = document.getElementById('userForm');
    const closeModalBtn = document.querySelector('.user-modal-close');
    const cancelBtn = document.querySelector('.form-btn.secondary');
    
    const deleteModal = document.getElementById('deleteModal');
    const deleteModalClose = document.querySelector('.delete-modal-close');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    const cancelDeleteBtn = document.getElementById('cancelDelete');
    
    let currentUserId = null;
    let currentAction = 'create';
    
    // Show create user modal
    createUserBtn.addEventListener('click', function() {
        showUserModal('create');
    });
    
    // Close modal events
    closeModalBtn.addEventListener('click', closeUserModal);
    cancelBtn.addEventListener('click', closeUserModal);
    
    // Close delete modal events
    deleteModalClose.addEventListener('click', closeDeleteModal);
    cancelDeleteBtn.addEventListener('click', closeDeleteModal);
    
    // Click outside modal to close
    userModal.addEventListener('click', function(e) {
        if (e.target === userModal) {
            closeUserModal();
        }
    });
    
    deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            closeDeleteModal();
        }
    });
    
    // Action buttons
    document.addEventListener('click', function(e) {
        const target = e.target.closest('button');
        if (!target) return;
        
        if (target.classList.contains('btn-edit')) {
            const row = target.closest('tr');
            const userId = row.querySelector('td:first-child').textContent;
            showUserModal('edit', getUserDataFromRow(row), userId);
        }
        
        if (target.classList.contains('btn-view')) {
            const row = target.closest('tr');
            const username = row.querySelector('.username').textContent.replace('@', '');
            window.open(`/user/${username}`, '_blank');
        }
        
        if (target.classList.contains('btn-delete')) {
            const row = target.closest('tr');
            const userId = row.querySelector('td:first-child').textContent;
            const userName = row.querySelector('.user-name').textContent;
            showDeleteModal(userId, userName);
        }
    });
    
    // Form submission
    userForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(userForm);
        const data = Object.fromEntries(formData);
        
        if (currentAction === 'create') {
            createUser(data);
        } else {
            updateUser(currentUserId, data);
        }
    });
    
    // Confirm delete
    confirmDeleteBtn.addEventListener('click', function() {
        if (currentUserId) {
            deleteUser(currentUserId);
        }
    });
    
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    searchInput.addEventListener('input', function() {
        filterUsers();
    });
    
    // Filter functionality
    const filterSelects = document.querySelectorAll('.filter-select');
    filterSelects.forEach(select => {
        select.addEventListener('change', filterUsers);
    });
    
    function showUserModal(action, userData = null, userId = null) {
        currentAction = action;
        currentUserId = userId;
        
        if (action === 'create') {
            modalTitle.textContent = 'Thêm User Mới';
            userForm.reset();
            document.getElementById('passwordGroup').style.display = 'block';
        } else {
            modalTitle.textContent = 'Chỉnh Sửa User';
            fillForm(userData);
            document.getElementById('passwordGroup').style.display = 'none';
        }
        
        userModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    function closeUserModal() {
        userModal.classList.remove('show');
        document.body.style.overflow = '';
        userForm.reset();
        currentUserId = null;
    }
    
    function showDeleteModal(userId, userName) {
        currentUserId = userId;
        document.getElementById('deleteUserName').textContent = userName;
        deleteModal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
    
    function closeDeleteModal() {
        deleteModal.classList.remove('show');
        document.body.style.overflow = '';
        currentUserId = null;
    }
    
    function fillForm(userData) {
        document.getElementById('firstName').value = userData.firstName || '';
        document.getElementById('lastName').value = userData.lastName || '';
        document.getElementById('username').value = userData.username || '';
        document.getElementById('email').value = userData.email || '';
        document.getElementById('role').value = userData.role || 'user';
        document.getElementById('isActive').checked = userData.isActive || false;
    }
    
    function getUserDataFromRow(row) {
        const cells = row.querySelectorAll('td');
        const nameParts = cells[1].querySelector('.user-name').textContent.trim().split(' ');
        const firstName = nameParts[0] || '';
        const lastName = nameParts.slice(1).join(' ') || '';
        const username = cells[2].querySelector('.username').textContent.replace('@', '');
        const email = cells[3].textContent.trim();
        const role = cells[4].querySelector('.role-badge').textContent.toLowerCase().trim();
        const isActive = cells[5].querySelector('.status-badge').classList.contains('status-active');
        
        return {
            firstName,
            lastName,
            username,
            email,
            role,
            isActive
        };
    }
    
    function createUser(data) {
        // Show loading state
        const submitBtn = userForm.querySelector('.form-btn.primary');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Đang tạo...';
        submitBtn.disabled = true;
        
        fetch('/admin/users/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('User đã được tạo thành công!', 'success');
                closeUserModal();
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(result.message || 'Có lỗi xảy ra khi tạo user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra khi tạo user', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }
    
    function updateUser(userId, data) {
        const submitBtn = userForm.querySelector('.form-btn.primary');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Đang cập nhật...';
        submitBtn.disabled = true;
        
        fetch(`/admin/users/update/${userId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('User đã được cập nhật thành công!', 'success');
                closeUserModal();
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(result.message || 'Có lỗi xảy ra khi cập nhật user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra khi cập nhật user', 'error');
        })
        .finally(() => {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }
    
    function deleteUser(userId) {
        const deleteBtn = confirmDeleteBtn;
        const originalText = deleteBtn.textContent;
        deleteBtn.textContent = 'Đang xóa...';
        deleteBtn.disabled = true;
        
        fetch(`/admin/users/delete/${userId}`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                showNotification('User đã được xóa thành công!', 'success');
                closeDeleteModal();
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                showNotification(result.message || 'Có lỗi xảy ra khi xóa user', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Có lỗi xảy ra khi xóa user', 'error');
        })
        .finally(() => {
            deleteBtn.textContent = originalText;
            deleteBtn.disabled = false;
        });
    }
    
    function filterUsers() {
        const searchTerm = document.querySelector('.search-input').value.toLowerCase();
        const roleFilter = document.querySelector('.filter-select:nth-of-type(1)').value;
        const statusFilter = document.querySelector('.filter-select:nth-of-type(2)').value;
        
        const rows = document.querySelectorAll('.admin-table tbody tr');
        
        rows.forEach(row => {
            if (row.querySelector('.no-data')) return;
            
            const name = row.querySelector('.user-name').textContent.toLowerCase();
            const username = row.querySelector('.username').textContent.toLowerCase();
            const email = row.cells[3].textContent.toLowerCase();
            const role = row.querySelector('.role-badge').textContent.toLowerCase();
            const isActive = row.querySelector('.status-badge').classList.contains('status-active');
            
            const matchesSearch = name.includes(searchTerm) || 
                                username.includes(searchTerm) || 
                                email.includes(searchTerm);
            
            const matchesRole = !roleFilter || role === roleFilter;
            const matchesStatus = !statusFilter || 
                                (statusFilter === 'active' && isActive) ||
                                (statusFilter === 'inactive' && !isActive);
            
            if (matchesSearch && matchesRole && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    function showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <span class="notification-message">${message}</span>
                <button class="notification-close">&times;</button>
            </div>
        `;
        
        // Add to page
        document.body.appendChild(notification);
        
        // Show notification
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            hideNotification(notification);
        }, 5000);
        
        // Close button
        notification.querySelector('.notification-close').addEventListener('click', () => {
            hideNotification(notification);
        });
    }
    
    function hideNotification(notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }
});