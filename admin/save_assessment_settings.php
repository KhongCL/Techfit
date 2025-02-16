<?php
session_start();

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
    $user_id_from_session = $_SESSION['user_id'];

    $sql_admin_query = "SELECT admin_id FROM admin WHERE user_id = ?"; 
    $stmt_admin = $conn->prepare($sql_admin_query);
    $stmt_admin->bind_param("s", $user_id_from_session);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($row_admin = $result_admin->fetch_assoc()) {
        $current_admin_id = $row_admin['admin_id'];

        $sql = "INSERT INTO Assessment_Settings (setting_id, default_time_limit, passing_score_percentage, admin_id)
                VALUES ('1', ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                default_time_limit = VALUES(default_time_limit),
                passing_score_percentage = VALUES(passing_score_percentage),
                admin_id = VALUES(admin_id)";

        $stmt_settings = $conn->prepare($sql);
        $stmt_settings->bind_param("iis", $default_time_limit, $passing_score, $current_admin_id);

        if ($stmt_settings->execute()) {
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
        $stmt_settings->close();


    } else {
        echo '<script>
            alert("Error: Admin ID not found for the current user.");
            window.location.href = "system_configuration.php";
        </script>';
        error_log("Error: Admin ID not found in 'admin' table for user_id: " . $user_id_from_session);
    }

    $stmt_admin->close();


    $conn->close();
}
?>