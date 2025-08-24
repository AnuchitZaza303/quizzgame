<?php
require_once '../includes/db_connect.php';

$question = [
    'id' => '',
    'question_text' => '',
    'question_image_url' => '',
    'time_limit_seconds' => 30,
    'points' => 1 // เพิ่มค่าเริ่มต้น
];
$answers = [];
$edit_mode = false;
$page_title = "เพิ่มคำถามใหม่";

if (isset($_GET['id'])) {
    $edit_mode = true;
    $page_title = "แก้ไขคำถาม";
    $question_id = (int)$_GET['id'];
    
    $stmt_q = $conn->prepare("SELECT * FROM questions WHERE id = ?");
    $stmt_q->bind_param("i", $question_id);
    $stmt_q->execute();
    $result_q = $stmt_q->get_result();
    if ($result_q->num_rows > 0) {
        $question = $result_q->fetch_assoc();
    } else {
        header('Location: manage_questions.php');
        exit();
    }
    
    $stmt_a = $conn->prepare("SELECT * FROM answers WHERE question_id = ? ORDER BY id ASC");
    $stmt_a->bind_param("i", $question_id);
    $stmt_a->execute();
    $result_a = $stmt_a->get_result();
    while ($row = $result_a->fetch_assoc()) {
        $answers[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $question_text = $_POST['question_text'];
    $question_image_url = $_POST['question_image_url'];
    $time_limit = (int)$_POST['time_limit_seconds'];
    $points = (int)$_POST['points']; // <-- รับค่า points จากฟอร์ม
    $posted_answers = $_POST['answers'];
    $correct_answer_index = (int)$_POST['correct_answer_index'];

    $conn->begin_transaction();
    try {
        if ($edit_mode) {
            // --- UPDATE EXISTING QUESTION ---
            $question_id = (int)$_POST['question_id'];
            
            // 1. Update the question (เพิ่ม points เข้าไป)
            $stmt_update_q = $conn->prepare("UPDATE questions SET question_text = ?, question_image_url = ?, time_limit_seconds = ?, points = ? WHERE id = ?");
            $stmt_update_q->bind_param("ssiii", $question_text, $question_image_url, $time_limit, $points, $question_id); // <-- แก้ไข bind_param
            $stmt_update_q->execute();

            $stmt_delete_a = $conn->prepare("DELETE FROM answers WHERE question_id = ?");
            $stmt_delete_a->bind_param("i", $question_id);
            $stmt_delete_a->execute();

        } else {
            // --- INSERT NEW QUESTION --- (เพิ่ม points เข้าไป)
            $stmt_insert_q = $conn->prepare("INSERT INTO questions (question_text, question_image_url, time_limit_seconds, points) VALUES (?, ?, ?, ?)");
            $stmt_insert_q->bind_param("ssii", $question_text, $question_image_url, $time_limit, $points); // <-- แก้ไข bind_param
            $stmt_insert_q->execute();
            $question_id = $conn->insert_id;
        }

        $stmt_insert_a = $conn->prepare("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)");
        foreach ($posted_answers as $index => $answer_text) {
            if (!empty($answer_text)) {
                $is_correct = ($index === $correct_answer_index) ? 1 : 0;
                $stmt_insert_a->bind_param("isi", $question_id, $answer_text, $is_correct);
                $stmt_insert_a->execute();
            }
        }

        $conn->commit();
        header('Location: manage_questions.php');
        exit();

    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        die("Error: " . $exception->getMessage());
    }
}


include 'header.php';
?>

<h1 class="text-4xl font-bold text-gray-800 mb-6"><?php echo $page_title; ?></h1>

<div class="bg-white p-8 rounded-lg shadow-md">
    <form action="edit_question.php<?php echo $edit_mode ? '?id='.$question['id'] : ''; ?>" method="POST">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="question_id" value="<?php echo $question['id']; ?>">
        <?php endif; ?>

        <fieldset class="border p-4 rounded-md mb-6">
            <legend class="text-lg font-semibold px-2">รายละเอียดคำถาม</legend>
            <div class="mb-4">
                <label for="question_text" class="block text-gray-700 text-sm font-bold mb-2">ข้อความคำถาม:</label>
                <textarea id="question_text" name="question_text" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($question['question_text']); ?></textarea>
            </div>
            <div class="mb-4">
                <label for="question_image_url" class="block text-gray-700 text-sm font-bold mb-2">URL รูปภาพ (ถ้ามี):</label>
                <input type="text" id="question_image_url" name="question_image_url" value="<?php echo htmlspecialchars($question['question_image_url']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" placeholder="https://example.com/image.jpg">
            </div>
            <div>
                <label for="time_limit_seconds" class="block text-gray-700 text-sm font-bold mb-2">เวลาที่กำหนด (วินาที):</label>
                <input type="number" id="time_limit_seconds" name="time_limit_seconds" value="<?php echo $question['time_limit_seconds']; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="w-full md:w-1/2 px-3">
                <label for="points" class="block text-gray-700 text-sm font-bold mb-2">คะแนนสำหรับข้อนี้:</label>
                <input type="number" id="points" name="points" value="<?php echo $question['points'] ?? 10; ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            </div>
        </fieldset>

        <fieldset class="border p-4 rounded-md">
            <legend class="text-lg font-semibold px-2">ตัวเลือกและคำตอบ</legend>
            <div id="answers-container" class="space-y-4">
                <?php if (empty($answers)): ?>
                    <div class="answer-field flex items-center space-x-2">
                        <input type="radio" name="correct_answer_index" value="0" class="h-5 w-5" checked>
                        <input type="text" name="answers[]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="กรอกตัวเลือกที่ 1" required>
                        <button type="button" class="remove-answer-btn bg-red-500 text-white p-2 rounded hidden"><i class="fas fa-trash"></i></button>
                    </div>
                     <div class="answer-field flex items-center space-x-2">
                        <input type="radio" name="correct_answer_index" value="1" class="h-5 w-5">
                        <input type="text" name="answers[]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="กรอกตัวเลือกที่ 2" required>
                        <button type="button" class="remove-answer-btn bg-red-500 text-white p-2 rounded"><i class="fas fa-trash"></i></button>
                    </div>
                <?php else: ?>
                    <?php foreach ($answers as $index => $answer): ?>
                    <div class="answer-field flex items-center space-x-2">
                        <input type="radio" name="correct_answer_index" value="<?php echo $index; ?>" class="h-5 w-5" <?php echo $answer['is_correct'] ? 'checked' : ''; ?>>
                        <input type="text" name="answers[]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" value="<?php echo htmlspecialchars($answer['answer_text']); ?>" required>
                        <button type="button" class="remove-answer-btn bg-red-500 text-white p-2 rounded <?php echo count($answers) <= 2 ? 'hidden' : ''; ?>"><i class="fas fa-trash"></i></button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" id="add-answer-btn" class="mt-4 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-2"></i> เพิ่มตัวเลือก
            </button>
        </fieldset>
        
        <div class="flex items-center justify-end mt-6">
            <a href="manage_questions.php" class="text-gray-600 mr-4">ยกเลิก</a>
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                <i class="fas fa-save mr-2"></i> บันทึกข้อมูล
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const answersContainer = document.getElementById('answers-container');
    const addAnswerBtn = document.getElementById('add-answer-btn');
    let answerIndex = answersContainer.getElementsByClassName('answer-field').length;

    function updateRemoveButtons() {
        const removeButtons = answersContainer.getElementsByClassName('remove-answer-btn');
        if (removeButtons.length <= 2) {
            Array.from(removeButtons).forEach(btn => btn.classList.add('hidden'));
        } else {
            Array.from(removeButtons).forEach(btn => btn.classList.remove('hidden'));
        }
    }

    addAnswerBtn.addEventListener('click', function () {
        const newField = document.createElement('div');
        newField.className = 'answer-field flex items-center space-x-2';
        newField.innerHTML = `
            <input type="radio" name="correct_answer_index" value="${answerIndex}" class="h-5 w-5">
            <input type="text" name="answers[]" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" placeholder="กรอกตัวเลือกที่ ${answerIndex + 1}" required>
            <button type="button" class="remove-answer-btn bg-red-500 text-white p-2 rounded"><i class="fas fa-trash"></i></button>
        `;
        answersContainer.appendChild(newField);
        answerIndex++;
        updateRemoveButtons();
    });

    answersContainer.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.remove-answer-btn');
        if (removeBtn) {
            if (answersContainer.getElementsByClassName('answer-field').length > 2) {
                removeBtn.closest('.answer-field').remove();
                
                const radioButtons = answersContainer.querySelectorAll('input[type="radio"]');
                radioButtons.forEach((radio, index) => {
                    radio.value = index;
                });
                answerIndex--;
                updateRemoveButtons();
            }
        }
    });
    
    updateRemoveButtons();
});
</script>

<?php include 'footer.php'; ?>