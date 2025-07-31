<?php 
$content = ob_start(); 

$step = $_GET['step'] ?? 'verify_pin';
$email = $_SESSION['password_reset_email'] ?? '';
?>

<section class="reset-section">
    <div class="reset-container">
        <div class="reset-content">
            <div class="reset-form-section">
                <div class="reset-header">
                    <div class="reset-icon">
                        <i class="<?php echo $step === 'verify_pin' ? 'bx bx-lock-alt' : 'bx bx-key'; ?>"></i>
                    </div>
                    <h1 class="reset-title">
                        <?php echo $step === 'verify_pin' ? 'Xác Thực Mã PIN' : 'Đặt Lại Mật Khẩu'; ?>
                    </h1>
                    <p class="reset-subtitle">
                        <?php if ($step === 'verify_pin'): ?>
                            Chúng tôi đã gửi mã PIN 6 số đến email: <strong><?php echo htmlspecialchars($email); ?></strong>
                        <?php else: ?>
                            Tạo mật khẩu mới cho tài khoản của bạn
                        <?php endif; ?>
                    </p>
                </div>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <i class="bx bx-error-circle"></i>
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="bx bx-check-circle"></i>
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($step === 'verify_pin'): ?>
                    <!-- PIN Verification Form -->
                    <form class="reset-form" action="/reset-password?step=verify_pin" method="POST">
                        <div class="pin-input-container">
                            <label class="pin-label">Nhập mã PIN 6 số</label>
                            <div class="pin-inputs">
                                <input type="text" name="pin_1" maxlength="1" class="pin-digit" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                <input type="text" name="pin_2" maxlength="1" class="pin-digit" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                <input type="text" name="pin_3" maxlength="1" class="pin-digit" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                <input type="text" name="pin_4" maxlength="1" class="pin-digit" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                <input type="text" name="pin_5" maxlength="1" class="pin-digit" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                                <input type="text" name="pin_6" maxlength="1" class="pin-digit" pattern="[0-9]" inputmode="numeric" autocomplete="off">
                            </div>
                            <input type="hidden" name="pin" id="combined-pin">
                        </div>
                        
                        <button type="submit" class="reset-btn btn-primary" id="verify-pin-btn" disabled>
                            <i class="bx bx-check"></i>
                            Xác Thực PIN
                        </button>
                    </form>
                    
                    <div class="reset-footer">
                        <div class="resend-section">
                            <p class="resend-text">Không nhận được mã?</p>
                            <a href="/forgot-password" class="resend-link">
                                Gửi lại mã PIN
                            </a>
                        </div>
                        
                        <div class="help-section">
                            <p class="help-text">
                                <i class="bx bx-info-circle"></i>
                                Mã PIN sẽ hết hạn sau 15 phút
                            </p>
                        </div>
                    </div>
                    
                <?php else: ?>
                    <!-- New Password Form -->
                    <form class="reset-form" action="/reset-password?step=new_password" method="POST">
                        <div class="form-group">
                            <label for="new_password" class="form-label">Mật khẩu mới</label>
                            <div class="input-group">
                                <i class="bx bx-lock-alt input-icon"></i>
                                <input type="password" id="new_password" name="new_password" class="form-input" placeholder="Nhập mật khẩu mới" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                    <i class="bx bx-hide" id="new_password-toggle-icon"></i>
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
                            <label for="confirm_password" class="form-label">Xác nhận mật khẩu</label>
                            <div class="input-group">
                                <i class="bx bx-lock-alt input-icon"></i>
                                <input type="password" id="confirm_password" name="confirm_password" class="form-input" placeholder="Xác nhận mật khẩu mới" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <i class="bx bx-hide" id="confirm_password-toggle-icon"></i>
                                </button>
                            </div>
                            <div class="password-match" id="passwordMatch"></div>
                        </div>
                        
                        <div class="password-requirements">
                            <h4>Yêu cầu mật khẩu:</h4>
                            <ul>
                                <li id="req-length">Ít nhất 8 ký tự</li>
                                <li id="req-lowercase">Có chữ thường (a-z)</li>
                                <li id="req-uppercase">Có chữ hoa (A-Z)</li>
                                <li id="req-number">Có số (0-9)</li>
                                <li id="req-special">Có ký tự đặc biệt</li>
                            </ul>
                        </div>
                        
                        <button type="submit" class="reset-btn btn-primary" id="reset-password-btn" disabled>
                            <i class="bx bx-key"></i>
                            Đặt Lại Mật Khẩu
                        </button>
                    </form>
                <?php endif; ?>
                
                <div class="back-section">
                    <a href="/login" class="back-link">
                        <i class="bx bx-arrow-back"></i>
                        Quay lại đăng nhập
                    </a>
                </div>
            </div>
            
            <div class="reset-visual-section">
                <div class="visual-content">
                    <div class="visual-header">
                        <h2><?php echo $step === 'verify_pin' ? 'Bảo Mật Tối Đa' : 'Mật Khẩu Mạnh'; ?></h2>
                        <p><?php echo $step === 'verify_pin' ? 'Xác thực bằng mã PIN để đảm bảo an toàn' : 'Tạo mật khẩu mạnh để bảo vệ tài khoản'; ?></p>
                    </div>
                    
                    <div class="security-features">
                        <div class="security-item">
                            <div class="security-icon">
                                <i class="bx bx-shield-check"></i>
                            </div>
                            <div class="security-text">
                                <h4>Mã Hóa An Toàn</h4>
                                <p>Mật khẩu được mã hóa bằng thuật toán bcrypt</p>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-icon">
                                <i class="bx bx-time-five"></i>
                            </div>
                            <div class="security-text">
                                <h4>Hết Hạn Tự Động</h4>
                                <p>Mã PIN tự động hết hạn để đảm bảo bảo mật</p>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-icon">
                                <i class="bx bx-user-check"></i>
                            </div>
                            <div class="security-text">
                                <h4>Xác Thực Chủ Sở Hữu</h4>
                                <p>Chỉ chủ email mới có thể đặt lại mật khẩu</p>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-icon">
                                <i class="bx bx-history"></i>
                            </div>
                            <div class="security-text">
                                <h4>Lịch Sử Bảo Mật</h4>
                                <p>Theo dõi và ghi nhận mọi thay đổi</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tips-section">
                        <h3>💡 Mẹo Bảo Mật</h3>
                        <ul>
                            <li>Sử dụng mật khẩu duy nhất cho mỗi tài khoản</li>
                            <li>Kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt</li>
                            <li>Tránh sử dụng thông tin cá nhân dễ đoán</li>
                            <li>Cập nhật mật khẩu định kỳ</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
