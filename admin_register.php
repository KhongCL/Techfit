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

// Check for unique key in URL
$admin_key = "techfit";
if (!isset($_GET['key']) || $_GET['key'] !== $admin_key) {
    die("Access denied.");
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
    $user_id = generateNextId($conn, 'User', 'user_id', 'U');
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $role = 'Admin'; // Fixed role for admin registration

    // Check for duplicate username or email
    $check_sql = "SELECT * FROM User WHERE username='$username' OR email='$email'";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Username or email already exists.";
        header("Location: admin_register.php?key=$admin_key");
        exit();
    }

    $sql = "INSERT INTO User (user_id, username, first_name, last_name, email, password, birthday, gender, role, is_active)
            VALUES ('$user_id', '$username', '$first_name', '$last_name', '$email', '$password', '$birthday', '$gender', '$role', TRUE)";

    if ($conn->query($sql) === TRUE) {
        $admin_id = generateNextId($conn, 'Admin', 'admin_id', 'AD');
        $sql = "INSERT INTO Admin (admin_id, user_id)
                VALUES ('$admin_id', '$user_id')";

        if ($conn->query($sql) === TRUE) {
            header("Location: admin_login.php?key=$admin_key");
            exit();
        } else {
            $_SESSION['error_message'] = "Error: " . $conn->error;
            header("Location: admin_register.php?key=$admin_key");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Error: " . $conn->error;
        header("Location: admin_register.php?key=$admin_key");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register - TechFit</title>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow:hidden; /* Prevent scrolling*/
        }

        .logo {
            position: absolute;
            top: 20px;
            left: 20px;
            z-index: 2; /* Ensure the logo is above the container */
        }
        .logo img {
            height: 50px;
        }

        h2 {
            margin-top: 60px; /* Add margin to avoid overlap with the logo */
        }

        .container {
            background-color: #1e1e1e;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
            box-sizing: border-box;
            overflow-y: auto; /* Add vertical scrollbar */
            max-height: 90vh;
            position: relative; /* Ensure the logo is positioned relative to the container */
            z-index: 1; /* Ensure the container is below the logo */

        }

        input[type="text"], input[type="email"], input[type="password"], input[type="date"], select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #333;
            color: #fff;
            box-sizing: border-box;
        }
        
        input[type="submit"] {
            width: 100%;
            padding: 10px 20px;
            margin: 10px 0;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            box-sizing: border-box;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: red;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .logo {
                position: relative;
                top: 0;
                left: 0;
                margin-bottom: 20px;
                align-items: left;
                justify-content: left;
                z-index: 2;
            }

            h2 {
                margin-top: 0; /* Remove top margin for mobile */
            }
        }
        
    </style>
    
</head>
<body>

    <main>
        <div class="container">
        <div class="logo">
            <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
        </div>
            <h2>Admin Register</h2>
            <?php

            if (isset($_SESSION['error_message'])) {
                echo '<p class="error-message">' . $_SESSION['error_message'] . '</p>';
                unset($_SESSION['error_message']);
            }
            ?>
            <form action="admin_register.php?key=techfit" method="post">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required><br>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required><br>

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required><br>

                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required><br>

                <label for="birthday">Birthday:</label>
                <input type="date" id="birthday" name="birthday" required><br>

                <label for="gender">Gender:</label>
                <select id="gender" name="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select><br>

                <input type="submit" value="Register">
            </form>
            <p>Already have an account? <a href="admin_login.php?key=techfit">Login here</a></p>
        </div>
    </main>

</body>
</html>