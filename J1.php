<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Redirect to login page if not logged in
    exit();
}

// Check if the user has the correct role
if ($_SESSION['role'] !== 'Job Seeker') {
    header("Location: ../login.php"); // Redirect to login page if the role is not Job Seeker
    exit();
}

// Check if the job seeker ID is set
if (!isset($_SESSION['job_seeker_id'])) {
    header("Location: ../login.php"); // Redirect to login page if job seeker ID is not set
    exit();
}
?>