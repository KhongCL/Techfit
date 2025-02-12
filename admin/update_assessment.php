<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['assessment_id'], $input['column'], $input['value'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit();
}

$assessmentId = $input['assessment_id'];
$column = $input['column'];
$value = $input['value'];

$validColumns = ['assessment_name', 'description'];
if (!in_array($column, $validColumns)) {
    echo json_encode(['success' => false, 'message' => 'Invalid column']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$stmt = $conn->prepare("UPDATE Assessment_Admin SET $column = ? WHERE assessment_id = ?");
$stmt->bind_param("ss", $value, $assessmentId);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update assessment']);
}

$stmt->close();
$conn->close();
?>