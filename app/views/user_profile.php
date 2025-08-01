<?php 
require_once APP_PATH . '/helpers/AvatarHelper.php';
require_once dirname(APP_PATH) . '/config/config.php';

$viewingUser = $user ?? null;
$isOwnProfile = false;

if (!$viewingUser) {
    header('Location: /leaderboard');
    exit;
}

function getUserRank($rating) {
    global $RANKING;
    
    if ($rating == -1 || $rating < 0) {
        return [
            'name' => 'Chưa Xếp Hạng',
            'icon' => 'rank_Unranked.png',
            'color' => '#6c757d'
        ];
    }
    
    $rankColors = [
        'Iron' => '#6c757d',
        'Bronze' => '#cd7f32',
        'Silver' => '#666666ff',
        'Gold' => '#ffd700',
        'Platinum' => '#00a78bff',
        'Diamond' => '#b30fffff',
        'Ascendant' => '#00c462ff',
        'Immortal' => '#7e0000ff',
        'Radiant' => '#fff345ff'
    ];
    
    foreach ($RANKING as $rankKey => $rankData) {
        if ($rating >= $rankData['min_rating'] && $rating <= $rankData['max_rating']) {
            $rankType = explode('_', $rankKey)[0];
            $color = $rankColors[$rankType] ?? '#4285f4';
            
            return [
                'name' => $rankData['name'],
                'icon' => $rankData['icon'],
                'color' => $color
            ];
        }
    }
    
    return [
        'name' => 'Chưa Xếp Hạng',
        'icon' => 'rank_Unranked.png',
        'color' => '#6c757d'
    ];
}

$userBadges = [];
if (!empty($user_badges)) {
    foreach ($user_badges as $badge) {
        $userBadges[] = $badge['id'];
    }
}


