<?php
require_once '../includes/db_connect.php';

// --- ดึงข้อมูลสถิติต่างๆ ---

// 1. นับจำนวนคำถามทั้งหมด
$questions_result = $conn->query("SELECT COUNT(id) as total_questions FROM questions");
$total_questions = $questions_result->fetch_assoc()['total_questions'];

// 2. นับจำนวนผู้เล่นทั้งหมด และคะแนนเฉลี่ย
$scores_result = $conn->query("SELECT COUNT(id) as total_plays, AVG(score) as average_score FROM player_scores");
$stats = $scores_result->fetch_assoc();
$total_plays = $stats['total_plays'];
$average_score = round($stats['average_score'] ?? 0, 2); // ปัดเศษทศนิยม 2 ตำแหน่ง

// 3. ดึงข้อมูล 5 ผู้เล่นล่าสุด
$latest_players_result = $conn->query("SELECT player_name, score, played_at FROM player_scores ORDER BY played_at DESC LIMIT 5");
$latest_players = $latest_players_result->fetch_all(MYSQLI_ASSOC);


include 'header.php'; 
?>

<h1 class="text-4xl font-bold text-gray-800 mb-6">แดชบอร์ดและสถิติ</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-6 border-l-4 border-blue-500">
        <div class="bg-blue-100 p-4 rounded-full">
            <i class="fas fa-question-circle text-3xl text-blue-600"></i>
        </div>
        <div>
            <p class="text-gray-500 text-sm font-semibold">จำนวนคำถามทั้งหมด</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_questions); ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-6 border-l-4 border-green-500">
        <div class="bg-green-100 p-4 rounded-full">
            <i class="fas fa-users text-3xl text-green-600"></i>
        </div>
        <div>
            <p class="text-gray-500 text-sm font-semibold">จำนวนการเล่นทั้งหมด</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo number_format($total_plays); ?></p>
        </div>
    </div>

    <div class="bg-white p-6 rounded-2xl shadow-lg flex items-center gap-6 border-l-4 border-yellow-500">
        <div class="bg-yellow-100 p-4 rounded-full">
            <i class="fas fa-star-half-alt text-3xl text-yellow-600"></i>
        </div>
        <div>
            <p class="text-gray-500 text-sm font-semibold">คะแนนเฉลี่ย</p>
            <p class="text-3xl font-bold text-gray-800"><?php echo $average_score; ?></p>
        </div>
    </div>
</div>

<div class="bg-white p-8 rounded-2xl shadow-lg">
    <h2 class="text-2xl font-bold mb-4 text-gray-800">ผู้เล่น 5 คนล่าสุด</h2>
    <div class="overflow-x-auto">
        <table class="w-full table-auto">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-4 text-left text-gray-600 font-semibold text-sm">ชื่อผู้เล่น</th>
                    <th class="py-3 px-4 text-center text-gray-600 font-semibold text-sm">คะแนน</th>
                    <th class="py-3 px-4 text-right text-gray-600 font-semibold text-sm">วันที่เล่น</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php if (!empty($latest_players)): ?>
                    <?php foreach ($latest_players as $player): ?>
                    <tr class="border-b hover:bg-gray-50">
                        <td class="py-3 px-4"><?php echo htmlspecialchars($player['player_name']); ?></td>
                        <td class="py-3 px-4 text-center font-semibold text-purple-700"><?php echo $player['score']; ?></td>
                        <td class="py-3 px-4 text-right text-sm text-gray-500"><?php echo date('d M Y, H:i', strtotime($player['played_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center py-6 text-gray-500">ยังไม่มีข้อมูลผู้เล่น</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>


<?php include 'footer.php'; ?>