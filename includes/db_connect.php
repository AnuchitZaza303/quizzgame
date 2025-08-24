<?php
// /includes/db_connect.php

$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = "";     // Default password for XAMPP
$dbname = "quiz_game_db"; // <-- ตรวจสอบว่าชื่อนี้ตรงกับใน phpMyAdmin

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set character set to utf8mb4
$conn->set_charset("utf8mb4");

// Check connection
if ($conn->connect_error) {
  // หากเชื่อมต่อไม่ได้ จะหยุดการทำงานและแสดงข้อความนี้
  die("Connection failed: " . $conn->connect_error); 
}
?>