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

$question_id = $_GET['question_id'];

$sql = "SELECT input, expected_output, programming_language FROM Test_Cases WHERE question_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $question_id);
$stmt->execute();
$result = $stmt->get_result();

$testCases = array();
while ($row = $result->fetch_assoc()) {
    $testCases[] = $row;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($testCases);
?>