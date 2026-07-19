<?php
// operator.php
require_once 'db.php';

// ส่วนของ API Action สำหรับจัดการปุ่มกดผ่าน Ajax (ปรับปรุงเป็น mysqli)
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    
    $action = $_GET['action'];
    $room_no = isset($_GET['room_no']) ? $_GET['room_no'] : '1';

    if ($action === 'call_next') {
        try {
            // 1. หาคิวที่สถานะ waiting คิวแรกสุด
            $stmt = $con->prepare("SELECT id, queue_no FROM queue WHERE status = 'waiting' ORDER BY id ASC LIMIT 1");
            $stmt->execute();
            $next_queue = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($next_queue) {
                // เคลียร์คิวอื่นๆ ที่ห้องนี้กำลังเรียกอยู่ก่อนหน้าให้เป็น done
                $stmt_clear = $con->prepare("UPDATE queue SET status = 'done' WHERE room_no = ? AND status = 'calling'");
                $stmt_clear->bind_param("s", $room_no);
                $stmt_clear->execute();
                $stmt_clear->close();

                // 2. อัปเดตคิวใหม่ให้เป็นสถานะ calling และเจน call_id ใหม่
                $new_call_id = time() . '_' . rand(100, 999);
                $update_stmt = $con->prepare("UPDATE queue SET status = 'calling', room_no = ?, call_id = ? WHERE id = ?");
                $update_stmt->bind_param("ssi", $room_no, $new_call_id, $next_queue['id']);
                $update_stmt->execute();
                $update_stmt->close();

                echo json_encode(['success' => true, 'message' => 'เรียกคิวใหม่สำเร็จ', 'queue_no' => $next_queue['queue_no']]);
            } else {
                echo json_encode(['success' => false, 'message' => 'ไม่มีคิวรออยู่ในระบบ']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'SQL พัง: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'recall') {
        try {
            // ดึงคิวปัจจุบันของห้องตรวจนี้ที่กำลังเรียกอยู่
            $stmt = $con->prepare("SELECT id FROM queue WHERE room_no = ? AND status = 'calling' ORDER BY id DESC LIMIT 1");
            $stmt->bind_param("s", $room_no);
            $stmt->execute();
            $current_queue = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($current_queue) {
                // เจน call_id ชุดใหม่เพื่อให้ฝั่งคนไข้รู้ว่าต้องเล่นเสียงซ้ำ
                $new_call_id = time() . '_' . rand(100, 999);
                $update_stmt = $con->prepare("UPDATE queue SET call_id = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_call_id, $current_queue['id']);
                $update_stmt->execute();
                $update_stmt->close();

                echo json_encode(['success' => true, 'message' => 'เรียกซ้ำสำเร็จ']);
            } else {
                echo json_encode(['success' => false, 'message' => 'ไม่มีคิวที่กำลังเรียกอยู่ในห้องนี้']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'SQL พัง: ' . $e->getMessage()]);
        }
        exit;
    }

    if ($action === 'finish') {
        try {
            // ปิดคิวปัจจุบันของห้องตรวจนี้ให้เป็น done
            $update_stmt = $con->prepare("UPDATE queue SET status = 'done' WHERE room_no = ? AND status = 'calling'");
            $update_stmt->bind_param("s", $room_no);
            $update_stmt->execute();
            $update_stmt->close();

            echo json_encode(['success' => true, 'message' => 'เสร็จสิ้นคิวปัจจุบัน']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'SQL พัง: ' . $e->getMessage()]);
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Operator Console - ระบบเรียกคิวสำหรับเจ้าหน้าที่</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Kanit', sans-serif; }
        body { background-color: #f4f7f6; min-height: 100vh; }
        .operator-panel { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,.05); padding: 40px; margin-top: 30px; }
        .current-box { background: #eef5ff; border-radius: 15px; padding: 30px; margin-bottom: 25px; border-left: 5px solid #0d6efd; }
        .btn-action { padding: 15px 30px; font-size: 20px; font-weight: 500; border-radius: 12px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1"><i class="bi bi-person-workspace"></i> หน้าจอควบคุมสำหรับเจ้าหน้าที่</span>
        <div class="d-flex align-items-center">
            <span class="text-white me-2">ห้องตรวจหมายเลข:</span>
            <select id="select_room" class="form-select">
                <option value="1">ห้องตรวจ 1</option>
                <option value="2">ห้องตรวจ 2</option>
            </select>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="operator-panel">
                
                <div class="current-box text-center">
                    <div class="text-muted fs-4">คิวที่ห้องตรวจนี้กำลังเรียก</div>
                    <h1 id="op_queue_no">--</h1>
                    <p id="op_patient_name">ไม่มีคิวค้างอยู่ในห้องตรวจ</p>
                </div>

                <div class="d-grid gap-3">
                    <button class="btn btn-success btn-action text-white" onclick="handleAction('call_next')">
                        <i class="bi bi-skip-forward-fill"></i> เรียกคิวถัดไป (Next)
                    </button>
                    
                    <div class="row g-3">
                        <div class="col-6">
                            <button class="btn btn-warning btn-action text-white w-100" onclick="handleAction('recall')">
                                <i class="bi bi-megaphone-fill"></i> เรียกซ้ำ (Recall)
                            </button>
                        </div>
                        <div class="col-6">
                            <button class="btn btn-danger btn-action w-100" onclick="handleAction('finish')">
                                <i class="bi bi-check-circle-fill"></i> เสร็จสิ้นคิว (Finish)
                            </button>
                        </div>
                    </div>
                </div>

                <div id="alert_msg" class="alert mt-4 d-none" role="alert"></div>

            </div>
        </div>
    </div>
</div>

<script>
async function handleAction(action) {
    const roomNo = document.getElementById('select_room').value;
    const alertBox = document.getElementById('alert_msg');
    
    try {
        const response = await fetch(`operator.php?action=${action}&room_no=${roomNo}`);
        const data = await response.json();
        
        alertBox.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
        if (data.success) {
            alertBox.classList.add('alert-success');
            alertBox.innerText = data.message;
        } else {
            alertBox.classList.add('alert-warning');
            alertBox.innerText = data.message;
        }
        
        // สั่งอัปเดตหน้าจอทันทีหลังจากกดปุ่มสำเร็จ
        fetchCurrentRoomQueue();
        
        setTimeout(() => alertBox.classList.add('d-none'), 3000);

    } catch (error) {
        console.error("Error operational action:", error);
    }
}

async function fetchCurrentRoomQueue() {
    const roomSelect = document.getElementById('select_room');
    if (!roomSelect) return;
    
    const roomNo = roomSelect.value;
    
    try {
        const response = await fetch(`queue_api.php?room_no=${roomNo}&t=${new Date().getTime()}`);
        const data = await response.json();
        
        console.log(`[Room ${roomNo}] ข้อมูลจาก API:`, data);
        
        // แสดงผลลัพธ์ตรงๆ โดยอิงจากสิ่งที่ API คัดกรองมาให้แล้ว
        if (data.success && data.current) {
            document.getElementById("op_queue_no").innerText = data.current.queue_no;
            document.getElementById("op_patient_name").innerHTML = `<i class="bi bi-person-circle"></i> ${data.current.patient_name}`;
        } else {
            document.getElementById("op_queue_no").innerText = "--";
            document.getElementById("op_patient_name").innerText = "ไม่มีคิวค้างอยู่ในห้องตรวจ";
        }
        
    } catch (error) {
        console.error("เกิดข้อผิดพลาดในการดึงข้อมูลคิวห้องตรวจ:", error);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const roomSelect = document.getElementById('select_room');
    if (roomSelect) {
        roomSelect.addEventListener('change', fetchCurrentRoomQueue);
    }
    
    fetchCurrentRoomQueue();
    setInterval(fetchCurrentRoomQueue, 2000);
});
</script>
</body>
</html>