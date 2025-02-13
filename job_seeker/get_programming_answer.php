<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Connection to techfit database failed: ' . $conn->connect_error);
}

if (!isset($_GET['question_id']) || $_GET['question_id'] !== 'Q209') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid question ID']);
    exit();
}

$job_seeker_id = $_SESSION['job_seeker_id'];

$sql = "SELECT answer_text 
        FROM Answer 
        WHERE job_seeker_id = ? AND question_id = 'Q209'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $job_seeker_id);
$stmt->execute();
$result = $stmt->get_result();

$response = ['answer' => null];
if ($row = $result->fetch_assoc()) {
    $response['answer'] = $row['answer_text'];
} else {
    $response['answer'] = null;
}

header('Content-Type: application/json');
echo json_encode($response);
$conn->close();