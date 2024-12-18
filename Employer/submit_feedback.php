<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Function to generate the next ID with a given prefix
function generateNextId($conn, $table, $column, $prefix) {
    $sql = "SELECT MAX(CAST(SUBSTRING($column, LENGTH('$prefix') + 1) AS UNSIGNED)) AS max_id FROM $table WHERE $column LIKE '$prefix%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ? $row['max_id'] : 0;
    $next_id = $prefix . str_pad($max_id + 1, 2, '0', STR_PAD_LEFT);
    return $next_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $feedback_id = generateNextId($conn, 'Feedback', 'feedback_id', 'F');
    $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session
    $feedback_text = $_POST['feedback_text'];
    $timestamp = date('Y-m-d H:i:s');

    $sql = "INSERT INTO Feedback (feedback_id, user_id, text, timestamp)
            VALUES ('$feedback_id', '$user_id', '$feedback_text', '$timestamp')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Thank you! Your feedback has been submitted successfully.";
        header("Location: feedback.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $conn->error;
        header("Location: feedback.php");
        exit();
    }
}

$conn->close();
?>