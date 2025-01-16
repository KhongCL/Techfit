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
    $min_password_length = $_POST['min_password_length'];
    $complexity_requirements = $_POST['complexity_requirements'];
    $password_expiration = $_POST['password_expiration'];

    $require_uppercase = in_array('Uppercase', $complexity_requirements) ? 1 : 0;
    $require_special_char = in_array('Special Characters', $complexity_requirements) ? 1 : 0;
    $require_numbers = in_array('Numbers', $complexity_requirements) ? 1 : 0;

    // Insert or update security settings in the database
    $sql = "INSERT INTO Security_Settings (setting_id, min_password_length, require_special_char, require_uppercase, require_numbers, password_expiration_days)
            VALUES ('1', ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
            min_password_length = VALUES(min_password_length),
            require_special_char = VALUES(require_special_char),
            require_uppercase = VALUES(require_uppercase),
            require_numbers = VALUES(require_numbers),
            password_expiration_days = VALUES(password_expiration_days)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiiii", $min_password_length, $require_special_char, $require_uppercase, $require_numbers, $password_expiration);

    if ($stmt->execute()) {
        echo '<script>
            alert("Security settings saved successfully.");
            window.location.href = "system_configuration.php";
        </script>';
    } else {
        echo '<script>
            alert("Failed to save security settings.");
            window.location.href = "system_configuration.php";
        </script>';
    }

    $stmt->close();
    $conn->close();
}
?>