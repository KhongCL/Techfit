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

$sql = "SELECT question_id, question_text FROM Question WHERE assessment_id = ? AND is_active = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

$deleted_questions = array();
while ($row = $result->fetch_assoc()) {
    $deleted_questions[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($deleted_questions);
?>