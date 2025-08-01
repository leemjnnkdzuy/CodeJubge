<?php 
require_once APP_PATH . '/helpers/AvatarHelper.php';
$content = ob_start(); 
?>

<div class="leaderboard-container">
    <div class="leaderboard-list">
        <div class="leaderboard-header">
            <h1><i class="bx bx-trophy"></i> Bảng Xếp Hạng</h1>
            <div class="leaderboard-stats">
                <span class="total-users"><?= number_format($totalUsers ?? 0) ?> thành viên</span>
                <span class="page-info">Trang <?= $currentPage ?? 1 ?>/<?= $totalPages ?? 1 ?></span>
            </div>
        </div>

        <div class="leaderboard-table">
            <div class="table-header">
                <div class="rank-col">Hạng</div>
                <div class="user-col">Người dùng</div>
                <div class="problems-col">Bài giải</div>
                <div class="rating-col">Điểm</div>
                <div class="badges-col">Huy hiệu</div>
            </div>

            <div class="table-body" id="leaderboardTableBody">
                <?php if (!empty($leaderboard)): ?>
                    <?php foreach ($leaderboard as $user): ?>
                        <div class="leaderboard-row" data-user-id="<?= $user['id'] ?>" data-username="<?= htmlspecialchars($user['username']) ?>">
                            <div class="rank-col">
                                <div class="rank-badge rank-<?= $user['rank_tier'] ?>">
                                    #<?= number_format($user['rank']) ?>
                                </div>
                            </div>
                            
                            <div class="user-col">
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <img src="<?= $user['avatar_src'] ?? AvatarHelper::base64ToImageSrc($user['avatar']) ?>" 
                                             alt="<?= htmlspecialchars($user['username']) ?>">
                                    </div>
                                    <div class="user-details">
                                        <div class="username"><?= htmlspecialchars($user['username']) ?></div>
                                        <div class="full-name"><?= htmlspecialchars($user['full_name']) ?></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="problems-col">
                                <div class="problems-solved">
                                    <span class="number"><?= number_format($user['problems_solved']) ?></span>
                                    <span class="label">bài</span>
                                </div>
                                <div class="submissions">
                                    <?= number_format($user['total_submissions']) ?> lần gửi
                                </div>
                            </div>
                            
                            <div class="rating-col">
                                <div class="rating-value <?= $user['rating'] >= 0 ? 'positive' : 'negative' ?>">
                                    <?= $user['rating'] >= 0 ? number_format($user['rating']) : 'Chưa có' ?>
                                </div>
                            </div>
                            
                            <div class="badges-col">
                                <div class="badges-container">
                                    <?php if (!empty($user['badges'])): ?>
                                        <?php $badgeCount = count($user['badges']); ?>
                                        <?php for ($i = 0; $i < min(3, $badgeCount); $i++): ?>
                                            <div class="badge-item">
                                                <img src="/assets/<?= $BADGES[$user['badges'][$i]]['File'] ?? 'default-badge.svg' ?>" 
                                                     alt="<?= $user['badges'][$i] ?>"
                                                     title="<?= $BADGES[$user['badges'][$i]]['title'] ?? ucwords(str_replace('_', ' ', $user['badges'][$i])) ?>">
                                            </div>
                                        <?php endfor; ?>
                                        <?php if ($badgeCount > 3): ?>
                                            <div class="badge-more">+<?= $badgeCount - 3 ?></div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="no-badges">Chưa có huy hiệu</div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-data">
                        <i class="bx bx-trophy"></i>
                        <p>Chưa có dữ liệu xếp hạng</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (($totalPages ?? 1) > 1): ?>
            <div class="pagination">
                <?php if ($hasPrevPage ?? false): ?>
                    <button class="page-btn" onclick="changePage(<?= ($currentPage ?? 1) - 1 ?>)">
                        <i class="bx bx-chevron-left"></i> Trước
                    </button>
                <?php endif; ?>

                <div class="page-numbers">
                    <?php
                    $start = max(1, ($currentPage ?? 1) - 2);
                    $end = min(($totalPages ?? 1), ($currentPage ?? 1) + 2);
                    ?>
                    
                    <?php if ($start > 1): ?>
                        <button class="page-btn" onclick="changePage(1)">1</button>
                        <?php if ($start > 2): ?>
                            <span class="page-dots">...</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $start; $i <= $end; $i++): ?>
                        <button class="page-btn <?= $i == ($currentPage ?? 1) ? 'active' : '' ?>" 
                                onclick="changePage(<?= $i ?>)">
                            <?= $i ?>
                        </button>
                    <?php endfor; ?>

                    <?php if ($end < ($totalPages ?? 1)): ?>
                        <?php if ($end < ($totalPages ?? 1) - 1): ?>
                            <span class="page-dots">...</span>
                        <?php endif; ?>
                        <button class="page-btn" onclick="changePage(<?= $totalPages ?? 1 ?>)">
                            <?= $totalPages ?? 1 ?>
                        </button>
                    <?php endif; ?>
                </div>

                <?php if ($hasNextPage ?? false): ?>
                    <button class="page-btn" onclick="changePage(<?= ($currentPage ?? 1) + 1 ?>)">
                        Sau <i class="bx bx-chevron-right"></i>
                    </button>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="rank-sidebar">
        <div class="rank-tiers">
                <?php 
                $currentRank = $currentRankFilter ?? 'all';
                $rankTiersCopy = $rankTiers ?? [];
                
                if ($currentRank === 'all') {
                    $currentRankInfo = [
                        'name' => 'Tất cả hạng',
                        'range' => 'Hiển thị toàn bộ',
                        'color' => 'var(--primary-blue)',
                        'image' => '/assets/all-ranks.png'
                    ];
                } else {
                    $currentRankInfo = $rankTiersCopy[$currentRank] ?? [
                        'name' => 'Tất cả hạng',
                        'range' => 'Hiển thị toàn bộ',
                        'color' => 'var(--primary-blue)',
                        'image' => '/assets/all-ranks.png'
                    ];
                }
                ?>
                <?php if ($currentRankInfo): ?>
                    <div class="rank-item-single" data-tier="<?= $currentRank ?>">
                        <div class="rank-image">
                            <?php if ($currentRank === 'all'): ?>
                                <div style="width: 120px; height: 120px; background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.5rem;">
                                    ALL
                                </div>
                            <?php else: ?>
                                <img src="<?= $currentRankInfo['image'] ?>" 
                                     alt="<?= $currentRankInfo['name'] ?>"
                                     onerror="this.src='/assets/default-rank.png'">
                            <?php endif; ?>
                        </div>
                        
                        <div class="rank-name" style="color: <?= $currentRankInfo['color'] ?>">
                            <?= $currentRankInfo['name'] ?>
                        </div>
                        
                        <div class="rank-range" style="color: <?= $currentRankInfo['color'] ?>">
                            <?= $currentRankInfo['range'] ?>
                        </div>
                        
                        <button class="rank-change-btn" onclick="showRankSelectionPopup()">
                            <i class="bx bx-chevron-down"></i>
                            Thay đổi hạng
                        </button>
                    </div>
                <?php endif; ?>
        </div>
        
        <div id="rankSelectionPopup" class="rank-popup" style="display: none;">
            <div class="popup-content">
                <div class="popup-header">
                    <h4>Chọn Hạng Để Lọc</h4>
                    <button class="close-btn" onclick="closeRankSelectionPopup()">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="popup-body">
                    <div class="rank-selection-list">
                        <div class="rank-selection-item" 
                             data-tier="all"
                             onclick="selectRank('all', 'Tất cả hạng', 'var(--primary-blue)', 'Hiển thị toàn bộ', '/assets/all-ranks.png')">
                            <div class="rank-image-small">
                                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem;">
                                    ALL
                                </div>
                            </div>
                            <div class="rank-info-small">
                                <div class="rank-name-small" style="color: var(--primary-blue);">
                                    Tất cả hạng
                                </div>
                                <div class="rank-range-small" style="color: var(--primary-blue);">
                                    Hiển thị toàn bộ
                                </div>
                            </div>
                            <div class="rank-select-icon">
                                <i class="bx bx-chevron-right"></i>
                            </div>
                        </div>
                        
                        <?php foreach (($rankTiers ?? []) as $tier => $info): ?>
                            <div class="rank-selection-item" 
                                 data-tier="<?= $tier ?>"
                                 onclick="selectRank('<?= $tier ?>', '<?= addslashes($info['name']) ?>', '<?= $info['color'] ?>', '<?= addslashes($info['range']) ?>', '<?= $info['image'] ?>')">
                                <div class="rank-image-small">
                                    <img src="<?= $info['image'] ?>" 
                                         alt="<?= $info['name'] ?>"
                                         onerror="this.src='/assets/default-rank.png'">
                                </div>
                                <div class="rank-info-small">
                                    <div class="rank-name-small" style="color: <?= $info['color'] ?>">
                                        <?= $info['name'] ?>
                                    </div>
                                    <div class="rank-range-small" style="color: <?= $info['color'] ?>">
                                        <?= $info['range'] ?>
                                    </div>
                                </div>
                                <div class="rank-select-icon">
                                    <i class="bx bx-chevron-right"></i>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
