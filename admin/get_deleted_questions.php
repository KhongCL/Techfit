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

$sql = "
    SELECT 
        q.question_id, 
        q.question_text, 
        q.question_type, 
        q.answer_type, 
        q.correct_answer,
        GROUP_CONCAT(DISTINCT c.choice_text ORDER BY c.choice_id SEPARATOR '|') AS choices,
        GROUP_CONCAT(DISTINCT CONCAT(tc.input, '=>', tc.expected_output) ORDER BY tc.test_case_id SEPARATOR '|') AS test_cases
    FROM 
        Question q
    LEFT JOIN 
        Choices c ON q.question_id = c.question_id
    LEFT JOIN 
        Test_Cases tc ON q.question_id = tc.question_id
    WHERE 
        q.assessment_id = ? AND q.is_active = 0
    GROUP BY 
        q.question_id
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

$deleted_questions = array();
while ($row = $result->fetch_assoc()) {
    $row['choices'] = $row['choices'] ? explode('|', $row['choices']) : [];
    $row['test_cases'] = $row['test_cases'] ? array_map(function($tc) {
        list($input, $output) = explode('=>', $tc);
        return ['input' => $input, 'expected_output' => $output];
    }, explode('|', $row['test_cases'])) : [];
    $deleted_questions[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($deleted_questions);
?>