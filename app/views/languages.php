<?php 
$content = ob_start(); 
?>

<section class="languages-section">
    <div class="container">
        <div class="languages-header">
            <div class="languages-icon">
                <i class='bx bx-code-alt'></i>
            </div>
            <h1 class="languages-title">NGÔN NGỮ LẬP TRÌNH ĐƯỢC HỖ TRỢ</h1>
            <p class="languages-subtitle">
                CodeJudge hiện hỗ trợ bốn ngôn ngữ lập trình phổ biến, mỗi ngôn ngữ có ưu thế và ứng dụng riêng. 
                Dưới đây là mô tả chi tiết về từng ngôn ngữ để bạn có thể chọn lựa phù hợp với mục tiêu học tập.
            </p>
            <div class="languages-buttons">
                <a href="/" class="btn btn-primary-hero">
                    <i class="bx bx-home"></i>
                    Về Trang Chủ
                </a>
                <a href="/problems" class="btn btn-outline">
                    <i class="bx bx-code-curly"></i>
                    Thử Giải Bài Tập
                </a>
            </div>
        </div>

        <div class="languages-content">
            <div class="languages-article">
                <div class="languages-intro">
                    <p>
                        Hiện tại, CodeJudge hỗ trợ <strong>4 ngôn ngữ lập trình phổ biến nhất</strong> dùng trong học thuật, thi đấu lập trình, và thực tế công việc. Mỗi ngôn ngữ đều có thế mạnh riêng, phù hợp với các mục tiêu học tập và phát triển khác nhau.
                    </p>
                </div>

                <div class="language-card python">
                    <div class="language-header">
                        <div class="language-icon">
                            <img src="/assets/python_logo.png" alt="Python Logo">
                        </div>
                        <h2>Python</h2>
                        <div class="language-badge">Dễ học</div>
                    </div>
                    <div class="language-content">
                        <p class="language-description">
                            Python là ngôn ngữ đơn giản, dễ học và cực kỳ phổ biến trong lĩnh vực trí tuệ nhân tạo, khoa học dữ liệu, và cả lập trình giải thuật.
                        </p>
                        
                        <h4>Ưu điểm nổi bật:</h4>
                        <ul class="language-features">
                            <li><strong>Cú pháp ngắn gọn, dễ đọc:</strong> Python có cú pháp giống ngôn ngữ tự nhiên, giúp người mới dễ đọc và viết code</li>
                            <li><strong>Thư viện mạnh mẽ:</strong> Thư viện chuẩn lớn (math, itertools, collections) và nhiều thư viện bên thứ ba (NumPy, Pandas)</li>
                            <li><strong>Phù hợp người mới:</strong> Không yêu cầu khai báo biến phức tạp, tập trung vào giải thuật</li>
                            <li><strong>Ứng dụng rộng rãi:</strong> Phát triển web, khoa học dữ liệu, trí tuệ nhân tạo, machine learning</li>
                        </ul>

                        <div class="language-specs">
                            <div class="spec-item">
                                <i class="bx bx-cog"></i>
                                <span><strong>Phiên bản:</strong> Python 3.x</span>
                            </div>
                            <div class="spec-item">
                                <i class="bx bx-book"></i>
                                <span><strong>Tài liệu:</strong> <a href="https://python.org" target="_blank">python.org</a></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="language-card javascript">
                    <div class="language-header">
                        <div class="language-icon">
                            <img src="/assets/javascript-logo.png" alt="JavaScript Logo">
                        </div>
                        <h2>JavaScript</h2>
                        <div class="language-badge">Web-friendly</div>
                    </div>
                    <div class="language-content">
                        <p class="language-description">
                            JavaScript thường dùng cho lập trình web, nhưng cũng có thể sử dụng để giải thuật toán. Chúng tôi hỗ trợ chạy JS trên nền tảng server (Node.js).
                        </p>
                        
                        <h4>Ưu điểm nổi bật:</h4>
                        <ul class="language-features">
                            <li><strong>Gần gũi với lập trình web:</strong> Thích hợp cho người quen viết frontend và muốn thử sức với giải thuật</li>
                            <li><strong>Hàm mảng mạnh mẽ:</strong> Các phương thức như map(), filter(), reduce() giúp viết code ngắn gọn</li>
                            <li><strong>Chạy trên server:</strong> Node.js tích hợp V8 engine tối ưu và thư viện NPM phong phú</li>
                            <li><strong>Dễ tiếp cận:</strong> Không phải học thêm ngôn ngữ mới nếu đã quen với web development</li>
                        </ul>

                        <div class="language-specs">
                            <div class="spec-item">
                                <i class="bx bx-cog"></i>
                                <span><strong>Phiên bản:</strong> Node.js (ES6+)</span>
                            </div>
                            <div class="spec-item">
                                <i class="bx bx-book"></i>
                                <span><strong>Tài liệu:</strong> <a href="https://developer.mozilla.org" target="_blank">developer.mozilla.org</a></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="language-card cpp">
                    <div class="language-header">
                        <div class="language-icon">
                            <img src="/assets/c_c++_logo.png" alt="C++ Logo">
                        </div>
                        <h2>C++</h2>
                        <div class="language-badge">Hiệu suất cao</div>
                    </div>
                    <div class="language-content">
                        <p class="language-description">
                            C++ là lựa chọn số 1 trong các kỳ thi lập trình như ICPC, Olympiad, Codeforces, nhờ tốc độ cực nhanh và khả năng kiểm soát bộ nhớ.
                        </p>
                        
                        <h4>Ưu điểm nổi bật:</h4>
                        <ul class="language-features">
                            <li><strong>Hiệu suất cao:</strong> Ngôn ngữ biên dịch nhanh, kiểm soát bộ nhớ trực tiếp, phù hợp tối ưu thời gian</li>
                            <li><strong>Thư viện STL phong phú:</strong> std::set, std::map, std::priority_queue hỗ trợ cấu trúc dữ liệu mạnh</li>
                            <li><strong>Ưu thế thi đấu:</strong> Ngôn ngữ được ưa chuộng nhất trong các cuộc thi lập trình</li>
                            <li><strong>Kiểm soát tối đa:</strong> Con trỏ, tham chiếu, quản lý vùng nhớ cho hiệu suất tối ưu</li>
                        </ul>

                        <div class="language-specs">
                            <div class="spec-item">
                                <i class="bx bx-cog"></i>
                                <span><strong>Phiên bản:</strong> C++17</span>
                            </div>
                            <div class="spec-item">
                                <i class="bx bx-book"></i>
                                <span><strong>Tài liệu:</strong> <a href="https://cplusplus.com" target="_blank">cplusplus.com</a>, <a href="https://cppreference.com" target="_blank">cppreference.com</a></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="language-card java">
                    <div class="language-header">
                        <div class="language-icon">
                            <img src="/assets/java_logo.png" alt="Java Logo">
                        </div>
                        <h2>Java</h2>
                        <div class="language-badge">OOP</div>
                    </div>
                    <div class="language-content">
                        <p class="language-description">
                            Java là ngôn ngữ hướng đối tượng phổ biến, phù hợp cho cả lập trình ứng dụng và giải thuật. Mạnh mẽ, ổn định, và dễ bảo trì.
                        </p>
                        
                        <h4>Ưu điểm nổi bật:</h4>
                        <ul class="language-features">
                            <li><strong>Cú pháp rõ ràng:</strong> Kiểu mạnh (strong typing), tránh các tính năng rủi ro như con trỏ</li>
                            <li><strong>Thư viện cho thuật toán:</strong> HashMap, TreeSet, PriorityQueue hỗ trợ hiện thực nhanh</li>
                            <li><strong>Thiết kế OOP:</strong> Mã được tổ chức thành lớp, dễ mở rộng và tái sử dụng</li>
                            <li><strong>Đa nền tảng:</strong> "Viết một lần, chạy mọi nơi" với JVM</li>
                        </ul>

                        <div class="language-specs">
                            <div class="spec-item">
                                <i class="bx bx-cog"></i>
                                <span><strong>Phiên bản:</strong> Java 11+</span>
                            </div>
                            <div class="spec-item">
                                <i class="bx bx-book"></i>
                                <span><strong>Tài liệu:</strong> <a href="https://docs.oracle.com" target="_blank">docs.oracle.com</a>, <a href="https://geeksforgeeks.org" target="_blank">geeksforgeeks.org</a></span>
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
$title = "Ngôn Ngữ Lập Trình - CodeJudge";
$description = "Tìm hiểu về 4 ngôn ngữ lập trình được hỗ trợ trên CodeJudge: Python, JavaScript, C++, Java";
include VIEW_PATH . '/layouts/pagesNothing.php';
?>
