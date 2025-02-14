<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


function generateNextId($conn, $table, $column, $prefix) {
    $sql = "SELECT MAX(CAST(SUBSTRING($column, LENGTH('$prefix') + 1) AS UNSIGNED)) AS max_id FROM $table WHERE $column LIKE '$prefix%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ? $row['max_id'] : 0;
    $next_id = $prefix . str_pad($max_id + 1, 2, '0', STR_PAD_LEFT);
    return $next_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    error_log("POST data: " . print_r($_POST, true));

    $assessment_id = $_POST['assessment_id'];
    $question_texts = $_POST['question_text'];
    $question_types = $_POST['question_type'];
    $answer_types = $_POST['answer_type'];
    $correct_answers = $_POST['correct_choice'];

    
    $assessment_check_sql = "SELECT assessment_id FROM Assessment_Admin WHERE assessment_id = '$assessment_id'";
    $assessment_check_result = $conn->query($assessment_check_sql);
    if ($assessment_check_result->num_rows == 0) {
        $_SESSION['error_message'] = "Invalid assessment ID.";
        header("Location: create_questions.php?assessment_id=$assessment_id");
        exit();
    }

    foreach ($question_texts as $index => $question_text) {
        if (empty($question_text) || empty($question_types[$index]) || empty($answer_types[$index]) || empty($correct_answers[$index])) {
            $_SESSION['error_message'] = "All fields are required.";
            header("Location: create_questions.php?assessment_id=$assessment_id");
            exit();
        }

        $question_id = generateNextId($conn, 'Question', 'question_id', 'Q');
        $question_type = $question_types[$index];
        $answer_type = $answer_types[$index];
        $correct_answer = $correct_answers[$index];

        if ($answer_type === 'code') {
            $code_template = $_POST['code_template'][$index];
            $programming_language = $_POST['code_language'][$index];

            
            $answers = explode('<<ANSWER_BREAK>>', $correct_answer);
            if (count($answers) < 2) {
                $_SESSION['error_message'] = "Please provide at least two answers separated by <<ANSWER_BREAK>>";
                header("Location: create_questions.php?assessment_id=$assessment_id");
                exit();
            }
            
            
            foreach ($answers as $answer) {
                if (trim($answer) === '') {
                    $_SESSION['error_message'] = "Empty or blank answers are not allowed. Please provide valid answers separated by <<ANSWER_BREAK>>";
                    header("Location: create_questions.php?assessment_id=$assessment_id");
                    exit();
                }
            }
    
            $stmt = $conn->prepare("INSERT INTO Question (question_id, assessment_id, question_text, question_type, answer_type, correct_answer, code_template, programming_language) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $question_id, $assessment_id, $question_text, $question_type, $answer_type, $correct_answer, $code_template, $programming_language);
        } else {
            $stmt = $conn->prepare("INSERT INTO Question (question_id, assessment_id, question_text, question_type, answer_type, correct_answer) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $question_id, $assessment_id, $question_text, $question_type, $answer_type, $correct_answer);
        }
    
        if (!$stmt->execute()) {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
            header("Location: create_questions.php?assessment_id=$assessment_id");
            exit();
        }

        
        if ($answer_type === 'multiple choice') {
            $choices_key = "choices_" . ($index + 1); 
            if (isset($_POST[$choices_key])) {
                $choices = $_POST[$choices_key];
                foreach ($choices as $choice_text) {
                    if (empty($choice_text)) {
                        $_SESSION['error_message'] = "All choice fields are required.";
                        header("Location: create_questions.php?assessment_id=$assessment_id");
                        exit();
                    }
                    $choice_id = generateNextId($conn, 'Choices', 'choice_id', 'C');
                    $stmt = $conn->prepare("INSERT INTO Choices (choice_id, question_id, choice_text) VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $choice_id, $question_id, $choice_text);
                    if ($stmt->execute() !== TRUE) {
                        $_SESSION['error_message'] = "Error: " . $stmt->error;
                        header("Location: create_questions.php?assessment_id=$assessment_id");
                        exit();
                    }
                }
            } else {
                error_log("Choices key $choices_key not found in POST data");
            }
        }
    }

    
    $update_last_modified_sql = "UPDATE Assessment_Admin SET last_modified = NOW() WHERE assessment_id = '$assessment_id'";
    if ($conn->query($update_last_modified_sql) !== TRUE) {
        $_SESSION['error_message'] = "Error updating last modified date: " . $conn->error;
        header("Location: create_questions.php?assessment_id=$assessment_id");
        exit();
    }

    header("Location: manage_assessments.php");
    exit();
}

$conn->close();
?>