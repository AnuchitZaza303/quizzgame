<?php
require_once 'includes/db_connect.php';

// 1. ดึงข้อมูลคะแนนสูงสุด 10 อันดับแรก
$stmt = $conn->prepare("SELECT player_name, score, played_at FROM player_scores ORDER BY score DESC, played_at DESC LIMIT 10");
$stmt->execute();
$scores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 2. คำนวณคะแนนเต็มทั้งหมด
$total_points_result = $conn->query("SELECT SUM(points) AS total FROM questions");
$total_points = $total_points_result->fetch_assoc()['total'] ?? 0;

$conn->close();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>อันดับคะแนน (Leaderboard)</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style> body { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="leaderboard-background min-h-screen flex items-center justify-center p-4">
    <div class="container mx-auto max-w-3xl fade-in">
        <div class="bg-white/90 backdrop-blur-xl p-8 rounded-3xl shadow-2xl">
            <h1 class="text-4xl md:text-5xl font-bold text-center text-gray-800 mb-8 flex items-center justify-center drop-shadow-sm">
                <i class="fas fa-medal text-yellow-500 mr-4 text-5xl"></i>
                ตารางอันดับผู้เล่น
            </h1>
            <div class="overflow-hidden">
                <table class="w-full text-left">
                    <thead>
                        <tr class="border-b-2 border-gray-300">
                            <th class="py-3 px-4 text-center text-gray-500 font-bold uppercase text-sm w-16">อันดับ</th>
                            <th class="py-3 px-4 text-gray-500 font-bold uppercase text-sm">ชื่อผู้เล่น</th>
                            <th class="py-3 px-4 text-left text-gray-500 font-bold uppercase text-sm">คะแนน (ที่ทำได้ / เต็ม)</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 leaderboard-table">
                        <?php foreach($scores as $index => $row): ?>
                        <?php
                            // คำนวณเปอร์เซ็นต์สำหรับ Progress bar
                            $percentage = ($total_points > 0) ? ($row['score'] / $total_points) * 100 : 0;
                        ?>
                        <tr class="border-b border-gray-200 last:border-b-0">
                            <td class="py-4 px-4 text-center text-2xl font-bold text-gray-600">
                                <?php if($index == 0): ?> <span title="อันดับ 1">🥇</span>
                                <?php elseif($index == 1): ?> <span title="อันดับ 2">🥈</span>
                                <?php elseif($index == 2): ?> <span title="อันดับ 3">🥉</span>
                                <?php else: echo $index + 1; ?>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 text-lg font-semibold"><?php echo htmlspecialchars($row['player_name']); ?></td>
                            
                            <td class="py-4 px-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-full bg-gray-200 rounded-full h-4 shadow-inner">
                                        <div class="bg-gradient-to-r from-cyan-400 to-purple-500 h-4 rounded-full" 
                                             style="width: <?php echo $percentage; ?>%; transition: width 0.5s ease-in-out;">
                                        </div>
                                    </div>
                                    <div class="text-lg font-bold text-purple-700 whitespace-nowrap">
                                        <?php echo number_format($row['score']); ?> 
                                        <span class="text-sm font-normal text-gray-500">/ <?php echo $total_points; ?></span>
                                    </div>
                                </div>
                            </td>
                            </tr>
                        <?php endforeach; ?>
                         <?php if (empty($scores)): ?>
                            <tr><td colspan="3" class="text-center py-8 text-gray-500">ยังไม่มีข้อมูลคะแนน</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
             <div class="text-center mt-10">
                <a href="index.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-8 rounded-full transition-colors shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-home mr-2"></i> กลับหน้าแรก
                </a>
            </div>
        </div>
    </div>
<?php require_once 'includes/footer.php'; ?>
</body>
</html>