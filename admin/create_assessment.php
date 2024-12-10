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
    $assessment_id = generateNextId($conn, 'Assessment', 'assessment_id', 'AS');
    $admin_user_id = $_SESSION['user_id']; // Assuming the admin is logged in and their user_id is stored in the session

    // Retrieve the admin_id using the user_id
    $admin_sql = "SELECT admin_id FROM Admin WHERE user_id = '$admin_user_id'";
    $admin_result = $conn->query($admin_sql);
    if ($admin_result->num_rows > 0) {
        $admin_row = $admin_result->fetch_assoc();
        $admin_id = $admin_row['admin_id'];
    } else {
        $_SESSION['error_message'] = "Admin not found.";
        header("Location: create_assessment.html");
        exit();
    }

    $assessment_type = $_POST['assessment_type'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

   
    $sql = "INSERT INTO Assessment (assessment_id, admin_id, assessment_type, start_time, end_time, is_active)
            VALUES ('$assessment_id', '$admin_id', '$assessment_type', '$start_time', '$end_time', TRUE)";
    echo $sql; // Debugging: Print the SQL query

    if ($conn->query($sql) === TRUE) {
        $_SESSION['success_message'] = "Assessment created successfully.";
        header("Location: manage_assessments.html");
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $conn->error;
        header("Location: create_assessment.html");
        exit();
    }
}

$conn->close();
?>