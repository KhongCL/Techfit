<?php
session_start();

// Check if the URL is accessed as shared
$is_shared = isset($_GET['shared']) && $_GET['shared'] === 'true';


// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Validate and get assessment ID from query parameter
if (!isset($_GET['assessment_id']) || empty($_GET['assessment_id'])) {
    die("Invalid assessment ID.");
}

$assessment_id = $_GET['assessment_id'];

// Fetch assessment details
$sql = "
    SELECT 
        Assessment_Job_Seeker.assessment_id, 
        Assessment_Job_Seeker.job_seeker_id, 
        Assessment_Job_Seeker.start_time, 
        Assessment_Job_Seeker.end_time, 
        Assessment_Job_Seeker.score
    FROM Assessment_Job_Seeker
    WHERE assessment_id = ?
";

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
    <title>Assessment History Report</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 100vh;
        margin: 0;
        background-color: #f9f9f9;
    }

    .report-card {
        background-color: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        width: 400px;
        text-align: left;
        margin-top: 10vh;
    }

    .report-card h2 {
        text-align: center;
        margin-bottom: 20px;
    }

    .report-row {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
    }

    .report-label {
        font-weight: bold;
    }

    .share-btn {
        margin-top: 20px;
        display: block;
        background-color: #007bff;
        color: white;
        text-align: center;
        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
    }

    .share-btn:hover {
        background-color: #0056b3;
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
    <div class="report-card">
        <h2>Assessment History Report</h2>

        <div class="report-row">
            <span class="report-label">Assessment ID:</span>
            <span><?= htmlspecialchars($assessment['assessment_id']) ?></span>
        </div>
        <div class="report-row">
            <span class="report-label">Job Seeker ID:</span>
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

        <?php if (!$is_shared): ?>
            <button class="share-btn" onclick="copyToClipboard()">Share</button>
        <?php endif; ?>

    </div>
</body>
</html>
