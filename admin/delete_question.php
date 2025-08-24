<?php
require_once '../includes/db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM questions WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}
$conn->close();

header('Location: manage_questions.php');
exit();
?>