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

$sql = "SELECT 
        js.job_seeker_id,
        CONCAT(u.first_name, ' ', u.last_name) as name,
        js.education_level,
        js.year_of_experience,
        GROUP_CONCAT(
            CASE 
                WHEN ajs.score IS NULL AND ajs.end_time IS NOT NULL THEN '0'
                WHEN ajs.score IS NOT NULL THEN CAST(ajs.score AS CHAR)
                ELSE 'N/A'
            END 
            ORDER BY ajs.result_id SEPARATOR ', '
        ) AS assessment_scores
        FROM Job_Seeker js
        JOIN User u ON js.user_id = u.user_id
        JOIN Assessment_Job_Seeker ajs ON js.job_seeker_id = ajs.job_seeker_id
        JOIN Employer_Interest ei ON js.job_seeker_id = ei.job_seeker_id
        WHERE ei.employer_id = ? 
        AND ei.is_active = 0
        AND ajs.end_time IS NOT NULL 
        GROUP BY js.job_seeker_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $_SESSION['employer_id']);
$stmt->execute();
$result = $stmt->get_result();

$deletedCandidates = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $deletedCandidates[] = [
            'job_seeker_id' => $row['job_seeker_id'],
            'name' => htmlspecialchars($row['name']),
            'education_level' => !empty($row['education_level']) ? htmlspecialchars($row['education_level']) : 'N/A',
            'year_of_experience' => (!empty($row['year_of_experience']) || $row['year_of_experience'] === '0') ? htmlspecialchars($row['year_of_experience']) : 'N/A',
            'assessment_scores' => ($row['assessment_scores'] !== null && $row['assessment_scores'] !== '') ? 
                str_replace(['null', 'NULL'], '0', htmlspecialchars($row['assessment_scores'])) : '0'
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($deletedCandidates);
} else {
    header('Content-Type: application/json');
    echo json_encode(['noCandidates' => true]);
}

$conn->close();
?>