$content = ob_start(); 
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-main-info">
            <div class="avatar-container">
                <img src="<?= AvatarHelper::base64ToImageSrc($viewingUser['avatar']) ?>" 
                     alt="Avatar" class="profile-avatar">
            </div>
            
            <div class="profile-info">
                <h1 class="profile-name">
                    <?= htmlspecialchars($viewingUser['first_name'] . ' ' . $viewingUser['last_name']) ?>
                </h1>
                <p class="profile-username">@<?= htmlspecialchars($viewingUser['username']) ?></p>
            </div>
        </div>
        
        <div class="rank-display">
            <?php 
            $userRank = getUserRank($viewingUser['rating'] ?? -1);
            ?>
            <div class="rank-icon">
                <img src="/assets/<?= $userRank['icon'] ?>" alt="<?= $userRank['name'] ?>" class="rank-image">
            </div>
            <div class="rank-info">
                <span class="rank-name" style="color: <?= $userRank['color'] ?>"><?= $userRank['name'] ?></span>
                <span class="rank-rating"><?= isset($viewingUser['rating']) && $viewingUser['rating'] != -1 ? $viewingUser['rating'] . ' CJP' : 'Chưa có xếp hạng' ?></span>
            </div>
        </div>
        
        <div class="profile-stats">
            <div class="stat-item">
                <span class="stat-number"><?= $viewingUser['total_problems_solved'] ?? 0 ?></span>
                <span class="stat-label">Bài giải</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= $viewingUser['login_streak'] ?? 0 ?></span>
                <span class="stat-label">Streak</span>
            </div>
            <div class="stat-item">
                <span class="stat-number"><?= count($userBadges) ?></span>
                <span class="stat-label">Badges</span>
            </div>
        </div>
    </div>
    
    <div class="profile-content">
        <div class="profile-section">
            <h2 class="section-title">Giới thiệu</h2>
            <div class="bio-content">
                <?php if (!empty($viewingUser['bio'])): ?>
                    <p><?= nl2br(htmlspecialchars($viewingUser['bio'])) ?></p>
                <?php else: ?>
                    <p class="no-content">Người dùng này chưa có giới thiệu.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($viewingUser['github_url']) || !empty($viewingUser['linkedin_url']) || !empty($viewingUser['website_url'])): ?>
        <div class="profile-section">
            <h2 class="section-title">Liên kết</h2>
            <div class="links-container">
                <?php if (!empty($viewingUser['github_url'])): ?>
                <a href="<?= htmlspecialchars($viewingUser['github_url']) ?>" target="_blank" class="link-item">
                    <i class='bx bxl-github'></i>
                    <span>GitHub</span>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($viewingUser['linkedin_url'])): ?>
                <a href="<?= htmlspecialchars($viewingUser['linkedin_url']) ?>" target="_blank" class="link-item">
                    <i class='bx bxl-linkedin'></i>
                    <span>LinkedIn</span>
                </a>
                <?php endif; ?>
                
                <?php if (!empty($viewingUser['website_url'])): ?>
                <a href="<?= htmlspecialchars($viewingUser['website_url']) ?>" target="_blank" class="link-item">
                    <i class='bx bx-link'></i>
                    <span>Website</span>
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="profile-section">
            <h2 class="section-title">Thống kê</h2>
            <div class="stats-grid">
                <?php if (isset($stats['difficulty']) && !empty($stats['difficulty'])): ?>
                    <?php foreach ($stats['difficulty'] as $difficulty): ?>
                        <div class="stat-card">
                            <span class="stat-value"><?= $difficulty['solved_count'] ?></span>
                            <span class="stat-desc"><?= ucfirst($difficulty['difficulty']) ?> solved</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (isset($stats['submissions']) && !empty($stats['submissions'])): ?>
                    <?php foreach ($stats['submissions'] as $submission): ?>
                        <div class="stat-card">
                            <span class="stat-value"><?= $submission['count'] ?></span>
                            <span class="stat-desc"><?= ucfirst($submission['status']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="profile-section">
            <h2 class="section-title">Huy hiệu & Thành tích</h2>
            <div class="badges-grid">
                <?php 
                global $BADGES;
                $earnedCount = 0;
                
                foreach ($BADGES as $badgeKey => $badge): 
                    $isEarned = in_array($badgeKey, $userBadges);
                    if ($isEarned) $earnedCount++;
                    $badgeClass = $isEarned ? 'earned' : 'unearned';
                    $assetPath = '/assets/' . $badge['File'];
                ?>
                <div class="badge-item <?= $badgeClass ?>" title="<?= htmlspecialchars($badge['description']) ?>">
                    <img src="<?= $assetPath ?>" alt="<?= htmlspecialchars($badge['title']) ?>" class="badge-icon">
                    <span class="badge-title"><?= htmlspecialchars($badge['title']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if ($earnedCount === 0): ?>
            <p class="no-content">
                Người dùng này chưa có huy hiệu nào.
            </p>
            <?php endif; ?>
        </div>
        
        <?php if (isset($recent_submissions) && !empty($recent_submissions)): ?>
        <div class="profile-section">
            <h2 class="section-title">Submissions gần đây</h2>
            <div class="submissions-list">
                <?php foreach ($recent_submissions as $submission): ?>
                    <div class="submission-item">
                        <div class="submission-info">
                            <span class="problem-title"><?= htmlspecialchars($submission['problem_title']) ?></span>
                            <span class="submission-status status-<?= strtolower($submission['status']) ?>">
                                <?= ucfirst($submission['status']) ?>
                            </span>
                        </div>
                        <div class="submission-meta">
                            <span class="language"><?= htmlspecialchars($submission['language']) ?></span>
                            <span class="time"><?= date('d/m/Y H:i', strtotime($submission['submitted_at'])) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php 
$content = ob_get_clean();
$title = "Hồ sơ - " . htmlspecialchars($viewingUser['username']) . " - CodeJudge";
$css = '/css/userStyle.css';
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>
