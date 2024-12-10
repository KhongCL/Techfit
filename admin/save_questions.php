<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Function to generate the next ID with a given prefix
function generateNextId($conn, $table, $column, $prefix) {
    $sql = "SELECT MAX(CAST(SUBSTRING($column, LENGTH('$prefix') + 1) AS UNSIGNED)) AS max_id FROM $table WHERE $column LIKE '$prefix%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ? $row['max_id'] : 0;
    $next_id = $prefix . str_pad($max_id + 1, 2, '0', STR_PAD_LEFT);
    return $next_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assessment_id = $_POST['assessment_id'];
    $question_texts = $_POST['question_text'];
    $answer_types = $_POST['answer_type'];

    foreach ($question_texts as $index => $question_text) {
        $question_id = generateNextId($conn, 'Question', 'question_id', 'Q');
        $answer_type = $answer_types[$index];

        // Insert the question into the database
        $sql = "INSERT INTO Question (question_id, assessment_id, question_text, answer_type)
                VALUES ('$question_id', '$assessment_id', '$question_text', '$answer_type')";

        if ($conn->query($sql) !== TRUE) {
            $_SESSION['error_message'] = "Error: " . $conn->error;
            header("Location: create_questions.php?assessment_id=$assessment_id");
            exit();
        }
    }

    $_SESSION['success_message'] = "Questions added successfully.";
    header("Location: manage_assessments.php");
    exit();
}

$conn->close();
?>