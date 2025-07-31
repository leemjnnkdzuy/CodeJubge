<?php
$content = ob_start();
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-content">
            <div class="auth-form-section">
                <div class="auth-header">
                    <div class="reset-icon">
                        <i class="bx bx-lock-open"></i>
                    </div>
                    <h1 class="auth-title">Đặt Lại Mật Khẩu</h1>
                    <p class="auth-subtitle">Đừng lo lắng! Nhập email của bạn và chúng tôi sẽ gửi cho bạn liên kết đặt lại</p>
                </div>
                
                <form class="auth-form" action="/forgot-password" method="POST">
                    <div class="form-group">
                        <label for="email" class="form-label">Địa chỉ Email</label>
                        <div class="input-group">
                            <i class="bx bx-envelope input-icon"></i>
                            <input type="email" id="email" name="email" class="form-input" placeholder="Nhập địa chỉ email của bạn" required>
                        </div>
                        <div class="input-hint">
                            <i class="bx bx-info-circle"></i>
                            Chúng tôi sẽ gửi liên kết đặt lại mật khẩu đến email này
                        </div>
                    </div>
                    
                    <button type="submit" class="auth-btn btn-primary">
                        <i class="bx bx-send"></i>
                        Gửi Liên Kết Đặt Lại
                    </button>
                    
                    <div class="auth-footer">
                        <p>Nhớ mật khẩu của bạn? <a href="/login" class="auth-link">Trở về Đăng nhập</a></p>
                        <p>Chưa có tài khoản? <a href="/register" class="auth-link">Đăng ký tại đây</a></p>
                    </div>
                </form>
            </div>
            
            <div class="auth-visual-section">
                <div class="visual-content">
                    <div class="visual-header">
                        <h2>Bảo Mật Là Ưu Tiên</h2>
                        <p>Bảo mật tài khoản của bạn là ưu tiên hàng đầu của chúng tôi</p>
                    </div>
                    
                    <div class="security-features">
                        <div class="security-item">
                            <div class="security-icon">
                                <i class="bx bx-shield-check"></i>
                            </div>
                            <div class="security-text">
                                <h4>Quy Trình Đặt Lại Bảo Mật</h4>
                                <p>Liên kết đặt lại mật khẩu được mã hóa và hết hạn nhanh chóng để đảm bảo bảo mật tối đa</p>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-icon">
                                <i class="bx bx-time-five"></i>
                            </div>
                            <div class="security-text">
                                <h4>Liên Kết Có Thời Hạn</h4>
                                <p>Liên kết đặt lại tự động hết hạn sau 1 giờ để bảo vệ tài khoản của bạn</p>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-icon">
                                <i class="bx bx-notification"></i>
                            </div>
                            <div class="security-text">
                                <h4>Xác Minh Email</h4>
                                <p>Chỉ những địa chỉ email đã được xác minh mới có thể nhận hướng dẫn đặt lại mật khẩu</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="illustration">
                        <div class="lock-container">
                            <div class="lock-body">
                                <div class="lock-shackle"></div>
                                <div class="lock-keyhole">
                                    <i class="bx bx-key"></i>
                                </div>
                            </div>
                            <div class="security-waves">
                                <div class="wave wave-1"></div>
                                <div class="wave wave-2"></div>
                                <div class="wave wave-3"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tips-section">
                        <h3>Mẹo Mật Khẩu</h3>
                        <ul class="tips-list">
                            <li><i class="bx bx-check"></i> Sử dụng ít nhất 8 ký tự</li>
                            <li><i class="bx bx-check"></i> Bao gồm chữ hoa và chữ thường</li>
                            <li><i class="bx bx-check"></i> Thêm số và ký tự đặc biệt</li>
                            <li><i class="bx bx-check"></i> Tránh từ thông dụng hoặc thông tin cá nhân</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
$content = ob_get_clean();
$title = "Quên Mật Khẩu - CodeJudge";
include VIEW_PATH . '/layouts/pagesNothing.php';
?>
