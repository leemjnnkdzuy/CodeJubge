<?php
// Page title
$pageTitle = htmlspecialchars($contest['title']) . ' - Leaderboard - CodeJudge';

// Start output buffering
ob_start();
?>

<!-- Contest Leaderboard Header -->
<div class="contest-leaderboard-header">
    <div>
        <h1><i class="fas fa-trophy"></i> Bảng xếp hạng Contest</h1>
        <h2 class="contest-title"><?= htmlspecialchars($contest['title']) ?></h2>
        <div class="contest-info">
            <span class="contest-status <?= $contest['status'] ?>">
                <?php
                $statusText = [
                    'upcoming' => 'Sắp diễn ra',
                    'live' => 'Đang diễn ra', 
                    'finished' => 'Đã kết thúc'
                ];
                echo $statusText[$contest['status']] ?? 'Không xác định';
                ?>
            </span>
            <span class="contest-time">
                                <?= date('d/m/Y H:i', strtotime($contest['start_time'])) ?> - 
                                <?= date('d/m/Y H:i', strtotime($contest['end_time'])) ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="leaderboard-stats">
                        <div class="total-users">
                            <?= number_format($totalParticipants) ?> người tham gia
                        </div>
                        <?php if ($totalPages > 1): ?>
                        <div class="page-info">
                            Trang <?= $currentPage ?> / <?= $totalPages ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="leaderboard-actions">
                    <a href="/contests/<?= $contest['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Về Contest
                    </a>
                    
                    <div class="leaderboard-actions">
                        <button class="btn btn-secondary" onclick="window.print()">
                            <i class="fas fa-print"></i> In bảng xếp hạng
                        </button>
                        
                        <button class="btn btn-secondary" onclick="exportLeaderboard()">
                            <i class="fas fa-download"></i> Xuất CSV
                        </button>
                    </div>
                </div>
                
                <!-- Leaderboard Table -->
                <?php if (empty($leaderboard)): ?>
                    <div class="empty-leaderboard">
                        <i class="fas fa-users"></i>
                        <h3>Chưa có người tham gia nào</h3>
                        <p>Contest này chưa có người tham gia hoặc chưa có kết quả.</p>
                        <a href="/contests/<?= $contest['id'] ?>" class="btn btn-primary">
                            Về trang Contest
                        </a>
                    </div>
                <?php else: ?>
                    <div class="leaderboard-table">
                        <div class="table-header">
                            <div class="header-rank">Hạng</div>
                            <div class="header-user">Người dùng</div>
                            <div class="header-score">Điểm số</div>
                            <div class="header-solved">Giải được</div>
                            <div class="header-time">Thời gian</div>
                        </div>
                        
                        <div class="table-body">
                            <?php foreach ($leaderboard as $participant): ?>
                                <div class="table-row <?= $participant['rank_position'] <= 3 ? 'top-rank rank-' . $participant['rank_position'] : '' ?>">
                                    <div class="rank-cell">
                                        <div class="rank-number">
                                            <?php if ($participant['rank_position'] == 1): ?>
                                                <i class="fas fa-crown gold"></i>
                                            <?php elseif ($participant['rank_position'] == 2): ?>
                                                <i class="fas fa-medal silver"></i>
                                            <?php elseif ($participant['rank_position'] == 3): ?>
                                                <i class="fas fa-medal bronze"></i>
                                            <?php endif; ?>
                                            #<?= $participant['rank_position'] ?>
                                        </div>
                                    </div>
                                    
                                    <div class="user-cell">
                                        <div class="user-avatar">
                                            <?php if (!empty($participant['avatar'])): ?>
                                                <img src="data:image/jpeg;base64,<?= $participant['avatar'] ?>" alt="Avatar">
                                            <?php else: ?>
                                                <div class="default-avatar">
                                                    <?= strtoupper(substr($participant['username'], 0, 1)) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="user-info">
                                            <div class="username"><?= htmlspecialchars($participant['username']) ?></div>
                                            <div class="full-name">
                                                <?= htmlspecialchars(trim($participant['first_name'] . ' ' . $participant['last_name'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="score-cell">
                                        <div class="total-score">
                                            <?= number_format($participant['total_score']) ?>
                                        </div>
                                        <div class="score-unit">điểm</div>
                                    </div>
                                    
                                    <div class="solved-cell">
                                        <div class="problems-solved">
                                            <?= $participant['problems_solved'] ?? 0 ?>
                                        </div>
                                        <div class="problems-total">/ <?= $contest['problem_count'] ?? 0 ?></div>
                                    </div>
                                    
                                    <div class="time-cell">
                                        <div class="total-time">
                                            <?php
                                            $totalMinutes = intval($participant['total_time'] / 60);
                                            $hours = intval($totalMinutes / 60);
                                            $minutes = $totalMinutes % 60;
                                            
                                            if ($hours > 0) {
                                                echo "{$hours}h {$minutes}m";
                                            } else {
                                                echo "{$minutes}m";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($hasPrevPage): ?>
                                <a href="?page=<?= $currentPage - 1 ?>" class="prev">
                                    <i class="fas fa-chevron-left"></i> Trước
                                </a>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $currentPage - 2);
                            $end = min($totalPages, $currentPage + 2);
                            
                            if ($start > 1) {
                                echo '<a href="?page=1">1</a>';
                                if ($start > 2) {
                                    echo '<span class="dots">...</span>';
                                }
                            }
                            
                            for ($i = $start; $i <= $end; $i++) {
                                if ($i == $currentPage) {
                                    echo '<span class="current">' . $i . '</span>';
                                } else {
                                    echo '<a href="?page=' . $i . '">' . $i . '</a>';
                                }
                            }
                            
                            if ($end < $totalPages) {
                                if ($end < $totalPages - 1) {
                                    echo '<span class="dots">...</span>';
                                }
                                echo '<a href="?page=' . $totalPages . '">' . $totalPages . '</a>';
                            }
                            ?>
                            
                            <?php if ($hasNextPage): ?>
                                <a href="?page=<?= $currentPage + 1 ?>" class="next">
                                    Sau <i class="fas fa-chevron-right"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

<style>
    /* Contest Leaderboard Specific Styles */
    .contest-leaderboard-header {
        background: linear-gradient(135deg, var(--primary-blue) 0%, var(--primary-blue-dark) 100%);
        color: white;
        padding: var(--spacing-xl);
        border-radius: var(--radius-lg);
        margin-bottom: var(--spacing-xl);
    }
        .contest-title {
            color: var(--primary-blue);
            font-size: 1.5rem;
            margin: 0.5rem 0;
        }
        
        .contest-info {
            display: flex;
            gap: 1rem;
            align-items: center;
            margin-top: 0.5rem;
        }
        
        .contest-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .contest-status.upcoming {
            background: rgba(255, 111, 0, 0.1);
            color: var(--secondary-orange);
        }
        
        .contest-status.live {
            background: rgba(3, 218, 198, 0.1);
            color: var(--secondary-green);
            animation: pulse 2s infinite;
        }
        
        .contest-status.finished {
            background: rgba(108, 117, 125, 0.1);
            color: var(--dark-gray);
        }
        
        .contest-time {
            color: var(--text-secondary);
            font-size: 0.9rem;
        }
        
        .leaderboard-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-light);
        }
        
        .leaderboard-tools {
            display: flex;
            gap: 0.5rem;
        }
        
        .empty-leaderboard {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-light);
        }
        
        .empty-leaderboard i {
            font-size: 4rem;
            color: var(--medium-gray);
            margin-bottom: 1rem;
        }
        
        .table-header {
            display: grid;
            grid-template-columns: 80px 1fr 120px 100px 100px;
            gap: 1rem;
            padding: 1rem 1.5rem;
            background: var(--light-gray);
            font-weight: 600;
            color: var(--text-secondary);
            font-size: 0.9rem;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 10;
        }
        
        .table-row {
            display: grid;
            grid-template-columns: 80px 1fr 120px 100px 100px;
            gap: 1rem;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            align-items: center;
            transition: background 0.2s ease;
            background: white;
        }
        
        .table-row:hover {
            background: rgba(32, 190, 255, 0.05);
        }
        
        .table-row.top-rank {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.1) 0%, rgba(255, 255, 255, 0.1) 100%);
        }
        
        .table-row.rank-1 {
            background: linear-gradient(135deg, rgba(255, 215, 0, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        }
        
        .table-row.rank-2 {
            background: linear-gradient(135deg, rgba(192, 192, 192, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        }
        
        .table-row.rank-3 {
            background: linear-gradient(135deg, rgba(205, 127, 50, 0.2) 0%, rgba(255, 255, 255, 0.1) 100%);
        }
        
        .rank-number {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 600;
            font-size: 1.1rem;
        }
        
        .rank-number i.gold {
            color: #ffd700;
        }
        
        .rank-number i.silver {
            color: #c0c0c0;
        }
        
        .rank-number i.bronze {
            color: #cd7f32;
        }
        
        .user-cell {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .user-avatar img,
        .default-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .default-avatar {
            background: var(--primary-blue);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }
        
        .username {
            font-weight: 600;
            color: var(--text-primary);
        }
        
        .full-name {
            color: var(--text-secondary);
            font-size: 0.85rem;
        }
        
        .total-score {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--primary-blue);
        }
        
        .score-unit {
            font-size: 0.8rem;
            color: var(--text-secondary);
        }
        
        .problems-solved {
            font-weight: 600;
            color: var(--secondary-green);
        }
        
        .problems-total {
            color: var(--text-secondary);
        }
        
        .total-time {
            font-weight: 500;
            color: var(--text-primary);
        }
        
        .dots {
            color: var(--text-secondary);
        }
        
        @media (max-width: 768px) {
            .table-header,
            .table-row {
                grid-template-columns: 60px 1fr 80px;
                gap: 0.5rem;
                padding: 0.75rem;
            }
            
            .header-solved,
            .header-time,
            .solved-cell,
            .time-cell {
                display: none;
            }
            
            .leaderboard-actions {
                flex-direction: column;
                gap: 1rem;
            }
            
            .leaderboard-tools {
                width: 100%;
                justify-content: center;
            }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        
        @media print {
            .leaderboard-actions,
            .pagination {
                display: none !important;
            }
            
            .table-row {
                break-inside: avoid;
            }
        }
    </style>
    
    <script>
        function exportLeaderboard() {
            // Create CSV content
            let csv = 'Rank,Username,Full Name,Score,Problems Solved,Total Time\n';
            
            <?php foreach ($leaderboard as $participant): ?>
                csv += '<?= $participant['rank_position'] ?>,"<?= addslashes($participant['username']) ?>","<?= addslashes(trim($participant['first_name'] . ' ' . $participant['last_name'])) ?>",<?= $participant['total_score'] ?>,<?= $participant['problems_solved'] ?? 0 ?>,<?= intval($participant['total_time'] / 60) ?>\n';
            <?php endforeach; ?>
            
            // Create and download file
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'contest_leaderboard_<?= $contest['id'] ?>.csv';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }
    </script>

<?php
$content = ob_get_clean();

// Additional CSS for this page  
$additionalCSS = ['/css/leaderboardStyle.css'];

// Use the pagesWithSidebar layout
include VIEW_PATH . '/layouts/pagesWithSidebar.php';
?>
