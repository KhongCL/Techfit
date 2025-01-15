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

$sql = "SELECT assessment_id, assessment_name, description, last_modified,  timestamp FROM Assessment_Admin WHERE is_active = 0";
$result = $conn->query($sql);

$deleted_assessments = array();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['last_modified'] !== null) {
            $row['last_modified'] = date('Y-m-d H:i:s', strtotime($row['last_modified']));
        } else {
            $row['last_modified'] = '';
        }
        $deleted_assessments[] = $row;
    }
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($deleted_assessments);
?>