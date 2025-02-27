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
    
    $sql = "SELECT MAX(CAST(SUBSTRING(answer_id, 4) AS UNSIGNED)) AS max_id 
            FROM Answer 
            WHERE answer_id LIKE 'ANS%'";
    
    error_log("Executing query: " . $sql);
    
    $result = $conn->query($sql);
    if (!$result) {
        error_log("Query failed: " . $conn->error);
        return false;
    }
    
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ?? 0;
    error_log("Current max_id: " . $max_id);
    
    
    $next_id = $max_id + 1;
    error_log("Next ID number: " . $next_id);
    
    
    $answer_id = 'ANS' . $next_id;
    error_log("Generated answer_id: " . $answer_id);
    
    return $answer_id;
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
    error_log("Using generated answer_id: " . $answer_id);
    if (!$answer_id) {
        die("ERROR: Failed to generate answer ID");
    }
    
    
    error_log("Preparing to execute INSERT with answer_id: " . $answer_id);
    
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