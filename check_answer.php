<?php
session_start();
header('Content-Type: application/json');
require_once 'includes/db_connect.php';

$question_id = isset($_GET['question_id']) ? (int)$_GET['question_id'] : 0;
$answer_id = isset($_GET['answer_id']) ? (int)$_GET['answer_id'] : 0;
$response_data = ['correct' => false];

if ($question_id > 0 && $answer_id > 0) {
    // 1. ตรวจสอบว่าคำตอบที่ส่งมาถูกต้องหรือไม่
    $stmt = $conn->prepare("SELECT is_correct FROM answers WHERE id = ? AND question_id = ?");
    $stmt->bind_param("ii", $answer_id, $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $answer = $result->fetch_assoc();

    if ($answer && $answer['is_correct'] == 1) {
        // 2. ถ้าตอบถูก ให้ไปดึงคะแนนของคำถามข้อนี้
        $stmt_points = $conn->prepare("SELECT points FROM questions WHERE id = ?");
        $stmt_points->bind_param("i", $question_id);
        $stmt_points->execute();
        $question_data = $stmt_points->get_result()->fetch_assoc();
        $points_to_add = $question_data ? (int)$question_data['points'] : 10;

        if (!isset($_SESSION['current_score'])) {
            $_SESSION['current_score'] = 0;
        }
        // 3. บวกคะแนนตามที่ดึงมาได้
        $_SESSION['current_score'] += $points_to_add; 
        $response_data['correct'] = true;
        $stmt_points->close();
    } else {
        // ถ้าตอบผิด ไม่ต้องทำอะไร (ไม่ต้องดึงเฉลย)
        $response_data['correct'] = false;
    }
    
    $stmt->close();
}

$response_data['current_score'] = $_SESSION['current_score'] ?? 0;
echo json_encode($response_data);
$conn->close();
?>