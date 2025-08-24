<?php
require_once '../includes/db_connect.php';

// ดึงคำถามทั้งหมด (เพิ่ม points)
$questions = [];
$result = $conn->query("SELECT id, question_text, time_limit_seconds, points FROM questions ORDER BY id DESC");
while($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
$conn->close();

include 'header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-4xl font-bold text-gray-800">จัดการคำถาม</h1>
    <a href="edit_question.php" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
        <i class="fas fa-plus mr-2"></i> เพิ่มคำถามใหม่
    </a>
</div>

<div class="bg-white p-8 rounded-lg shadow-md">
    <table class="w-full table-auto">
        <thead class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
            <tr>
                <th class="py-3 px-6 text-left">ID</th>
                <th class="py-3 px-6 text-left">คำถาม</th>
                <th class="py-3 px-6 text-center">เวลา (วินาที)</th>
                <th class="py-3 px-6 text-center">คะแนน</th>
                <th class="py-3 px-6 text-center">จัดการ</th>
            </tr>
        </thead>
        <tbody class="text-gray-600 text-sm font-light">
            <?php foreach ($questions as $q): ?>
            <tr class="border-b border-gray-200 hover:bg-gray-100">
                <td class="py-3 px-6 text-left whitespace-nowrap"><?php echo $q['id']; ?></td>
                <td class="py-3 px-6 text-left"><?php echo htmlspecialchars(mb_substr($q['question_text'], 0, 80)) . (mb_strlen($q['question_text']) > 80 ? '...' : ''); ?></td>
                <td class="py-3 px-6 text-center"><?php echo $q['time_limit_seconds']; ?></td>
                <td class="py-3 px-6 text-center font-bold"><?php echo $q['points']; ?></td>
                <td class="py-3 px-6 text-center">
                    <div class="flex item-center justify-center">
                        <a href="edit_question.php?id=<?php echo $q['id']; ?>" class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-200 text-blue-600 mr-2 transform hover:scale-110" title="แก้ไข">
                            <i class="fas fa-pencil-alt"></i>
                        </a>
                        <a href="#" onclick="deleteQuestion(<?php echo $q['id']; ?>)" class="w-8 h-8 flex items-center justify-center rounded-full bg-red-200 text-red-600 transform hover:scale-110" title="ลบ">
                            <i class="fas fa-trash-alt"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
             <?php if (empty($questions)): ?>
                <tr>
                    <td colspan="5" class="text-center py-4">ยังไม่มีคำถามในระบบ</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
function deleteQuestion(id) {
    Swal.fire({
        title: 'คุณแน่ใจหรือไม่?',
        text: "คุณจะไม่สามารถกู้คืนข้อมูลคำถามนี้ได้!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ใช่, ลบเลย!',
        cancelButtonText: 'ยกเลิก'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `delete_question.php?id=${id}`;
        }
    })
}
</script>

<?php include 'footer.php'; ?>