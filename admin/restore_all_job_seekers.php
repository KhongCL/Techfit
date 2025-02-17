<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$checkSql = "SELECT COUNT(*) as count FROM User WHERE role = 'Job Seeker' AND is_active = 0";
$result = $conn->query($checkSql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    echo json_encode(['success' => false, 'message' => 'No deleted job seekers found to restore']);
    $conn->close();
    exit();
}

$sql = "UPDATE User SET is_active = 1 WHERE role = 'Job Seeker' AND is_active = 0";

if ($conn->query($sql) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'All job seekers have been restored']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to restore job seekers']);
}

$conn->close();
?>