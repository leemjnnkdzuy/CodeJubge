<?php
// Page title and description
$title = htmlspecialchars($contest['title']) . ' - Contest Detail - CodeJudge';
$description = 'Chi tiết contest ' . htmlspecialchars($contest['title']);

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);

// Content for the layout
ob_start();
?>

<!-- Contest Header -->
<div class="contest-detail-header">
    <div class="contest-detail-title-area">
        <h1 class="contest-detail-title"><?= htmlspecialchars($contest['title']) ?></h1>
        <div class="contest-status <?= $contest['status'] ?>">
            <?php
            $statusText = [
                'upcoming' => 'Sắp diễn ra',
                'live' => 'Đang diễn ra',
                'finished' => 'Đã kết thúc'
            ];
            echo $statusText[$contest['status']] ?? 'Không xác định';
            ?>
        </div>
    </div>
    
    <?php if (!empty($contest['description'])): ?>
    <p class="contest-description"><?= nl2br(htmlspecialchars($contest['description'])) ?></p>
    <?php endif; ?>
                
                <div class="contest-detail-meta">
                    <div class="contest-detail-meta-item">
                        <span class="contest-detail-meta-label">Thời gian bắt đầu</span>
                        <span class="contest-detail-meta-value">
                            <?= date('d/m/Y H:i', strtotime($contest['start_time'])) ?>
                        </span>
                    </div>
                    <div class="contest-detail-meta-item">
                        <span class="contest-detail-meta-label">Thời gian kết thúc</span>
                        <span class="contest-detail-meta-value">
                            <?= date('d/m/Y H:i', strtotime($contest['end_time'])) ?>
                        </span>
                    </div>
                    <div class="contest-detail-meta-item">
                        <span class="contest-detail-meta-label">Độ khó</span>
                        <span class="contest-detail-meta-value">
                            <?php
                            $difficultyText = [
                                'easy' => 'Dễ',
                                'medium' => 'Trung bình',
                                'hard' => 'Khó'
                            ];
                            echo $difficultyText[$contest['difficulty']] ?? 'Không xác định';
                            ?>
                        </span>
                    </div>
                    <div class="contest-detail-meta-item">
                        <span class="contest-detail-meta-label">Người tham gia</span>
                        <span class="contest-detail-meta-value"><?= number_format($contest['participant_count']) ?></span>
                    </div>
                </div>
                
                <!-- Contest Actions -->
                <div class="contest-actions" style="margin-top: 1.5rem;">
                    <?php if ($isLoggedIn && !$isCreator): ?>
                        <?php if ($contest['status'] !== 'finished'): ?>
                            <?php if ($isParticipant): ?>
                                <button class="btn btn-secondary leave-contest-btn" data-contest-id="<?= $contest['id'] ?>">
                                    <i class="fas fa-sign-out-alt"></i> Rời khỏi Contest
                                </button>
                            <?php else: ?>
                                <button class="btn btn-success join-contest-btn" data-contest-id="<?= $contest['id'] ?>">
                                    <i class="fas fa-sign-in-alt"></i> Tham gia Contest
                                </button>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <a href="/contests/<?= $contest['id'] ?>/leaderboard" class="btn btn-primary">
                        <i class="fas fa-trophy"></i> Bảng xếp hạng
                    </a>
                    
                    <?php if ($isCreator): ?>
                        <a href="/contests/<?= $contest['id'] ?>/edit" class="btn btn-secondary">
                            <i class="fas fa-edit"></i> Chỉnh sửa
                        </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Contest Content -->
            <div class="row" style="margin-top: 2rem;">
                <!-- Problems List -->
                <div class="col-8">
                    <div class="contest-section">
                        <h3><i class="fas fa-code"></i> Bài tập (<?= count($problems) ?>)</h3>
                        
                        <?php if (empty($problems)): ?>
                            <div class="empty-state">
                                <i class="fas fa-file-code"></i>
                                <p>Chưa có bài tập nào trong contest này</p>
                                <?php if ($isCreator): ?>
                                    <a href="/contests/<?= $contest['id'] ?>/problems/add" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Thêm bài tập
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="problems-list">
                                <?php foreach ($problems as $index => $problem): ?>
                                    <div class="problem-item">
                                        <div class="problem-info">
                                            <span class="problem-number"><?= $index + 1 ?>.</span>
                                            <div class="problem-details">
                                                <h4 class="problem-title">
                                                    <?php if ($isParticipant || $isCreator || $contest['status'] === 'finished'): ?>
                                                        <a href="/contests/<?= $contest['id'] ?>/problems/<?= $problem['id'] ?>">
                                                            <?= htmlspecialchars($problem['title']) ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <?= htmlspecialchars($problem['title']) ?>
                                                    <?php endif; ?>
                                                </h4>
                                                <div class="problem-meta">
                                                    <span class="difficulty <?= $problem['difficulty'] ?>">
                                                        <?= ucfirst($problem['difficulty']) ?>
                                                    </span>
                                                    <span class="points">
                                                        <i class="fas fa-star"></i> <?= $problem['points'] ?? 100 ?> điểm
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="problem-stats">
                                            <small>
                                                <i class="fas fa-check-circle text-success"></i> 
                                                <?= $problem['solved_count'] ?? 0 ?>
                                            </small>
                                            <small>
                                                <i class="fas fa-paper-plane"></i> 
                                                <?= $problem['submission_count'] ?? 0 ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Contest Sidebar -->
                <div class="col-4">
                    <!-- Contest Rules -->
                    <?php if (!empty($contest['rules'])): ?>
                    <div class="contest-section">
                        <h4><i class="fas fa-gavel"></i> Quy tắc Contest</h4>
                        <div class="contest-rules">
                            <?= nl2br(htmlspecialchars($contest['rules'])) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Top Participants -->
                    <div class="contest-section">
                        <h4><i class="fas fa-users"></i> Top Participants</h4>
                        
                        <?php if (empty($leaderboard)): ?>
                            <p class="text-muted">Chưa có ai tham gia</p>
                        <?php else: ?>
                            <div class="top-participants">
                                <?php foreach (array_slice($leaderboard, 0, 5) as $participant): ?>
                                    <div class="participant-item">
                                        <div class="participant-rank">#<?= $participant['rank_position'] ?></div>
                                        <div class="participant-info">
                                            <div class="participant-name">
                                                <?= htmlspecialchars($participant['username']) ?>
                                            </div>
                                            <div class="participant-score">
                                                <?= number_format($participant['total_score']) ?> điểm
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <a href="/contests/<?= $contest['id'] ?>/leaderboard" class="btn btn-secondary btn-small" style="width: 100%; margin-top: 1rem;">
                                Xem tất cả
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Contest Statistics -->
                    <div class="contest-section">
                        <h4><i class="fas fa-chart-bar"></i> Thống kê</h4>
                        <div class="contest-stats-grid">
                            <div class="stat-item">
                                <div class="stat-value"><?= number_format($stats['total_participants'] ?? 0) ?></div>
                                <div class="stat-label">Người tham gia</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= number_format($stats['total_problems'] ?? 0) ?></div>
                                <div class="stat-label">Bài tập</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= number_format($stats['total_submissions'] ?? 0) ?></div>
                                <div class="stat-label">Lần nộp</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Creator Info -->
                    <div class="contest-section">
                        <h4><i class="fas fa-user"></i> Tác giả</h4>
                        <div class="creator-info">
                            <strong><?= htmlspecialchars($contest['creator_username']) ?></strong>
                            <p>Tạo ngày <?= date('d/m/Y', strtotime($contest['created_at'])) ?></p>
                        </div>
                    </div>
                </div>
    </div>
