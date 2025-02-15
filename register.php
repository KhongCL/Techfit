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
    // Store input values in session
    $_SESSION['entered_username'] = $_POST['username'];
    $_SESSION['entered_first_name'] = $_POST['first_name'];
    $_SESSION['entered_last_name'] = $_POST['last_name'];
    $_SESSION['entered_email'] = $_POST['email'];
    $_SESSION['entered_birthday'] = $_POST['birthday'];
    $_SESSION['entered_gender'] = $_POST['gender'];
    $_SESSION['entered_role'] = $_POST['role'];
    $_SESSION['entered_job_position'] = isset($_POST['job_position_interested']) ? $_POST['job_position_interested'] : '';
    
    // Hash password but don't store it in session for security reasons
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check for duplicate username or email
    $check_sql = "SELECT * FROM User WHERE username='{$_POST['username']}' OR email='{$_POST['email']}'";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Username or email already exists.";
        header("Location: register.php");
        exit();
    }

    // Insert user data into the database
    $user_id = generateNextId($conn, 'User', 'user_id', 'U');
    $sql = "INSERT INTO User (user_id, username, first_name, last_name, email, password, birthday, gender, role, is_active)
            VALUES ('$user_id', '{$_POST['username']}', '{$_POST['first_name']}', '{$_POST['last_name']}', '{$_POST['email']}', '$password', '{$_POST['birthday']}', '{$_POST['gender']}', '{$_POST['role']}', TRUE)";

    if ($conn->query($sql) === TRUE) {
        if ($_POST['role'] == 'Job Seeker') {
            $job_seeker_id = generateNextId($conn, 'Job_Seeker', 'job_seeker_id', 'J');
            $sql = "INSERT INTO Job_Seeker (job_seeker_id, user_id, job_position_interested)
                    VALUES ('$job_seeker_id', '$user_id', '{$_POST['job_position_interested']}')";
        } else if ($_POST['role'] == 'Employer') {
            $employer_id = generateNextId($conn, 'Employer', 'employer_id', 'E');
            $sql = "INSERT INTO Employer (employer_id, user_id, job_position_interested)
                    VALUES ('$employer_id', '$user_id', '{$_POST['job_position_interested']}')";
        }

        if ($conn->query($sql) === TRUE) {
            unset($_SESSION['entered_password']);
            unset($_SESSION['entered_confirm_password']);
            session_unset();
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
    <script src="scripts.js?v=1.0"></script>
    <style>
        body {
            background-color: #121212;
            color: #ffffff;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align items from the top */
            height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            overflow: auto; /* Enable scrolling */
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
            width: 100%;
            max-width: 900px; /* Set max width */
            text-align: center;
            box-sizing: border-box;
            overflow: auto; /* Enable scrolling */
        }
        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 35px; /* Increased spacing */
            flex-wrap: wrap; /* Allow wrapping */
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
            margin-bottom: 10px; /* Add margin for spacing */
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

        @media (max-width: 850px) {
            .container {
                padding: 20px;
                overflow: auto; /* Enable scrolling */
            }
            .form-row {
                flex-direction: column;
                align-items: flex-start;
            }
            .form-row label, .form-row input, .form-row select {
                width: 100%;
                margin-bottom: 10px;
            }
            .form-row input, .form-row select {
                padding: 10px; /* Reduce padding */
            }
            .form-row input[type="checkbox"] {
                margin-right: 10px;
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

        function openPopup() {
            document.getElementById("popup").style.display = "block";
        }

        function closePopup() {
            document.getElementById("popup").style.display = "none";
        }

        function updateJobPositions() {
            var checkboxes = document.querySelectorAll('#popup input[type="checkbox"]:checked');
            var selectedPositions = [];
            checkboxes.forEach(function(checkbox) {
                selectedPositions.push(checkbox.value);
            });
            document.getElementById("job_position_interested").value = selectedPositions.join(', ');
        }
    </script>
</head>
<body>
    <div class="logo">
        <a href="index.php"><img src="images/logo.jpg" alt="TechFit Logo"></a>
    </div>
    <div class="container">
        <h2 style="margin-top: -25px;">Register</h2> <!-- Move title up by 25px -->
        <div id="error-message" class="error-message">
            <?php
                if (isset($_SESSION['error_message'])) {
                    echo $_SESSION['error_message'];
                    unset($_SESSION['error_message']);
                }
                ?>
        </div>
            <form action="register.php" method="post" onsubmit="return validateForm()">
        <div class="form-row">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" 
                value="<?php echo isset($_SESSION['entered_username']) ? htmlspecialchars($_SESSION['entered_username']) : ''; ?>" required>
            
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" 
                value="<?php echo isset($_SESSION['entered_email']) ? htmlspecialchars($_SESSION['entered_email']) : ''; ?>" required>
        </div>

        <div class="form-row">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" 
                value="<?php echo isset($_SESSION['entered_first_name']) ? htmlspecialchars($_SESSION['entered_first_name']) : ''; ?>" required>
            
            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" 
                value="<?php echo isset($_SESSION['entered_last_name']) ? htmlspecialchars($_SESSION['entered_last_name']) : ''; ?>" required>
        </div>

        <div class="form-row">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>

        <div class="form-row">
            <label for="birthday">Birthday:</label>
            <input type="date" id="birthday" name="birthday"
                value="<?php echo isset($_SESSION['entered_birthday']) ? htmlspecialchars($_SESSION['entered_birthday']) : ''; ?>" required>
            
            <label for="gender">Gender:</label>
            <select id="gender" name="gender">
                <option value="Male" <?php echo (isset($_SESSION['entered_gender']) && $_SESSION['entered_gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
                <option value="Female" <?php echo (isset($_SESSION['entered_gender']) && $_SESSION['entered_gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
            </select>
        </div>

        <div class="form-row">
            <label for="role">Role:</label>
            <select id="role" name="role">
                <option value="Job Seeker" <?php echo (isset($_SESSION['entered_role']) && $_SESSION['entered_role'] == 'Job Seeker') ? 'selected' : ''; ?>>Job Seeker</option>
                <option value="Employer" <?php echo (isset($_SESSION['entered_role']) && $_SESSION['entered_role'] == 'Employer') ? 'selected' : ''; ?>>Employer</option>
            </select>

            <label for="job_position_interested">Job Position Interested:</label>
            <input type="text" id="job_position_interested" name="job_position_interested" readonly onclick="openPopup()" 
                value="<?php echo isset($_SESSION['entered_job_position']) ? htmlspecialchars($_SESSION['entered_job_position']) : 'Select'; ?>">
        </div>

        <div class="form-row checkbox-row">
            <input type="checkbox" id="terms" name="terms" required>
            <label for="terms">I agree to the Terms of Service and privacy policy</label>
        </div>

        <input type="submit" value="Register">
    </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

    <div id="popup" class="popup">
        <h3>Select Job Position</h3>
        <ul>
            <li><input type="checkbox" value="Software Developer/Engineer" onclick="updateJobPositions()"> Software Developer/Engineer</li>
            <li><input type="checkbox" value="Full-Stack Developer" onclick="updateJobPositions()"> Full-Stack Developer</li>
            <li><input type="checkbox" value="Data Scientist" onclick="updateJobPositions()"> Data Scientist</li>
            <li><input type="checkbox" value="DevOps Engineer" onclick="updateJobPositions()"> DevOps Engineer</li>
            <li><input type="checkbox" value="Cybersecurity Analyst" onclick="updateJobPositions()"> Cybersecurity Analyst</li>
            <li><input type="checkbox" value="Cloud Engineer" onclick="updateJobPositions()"> Cloud Engineer</li>
            <li><input type="checkbox" value="UI/UX Designer" onclick="updateJobPositions()"> UI/UX Designer</li>
            <li><input type="checkbox" value="IT Support Specialist" onclick="updateJobPositions()"> IT Support Specialist</li>
            <li><input type="checkbox" value="Machine Learning Engineer" onclick="updateJobPositions()"> Machine Learning Engineer</li>
            <li><input type="checkbox" value="QA Analyst" onclick="updateJobPositions()"> QA Analyst</li>
            <li><input type="checkbox" value="Others" onclick="updateJobPositions()"> Others</li>
        </ul>
        <button onclick="closePopup()">Close</button>
    </div>
</body>
</html>