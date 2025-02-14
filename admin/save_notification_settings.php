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
    $notification_events = isset($_POST['notification_events']) ? $_POST['notification_events'] : [];
    $email_templates = $_POST['email_template'];

    
    $stmt_update = $conn->prepare("UPDATE Notification_Settings SET is_enabled = ?, email_template = ? WHERE event_name = ?");
    $stmt_insert = $conn->prepare("INSERT INTO Notification_Settings (setting_id, event_name, is_enabled, email_template) VALUES (?, ?, ?, ?)");

    
    $result = $conn->query("SELECT MAX(CAST(SUBSTRING(setting_id, 3) AS UNSIGNED)) AS max_id FROM Notification_Settings");
    $row = $result->fetch_assoc();
    $setting_id_num = $row['max_id'] ? $row['max_id'] + 1 : 1;

    foreach ($email_templates as $event => $template) {
        $is_enabled = in_array($event, $notification_events) ? 1 : 0;

        
        $stmt_update->bind_param("iss", $is_enabled, $template, $event);
        $stmt_update->execute();

        
        if ($stmt_update->affected_rows === 0) {
            $stmt_check = $conn->prepare("SELECT COUNT(*) FROM Notification_Settings WHERE event_name = ?");
            $stmt_check->bind_param("s", $event);
            $stmt_check->execute();
            $stmt_check->bind_result($count);
            $stmt_check->fetch();
            $stmt_check->close();

            
            if ($count == 0) {
                $stmt_check_template = $conn->prepare("SELECT COUNT(*) FROM Notification_Settings WHERE event_name = ? AND email_template = ?");
                $stmt_check_template->bind_param("ss", $event, $template);
                $stmt_check_template->execute();
                $stmt_check_template->bind_result($template_count);
                $stmt_check_template->fetch();
                $stmt_check_template->close();
            
                if ($template_count == 0) {
                    $setting_id = sprintf("SE%02d", $setting_id_num);
                    $stmt_insert->bind_param("ssis", $setting_id, $event, $is_enabled, $template);
                    if (!$stmt_insert->execute()) {
                        echo '<script>
                            alert("Failed to insert new notification setting: ' . $stmt_insert->error . '");
                            window.location.href = "system_configuration.php";
                        </script>';
                        exit();
                    }
                    $setting_id_num++;
                }
            }
        } else {
            if ($stmt_update->affected_rows === 0) {
                echo '<script>
                    alert("Failed to update notification setting: ' . $stmt_update->error . '");
                    window.location.href = "system_configuration.php";
                </script>';
                exit();
            }
        }
    }

    echo '<script>
        alert("Notification settings saved successfully.");
        window.location.href = "system_configuration.php";
    </script>';

    $stmt_update->close();
    $stmt_insert->close();
    $conn->close();
}
?>