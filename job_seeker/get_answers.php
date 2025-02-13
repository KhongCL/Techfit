<?php
// Get saved answers for the assessment
session_start();
header('Content-Type: text/plain');

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Connection to techfit database failed: ' . $conn->connect_error);
}

$assessment_id = $_GET['assessment_id'];
$job_seeker_id = $_GET['job_seeker_id'];

$sql = "SELECT question_id, answer_text FROM Answer 
        WHERE job_seeker_id = ? AND question_id IN 
        (SELECT question_id FROM Question WHERE assessment_id = ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $job_seeker_id, $assessment_id);
$stmt->execute();
$result = $stmt->get_result();


$answers = [];
while ($row = $result->fetch_assoc()) {
    $answers[] = $row['question_id'] . '<<QA_BREAK>>' . $row['answer_text'];
}

echo implode('<<ANSWER_SET>>', $answers);
?>