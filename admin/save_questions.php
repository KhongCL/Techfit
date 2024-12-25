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
    // Log the entire $_POST array
    error_log("POST data: " . print_r($_POST, true));

    $assessment_id = $_POST['assessment_id'];
    $question_texts = $_POST['question_text'];
    $question_types = $_POST['question_type'];
    $answer_types = $_POST['answer_type'];
    $correct_answers = $_POST['correct_choice'];
    $programming_languages = $_POST['code_language']; // Added line

    // Check if the assessment_id exists in the Assessment_Admin table
    $assessment_check_sql = "SELECT assessment_id FROM Assessment_Admin WHERE assessment_id = '$assessment_id'";
    $assessment_check_result = $conn->query($assessment_check_sql);
    if ($assessment_check_result->num_rows == 0) {
        $_SESSION['error_message'] = "Invalid assessment ID.";
        header("Location: create_questions.php?assessment_id=$assessment_id");
        exit();
    }

    foreach ($question_texts as $index => $question_text) {
        $question_id = generateNextId($conn, 'Question', 'question_id', 'Q');
        $question_type = $question_types[$index];
        $answer_type = $answer_types[$index];
        $correct_answer = $correct_answers[$index];

        // Debugging: Log the answer type and index
        error_log("Answer Type for Question $question_id: $answer_type (Index: $index)");

        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO Question (question_id, assessment_id, question_text, question_type, answer_type, correct_answer) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $question_id, $assessment_id, $question_text, $question_type, $answer_type, $correct_answer);

        if ($stmt->execute() !== TRUE) {
            $_SESSION['error_message'] = "Error: " . $stmt->error;
            header("Location: create_questions.php?assessment_id=$assessment_id");
            exit();
        }

        // Insert choices for multiple choice questions
        if ($answer_type === 'multiple choice') {
            error_log("Inserting choices for Question $question_id");
            $choices_key = "choices_" . ($index + 1); // Adjust the key by adding 1
            if (isset($_POST[$choices_key])) {
                $choices = $_POST[$choices_key];
                foreach ($choices as $choice_text) {
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

        // Insert test cases for code questions
        if ($answer_type === 'code') {
            error_log("Inserting test cases for Question $question_id");
            $test_cases_key = "test_cases_" . ($index + 1); // Adjust the key by adding 1
            $expected_output_key = "expected_output_" . ($index + 1); // Adjust the key by adding 1
            $programming_language = $programming_languages[$index]; // Added line
            if (isset($_POST[$test_cases_key]) && isset($_POST[$expected_output_key])) {
                $test_cases = $_POST[$test_cases_key];
                $expected_outputs = $_POST[$expected_output_key];
                foreach ($test_cases as $tc_index => $input) {
                    $test_case_id = generateNextId($conn, 'Test_Cases', 'test_case_id', 'T');
                    $expected_output = $expected_outputs[$tc_index];
                    $stmt = $conn->prepare("INSERT INTO Test_Cases (test_case_id, question_id, input, expected_output, programming_language) VALUES (?, ?, ?, ?, ?)"); // Modified line
                    $stmt->bind_param("sssss", $test_case_id, $question_id, $input, $expected_output, $programming_language); // Modified line
                    if ($stmt->execute() !== TRUE) {
                        $_SESSION['error_message'] = "Error: " . $stmt->error;
                        header("Location: create_questions.php?assessment_id=$assessment_id");
                        exit();
                    }
                }
            } else {
                error_log("Test cases or expected output key not found in POST data for index $index");
            }
        }
    }

    $_SESSION['success_message'] = "Questions added successfully.";
    header("Location: manage_assessments.php");
    exit();
}

$conn->close();
?>