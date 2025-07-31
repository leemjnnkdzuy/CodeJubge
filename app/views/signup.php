<?php
$content = ob_start();
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-content">
            <div class="auth-form-section">
                <div class="auth-header">
                    <h1 class="auth-title">Tạo Tài Khoản</h1>
                    <p class="auth-subtitle">Tham gia CodeJudge và bắt đầu hành trình lập trình của bạn</p>
                </div>
                
                <form class="auth-form" action="register" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="firstName" class="form-label">Họ</label>
                            <div class="input-group">
                                <i class="bx bx-user input-icon"></i>
                                <input type="text" id="firstName" name="firstName" class="form-input" placeholder="Nguyễn" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="lastName" class="form-label">Tên</label>
                            <div class="input-group">
                                <i class="bx bx-user input-icon"></i>
                                <input type="text" id="lastName" name="lastName" class="form-input" placeholder="Văn A" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username" class="form-label">Tên đăng nhập</label>
                        <div class="input-group">
                            <i class="bx bx-at input-icon"></i>
                            <input type="text" id="username" name="username" class="form-input" placeholder="nguyenvana" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email" class="form-label">Địa chỉ Email</label>
                        <div class="input-group">
                            <i class="bx bx-envelope input-icon"></i>
                            <input type="email" id="email" name="email" class="form-input" placeholder="nguyenvana@example.com" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <div class="input-group">
                            <i class="bx bx-lock-alt input-icon"></i>
                            <input type="password" id="password" name="password" class="form-input" placeholder="Tạo mật khẩu mạnh" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="bx bx-hide" id="password-toggle-icon"></i>
                            </button>
                        </div>
                        <div class="password-strength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Độ mạnh mật khẩu</span>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirmPassword" class="form-label">Xác nhận mật khẩu</label>
                        <div class="input-group">
                            <i class="bx bx-lock-alt input-icon"></i>
                            <input type="password" id="confirmPassword" name="confirmPassword" class="form-input" placeholder="Xác nhận mật khẩu của bạn" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('confirmPassword')">
                                <i class="bx bx-hide" id="confirmPassword-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" name="terms" class="checkbox-input" required>
                            Tôi đồng ý với <a href="/docs/terms" class="terms-link">Điều khoản Dịch vụ</a> và <a href="/docs/privacy" class="terms-link">Chính sách Bảo mật</a>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label class="checkbox-container">
                            <input type="checkbox" name="newsletter" class="checkbox-input">
                            Đăng ký nhận bản tin để nhận mẹo lập trình và cập nhật
                        </label>
                    </div>
                    
                    <button type="submit" class="auth-btn btn-primary">
                        <i class="bx bx-user-plus"></i>
                        Tạo Tài Khoản
                    </button>
                    
                    <div class="auth-divider">
                        <span>hoặc</span>
                    </div>
                    
                    <div class="social-login">
                        <button type="button" class="social-btn google-btn">
                            <i class="bx bxl-google"></i>
                            Đăng ký với Google
                        </button>
                        <button type="button" class="social-btn github-btn">
                            <i class="bx bxl-github"></i>
                            Đăng ký với GitHub
                        </button>
                    </div>
                    
                    <div class="auth-footer">
                        <p>Đã có tài khoản? <a href="login" class="auth-link">Đăng nhập tại đây</a></p>
                    </div>
                </form>
            </div>
            
            <div class="auth-visual-section">
                <div class="visual-content">
                    <div class="visual-header">
                        <h2>Bắt Đầu Hành Trình Của Bạn</h2>
                        <p>Tham gia cùng hàng nghìn lập trình viên đang cải thiện kỹ năng mỗi ngày</p>
                    </div>
                    
                    <div class="benefits-list">
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bx bx-check-circle"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>Miễn Phí Mãi Mãi</h4>
                                <p>Truy cập hàng trăm bài toán mà không tốn phí</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bx bx-trending-up"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>Theo Dõi Tiến Độ</h4>
                                <p>Giám sát sự cải thiện của bạn với phân tích chi tiết</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bx bx-group"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>Hỗ Trợ Cộng Đồng</h4>
                                <p>Học hỏi và giúp đỡ các lập trình viên khác</p>
                            </div>
                        </div>
                        
                        <div class="benefit-item">
                            <div class="benefit-icon">
                                <i class="bx bx-trophy"></i>
                            </div>
                            <div class="benefit-text">
                                <h4>Cuộc Thi</h4>
                                <p>Tham gia các cuộc thi lập trình và thử thách</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
$content = ob_get_clean();
$title = "Đăng Ký - CodeJudge";
include VIEW_PATH . '/layouts/pagesNothing.php';
?>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '-toggle-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bx bx-show';
    } else {
        input.type = 'password';
        icon.className = 'bx bx-hide';
    }
}

document.getElementById('password').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthFill = document.getElementById('strengthFill');
    const strengthText = document.getElementById('strengthText');
    
    let strength = 0;
    let text = '';
    let color = '';
    
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    switch (strength) {
        case 0:
        case 1:
            text = 'Rất Yếu';
            color = '#e74c3c';
            break;
        case 2:
            text = 'Yếu';
            color = '#f39c12';
            break;
        case 3:
            text = 'Trung Bình';
            color = '#f1c40f';
            break;
        case 4:
            text = 'Tốt';
            color = '#27ae60';
            break;
        case 5:
            text = 'Mạnh';
            color = '#2ecc71';
            break;
    }
    
    strengthFill.style.width = (strength * 20) + '%';
    strengthFill.style.backgroundColor = color;
    strengthText.textContent = text;
    strengthText.style.color = color;
});
</script>
