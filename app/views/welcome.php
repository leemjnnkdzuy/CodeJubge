<?php 
$content = ob_start(); 
?>

<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">Nâng tầm với cộng đồng lập trình và chấm tự động lớn nhất</h1>
                <p class="hero-subtitle">
                    Tham gia cùng hơn 25 triệu lập trình viên và sinh viên để thực hành, chia sẻ và kiểm tra giải pháp của bạn.
                    Luôn cập nhật với các thuật toán và công nghệ mới nhất. Khám phá thư viện khổng lồ các bài tập, giải pháp mẫu và hệ thống chấm tự động cho tất cả các dự án học tập của bạn.
                </p>
                <?php if (!isset($_SESSION['user_id'])): ?>
                <div class="hero-buttons">
                    <a href="/login" class="btn btn-primary-hero">Đăng Nhập</a>
                    <a href="/register" class="btn btn-outline">Đăng Ký</a>
                </div>
                <?php else: ?>
                <div class="hero-buttons">
                    <a href="/home" class="btn btn-primary-hero">Vào Trang Chủ</a>
                    <a href="/profile" class="btn btn-outline">Hồ Sơ Của Tôi</a>
                </div>
                <?php endif; ?>
            </div>
            <div class="hero-image">
                <img src="/assets/home_image_1.png" alt="Coding Community" class="hero-img">
            </div>
        </div>
    </div>
</section>

