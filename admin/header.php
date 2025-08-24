<?php
session_start();
if (!isset($_SESSION['admin_loggedin']) || $_SESSION['admin_loggedin'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการเกมตอบคำถาม</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Sarabun', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <div class="w-64 bg-gray-800 text-white p-5 flex flex-col">
            <h2 class="text-2xl font-bold mb-10 text-center">Admin Panel</h2>
            <nav class="flex flex-col space-y-2 flex-grow">
                <a href="index.php" class="flex items-center py-2 px-4 rounded hover:bg-gray-700">
                    <i class="fas fa-tachometer-alt mr-3 w-5"></i> แดชบอร์ด
                </a>
                <a href="manage_questions.php" class="flex items-center py-2 px-4 rounded hover:bg-gray-700">
                    <i class="fas fa-question-circle mr-3 w-5"></i> จัดการคำถาม
                </a>
                <a href="settings.php" class="flex items-center py-2 px-4 rounded hover:bg-gray-700">
                    <i class="fas fa-cog mr-3 w-5"></i> ตั้งค่าข้อความ
                </a>
                <a href="../index.php" target="_blank" class="flex items-center py-2 px-4 rounded hover:bg-gray-700">
                    <i class="fas fa-eye mr-3 w-5"></i> ดูหน้าเว็บ
                </a>
            </nav>
            <div class="mt-auto">
                 <a href="logout.php" class="flex items-center py-2 px-4 rounded bg-red-500 hover:bg-red-600">
                    <i class="fas fa-sign-out-alt mr-3 w-5"></i> ออกจากระบบ
                </a>
            </div>
        </div>
        <main class="flex-1 p-10">