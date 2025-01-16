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
    $notification_events = isset($_POST['notification_events']) ? $_POST['notification_events'] : [];
    $email_templates = $_POST['email_template'];

    // Prepare statements for updating and inserting settings
    $stmt_update = $conn->prepare("UPDATE Notification_Settings SET is_enabled = ?, email_template = ? WHERE event_name = ?");
    $stmt_insert = $conn->prepare("INSERT INTO Notification_Settings (setting_id, event_name, is_enabled, email_template) VALUES (?, ?, ?, ?)");

    // Fetch the maximum setting_id from the database
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING(setting_id, 3) AS UNSIGNED)) AS max_id FROM Notification_Settings");
    $row = $result->fetch_assoc();
    $setting_id_num = $row['max_id'] ? $row['max_id'] + 1 : 1;
    $setting_id = sprintf("SE%02d", $setting_id_num);

    foreach ($email_templates as $event => $template) {
        $is_enabled = in_array($event, $notification_events) ? 1 : 0;

        // Try to update the existing setting
        $stmt_update->bind_param("iss", $is_enabled, $template, $event);
        $stmt_update->execute();

        // If no rows were affected, insert a new setting
        if ($stmt_update->affected_rows === 0) {
            $stmt_insert->bind_param("ssis", $setting_id, $event, $is_enabled, $template);
            $stmt_insert->execute();
            $setting_id_num++;
            $setting_id = sprintf("SE%02d", $setting_id_num);
        }
    }

    if ($stmt_update->affected_rows > 0 || $stmt_insert->affected_rows > 0) {
        echo '<script>
            alert("Notification settings saved successfully.");
            window.location.href = "system_configuration.php";
        </script>';
    } else {
        echo '<script>
            alert("Failed to save notification settings.");
            window.location.href = "system_configuration.php";
        </script>';
    }

    $stmt_update->close();
    $stmt_insert->close();
    $conn->close();
}
?>