$content = ob_get_clean();
$title = "Bảng Xếp Hạng - CodeJudge";
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>

<script>
let selectedRankTier = '';

function showRankSelectionPopup() {
    const popup = document.getElementById('rankSelectionPopup');
    popup.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeRankSelectionPopup() {
    const popup = document.getElementById('rankSelectionPopup');
    popup.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function selectRank(tier, name, color, range, imageUrl) {
    selectedRankTier = tier;
    
    // Update the displayed rank
    updateCurrentRankDisplay(tier, name, color, range, imageUrl);
    
    // Apply the filter
    filterByRank(tier);
    
    // Close the popup
    closeRankSelectionPopup();
}

function updateCurrentRankDisplay(tier, name, color, range, imageUrl) {
    const rankItem = document.querySelector('.rank-item-single');
    if (rankItem) {
        rankItem.setAttribute('data-tier', tier);
        
        const rankName = rankItem.querySelector('.rank-name');
        const rankRange = rankItem.querySelector('.rank-range');
        const rankImage = rankItem.querySelector('.rank-image img');
        
        if (rankName) {
            rankName.textContent = name;
            // Handle CSS variable colors
            if (color.startsWith('var(')) {
                rankName.style.color = '';
                rankName.style.setProperty('color', color);
            } else {
                rankName.style.color = color;
            }
        }
        
        if (rankRange) {
            rankRange.textContent = range;
            // Handle CSS variable colors
            if (color.startsWith('var(')) {
                rankRange.style.color = '';
                rankRange.style.setProperty('color', color);
            } else {
                rankRange.style.color = color;
            }
        }
        
        if (rankImage) {
            // Handle special case for "all" option
            if (tier === 'all') {
                // Create a div element to replace the image for "all" option
                const imageContainer = rankImage.parentElement;
                imageContainer.innerHTML = `
                    <div style="width: 100%; height: 100%; background: linear-gradient(135deg, var(--primary-blue), var(--secondary-green)); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.2rem;">
                        ALL
                    </div>
                `;
            } else if (imageUrl) {
                rankImage.src = imageUrl;
                rankImage.alt = name;
            }
        }
    }
}

// Close popup when clicking outside
document.addEventListener('DOMContentLoaded', function() {
    const popup = document.getElementById('rankSelectionPopup');
    if (popup) {
        popup.addEventListener('click', function(e) {
            if (e.target === this) {
                closeRankSelectionPopup();
            }
        });
    }
});

// Close popup with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRankSelectionPopup();
    }
});

