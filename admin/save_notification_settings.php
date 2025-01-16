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
    $notification_events = $_POST['notification_events'];
    $email_template = $_POST['email_template'];

    // Clear existing settings
    $sql_clear = "DELETE FROM Notification_Settings";
    $conn->query($sql_clear);

    // Insert new settings
    $stmt = $conn->prepare("INSERT INTO Notification_Settings (setting_id, event_name, is_enabled, email_template) VALUES (?, ?, ?, ?)");
    $setting_id = 1;
    foreach ($notification_events as $event) {
        $is_enabled = 1;
        $stmt->bind_param("isis", $setting_id, $event, $is_enabled, $email_template);
        $stmt->execute();
        $setting_id++;
    }

    if ($stmt->affected_rows > 0) {
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

    $stmt->close();
    $conn->close();
}
?>