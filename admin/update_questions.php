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

$response = array('success' => true);


error_log("Received POST data: " . print_r($_POST, true));

foreach ($_POST['question_id'] as $index => $question_id) {
    $question_text = $_POST['question_text'][$index];
    $question_type = $_POST['question_type'][$index];
    $answer_type = $_POST['answer_type'][$index];
    $correct_answer = $_POST['correct_choice'][$index];

    
    if (empty($question_text) || empty($question_type) || empty($answer_type) || empty($correct_answer)) {
        $response['success'] = false;
        $response['error'] = "All fields are required.";
        break;
    }

    
    if (empty($question_id)) {
        $question_id = generateNextId($conn, 'Question', 'question_id', 'Q');
        $sql = "INSERT INTO Question (question_id, assessment_id, question_text, question_type, answer_type, correct_answer) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $question_id, $_POST['assessment_id'], $question_text, $question_type, $answer_type, $correct_answer);
    } else {
        $sql = "UPDATE Question SET question_text = ?, question_type = ?, answer_type = ?, correct_answer = ? WHERE question_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $question_text, $question_type, $answer_type, $correct_answer, $question_id);
    }

    if (!$stmt->execute()) {
        $response['success'] = false;
        $response['error'] = $stmt->error;
        break;
    }

    
    if ($answer_type === 'multiple choice') {
        $choices_key = "choices_" . ($index + 1);
        $choice_ids_key = "choice_id_" . ($index + 1);
        if (isset($_POST[$choices_key]) && isset($_POST[$choice_ids_key])) {
            $choices = $_POST[$choices_key];
            $choice_ids = $_POST[$choice_ids_key];
            foreach ($choices as $choice_index => $choice_text) {
                if (empty($choice_text)) {
                    $response['success'] = false;
                    $response['error'] = "All choice fields are required.";
                    break 2;
                }

                
                $choice_id = isset($choice_ids[$choice_index]) ? $choice_ids[$choice_index] : '';

                
                if (!empty($choice_id)) {
                    
                    $check_choice_sql = "SELECT choice_text FROM Choices WHERE choice_id = ? AND question_id = ?";
                    $check_choice_stmt = $conn->prepare($check_choice_sql);
                    $check_choice_stmt->bind_param("ss", $choice_id, $question_id);
                    $check_choice_stmt->execute();
                    $check_choice_result = $check_choice_stmt->get_result();
                    $existing_choice = $check_choice_result->fetch_assoc();

                    if ($existing_choice['choice_text'] !== $choice_text) {
                        
                        $update_choice_sql = "UPDATE Choices SET choice_text = ? WHERE choice_id = ? AND question_id = ?";
                        $update_choice_stmt = $conn->prepare($update_choice_sql);
                        $update_choice_stmt->bind_param("sss", $choice_text, $choice_id, $question_id);
                        if (!$update_choice_stmt->execute()) {
                            $response['success'] = false;
                            $response['error'] = $update_choice_stmt->error;
                            break 2;
                        }
                    } else {
                        error_log("No changes detected for choice ID $choice_id");
                    }
                } else {
                    
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

    if ($answer_type === 'code') {
        $code_template = isset($_POST['code_template'][$index]) ? $_POST['code_template'][$index] : '';
        $programming_language = isset($_POST['code_language'][$index]) ? $_POST['code_language'][$index] : '';
    
        
        if (empty($code_template) || empty($programming_language)) {
            $response['success'] = false;
            $response['error'] = "Code template and programming language are required for code questions.";
            error_log("Missing code template or programming language for question index $index");
            break;
        }
    
        
        $answers = explode('<<ANSWER_BREAK>>', $correct_answer);
        if (count($answers) < 2) {
            $response['success'] = false;
            $response['error'] = "Please provide at least two answers separated by <<ANSWER_BREAK>>";
            break;
        }
        
        
        foreach ($answers as $answer) {
            if (trim($answer) === '') {
                $response['success'] = false;
                $response['error'] = "Empty or blank answers are not allowed. Please provide valid answers separated by <<ANSWER_BREAK>>";
                break 2;
            }
        }
    
        
        $blank_count = substr_count($code_template, '__BLANK__');
        if ($blank_count !== count($answers)) {
            $response['success'] = false;
            $response['error'] = "Number of blanks ({$blank_count}) must match number of answers (" . count($answers) . ").";
            break;
        }
    
        
        if (empty($question_id)) {
            $question_id = generateNextId($conn, 'Question', 'question_id', 'Q');
            $sql = "INSERT INTO Question (question_id, assessment_id, question_text, question_type, answer_type, correct_answer, code_template, programming_language) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssss", $question_id, $_POST['assessment_id'], $question_text, $question_type, $answer_type, $correct_answer, $code_template, $programming_language);
        } else {
            $sql = "UPDATE Question SET question_text = ?, question_type = ?, answer_type = ?, correct_answer = ?, code_template = ?, programming_language = ? WHERE question_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssss", $question_text, $question_type, $answer_type, $correct_answer, $code_template, $programming_language, $question_id);
        }
    
        if (!$stmt->execute()) {
            $response['success'] = false;
            $response['error'] = "Error saving code question: " . $stmt->error;
            error_log("Database error while saving code question: " . $stmt->error);
            break;
        }
    }
}


$update_last_modified_sql = "UPDATE Assessment_Admin SET last_modified = NOW() WHERE assessment_id = ?";
$update_last_modified_stmt = $conn->prepare($update_last_modified_sql);
$update_last_modified_stmt->bind_param("s", $_POST['assessment_id']);
if (!$update_last_modified_stmt->execute()) {
    $response['success'] = false;
    $response['error'] = $update_last_modified_stmt->error;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>