<?php
session_start();
require_once 'includes/db_connect.php';

if (isset($_POST['player_name']) && !empty(trim($_POST['player_name']))) {
    $_SESSION['player_name'] = trim($_POST['player_name']);
} else if (!isset($_SESSION['player_name'])) {
    header('Location: index.php');
    exit();
}

$_SESSION['current_score'] = 0;

$total_points_result = $conn->query("SELECT SUM(points) AS total FROM questions");
$total_points = $total_points_result->fetch_assoc()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>กำลังเล่นเกม...</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="assets/css/style.css">
    <style> body { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="game-background min-h-screen flex items-center justify-center p-4">
    <div id="game-container" class="bg-slate-50 text-gray-800 rounded-3xl shadow-2xl p-6 md:p-8 w-full max-w-3xl fade-in">
        <div class="flex justify-between items-center mb-6">
            <div class="bg-blue-100 text-blue-800 font-bold py-2 px-4 rounded-full shadow-sm">
                ผู้เล่น: <span class="font-semibold"><?php echo htmlspecialchars($_SESSION['player_name']); ?></span>
            </div>
            <div class="bg-purple-100 text-purple-800 text-sm font-bold p-2 rounded-full shadow-sm text-center">
                <div>คะแนนเต็ม: <?php echo $total_points; ?></div>
            </div>
            <div class="bg-yellow-400 text-purple-900 font-bold py-2 px-4 rounded-full shadow-lg">
                <i class="fas fa-star text-yellow-600"></i>
                คะแนน: <span id="score-display">0</span>
            </div>
        </div>
        <div id="question-area" class="text-center mb-6 min-h-[120px] flex items-center justify-center bg-white p-6 rounded-2xl shadow-inner">
             <div class="text-2xl font-bold">กำลังโหลดคำถาม...</div>
        </div>
        <div class="mb-4">
            <div class="flex justify-end items-center mb-1">
                <span id="time-display" class="font-bold text-lg text-purple-700">30</span>
                <i class="far fa-clock text-purple-700 ml-2"></i>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3">
                <div id="progress-bar" class="progress-bar-inner bg-gradient-to-r from-green-400 to-blue-500 h-3 rounded-full" style="width: 100%"></div>
            </div>
        </div>
        <div id="answers-area" class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6"></div>
    </div>
    
    <audio id="countdown-audio" src="https://www.soundjay.com/buttons/sounds/button-7.mp3" preload="auto"></audio>
    <footer class="text-center text-white/60 text-sm py-5 absolute bottom-0 w-full">
        <p>Developed by นายอนุชิต คุ้มบุ้งคล้า</p>
    </footer>

<script>
    let currentQuestionIndex = 0;
    let questionIds = []; 
    let timer;
    let timeLeft;
    const countdownAudio = document.getElementById('countdown-audio');
    const timeDisplay = document.getElementById('time-display');
    const progressBar = document.getElementById('progress-bar');
    const questionArea = document.getElementById('question-area');
    const answersArea = document.getElementById('answers-area');
    const scoreDisplay = document.getElementById('score-display');

    async function fetchQuestionIds() {
        try {
            const response = await fetch('get_question.php?action=get_ids');
            const data = await response.json();
            if (data.ids && data.ids.length > 0) {
                questionIds = data.ids;
                fetchQuestion(questionIds[currentQuestionIndex]);
            } else {
                showEndGame();
            }
        } catch (error) { console.error('Failed to fetch question IDs:', error); }
    }
    
    async function fetchQuestion(questionId) {
        if (questionId === undefined) {
            showEndGame();
            return;
        }
        try {
            const response = await fetch(`get_question.php?id=${questionId}`);
            const data = await response.json();
            if (data.error) {
                showEndGame();
            } else {
                displayQuestion(data);
                timeDisplay.dataset.totalTime = data.question.time_limit_seconds;
                startTimer(data.question.time_limit_seconds);
            }
        } catch (error) {
            console.error('Failed to fetch question:', error);
            questionArea.innerHTML = '<div class="text-red-500">เกิดข้อผิดพลาดในการโหลดคำถาม</div>';
        }
    }

    function displayQuestion(data) {
        let questionHTML = '';
        if (data.question.question_image_url) {
            questionHTML = `<img src="${data.question.question_image_url}" alt="คำถาม" class="max-w-full h-auto mx-auto rounded-lg shadow-md mb-4" style="max-height: 200px;"><h2 class="text-2xl font-bold">${data.question.question_text}</h2>`;
        } else {
            questionHTML = `<h2 class="text-3xl font-bold">${data.question.question_text}</h2>`;
        }
        questionArea.innerHTML = questionHTML;
        answersArea.innerHTML = '';
        data.answers.forEach(answer => {
            const button = document.createElement('button');
            button.className = 'answer-option bg-white p-4 rounded-lg shadow-md text-left font-semibold text-gray-700 border-2 border-transparent hover:border-purple-500';
            button.innerHTML = `<i class="far fa-circle mr-3"></i> ${answer.answer_text}`;
            button.onclick = () => selectAnswer(answer.id, data.question.id);
            answersArea.appendChild(button);
        });
    }

    function startTimer(duration) {
        timeLeft = duration;
        updateTimerDisplay();
        clearInterval(timer); 
        timer = setInterval(() => {
            timeLeft--;
            updateTimerDisplay();
            if (timeLeft > 0 && timeLeft <= 5) {
                countdownAudio.currentTime = 0;
                countdownAudio.play().catch(e => console.log("Audio play failed"));
            }
            if (timeLeft <= 0) {
                clearInterval(timer);
                timeUp();
            }
        }, 1000);
    }

    function updateTimerDisplay() {
        timeDisplay.innerText = timeLeft;
        const totalTime = parseInt(timeDisplay.dataset.totalTime || timeLeft);
        const percentage = totalTime > 0 ? (timeLeft / totalTime) * 100 : 0;
        progressBar.style.width = `${percentage}%`;
        if (percentage < 25) {
            progressBar.classList.remove('from-green-400', 'to-blue-500');
            progressBar.classList.add('bg-red-500');
        } else {
            progressBar.classList.add('from-green-400', 'to-blue-500');
            progressBar.classList.remove('bg-red-500');
        }
    }

    async function selectAnswer(answerId, questionId) {
        clearInterval(timer); 
        const response = await fetch(`check_answer.php?question_id=${questionId}&answer_id=${answerId}`);
        const result = await response.json();
        scoreDisplay.innerText = result.current_score;

        if (result.correct) {
            Swal.fire({
                icon: 'success',
                title: 'ถูกต้อง!',
                text: 'ไปข้อต่อไปกันเลย',
                timer: 1500,
                showConfirmButton: false
            });
        } else {
            // === แก้ไข Pop-up แจ้งเตือนเมื่อตอบผิด ===
            Swal.fire({
                icon: 'error',
                title: 'ผิดครับ!',
                text: 'น่าเสียดาย ลองใหม่ในข้อต่อไปนะ',
                timer: 2000, // เพิ่มเวลาเล็กน้อย
                showConfirmButton: false
            });
            // ======================================
        }

        // ปรับเวลาให้สอดคล้องกับ Pop-up
        setTimeout(() => {
            currentQuestionIndex++;
            fetchQuestion(questionIds[currentQuestionIndex]);
        }, result.correct ? 1500 : 2000);
    }
    
    function timeUp() {
        Swal.fire({ icon: 'warning', title: 'หมดเวลา!', text: 'น่าเสียดายจัง ลองใหม่ในข้อต่อไปนะ', timer: 2000, showConfirmButton: false });
         setTimeout(() => {
            currentQuestionIndex++;
            fetchQuestion(questionIds[currentQuestionIndex]);
        }, 2000);
    }

    function showEndGame() {
        clearInterval(timer);
        const finalScore = scoreDisplay.innerText;
        const playerName = "<?php echo htmlspecialchars($_SESSION['player_name']); ?>";
        document.getElementById('game-container').innerHTML = `
            <div class="text-center">
                <i class="fas fa-trophy text-6xl text-yellow-500 mb-4"></i>
                <h2 class="text-4xl font-bold mb-2">จบเกมแล้ว, ${playerName}!</h2>
                <p class="text-2xl mb-6">คะแนนของคุณคือ: <span class="font-bold text-purple-600">${finalScore}</span></p>
                <div id="end-game-buttons" class="flex flex-col items-center gap-4">
                     <button id="save-score-btn" class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-8 rounded-full text-lg focus:outline-none focus:shadow-outline">
                        <i class="fas fa-save mr-2"></i> บันทึกคะแนน
                    </button>
                    <a href="index.php" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-play mr-1"></i> เล่นอีกครั้ง (เปลี่ยนชื่อ)
                    </a>
                    <a href="leaderboard.php" class="text-purple-600 hover:text-purple-800 font-semibold">
                        <i class="fas fa-medal mr-1"></i> ดูอันดับคะแนน
                    </a>
                </div>
            </div>`;

        document.getElementById('save-score-btn').addEventListener('click', async function() {
            const button = this;
            button.disabled = true;
            button.innerHTML = 'กำลังบันทึก...';
            try {
                const response = await fetch('save_score.php', { method: 'POST' });
                const result = await response.json();
                if(result.success) {
                    Swal.fire('บันทึกสำเร็จ!', 'คะแนนของคุณถูกบันทึกแล้ว', 'success').then(() => {
                        window.location.href = 'leaderboard.php';
                    });
                } else {
                     Swal.fire('เกิดข้อผิดพลาด', result.message || 'ไม่สามารถบันทึกคะแนนได้', 'error');
                     button.disabled = false;
                     button.innerHTML = '<i class="fas fa-save mr-2"></i> บันทึกคะแนน';
                }
            } catch (error) {
                Swal.fire('เกิดข้อผิดพลาด', 'การเชื่อมต่อล้มเหลว', 'error');
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-save mr-2"></i> บันทึกคะแนน';
            }
        });
    }
    
    document.addEventListener('DOMContentLoaded', () => { fetchQuestionIds(); });
</script>
</body>
</html>