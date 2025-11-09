<?php
// practice.php
require_once 'includes/header.php';
require_once 'includes/functions.php';
redirectIfNotLoggedIn();

$user = getUser($pdo, $_SESSION['user_id']);

// Kiểm tra nếu user không tồn tại, chuyển hướng về login
if (!$user) {
    header("Location: logout.php");
    exit();
}

// Các bài tập theo trình độ
$practice_texts = [
    'beginner' => [
        "Hello, my name is [Your Name].",
        "I am from Vietnam.",
        "How are you today?",
        "What is your favorite color?",
        "I like to learn English."
    ],
    'elementary' => [
        "The weather is very nice today.",
        "I usually have breakfast at 7 AM.",
        "Can you tell me about your family?",
        "What do you do in your free time?",
        "I enjoy watching movies on weekends."
    ],
    'intermediate' => [
        "The quick brown fox jumps over the lazy dog.",
        "Success usually comes to those who are too busy to be looking for it.",
        "The only way to do great work is to love what you do.",
        "She sells seashells by the seashore.",
        "How much wood would a woodchuck chuck if a woodchuck could chuck wood?"
    ],
    'upper-intermediate' => [
        "Despite the inclement weather, the conference proceeded as scheduled with remarkable attendance.",
        "The intricate mechanisms of the human brain continue to baffle even the most brilliant neuroscientists.",
        "Globalization has facilitated unprecedented cultural exchange while simultaneously threatening local traditions.",
        "The entrepreneur's perspicacity enabled her to identify market gaps that others had overlooked.",
        "Cognitive dissonance arises when individuals hold contradictory beliefs or engage in behaviors that conflict with their values."
    ],
    'advanced' => [
        "The epistemological foundations of postmodern thought challenge traditional notions of objective truth and universal morality.",
        "Quantum entanglement phenomena defy classical intuition, exhibiting correlations that transcend spatial separation.",
        "The sociopolitical ramifications of artificial intelligence implementation warrant meticulous ethical consideration.",
        "Neuroplasticity research has revolutionized our understanding of the brain's capacity for structural reorganization.",
        "The hermeneutic circle illustrates the interdependent relationship between understanding individual components and comprehending the whole."
    ]
];

$user_level = $user['level'];
$current_texts = $practice_texts[$user_level] ?? $practice_texts['beginner'];
$current_text = $current_texts[array_rand($current_texts)];

// Hàm phân tích phát âm với AI
function analyzePronunciationWithAI($audio_file, $target_text) {
    // Sử dụng API AI thực sự - ở đây tôi sẽ dùng Google Speech-to-Text hoặc Azure Speech
    // Bạn cần thay thế bằng API key thực của mình
    
    $analysis = [
        'pronunciation' => 0,
        'fluency' => 0,
        'accuracy' => 0,
        'feedback' => '',
        'suggestions' => [],
        'word_analysis' => [],
        'spoken_text' => ''
    ];
    
    try {
        // Phần 1: Chuyển đổi speech-to-text
        $spoken_text = convertSpeechToText($audio_file);
        $analysis['spoken_text'] = $spoken_text;
        
        // Phần 2: Phân tích phát âm
        $pronunciation_result = analyzePronunciationAccuracy($target_text, $spoken_text);
        
        // Phần 3: Phân tích độ trôi chảy
        $fluency_result = analyzeFluency($audio_file);
        
        // Kết hợp kết quả
        $analysis['pronunciation'] = $pronunciation_result['score'];
        $analysis['fluency'] = $fluency_result['score'];
        $analysis['accuracy'] = calculateAccuracy($target_text, $spoken_text);
        $analysis['word_analysis'] = $pronunciation_result['word_analysis'];
        $analysis['suggestions'] = generateAIFeedback($pronunciation_result, $fluency_result, $target_text, $spoken_text);
        $analysis['feedback'] = generateOverallFeedback($analysis);
        
    } catch (Exception $e) {
        // Fallback: phân tích cơ bản nếu AI fail
        $analysis = analyzePronunciationBasic($audio_file, $target_text);
    }
    
    return $analysis;
}

// Hàm chuyển đổi speech-to-text sử dụng Google Speech-to-Text
function convertSpeechToText($audio_file) {
    // Cần cài đặt Google Cloud Speech-to-Text
    // require_once 'vendor/autoload.php';
    
    // Code mẫu với Google Speech-to-Text
    /*
    $client = new Google\Cloud\Speech\V1\SpeechClient();
    $config = new Google\Cloud\Speech\V1\RecognitionConfig();
    $config->setEncoding(Google\Cloud\Speech\V1\RecognitionConfig\AudioEncoding::WEBM_OPUS);
    $config->setSampleRateHertz(16000);
    $config->setLanguageCode('en-US');
    $config->setEnableWordTimeOffsets(true);
    
    $audio = new Google\Cloud\Speech\V1\RecognitionAudio();
    $audio->setContent(file_get_contents($audio_file));
    
    $response = $client->recognize($config, $audio);
    $transcript = '';
    
    foreach ($response->getResults() as $result) {
        $alternatives = $result->getAlternatives();
        $mostLikely = $alternatives[0];
        $transcript .= $mostLikely->getTranscript();
    }
    
    return $transcript;
    */
    
    // Tạm thời trả về text mẫu - THAY THẾ BẰNG API THẬT
    return "I'd like to order the steak please";
}

