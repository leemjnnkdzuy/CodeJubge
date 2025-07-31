<?php 
$content = ob_start(); 
?>

<section class="error-section">
    <div class="container">
        <div class="error-content">
            <div class="error-text">
                <div class="error-code">404</div>
                <h1 class="error-title">Ôi! Không Tìm Thấy Trang</h1>
                <p class="error-subtitle">
                    Trang bạn đang tìm kiếm có vẻ như đã lạc vào khoảng trống kỹ thuật số. 
                    Đừng lo lắng, ngay cả những thuật toán tốt nhất đôi khi cũng đi sai đường!
                </p>
                <div class="error-buttons">
                    <a href="/" class="btn btn-primary-hero">
                        <i class="bx bx-home"></i>
                        Về Trang Chủ
                    </a>
                    <a href="/problems" class="btn btn-outline">
                        <i class="bx bx-code-alt"></i>
                        Duyệt Bài Tập
                    </a>
                </div>
                <div class="error-suggestions">
                    <h3>Những gì bạn có thể làm:</h3>
                    <ul class="suggestions-list">
                        <li>
                            <i class="bx bx-search"></i>
                            <span>Kiểm tra lỗi chính tả trong URL</span>
                        </li>
                        <li>
                            <i class="bx bx-refresh"></i>
                            <span>Làm mới trang</span>
                        </li>
                        <li>
                            <i class="bx bx-support"></i>
                            <span>Liên hệ đội hỗ trợ của chúng tôi</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="error-image">
                <div class="error-illustration">
                    <div class="code-block">
                        <div class="code-line">
                            <span class="keyword">function</span> 
                            <span class="function-name">findPage</span>() {
                        </div>
                        <div class="code-line indent">
                            <span class="keyword">return</span> 
                            <span class="string">"404: Not Found"</span>;
                        </div>
                        <div class="code-line">}</div>
                        <div class="code-line">
                            <span class="comment">// TODO: Sửa lỗi này! 🐛</span>
                        </div>
                    </div>
                    <div class="floating-icons">
                        <i class="bx bx-bug"></i>
                        <i class="bx bx-error-circle"></i>
                        <i class="bx bx-question-mark"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
$content = ob_get_clean();
$title = "404 - Không Tìm Thấy Trang";
include VIEW_PATH . '/layouts/pagesNothing.php';
?>
