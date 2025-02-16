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


$job_seeker_id = $_SESSION['job_seeker_id'];

$sql = "
    SELECT 
        Assessment_Job_Seeker.start_time,
        Assessment_Job_Seeker.end_time,
        Assessment_Job_Seeker.result_id AS assessment_id,
        Assessment_Job_Seeker.score,
        Assessment_Settings.passing_score_percentage,
        TIMESTAMPDIFF(SECOND, Assessment_Job_Seeker.start_time, Assessment_Job_Seeker.end_time) as duration
    FROM Assessment_Job_Seeker
    JOIN Assessment_Settings ON Assessment_Settings.setting_id = '1'
    WHERE Assessment_Job_Seeker.job_seeker_id = ?
    AND Assessment_Job_Seeker.end_time IS NOT NULL
    ORDER BY Assessment_Job_Seeker.end_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $job_seeker_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment History - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <style>

        .summary_header {
            padding: 20px;
            border-bottom: 2px solid var(--background-color-light);
            text-align: left;
        }

        .summary-item {
            background-color: var(--background-color-light);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .view-answers-button {
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 4px;
            color: var(--text-color);
            background-color: var(--primary-color);
            transition: background-color 0.3s ease;
            align-self: flex-start;
            margin-left: 20px;
        }

        .view-answers-button:hover {
            background-color: var(--button-color-hover);
        }

        .status {
            font-weight: bold;
            margin-top: 10px;
        }

        .status.passed {
            color: var(--success-color);
        }

        .status.failed {
            color: var(--danger-color);
        }

        .summary-details {
            display: flex;
            flex-direction: column;
            gap: 5px;
            text-align: left;
            flex-grow: 1;
        }

        .summary-details h3 {
            margin-bottom: 10px;
            color: var(--text-color);
            text-align: left;
        }

        .summary-details p {
            margin: 0;
            color: var(--text-color-dark);
            text-align: left;
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

    <section id="assessment-summary">
        <div class="container_a_s">
            <div class="summary_header">Assessment History</div>
            <div class="scrollable">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="summary-item">
                            <div class="summary-details">
                                <h3>Assessment <?= $row['assessment_id']; ?></h3>
                                <p>Date: <?= date('Y-m-d', strtotime($row['start_time'])); ?></p>
                                <p>Time Spent: <?= max(0, floor($row['duration']/60)) ?> minutes <?= max(0, $row['duration']%60) ?> seconds</p>
                                <p>Score: <?= $row['score'] ?? 'Not completed'; ?>%</p>
                                <?php if(isset($row['score'])): ?>
                                    <p class="status <?= ($row['score'] >= $row['passing_score_percentage']) ? 'passed' : 'failed' ?>">
                                        Status: <?= ($row['score'] >= $row['passing_score_percentage']) ? 'PASSED' : 'FAILED' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <a href="view_answers.php?assessment_id=<?= urlencode($row['assessment_id']); ?>" class="view-answers-button">View answers</a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No assessments found.</p>
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