<section class="about-section">
    <div class="container">
        <div class="about-header">
            <div class="about-header-content">
                <h2 class="about-title">Ai đang sử dụng CodeJudge?</h2>
                <button class="see-more-btn-main" onclick="toggleAllFeatures(this)">Xem thêm</button>
            </div>
        </div>
        
        <div class="about-grid">
            <div class="about-card">
                <div class="about-card-header">
                    <div class="about-text-section">
                        <h3 class="about-card-title">Sinh Viên</h3>
                        <p class="about-card-description">
                            Thành thạo thuật toán và cấu trúc dữ liệu thông qua các lộ trình học tập có cấu trúc và thử thách lập trình.
                        </p>
                    </div>
                    <div class="about-image">
                        <img src="/assets/home_image_2.png" alt="Students" class="about-img">
                    </div>
                </div>
                
                <div class="about-features" style="display: none;">
                    <h4 class="features-title">TÍNH NĂNG CHÍNH</h4>
                    <ul class="features-list">
                        <li class="feature-item">
                            <span class="feature-icon"><i class="bx bx-medal"></i></span>
                            <span class="feature-text">Cuộc thi lập trình tương tác</span>
                        </li>
                        <li class="feature-item">
                            <span class="feature-icon"><i class="bx bx-book-open"></i></span>
                            <span class="feature-text">Hướng dẫn từng bước</span>
                        </li>
                        <li class="feature-item">
                            <span class="feature-icon"><i class="bx bx-folder"></i></span>
                            <span class="feature-text">Bộ bài tập thực hành</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="about-card">
                <div class="about-card-header">
                    <div class="about-text-section">
                        <h3 class="about-card-title">Lập Trình Viên</h3>
                        <p class="about-card-description">
                            Rèn luyện kỹ năng lập trình và chuẩn bị cho phỏng vấn kỹ thuật với các thử thách thực tế.
                        </p>
                    </div>
                    <div class="about-image">
                        <img src="/assets/home_image_3.png" alt="Developers" class="about-img">
                    </div>
                </div>
                
                <div class="about-features" style="display: none;">
                    <h4 class="features-title">TÍNH NĂNG CHÍNH</h4>
                    <ul class="features-list">
                        <li class="feature-item">
                            <span class="feature-icon"><i class="bx bx-code-alt"></i></span>
                            <span class="feature-text">Nhiều ngôn ngữ lập trình</span>
                        </li>
                        <li class="feature-item">
                            <span class="feature-icon"><i class="bx bx-timer"></i></span>
                            <span class="feature-text">Thử thách có thời gian</span>
                        </li>
                        <li class="feature-item">
                            <span class="feature-icon"><i class="bx bx-bar-chart"></i></span>
                            <span class="feature-text">Phân tích hiệu suất</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="about-card">
                <div class="about-card-header">
                    <div class="about-text-section">
                        <h3 class="about-card-title">Công Ty</h3>
                        <p class="about-card-description">
                            Tổ chức cuộc thi lập trình và khám phá các lập trình viên tài năng cho đội ngũ của bạn.
                        </p>
                    </div>
                    <div class="about-image">
                        <img src="/assets/home_image_4.png" alt="Companies" class="about-img">
                    </div>
                </div>
                
                <div class="about-features" style="display: none;">
                    <h4 class="features-title">TÍNH NĂNG CHÍNH</h4>
                    <ul class="features-list">
                        <li class="feature-item">
                            <span class="feature-icon"><i class="bx bx-trophy"></i></span>
                            <span class="feature-text">Tổ chức cuộc thi tùy chỉnh</span>
                        </li>
                        <li class="feature-item">
                            <span class="feature-icon"><i class="bx bx-user-check"></i></span>
                            <span class="feature-text">Công cụ tuyển dụng nhân tài</span>
                        </li>
                        <li class="feature-item">
                            <span class="feature-icon"><i class="bx bx-pie-chart"></i></span>
                            <span class="feature-text">Báo cáo ứng viên chi tiết</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="languages-section">
    <div class="container">
        <div class="section-header">
            <h2 class="section-title">Ngôn Ngữ Lập Trình Được Hỗ Trợ</h2>
            <p class="section-subtitle">
                Viết giải pháp bằng ngôn ngữ lập trình ưa thích của bạn với sự hỗ trợ toàn diện của chúng tôi
            </p>
        </div>
        
        <div class="languages-grid">
            <div class="language-card">
                <div class="language-icon">
                    <img src="/assets/python_logo.png" alt="Python" class="language-logo">
                </div>
                <div class="language-info">
                    <h3 class="language-name">Python</h3>
                    <p class="language-description">Hoàn hảo cho thuật toán và khoa học dữ liệu</p>
                    <div class="language-features">
                        <span class="feature-tag">Cú pháp dễ</span>
                        <span class="feature-tag">Thư viện phong phú</span>
                    </div>
                </div>
            </div>
            
            <div class="language-card">
                <div class="language-icon">
                    <img src="/assets/javascript-logo.png" alt="JavaScript" class="language-logo">
                </div>
                <div class="language-info">
                    <h3 class="language-name">JavaScript</h3>
                    <p class="language-description">Linh hoạt và đa dụng cho mọi lĩnh vực</p>
                    <div class="language-features">
                        <span class="feature-tag">Linh hoạt</span>
                        <span class="feature-tag">Hiện đại</span>
                    </div>
                </div>
            </div>
            
            <div class="language-card">
                <div class="language-icon">
                    <img src="/assets/c_c++_logo.png" alt="C/C++" class="language-logo">
                </div>
                <div class="language-info">
                    <h3 class="language-name">C/C++</h3>
                    <p class="language-description">Lập trình thi đấu hiệu năng cao</p>
                    <div class="language-features">
                        <span class="feature-tag">Nhanh</span>
                        <span class="feature-tag">Hiệu quả</span>
                    </div>
                </div>
            </div>
            
            <div class="language-card">
                <div class="language-icon">
                    <img src="/assets/java_logo.png" alt="Java" class="language-logo">
                </div>
                <div class="language-info">
                    <h3 class="language-name">Java</h3>
                    <p class="language-description">Lập trình hướng đối tượng cấp doanh nghiệp</p>
                    <div class="language-features">
                        <span class="feature-tag">Mạnh mẽ</span>
                        <span class="feature-tag">Có thể mở rộng</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="languages-footer">
            <p class="languages-note">
                <i class="bx bx-info-circle"></i>
                Không thấy ngôn ngữ yêu thích của bạn? Chúng tôi liên tục thêm hỗ trợ cho nhiều ngôn ngữ lập trình hơn.
            </p>
            <a href="/languages" class="btn btn-outline">Xem Tất Cả Ngôn Ngữ</a>
        </div>
    </div>
</section>

<section class="cta-section">
    <div class="container">
        <div class="cta-content">
            <h2 class="cta-title">Sẵn sàng Bắt Đầu Lập Trình?</h2>
            <p class="cta-text">
                Tham gia cùng hàng nghìn lập trình viên đang cải thiện kỹ năng mỗi ngày. Bắt đầu giải quyết bài toán ngay!
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="/register" class="btn btn-primary btn-lg">Tạo Tài Khoản</a>
                <?php else: ?>
                <a href="/profile" class="btn btn-primary btn-lg">Xem Hồ Sơ</a>
                <?php endif; ?>
                <a href="/home" class="btn btn-outline btn-lg">Duyệt Bài Toán</a>
            </div>
        </div>
    </div>
</section>

<?php 
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesWithHeader.php';
?>

<script>
function toggleAllFeatures(button) {
    const allFeatures = document.querySelectorAll('.about-features');
    
    const anyVisible = Array.from(allFeatures).some(features => 
        features.style.display === 'block'
    );
    
    if (anyVisible) {
        allFeatures.forEach(features => {
            features.style.display = 'none';
        });
        button.textContent = 'Xem thêm';
    } else {
        allFeatures.forEach(features => {
            features.style.display = 'block';
        });
        button.textContent = 'Ẩn bớt';
    }
}
</script>