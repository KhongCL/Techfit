<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php"); // Redirect to login page if not logged in
    exit();
}

// Check if the user has the correct role
if ($_SESSION['role'] !== 'Employer') {
    header("Location: ../login.php"); // Redirect to login page if the role is not Employer
    exit();
}

// Check if the employer ID is set
if (!isset($_SESSION['employer_id'])) {
    header("Location: ../login.php"); // Redirect to login page if employer ID is not set
    exit();
}
?>