$content = ob_get_clean();
$title = "Đặt Lại Mật Khẩu - CodeJudge";
include VIEW_PATH . '/layouts/pagesNothing.php';
?>

<style>
.reset-section {
    min-height: 100vh;
    background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.reset-container {
    max-width: 1200px;
    width: 100%;
}

.reset-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    min-height: 700px;
}

.reset-form-section {
    padding: 60px 50px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.reset-header {
    text-align: center;
    margin-bottom: 40px;
}

.reset-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, #dc2626, #991b1b);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.reset-icon i {
    font-size: 40px;
    color: white;
}

.reset-title {
    font-size: 32px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 10px;
}

.reset-subtitle {
    color: #718096;
    font-size: 16px;
    line-height: 1.6;
}

.reset-subtitle strong {
    color: #dc2626;
    font-weight: 600;
}

.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 500;
}

.alert-error {
    background-color: #fed7d7;
    color: #c53030;
    border: 1px solid #feb2b2;
}

.alert-success {
    background-color: #c6f6d5;
    color: #2f855a;
    border: 1px solid #9ae6b4;
}

/* PIN Input Styles */
.pin-input-container {
    margin-bottom: 30px;
}

.pin-label {
    display: block;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 15px;
    text-align: center;
}

.pin-inputs {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-bottom: 20px;
}

.pin-digit {
    width: 60px;
    height: 60px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    text-align: center;
    font-size: 24px;
    font-weight: 700;
    color: #2d3748;
    background: #f7fafc;
    transition: all 0.3s ease;
}

.pin-digit:focus {
    outline: none;
    border-color: #dc2626;
    background: white;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.pin-digit.filled {
    border-color: #dc2626;
    background: white;
    color: #dc2626;
}

/* Form Styles */
.form-group {
    margin-bottom: 25px;
}

.form-label {
    display: block;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
}

.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 15px;
    color: #a0aec0;
    font-size: 18px;
    z-index: 2;
}

