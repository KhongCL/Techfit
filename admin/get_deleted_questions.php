<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";


$conn = new mysqli($servername, $username, $password, $dbname);


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
        q.code_template,
        q.programming_language,
        GROUP_CONCAT(DISTINCT c.choice_text ORDER BY c.choice_id SEPARATOR '<<ANSWER_BREAK>>') AS choices
    FROM 
        Question q
    LEFT JOIN 
        Choices c ON q.question_id = c.question_id
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
    $row['choices'] = $row['choices'] ? explode('<<ANSWER_BREAK>>', $row['choices']) : [];
    $deleted_questions[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($deleted_questions);
?>