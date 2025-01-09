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

$sql = "DELETE FROM Employer_Interest WHERE employer_id = '$employer_id' AND job_seeker_id = '$job_seeker_id'";

if ($conn->query($sql) === TRUE) {
    echo "Record deleted successfully";
} else {
    echo "Error deleting record: " . $conn->error;
}

$conn->close();
?>