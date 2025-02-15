<?php
session_start(); 


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


if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); 
}


if ($_SESSION['role'] !== 'Job Seeker') {
    displayLoginMessage(); 
}


if (!isset($_SESSION['job_seeker_id'])) {
    displayLoginMessage(); 
}


session_write_close();
?>

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

$user_id = $_SESSION['user_id']; 


$sql = "
    SELECT 
        Assessment_Job_Seeker.result_id AS 'assessment_id', 
        Assessment_Job_Seeker.job_seeker_id AS 'job_id',
        Assessment_Job_Seeker.start_time AS 'start_time',
        Assessment_Job_Seeker.end_time AS 'end_time',
        Assessment_Job_Seeker.score AS 'score',
        Assessment_Settings.passing_score_percentage
    FROM Assessment_Job_Seeker
    INNER JOIN Job_Seeker 
        ON Assessment_Job_Seeker.job_seeker_id = Job_Seeker.job_seeker_id
    INNER JOIN User
        ON Job_Seeker.user_id = User.user_id
    INNER JOIN Assessment_Settings
        ON Assessment_Settings.setting_id = '1'
    WHERE User.user_id = ?
    AND Assessment_Job_Seeker.end_time IS NOT NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$assessments = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $assessments[] = $row;
    }
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Summary - TechFit</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        :root {
            --primary-color: #007bff;
            --accent-color: #5c7dff; 
            --danger-color: #e74c3c; 
            --danger-color-hover: #c0392b;
            --success-color: #28a745;
            --success-color-hover: #2ecc71;

            --background-color: #121212;
            --background-color-medium: #080808;
            --background-color-medium: #1E1E1E;
            --background-color-light: #444;
            --background-color-extra-light: #555;
            --background-color-hover: #666;
            
            --text-color: #fafafa;
            --text-color-dark: #b0b0b0;
            --text-color-medium: #e0e0e0;
            --text-color-light: #f7f7f7;
            --text-color-extra-light: #ffffff;
            --text-color-hover: #b0b0b0;
            
            --button-color: #007bff;
            --button-color-hover: #3c87e3;
            --focus-border-color: #47a3e0;
            --disabled-color: #7f8c8d;
        }
        
        .actions {
            display: flex;
            gap: 10px;
        }

        .actions a {
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            color: var(--text-color);
            transition: background-color 0.3s ease;
        }

        .actions a[title="Download"] {
            background-color: var(--primary-color);
        }

        .actions a[title="Download"]:hover {
            background-color: var(--button-color-hover);
        }

        .actions a[title="Share"] {
            background-color: var(--success-color);
        }

        .actions a[title="Share"]:hover {
            background-color: var(--success-color-hover);
        }

        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            background-color: var(--background-color-light);
            margin-bottom: 15px;
            border-radius: 8px;
            width: 100%;
            box-sizing: border-box;
        }

        .date-time {
            flex: 2;
            text-align: left;
        }

        .date-time p {
            margin: 0;
            line-height: 1.5;
            color: var(--text-color);
        }

        .score {
            flex: 1;
            text-align: center;
        }

        .score h3 {
            margin: 0;
            color: var(--text-color);
        }

        .score p {
            font-size: 1.2em;
            margin: 5px 0 0;
            color: var(--text-color);
        }

        #assessment-history {
            min-height: calc(100vh - 250px);
            padding: 40px 0;
        }

        .container {
            max-width: 1400px;
            width: 50%;
            margin: 0 auto;
            padding: 0 20px;
            box-sizing: border-box;
        }

        .history-header {
            margin-bottom: 30px;
            text-align: left;
            color: var(--text-color);
        }

        .history-header h2 {
            font-size: 1.8em;
            margin: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--background-color-light);
        }

        .scrollable-container {
            max-height: calc(100vh - 350px);
            overflow-y: auto;
            padding-right: 10px;
            width: 100%;
        }

        .score-passed {
            color: var(--success-color) !important;
        }

        .score-failed {
            color: var(--danger-color) !important;
        }

        @media (max-width: 1024px) {
            .container {
                width: 70%;
            }

            .history-item {
                flex-wrap: wrap;
                gap: 15px;
            }

            .date-time {
                flex: 100%;
                order: 1;
            }

            .score {
                flex: 1;
                order: 2;
            }

            .actions {
                flex: 1;
                order: 3;
                justify-content: flex-end;
            }
        }

        @media (max-width: 768px) {
            .container {
                width: 90%;
                padding: 0 15px;
            }

            .history-header h2 {
                font-size: 1.5em;
            }

            .history-item {
                padding: 15px;
                flex-direction: column;
                align-items: flex-start;
            }

            .date-time, .score, .actions {
                width: 100%;
                text-align: left;
                margin-bottom: 10px;
            }

            .actions {
                justify-content: flex-start;
            }

            .actions a {
                padding: 6px 12px;
                font-size: 0.9em;
            }

            .score {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .score h3 {
                margin-right: 10px;
            }

            .scrollable-container {
                max-height: calc(100vh - 300px);
            }

            #assessment-history {
                padding: 20px 0;
            }

            .hamburger {
                display: flex;
            }

            .nav-list {
                transform: translateX(100%);
                transition: transform 0.3s ease;
            }

            .nav-list.active {
                transform: translateX(0);
            }
        }

        @media (max-width: 480px) {
            .container {
                width: 95%;
                padding: 0 10px;
            }

            .history-item {
                padding: 12px;
            }

            .date-time p {
                font-size: 0.9em;
            }

            .score h3 {
                font-size: 1em;
            }

            .score p {
                font-size: 1em;
            }

            .actions {
                flex-direction: column;
                gap: 5px;
            }

            .actions a {
                width: 100%;
                text-align: center;
            }
        }
    </style>

