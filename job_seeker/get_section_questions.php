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


$output = [];
foreach ($questions as $question) {
    
    $choices = '';
    if (!empty($question['choices'])) {
        $choices = implode('~', array_map(function($choice) {
            return $choice['choice_id'] . '=' . $choice['choice_text'];
        }, $question['choices']));
    }

    
    $code_template = '';
    if (!empty($question['code_template'])) {
        
        $template = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($matches) {
            return mb_convert_encoding(pack('H*', $matches[1]), 'UTF-8', 'UCS-2BE');
        }, $question['code_template']);
        
        
        $template = str_replace(["\r\n", "\r"], "\n", $template);
        
        
        $template = str_replace("\t", "    ", $template);
        
        
        $template = str_replace('\/\/', '
        
        
        $code_template = json_encode($template);
        
        
        $code_template = substr($code_template, 1, -1);
        
        
        $code_template = str_replace('\/', '/', $code_template);
    }
    
    
    $programming_language = strtolower(trim($question['programming_language'] ?? ''));
    
    
    $question_text = str_replace('|', '&#124;', $question['question_text']);
    
    
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