<?php
session_start(); 

function displayLoginMessage() {
    echo '<script>
        alert("You need to log in to access this page.");
    </script>';
    exit();
}

if (!isset($_SESSION['user_id'])) {
    displayLoginMessage();s
}

if ($_SESSION['role'] !== 'Admin') {
    displayLoginMessage(); // Display message if the role is not Admin
}

// Close the session
session_write_close();
?>