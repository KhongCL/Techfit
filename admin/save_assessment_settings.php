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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $default_time_limit = $_POST['default_time_limit'];
    $passing_score = $_POST['passing_score'];
    $question_types = json_encode($_POST['question_types']);

    // Insert or update assessment settings in the database
    $sql = "INSERT INTO Assessment_Settings (setting_id, default_time_limit, passing_score_percentage, allowed_question_types)
            VALUES ('1', ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            default_time_limit = VALUES(default_time_limit),
            passing_score_percentage = VALUES(passing_score_percentage),
            allowed_question_types = VALUES(allowed_question_types)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iis", $default_time_limit, $passing_score, $question_types);

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