<?php
// includes/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'speakeasy');
define('BASE_URL', 'http://localhost/speakeasy');

session_start();

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Kết nối database thất bại: " . $e->getMessage());
}
// config/ai_config.php
define('GOOGLE_CLOUD_API_KEY', 'your-api-key-here');
define('AZURE_SPEECH_KEY', 'your-azure-key-here');
define('AZURE_REGION', 'your-region');
?>
