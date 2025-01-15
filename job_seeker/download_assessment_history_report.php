

<!-- Note: This file is used to download the assessment history report of a job seeker. 
This file does not have an interface, it is used to generate and download the report.
-->


<?php
session_start();
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assessment_id = $_GET['assessment_id']; // Get assessment ID from request

// Fetch assessment details
$sql = "
    SELECT assessment_id, job_seeker_id, start_time, end_time, score 
    FROM Assessment_Job_Seeker 
    WHERE assessment_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Invalid Assessment ID.";
    exit();
}

$row = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Generate report content
$reportContent = "
Assessment History Report
-------------------------
Assessment ID: {$row['assessment_id']}
Job Seeker ID: {$row['job_seeker_id']}
Start Time: {$row['start_time']}
End Time: {$row['end_time']}
Score: {$row['score']}
";

// Serve the file as a download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="assessment_report_' . $row['assessment_id'] . '.txt"');
echo $reportContent;
exit();
?>
