<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['user_ids'])) {
    $user_ids = explode(',', $_GET['user_ids']);
    $sql = "UPDATE User SET is_active = 0 WHERE user_id = ? AND role = 'Job Seeker'";
    $stmt = $conn->prepare($sql);
    foreach ($user_ids as $user_id) {
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
    }
    $stmt->close();
}

$conn->close();
header("Location: manage_users.php");
exit();
?>