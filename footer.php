<?php
// Tên file: includes/footer.php
?>
    </main> <!-- Đóng thẻ main từ header.php -->
    
    <!-- Footer -->
    <footer class="bg-white shadow-inner mt-auto">
        <div class="container mx-auto px-4 py-4 text-center text-sm text-gray-500">
            &copy; 2024 SpeakUpPro. Công cụ luyện nói cá nhân.
        </div>
    </footer>

    <!-- Truyền User ID từ PHP Session sang JavaScript -->
    <script>
        const currentUserID = <?php echo $_SESSION['user_id'] ?? 'null'; ?>;
        const apiBaseUrl = 'api_handler.php'; // Định nghĩa URL API
    </script>
    
    <!-- Tải JavaScript chính -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>
</body>
</html>