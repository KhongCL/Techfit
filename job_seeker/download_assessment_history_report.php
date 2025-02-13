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

$assessment_id = $_GET['assessment_id'];

// Fetch assessment details including section scores
$sql = "WITH SectionScores AS (
    SELECT 
        a.job_seeker_id,
        q.assessment_id,
        ROUND(
            AVG(CASE 
                WHEN a.is_correct = 1 THEN 100
                WHEN a.score_percentage IS NOT NULL THEN a.score_percentage
                ELSE 0
            END), 1
        ) as section_score
    FROM Answer a
    JOIN Question q ON a.question_id = q.question_id
    WHERE q.assessment_id IN ('AS76', 'AS77', 'AS78', 'AS79', 'AS80')
    GROUP BY a.job_seeker_id, q.assessment_id
)
SELECT 
    ajs.result_id as assessment_id,
    ajs.job_seeker_id,
    ajs.start_time,
    ajs.end_time,
    ajs.score,
    asts.passing_score_percentage,
    TIMESTAMPDIFF(SECOND, ajs.start_time, ajs.end_time) as duration,
    GROUP_CONCAT(
        CONCAT(
            CASE 
                WHEN ss.assessment_id = 'AS76' THEN 'Scenario-Based Questions'
                WHEN ss.assessment_id = 'AS77' THEN 'Python Programming'
                WHEN ss.assessment_id = 'AS78' THEN 'Java Programming'
                WHEN ss.assessment_id = 'AS79' THEN 'JavaScript Programming'
                WHEN ss.assessment_id = 'AS80' THEN 'C++ Programming'
            END,
            ': ',
            ss.section_score,
            '%'
        ) ORDER BY ss.assessment_id
    ) as section_scores
FROM Assessment_Job_Seeker ajs
JOIN Assessment_Settings asts ON asts.setting_id = '1'
LEFT JOIN SectionScores ss ON ss.job_seeker_id = ajs.job_seeker_id
WHERE ajs.result_id = ?
GROUP BY 
    ajs.result_id,
    ajs.job_seeker_id,
    ajs.start_time,
    ajs.end_time,
    ajs.score,
    asts.passing_score_percentage";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $assessment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Invalid Assessment ID.";
    exit();
}

$row = $result->fetch_assoc();

// Calculate minutes and seconds
$minutes = floor($row['duration'] / 60);
$seconds = $row['duration'] % 60;

// Determine pass/fail status
$passed = $row['score'] >= $row['passing_score_percentage'];
$status = $passed ? "PASSED" : "FAILED";

$section_scores = explode(',', $row['section_scores']);
$formatted_scores = implode("\n", $section_scores);

$reportContent = "
Assessment History Report
-------------------------
Status: {$status}

Assessment ID: {$row['assessment_id']}
Job Seeker ID: {$row['job_seeker_id']}
Score: {$row['score']}%
Passing Score: {$row['passing_score_percentage']}%
Time Spent: {$minutes} minutes {$seconds} seconds

Section Scores:
{$formatted_scores}
";

// Serve the file as a download
header('Content-Type: text/plain');
header('Content-Disposition: attachment; filename="assessment_report_' . $row['assessment_id'] . '.txt"');
echo $reportContent;
exit();
?>