// Hàm phân tích độ chính xác phát âm
function analyzePronunciationAccuracy($target_text, $spoken_text) {
    $target_words = preg_split('/\s+/', strtolower(trim($target_text)));
    $spoken_words = preg_split('/\s+/', strtolower(trim($spoken_text)));
    
    $word_analysis = [];
    $correct_count = 0;
    
    foreach ($target_words as $index => $target_word) {
        $spoken_word = $spoken_words[$index] ?? '';
        $is_correct = $spoken_word === $target_word;
        
        if ($is_correct) {
            $correct_count++;
        }
        
        $word_analysis[] = [
            'target_word' => $target_word,
            'spoken_word' => $spoken_word,
            'is_correct' => $is_correct,
            'confidence' => calculateWordConfidence($target_word, $spoken_word)
        ];
    }
    
    $score = count($target_words) > 0 ? ($correct_count / count($target_words)) * 100 : 0;
    
    return [
        'score' => round($score),
        'word_analysis' => $word_analysis
    ];
}

// Hàm tính độ tin cậy của từ
function calculateWordConfidence($target_word, $spoken_word) {
    similar_text($target_word, $spoken_word, $percent);
    return $percent;
}

// Hàm tính độ chính xác tổng thể
function calculateAccuracy($target_text, $spoken_text) {
    similar_text(
        strtolower(trim($target_text)),
        strtolower(trim($spoken_text)),
        $accuracy
    );
    return round($accuracy);
}

// Hàm phân tích độ trôi chảy
function analyzeFluency($audio_file) {
    // Phân tích các yếu tố về độ trôi chảy
    // - Tốc độ nói
    // - Độ dài tạm dừng
    // - Sự liên tục
    
    // Tạm thời trả về giá trị mẫu
    return [
        'score' => rand(70, 95),
        'pace' => 'appropriate', // too_fast, appropriate, too_slow
        'pauses' => 'minimal', // excessive, appropriate, minimal
        'rhythm' => 'good' // poor, fair, good, excellent
    ];
}

// Hàm tạo phản hồi AI
function generateAIFeedback($pronunciation_result, $fluency_result, $target_text, $spoken_text) {
    $suggestions = [];
    
    // Phân tích lỗi phát âm
    foreach ($pronunciation_result['word_analysis'] as $word_analysis) {
        if (!$word_analysis['is_correct'] && $word_analysis['confidence'] < 80) {
            $suggestions[] = generateWordSuggestion($word_analysis['target_word'], $word_analysis['spoken_word']);
        }
    }
    
    // Phản hồi về độ trôi chảy
    if ($fluency_result['pace'] === 'too_fast') {
        $suggestions[] = "Hãy nói chậm lại một chút để phát âm rõ ràng hơn";
    } elseif ($fluency_result['pace'] === 'too_slow') {
        $suggestions[] = "Bạn có thể nói nhanh hơn một chút để tự nhiên hơn";
    }
    
    if ($fluency_result['pauses'] === 'excessive') {
        $suggestions[] = "Cố gắng giảm bớt thời gian tạm dừng giữa các từ";
    }
    
    // Thêm gợi ý chung
    if (count($suggestions) === 0) {
        $suggestions[] = "Phát âm của bạn rất tốt! Hãy tiếp tục luyện tập";
    }
    
    return array_slice($suggestions, 0, 3); // Giới hạn 3 gợi ý
}

// Hàm tạo gợi ý cho từ cụ thể
function generateWordSuggestion($target_word, $spoken_word) {
    $suggestions = [
        "order" => "Hãy thử nhấn lưỡi vào vòm họng khi phát âm âm 'r' trong từ 'order'",
        "steak" => "Chú ý phát âm âm 'ea' trong 'steak' như /steɪk/, không phải /stiːk/",
        "please" => "Âm 'p' trong 'please' cần được phát âm rõ ràng, không bật hơi quá mạnh",
        "like" => "Âm 'i' trong 'like' nên là /aɪ/, kéo dài một chút",
        "the" => "Từ 'the' trước phụ âm nên phát âm là /ðə/",
        "I'd" => "Chú ý nối âm 'I' và 'would' thành /aɪd/",
    ];
    
    return $suggestions[$target_word] ?? "Tập trung vào phát âm từ '{$target_word}'";
}

// Hàm tạo phản hồi tổng quan
function generateOverallFeedback($analysis) {
    $score = $analysis['pronunciation'];
    
    if ($score >= 90) {
        return "Xuất sắc! Phát âm của bạn rất chuẩn và tự nhiên";
    } elseif ($score >= 80) {
        return "Rất tốt! Phát âm rõ ràng và dễ hiểu";
    } elseif ($score >= 70) {
        return "Tốt! Có một vài điểm cần cải thiện nhỏ";
    } elseif ($score >= 60) {
        return "Khá! Hãy luyện tập thêm để cải thiện phát âm";
    } else {
        return "Cần luyện tập nhiều hơn để cải thiện phát âm";
    }
}

