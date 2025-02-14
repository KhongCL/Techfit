<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $default_time_limit = $_POST['default_time_limit'];
    $passing_score = $_POST['passing_score'];

    
    $sql = "INSERT INTO Assessment_Settings (setting_id, default_time_limit, passing_score_percentage)
            VALUES ('1', ?, ?)
            ON DUPLICATE KEY UPDATE
            default_time_limit = VALUES(default_time_limit),
            passing_score_percentage = VALUES(passing_score_percentage)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $default_time_limit, $passing_score);

    if ($stmt->execute()) {
        echo '<script>
            alert("Assessment settings saved successfully.");
            window.location.href = "system_configuration.php";
        </script>';
    } else {
        echo '<script>
            alert("Failed to save assessment settings.");
            window.location.href = "system_configuration.php";
        </script>';
    }

    $stmt->close();
    $conn->close();
}
?>