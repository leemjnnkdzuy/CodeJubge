<?php
$title = 'Admin Login - CodeJudge';
$description = 'Đăng nhập vào trang quản trị CodeJudge';

ob_start();
?>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <h1>CodeJudge</h1>
                <span class="admin-badge">Admin</span>
            </div>
            <h2>Đăng nhập quản trị</h2>
            <p>Vui lòng đăng nhập bằng tài khoản admin</p>
        </div>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       placeholder="admin@codejudge.com" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" required 
                       placeholder="Nhập mật khẩu admin">
            </div>
            
            <div class="form-options">
                <label class="checkbox-container">
                    <input type="checkbox" name="remember" <?= isset($_POST['remember']) ? 'checked' : '' ?>>
                    <span class="checkmark"></span>
                    Ghi nhớ đăng nhập
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-full">
                Đăng nhập
            </button>
        </form>
        
        <div class="auth-footer">
            <a href="/" class="back-to-site">
                <i class='bx bx-arrow-back'></i>
                Về trang chủ
            </a>
        </div>
    </div>
</div>

<style>
.auth-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--spacing-lg);
}

.auth-card {
    background: var(--white);
    border-radius: var(--radius-xl);
    box-shadow: var(--shadow-heavy);
    padding: var(--spacing-xxl);
    width: 100%;
    max-width: 400px;
}

.auth-header {
    text-align: center;
    margin-bottom: var(--spacing-xl);
}

.auth-logo {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: var(--spacing-sm);
    margin-bottom: var(--spacing-lg);
}

.auth-logo h1 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-blue);
    margin: 0;
}

.admin-badge {
    background: var(--secondary-orange);
    color: var(--white);
    padding: var(--spacing-xs) var(--spacing-sm);
    border-radius: var(--radius-sm);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.auth-header h2 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--spacing-xs);
}

.auth-header p {
    color: var(--text-secondary);
    margin: 0;
}

.auth-form {
    margin-bottom: var(--spacing-lg);
}

.form-group {
    margin-bottom: var(--spacing-lg);
}

.form-group label {
    display: block;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: var(--spacing-xs);
}

.form-group input {
    width: 100%;
    padding: var(--spacing-md);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-md);
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-blue);
    box-shadow: 0 0 0 3px rgba(32, 190, 255, 0.1);
}

.form-options {
    margin-bottom: var(--spacing-lg);
}

.checkbox-container {
    position: relative;
    padding-left: 2rem;
    cursor: pointer;
    font-size: 0.875rem;
    color: var(--text-secondary);
    display: block;
}

.checkbox-container input {
    position: absolute;
    opacity: 0;
    cursor: pointer;
    height: 0;
    width: 0;
}

.checkmark {
    position: absolute;
    top: 0;
    left: 0;
    height: 1.25rem;
    width: 1.25rem;
    background-color: var(--white);
    border: 2px solid var(--border-color);
    border-radius: var(--radius-sm);
    transition: all 0.3s ease;
}

.checkbox-container:hover input ~ .checkmark {
    border-color: var(--primary-blue);
}

.checkbox-container input:checked ~ .checkmark {
    background-color: var(--primary-blue);
    border-color: var(--primary-blue);
}

.checkmark:after {
    content: "";
    position: absolute;
    display: none;
}

.checkbox-container input:checked ~ .checkmark:after {
    display: block;
}

.checkbox-container .checkmark:after {
    left: 0.25rem;
    top: 0.125rem;
    width: 0.25rem;
    height: 0.5rem;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.btn {
    border: none;
    border-radius: var(--radius-md);
    padding: var(--spacing-md) var(--spacing-lg);
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: var(--primary-blue);
    color: var(--white);
}

.btn-primary:hover {
    background: var(--primary-blue-dark);
    transform: translateY(-1px);
    box-shadow: var(--shadow-medium);
}

.btn-full {
    width: 100%;
}

.auth-footer {
    text-align: center;
    padding-top: var(--spacing-lg);
    border-top: 1px solid var(--border-color);
}

.back-to-site {
    color: var(--text-secondary);
    text-decoration: none;
    font-size: 0.875rem;
    display: inline-flex;
    align-items: center;
    gap: var(--spacing-xs);
    transition: color 0.3s ease;
}

.back-to-site:hover {
    color: var(--primary-blue);
}

@media (max-width: 480px) {
    .auth-card {
        padding: var(--spacing-lg);
        margin: var(--spacing-md);
    }
    
    .auth-container {
        padding: var(--spacing-md);
    }
}
</style>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesNothing.php';
?>
