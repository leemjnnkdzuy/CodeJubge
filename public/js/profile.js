let originalFormData = {};

function openEditModal() {
    document.getElementById('editProfileModal').style.display = 'flex';
    saveOriginalFormData();
    checkFormChanges();
}

function closeEditModal() {
    document.getElementById('editProfileModal').style.display = 'none';
}

function saveOriginalFormData() {
    const form = document.querySelector('.edit-form');
    originalFormData = {
        first_name: form.first_name.value,
        last_name: form.last_name.value,
        bio: form.bio.value,
        github_url: form.github_url.value,
        linkedin_url: form.linkedin_url.value,
        website_url: form.website_url.value,
        youtube_url: form.youtube_url.value,
        facebook_url: form.facebook_url.value,
        instagram_url: form.instagram_url.value,
        avatar: null
    };
}

function checkFormChanges() {
    const form = document.querySelector('.edit-form');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    if (!form || !submitBtn) return;
    
    const hasTextChanges = 
        form.first_name.value !== originalFormData.first_name ||
        form.last_name.value !== originalFormData.last_name ||
        form.bio.value !== originalFormData.bio ||
        form.github_url.value !== originalFormData.github_url ||
        form.linkedin_url.value !== originalFormData.linkedin_url ||
        form.website_url.value !== originalFormData.website_url ||
        form.youtube_url.value !== originalFormData.youtube_url ||
        form.facebook_url.value !== originalFormData.facebook_url ||
        form.instagram_url.value !== originalFormData.instagram_url;
    
    const hasAvatarChange = form.avatar.files && form.avatar.files.length > 0;
    
    const hasChanges = hasTextChanges || hasAvatarChange;
    
    submitBtn.disabled = !hasChanges;
    
    if (hasChanges) {
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
        submitBtn.title = '';
    } else {
        submitBtn.style.opacity = '0.5';
        submitBtn.style.cursor = 'not-allowed';
        submitBtn.title = 'Không có thay đổi nào để lưu';
    }
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

function previewAvatarInEdit(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('error', 'Chỉ chấp nhận file ảnh với định dạng JPG, PNG, GIF, WebP!');
            input.value = '';
            checkFormChanges();
            return;
        }
        
        const maxSize = 2048000;
        if (file.size > maxSize) {
            showNotification('error', 'File ảnh quá lớn! Vui lòng chọn file nhỏ hơn 2MB.');
            input.value = '';
            checkFormChanges();
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('currentAvatarPreview').src = e.target.result;
            showNotification('success', 'Ảnh hợp lệ! Sẽ được cập nhật khi lưu thay đổi.');
            checkFormChanges();
        };
        reader.readAsDataURL(file);
    } else {
        checkFormChanges();
    }
}

function submitForm(form) {
    const submitBtn = form.querySelector('button[type="submit"]');
    
    if (submitBtn && submitBtn.disabled) {
        showNotification('warning', 'Không có thay đổi nào để lưu!');
        return false;
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

document.addEventListener('DOMContentLoaded', function() {
    const editForm = document.querySelector('.edit-form');
    if (editForm) {
        const inputs = editForm.querySelectorAll('input[type="text"], input[type="email"], input[type="url"], textarea, input[type="file"]');
        inputs.forEach(input => {
            if (input.type === 'file') {
                input.addEventListener('change', checkFormChanges);
            } else if (!input.disabled) {
                input.addEventListener('input', checkFormChanges);
                input.addEventListener('change', checkFormChanges);
            }
        });
    }
});
