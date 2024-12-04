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