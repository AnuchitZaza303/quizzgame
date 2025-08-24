<?php
require_once '../includes/db_connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        foreach ($_POST as $key => $value) {
            $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
            $stmt->bind_param('ss', $value, $key);
            $stmt->execute();
        }
        $conn->commit();
        $message = '<div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">บันทึกการเปลี่ยนแปลงเรียบร้อยแล้ว!</div>';
    } catch (mysqli_sql_exception $exception) {
        $conn->rollback();
        $message = '<div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">เกิดข้อผิดพลาดในการบันทึก: ' . $exception->getMessage() . '</div>';
    }
}

$settings = [];
$result = $conn->query("SELECT * FROM settings");
while($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
$conn->close();

include 'header.php'; 
?>

<h1 class="text-4xl font-bold text-gray-800 mb-6">ตั้งค่าข้อความหน้าเว็บไซต์</h1>

<?php echo $message; ?>

<div class="bg-white p-8 rounded-lg shadow-md">
    <form action="settings.php" method="POST">
        <div class="mb-6">
            <label for="welcome_title" class="block text-gray-700 text-sm font-bold mb-2">หัวข้อหลัก (Title):</label>
            <input type="text" id="welcome_title" name="welcome_title" value="<?php echo htmlspecialchars($settings['welcome_title']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-6">
            <label for="welcome_subtitle" class="block text-gray-700 text-sm font-bold mb-2">คำโปรย (Subtitle):</label>
            <input type="text" id="welcome_subtitle" name="welcome_subtitle" value="<?php echo htmlspecialchars($settings['welcome_subtitle']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-6">
            <label for="start_button_text" class="block text-gray-700 text-sm font-bold mb-2">ข้อความบนปุ่มเริ่มเกม:</label>
            <input type="text" id="start_button_text" name="start_button_text" value="<?php echo htmlspecialchars($settings['start_button_text']); ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="flex items-center justify-end">
            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded focus:outline-none focus:shadow-outline">
                <i class="fas fa-save mr-2"></i> บันทึกการเปลี่ยนแปลง
            </button>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>