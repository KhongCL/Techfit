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

$employer_id = $_SESSION['employer_id'];
$job_seeker_id = $_POST['job_seeker_id'];
$interest_status = $_POST['interest_status'];


$sql = "SELECT * FROM Employer_Interest WHERE employer_id = '$employer_id' AND job_seeker_id = '$job_seeker_id'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    
    $sql = "UPDATE Employer_Interest SET interest_status = '$interest_status', is_active = 1 WHERE employer_id = '$employer_id' AND job_seeker_id = '$job_seeker_id'";
} else {
    
    $sql = "INSERT INTO Employer_Interest (employer_id, job_seeker_id, interest_status, is_active) VALUES ('$employer_id', '$job_seeker_id', '$interest_status', 1)";
}

if ($conn->query($sql) === TRUE) {
    echo "Record updated successfully";
} else {
    echo "Error updating record: " . $conn->error;
}

$conn->close();
?>