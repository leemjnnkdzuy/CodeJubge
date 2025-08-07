<?php
$notification = null;
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']);
}

if ($notification): ?>
<div id="popupNotification" class="popup-notification <?= htmlspecialchars($notification['type']) ?>" style="display: block;">
    <div class="notification-content">
        <div class="notification-icon">
            <?php if ($notification['type'] === 'success'): ?>
                <i class='bx bx-check-circle'></i>
            <?php elseif ($notification['type'] === 'error'): ?>
                <i class='bx bx-error-circle'></i>
            <?php elseif ($notification['type'] === 'warning'): ?>
                <i class='bx bx-error'></i>
            <?php else: ?>
                <i class='bx bx-info-circle'></i>
            <?php endif; ?>
        </div>
        <div class="notification-message">
            <?= htmlspecialchars($notification['message']) ?>
        </div>
        <button class="notification-close" onclick="closeNotification()">
            <i class='bx bx-x'></i>
        </button>
    </div>
</div>
<?php endif; ?>

<style>
.popup-notification {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 10000;
    max-width: 400px;
    min-width: 300px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease-in-out;
    font-family: 'Inter Tight', sans-serif;
}

.popup-notification.show {
    opacity: 1;
    transform: translateX(0);
}

.notification-content {
    display: flex;
    align-items: center;
    padding: 16px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    background: white;
    border-left: 4px solid;
}

.popup-notification.success .notification-content {
    border-left-color: #10b981;
    background: #f0fdf4;
}

.popup-notification.error .notification-content {
    border-left-color: #ef4444;
    background: #fef2f2;
}

.popup-notification.warning .notification-content {
    border-left-color: #f59e0b;
    background: #fffbeb;
}

.popup-notification.info .notification-content {
    border-left-color: #3b82f6;
    background: #eff6ff;
}

.notification-icon {
    margin-right: 12px;
    font-size: 20px;
}

.popup-notification.success .notification-icon {
    color: #10b981;
}

.popup-notification.error .notification-icon {
    color: #ef4444;
}

.popup-notification.warning .notification-icon {
    color: #f59e0b;
}

.popup-notification.info .notification-icon {
    color: #3b82f6;
}

.notification-message {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    line-height: 1.4;
}

.notification-close {
    background: none;
    border: none;
    font-size: 18px;
    color: #6b7280;
    cursor: pointer;
    padding: 4px;
    margin-left: 12px;
    border-radius: 4px;
    transition: background-color 0.2s ease;
}

.notification-close:hover {
    background-color: rgba(0, 0, 0, 0.1);
}

@media (max-width: 480px) {
    .popup-notification {
        right: 10px;
        left: 10px;
        max-width: none;
        min-width: auto;
    }
}
</style>

<script>
function closeNotification() {
    const notification = document.getElementById('popupNotification');
    if (notification) {
        notification.classList.remove('show');
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

<?php if ($notification): ?>
document.addEventListener('DOMContentLoaded', function() {
    const notification = document.getElementById('popupNotification');
    if (notification) {
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            closeNotification();
        }, 5000);
    }
});
<?php endif; ?>

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
    
    setTimeout(() => {
        closeNotification();
    }, 5000);
}
</script>
