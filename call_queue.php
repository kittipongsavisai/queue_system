<?php

include 'db.php';

mysqli_begin_transaction($con);

try {

    // ปิดคิวที่กำลังเรียกอยู่
    mysqli_query($con,"
        UPDATE queue
        SET status='done'
        WHERE status='calling'
    ");

    // ดึงคิวถัดไป
    $sql = mysqli_query($con,"
        SELECT id
        FROM queue
        WHERE status='waiting'
        ORDER BY id ASC
        LIMIT 1
    ");

    if(mysqli_num_rows($sql)>0){

        $row=mysqli_fetch_assoc($sql);

        mysqli_query($con,"
            UPDATE queue
            SET status='calling'
            WHERE id=".$row['id']."
        ");

        mysqli_commit($con);

        echo json_encode([
            "success"=>true,
            "message"=>"เรียกคิวถัดไปสำเร็จ"
        ]);

    }else{

        mysqli_commit($con);

        echo json_encode([
            "success"=>false,
            "message"=>"ไม่มีคิวรอ"
        ]);

    }

}catch(Exception $e){

    mysqli_rollback($con);

    echo json_encode([
        "success"=>false,
        "message"=>$e->getMessage()
    ]);

}