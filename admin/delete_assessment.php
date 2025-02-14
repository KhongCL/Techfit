<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['assessment_id'])) {
    $assessment_id = $_GET['assessment_id'];
    $sql = "UPDATE Assessment_Admin SET is_active = 0 WHERE assessment_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $assessment_id);
    $stmt->execute();
    $stmt->close();
} elseif (isset($_GET['assessment_ids'])) {
    $assessment_ids = explode(',', $_GET['assessment_ids']);
    $sql = "UPDATE Assessment_Admin SET is_active = 0 WHERE assessment_id = ?";
    $stmt = $conn->prepare($sql);
    foreach ($assessment_ids as $assessment_id) {
        $stmt->bind_param("s", $assessment_id);
        $stmt->execute();
    }
    $stmt->close();
}

$conn->close();
header("Location: manage_assessments.php");
exit();
?>