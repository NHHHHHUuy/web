// assets/js/main.js - Complete Version

class WebcamRecorder {
    constructor() {
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.stream = null;
        this.isRecording = false;
        this.recordBtn = document.getElementById('recordBtn');
        this.recordingStatus = document.getElementById('recordingStatus');
        this.actionButtons = document.getElementById('actionButtons');
        this.userSpeechSection = document.getElementById('userSpeechSection');
    }

    async startWebcam() {
        try {
            console.log('🎤 Đang yêu cầu quyền microphone...');
            
            this.stream = await navigator.mediaDevices.getUserMedia({
                audio: {
                    echoCancellation: false,
                    noiseSuppression: false,  
                    autoGainControl: false,
                    channelCount: 1,
                    sampleRate: 16000
                },
                video: false
            });

            if (!this.stream) {
                throw new Error('Không nhận được stream âm thanh');
            }

            const audioTracks = this.stream.getAudioTracks();
            console.log('✅ Microphone đã kết nối:', audioTracks);

            if (audioTracks.length === 0) {
                throw new Error('Không tìm thấy microphone');
            }

            return true;

        } catch (error) {
            console.error('❌ Lỗi microphone:', error);
            this.showError(this.getErrorMessage(error));
            return false;
        }
    }

    getErrorMessage(error) {
        switch (error.name) {
            case 'NotAllowedError':
                return 'Vui lòng cho phép truy cập microphone trong trình duyệt';
            case 'NotFoundError':
                return 'Không tìm thấy microphone. Vui lòng kiểm tra thiết bị';
            case 'NotReadableError':
                return 'Microphone đang bị ứng dụng khác sử dụng';
            case 'OverconstrainedError':
                return 'Cấu hình microphone không được hỗ trợ';
            default:
                return `Lỗi microphone: ${error.message}`;
        }
    }

    getSupportedMimeType() {
        const testTypes = [
            'audio/webm',
            'audio/webm;codecs=opus',
            'audio/ogg;codecs=opus',
            'audio/mp4',
            'audio/wav'
        ];

        for (const type of testTypes) {
            try {
                if (MediaRecorder.isTypeSupported(type)) {
                    console.log('✅ Định dạng được hỗ trợ:', type);
                    return type;
                }
            } catch (e) {
                console.warn('Lỗi kiểm tra MIME type:', type, e);
            }
        }

        console.log('⚠️ Không tìm thấy MIME type phù hợp, dùng mặc định');
        return '';
    }

