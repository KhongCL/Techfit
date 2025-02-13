<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['job_seeker_id'])) {
    die(json_encode(['success' => false, 'error' => 'No job seeker ID in session']));
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

function generateResultId($conn) {
    // Get max ID number
    $sql = "SELECT MAX(CAST(SUBSTRING(result_id, 4) AS UNSIGNED)) as max_num 
            FROM Assessment_Job_Seeker 
            WHERE result_id LIKE 'ASJ%'";
    
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $nextNum = ($row['max_num'] ?? 0) + 1;
    
    return 'ASJ' . str_pad($nextNum, 2, '0', STR_PAD_LEFT);
}

$result_id = generateResultId($conn);

$start_assessment_sql = "INSERT INTO Assessment_Job_Seeker (result_id, job_seeker_id, start_time) 
                        VALUES (?, ?, NOW())";
$stmt = $conn->prepare($start_assessment_sql);
$stmt->bind_param("ss", $result_id, $_SESSION['job_seeker_id']);

if ($stmt->execute()) {
    $_SESSION['current_assessment_id'] = $result_id;
    echo json_encode(['success' => true, 'result_id' => $result_id]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}

$conn->close();
?>