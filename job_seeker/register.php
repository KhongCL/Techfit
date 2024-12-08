<?php
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = uniqid();
    $username = $_POST['username'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $birthday = $_POST['birthday'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];
    $job_position_interested = $_POST['job_position_interested'];

    $sql = "INSERT INTO User (user_id, username, first_name, last_name, email, password, birthday, gender, role, is_active)
            VALUES ('$user_id', '$username', '$first_name', '$last_name', '$email', '$password', '$birthday', '$gender', '$role', TRUE)";

    if ($conn->query($sql) === TRUE) {
        if ($role == 'Job Seeker') {
            $sql = "INSERT INTO Job_Seeker (job_seeker_id, user_id, job_position_interested)
                    VALUES ('$user_id', '$user_id', '$job_position_interested')";
        } else if ($role == 'Employer') {
            $sql = "INSERT INTO Employer (employer_id, user_id, job_position_interested)
                    VALUES ('$user_id', '$user_id', '$job_position_interested')";
        }

        if ($conn->query($sql) === TRUE) {
            echo "Registration successful!";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
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
            margin-bottom: 20px;
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
        <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
    </div>
    <div class="container">
        <h2 style="margin-top: -25px;">Register</h2> <!-- Move title up by 25px -->
        <div id="error-message" class="error-message"></div>
        <form action="register.php" method="post" onsubmit="return validateForm()">
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
            <div class="form-row">
                <label for="role">Role:</label>
                <select id="role" name="role">
                    <option value="Job Seeker">Job Seeker</option>
                    <option value="Employer">Employer</option>
                </select>
                <label for="job_position_interested">Job Position Interested:</label>
                <input type="text" id="job_position_interested" name="job_position_interested">
            </div>
            <div class="form-row checkbox-row">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">I agree to the terms and conditions and privacy policy</label>
            </div>
            <input type="submit" value="Register">
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>