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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = 1; // Replace with actual user ID
    $languages = $_POST['languages'];

    foreach ($languages as $language) {
        $sql = "INSERT INTO skills (user_id, language, proficiency) VALUES ('$user_id', '$language', 'Intermediate')"; // Replace 'Intermediate' with actual proficiency
        if ($conn->query($sql) !== TRUE) {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    echo "Skills saved successfully";
}

$conn->close();
?>