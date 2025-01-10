<?php
session_start(); // Start the session to access session variables

// Function to display the message and options
function displayLoginMessage() {
    echo '<script>
        if (confirm("You need to log in to access this page. Go to Login Page? Click cancel to go to home page.")) {
            window.location.href = "../login.php";
        } else {
            window.location.href = "../index.php";
        }
    </script>';
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); // Display message and options if not logged in
}

// Check if the user has the correct role
if ($_SESSION['role'] !== 'Job Seeker') {
    displayLoginMessage(); // Display message and options if the role is not Job Seeker
}

// Check if the job seeker ID is set
if (!isset($_SESSION['job_seeker_id'])) {
    displayLoginMessage(); // Display message and options if job seeker ID is not set
}

// Close the session
session_write_close();
?>