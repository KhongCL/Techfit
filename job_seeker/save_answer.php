<?php
session_start();
header('Content-Type: text/plain');

require_once 'check_code_answer.php';

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("ERROR: Connection failed");
}

$job_seeker_id = $_POST['job_seeker_id'];
$question_id = $_POST['question_id'];
$answer_text = $_POST['answer_text'];
$answer_type = $_POST['answer_type'] ?? '';


if (!$job_seeker_id || !$question_id || !isset($answer_text)) {
    die("ERROR: Missing required fields");
}


$check_sql = "SELECT question_id, answer_type FROM Question WHERE question_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("s", $question_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    die("ERROR: Invalid question ID");
}

$question = $result->fetch_assoc();


$is_correct = null;
if ($answer_type === 'code') {
    $answer_parts = explode('<<ANSWER_BREAK>>', $answer_text);
    $question_sql = "SELECT code_template FROM Question WHERE question_id = ?";
    $stmt = $conn->prepare($question_sql);
    $stmt->bind_param("s", $question_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();
    $is_correct = checkCodeAnswer($conn, $question_id, $answer_text);
    
    
    $blank_count = substr_count($question['code_template'], '__BLANK__');

    error_log("Answer parts count: " . count($answer_parts));
    error_log("Expected blanks: " . $blank_count);
    error_log("Answer text: " . $answer_text);
    error_log("Is correct: " . $is_correct);

    if (count($answer_parts) !== $blank_count) {
        die("ERROR: Invalid answer format for code question. Expected $blank_count parts but got " . count($answer_parts));
    }
}

function generateAnswerId($conn) {
    
    $check_sql = "SELECT COUNT(*) as count FROM Answer";
    $check_result = $conn->query($check_sql);
    if (!$check_result) {
        return false;
    }
    
    $row = $check_result->fetch_assoc();
    if ($row['count'] == 0) {
        return 'ANS01'; 
    }

    
    $sql = "SELECT MAX(CAST(SUBSTRING(answer_id, 4) AS UNSIGNED)) AS max_id 
            FROM Answer 
            WHERE answer_id LIKE 'ANS%'";
    
    $result = $conn->query($sql);
    if (!$result) {
        return false;
    }
    
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ? $row['max_id'] : 0;
    
    
    return 'ANS' . str_pad($max_id + 1, 2, '0', STR_PAD_LEFT);
}


$stmt = $conn->prepare("SELECT answer_id FROM Answer WHERE job_seeker_id = ? AND question_id = ?");
$stmt->bind_param("ss", $job_seeker_id, $question_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    
    $row = $result->fetch_assoc();
    $answer_id = $row['answer_id'];
    
    $stmt = $conn->prepare("UPDATE Answer SET answer_text = ?, is_correct = ? WHERE answer_id = ?");
    $stmt->bind_param("sis", $answer_text, $is_correct, $answer_id);
} else {
    
    $answer_id = generateAnswerId($conn);
    if (!$answer_id) {
        die("ERROR: Failed to generate answer ID");
    }
    
    $stmt = $conn->prepare("INSERT INTO Answer (answer_id, job_seeker_id, question_id, answer_text, is_correct) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $answer_id, $job_seeker_id, $question_id, $answer_text, $is_correct);
}

$success = $stmt->execute();

if ($success) {
    echo 'SUCCESS';
} else {
    echo 'ERROR: Failed to save answer';
}

$stmt->close();
$conn->close();
?>