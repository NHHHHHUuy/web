<?php
// includes/header.php
require_once 'config.php';
require_once 'auth.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SpeakEasy - Luyện nói tiếng Anh</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Move Chart.js to footer.php -->
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <div class="logo">
                    <i class="fas fa-comments logo-icon"></i>
                    <span>SpeakEasy</span>
                </div>
                <div class="nav-links">
                    <a href="index.php">Trang chủ</a>
                    <a href="practice.php">Luyện tập</a>
                    <a href="materials.php">Tài liệu</a>
                    <a href="progress.php">Tiến độ</a>
                    <a href="community.php">Cộng đồng</a>
                </div>
                <div class="auth-buttons">
                    <?php if (isLoggedIn()): 
                        $user = getUser($pdo, $_SESSION['user_id']);
                        // Kiểm tra nếu user tồn tại
                        if ($user):
                    ?>
                        <div class="user-menu">
                            <span class="user-greeting">Xin chào, <?php echo htmlspecialchars($user['name']); ?></span>
                            <div class="user-dropdown">
                                <a href="profile.php"><i class="fas fa-user"></i> Hồ sơ</a>
                                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Nếu user không tồn tại, hiển thị nút đăng nhập -->
                        <a href="login.php" class="btn btn-outline">Đăng nhập</a>
                        <a href="register.php" class="btn btn-primary">Đăng ký</a>
                    <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Đăng nhập</a>
                        <a href="register.php" class="btn btn-primary">Đăng ký</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main>