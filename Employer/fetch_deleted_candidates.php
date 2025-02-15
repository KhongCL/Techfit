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

$sql = "SELECT js.user_id, u.first_name, u.last_name, js.education_level, js.year_of_experience, js.job_seeker_id,
                GROUP_CONCAT(ajs.score ORDER BY ajs.result_id SEPARATOR ', ') AS scores
            FROM Employer_Interest ei
            JOIN Job_Seeker js ON ei.job_seeker_id = js.job_seeker_id
            JOIN User u ON js.user_id = u.user_id
            LEFT JOIN Assessment_Job_Seeker ajs ON js.job_seeker_id = ajs.job_seeker_id
            WHERE ei.employer_id = '$employer_id' AND ei.is_active = 0
            GROUP BY js.job_seeker_id";

$result = $conn->query($sql);

$deletedCandidates = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        
        $education_level = (isset($row['education_level']) && $row['education_level'] !== null && $row['education_level'] !== '') ? htmlspecialchars($row['education_level']) : 'N/A';

        
        $experience = (isset($row['year_of_experience']) && $row['year_of_experience'] !== null) ? htmlspecialchars($row['year_of_experience']) : 'N/A';

        
        $scores_display = !empty($row['scores']) ? htmlspecialchars($row['scores']) : 'N/A';

        $deletedCandidates[] = [
            'job_seeker_id' => $row['job_seeker_id'],
            'name' => htmlspecialchars($row['first_name'] . ' ' . $row['last_name']),
            'education_level' => $education_level,
            'years_of_experience' => $experience,
            'assessment_scores' => $scores_display
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($deletedCandidates);

} else {
    
    header('Content-Type: application/json');
    echo json_encode(["noCandidates" => true]);
}

$conn->close();
?>