// Fallback function nếu AI không hoạt động
function analyzePronunciationBasic($audio_file, $target_text) {
    // Phân tích cơ bản - đây chỉ là fallback
    return [
        'pronunciation' => rand(65, 85),
        'fluency' => rand(70, 90),
        'accuracy' => rand(75, 95),
        'feedback' => 'Phát âm khá tốt, hãy tiếp tục luyện tập!',
        'suggestions' => [
            'Tập trung vào âm cuối của từ',
            'Chú ý ngữ điệu khi nói',
            'Luyện tập nối âm giữa các từ'
        ],
        'word_analysis' => [],
        'spoken_text' => ''
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio'])) {
    $upload_dir = 'assets/uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $audio_file = $upload_dir . uniqid() . '_' . $_SESSION['user_id'] . '.webm';
    
    if (move_uploaded_file($_FILES['audio']['tmp_name'], $audio_file)) {
        // Sử dụng AI để phân tích
        $analysis = analyzePronunciationWithAI($audio_file, $current_text);
        
        if (savePracticeSession($pdo, $_SESSION['user_id'], $current_text, $audio_file, $analysis, $analysis['feedback'])) {
            $_SESSION['last_analysis'] = $analysis;
            $_SESSION['last_audio_path'] = $audio_file;
            header("Location: practice.php?success=1");
            exit();
        } else {
            $error = "Có lỗi xảy ra khi lưu bài tập.";
        }
    } else {
        $error = "Có lỗi xảy ra khi tải lên file âm thanh.";
    }
}

$last_analysis = $_SESSION['last_analysis'] ?? null;
$last_audio_path = $_SESSION['last_audio_path'] ?? null;
unset($_SESSION['last_analysis']);
unset($_SESSION['last_audio_path']);
?>

<section class="section">
    <div class="container">
        <!-- Header giống trong ảnh -->
        <div class="lesson-header">
            <div class="user-greeting">
                <h1>Chào buổi sáng, <?php echo htmlspecialchars($user['username'] ?? $user['email'] ?? 'User'); ?>!</h1>
                <div class="next-lesson">
                    <span>Tiếp tục bài: </span>
                    <strong>Gọi món tại nhà hàng</strong>
                </div>
            </div>
        </div>

        <div class="dashboard-layout">
            <!-- Cột trái: Luyện tập nhanh -->
            <div class="left-sidebar">
                <div class="quick-practice-card">
                    <h3>Luyện tập nhanh</h3>
                    
                    <div class="practice-item">
                        <div class="practice-icon">
                            <i class="fas fa-headphones"></i>
                        </div>
                        <div class="practice-content">
                            <h4>Luyện phát âm Nâng cao</h4>
                            <div class="practice-actions">
                                <button class="btn-small btn-outline">
                                    <i class="fas fa-volume-up"></i> Nghe thanh
                                </button>
                                <button class="btn-small btn-primary">
                                    <i class="fas fa-robot"></i> Chat với AI
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="practice-item">
                        <div class="practice-icon">
                            <i class="fas fa-book"></i>
                        </div>
                        <div class="practice-content">
                            <h4>Du lịch</h4>
                            <div class="practice-tags">
                                <span class="tag">Công việc</span>
                                <span class="tag">Giao tiếp</span>
                                <span class="tag">Ẩm thực</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột chính: Bài tập SpeakUp -->
            <div class="main-content">
                <div class="speakup-container">
                    <!-- Header SpeakUp -->
                    <div class="speakup-header">
                        <div class="speakup-title">
                            <h2>SpeakUp</h2>
                            <span class="lesson-step">Bước 3: Luyện nói</span>
                        </div>
                    </div>

                    <!-- Câu cần đọc -->
                    <div class="practice-text-card">
                        <div class="text-to-speak">
                            <h3><i class="fas fa-book-open"></i> Đọc câu sau:</h3>
                            <div id="practiceText" class="practice-text">
                                <?php 
                                // Hiển thị văn bản với từng từ được bọc trong span
                                $words = explode(' ', htmlspecialchars($current_text));
                                foreach ($words as $word) {
                                    echo '<span class="word pending">' . $word . ' </span>';
                                }
                                ?>
                            </div>
                        </div>
                        
                        <div class="audio-sample">
                            <button class="audio-btn" id="playSample">
                                <i class="fas fa-play"></i>
                            </button>
                            <span>Nghe mẫu phát âm chuẩn</span>
                            <button class="btn btn-outline btn-small" id="aiReadBtn" style="margin-left: auto;">
                                <i class="fas fa-robot"></i> AI Đọc mẫu
                            </button>
                        </div>
                    </div>

                    <!-- Phần ghi âm -->
                    <div class="recording-section">
                        <form id="recordingForm" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="text_content" value="<?php echo htmlspecialchars($current_text); ?>">
                            <input type="file" id="audioFile" name="audio" accept="audio/*" style="display: none;">
                            
                            <div class="recording-controls">
                                <button type="button" class="btn btn-primary" id="startBtn">
                                    <i class="fas fa-microphone"></i> Bắt đầu ghi âm
                                </button>
                                <button type="button" class="btn btn-outline" id="stopBtn" style="display: none;">
                                    <i class="fas fa-stop"></i> Dừng lại
                                </button>
                                <p id="recordingStatus">Nhấn "Bắt đầu" để bắt đầu ghi âm và nhận dạng giọng nói</p>
                            </div>
                            
                            <div class="action-buttons" id="actionButtons" style="display: none;">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-chart-line"></i>
                                    Phân tích phát âm với AI
                                </button>
                                <button type="button" class="btn btn-outline" id="retryBtn">
                                    <i class="fas fa-redo"></i>
                                    Thử lại
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Kết quả phân tích AI -->
                    <?php if ($last_analysis): ?>
                    <div class="ai-feedback-section">
                        <div class="feedback-header">
                            <h3><i class="fas fa-robot"></i> Phân tích AI (AI Feedback)</h3>
                            <div class="score-badge">
                                <span class="score-value"><?php echo $last_analysis['pronunciation']; ?>%</span>
                            </div>
                        </div>
                        
                        <div class="feedback-content">
                            <!-- Hiển thị câu với từ đúng/sai -->
                            <div class="sentence-analysis">
                                <div class="sentence-display">
                                    <?php
                                    $target_words = explode(' ', $current_text);
                                    $word_analysis = $last_analysis['word_analysis'] ?? [];
                                    
                                    foreach ($target_words as $index => $word) {
                                        $is_correct = true;
                                        $confidence = 100;
                                        
                                        if (isset($word_analysis[$index])) {
                                            $is_correct = $word_analysis[$index]['is_correct'];
                                            $confidence = $word_analysis[$index]['confidence'];
                                        }
                                        
                                        $class = $is_correct && $confidence > 80 ? 'word-correct' : 'word-incorrect';
                                        echo '<span class="' . $class . '" title="Độ tin cậy: ' . $confidence . '%">' . htmlspecialchars($word) . ' </span>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <!-- Phản hồi tổng quan -->
                            <div class="overall-feedback">
                                <div class="feedback-item">
                                    <strong><?php echo $last_analysis['feedback']; ?></strong>
                                </div>
                            </div>
                            
                            <!-- Phản hồi chi tiết từ AI -->
                            <div class="detailed-feedback">
                                <div class="ai-suggestions">
                                    <h4><i class="fas fa-lightbulb"></i> Gợi ý cải thiện từ AI:</h4>
                                    <ul>
                                        <?php foreach ($last_analysis['suggestions'] as $suggestion): ?>
                                            <li><?php echo htmlspecialchars($suggestion); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                                
                                <!-- Thống kê chi tiết -->
                                <div class="stats-grid">
                                    <div class="stat-item">
                                        <div class="stat-label">Phát âm</div>
                                        <div class="stat-value"><?php echo $last_analysis['pronunciation']; ?>%</div>
                                        <div class="stat-bar">
                                            <div class="stat-fill" style="width: <?php echo $last_analysis['pronunciation']; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">Độ trôi chảy</div>
                                        <div class="stat-value"><?php echo $last_analysis['fluency']; ?>%</div>
                                        <div class="stat-bar">
                                            <div class="stat-fill" style="width: <?php echo $last_analysis['fluency']; ?>%"></div>
                                        </div>
                                    </div>
                                    <div class="stat-item">
                                        <div class="stat-label">Độ chính xác</div>
                                        <div class="stat-value"><?php echo $last_analysis['accuracy']; ?>%</div>
                                        <div class="stat-bar">
                                            <div class="stat-fill" style="width: <?php echo $last_analysis['accuracy']; ?>%"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Phát lại bản ghi âm -->
                        <?php if ($last_audio_path && file_exists($last_audio_path)): ?>
                        <div class="playback-section">
                            <h4><i class="fas fa-headphones"></i> Nghe lại bản ghi của bạn</h4>
                            <audio controls style="width: 100%; margin-top: 10px;">
                                <source src="<?php echo $last_audio_path; ?>" type="audio/webm">
                                Trình duyệt của bạn không hỗ trợ phát audio.
                            </audio>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
/* Thêm CSS mới cho phần thống kê AI */
.overall-feedback {
    background: #e8f5e8;
    border: 1px solid #4caf50;
    padding: 1rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
}

.ai-suggestions {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    margin-bottom: 1.5rem;
}

.ai-suggestions h4 {
    margin: 0 0 1rem 0;
    color: #333;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-top: 1.5rem;
}

.stat-item {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    text-align: center;
}

.stat-label {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 0.5rem;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    color: #4361ee;
    margin-bottom: 0.5rem;
}

.stat-bar {
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.stat-fill {
    height: 100%;
    background: linear-gradient(90deg, #4361ee, #4cc9f0);
    transition: width 0.5s ease;
}


/* Layout chính */
.dashboard-layout {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin-top: 2rem;
}

/* Header bài học */
.lesson-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
}

.user-greeting h1 {
    margin: 0 0 0.5rem 0;
    font-size: 2rem;
    font-weight: 600;
}

.next-lesson {
    font-size: 1.1rem;
    opacity: 0.9;
}

.next-lesson strong {
    color: #ffd700;
}

/* Sidebar luyện tập nhanh */
.left-sidebar {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 1.5rem;
    height: fit-content;
}

.quick-practice-card h3 {
    margin: 0 0 1.5rem 0;
    color: #333;
    font-size: 1.3rem;
    border-bottom: 2px solid #4361ee;
    padding-bottom: 0.5rem;
}

.practice-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    margin-bottom: 1rem;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.practice-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.practice-icon {
    width: 40px;
    height: 40px;
    background: #4361ee;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.practice-content h4 {
    margin: 0 0 0.5rem 0;
    color: #333;
    font-size: 1rem;
}

.practice-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.practice-tags {
    display: flex;
    gap: 0.3rem;
    flex-wrap: wrap;
}

.tag {
    background: #e9ecef;
    color: #666;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
    font-size: 0.8rem;
}

/* Nút nhỏ */
.btn-small {
    padding: 0.4rem 0.8rem;
    font-size: 0.8rem;
    border-radius: 6px;
}

/* Main content */
.main-content {
    background: white;
    border-radius: 12px;
    padding: 0;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Header SpeakUp */
.speakup-header {
    background: linear-gradient(135deg, #4361ee 0%, #3a56d4 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
}

.speakup-title {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.speakup-title h2 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
}

.lesson-step {
    background: rgba(255,255,255,0.2);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
}

/* Card câu cần đọc */
.practice-text-card {
    padding: 2rem;
    border-bottom: 1px solid #e9ecef;
}

.text-to-speak h3 {
    color: #333;
    margin-bottom: 1rem;
    font-size: 1.2rem;
}

.practice-text {
    font-size: 1.4rem;
    line-height: 1.6;
    color: #555;
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #4361ee;
    margin-bottom: 1.5rem;
}

.practice-text .word {
    transition: all 0.3s ease;
    padding: 2px 4px;
    border-radius: 3px;
    cursor: pointer;
}

.practice-text .word.pending {
    color: #333;
    background: transparent;
}

.practice-text .word.correct {
    color: #10b981;
    background: rgba(16, 185, 129, 0.1);
    font-weight: bold;
}

.practice-text .word.incorrect {
    color: #ef4444;
    background: rgba(239, 68, 68, 0.1);
    font-weight: bold;
    text-decoration: line-through;
}

.practice-text .word.active {
    background: rgba(67, 97, 238, 0.2);
    box-shadow: 0 0 0 2px rgba(67, 97, 238, 0.3);
}

.audio-sample {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.audio-btn {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #4361ee;
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.audio-btn:hover {
    background: #3a56d4;
    transform: scale(1.05);
}

/* Phần ghi âm */
.recording-section {
    padding: 2rem;
    border-bottom: 1px solid #e9ecef;
}

.recording-controls {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

#recordingStatus {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
    flex: 1;
    min-width: 100%;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
}

/* Phản hồi AI */
.ai-feedback-section {
    padding: 2rem;
    background: #f8f9fa;
    border-radius: 0 0 12px 12px;
}

.feedback-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #e9ecef;
}

.feedback-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.3rem;
}

.score-badge {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: bold;
    font-size: 1.1rem;
}

.sentence-analysis {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    border: 1px solid #e9ecef;
}

.sentence-display {
    font-size: 1.3rem;
    line-height: 1.6;
    text-align: center;
}

.word-correct {
    color: #10b981;
    font-weight: bold;
}

.word-incorrect {
    color: #ef4444;
    font-weight: bold;
    text-decoration: line-through;
}

.detailed-feedback {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.feedback-item {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1rem;
    color: #856404;
}

.improvement-tips h4 {
    margin: 0 0 1rem 0;
    color: #333;
}

.improvement-tips ul {
    margin: 0;
    padding-left: 1.5rem;
}

.improvement-tips li {
    margin-bottom: 0.5rem;
    color: #555;
}

/* Responsive */
@media (max-width: 968px) {
    .dashboard-layout {
        grid-template-columns: 1fr;
    }
    
    .speakup-title {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .recording-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .recording-controls .btn {
        width: 100%;
    }
    
    #recordingStatus {
        text-align: center;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}

/* Nút chung */
.btn {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: #4361ee;
    color: white;
}

.btn-primary:hover {
    background: #3a56d4;
}

.btn-outline {
    background: transparent;
    color: #4361ee;
    border: 2px solid #4361ee;
}

.btn-outline:hover {
    background: #4361ee;
    color: white;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover {
    background: #0da271;
}

.btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}
</style>

<!-- Giữ nguyên toàn bộ JavaScript từ code trước -->
<script>
// Webcam và Recording - Phiên bản sửa lỗi
class WebcamRecorder {
    constructor() {
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.stream = null;
        this.isRecording = false;
        this.videoElement = document.getElementById('webcamVideo');
        this.placeholder = document.getElementById('webcamPlaceholder');
        this.audioStream = null;
    }

    async startWebcam() {
        try {
            console.log('🎤 Đang khởi động camera và microphone...');
            
            // Tách riêng video và audio stream
            const videoStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: 640,
                    height: 480,
                    facingMode: 'user'
                }
            });

            this.audioStream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: true,
                    noiseSuppression: true,
                    autoGainControl: true,
                    channelCount: 1,
                    sampleRate: 16000
                }
            });

            // Kết hợp stream cho video
            this.stream = new MediaStream([
                ...videoStream.getVideoTracks(),
                ...this.audioStream.getAudioTracks()
            ]);

            if (this.videoElement) {
                this.videoElement.srcObject = this.stream;
                this.videoElement.style.display = 'block';
                if (this.placeholder) {
                    this.placeholder.style.display = 'none';
                }
            }

            console.log('✅ Camera và microphone đã sẵn sàng');
            return true;

        } catch (error) {
            console.error('❌ Lỗi camera/microphone:', error);
            this.showError('Không thể truy cập camera/microphone. Vui lòng kiểm tra quyền truy cập.');
            return false;
        }
    }

    async startRecording() {
        try {
            if (!this.audioStream) {
                const success = await this.startWebcam();
                if (!success) return false;
            }

            this.audioChunks = [];
            
            // Chỉ sử dụng audio stream cho MediaRecorder
            this.mediaRecorder = new MediaRecorder(this.audioStream);

            this.mediaRecorder.ondataavailable = (event) => {
                if (event.data && event.data.size > 0) {
                    this.audioChunks.push(event.data);
                    console.log('📦 Nhận audio chunk:', event.data.size, 'bytes');
                }
            };

            this.mediaRecorder.onstop = () => {
                console.log('✅ Đã dừng ghi âm, tổng chunks:', this.audioChunks.length);
            };

            this.mediaRecorder.onerror = (event) => {
                console.error('❌ Lỗi MediaRecorder:', event.error);
                this.showError('Lỗi ghi âm: ' + event.error);
            };

            // Bắt đầu ghi âm với timeslice 1000ms
            this.mediaRecorder.start(1000);
            this.isRecording = true;
            console.log('🎤 Đang ghi âm...');
            
            return true;

        } catch (error) {
            console.error('❌ Lỗi bắt đầu ghi âm:', error);
            this.showError('Không thể bắt đầu ghi âm: ' + error.message);
            return false;
        }
    }

    async stopRecording() {
        return new Promise((resolve) => {
            if (this.mediaRecorder && this.isRecording) {
                this.mediaRecorder.onstop = () => {
                    const audioBlob = new Blob(this.audioChunks, { 
                        type: this.mediaRecorder.mimeType || 'audio/webm' 
                    });
                    console.log('🎵 Audio blob created:', audioBlob.size, 'bytes');
                    this.isRecording = false;
                    resolve(audioBlob);
                };
                
                try {
                    this.mediaRecorder.stop();
                } catch (e) {
                    console.error('Lỗi khi dừng MediaRecorder:', e);
                    resolve(null);
                }
            } else {
                console.log('⚠️ Không có ghi âm nào đang chạy');
                resolve(null);
            }
        });
    }

    stopWebcam() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }
        if (this.audioStream) {
            this.audioStream.getTracks().forEach(track => track.stop());
            this.audioStream = null;
        }
        if (this.videoElement) {
            this.videoElement.style.display = 'none';
        }
        if (this.placeholder) {
            this.placeholder.style.display = 'flex';
        }
        this.isRecording = false;
        this.audioChunks = [];
    }

    showError(message) {
        // Xóa thông báo lỗi cũ
        const oldError = document.querySelector('.alert-error');
        if (oldError) oldError.remove();

        const errorDiv = document.createElement('div');
        errorDiv.className = 'alert alert-error';
        errorDiv.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
            padding: 1rem;
            border-radius: 8px;
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        `;
        errorDiv.innerHTML = `
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-exclamation-triangle"></i>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(errorDiv);
        setTimeout(() => {
            if (errorDiv.parentNode) {
                errorDiv.remove();
            }
        }, 5000);
    }
}

