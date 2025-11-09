<?php
// includes/auth.php
require_once 'config.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUser($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user ? $user : null; // Đảm bảo trả về null nếu không tìm thấy
}

function redirectIfNotLoggedIn() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isLoggedIn()) {
        header("Location: index.php");
        exit();
    }
}

function getLevelName($level) {
    $levels = [
        'beginner' => 'Mới bắt đầu',
        'elementary' => 'Sơ cấp',
        'intermediate' => 'Trung cấp',
        'upper-intermediate' => 'Trung cao cấp',
        'advanced' => 'Nâng cao'
    ];
    return $levels[$level] ?? 'Chưa xác định';
}
?>