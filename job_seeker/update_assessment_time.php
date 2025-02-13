<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$job_seeker_id = $_SESSION['job_seeker_id'];

// Only update end_time, score will be calculated in assessment_result.php
$sql = "UPDATE Assessment_Job_Seeker 
        SET end_time = NOW() 
        WHERE job_seeker_id = ? 
        AND end_time IS NULL
        ORDER BY start_time DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $job_seeker_id);
$result = $stmt->execute();

if ($result) {
    echo "SUCCESS";
} else {
    echo "ERROR: " . $conn->error;
}

$conn->close();