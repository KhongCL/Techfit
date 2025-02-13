<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['employer_id'])) {
    die("Employer not logged in.");
}

$employer_id = $_SESSION['employer_id'];

$sql = "SELECT js.job_seeker_id, u.first_name, u.last_name, js.education_level, js.year_of_experience, 
            GROUP_CONCAT(ajs.score ORDER BY ajs.assessment_id SEPARATOR ', ') AS scores
        FROM Employer_Interest ei
        JOIN Job_Seeker js ON ei.job_seeker_id = js.job_seeker_id
        JOIN User u ON js.user_id = u.user_id
        LEFT JOIN Assessment_Job_Seeker_Old ajs ON js.job_seeker_id = ajs.job_seeker_id
        WHERE ei.employer_id = '$employer_id' AND ei.is_active = 0
        GROUP BY js.job_seeker_id";

$result = $conn->query($sql);

$deletedCandidates = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $deletedCandidates[] = [
            'job_seeker_id' => $row['job_seeker_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
            'education_level' => $row['education_level'],
            'years_of_experience' => $row['year_of_experience'],
            'assessment_scores' => $row['scores']
        ];
    }
}

echo json_encode($deletedCandidates);

$conn->close();
?>