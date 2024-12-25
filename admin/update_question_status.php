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

// Get the JSON data from the request
$data = json_decode(file_get_contents('php://input'), true);
$question_id = $data['question_id'];
$is_active = $data['is_active'];

// Update the is_active field in the database
$sql = "UPDATE Question SET is_active = ? WHERE question_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $is_active, $question_id);

$response = array();
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
}

$stmt->close();
$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>