<?php
session_start();
header('Content-Type: text/plain');

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

if (!isset($_GET['assessment_id'])) {
    die('ERROR: Missing assessment ID');
}

$sql = "SELECT q.*, c.choice_id, c.choice_text 
        FROM Question q 
        LEFT JOIN Choices c ON q.question_id = c.question_id 
        WHERE q.assessment_id = ? AND q.is_active = 1 
        ORDER BY q.question_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_GET['assessment_id']);
$stmt->execute();
$result = $stmt->get_result();

$questions = [];
$current_question = null;

while ($row = $result->fetch_assoc()) {
    if (!isset($questions[$row['question_id']])) {
        $questions[$row['question_id']] = [
            'question_id' => $row['question_id'],
            'question_text' => $row['question_text'],
            'answer_type' => $row['answer_type'],
            'code_template' => $row['code_template'],
            'programming_language' => $row['programming_language'], 
            'choices' => []
        ];
    }
    
    if ($row['choice_id']) {
        $questions[$row['question_id']]['choices'][] = [
            'choice_id' => $row['choice_id'],
            'choice_text' => $row['choice_text']
        ];
    }
}

error_log("Raw question data before formatting:");
foreach ($questions as $qid => $q) {
    error_log("Question $qid: " . print_r($q, true));
}

// Add debugging for each formatted question
foreach ($questions as $question) {
    $formatted = [
        'id' => $question['question_id'],
        'text' => $question['question_text'],
        'type' => $question['answer_type'],
        'template' => $question['code_template'],
        'language' => $question['programming_language']
    ];
    error_log("Formatted question: " . print_r($formatted, true));
}

$QUESTION_DELIMITER = '<<QUESTION_BREAK>>';
$FIELD_DELIMITER = '<<FIELD>>';

// Format output as pipe-delimited string
$output = [];
foreach ($questions as $question) {
    // Format choices as simple string
    $choices = '';
    if (!empty($question['choices'])) {
        $choices = implode('~', array_map(function($choice) {
            return $choice['choice_id'] . '=' . $choice['choice_text'];
        }, $question['choices']));
    }

    // Process code template and ensure newlines are properly escaped
    $code_template = '';
    if (!empty($question['code_template'])) {
        // Decode unicode escape sequences first
        $template = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
            return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
        }, $question['code_template']);
        
        // Normalize line endings
        $template = str_replace(["\r\n", "\r"], "\n", $template);
        
        // Convert tabs to spaces for consistent indentation
        $template = str_replace("\t", "    ", $template);
        
        // Fix escaped comment slashes
        $template = str_replace('\/\/', '//', $template);
        
        // JSON encode to properly escape special characters
        $code_template = json_encode($template);
        
        // Remove surrounding quotes from JSON encoding
        $code_template = substr($code_template, 1, -1);
        
        // Additional cleanup for any remaining escaped slashes
        $code_template = str_replace('\/', '/', $code_template);
    }
    
    // Ensure programming language is properly formatted
    $programming_language = strtolower(trim($question['programming_language'] ?? ''));
    
    // Don't escape colons in text fields anymore
    $question_text = str_replace('|', '&#124;', $question['question_text']);
    
    // Update the output array creation
    $output[] = implode($FIELD_DELIMITER, [
        $question['question_id'],          
        $question_text,                    
        $question['answer_type'],          
        $choices,                          
        $code_template,                    
        $programming_language              
    ]);
}

echo implode($QUESTION_DELIMITER, $output);
$conn->close();