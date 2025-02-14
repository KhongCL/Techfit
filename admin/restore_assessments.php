<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = array('success' => false);

if (isset($_POST['restore_assessments'])) {
    $assessment_ids = $_POST['restore_assessments'];
    $sql = "UPDATE Assessment_Admin SET is_active = 1 WHERE assessment_id = ?";
    $stmt = $conn->prepare($sql);
    foreach ($assessment_ids as $assessment_id) {
        $stmt->bind_param("s", $assessment_id);
        if (!$stmt->execute()) {
            $response['error'] = $stmt->error;
            echo json_encode($response);
            exit();
        }
    }
    $stmt->close();
    $response['success'] = true;
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>