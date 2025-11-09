<?php
// index.php
require_once 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Luyện Nói Tiếng Anh Tự Tin</h1>
            <p class="hero-subtitle">Môi trường luyện tập an toàn với phản hồi AI tức thì về phát âm, ngữ điệu và độ trôi chảy</p>
            <div class="hero-buttons">
                <?php if (isLoggedIn()): ?>
                    <a href="practice.php" class="btn btn-light btn-large">Bắt đầu luyện tập</a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-light btn-large">Bắt đầu miễn phí</a>
                <?php endif; ?>
                <a href="#features" class="btn btn-outline btn-large">Tìm hiểu thêm</a>
            </div>
        </div>
        <div class="hero-image">
            <div class="floating-card">
                <i class="fas fa-microphone"></i>
                <span>Phát âm chuẩn</span>
            </div>
            <div class="floating-card">
                <i class="fas fa-chart-line"></i>
                <span>Theo dõi tiến độ</span>
            </div>
        </div>
    </div>
</section>

<section class="section" id="features">
    <div class="container">
        <div class="section-title">
            <h2>Tính Năng Nổi Bật</h2>
            <p>SpeakEasy được thiết kế để giúp bạn cải thiện kỹ năng nói tiếng Anh hiệu quả</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-microphone-alt"></i>
                </div>
                <h3>Phân tích AI thông minh</h3>
                <p>Công nghệ AI tiên tiến phân tích phát âm, ngữ điệu và độ trôi chảy với độ chính xác cao</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Theo dõi tiến độ chi tiết</h3>
                <p>Biểu đồ và báo cáo chi tiết giúp bạn theo dõi sự tiến bộ theo thời gian</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fas fa-road"></i>
                </div>
                <h3>Lộ trình cá nhân hóa</h3>
                <p>Lộ trình học được thiết kế riêng dựa trên trình độ và mục tiêu của bạn</p>
            </div>
        </div>
    </div>
</section>

<section class="section journey-section">
    <div class="container">
        <div class="section-title">
            <h2>Hành Trình Học Tập</h2>
            <p>3 bước đơn giản để làm chủ kỹ năng nói tiếng Anh</p>
        </div>
        <div class="journey-steps">
            <div class="step">
                <div class="step-number">1</div>
                <h3>Kiểm tra đầu vào</h3>
                <p>Đánh giá trình độ hiện tại để đề xuất lộ trình học phù hợp nhất</p>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <h3>Luyện tập hàng ngày</h3>
                <p>Thực hành nói với các bài tập được cá nhân hóa và nhận phản hồi tức thì</p>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <h3>Theo dõi tiến bộ</h3>
                <p>Theo dõi sự cải thiện và điều chỉnh lộ trình học khi cần thiết</p>
            </div>
        </div>
    </div>
</section>

<section class="section cta-section">
    <div class="container">
        <div class="cta-content">
            <h2>Sẵn sàng cải thiện kỹ năng nói tiếng Anh?</h2>
            <p>Tham gia cùng hàng ngàn người học đã tự tin giao tiếp tiếng Anh</p>
            <?php if (isLoggedIn()): ?>
                <a href="practice.php" class="btn btn-primary btn-large">Bắt đầu luyện tập ngay</a>
            <?php else: ?>
                <a href="register.php" class="btn btn-primary btn-large">Đăng ký miễn phí</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>