    async startRecording() {
        try {
            if (!this.stream) {
                const success = await this.startWebcam();
                if (!success) return false;
            }

            this.audioChunks = [];

            let mediaRecorder;
            let lastError = null;

            const mimeType = this.getSupportedMimeType();
            if (mimeType) {
                try {
                    console.log('🔄 Thử MediaRecorder với MIME:', mimeType);
                    mediaRecorder = new MediaRecorder(this.stream, { mimeType });
                    console.log('✅ Thành công với MIME type');
                } catch (e) {
                    lastError = e;
                    console.warn('❌ Lỗi với MIME type, thử phương pháp khác:', e.message);
                }
            }

            if (!mediaRecorder) {
                try {
                    console.log('🔄 Thử MediaRecorder không options');
                    mediaRecorder = new MediaRecorder(this.stream);
                    console.log('✅ Thành công không options');
                } catch (e) {
                    lastError = e;
                    console.warn('❌ Lỗi không options:', e.message);
                }
            }

            if (!mediaRecorder) {
                try {
                    console.log('🔄 Thử MediaRecorder với timeslice mặc định');
                    mediaRecorder = new MediaRecorder(this.stream, { 
                        mimeType: 'audio/webm',
                        audioBitsPerSecond: 128000 
                    });
                    console.log('✅ Thành công với timeslice mặc định');
                } catch (e) {
                    lastError = e;
                    console.warn('❌ Lỗi timeslice mặc định:', e.message);
                }
            }

            if (!mediaRecorder) {
                throw new Error(`Không thể khởi tạo MediaRecorder: ${lastError?.message || 'Unknown error'}`);
            }

            this.mediaRecorder = mediaRecorder;

            this.mediaRecorder.ondataavailable = (event) => {
                if (event.data && event.data.size > 0) {
                    this.audioChunks.push(event.data);
                    console.log('📦 Nhận audio data:', event.data.size, 'bytes');
                }
            };

            this.mediaRecorder.onerror = (event) => {
                console.error('❌ MediaRecorder error:', event.error);
                this.showError(`Lỗi ghi âm: ${event.error.name}`);
                this.stopRecording();
            };

            this.mediaRecorder.onstart = () => {
                console.log('🎤 Ghi âm đã bắt đầu!');
                this.isRecording = true;
                this.updateUI('recording');
                
                // Bắt đầu speech recognition
                if (typeof startSpeechRecognition === 'function') {
                    startSpeechRecognition();
                }
            };

            console.log('🚀 Bắt đầu ghi âm...');
            this.mediaRecorder.start(500);

            setTimeout(() => {
                if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
                    console.log('✅ MediaRecorder đang chạy ổn định');
                } else {
                    console.error('❌ MediaRecorder không chạy được');
                    this.showError('Không thể bắt đầu ghi âm. Vui lòng thử lại.');
                    this.stopRecording();
                }
            }, 100);

            return true;

        } catch (error) {
            console.error('❌ Lỗi khởi tạo ghi âm:', error);
            this.showError(`Lỗi hệ thống: ${error.message}`);
            return false;
        }
    }

    stopRecording() {
        return new Promise((resolve) => {
            if (this.mediaRecorder && this.isRecording) {
                console.log('⏹️ Đang dừng ghi âm...');
                
                this.mediaRecorder.onstop = () => {
                    console.log('✅ Đã dừng ghi âm');
                    const audioBlob = new Blob(this.audioChunks, {
                        type: this.audioChunks[0]?.type || 'audio/webm'
                    });
                    
                    this.isRecording = false;
                    console.log('📊 Tổng kích thước audio:', audioBlob.size, 'bytes');
                    
                    this.updateUI('stopped');
                    
                    // Dừng speech recognition
                    if (typeof stopSpeechRecognition === 'function') {
                        stopSpeechRecognition();
                    }
                    
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

    updateUI(state) {
        if (state === 'recording') {
            if (this.recordBtn) {
                this.recordBtn.classList.add('recording');
                this.recordBtn.innerHTML = '<i class="fas fa-stop"></i>';
                this.recordBtn.disabled = false;
            }
            if (this.recordingStatus) {
                this.recordingStatus.textContent = 'Đang ghi âm... Nhấn để dừng';
                this.recordingStatus.style.color = '#ef4444';
            }
            if (this.actionButtons) {
                this.actionButtons.style.display = 'none';
            }
        } else if (state === 'stopped') {
            if (this.recordBtn) {
                this.recordBtn.classList.remove('recording');
                this.recordBtn.innerHTML = '<i class="fas fa-microphone"></i>';
                this.recordBtn.disabled = false;
            }
            if (this.recordingStatus) {
                this.recordingStatus.textContent = 'Ghi âm hoàn tất!';
                this.recordingStatus.style.color = '#16a34a';
            }
            if (this.actionButtons) {
                this.actionButtons.style.display = 'flex';
            }
        }
    }

    stopWebcam() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => {
                track.stop();
            });
            this.stream = null;
        }
        this.isRecording = false;
    }

    showError(message) {
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

    showSuccess(message) {
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
    }
}

// Khởi tạo recorder
const webcamRecorder = new WebcamRecorder();

// Kiểm tra hỗ trợ trình duyệt
function checkBrowserSupport() {
    console.log('🔍 Kiểm tra hỗ trợ trình duyệt...');
    
    const checks = {
        mediaDevices: !!navigator.mediaDevices,
        getUserMedia: !!navigator.mediaDevices?.getUserMedia,
        MediaRecorder: !!window.MediaRecorder,
        AudioContext: !!(window.AudioContext || window.webkitAudioContext),
        SpeechRecognition: !!(window.SpeechRecognition || window.webkitSpeechRecognition),
        speechSynthesis: !!window.speechSynthesis
    };

    console.log('Kết quả kiểm tra:', checks);

    if (!checks.MediaRecorder) {
        webcamRecorder.showError('Trình duyệt không hỗ trợ MediaRecorder. Vui lòng dùng Chrome/Firefox.');
        return false;
    }

    if (!checks.getUserMedia) {
        webcamRecorder.showError('Trình duyệt không hỗ trợ truy cập microphone.');
        return false;
    }

    if (!checks.SpeechRecognition) {
        console.warn('Trình duyệt không hỗ trợ Speech Recognition');
    }

    if (!checks.speechSynthesis) {
        console.warn('Trình duyệt không hỗ trợ Text-to-Speech');
    }

    console.log('✅ Trình duyệt hỗ trợ đầy đủ');
    return true;
}

// Khởi tạo khi trang load
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Trang đã load, khởi tạo...');
    
    if (!checkBrowserSupport()) {
        return;
    }

    initializeRecording();
    initializeCharts();
    initializeMaterialsFilter();
    initializeSpeechFeatures();
});

