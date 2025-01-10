<?php
session_start(); // Start the session to access session variables

// Function to display the message and options
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

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); // Display message and options if not logged in
}

// Check if the user has the correct role
if ($_SESSION['role'] !== 'Job Seeker') {
    displayLoginMessage(); // Display message and options if the role is not Job Seeker
}

// Check if the job seeker ID is set
if (!isset($_SESSION['job_seeker_id'])) {
    displayLoginMessage(); // Display message and options if job seeker ID is not set
}

// Close the session
session_write_close();
?>

<?php
session_start(); // Start the session to access session variables

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit'; 

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

// Updated SQL query with the required INNER JOINs
$sql = "
    SELECT 
        Assessment_Job_Seeker.assessment_id AS 'assessment_id',
        Assessment_Job_Seeker.job_seeker_id AS 'job_id',
        Assessment_Job_Seeker.start_time AS 'start_time',
        Assessment_Job_Seeker.end_time AS 'end_time',
        Assessment_Job_Seeker.score AS 'score'
    FROM Assessment_Job_Seeker
    INNER JOIN Job_Seeker 
        ON Assessment_Job_Seeker.job_seeker_id = Job_Seeker.job_seeker_id
    INNER JOIN User
        ON Job_Seeker.user_id = User.user_id
    WHERE User.user_id = ?";

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
    <title>Assessment History - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .container {
            margin: 20px auto;
            max-width: 800px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .history-item {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
        }

        .history-item div {
            display: flex;
            flex-direction: column;
        }

        .history-item div h3 {
            margin: 0;
            font-size: 14px;
            color: #555;
        }

        .history-item div p {
            margin: 5px 0 0;
            font-size: 16px;
            color: #000;
        }

        .history-item .actions {
            grid-column: span 2;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .actions a {
            text-decoration: none;
            color: #007BFF;
            font-size: 14px;
        }

        .actions a:hover {
            text-decoration: underline;
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
                                <span class="username" id="username">Profile</span>
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

    <section id="assessment-history">
        <div class="container">
            <!-- Header -->
            <div class="history-header">
                <h2>Assessment History</h2>
                <button class="refresh-btn" title="Refresh">&#x21BB;</button>
            </div>

            <!-- History Items -->
            <?php if (!empty($assessments)): ?>
                <?php foreach ($assessments as $assessment): ?>
                    <div class="history-item">
                        <div>
                            <h3>Date</h3>
                            <p><?php echo htmlspecialchars(date('Y-m-d', strtotime($assessment['start_time']))); ?></p>
                        </div>
                        <div class="score">
                            <h3>Score</h3>
                            <p><?php echo htmlspecialchars($assessment['score']); ?></p>
                        </div>
                        <div>
                            <h3>Avg Time Used (mins)</h3>
                            <p><?php echo round((strtotime($assessment['end_time']) - strtotime($assessment['start_time'])) / 60, 2); ?></p>
                        </div>
                        <div class="actions">
                            <a href="#" title="Download">&#x2193; Download</a>
                            <a href="#" title="Edit">&#x270E; Edit</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No assessment history available.</p>
            <?php endif; ?>
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
                    <p>techfit@gmail.com</p>
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
            window.location.href = '/Techfit'; // Redirect to the root directory
        }
    </script>
</body>
</html>