</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><img src="images/logo.jpg" alt="TechFit Logo"></a>
        </div>
        <nav>
            <div class="nav-container">
                <ul class="nav-list">
                    <li><a href="#">Assessment</a>
                        <ul class="dropdown">
                            <li><a href="start_assessment.php">Start Assessment</a></li>
                            <li><a href="assessment_history.php">Assessment History</a></li>
                            <li><a href="assessment_summary.php">Assessment Summary</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="useful_links.php">Useful Links</a></li>
                            <li><a href="faq.php">FAQ</a></li>
                            <li><a href="sitemap.php">Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.php">About</a></li>
                    <li>
                        <a href="#" id="profile-link">
                            <div class="profile-info">
                                <span class="username" id="username">
                                    <?php
                                    
                                    if (isset($_SESSION['username'])) {
                                        echo $_SESSION['username'];  
                                    } else {
                                        echo "Guest";  
                                    }
                                    ?>
                                </span>
                                <img src="images/usericon.png" alt="Profile" class="profile-image" id="profile-image">
                            </div>
                        </a>
                        <ul class="dropdown" id="profile-dropdown">
                            <li><a href="profile.php">Settings</a></li>
                            <li><a href="#" onclick="openPopup('logout-popup')">Logout</a></li>
                        </ul>
                    </li>
                </ul>
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        </nav>
    </header>

    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <button class="close-button" onclick="logoutUser()">Yes</button>
        <button class="cancel-button" onclick="closePopup('logout-popup')">No</button>
    </div>
    
    <section id="assessment-history">
        <div class="container">
            <div class="history-header">
                <h2>Assessment Summary</h2>
            </div>
            
            <div class="scrollable-container">
                <?php if (!empty($assessments)): ?>
                    <?php foreach ($assessments as $assessment): ?>
                        <div class="history-item">
                            <div class="date-time">
                                <p>
                                    <strong>Date:</strong> <?php echo htmlspecialchars(date('Y-m-d', strtotime($assessment['start_time']))); ?> 
                                    <br><strong>Time Used:</strong> <?php 
                                        if ($assessment['end_time']) {
                                            $duration = strtotime($assessment['end_time']) - strtotime($assessment['start_time']);
                                            $minutes = max(0, floor($duration / 60));
                                            $seconds = max(0, $duration % 60);
                                            echo $minutes . ' minutes ' . $seconds . ' seconds';
                                        } else {
                                            echo 'Assessment not completed';
                                        }
                                    ?>
                                </p>
                            </div>
                            <div class="score">
                                <h3>Score</h3>
                                <p class="<?php echo ($assessment['score'] >= $assessment['passing_score_percentage']) ? 'score-passed' : 'score-failed'; ?>">
                                    <?php echo $assessment['score'] ?? 'Not completed'; ?>
                                </p>
                            </div>
                            <div class="actions">
                                <?php if($assessment['end_time']): ?>
                                    <a href="download_assessment_history_report.php?assessment_id=<?php echo urlencode($assessment['assessment_id']); ?>" title="Download">Download</a>
                                    <a href="share_assessment_summary.php?assessment_id=<?= urlencode($assessment['assessment_id']); ?>" title="Share">Share</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No assessment summary available.</p>
    <?php endif; ?>
        </div>
    </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-logo">
                    <a href="index.php"><img src="images/logo.jpg" alt="TechFit Logo"></a>
                </div>
                <div class="social-media">
                    <p>Keep up with TechFit:</p>
                    <div class="social-icons">
                        <a href="https://facebook.com"><img src="images/facebook.png" alt="Facebook"></a>
                        <a href="https://twitter.com"><img src="images/twitter.png" alt="Twitter"></a>
                        <a href="https://instagram.com"><img src="images/instagram.png" alt="Instagram"></a>
                        <a href="https://linkedin.com"><img src="images/linkedin.png" alt="LinkedIn"></a>
                    </div>
                    <p><a href="mailto:techfit@gmail.com">techfit@gmail.com</a></p>
                </div>
            </div>
            <div class="footer-right">
                <div class="footer-column">
                    <h3>Assessment</h3>
                    <ul>
                        <li><a href="start_assessment.php">Start Assessment</a></li>
                        <li><a href="assessment_history.php">Assessment History</a></li>
                        <li><a href="assessment_summary.php">Assessment Summary</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="useful_links.php">Useful Links</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="sitemap.php">Sitemap</a></li>
                        <li><a href="about.php">About</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="feedback.php">Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="terms.php">Terms of Service</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TechPathway: TechFit. All rights reserved.</p>
        </div>
    </footer>

    <script src="scripts.js?v=1.0"></script>
    <script>
        function openPopup(popupId) {
            document.getElementById(popupId).style.display = 'block';
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        function logoutUser() {
            window.location.href = '/Techfit'; 
        }
    </script>
</body>
</html>
