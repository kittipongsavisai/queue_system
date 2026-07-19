<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Hospital Queue Management</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<style>

*{
    font-family:'Kanit',sans-serif;
}

body{

    background:linear-gradient(135deg,#edf6ff,#f7fbff);

    min-height:100vh;

}

/* ================= HEADER ================= */

.topbar{

    background:#0d6efd;

    color:white;

    padding:20px;

    box-shadow:0 8px 25px rgba(0,0,0,.08);

}

.hospital{

    font-size:30px;

    font-weight:700;

}

.clock{

    font-size:22px;

    font-weight:400;

}

/* ================= MAIN ================= */

.main-card{

    background:white;

    border-radius:30px;

    padding:50px;

    box-shadow:0 15px 40px rgba(0,0,0,.08);

}

.queue-title{

    color:#6c757d;

    font-size:34px;

}

.queue-number{

    font-size:150px;

    font-weight:700;

    color:#0d6efd;

    line-height:1;

}

.patient{

    font-size:38px;

    margin-top:20px;

}

.room{

    font-size:30px;

    color:#6c757d;

}

/* ================= SIDE ================= */

.side-card{

    background:white;

    border-radius:25px;

    padding:25px;

    margin-bottom:25px;

    box-shadow:0 10px 30px rgba(0,0,0,.08);

}

.side-title{

    font-size:28px;

    font-weight:600;

    margin-bottom:20px;

}

.next{

    font-size:28px;

    padding:15px;

    border-bottom:1px solid #ececec;

}

.status{

    font-size:24px;

    padding:10px 0;

}

/* ================= FOOTER ================= */

.footer{

    text-align:center;

    color:#666;

    margin-top:35px;

    font-size:18px;

}

/* ================= MOBILE ================= */

@media(max-width:992px){

.queue-number{

    font-size:90px;

}

.patient{

    font-size:26px;

}

.room{

    font-size:22px;

}

.side-title{

    font-size:22px;

}

.next{

    font-size:22px;

}

}

</style>

</head>

<body>

<!-- HEADER -->

<div class="topbar">

<div class="container-fluid">

<div class="row align-items-center">

<div class="col-md-8">

<div class="hospital">

<i class="bi bi-hospital"></i>

ระบบเรียกคิวโรงพยาบาล

</div>

</div>

<div class="col-md-4 text-end">

<div class="clock" id="clock"></div>

</div>

</div>

</div>

</div>

<!-- CONTENT -->

<div class="container py-5">

<div class="row g-4">

<!-- LEFT -->

<div class="col-lg-8">

<div class="main-card text-center">

<div class="queue-title">

กำลังเรียกคิว

</div>

<div class="queue-number" id="queue_no">

--

</div>

<div class="patient" id="patient_name">

<i class="bi bi-person-circle"></i>

ไม่มีคิว

</div>

<div class="room mt-3" id="room_no">

-

</div>

</div>

</div>

<!-- RIGHT -->

<div class="col-lg-4">

<div class="side-card">

<div class="side-title">

<i class="bi bi-list-ol"></i>

คิวถัดไป

</div>

<div id="next_queue">

<div class="next">

ไม่มีข้อมูล

</div>

</div>

</div>

<div class="side-card">

<div class="side-title">

<i class="bi bi-speedometer2"></i>

สถานะระบบ

</div>

<div class="status">

🟡 จำนวนคิวรอ :

<b id="waiting_count">0</b>

</div>

<div class="status">

✅ เรียกเสร็จ :

<b id="done_count">0</b>

</div>

<div class="status text-success">

🟢 Queue Online

</div>

<div class="status text-success">

🟢 Speaker Ready

</div>

</div>

</div>

</div>

<div class="footer">

โรงพยาบาลนาวังเฉลิมพระเกียรติ ๘๐ พรรษา

</div>

</div>

<!-- Bootstrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Speaker -->

<script src="assets/js/speaker.js"></script>

<!-- App -->

<script src="assets/js/app.js"></script>

<!-- Clock -->

<script>

function updateClock(){

    const now = new Date();

    document.getElementById("clock").innerHTML =
        now.toLocaleDateString('th-TH') +
        " " +
        now.toLocaleTimeString('th-TH');

}

updateClock();

setInterval(updateClock,1000);

</script>

</body>
</html>