<?php

header('Content-Type: application/json; charset=utf-8');

include 'db.php';

// ค้นหาคิวที่กำลังเรียก
$sql = mysqli_query($con, "
    SELECT id
    FROM queue
    WHERE status='calling'
    LIMIT 1
");

if(mysqli_num_rows($sql) == 0){

    echo json_encode([
        "success" => false,
        "message" => "ไม่มีคิวที่กำลังเรียก"
    ]);

    exit;
}

$row = mysqli_fetch_assoc($sql);

// เปลี่ยนสถานะเป็น done
$update = mysqli_query($con, "
    UPDATE queue
    SET status='done'
    WHERE id=".$row['id']."
");

if($update){

    echo json_encode([
        "success" => true,
        "message" => "ปิดคิวเรียบร้อย"
    ]);

}else{

    echo json_encode([
        "success" => false,
        "message" => "เกิดข้อผิดพลาด"
    ]);

}