function initializeRecording() {
    const recordBtn = document.getElementById('recordBtn');
    const audioFile = document.getElementById('audioFile');
    const retryBtn = document.getElementById('retryBtn');

    console.log('🎯 Khởi tạo controls recording...');

    if (recordBtn) {
        recordBtn.addEventListener('click', async function() {
            console.log('🖱️ Nhấn nút ghi âm');
            
            if (!webcamRecorder.isRecording) {
                console.log('▶️ Bắt đầu ghi âm...');
                recordBtn.disabled = true;
                recordBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                const success = await webcamRecorder.startRecording();
                
                if (!success) {
                    recordBtn.disabled = false;
                    recordBtn.innerHTML = '<i class="fas fa-microphone"></i>';
                }
            } else {
                console.log('⏸️ Dừng ghi âm...');
                recordBtn.disabled = true;
                recordBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                const audioBlob = await webcamRecorder.stopRecording();
                
                if (audioBlob && audioBlob.size > 0) {
                    console.log('✅ Ghi âm thành công:', audioBlob.size, 'bytes');
                    webcamRecorder.showSuccess(`Ghi âm thành công! (${Math.round(audioBlob.size/1024)}KB)`);
                    
                    if (audioFile) {
                        const file = new File([audioBlob], `recording_${Date.now()}.webm`, {
                            type: 'audio/webm'
                        });
                        
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        audioFile.files = dataTransfer.files;
                        
                        console.log('📁 File đã tạo:', file.name);
                    }
                } else {
                    console.warn('⚠️ Không có dữ liệu audio');
                    webcamRecorder.showError('Không có dữ liệu âm thanh. Vui lòng thử lại.');
                }
            }
        });
    }

    if (retryBtn) {
        retryBtn.addEventListener('click', function() {
            console.log('🔄 Reset recorder');
            webcamRecorder.stopWebcam();
            webcamRecorder.updateUI('stopped');
            
            // Reset speech recognition
            const userSpeechSection = document.getElementById('userSpeechSection');
            const userSpeechText = document.getElementById('userSpeechText');
            const confidenceScore = document.getElementById('confidenceScore');
            
            if (userSpeechSection) userSpeechSection.style.display = 'none';
            if (userSpeechText) userSpeechText.innerHTML = '';
            if (confidenceScore) {
                confidenceScore.textContent = '';
                confidenceScore.className = 'confidence-score';
            }
            
            if (audioFile) {
                audioFile.value = '';
            }
            
            webcamRecorder.showSuccess('Đã reset!');
        });
    }

    // Xử lý form submission
    const recordingForm = document.getElementById('recordingForm');
    if (recordingForm) {
        recordingForm.addEventListener('submit', function(e) {
            if (!audioFile.files.length) {
                e.preventDefault();
                webcamRecorder.showError('Vui lòng ghi âm trước khi gửi!');
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
}

// Khởi tạo speech features
function initializeSpeechFeatures() {
    console.log('🎤 Khởi tạo tính năng speech...');
    
    // AI Text-to-Speech
    const aiReadBtn = document.getElementById('aiReadBtn');
    const playSampleBtn = document.getElementById('playSample');
    const playUserRecording = document.getElementById('playUserRecording');

    if (aiReadBtn) {
        aiReadBtn.addEventListener('click', function() {
            const text = document.getElementById('practiceText').textContent;
            speakText(text);
        });
    }

    if (playSampleBtn) {
        playSampleBtn.addEventListener('click', function() {
            const text = document.getElementById('practiceText').textContent;
            speakText(text);
        });
    }

    if (playUserRecording) {
        playUserRecording.addEventListener('click', function() {
            const audioFile = document.getElementById('audioFile').files[0];
            if (audioFile) {
                const audioUrl = URL.createObjectURL(audioFile);
                const audio = new Audio(audioUrl);
                audio.play().catch(e => {
                    console.error('Lỗi phát audio:', e);
                    webcamRecorder.showError('Không thể phát bản ghi âm');
                });
            } else {
                webcamRecorder.showError('Không có bản ghi âm để phát');
            }
        });
    }

    // Dừng speech synthesis khi click nút stop
    if (playSampleBtn) {
        playSampleBtn.addEventListener('click', function() {
            if (this.innerHTML.includes('stop')) {
                speechSynthesis.cancel();
                this.innerHTML = '<i class="fas fa-play"></i>';
                this.style.backgroundColor = '#4361ee';
            }
        });
    }

    // Khởi tạo speech recognition
    initializeSpeechRecognition();
}

// Speech Recognition
function initializeSpeechRecognition() {
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    
    if (!SpeechRecognition) {
        console.log('Trình duyệt không hỗ trợ Speech Recognition');
        return;
    }

    const recognition = new SpeechRecognition();
    recognition.continuous = false;
    recognition.interimResults = true;
    recognition.lang = 'en-US';

    let finalTranscript = '';

    recognition.onstart = function() {
        console.log('Speech recognition started');
        const userSpeechSection = document.getElementById('userSpeechSection');
        const userSpeechText = document.getElementById('userSpeechText');
        
        if (userSpeechSection) userSpeechSection.style.display = 'block';
        if (userSpeechText) userSpeechText.innerHTML = '<em>Đang nghe...</em>';
    };

    recognition.onresult = function(event) {
        let interimTranscript = '';
        
        for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript;
            if (event.results[i].isFinal) {
                finalTranscript += transcript + ' ';
            } else {
                interimTranscript += transcript;
            }
        }

        const displayText = finalTranscript + '<span style="color: #666;">' + interimTranscript + '</span>';
        const userSpeechText = document.getElementById('userSpeechText');
        if (userSpeechText) {
            userSpeechText.innerHTML = displayText || '<em>Đang nghe...</em>';
        }
    };

    recognition.onend = function() {
        console.log('Speech recognition ended');
        if (finalTranscript) {
            // Tính confidence score giả lập
            const confidence = Math.random() * 100;
            const confidenceElem = document.getElementById('confidenceScore');
            if (confidenceElem) {
                confidenceElem.textContent = `Độ chính xác: ${Math.round(confidence)}%`;
                
                if (confidence > 80) {
                    confidenceElem.className = 'confidence-score high';
                } else if (confidence > 60) {
                    confidenceElem.className = 'confidence-score medium';
                } else {
                    confidenceElem.className = 'confidence-score low';
                }
            }
        }
    };

    recognition.onerror = function(event) {
        console.error('Speech recognition error:', event.error);
    };

    // Bắt đầu recognition khi bắt đầu ghi âm
    window.startSpeechRecognition = function() {
        finalTranscript = '';
        recognition.start();
    };

    // Dừng recognition khi dừng ghi âm
    window.stopSpeechRecognition = function() {
        recognition.stop();
    };
}

// AI Text-to-Speech
function speakText(text) {
    if ('speechSynthesis' in window) {
        // Dừng bất kỳ speech nào đang chạy
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

        const playSampleBtn = document.getElementById('playSample');
        
        utterance.onstart = function() {
            console.log('AI đang đọc...');
            if (playSampleBtn) {
                playSampleBtn.innerHTML = '<i class="fas fa-stop"></i>';
                playSampleBtn.style.backgroundColor = '#ef4444';
            }
        };

        utterance.onend = function() {
            console.log('AI đọc xong');
            if (playSampleBtn) {
                playSampleBtn.innerHTML = '<i class="fas fa-play"></i>';
                playSampleBtn.style.backgroundColor = '#4361ee';
            }
        };

        utterance.onerror = function(event) {
            console.error('Text-to-Speech error:', event);
            if (playSampleBtn) {
                playSampleBtn.innerHTML = '<i class="fas fa-play"></i>';
                playSampleBtn.style.backgroundColor = '#4361ee';
            }
            webcamRecorder.showError('Lỗi đọc văn bản: ' + event.error);
        };

        speechSynthesis.speak(utterance);
    } else {
        webcamRecorder.showError('Trình duyệt không hỗ trợ Text-to-Speech');
    }
}

// Khởi tạo biểu đồ
function initializeCharts() {
    const progressChart = document.getElementById('progressChart');
    if (progressChart) {
        try {
            const ctx = progressChart.getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Tuần 1', 'Tuần 2', 'Tuần 3', 'Tuần 4'],
                    datasets: [{
                        label: 'Phát âm',
                        data: [65, 72, 78, 85],
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Độ trôi chảy',
                        data: [60, 68, 75, 82],
                        borderColor: '#4cc9f0',
                        backgroundColor: 'rgba(76, 201, 240, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Độ chính xác',
                        data: [70, 76, 80, 88],
                        borderColor: '#4ade80',
                        backgroundColor: 'rgba(74, 222, 128, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Tiến Độ Học Tập'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            });
            console.log('✅ Biểu đồ đã được khởi tạo');
        } catch (error) {
            console.error('❌ Lỗi khởi tạo biểu đồ:', error);
        }
    }
}

// Khởi tạo bộ lọc tài liệu
function initializeMaterialsFilter() {
    const categoryFilter = document.getElementById('categoryFilter');
    const levelFilter = document.getElementById('levelFilter');
    const materialCards = document.querySelectorAll('.material-card');

    function filterMaterials() {
        const selectedCategory = categoryFilter ? categoryFilter.value : '';
        const selectedLevel = levelFilter ? levelFilter.value : '';

        let visibleCount = 0;

        materialCards.forEach(card => {
            const cardCategory = card.dataset.category;
            const cardLevel = card.dataset.level;

            const categoryMatch = !selectedCategory || cardCategory === selectedCategory;
            const levelMatch = !selectedLevel || cardLevel === selectedLevel;

            if (categoryMatch && levelMatch) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        // Hiển thị thông báo nếu không có kết quả
        const noResults = document.getElementById('noResults');
        if (!noResults && visibleCount === 0 && materialCards.length > 0) {
            const container = materialCards[0].parentNode;
            const message = document.createElement('div');
            message.id = 'noResults';
            message.className = 'no-results';
            message.innerHTML = `
                <i class="fas fa-search"></i>
                <p>Không tìm thấy tài liệu phù hợp với bộ lọc</p>
            `;
            container.appendChild(message);
        } else if (noResults && visibleCount > 0) {
            noResults.remove();
        }
    }

    if (categoryFilter) {
        categoryFilter.addEventListener('change', filterMaterials);
    }

    if (levelFilter) {
        levelFilter.addEventListener('change', filterMaterials);
    }

    // Áp dụng bộ lọc ban đầu
    filterMaterials();
}

// Utility functions
function formatDate(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    } catch (error) {
        console.error('Lỗi định dạng ngày:', error);
        return dateString;
    }
}

function formatTime(dateString) {
    try {
        const date = new Date(dateString);
        return date.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch (error) {
        console.error('Lỗi định dạng thời gian:', error);
        return dateString;
    }
}

function showLoading(message = 'Đang xử lý...') {
    hideLoading(); // Đảm bảo chỉ có một loading

    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'loading-overlay';
    loadingDiv.innerHTML = `
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <p>${message}</p>
        </div>
    `;
    document.body.appendChild(loadingDiv);
    
    return loadingDiv;
}

function hideLoading() {
    const loadingDiv = document.querySelector('.loading-overlay');
    if (loadingDiv) {
        loadingDiv.remove();
    }
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Load voices khi có sẵn
if ('speechSynthesis' in window) {
    speechSynthesis.onvoiceschanged = function() {
        console.log('✅ Voices đã được load');
    };
}

// Xử lý sự kiện trước khi rời trang
window.addEventListener('beforeunload', function() {
    console.log('👋 Đang rời trang...');
    webcamRecorder.stopWebcam();
    
    // Dừng tất cả speech synthesis
    if ('speechSynthesis' in window) {
        speechSynthesis.cancel();
    }
});

// Thêm CSS cho loading và các component
const style = document.createElement('style');
style.textContent = `
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }
    
    .loading-spinner {
        background: white;
        padding: 2rem;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        min-width: 200px;
    }
    
    .loading-spinner i {
        font-size: 2rem;
        color: #4361ee;
        margin-bottom: 1rem;
    }
    
    .loading-spinner p {
        margin: 0;
        color: #333;
        font-weight: 500;
    }
    
    .btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .no-results {
        text-align: center;
        padding: 3rem;
        color: #666;
        grid-column: 1 / -1;
    }
    
    .no-results i {
        font-size: 3rem;
        margin-bottom: 1rem;
        display: block;
        color: #999;
    }
    
    .confidence-score {
        background: #e9ecef;
        padding: 0.3rem 0.8rem;
        border-radius: 15px;
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .confidence-score.high {
        background: #d4edda;
        color: #155724;
    }
    
    .confidence-score.medium {
        background: #fff3cd;
        color: #856404;
    }
    
    .confidence-score.low {
        background: #f8d7da;
        color: #721c24;
    }
`;
document.head.appendChild(style);

// Hiển thị cảnh báo trình duyệt
document.addEventListener('DOMContentLoaded', function() {
    const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
    const isFirefox = typeof InstallTrigger !== 'undefined';
    const isEdge = /Edg/.test(navigator.userAgent);
    
    if (!isChrome && !isFirefox && !isEdge) {
        const browserSupport = document.getElementById('browserSupport');
        if (browserSupport) {
            browserSupport.style.display = 'block';
        }
    }
});

console.log('✅ main.js đã được load hoàn chỉnh');