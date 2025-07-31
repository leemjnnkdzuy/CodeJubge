<?php 
$content = ob_start(); 
?>

<section class="auth-section">
    <div class="auth-container">
        <div class="auth-content">
            <div class="auth-form-section">
                <div class="auth-header">
                    <h1 class="auth-title">Chào Mừng Trở Lại</h1>
                    <p class="auth-subtitle">Đăng nhập vào tài khoản CodeJudge của bạn</p>
                </div>
                
                <form class="auth-form" action="/login" method="POST">
                    <div class="form-group">
                        <label for="email" class="form-label">Địa chỉ Email</label>
                        <div class="input-group">
                            <i class="bx bx-envelope input-icon"></i>
                            <input type="email" id="email" name="email" class="form-input" placeholder="Nhập email của bạn" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <div class="input-group">
                            <i class="bx bx-lock-alt input-icon"></i>
                            <input type="password" id="password" name="password" class="form-input" placeholder="Nhập mật khẩu của bạn" required>
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <i class="bx bx-hide" id="password-toggle-icon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-options">
                        <label class="checkbox-container">
                            <input type="checkbox" name="remember" class="checkbox-input">
                            Ghi nhớ tôi
                        </label>
                        <a href="/forgot-password" class="forgot-link">Quên mật khẩu?</a>
                    </div>
                    
                    <button type="submit" class="auth-btn btn-primary">
                        <i class="bx bx-log-in"></i>
                        Đăng Nhập
                    </button>
                    
                    <div class="auth-divider">
                        <span>hoặc</span>
                    </div>
                    
                    <div class="social-login">
                        <button type="button" class="social-btn google-btn">
                            <i class="bx bxl-google"></i>
                            Tiếp tục với Google
                        </button>
                        <button type="button" class="social-btn github-btn">
                            <i class="bx bxl-github"></i>
                            Tiếp tục với GitHub
                        </button>
                    </div>
                    
                    <div class="auth-footer">
                        <p>Chưa có tài khoản? <a href="/register" class="auth-link">Đăng ký tại đây</a></p>
                    </div>
                </form>
            </div>
            
            <div class="auth-visual-section">
                <div class="visual-content">
                    <div class="visual-header">
                        <h2>Tham Gia Cộng Đồng Lập Trình</h2>
                        <p>Thực hành, học tập và phát triển cùng hàng nghìn lập trình viên trên toàn thế giới</p>
                    </div>
                    
                    <div class="features-showcase">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bx bx-code-alt"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Bài Tập Thực Hành</h4>
                                <p>Giải quyết hàng trăm thử thách lập trình</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bx bx-trophy"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Cuộc Thi</h4>
                                <p>Thi đấu với các lập trình viên toàn cầu</p>
                            </div>
                        </div>
                        
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i class="bx bx-stats"></i>
                            </div>
                            <div class="feature-text">
                                <h4>Theo Dõi Tiến Độ</h4>
                                <p>Giám sát hành trình lập trình của bạn</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="code-preview">
                        <div class="code-header">
                            <div class="code-dots">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <span class="code-title">solution.py</span>
                        </div>
                        <div class="code-content">
                            <div class="code-line">
                                <span class="line-number">1</span>
                                <span class="keyword">def</span> <span class="function">&nbsp;solve</span>():
                            </div>
                            <div class="code-line">
                                <span class="line-number">2</span>
                                &nbsp;&nbsp;&nbsp;&nbsp;<span class="keyword">return</span> <span class="string">"Success!"</span>
                            </div>
                            <div class="code-line">
                                <span class="line-number">3</span>
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
$title = "Đăng Nhập - CodeJudge";
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
</script>
