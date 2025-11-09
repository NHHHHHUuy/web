<?php
// includes/functions.php
require_once 'config.php';

function savePracticeSession($pdo, $user_id, $text_content, $audio_path, $scores, $feedback) {
    $stmt = $pdo->prepare("INSERT INTO practice_sessions (user_id, text_content, user_audio_path, pronunciation_score, fluency_score, accuracy_score, feedback) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([
        $user_id,
        $text_content,
        $audio_path,
        $scores['pronunciation'],
        $scores['fluency'],
        $scores['accuracy'],
        $feedback
    ]);
}

function getUserProgress($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT metric_type, AVG(score) as avg_score 
        FROM user_progress 
        WHERE user_id = ? 
        GROUP BY metric_type
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPracticeHistory($pdo, $user_id, $limit = 10) {
    $limit = (int)$limit;
    $stmt = $pdo->prepare("
        SELECT * FROM practice_sessions 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT $limit
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalPracticeSessions($pdo, $user_id) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM practice_sessions WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'] ?? 0;
}

function getAverageScore($pdo, $user_id) {
    $stmt = $pdo->prepare("
        SELECT AVG(pronunciation_score) as avg_pronunciation,
               AVG(fluency_score) as avg_fluency,
               AVG(accuracy_score) as avg_accuracy
        FROM practice_sessions 
        WHERE user_id = ?
    ");
    $stmt->execute([$user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function analyzePronunciation($user_audio_path, $original_text) {
    // Giả lập phân tích AI
    $pronunciation_score = rand(70, 95);
    $fluency_score = rand(75, 98);
    $accuracy_score = rand(80, 97);
    
    $feedback = "Phát âm khá tốt! ";
    $suggestions = [];
    
    if ($pronunciation_score < 80) {
        $feedback .= "Cần chú ý phát âm rõ ràng hơn. ";
        $suggestions[] = "Luyện tập các âm khó";
    }
    if ($fluency_score < 80) {
        $feedback .= "Nên luyện tập để nói trôi chảy hơn. ";
        $suggestions[] = "Tập nói với tốc độ ổn định";
    }
    
    // Lưu vào progress
    global $pdo;
    $user_id = $_SESSION['user_id'];
    
    $metrics = [
        'pronunciation' => $pronunciation_score,
        'fluency' => $fluency_score,
        'accuracy' => $accuracy_score
    ];
    
    foreach ($metrics as $type => $score) {
        $stmt = $pdo->prepare("INSERT INTO user_progress (user_id, metric_type, score) VALUES (?, ?, ?)");
        $stmt->execute([$user_id, $type, $score]);
    }
    
    return [
        'pronunciation' => $pronunciation_score,
        'fluency' => $fluency_score,
        'accuracy' => $accuracy_score,
        'feedback' => $feedback,
        'suggestions' => $suggestions
    ];
}

function getLearningMaterials($pdo, $category = null, $level = null) {
    $sql = "SELECT * FROM learning_materials WHERE 1=1";
    $params = [];
    
    if ($category) {
        $sql .= " AND category = ?";
        $params[] = $category;
    }
    
    if ($level) {
        $sql .= " AND level = ?";
        $params[] = $level;
    }
    
    $sql .= " ORDER BY created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>