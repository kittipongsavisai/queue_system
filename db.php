<?php
// db.php

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "queue_system";

// สร้างการเชื่อมต่อ
$con = new mysqli($host, $user, $pass, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($con->connect_error) {
    die("เชื่อมต่อฐานข้อมูลไม่สำเร็จ : " . $con->connect_error);
}

// ตั้งค่า charset เป็น utf8mb4
$con->set_charset("utf8mb4");
?>