<?php
// materials.php
require_once 'includes/header.php';
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUser($pdo, $_SESSION['user_id']);

// Kiểm tra nếu user không tồn tại, chuyển hướng về login
if (!$user) {
    header("Location: logout.php");
    exit();
}
$category = $_GET['category'] ?? null;
$level = $_GET['level'] ?? null;
$materials = getLearningMaterials($pdo, $category, $level);
?>

<section class="section">
    <div class="container">
        <div class="section-title">
            <h1>Tài Liệu Học Tập</h1>
            <p>Khám phá các tài liệu học tiếng Anh được biên soạn theo trình độ</p>
        </div>

        <div class="filters">
            <div class="filter-group">
                <label for="categoryFilter">Danh mục</label>
                <select id="categoryFilter" class="form-control">
                    <option value="">Tất cả danh mục</option>
                    <option value="vocabulary" <?php echo $category == 'vocabulary' ? 'selected' : ''; ?>>Từ vựng</option>
                    <option value="grammar" <?php echo $category == 'grammar' ? 'selected' : ''; ?>>Ngữ pháp</option>
                    <option value="pronunciation" <?php echo $category == 'pronunciation' ? 'selected' : ''; ?>>Phát âm</option>
                    <option value="conversation" <?php echo $category == 'conversation' ? 'selected' : ''; ?>>Hội thoại</option>
                    <option value="business" <?php echo $category == 'business' ? 'selected' : ''; ?>>Tiếng Anh công sở</option>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="levelFilter">Trình độ</label>
                <select id="levelFilter" class="form-control">
                    <option value="">Tất cả trình độ</option>
                    <option value="beginner" <?php echo $level == 'beginner' ? 'selected' : ''; ?>>Mới bắt đầu</option>
                    <option value="elementary" <?php echo $level == 'elementary' ? 'selected' : ''; ?>>Sơ cấp</option>
                    <option value="intermediate" <?php echo $level == 'intermediate' ? 'selected' : ''; ?>>Trung cấp</option>
                    <option value="upper-intermediate" <?php echo $level == 'upper-intermediate' ? 'selected' : ''; ?>>Trung cao cấp</option>
                    <option value="advanced" <?php echo $level == 'advanced' ? 'selected' : ''; ?>>Nâng cao</option>
                </select>
            </div>
        </div>

        <div class="materials-grid">
            <?php if (empty($materials)): ?>
                <div class="empty-state">
                    <i class="fas fa-book"></i>
                    <p>Không có tài liệu nào phù hợp với bộ lọc.</p>
                </div>
            <?php else: ?>
                <?php foreach ($materials as $material): ?>
                    <div class="material-card" data-category="<?php echo $material['category']; ?>" data-level="<?php echo $material['level']; ?>">
                        <h3><?php echo htmlspecialchars($material['title']); ?></h3>
                        <p><?php echo htmlspecialchars($material['content']); ?></p>
                        <div class="material-meta">
                            <span class="level-badge"><?php echo getLevelName($material['level']); ?></span>
                            <span class="category-badge"><?php echo ucfirst($material['category']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="assets/js/main.js"></script>

<?php require_once 'includes/footer.php'; ?>