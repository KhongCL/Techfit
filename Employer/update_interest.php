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

if (!isset($_SESSION['employer_id'])) {
    die("Employer not logged in.");
}

$employer_id = $_SESSION['employer_id']; // Get the logged-in employer's ID from the session
$job_seeker_id = $_POST['job_seeker_id'];
$interest_status = $_POST['interest_status'];

$sql = "INSERT INTO Employer_Interest (employer_id, job_seeker_id, interest_status) VALUES ('$employer_id', '$job_seeker_id', '$interest_status')
        ON DUPLICATE KEY UPDATE interest_status='$interest_status'";

if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>