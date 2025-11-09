<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Luyện nói tiếng Anh</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<h2>Xin chào, <?php echo $_SESSION['fullname']; ?> 👋</h2>
<h3>Hãy đọc câu sau:</h3>
<p id="sentence">I would like to order a cup of coffee, please.</p>

<video id="webcam" autoplay playsinline width="320" height="240"></video><br>
<button id="startRecord">🎙️ Ghi âm</button>
<button id="stopRecord">⏹ Dừng</button>

<h3>Kết quả:</h3>
<p><strong>Bạn nói:</strong> <span id="userText"></span></p>
<p><strong>Độ chính xác:</strong> <span id="accuracy"></span>%</p>
<p><strong>Phản hồi:</strong> <span id="feedback"></span></p>

<script src="js/webcam.js"></script>
<script src="js/speech.js"></script>
</body>
</html>
