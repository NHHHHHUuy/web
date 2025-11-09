<?php
// progress.php
require_once 'includes/header.php';
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUser($pdo, $_SESSION['user_id']);

// Kiểm tra nếu user không tồn tại, chuyển hướng về login
if (!$user) {
    header("Location: logout.php");
    exit();
}
$progress = getUserProgress($pdo, $_SESSION['user_id']);
$recent_sessions = getPracticeHistory($pdo, $_SESSION['user_id'], 5);
$total_sessions = getTotalPracticeSessions($pdo, $_SESSION['user_id']);
$average_scores = getAverageScore($pdo, $_SESSION['user_id']);

// Tính điểm trung bình tổng
$avg_score = 0;
if ($average_scores && $total_sessions > 0) {
    $avg_score = round(($average_scores['avg_pronunciation'] + $average_scores['avg_fluency'] + $average_scores['avg_accuracy']) / 3);
}
?>

<section class="section">
    <div class="container">
        <div class="section-title">
            <h1>Theo Dõi Tiến Độ</h1>
            <p>Xem sự tiến bộ của bạn theo thời gian</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-microphone"></i>
                </div>
                <div class="stat-info">
                    <h3>Bài tập đã làm</h3>
                    <p class="stat-number"><?php echo $total_sessions; ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-info">
                    <h3>Điểm trung bình</h3>
                    <p class="stat-number"><?php echo $avg_score; ?>%</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="stat-info">
                    <h3>Trình độ</h3>
                    <p class="stat-number"><?php echo getLevelName($user['level']); ?></p>
                </div>
            </div>
        </div>

        <div class="dashboard-content">
            <div class="recent-sessions">
                <h2>Bài tập gần đây</h2>
                <?php if (empty($recent_sessions)): ?>
                    <div class="empty-state">
                        <i class="fas fa-microphone-slash"></i>
                        <p>Bạn chưa có bài tập nào.</p>
                        <a href="practice.php" class="btn btn-primary">Bắt đầu luyện tập ngay!</a>
                    </div>
                <?php else: ?>
                    <div class="sessions-list">
                        <?php foreach ($recent_sessions as $session): ?>
                            <div class="session-item">
                                <div class="session-content">
                                    <p><?php echo htmlspecialchars(substr($session['text_content'], 0, 100)); ?><?php echo strlen($session['text_content']) > 100 ? '...' : ''; ?></p>
                                    <small><?php echo date('d/m/Y H:i', strtotime($session['created_at'])); ?></small>
                                </div>
                                <div class="session-score">
                                    <span class="score-badge"><?php echo $session['pronunciation_score']; ?>%</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="quick-actions">
                <h2>Biểu đồ tiến độ</h2>
                <div class="chart-container">
                    <canvas id="progressChart" height="300"></canvas>
                </div>
                
                <?php if (!empty($average_scores)): ?>
                <div class="score-summary">
                    <h3>Điểm trung bình chi tiết</h3>
                    <div class="score-item">
                        <label>Phát âm:</label>
                        <span><?php echo round($average_scores['avg_pronunciation']); ?>%</span>
                    </div>
                    <div class="score-item">
                        <label>Độ trôi chảy:</label>
                        <span><?php echo round($average_scores['avg_fluency']); ?>%</span>
                    </div>
                    <div class="score-item">
                        <label>Độ chính xác:</label>
                        <span><?php echo round($average_scores['avg_accuracy']); ?>%</span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script src="assets/js/main.js"></script>

<?php require_once 'includes/footer.php'; ?>