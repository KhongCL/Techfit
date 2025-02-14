<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = array('success' => true);

if (isset($_POST['restore_questions'])) {
    $question_ids = $_POST['restore_questions'];
    foreach ($question_ids as $question_id) {
        $sql = "UPDATE Question SET is_active = 1 WHERE question_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $question_id);
        if (!$stmt->execute()) {
            $response['success'] = false;
            $response['error'] = $stmt->error;
            break;
        }
        $stmt->close();
    }
} else {
    $response['success'] = false;
    $response['error'] = "No questions selected for restoration.";
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($response);
?>