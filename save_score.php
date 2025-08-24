<?php
session_start();
header('Content-Type: application/json');
require_once 'includes/db_connect.php';

$response = ['success' => false];

// ตรวจสอบว่ามีชื่อผู้เล่นและคะแนนอยู่ใน Session หรือไม่
if (isset($_SESSION['player_name']) && isset($_SESSION['current_score'])) {
    $player_name = $_SESSION['player_name'];
    $score = (int)$_SESSION['current_score'];

    $stmt = $conn->prepare("INSERT INTO player_scores (player_name, score) VALUES (?, ?)");
    $stmt->bind_param("si", $player_name, $score);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        // ล้างข้อมูลเกมใน session เพื่อเตรียมเล่นเกมใหม่
        unset($_SESSION['current_score']); 
        unset($_SESSION['player_name']);
    } else {
        $response['message'] = 'Database error.';
    }
    $stmt->close();
    
} else {
    $response['message'] = 'Invalid session data. No name or score to save.';
}

echo json_encode($response);
$conn->close();
?>