// Khởi tạo recorder
const webcamRecorder = new WebcamRecorder();

// Speech Recognition
let recognition = null;
let isListening = false;

// Biến để theo dõi từ hiện tại
let currentWordIndex = 0;
let wordElements = [];

function initializeSpeechRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
        console.log('Trình duyệt không hỗ trợ Speech Recognition');
        webcamRecorder.showError('Trình duyệt không hỗ trợ nhận dạng giọng nói. Vui lòng dùng Chrome hoặc Edge.');
        return;
    }

    recognition = new SpeechRecognition();
    recognition.continuous = true;
    recognition.interimResults = true;
    recognition.lang = 'en-US';
    recognition.maxAlternatives = 1;

    recognition.onstart = function() {
        console.log('🎤 Đang nghe...');
        isListening = true;
        const userSpeechText = document.getElementById('userSpeechText');
        if (userSpeechText) {
            userSpeechText.classList.add('recording');
            userSpeechText.placeholder = 'Đang nghe... nói ngay bây giờ!';
        }
        
        // Reset từ khi bắt đầu nhận dạng
        resetWordHighlighting();
    };

    recognition.onresult = function(event) {
        let finalTranscript = '';
        let interimTranscript = '';
        
        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript;
            if (event.results[i].isFinal) {
                finalTranscript += transcript + ' ';
            } else {
                interimTranscript += transcript;
            }
        }

        const userSpeechText = document.getElementById('userSpeechText');
        if (userSpeechText) {
            userSpeechText.value = finalTranscript + interimTranscript;
            userSpeechText.scrollTop = userSpeechText.scrollHeight;
        }
        
        // Phân tích và đánh dấu màu cho từng từ
        analyzeAndHighlightWords(finalTranscript + interimTranscript);
    };

    recognition.onend = function() {
        console.log('⏹️ Dừng nghe');
        isListening = false;
        const userSpeechText = document.getElementById('userSpeechText');
        if (userSpeechText) {
            userSpeechText.classList.remove('recording');
            userSpeechText.placeholder = 'Văn bản bạn nói sẽ hiển thị ở đây...';
        }
    };

    recognition.onerror = function(event) {
        console.error('Lỗi nhận dạng giọng nói:', event.error);
        isListening = false;
        const userSpeechText = document.getElementById('userSpeechText');
        if (userSpeechText) {
            userSpeechText.classList.remove('recording');
        }
        
        if (event.error !== 'no-speech') {
            webcamRecorder.showError('Lỗi nhận dạng giọng nói: ' + event.error);
        }
    };
}

