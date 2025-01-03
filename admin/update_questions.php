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

$response = array('success' => true);

foreach ($_POST['question_id'] as $index => $question_id) {
    $question_text = $_POST['question_text'][$index];
    $question_type = $_POST['question_type'][$index];
    $answer_type = $_POST['answer_type'][$index];
    $correct_answer = $_POST['correct_choice'][$index];

    // Validate input fields
    if (empty($question_text) || empty($question_type) || empty($answer_type) || empty($correct_answer)) {
        $response['success'] = false;
        $response['error'] = "All fields are required.";
        error_log("Validation failed for question index $index: All fields are required.");
        break;
    }

    // Check if the question ID is empty (new question)
    if (empty($question_id)) {
        $question_id = generateNextId($conn, 'Question', 'question_id', 'Q');
        $sql = "INSERT INTO Question (question_id, assessment_id, question_text, question_type, answer_type, correct_answer) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $question_id, $_POST['assessment_id'], $question_text, $question_type, $answer_type, $correct_answer);
        error_log("Inserting new question with ID $question_id");
    } else {
        $sql = "UPDATE Question SET question_text = ?, question_type = ?, answer_type = ?, correct_answer = ? WHERE question_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $question_text, $question_type, $answer_type, $correct_answer, $question_id);
        error_log("Updating question with ID $question_id");
    }

    if (!$stmt->execute()) {
        $response['success'] = false;
        $response['error'] = $stmt->error;
        error_log("Database error for question ID $question_id: " . $stmt->error);
        break;
    }

    // Update choices for multiple choice questions
    if ($answer_type === 'multiple choice') {
        $choices_key = "choices_" . ($index + 1);
        if (isset($_POST[$choices_key])) {
            $choices = $_POST[$choices_key];
            foreach ($choices as $choice_index => $choice_text) {
                if (empty($choice_text)) {
                    $response['success'] = false;
                    $response['error'] = "All choice fields are required.";
                    error_log("Validation failed for choice index $choice_index: All choice fields are required.");
                    break 2;
                }
    
                // Use the choice ID provided in the form data
                $choice_id_key = "choice_id_" . ($index + 1) . "_" . ($choice_index + 1);
                error_log("Choice ID key for choice index $choice_index: " . $choice_id_key);
                error_log("Choice text for choice index $choice_index: " . $choice_text);
    
                $choice_id = isset($_POST[$choice_id_key]) ? $_POST[$choice_id_key] : '';
    
                // Log the choice ID to see if it is empty
                error_log("Choice ID for choice index $choice_index: " . $choice_id);
    
                if (!empty($choice_id)) {
                    // Check if the choice text has changed
                    $check_choice_sql = "SELECT choice_text FROM Choices WHERE choice_id = ? AND question_id = ?";
                    $check_choice_stmt = $conn->prepare($check_choice_sql);
                    $check_choice_stmt->bind_param("ss", $choice_id, $question_id);
                    $check_choice_stmt->execute();
                    $check_choice_result = $check_choice_stmt->get_result();
                    $existing_choice = $check_choice_result->fetch_assoc();
    
                    if ($existing_choice['choice_text'] !== $choice_text) {
                        // Update existing choice
                        $update_choice_sql = "UPDATE Choices SET choice_text = ? WHERE choice_id = ? AND question_id = ?";
                        $update_choice_stmt = $conn->prepare($update_choice_sql);
                        $update_choice_stmt->bind_param("sss", $choice_text, $choice_id, $question_id);
                        if (!$update_choice_stmt->execute()) {
                            $response['success'] = false;
                            $response['error'] = $update_choice_stmt->error;
                            error_log("Database error for choice ID $choice_id: " . $update_choice_stmt->error);
                            break 2;
                        }
                    }
                } else {
                    // Insert new choice
                    $choice_id = generateNextId($conn, 'Choices', 'choice_id', 'C');
                    $insert_choice_sql = "INSERT INTO Choices (choice_id, question_id, choice_text) VALUES (?, ?, ?)";
                    $insert_choice_stmt = $conn->prepare($insert_choice_sql);
                    $insert_choice_stmt->bind_param("sss", $choice_id, $question_id, $choice_text);
                    if (!$insert_choice_stmt->execute()) {
                        $response['success'] = false;
                        $response['error'] = $insert_choice_stmt->error;
                        error_log("Database error for new choice ID $choice_id: " . $insert_choice_stmt->error);
                        break 2;
                    }
                }
            }
        }
    }

    // Update test cases for code questions
    if ($answer_type === 'code') {
        $test_cases_key = "test_cases_" . ($index + 1);
        $expected_output_key = "expected_output_" . ($index + 1);
        if (isset($_POST[$test_cases_key]) && isset($_POST[$expected_output_key])) {
            $test_cases = $_POST[$test_cases_key];
            $expected_outputs = $_POST[$expected_output_key];
            foreach ($test_cases as $tc_index => $input) {
                if (empty($input) || empty($expected_outputs[$tc_index])) {
                    $response['success'] = false;
                    $response['error'] = "All test case fields are required.";
                    error_log("Validation failed for test case index $tc_index: All test case fields are required.");
                    break 2;
                }

                // Use the test case ID provided in the form data
                $test_case_id_key = "test_case_id_" . ($index + 1) . "_" . ($tc_index + 1);
                $test_case_id = isset($_POST[$test_case_id_key]) ? $_POST[$test_case_id_key] : '';

                if (!empty($test_case_id)) {
                    // Update existing test case
                    $update_test_case_sql = "UPDATE Test_Cases SET input = ?, expected_output = ? WHERE test_case_id = ? AND question_id = ?";
                    $update_test_case_stmt = $conn->prepare($update_test_case_sql);
                    $update_test_case_stmt->bind_param("ssss", $input, $expected_outputs[$tc_index], $test_case_id, $question_id);
                    if (!$update_test_case_stmt->execute()) {
                        $response['success'] = false;
                        $response['error'] = $update_test_case_stmt->error;
                        error_log("Database error for test case ID $test_case_id: " . $update_test_case_stmt->error);
                        break 2;
                    }
                } else {
                    // Insert new test case
                    $test_case_id = generateNextId($conn, 'Test_Cases', 'test_case_id', 'T');
                    $insert_test_case_sql = "INSERT INTO Test_Cases (test_case_id, question_id, input, expected_output) VALUES (?, ?, ?, ?)";
                    $insert_test_case_stmt = $conn->prepare($insert_test_case_sql);
                    $insert_test_case_stmt->bind_param("ssss", $test_case_id, $question_id, $input, $expected_outputs[$tc_index]);
                    if (!$insert_test_case_stmt->execute()) {
                        $response['success'] = false;
                        $response['error'] = $insert_test_case_stmt->error;
                        error_log("Database error for new test case ID $test_case_id: " . $insert_test_case_stmt->error);
                        break 2;
                    }
                }
            }
        }
    }
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>