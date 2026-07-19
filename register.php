<?php
// register.php
require_once 'db.php';

$message = "";
$alert_class = "";
$generated_queue = "";

// เมื่อมีการกดปุ่มลงทะเบียน (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_name = isset($_POST['patient_name']) ? trim($_POST['patient_name']) : '';

    if (!empty($patient_name)) {
        try {
            // 1. หาคิวล่าสุดของวันนี้เพื่อรันเลขต่อ (สมมุติต้นแบบคิวเป็นแบบตัวเลขรัน 1, 2, 3...)
            // พี่สามารถปรับ Logic การเจนคิว เช่น เติม A001, B001 ตามสไตล์ของโรงพยาบาลได้เลยครับ
            $stmt = $con->prepare("SELECT queue_no FROM queue ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($result) {
                // ถ้ามีคิวเดิมอยู่แล้ว ให้นำเลขคิวมาบวกเพิ่ม 1
                $last_num = (int)$result['queue_no'];
                $next_num = $last_num + 1;
            } else {
                // ถ้าเป็นคิวแรกของวัน ให้เริ่มที่ 1
                $next_num = 1;
            }

            // แปลงเป็น format สวยๆ เช่น 001, 002 (หรือจะใช้เป็นตัวเลขตรงๆ ก็ได้ครับ)
            $queue_no = str_pad($next_num, 3, "0", STR_PAD_LEFT); 

            // 2. Insert ข้อมูลคนไข้เข้าตาราง queue
            // กำหนดสถานะเริ่มต้นเป็น 'waiting' และยังไม่มีห้องตรวจ (room_no = null)
            $status = 'waiting';
            $insert_stmt = $con->prepare("INSERT INTO queue (queue_no, patient_name, status) VALUES (?, ?, ?)");
            $insert_stmt->bind_param("sss", $queue_no, $patient_name, $status);
            
            if ($insert_stmt->execute()) {
                $generated_queue = $queue_no;
                $message = "ลงทะเบียนสำเร็จ! คนไข้ได้รับคิวหมายเลข <b>{$queue_no}</b>";
                $alert_class = "alert-success";
            } else {
                $message = "เกิดข้อผิดพลาดในการบันทึกข้อมูลคิว";
                $alert_class = "alert-danger";
            }
            $insert_stmt->close();

        } catch (Exception $e) {
            $message = "ระบบฐานข้อมูลมีปัญหา: " . $e->getMessage();
            $alert_class = "alert-danger";
        }
    } else {
        $message = "กรุณากรอกชื่อ-นามสกุลของคนไข้";
        $alert_class = "alert-warning";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบลงทะเบียนรับคิวคนไข้</title>
    <!-- ใช้ Bootstrap 5 และ Font Kanit ให้หน้าตาธีมเดียวกับระบบของพี่ครับ -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Kanit', sans-serif; }
        body { background-color: #f0f4f8; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .register-card { background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); padding: 40px; width: 100%; max-width: 500px; }
        .queue-display { background: #e8f5e9; border: 2px dashed #2e7d32; color: #2e7d32; border-radius: 15px; padding: 20px; margin-top: 20px; text-align: center; }
        .queue-number { font-size: 4rem; font-weight: 700; line-height: 1; margin-top: 10px; }
    </style>
</head>
<body>

<div class="register-card">
    <div class="text-center mb-4">
        <div class="display-6 text-primary"><i class="bi bi-person-plus-fill"></i></div>
        <h3 class="fw-bold mt-2">ลงทะเบียนรับคิวคนไข้</h3>
        <p class="text-muted">กรอกข้อมูลเพื่อออกบัตรคิวเข้าสู่ระบบ</p>
    </div>

    <!-- ส่วนแสดงข้อความแจ้งเตือนสถานะ -->
    <?php if (!empty($message)): ?>
        <div class="alert <?php echo $alert_class; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-remov-alert data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- ฟอร์มรับข้อมูล -->
    <form action="register.php" method="POST" autocomplete="off">
        <div class="mb-4">
            <label for="patient_name" class="form-label fw-medium">ชื่อ - นามสกุล คนไข้</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person text-muted"></i></span>
                <input type="text" class="form-control form-control-lg" id="patient_name" name="patient_name" placeholder="ตัวอย่าง: นายสมชาย ใจดี" required autofocus>
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg fw-medium">
                <i class="bi bi-ticket-perforated-fill"></i> ออกบัตรคิว (Print Queue)
            </button>
        </div>
    </form>

    <!-- ส่วนตั๋วคิวจำลอง (จะแสดงขึ้นมาเมื่อกดลงทะเบียนสำเร็จ) -->
    <?php if (!empty($generated_queue)): ?>
        <div class="queue-display">
            <div class="fw-medium text-uppercase small tracking-wide">คิวของคุณคือ</div>
            <div class="queue-number"><?php echo $generated_queue; ?></div>
            <div class="mt-2 small text-muted"><i class="bi bi-clock"></i> เข้าสู่สถานะ: รอเรียก (Waiting)</div>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>