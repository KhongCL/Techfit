<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assessment_id = $_GET['assessment_id'];

$sql = "SELECT question_id, question_text, question_type, answer_type, correct_answer FROM Question WHERE assessment_id = ? AND is_active = 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

$questions = array();
while ($row = $result->fetch_assoc()) {
    $question_id = $row['question_id'];

    // Fetch choices for the question
    $choices_sql = "SELECT choice_id, choice_text FROM Choices WHERE question_id = ?";
    $choices_stmt = $conn->prepare($choices_sql);
    $choices_stmt->bind_param("s", $question_id);
    $choices_stmt->execute();
    $choices_result = $choices_stmt->get_result();

    $choices = array();
    while ($choice_row = $choices_result->fetch_assoc()) {
        $choices[] = $choice_row;
    }

    $row['choices'] = $choices;

    // Fetch programming language for code questions
    if ($row['answer_type'] === 'code') {
        $language_sql = "SELECT programming_language FROM Test_Cases WHERE question_id = ? LIMIT 1";
        $language_stmt = $conn->prepare($language_sql);
        $language_stmt->bind_param("s", $question_id);
        $language_stmt->execute();
        $language_result = $language_stmt->get_result();
        if ($language_row = $language_result->fetch_assoc()) {
            $row['programming_language'] = $language_row['programming_language'];
        }
        $language_stmt->close();
    }

    $questions[] = $row;

    $choices_stmt->close();
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($questions);
?>