.form-input {
    width: 100%;
    padding: 15px 45px 15px 50px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 16px;
    background: #f7fafc;
    transition: all 0.3s ease;
}

.form-input:focus {
    outline: none;
    border-color: #dc2626;
    background: white;
    box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.password-toggle {
    position: absolute;
    right: 15px;
    background: none;
    border: none;
    color: #a0aec0;
    cursor: pointer;
    font-size: 18px;
    z-index: 2;
}

.password-strength {
    margin-top: 10px;
}

.strength-bar {
    height: 4px;
    background: #e2e8f0;
    border-radius: 2px;
    overflow: hidden;
    margin-bottom: 5px;
}

.strength-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 2px;
}

.strength-text {
    font-size: 12px;
    font-weight: 500;
}

.password-match {
    margin-top: 8px;
    font-size: 14px;
    font-weight: 500;
}

.password-requirements {
    background: #f7fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 25px;
}

.password-requirements h4 {
    color: #2d3748;
    margin-bottom: 15px;
    font-size: 14px;
    font-weight: 600;
}

.password-requirements ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.password-requirements li {
    padding: 5px 0;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
    color: #718096;
}

.password-requirements li::before {
    content: "✗";
    color: #e53e3e;
    font-weight: bold;
}

.password-requirements li.valid::before {
    content: "✓";
    color: #38a169;
}

.password-requirements li.valid {
    color: #38a169;
}

.reset-btn {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, #dc2626, #991b1b);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.reset-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px rgba(220, 38, 38, 0.3);
}

.reset-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.reset-footer {
    margin-top: 30px;
    text-align: center;
}

.resend-section {
    margin-bottom: 20px;
}

.resend-text {
    color: #718096;
    margin-bottom: 8px;
}

.resend-link {
    color: #dc2626;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}

.resend-link:hover {
    color: #991b1b;
}

.help-section {
    margin-bottom: 20px;
}

.help-text {
    color: #a0aec0;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.back-section {
    border-top: 1px solid #e2e8f0;
    padding-top: 20px;
}

.back-link {
    color: #718096;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: color 0.3s ease;
}

.back-link:hover {
    color: #dc2626;
}

.reset-visual-section {
    background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
    padding: 60px 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.visual-content {
    text-align: center;
    max-width: 400px;
}

.visual-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #2d3748;
    margin-bottom: 15px;
}

.visual-header p {
    color: #718096;
    font-size: 16px;
    margin-bottom: 40px;
}

.security-features {
    margin-bottom: 40px;
}

.security-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    margin-bottom: 25px;
    text-align: left;
}

.security-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #dc2626, #991b1b);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.security-icon i {
    color: white;
    font-size: 24px;
}

.security-text h4 {
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 5px;
    font-size: 16px;
}

.security-text p {
    color: #718096;
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
}

.tips-section {
    background: #fef2f2;
    border: 1px solid #fecaca;
    border-radius: 12px;
    padding: 20px;
    text-align: left;
}

.tips-section h3 {
    color: #991b1b;
    margin-bottom: 15px;
    font-size: 16px;
}

.tips-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.tips-section li {
    color: #dc2626;
    font-size: 14px;
    margin-bottom: 8px;
    padding-left: 15px;
    position: relative;
}

.tips-section li::before {
    content: "•";
    position: absolute;
    left: 0;
    color: #dc2626;
    font-weight: bold;
}

/* Responsive */
@media (max-width: 768px) {
    .reset-content {
        grid-template-columns: 1fr;
        gap: 0;
    }
    
    .reset-visual-section {
        display: none;
    }
    
    .reset-form-section {
        padding: 40px 30px;
    }
    
    .pin-inputs {
        gap: 10px;
    }
    
    .pin-digit {
        width: 45px;
        height: 45px;
        font-size: 18px;
    }
}
</style>

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

