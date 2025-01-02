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
        break;
    }

    $sql = "UPDATE Question SET question_text = ?, question_type = ?, answer_type = ?, correct_answer = ? WHERE question_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $question_text, $question_type, $answer_type, $correct_answer, $question_id);

    if (!$stmt->execute()) {
        $response['success'] = false;
        $response['error'] = $stmt->error;
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
                    break 2;
                }

                // Check if the choice already exists
                $choice_id = "C" . str_pad($choice_index + 1, 2, '0', STR_PAD_LEFT);
                $check_choice_sql = "SELECT choice_id FROM Choices WHERE choice_id = ? AND question_id = ?";
                $check_choice_stmt = $conn->prepare($check_choice_sql);
                $check_choice_stmt->bind_param("ss", $choice_id, $question_id);
                $check_choice_stmt->execute();
                $check_choice_result = $check_choice_stmt->get_result();

                if ($check_choice_result->num_rows > 0) {
                    // Update existing choice
                    $update_choice_sql = "UPDATE Choices SET choice_text = ? WHERE choice_id = ? AND question_id = ?";
                    $update_choice_stmt = $conn->prepare($update_choice_sql);
                    $update_choice_stmt->bind_param("sss", $choice_text, $choice_id, $question_id);
                    if (!$update_choice_stmt->execute()) {
                        $response['success'] = false;
                        $response['error'] = $update_choice_stmt->error;
                        break 2;
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
                    break 2;
                }
            }
            $sql = "DELETE FROM Test_Cases WHERE question_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $question_id);
            $stmt->execute();

            foreach ($test_cases as $tc_index => $input) {
                $test_case_id = generateNextId($conn, 'Test_Cases', 'test_case_id', 'T');
                $expected_output = $expected_outputs[$tc_index];
                $sql = "INSERT INTO Test_Cases (test_case_id, question_id, input, expected_output) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $test_case_id, $question_id, $input, $expected_output);
                if (!$stmt->execute()) {
                    $response['success'] = false;
                    $response['error'] = $stmt->error;
                    break 2;
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