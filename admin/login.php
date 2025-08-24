<?php
session_start();
require_once '../includes/db_connect.php';

$error_message = '';

if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($password)) {
        $stmt = $conn->prepare("SELECT id, username, password_hash FROM admin_users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password_hash'])) {
                session_regenerate_id();
                $_SESSION['admin_loggedin'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                
                header('Location: index.php');
                exit;
            } else {
                $error_message = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
            }
        } else {
            $error_message = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
        }
        $stmt->close();
    } else {
        $error_message = 'กรุณากรอกข้อมูลให้ครบถ้วน';
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;700&display=swap" rel="stylesheet">
    <style> body { font-family: 'Sarabun', sans-serif; } </style>
</head>
<body class="bg-gray-200 flex items-center justify-center h-screen">
    <div class="w-full max-w-xs">
        <form action="login.php" method="post" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h1 class="text-2xl font-bold text-center text-gray-700 mb-6">Admin Login</h1>
            <?php if (!empty($error_message)): ?>
                <p class="bg-red-100 text-red-700 p-3 rounded text-center mb-4"><?php echo $error_message; ?></p>
            <?php endif; ?>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="username">
                    ชื่อผู้ใช้
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="username" name="username" type="text" placeholder="Username" required>
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    รหัสผ่าน
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="******************" required>
            </div>
            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                    เข้าสู่ระบบ
                </button>
            </div>
             <p class="text-center text-gray-500 text-xs mt-6">
                ยังไม่มีบัญชี? <a class="font-bold text-green-500 hover:text-green-800" href="register.php">
                    สร้างบัญชีใหม่
                </a>
            </p>
        </form>
    </div>
</body>
</html>