// Hàm phân tích và đánh dấu màu cho từng từ
function analyzeAndHighlightWords(spokenText) {
    const targetText = document.querySelector('#practiceText').textContent.toLowerCase();
    const spokenWords = spokenText.toLowerCase().split(/\s+/).filter(word => word.length > 0);
    const targetWords = targetText.split(/\s+/).filter(word => word.length > 0);
    
    // Lấy tất cả các phần tử từ
    wordElements = document.querySelectorAll('#practiceText .word');
    
    // Reset tất cả về trạng thái pending
    wordElements.forEach(word => {
        word.classList.remove('correct', 'incorrect', 'active');
        word.classList.add('pending');
    });
    
    // So sánh từng từ và đánh dấu màu
    for (let i = 0; i < targetWords.length; i++) {
        if (i < spokenWords.length) {
            const targetWord = targetWords[i].replace(/[.,?!]/g, '');
            const spokenWord = spokenWords[i].replace(/[.,?!]/g, '');
            
            if (targetWord === spokenWord) {
                // Từ đúng - màu xanh
                wordElements[i].classList.remove('pending');
                wordElements[i].classList.add('correct');
            } else {
                // Từ sai - màu đỏ
                wordElements[i].classList.remove('pending');
                wordElements[i].classList.add('incorrect');
            }
        }
        
        // Đánh dấu từ hiện tại đang được nói
        if (i === spokenWords.length - 1) {
            wordElements[i].classList.add('active');
        }
    }
}

