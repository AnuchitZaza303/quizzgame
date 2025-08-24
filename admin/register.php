<?php
session_start();
require_once '../includes/db_connect.php';

$recaptcha_secret_key = '6LdqMrArAAAAAOCSEoptr6BzVFhuy8h0LFoy_vlD'; // <--- !!! แก้ไขตรงนี้

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $recaptcha_response = $_POST['g-recaptcha-response'];

    if (empty($first_name) || empty($last_name) || empty($username) || empty($password)) {
        $error_message = 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน';
    } elseif (empty($email)) {
        $error_message = 'กรุณากรอกอีเมล';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'รูปแบบอีเมลไม่ถูกต้อง';
    } elseif ($password !== $confirm_password) {
        $error_message = 'รหัสผ่านและยืนยันรหัสผ่านไม่ตรงกัน';
    } elseif (strlen($password) < 6) {
        $error_message = 'รหัสผ่านต้องมีความยาวอย่างน้อย 6 ตัวอักษร';
    } elseif (empty($recaptcha_response)) {
        $error_message = 'กรุณายืนยันว่าคุณไม่ใช่โปรแกรมอัตโนมัติ';
    } else {
        $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
        $response = file_get_contents($verify_url . '?secret=' . $recaptcha_secret_key . '&response=' . $recaptcha_response);
        $response_data = json_decode($response);

        if (!$response_data->success) {
            $error_message = 'การยืนยัน reCAPTCHA ล้มเหลว กรุณาลองอีกครั้ง';
        } else {
            $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error_message = 'ชื่อผู้ใช้หรืออีเมลนี้มีผู้ใช้งานแล้ว';
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $insert_stmt = $conn->prepare("INSERT INTO admin_users (first_name, last_name, email, username, password_hash) VALUES (?, ?, ?, ?, ?)");
                $insert_stmt->bind_param("sssss", $first_name, $last_name, $email, $username, $password_hash);

                if ($insert_stmt->execute()) {
                    $success_message = 'สมัครสมาชิกสำเร็จ! คุณสามารถเข้าสู่ระบบได้แล้ว';
                } else {
                    $error_message = 'เกิดข้อผิดพลาดในการสมัครสมาชิก กรุณาลองอีกครั้ง';
                }
                $insert_stmt->close();
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>สมัครสมาชิก Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style> body { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="bg-gray-200 flex items-center justify-center min-h-screen py-12">
    <div class="w-full max-w-md">
        <form action="register.php" method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h1 class="text-2xl font-bold text-center text-gray-700 mb-6">สร้างบัญชีผู้ดูแลระบบ</h1>
            <?php if (!empty($error_message)): ?>
                <p class="bg-red-100 text-red-700 p-3 rounded text-center mb-4"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <p class="bg-green-100 text-green-700 p-3 rounded text-center mb-4"><?php echo $success_message; ?></p>
            <?php endif; ?>
            <div class="flex flex-wrap -mx-3 mb-4">
                <div class="w-full md:w-1/2 px-3 mb-4 md:mb-0">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="first_name">ชื่อจริง</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="first_name" name="first_name" type="text" required>
                </div>
                <div class="w-full md:w-1/2 px-3">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="last_name">นามสกุล</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="last_name" name="last_name" type="text" required>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">ชื่อผู้ใช้ (Username)</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="username" name="username" type="text" required>
            </div>
             <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">อีเมล</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="email" name="email" type="email" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">รหัสผ่าน</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="password" name="password" type="password" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">ยืนยันรหัสผ่าน</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700" id="confirm_password" name="confirm_password" type="password" required>
            </div>
            <div class="mb-6 flex justify-center">
                <div class="g-recaptcha" data-sitekey="6LdqMrArAAAAAM29eZvf5vH_rZDO8RIEdsuwMv6Q"></div> </div>
            <div class="flex items-center justify-center">
                <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    สมัครสมาชิก
                </button>
            </div>
            <p class="text-center text-gray-500 text-xs mt-6">
                มีบัญชีอยู่แล้ว? <a class="font-bold text-blue-500 hover:text-blue-800" href="login.php">เข้าสู่ระบบที่นี่</a>
            </p>
        </form>
    </div>
</body>
</html>