</div>

<style>
    /* Contest Detail Specific Styles */
    .contest-detail-header {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
        color: white;
        padding: var(--spacing-xl);
        border-radius: var(--radius-lg);
        margin-bottom: var(--spacing-xl);
    }

    .contest-detail-title {
        font-size: 2rem;
        margin: 0 0 var(--spacing-sm) 0;
    }

    .contest-detail-meta {
        display: flex;
        gap: var(--spacing-xl);
        flex-wrap: wrap;
        margin-top: var(--spacing-lg);
    }

    .contest-detail-meta-item {
        display: flex;
        flex-direction: column;
    }

    .contest-detail-meta-label {
        font-size: 0.85rem;
        opacity: 0.9;
        margin-bottom: 0.25rem;
    }

    .contest-detail-meta-value {
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .contest-section {
        background: white;
        border-radius: var(--radius-lg);
        padding: var(--spacing-lg);
        box-shadow: var(--shadow-light);
        margin-bottom: var(--spacing-lg);
    }
    
    .contest-section h3,
    .contest-section h4 {
        color: var(--text-primary);
        margin-bottom: var(--spacing-md);
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
    }
    
    .problems-list {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-md);
    }
    
    .problem-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: var(--spacing-md);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        transition: all 0.3s ease;
    }
    
    .problem-item:hover {
        border-color: var(--primary-blue);
        background: rgba(32, 190, 255, 0.05);
    }
    
    .problem-info {
        display: flex;
        align-items: center;
        gap: var(--spacing-md);
        flex: 1;
    }
    
    .problem-number {
        font-weight: 600;
        color: var(--primary-blue);
        min-width: 24px;
    }
    
    .problem-title {
        margin: 0;
        font-size: 1.1rem;
    }
    
    .problem-title a {
        color: var(--text-primary);
        text-decoration: none;
    }
    
    .problem-title a:hover {
        color: var(--primary-blue);
    }
    
    .problem-meta {
        display: flex;
        gap: var(--spacing-md);
        margin-top: 0.25rem;
    }
    
    .difficulty {
        padding: 0.125rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .difficulty.easy {
        background: rgba(40, 167, 69, 0.1);
        color: #28a745;
    }
    
    .difficulty.medium {
        background: rgba(255, 193, 7, 0.1);
        color: #ffc107;
    }
    
    .difficulty.hard {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }
    
    .points {
        color: var(--secondary-orange);
        font-size: 0.85rem;
        font-weight: 500;
    }
    
    .problem-stats {
        display: flex;
        gap: var(--spacing-md);
    }
    
    .problem-stats small {
        color: var(--text-secondary);
        font-size: 0.8rem;
    }
    
    .top-participants {
        display: flex;
        flex-direction: column;
        gap: var(--spacing-sm);
    }
    
    .participant-item {
        display: flex;
        align-items: center;
        gap: var(--spacing-sm);
        padding: var(--spacing-sm);
        border-radius: var(--radius-sm);
        background: var(--light-gray);
    }
    
    .participant-rank {
        font-weight: 600;
        color: var(--primary-blue);
        min-width: 24px;
    }
    
    .participant-info {
        flex: 1;
    }
    
    .participant-name {
        font-weight: 500;
        color: var(--text-primary);
    }
    
    .participant-score {
        font-size: 0.85rem;
        color: var(--text-secondary);
    }
    
    .contest-stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: var(--spacing-md);
    }
    
    .stat-item {
        text-align: center;
    }
    
    .stat-value {
        font-size: 1.5rem;
        font-weight: 600;
        color: var(--primary-blue);
    }
    
    .stat-label {
        font-size: 0.8rem;
        color: var(--text-secondary);
        margin-top: 0.25rem;
    }
    
    .creator-info {
        text-align: center;
    }
    
    .empty-state {
        text-align: center;
        padding: var(--spacing-xl);
        color: var(--text-secondary);
    }
    
    .empty-state i {
        font-size: 3rem;
        margin-bottom: var(--spacing-md);
        color: var(--medium-gray);
    }
    
    .contest-rules {
        background: var(--light-gray);
        padding: var(--spacing-md);
        border-radius: var(--radius-sm);
        font-size: 0.9rem;
        line-height: 1.6;
    }
    
    .text-success {
        color: #28a745 !important;
    }
    
    .text-muted {
        color: var(--text-secondary) !important;
    }
    
    @media (max-width: 768px) {
        .row {
            flex-direction: column;
        }
        
        .col-8, .col-4 {
            width: 100%;
        }
        
        .contest-stats-grid {
            grid-template-columns: 1fr;
        }
        
        .problem-item {
            flex-direction: column;
            align-items: flex-start;
            gap: var(--spacing-sm);
        }
        
        .problem-stats {
            align-self: flex-end;
        }
    }
</style>

<script src="/js/contests.js"></script>

<?php
$content = ob_get_clean();

// Additional CSS for this page
$additionalCSS = ['/css/problemsStyle.css'];

// Use the pagesWithSidebar layout
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>
