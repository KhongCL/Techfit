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
    $teamwork = $_POST['teamwork'];
    $learning = $_POST['learning'];
    $environment = $_POST['environment'];

    $sql = "INSERT INTO assessments (user_id, teamwork, learning, environment) VALUES ('$user_id', '$teamwork', '$learning', '$environment')";

    if ($conn->query($sql) === TRUE) {
        echo "Assessment saved successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>