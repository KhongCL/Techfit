<?php
session_start();


$is_shared = isset($_GET['shared']) && $_GET['shared'] === 'true';



$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


function displayLoginMessage() {
    echo '<script>
        if (confirm("You need to log in to access this page. Go to Login Page? Click cancel to go to home page.")) {
            window.location.href = "../login.php";
        } else {
            window.location.href = "../index.php";
        }
    </script>';
    exit();
}

function displayErrorMessage() {
    echo '<script>
        if (confirm("You need to access this page from assessment summay. Go to Assessment Summary? Click cancel to go to home page.")) {
            window.location.href = "./assessment_summary.php";
        } else {
            window.location.href = "./index.php";
        }
    </script>';
    exit();
}

if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); 
}


if ($_SESSION['role'] !== 'Job Seeker') {
    displayLoginMessage(); 
}


if (!isset($_SESSION['job_seeker_id'])) {
    displayLoginMessage(); 
}

if (!isset($_GET['assessment_id']) || trim($_GET['assessment_id']) === '') {
    displayErrorMessage();
}

$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'assessment_summary.php') === false) {
    displayErrorMessage();
}

session_write_close();

$assessment_id = $_GET['assessment_id'];


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

$assessment = $result->fetch_assoc();
if (!$assessment) {
    die("Assessment not found.");
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment History Report - TechFit</title>
    <style>
        :root {
            --primary-color: #007bff;
            --accent-color: #5c7dff; 
            --danger-color: #e74c3c; 
            --danger-color-hover: #c0392b;
            --success-color: #28a745;
            --success-color-hover: #2ecc71;
            --background-color: #121212;
            --background-color-medium: #1E1E1E;
            --background-color-light: #444;
            --text-color: #fafafa;
            --text-color-dark: #b0b0b0;
            --button-color: #007bff;
            --button-color-hover: #3c87e3;
        }

        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            margin: 0;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .report-card {
            background-color: var(--background-color-medium);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
            width: 400px;
            text-align: left;
            margin-top: 10vh;
        }

        .report-card h2 {
            text-align: center;
            margin-bottom: 20px;
            color: var(--text-color);
        }

        .status {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }

        .passed { color: var(--success-color); }
        .failed { color: var(--danger-color); }

        .report-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--background-color-light);
        }

        .report-label {
            font-weight: bold;
            color: var(--text-color-dark);
        }

        .share-btn {
            margin-top: 20px;
            display: block;
            background-color: var(--button-color);
            color: var(--text-color);
            text-align: center;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        .share-btn:hover {
            background-color: var(--button-color-hover);
        }

        .section-scores {
            margin-top: 20px;
        }

        .section-score {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }

        .time-spent {
            background-color: var(--background-color-light);
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            text-align: center;
        }

        .section-scores h3 {
            color: var(--text-color);
            margin-bottom: 15px;
            padding-bottom: 5px;
            border-bottom: 1px solid var(--background-color-light);
        }

        .back-arrow {
            display: block; 
            padding: 0;
            background-color: transparent;
            color: var(--primary-color);
            text-decoration: none;
            border-radius: 0;
            margin-bottom: 15px;
            transition: none;
            font-size: 3em;
            line-height: 2;
        }

        .back-arrow:hover {
            color: var(--primary-color-hover);
            background-color: transparent;
            color: darkblue;
        }

        .summary_header {
            width: 100%;      
            display: block;  
            overflow: hidden;
        }
    </style>
    <script>
        function copyToClipboard() {
            const url = new URL(window.location.href);
            url.searchParams.set('shared', 'true');
            navigator.clipboard.writeText(url.toString()).then(() => {
                alert('URL copied to clipboard!');
            }).catch(err => {
                alert('Failed to copy URL');
            });
        }
    </script>
</head>
<body>
    <a href="./assessment_summary.php" class="back-arrow">&#8592;</a>
    <div class="report-card">
        <h2>Assessment Summary Report</h2>
        
        <div class="status <?= $assessment['score'] >= $assessment['passing_score_percentage'] ? 'passed' : 'failed' ?>">
            <?= $assessment['score'] >= $assessment['passing_score_percentage'] ? 'PASSED' : 'FAILED' ?>
        </div>

        <div class="report-row">
            <span class="report-label">Assessment ID:</span>
            <span><?= htmlspecialchars($assessment['assessment_id']) ?></span>
        </div>
        <div class="report-row">
            <spna class="report-label">Job Seeker ID:</spna>
            <span><?= htmlspecialchars($assessment['job_seeker_id']) ?></span>
        </div>
        <div class="report-row">
            <span class="report-label">Start Time:</span>
            <span><?= htmlspecialchars($assessment['start_time']) ?></span>
        </div>
        <div class="report-row">
            <span class="report-label">End Time:</span>
            <span><?= htmlspecialchars($assessment['end_time']) ?></span>
        </div>
        <div class="report-row">
            <span class="report-label">Score:</span>
            <span><?= htmlspecialchars($assessment['score']) ?></span>
        </div>

        <div class="report-row">
            <span class="report-label">Passing Score:</span>
            <span><?= htmlspecialchars($assessment['passing_score_percentage']) ?>%</span>
        </div>
        <div class="report-row">
            <span class="report-label">Time Spent:</span>
            <span>
                <?php
                $minutes = floor($assessment['duration'] / 60);
                $seconds = $assessment['duration'] % 60;
                echo "{$minutes} minutes {$seconds} seconds";
                ?>
            </span>
        </div>
        
        <div class="section-scores">
            <h3>Section Scores</h3>
            <?php 
            $scores = array_filter(explode(',', $assessment['section_scores']));
            foreach ($scores as $score): 
                list($name, $value) = explode(':', $score);
                ?>
                <div class="section-score">
                    <span><?= trim($name) ?></span>
                    <span><?= trim($value) ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!$is_shared): ?>
            <button class="share-btn" onclick="copyToClipboard()">Share</button>
        <?php endif; ?>
    </div>
</body>
</html>
