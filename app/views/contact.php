<?php 
$content = ob_start(); 
?>

<section class="docs-section">
    <div class="container">
        <div class="docs-content">
            <div class="docs-text">
                <div class="docs-icon">
                    <i class='bx bx-support'></i>
                </div>
                <h1 class="docs-title">Liên Hệ Hỗ Trợ</h1>
                <p class="docs-subtitle">
                    Cần hỗ trợ hoặc có câu hỏi về CodeJudge? 
                    Đội ngũ của chúng tôi luôn sẵn sàng giúp đỡ bạn!
                </p>
                <div class="docs-buttons">
                    <a href="/" class="btn btn-primary-hero">
                        <i class="bx bx-home"></i>
                        Về Trang Chủ
                    </a>
                    <a href="mailto:support@codejudge.com" class="btn btn-outline">
                        <i class="bx bx-envelope"></i>
                        Gửi Email
                    </a>
                </div>
                <div class="docs-info">
                    <h3>Các cách liên hệ:</h3>
                    <ul class="docs-list">
                        <li>
                            <i class="bx bx-envelope"></i>
                            <span>Email: support@codejudge.com</span>
                        </li>
                        <li>
                            <i class="bx bxl-discord-alt"></i>
                            <span>Discord: discord.gg/aagSgZpe</span>
                        </li>
                        <li>
                            <i class="bx bxl-github"></i>
                            <span>GitHub Issues: github.com/leemjnnkdzuy/CodeJubge</span>
                        </li>
                        <li>
                            <i class="bx bx-time"></i>
                            <span>Thời gian hỗ trợ: 24/7</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="docs-image">
                <div class="docs-illustration">
                    <div class="code-block">
                        <div class="code-line">
                            <span class="keyword">class</span> 
                            <span class="function-name">SupportTeam</span> {
                        </div>
                        <div class="code-line indent">
                            <span class="keyword">async</span> <span class="function-name">respondToUser</span>() {
                        </div>
                        <div class="code-line indent indent">
                            <span class="keyword">return</span> <span class="string">"Chúng tôi sẽ hỗ trợ!"</span>;
                        </div>
                        <div class="code-line indent">}
                        </div>
                        <div class="code-line">}</div>
                        <div class="code-line">
                            <span class="comment">// Luôn sẵn sàng hỗ trợ bạn 💬</span>
                        </div>
                    </div>
                    <div class="floating-icons">
                        <i class="bx bx-support"></i>
                        <i class="bx bx-message-dots"></i>
                        <i class="bx bx-help-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php 
$content = ob_get_clean();
$title = "Liên Hệ Hỗ Trợ - CodeJudge";
$description = "Liên hệ với đội ngũ hỗ trợ của CodeJudge để được giúp đỡ và giải đáp thắc mắc";
include VIEW_PATH . '/layouts/pagesNothing.php';
?>
