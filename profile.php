<?php
// profile.php
require_once 'includes/header.php';
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUser($pdo, $_SESSION['user_id']);

// Kiểm tra nếu user không tồn tại, chuyển hướng về login
if (!$user) {
    header("Location: logout.php");
    exit();
}
?>

<section class="section">
    <div class="container">
        <div class="section-title">
            <h1>Hồ Sơ Cá Nhân</h1>
            <p>Quản lý thông tin tài khoản của bạn</p>
        </div>

        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                        <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
                        <span class="level-badge"><?php echo getLevelName($user['level']); ?></span>
                    </div>
                </div>
                
                <div class="profile-stats">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo getTotalPracticeSessions($pdo, $user['id']); ?></span>
                        <span class="stat-label">Bài tập đã làm</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number">
                            <?php 
                            $avg_scores = getAverageScore($pdo, $user['id']);
                            $avg_score = 0;
                            if ($avg_scores && getTotalPracticeSessions($pdo, $user['id']) > 0) {
                                $avg_score = round(($avg_scores['avg_pronunciation'] + $avg_scores['avg_fluency'] + $avg_scores['avg_accuracy']) / 3);
                            }
                            echo $avg_score;
                            ?>%
                        </span>
                        <span class="stat-label">Điểm trung bình</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></span>
                        <span class="stat-label">Tham gia từ</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.profile-container {
    max-width: 600px;
    margin: 0 auto;
}

.profile-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 2rem;
    box-shadow: var(--box-shadow);
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 2rem;
    border-bottom: 2px solid var(--light-color);
}

.profile-avatar {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: white;
}

.profile-info h2 {
    margin-bottom: 0.5rem;
    color: var(--dark-color);
}

.profile-email {
    color: var(--gray-color);
    margin-bottom: 1rem;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
}

.stat-item {
    text-align: center;
    padding: 1rem;
    background: var(--light-color);
    border-radius: var(--border-radius);
}

.stat-number {
    display: block;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--primary-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    color: var(--gray-color);
}
</style>

<?php require_once 'includes/footer.php'; ?>