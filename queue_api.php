<?php
// queue_api.php
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php'; 

$response = [
    'success' => false,
    'current' => null,
    'next' => [],
    'waiting' => 0,
    'done' => 0
];

// รับค่า room_no และแปลงเป็น String พร้อมตัดช่องว่าง
$room_no = isset($_GET['room_no']) ? trim((string)$_GET['room_no']) : null;

try {
    /* ===================================================
       1. ดึงคิวที่กำลังเรียกล่าสุด (Current Calling Queue)
    =================================================== */
    if ($room_no !== null && $room_no !== '') {
        // สำหรับหน้าจอเจ้าหน้าที่: ดึงคิวล่าสุดที่กำลังเรียกเฉพาะห้องนี้
        $stmt_current = $con->prepare("SELECT queue_no, patient_name, room_no, call_id FROM queue WHERE status = 'calling' AND CAST(room_no AS CHAR) = ? ORDER BY id DESC LIMIT 1");
        $stmt_current->bind_param("s", $room_no);
    } else {
        // สำหรับหน้าจอรวม: ดึงคิวล่าสุดภาพรวมของทุกห้องตรวจ
        $stmt_current = $con->prepare("SELECT queue_no, patient_name, room_no, call_id FROM queue WHERE status = 'calling' ORDER BY id DESC LIMIT 1");
    }
    
    $stmt_current->execute();
    $result_current = $stmt_current->get_result();
    $current_queue = $result_current->fetch_assoc();
    
    if ($current_queue) {
        $response['current'] = [
            'queue_no' => (string)$current_queue['queue_no'],
            'patient_name' => (string)$current_queue['patient_name'],
            'room_no' => (string)$current_queue['room_no'],
            'call_id' => $current_queue['call_id'] 
        ];
    }
    $stmt_current->close();

    /* ===================================================
       2. ดึงคิวถัดไปที่ยังรออยู่ (Next Queue)
    =================================================== */
    $stmt_next = $con->prepare("SELECT queue_no, patient_name FROM queue WHERE status = 'waiting' ORDER BY id ASC LIMIT 4");
    $stmt_next->execute();
    $result_next = $stmt_next->get_result();
    
    while ($row = $result_next->fetch_assoc()) {
        $response['next'][] = $row;
    }
    $stmt_next->close();

    /* ===================================================
       3. สถิติจำนวนคิวรอทั้งหมด (Waiting Count)
    =================================================== */
    $stmt_wait = $con->prepare("SELECT COUNT(*) AS total FROM queue WHERE status = 'waiting'");
    $stmt_wait->execute();
    $result_wait = $stmt_wait->get_result();
    $row_wait = $result_wait->fetch_assoc();
    $response['waiting'] = (int)$row_wait['total'];
    $stmt_wait->close();

    /* ===================================================
       4. สถิติจำนวนคิวที่เสร็จแล้วเฉพาะวันนี้ (Done Count)
    =================================================== */
    $stmt_done = $con->prepare("SELECT COUNT(*) AS total FROM queue WHERE status = 'done' AND DATE(created_at) = CURDATE()");
    $stmt_done->execute();
    $result_done = $stmt_done->get_result();
    $row_done = $result_done->fetch_assoc();
    $response['done'] = (int)$row_done['total'];
    $stmt_done->close();

    $response['success'] = true;

} catch (Exception $e) {
    $response['success'] = false;
    $response['error'] = $e->getMessage();
}

echo json_encode($response);