// Existing functions (placeholder - you may need to implement these)
function filterByRank(tier) {
    // Create URL with rank filter parameter
    const currentUrl = new URL(window.location);
    
    if (tier === 'all') {
        // Remove rank filter - show all ranks
        currentUrl.searchParams.delete('rank');
    } else {
        // Add rank filter
        currentUrl.searchParams.set('rank', tier);
    }
    
    // Reset to first page when filtering
    currentUrl.searchParams.delete('page');
    
    // Reload page with new filter
    window.location.href = currentUrl.toString();
}

function changePage(page) {
    // Get current URL and update page parameter
    const currentUrl = new URL(window.location);
    currentUrl.searchParams.set('page', page);
    
    // Reload page with new page number
    window.location.href = currentUrl.toString();
}

function scrollToTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Handle leaderboard row clicks to navigate to user profile
document.addEventListener('DOMContentLoaded', function() {
    const leaderboardRows = document.querySelectorAll('.leaderboard-row');
    
    leaderboardRows.forEach(row => {
        row.addEventListener('click', function(e) {
            // Prevent navigation if clicking on interactive elements
            if (e.target.closest('button') || e.target.closest('a')) {
                return;
            }
            
            const username = this.getAttribute('data-username');
            if (username) {
                // Add loading animation
                this.style.opacity = '0.7';
                this.style.transform = 'scale(0.98)';
                
                // Navigate to user profile
                setTimeout(() => {
                    window.location.href = `/user/${username}`;
                }, 150);
            }
        });
        
        // Add hover effect
        row.style.cursor = 'pointer';
    });
});
</script>