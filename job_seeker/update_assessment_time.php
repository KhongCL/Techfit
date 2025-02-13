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

$data = json_decode(file_get_contents('php://input'), true);
$job_seeker_id = $data['job_seeker_id'];

$sql = "UPDATE Assessment_Job_Seeker 
        SET end_time = NOW() 
        WHERE job_seeker_id = ? 
        ORDER BY start_time DESC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $job_seeker_id);
$stmt->execute();

echo json_encode(['success' => true]);