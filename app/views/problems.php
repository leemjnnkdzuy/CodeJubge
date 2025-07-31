<?php
$title = 'Problems - CodeJudge';
$description = 'Explore coding problems to practice your programming skills';

// Load TYPE_PROBLEM configuration for displaying names
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../helpers/ProblemHelper.php';

ob_start();
?>

<div class="problems-header-sticky">
    <div class="problems-header-content">
        <div class="problems-header-text">
            <h1>Bài Tập</h1>
            <p>Luyện tập các bài toán lập trình để nâng cao kỹ năng của bạn</p>
        </div>
        <div class="problems-header-search">
            <div class="search-container">
                <input type="text" id="searchInput" class="search-input" placeholder="Tìm kiếm bài toán...">
                <button id="searchBtn" class="search-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="m21 21-4.35-4.35"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="problems-container">
    <div class="problems-main">
        
        <div class="problems-list">
            <?php if (!empty($problems)): ?>
                <?php foreach ($problems as $problem): ?>
                    <div class="problem-item" data-id="<?= $problem['id'] ?>" data-slug="<?= htmlspecialchars($problem['slug']) ?>">
                        <div class="problem-info">
                            <h3 class="problem-title"><?= htmlspecialchars($problem['title']) ?></h3>
                            <p class="problem-description"><?= htmlspecialchars(substr($problem['description'], 0, 150)) ?>...</p>
                            <div class="problem-tags">
                                <?= ProblemHelper::formatProblemTypeTags($problem['problem_types'], 4) ?>
                            </div>
                        </div>
                        <div class="problem-meta">
                            <span class="difficulty <?= strtolower($problem['difficulty']) ?>"><?= ProblemHelper::formatDifficulty($problem['difficulty']) ?></span>
                            <span class="solved-count"><?= ProblemHelper::formatSolvedCount($problem['solved_count']) ?> đã giải</span>
                            <?php if (isset($problem['user_status']) && $problem['user_status'] === 'solved'): ?>
                                <span class="status-badge solved">✓ Đã giải</span>
                            <?php elseif (isset($problem['user_status']) && $problem['user_status'] === 'attempted'): ?>
                                <span class="status-badge attempted">⟳ Đã thử</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-problems">
                    <i class='bx bx-search'></i>
                    <h3>Không tìm thấy bài toán nào</h3>
                    <p>Thử thay đổi bộ lọc hoặc từ khóa tìm kiếm</p>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="pagination">
            <button class="pagination-btn" <?= $pagination['current_page'] <= 1 ? 'disabled' : '' ?> 
                    onclick="changePage(<?= max(1, $pagination['current_page'] - 1) ?>)">
                <i class='bx bx-chevrons-left'></i>
            </button>
            <span class="pagination-info">
                Trang <?= $pagination['current_page'] ?> / <?= $pagination['total_pages'] ?> 
                (<?= number_format($pagination['total_items']) ?> bài)
            </span>
            <button class="pagination-btn" <?= $pagination['current_page'] >= $pagination['total_pages'] ? 'disabled' : '' ?>
                    onclick="changePage(<?= min($pagination['total_pages'], $pagination['current_page'] + 1) ?>)">
                <i class='bx bx-chevrons-right'></i>
            </button>
        </div>
    </div>
    
    <div class="problems-sidebar">
        <div class="problem-types-section">
            <h3>Loại Bài Toán</h3>
            <div class="problem-types-container">
                <div class="problem-types-list" id="problemTypesList">
                    <?php 
                    $count = 0;
                    if (isset($TYPE_PROBLEM) && is_array($TYPE_PROBLEM)): 
                        foreach ($TYPE_PROBLEM as $key => $type): 
                            $isHidden = $count >= 10 ? 'hidden' : '';
                            $iconClass = isset($type['icon']) ? $type['icon'] : 'bx-code';
                            $name = isset($type['name']) ? $type['name'] : ucfirst(str_replace('_', ' ', $key));
                        ?>
                            <div class="problem-type-item <?= $isHidden ?>" data-type="<?= $key ?>">
                                <i class='bx <?= $iconClass ?>'></i>
                                <span><?= $name ?></span>
                            </div>
                        <?php 
                            $count++;
                        endforeach; 
                    else:
                    ?>
                        <div class="problem-type-item">
                            <i class='bx bx-error'></i>
                            <span>Không thể tải danh sách loại bài toán</span>
                        </div>
                    <?php endif; ?>
                </div>
                <button id="toggleTypesBtn" class="toggle-types-btn">
                    <span id="toggleText">Xem nhiều hơn</span>
                    <i class='bx bx-chevron-down' id="toggleIcon"></i>
                </button>
            </div>
        </div>
        
        <div class="difficulty-section">
            <h3>Độ Khó</h3>
            <div class="difficulty-filters">
                <label class="difficulty-filter">
                    <input type="checkbox" value="easy" checked>
                    <span class="difficulty-label easy">Dễ</span>
                </label>
                <label class="difficulty-filter">
                    <input type="checkbox" value="medium" checked>
                    <span class="difficulty-label medium">Trung bình</span>
                </label>
                <label class="difficulty-filter">
                    <input type="checkbox" value="hard" checked>
                    <span class="difficulty-label hard">Khó</span>
                </label>
            </div>
        </div>
        
        <div class="status-section">
            <h3>Trạng Thái</h3>
            <div class="status-filters">
                <label class="status-filter">
                    <input type="checkbox" value="all" checked>
                    <span>Tất cả</span>
                </label>
                <label class="status-filter">
                    <input type="checkbox" value="solved">
                    <span>Đã giải</span>
                </label>
                <label class="status-filter">
                    <input type="checkbox" value="unsolved">
                    <span>Chưa giải</span>
                </label>
            </div>
        </div>
        
        <div class="filter-actions">
            <button id="applyFiltersBtn" class="apply-filters-btn">
                <i class='bx bx-filter'></i>
                <span>Áp dụng lọc</span>
            </button>
        </div>
    </div>
</div>

<script src="/js/problems.js"></script>

<?php
$content = ob_get_clean();
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>
