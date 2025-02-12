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
        }

        .logo {
            position: absolute;
            top: 10px;
            left: 20px;
        }
        .logo img {
            height: 50px;
        }

        h2 {
            margin-top: -25px; /* Move title up by 25px */
        }

        .container {
            background-color: #1e1e1e;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 900px; /* Increased width */
            text-align: center;
        }
        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 35px; /* Increased spacing */
        }
        .form-row label {
            flex: 1;
            margin-right: 10px; /* Increased spacing */
        }
        .form-row input, .form-row select {
            flex: 1;
            padding: 15px; /* Increased padding */
            border: none;
            border-radius: 10px;
            background-color: #333;
            color: #fff;
        }
        .form-row.full-width input, .form-row.full-width select {
            width: calc(100% - 30px); /* Adjusted width */
        }
        .form-row input[type="checkbox"] {
            flex: 0;
            margin-right: 20px;
        }
        
        input[type="submit"] {
            width: 100%;
            padding: 15px; /* Increased padding */
            margin: 20px 0; /* Increased spacing */
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
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

        .form-row.full-width {
            flex-direction: column;
        }
        .form-row.full-width input, .form-row.full-width select {
            width: calc(100% - 30px); /* Adjusted width */
        }
        .form-row.checkbox-row {
            align-items: center;
            justify-content: center; /* Center align the checkbox row */
        }
        .form-row.checkbox-row label {
            flex: none;
            margin-right: 10px;
        }

        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .popup button {
            margin-top: 20px;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }
        .popup button:hover {
            background-color: #0056b3;
        }
        .popup ul {
            list-style-type: none;
            padding: 0;
        }
        .popup ul li {
            margin: 10px 0;
            cursor: pointer;
        }
        .popup ul li:hover {
            text-decoration: underline;
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
    <script>
        function validateForm() {
            let isValid = true;
            let errorMessage = "";

            // Password validation
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm_password").value;
            if (password.length < 8) {
                errorMessage += "Password must be at least 8 characters long.<br>";
                isValid = false;
            }
            if (!/[0-9]/.test(password)) {
                errorMessage += "Password must contain at least one number.<br>";
                isValid = false;
            }
            if (!/[a-zA-Z]/.test(password)) {
                errorMessage += "Password must contain at least one letter.<br>";
                isValid = false;
            }
            if (!/[!@#$%^&*(),.?":{}|<>]/.test(password)) {
                errorMessage += "Password must contain at least one symbol.<br>";
                isValid = false;
            }
            if (password !== confirmPassword) {
                errorMessage += "Passwords do not match.<br>";
                isValid = false;
            }

            // Birthday validation
            const birthday = document.getElementById("birthday").value;
            if (!birthday) {
                errorMessage += "Please enter your birthday.<br>";
                isValid = false;
            }

            // First name and last name validation
            const firstName = document.getElementById("first_name").value;
            const lastName = document.getElementById("last_name").value;
            if (!/^[a-zA-Z-]+$/.test(firstName)) {
                errorMessage += "First name can only contain letters and hyphens.<br>";
                isValid = false;
            }
            if (!/^[a-zA-Z-]+$/.test(lastName)) {
                errorMessage += "Last name can only contain letters and hyphens.<br>";
                isValid = false;
            }

            // Display error message
            if (!isValid) {
                document.getElementById("error-message").innerHTML = errorMessage;
            }

            return isValid;
        }
    </script>
    
</head>
<body>

<div class="logo">
        <a href="index.php"><img src="images/logo.jpg" alt="TechFit Logo"></a>
    </div>
    <div class="container">
        <h2 style="margin-top: -25px;">Admin Register</h2> <!-- Move title up by 25px -->
        <div id="error-message" class="error-message">
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<p class="error-message">' . $_SESSION['error_message'] . '</p>';
            unset($_SESSION['error_message']);
        }
        ?>
        </div>
        <form action="admin_register.php?key=techfit" method="post" onsubmit="return validateForm()">
            <div class="form-row">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-row">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
            <div class="form-row">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <div class="form-row">
                <label for="birthday">Birthday:</label>
                <input type="date" id="birthday" name="birthday" required>
                <label for="gender">Gender:</label>
                <select id="gender" name="gender">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>
            
            <div class="form-row checkbox-row">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the Terms of Service and privacy policy</label>
            </div>
            <input type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="admin_login.php?key=techfit">Login here</a></p>
    </div>

</body>
</html>