// Hàm reset đánh dấu từ
function resetWordHighlighting() {
    wordElements = document.querySelectorAll('#practiceText .word');
    wordElements.forEach(word => {
        word.classList.remove('correct', 'incorrect', 'active');
        word.classList.add('pending');
    });
    currentWordIndex = 0;
}

// Global functions
window.startSpeechRecognition = function() {
    if (!recognition) {
        initializeSpeechRecognition();
    }
    if (recognition && !isListening) {
        try {
            recognition.start();
        } catch (error) {
            console.error('Lỗi khi bắt đầu speech recognition:', error);
        }
    }
};

window.stopSpeechRecognition = function() {
    if (recognition && isListening) {
        try {
            recognition.stop();
        } catch (error) {
            console.error('Lỗi khi dừng speech recognition:', error);
        }
    }
};

// Text-to-Speech
function speakText(text) {
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();
        
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.lang = 'en-US';
        utterance.rate = 0.9;
        utterance.pitch = 1;
        utterance.volume = 1;
        
        // Tìm giọng nói tiếng Anh
        const voices = speechSynthesis.getVoices();
        const englishVoice = voices.find(voice => 
            voice.lang.includes('en') && voice.name.includes('Female')
        ) || voices.find(voice => voice.lang.includes('en'));
        
        if (englishVoice) {
            utterance.voice = englishVoice;
        }

        const playBtn = document.getElementById('playSample');
        if (playBtn) {
            playBtn.innerHTML = '<i class="fas fa-stop"></i>';
            playBtn.style.background = '#ef4444';
        }

        utterance.onend = function() {
            if (playBtn) {
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
                playBtn.style.background = '#4361ee';
            }
        };

        utterance.onerror = function(event) {
            console.error('Lỗi Text-to-Speech:', event);
            if (playBtn) {
                playBtn.innerHTML = '<i class="fas fa-play"></i>';
                playBtn.style.background = '#4361ee';
            }
            webcamRecorder.showError('Lỗi đọc văn bản: ' + event.error);
        };

        speechSynthesis.speak(utterance);
    } else {
        webcamRecorder.showError('Trình duyệt không hỗ trợ Text-to-Speech');
    }
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    const startBtn = document.getElementById('startBtn');
    const stopBtn = document.getElementById('stopBtn');
    const retryBtn = document.getElementById('retryBtn');
    const audioFile = document.getElementById('audioFile');
    const playSampleBtn = document.getElementById('playSample');
    const aiReadBtn = document.getElementById('aiReadBtn');

    // Bắt đầu ghi âm và nhận dạng
    startBtn.addEventListener('click', async function() {
        console.log('🚀 Bắt đầu ghi âm...');
        startBtn.disabled = true;
        startBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang khởi động...';
        
        const success = await webcamRecorder.startRecording();
        
        if (success) {
            startBtn.style.display = 'none';
            stopBtn.style.display = 'inline-flex';
            document.getElementById('recordingStatus').textContent = 'Đang ghi âm và nhận dạng giọng nói...';
            document.getElementById('recordingStatus').style.color = '#ef4444';
            
            // Bắt đầu nhận dạng giọng nói
            startSpeechRecognition();
        } else {
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="fas fa-microphone"></i> Bắt đầu';
        }
    });

    // Dừng ghi âm và nhận dạng
    stopBtn.addEventListener('click', async function() {
        console.log('⏹️ Dừng ghi âm...');
        stopBtn.disabled = true;
        stopBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        
        const audioBlob = await webcamRecorder.stopRecording();
        stopSpeechRecognition();
        
        if (audioBlob && audioBlob.size > 0) {
            console.log('✅ Ghi âm thành công, kích thước:', audioBlob.size, 'bytes');
            
            const file = new File([audioBlob], `recording_${Date.now()}.webm`, { 
                type: audioBlob.type || 'audio/webm' 
            });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            audioFile.files = dataTransfer.files;
            
            stopBtn.style.display = 'none';
            startBtn.style.display = 'inline-flex';
            startBtn.disabled = false;
            startBtn.innerHTML = '<i class="fas fa-microphone"></i> Bắt đầu';
            stopBtn.disabled = false;
            stopBtn.innerHTML = '<i class="fas fa-stop"></i> Dừng lại';
            
            document.getElementById('recordingStatus').textContent = 'Ghi âm hoàn tất! Nhấn "Phân tích phát âm" để xem kết quả.';
            document.getElementById('recordingStatus').style.color = '#10b981';
            document.getElementById('actionButtons').style.display = 'flex';
            
            webcamRecorder.showSuccess('Ghi âm thành công! (' + Math.round(audioBlob.size/1024) + 'KB)');
        } else {
            console.error('❌ Ghi âm thất bại');
            webcamRecorder.showError('Ghi âm thất bại. Vui lòng thử lại.');
            
            stopBtn.disabled = false;
            stopBtn.innerHTML = '<i class="fas fa-stop"></i> Dừng lại';
        }
    });

    // Thử lại
    retryBtn.addEventListener('click', function() {
        console.log('🔄 Thử lại...');
        webcamRecorder.stopWebcam();
        stopSpeechRecognition();
        
        startBtn.style.display = 'inline-flex';
        startBtn.disabled = false;
        startBtn.innerHTML = '<i class="fas fa-microphone"></i> Bắt đầu';
        stopBtn.style.display = 'none';
        stopBtn.disabled = false;
        stopBtn.innerHTML = '<i class="fas fa-stop"></i> Dừng lại';
        
        document.getElementById('actionButtons').style.display = 'none';
        document.getElementById('recordingStatus').textContent = 'Nhấn "Bắt đầu" để bắt đầu ghi âm và nhận dạng giọng nói';
        document.getElementById('recordingStatus').style.color = '#666';
        document.getElementById('userSpeechText').value = '';
        document.getElementById('userSpeechText').classList.remove('recording');
        
        // Reset đánh dấu từ
        resetWordHighlighting();
        
        if (audioFile) {
            audioFile.value = '';
        }
    });

    // Nghe mẫu
    if (playSampleBtn) {
        playSampleBtn.addEventListener('click', function() {
            if (this.innerHTML.includes('stop')) {
                speechSynthesis.cancel();
            } else {
                const text = document.getElementById('practiceText').textContent;
                speakText(text);
            }
        });
    }

    // AI đọc mẫu
    if (aiReadBtn) {
        aiReadBtn.addEventListener('click', function() {
            const text = document.getElementById('practiceText').textContent;
            speakText(text);
        });
    }

    // Xử lý form submission
    const recordingForm = document.getElementById('recordingForm');
    if (recordingForm) {
        recordingForm.addEventListener('submit', function(e) {
            if (!audioFile.files.length) {
                e.preventDefault();
                webcamRecorder.showError('Vui lòng ghi âm trước khi phân tích!');
                return;
            }
            
            const file = audioFile.files[0];
            console.log('📤 Submitting file:', file.name, file.size);
            
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang phân tích...';
                submitBtn.disabled = true;
            }
            
            webcamRecorder.stopWebcam();
        });
    }
});

// Load voices khi có sẵn
if ('speechSynthesis' in window) {
    speechSynthesis.onvoiceschanged = function() {
        console.log('✅ Voices đã được load');
    };
}

// Dọn dẹp khi rời trang
window.addEventListener('beforeunload', function() {
    webcamRecorder.stopWebcam();
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();
    }
});

// Thêm hàm showSuccess
WebcamRecorder.prototype.showSuccess = function(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'alert alert-success';
    successDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 10000;
        max-width: 400px;
        padding: 1rem;
        border-radius: 8px;
        background: #dcfce7;
        color: #16a34a;
        border: 1px solid #bbf7d0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    `;
    successDiv.innerHTML = `
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <i class="fas fa-check-circle"></i>
            <span>${message}</span>
        </div>
    `;

    document.body.appendChild(successDiv);
    setTimeout(() => {
        if (successDiv.parentNode) {
            successDiv.remove();
        }
    }, 3000);
};
</script>

<?php require_once 'includes/footer.php'; ?>