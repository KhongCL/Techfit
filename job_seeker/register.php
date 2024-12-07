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
    $user_id = generateNextId($conn, 'User', 'user_id', 'U');
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];
    $job_position_interested = $_POST['job_position_interested'];

    // Check for duplicate username or email
    $check_sql = "SELECT * FROM User WHERE username='$username' OR email='$email'";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Username or email already exists.";
        header("Location: register.php");
        exit();
    }

    $sql = "INSERT INTO User (user_id, username, email, password, birthday, gender, role, is_active)
            VALUES ('$user_id', '$username', '$email', '$password', '$birthday', '$gender', '$role', TRUE)";

    if ($conn->query($sql) === TRUE) {
        if ($role == 'Job Seeker') {
            $job_seeker_id = generateNextId($conn, 'Job_Seeker', 'job_seeker_id', 'J');
            $sql = "INSERT INTO Job_Seeker (job_seeker_id, user_id, job_position_interested)
                    VALUES ('$job_seeker_id', '$user_id', '$job_position_interested')";
        } else if ($role == 'Employer') {
            $employer_id = generateNextId($conn, 'Employer', 'employer_id', 'E');
            $sql = "INSERT INTO Employer (employer_id, user_id, job_position_interested)
                    VALUES ('$employer_id', '$user_id', '$job_position_interested')";
        }

        if ($conn->query($sql) === TRUE) {
            header("Location: login.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Error: " . $conn->error;
            header("Location: register.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Error: " . $conn->error;
        header("Location: register.php");
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
    <title>Register - TechFit</title>
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
            top: 10px;
            left: 20px;
        }
        .logo img {
            height: 50px;
        }

        h2 {
            margin-top: 0;
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
        
    </style>
</head>
<body>
    <div class="logo">
        <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
    </div>
    <div class="container">
        <h2>Register</h2>

        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<p class="error-message">' . $_SESSION['error_message'] . '</p>';
            unset($_SESSION['error_message']);
        }
        ?>

        <form action="register.php" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required><br>

            <label for="birthday">Birthday:</label>
            <input type="date" id="birthday" name="birthday"><br>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender">
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select><br>

            <label for="role">Role:</label>
            <select id="role" name="role">
                <option value="Job Seeker">Job Seeker</option>
                <option value="Employer">Employer</option>
            </select><br>

            <label for="job_position_interested">Job Position Interested:</label>
            <input type="text" id="job_position_interested" name="job_position_interested"><br>

            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">I agree to the terms and conditions and privacy policy</label><br>

            <input type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>