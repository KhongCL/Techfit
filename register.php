<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function generateNextId($conn, $table, $column, $prefix) {
    $sql = "SELECT MAX(CAST(SUBSTRING($column, LENGTH('$prefix') + 1) AS UNSIGNED)) AS max_id FROM $table WHERE $column LIKE '$prefix%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ? $row['max_id'] : 0;
    $next_id = $prefix . str_pad($max_id + 1, 2, '0', STR_PAD_LEFT);
    return $next_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $_SESSION['entered_username'] = $_POST['username'];
    $_SESSION['entered_first_name'] = $_POST['first_name'];
    $_SESSION['entered_last_name'] = $_POST['last_name'];
    $_SESSION['entered_email'] = $_POST['email'];
    $_SESSION['entered_birthday'] = $_POST['birthday'];
    $_SESSION['entered_gender'] = $_POST['gender'];
    $_SESSION['entered_role'] = $_POST['role'];
    $_SESSION['entered_job_position'] = isset($_POST['job_position_interested']) ? $_POST['job_position_interested'] : '';
    
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $check_sql = "SELECT * FROM User WHERE username='{$_POST['username']}' OR email='{$_POST['email']}'";
    $check_result = $conn->query($check_sql);
    if ($check_result->num_rows > 0) {
        $_SESSION['error_message'] = "Username or email already exists.";
        header("Location: register.php");
        exit();
    }

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
            align-items: flex-start; 
            height: 100vh;
            margin: 0;
            padding: 20px;
            box-sizing: border-box;
            overflow: auto; 
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
            margin-top: -25px;
        }

        .container {
            background-color: #1e1e1e;
            padding: 60px;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 900px;
            text-align: center;
            box-sizing: border-box;
            overflow: auto;
        }
        .form-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 35px; 
            flex-wrap: wrap; 
        }
        .form-row label {
            flex: 1;
            margin-right: 10px; 
        }
        .form-row input, .form-row select {
            flex: 1;
            padding: 15px; 
            border: none;
            border-radius: 10px;
            background-color: #333;
            color: #fff;
            margin-bottom: 10px;
        }
        .form-row.full-width input, .form-row.full-width select {
            width: calc(100% - 30px); 
        }
        .form-row input[type="checkbox"] {
            flex: 0;
            margin-right: 20px;
        }
        input[type="submit"] {
            width: 100%;
            padding: 15px;
            margin: 20px 0; 
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
            width: calc(100% - 30px);
        }
        .form-row.checkbox-row {
            align-items: center;
            justify-content: center;
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
            h2 {
            margin-top: 10px;
            }

            .container {
            margin-top : 30px;
            padding: 20px;
            overflow: auto; 
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
            padding: 10px;
            width: calc(100% - 20px);
            }
            #gender, #role {
                width: 100%;    
            }
            .form-row input[type="checkbox"] {
            margin-right: 10px;
            }
            input[type="submit"] {
            width: 100%;
            padding: 15px;
            margin: 20px 0; 
            }
        }

        body {
            padding-top: 50px;
        }

    </style>
    <script>
        function validateForm() {
            let isValid = true;
            let errorMessage = "";

            const username = document.getElementById("username")?.value;
            if (username && !/^[a-zA-Z0-9_]{5,20}$/.test(username)) {
                errorMessage += "Username must be 5-20 characters and contain only letters, numbers, and underscores.<br>";
                isValid = false;
            }

            const email = document.getElementById("email")?.value;
            if (email && !/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(email)) {
                errorMessage += "Please enter a valid email address.<br>";
                isValid = false;
            }

            const password = document.getElementById("password")?.value;
            const confirmPassword = document.getElementById("confirm_password")?.value;
            if (password) {
                const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                if (!passwordRegex.test(password)) {
                    errorMessage += "Password must:<br>" +
                        "- Be at least 8 characters long<br>" +
                        "- Contain at least one letter<br>" +
                        "- Contain at least one number<br>" +
                        "- Contain at least one special character (@$!%*?&)<br>";
                    isValid = false;
                }
                if (confirmPassword && password !== confirmPassword) {
                    errorMessage += "Passwords do not match.<br>";
                    isValid = false;
                }
            }

            const nameRegex = /^[a-zA-Z-]+$/;
            const firstName = document.getElementById("first_name")?.value;
            const lastName = document.getElementById("last_name")?.value;
            
            if (firstName && !nameRegex.test(firstName)) {
                errorMessage += "First name can only contain letters and hyphens.<br>";
                isValid = false;
            }
            if (lastName && !nameRegex.test(lastName)) {
                errorMessage += "Last name can only contain letters and hyphens.<br>";
                isValid = false;
            }

            const birthday = document.getElementById("birthday")?.value;
            if (birthday) {
                const birthDate = new Date(birthday);
                const today = new Date();
                
                if (birthDate >= today) {
                    errorMessage += "Birthday cannot be in the future.<br>";
                    isValid = false;
                }

                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                if (age < 16) {
                    errorMessage += "Must be at least 16 years old.<br>";
                    isValid = false;
                }
            }

            const role = document.getElementById("role")?.value;
            const jobPosition = document.getElementById("job_position_interested")?.value;
            if (role && (jobPosition === 'Select' || !jobPosition)) {
                errorMessage += "Please select at least one job position.<br>";
                isValid = false;
            }

            const terms = document.getElementById("terms")?.checked;
            if (!terms) {
                errorMessage += "You must agree to the Terms of Service and Privacy Policy.<br>";
                isValid = false;
            }

            const errorDiv = document.getElementById("error-message");
            if (!isValid && errorDiv) {
                errorDiv.innerHTML = errorMessage;
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
        <h2>Register</h2>
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