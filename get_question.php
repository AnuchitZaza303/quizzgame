<?php
header('Content-Type: application/json');
require_once 'includes/db_connect.php';

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action === 'get_ids') {
    $result = $conn->query("SELECT id FROM questions ORDER BY RAND()");
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = $row['id'];
    }
    echo json_encode(['ids' => $ids]);
    exit;
}

$questionId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($questionId > 0) {
    // ดึงข้อมูลคำถาม
    $stmt_q = $conn->prepare("SELECT id, question_text, question_image_url, time_limit_seconds FROM questions WHERE id = ?");
    $stmt_q->bind_param("i", $questionId);
    $stmt_q->execute();
    $result_q = $stmt_q->get_result();

    if ($result_q->num_rows > 0) {
        $question = $result_q->fetch_assoc();

        // ดึงข้อมูลตัวเลือก
        $stmt_a = $conn->prepare("SELECT id, answer_text FROM answers WHERE question_id = ? ORDER BY RAND()");
        $stmt_a->bind_param("i", $questionId);
        $stmt_a->execute();
        $result_a = $stmt_a->get_result();
        $answers = $result_a->fetch_all(MYSQLI_ASSOC);
        
        echo json_encode(['question' => $question, 'answers' => $answers]);

    } else {
        echo json_encode(['error' => 'Question not found or end of game.']);
    }
    
    $stmt_q->close();
    $stmt_a->close();

} else {
    echo json_encode(['error' => 'Invalid question ID.']);
}

$conn->close();
?>