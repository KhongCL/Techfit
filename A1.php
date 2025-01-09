<?php
session_start(); // Start the session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../admin_login.php?key=techfit"); // Redirect to admin login page if not logged in
    exit();
}

// Check if the user has the correct role
if ($_SESSION['role'] !== 'Admin') {
    header("Location: ../admin_login.php?key=techfit"); // Redirect to admin login page if the role is not Admin
    exit();
}

// Check if the admin ID is set
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin_login.php?key=techfit"); // Redirect to admin login page if admin ID is not set
    exit();
}
?>