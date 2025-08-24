<?php
session_start();
require_once 'includes/db_connect.php';

$settings = [];
$result = $conn->query("SELECT * FROM settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($settings['welcome_title'] ?? 'เกมตอบคำถาม'); ?></title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-purple-800 via-violet-900 to-purple-800 min-h-screen flex items-center justify-center text-white p-4">

    <div class="text-center bg-white/10 backdrop-blur-xl p-8 md:p-12 rounded-3xl shadow-2xl border border-white/20 fade-in main-container">
        <i class="fas fa-brain text-7xl mb-4 text-yellow-300 drop-shadow-lg fade-in-up"></i>
        <h1 class="text-4xl md:text-6xl font-bold mb-3 drop-shadow-md fade-in-up">
            <?php echo htmlspecialchars($settings['welcome_title'] ?? 'ยินดีต้อนรับ!'); ?>
        </h1>
        <p class="text-lg md:text-xl mb-10 text-white/80 fade-in-up">
            <?php echo htmlspecialchars($settings['welcome_subtitle'] ?? 'มาเริ่มเล่นกันเลย'); ?>
        </p>

        <form action="game.php" method="POST" class="flex flex-col items-center gap-4 fade-in-up" style="animation-delay: 0.8s;">
            <input type="text" name="player_name" placeholder="กรอกชื่อของคุณที่นี่..." 
                   class="w-full max-w-sm text-center bg-white/20 border-2 border-white/30 rounded-full py-3 px-6 text-xl text-white placeholder-white/70 focus:outline-none focus:ring-2 focus:ring-yellow-300 transition" 
                   required>
            <button type="submit"
               class="start-button inline-block bg-yellow-400 text-purple-900 font-bold py-4 px-10 rounded-full text-2xl hover:bg-yellow-300 transform hover:scale-105 transition-transform duration-300 shadow-xl">
                <i class="fas fa-play mr-2"></i>
                <?php echo htmlspecialchars($settings['start_button_text'] ?? 'เริ่มเกม'); ?>
            </button>
        </form>
        <div class="mt-8 leaderboard-link fade-in-up" style="animation-delay: 1s;">
            <a href="leaderboard.php" class="text-yellow-300 hover:text-white font-semibold transition-colors text-lg">
                <i class="fas fa-medal mr-1"></i> ดูอันดับคะแนน
            </a>
        </div>
    </div>

<?php require_once 'includes/footer.php'; ?>
</body>
</html>