document.addEventListener('DOMContentLoaded', function() {
    const step = '<?php echo $step; ?>';
    
    if (step === 'verify_pin') {
        // PIN input handling
        const pinInputs = document.querySelectorAll('.pin-digit');
        const combinedPinInput = document.getElementById('combined-pin');
        const verifyBtn = document.getElementById('verify-pin-btn');
        
        pinInputs.forEach((input, index) => {
            input.addEventListener('input', function(e) {
                const value = e.target.value;
                
                if (!/^\d*$/.test(value)) {
                    e.target.value = '';
                    return;
                }
                
                if (value) {
                    e.target.classList.add('filled');
                    if (index < pinInputs.length - 1) {
                        pinInputs[index + 1].focus();
                    }
                } else {
                    e.target.classList.remove('filled');
                }
                
                updateCombinedPin();
            });
            
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    pinInputs[index - 1].focus();
                }
                
                if (e.key === 'v' && (e.ctrlKey || e.metaKey)) {
                    e.preventDefault();
                    navigator.clipboard.readText().then(text => {
                        const digits = text.replace(/\D/g, '').slice(0, 6);
                        if (digits.length === 6) {
                            pinInputs.forEach((input, i) => {
                                input.value = digits[i] || '';
                                input.classList.toggle('filled', !!digits[i]);
                            });
                            updateCombinedPin();
                            pinInputs[5].focus();
                        }
                    });
                }
            });
        });
        
        function updateCombinedPin() {
            const pinValue = Array.from(pinInputs).map(input => input.value).join('');
            combinedPinInput.value = pinValue;
            verifyBtn.disabled = pinValue.length !== 6;
        }
        
        pinInputs[0].focus();
        
    } else if (step === 'new_password') {
        // Password validation
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const resetBtn = document.getElementById('reset-password-btn');
        const strengthFill = document.getElementById('strengthFill');
        const strengthText = document.getElementById('strengthText');
        const passwordMatch = document.getElementById('passwordMatch');
        
        const requirements = {
            length: document.getElementById('req-length'),
            lowercase: document.getElementById('req-lowercase'),
            uppercase: document.getElementById('req-uppercase'),
            number: document.getElementById('req-number'),
            special: document.getElementById('req-special')
        };
        
        function validatePassword() {
            const password = newPasswordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            
            // Check requirements
            const checks = {
                length: password.length >= 8,
                lowercase: /[a-z]/.test(password),
                uppercase: /[A-Z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[^A-Za-z0-9]/.test(password)
            };
            
            // Update requirement indicators
            Object.keys(checks).forEach(key => {
                requirements[key].classList.toggle('valid', checks[key]);
            });
            
            // Calculate strength
            const validChecks = Object.values(checks).filter(Boolean).length;
            const strengthPercent = (validChecks / 5) * 100;
            
            strengthFill.style.width = strengthPercent + '%';
            
            let strengthColor, strengthTextValue;
            if (validChecks <= 1) {
                strengthColor = '#e53e3e';
                strengthTextValue = 'Rất yếu';
            } else if (validChecks <= 2) {
                strengthColor = '#f56500';
                strengthTextValue = 'Yếu';
            } else if (validChecks <= 3) {
                strengthColor = '#d69e2e';
                strengthTextValue = 'Trung bình';
            } else if (validChecks <= 4) {
                strengthColor = '#38a169';
                strengthTextValue = 'Tốt';
            } else {
                strengthColor = '#2f855a';
                strengthTextValue = 'Rất tốt';
            }
            
            strengthFill.style.backgroundColor = strengthColor;
            strengthText.textContent = strengthTextValue;
            strengthText.style.color = strengthColor;
            
            // Check password match
            if (confirmPassword) {
                if (password === confirmPassword) {
                    passwordMatch.textContent = '✓ Mật khẩu khớp';
                    passwordMatch.style.color = '#38a169';
                } else {
                    passwordMatch.textContent = '✗ Mật khẩu không khớp';
                    passwordMatch.style.color = '#e53e3e';
                }
            } else {
                passwordMatch.textContent = '';
            }
            
            // Enable/disable submit button
            const allValid = Object.values(checks).every(Boolean);
            const passwordsMatch = password === confirmPassword && confirmPassword.length > 0;
            resetBtn.disabled = !(allValid && passwordsMatch);
        }
        
        newPasswordInput.addEventListener('input', validatePassword);
        confirmPasswordInput.addEventListener('input', validatePassword);
    }
});
</script>
