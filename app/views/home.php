<?php 
require_once APP_PATH . '/models/UserModel.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

$userModel = new UserModel();
$currentUser = $userModel->getUserById($_SESSION['user_id']);
$userBadges = $userModel->getUserBadges($_SESSION['user_id']);

if (!$currentUser) {
    header('Location: /login');
    exit;
}

$content = ob_start(); 
?>

<div class="profile-container">
    <div class="top-section">
        <div class="welcome-header">
            <h1>Chào mừng, <b><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></b> !</h1>
            <p class="welcome-subtitle">Bạn đang làm rất tốt! Hãy tiếp tục hoặc bắt đầu điều gì đó mới.</p>
        </div>
        
        <div class="right-stats">
            <div class="stat">
                <p>Chuỗi Đăng Nhập</p>
                <strong><?= $currentUser['login_streak'] ?? 0 ?></strong><br>
                <small>ngày <?= ($currentUser['login_streak'] ?? 0) > 0 ? '– <span class="record-text">tuyệt vời!</span>' : '' ?></small>
            </div>
            <div class="stat">
                <p>Tiến Trình Cấp Độ</p>
                <?php if (isset($currentUser['rating']) && $currentUser['rating'] != -1): ?>
                    <div class="circle"><?= min(100, ($currentUser['rating'] - 1200) / 8) ?>%</div>
                    <small>đến Chuyên Gia</small>
                <?php else: ?>
                    <div class="circle">0%</div>
                    <small>Chưa có xếp hạng</small>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bottom-stats">
        <div class="stat">
            <div class="stat-left-column">
                <div class="stat-icon">
                    <i class='bx bx-code-alt'></i>
                </div>
                <div class="stat-bracket">
                    <div class="connection-line"></div>
                </div>
            </div>
            <div class="stat-right-column">
                <p>Bài Tập</p>
                <div class="stat-connection">
                    <strong><?= $currentUser['total_problems_solved'] ?? 0 ?></strong>
                    <small>đã giải quyết</small>
                </div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-left-column">
                <div class="stat-icon">
                    <i class='bx bx-trophy'></i>
                </div>
                <div class="stat-bracket">
                    <div class="connection-line"></div>
                </div>
            </div>
            <div class="stat-right-column">
                <p>Cuộc Thi</p>
                <div class="stat-connection">
                    <strong>0</strong>
                    <small>đã tham gia</small>
                </div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-left-column">
                <div class="stat-icon">
                    <i class='bx bx-send'></i>
                </div>
                <div class="stat-bracket">
                    <div class="connection-line"></div>
                </div>
            </div>
            <div class="stat-right-column">
                <p>Bài Nộp</p>
                <div class="stat-connection">
                    <strong><?= $currentUser['total_submissions'] ?? 0 ?></strong>
                    <small>đã gửi</small>
                </div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-left-column">
                <div class="stat-icon">
                    <i class='bx bx-chat'></i>
                </div>
                <div class="stat-bracket">
                    <div class="connection-line"></div>
                </div>
            </div>
            <div class="stat-right-column">
                <p>Thảo Luận</p>
                <div class="stat-connection">
                    <strong>0</strong>
                    <small>đã đăng</small>
                </div>
            </div>
        </div>
        <div class="stat">
            <div class="stat-left-column">
                <div class="stat-icon">
                    <i class='bx bx-medal'></i>
                </div>
                <div class="stat-bracket">
                    <div class="connection-line"></div>
                </div>
            </div>
            <div class="stat-right-column">
                <p>Thành Tích</p>
                <div class="stat-connection">
                    <strong><?= count($userBadges) ?></strong>
                    <small>đã đạt được</small>
                </div>
            </div>
        </div>
    </div>
    <div class="badges-stats">
        <div class="badges-header">
            <h2>Thành Tích & Huy Hiệu</h2>
            <p>Khám phá và thu thập các huy hiệu bằng cách hoàn thành các thử thách</p>
        </div>
        
        <div class="badges-container">
            <?php 
            global $BADGES;
            
            foreach ($BADGES as $badgeKey => $badge): 
                $isEarned = in_array($badgeKey, $userBadges);
                $badgeClass = $isEarned ? 'earned' : 'unearned';
                $assetPath = 'assets/' . $badge['File'];
            ?>
            <div class="badge-item <?php echo $badgeClass; ?>" data-badge="<?php echo htmlspecialchars($badgeKey); ?>">
                <div class="badge-hexagon">
                    <div class="badge-content">
                        <img src="<?php echo $assetPath; ?>" alt="<?php echo htmlspecialchars($badge['title']); ?>" class="badge-icon">
                    </div>
                </div>
                <div class="badge-title"><?php echo htmlspecialchars($badge['title']); ?></div>
                <div class="badge-tooltip">
                    <strong><?php echo htmlspecialchars($badge['title']); ?></strong><br>
                    <?php echo htmlspecialchars($badge['description']); ?>
                    <?php if (!$isEarned): ?>
                        <br><em style="color: #ffd700;">Chưa đạt được</em>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>
