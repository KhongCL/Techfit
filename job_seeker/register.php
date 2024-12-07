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