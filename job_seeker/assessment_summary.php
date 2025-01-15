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
session_start(); // Start the session

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve job seeker ID from session
$job_seeker_id = $_SESSION['job_seeker_id'];

// Fetch assessment summaries for the job seeker
$sql = "
    SELECT Assessment_Job_Seeker.end_time AS assessment_date, 
    COUNT(Question.question_id) AS number_of_questions, 
    SUM(CASE WHEN Answer.is_correct = TRUE THEN 1 ELSE 0 END) AS correct_answers,
    SUM(CASE WHEN Answer.is_correct = FALSE THEN 1 ELSE 0 END) AS wrong_answers,
    Assessment_Job_Seeker.score
    FROM Assessment_Job_Seeker
    JOIN Question ON Question.assessment_id = Assessment_Job_Seeker.assessment_id
    LEFT JOIN Answer ON Answer.job_seeker_id = Assessment_Job_Seeker.job_seeker_id AND Answer.question_id = Question.question_id
    WHERE Assessment_Job_Seeker.job_seeker_id = ?
    GROUP BY Assessment_Job_Seeker.assessment_id
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
    <title>TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* Full viewport height */
            margin: 0;
        }

        header {
            flex-shrink: 0; /* Header stays at the top */
        }

        #assessment-summary {
            flex-grow: 1; /* Main section grows to fill space between header and footer */
            margin: 20px 0; /* Optional spacing around the container */
            background-color: #f8f8f8;
            padding: 20px 0;
        }

        .container_a_s {
            width: 90%;
            max-width: 800px;
            margin: auto; /* Center horizontally */
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .summary_header {
            padding: 20px;
            text-align: left;
            font-size: 1.8rem;
            font-weight: bold;
            color: #333;
            border-bottom: 2px solid #ddd;
        }

        .scrollable {
            max-height: 400px;
            overflow-y: auto;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #ddd;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-item h3 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: bold;
            color: #333;
        }

        .summary-item p {
            margin: 5px 0;
            font-size: 1rem;
            color: #555;
        }

        .summary-details {
            flex-grow: 1;
        }

        .view-answers-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: background-color 0.3s;
        }

        .view-answers-button:hover {
            background-color: #0056b3;
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
                                    // Check if the user is logged in and display their username
                                    if (isset($_SESSION['username'])) {
                                        echo $_SESSION['username'];  // Display the username from session
                                    } else {
                                        echo "Guest";  // Default if not logged in
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

    <!-- Logout Popup -->
    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <button class="close-button" onclick="logoutUser()">Yes</button>
        <button class="cancel-button" onclick="closePopup('logout-popup')">No</button>
    </div>

    <section id="assessment-summary">
        <div class="container_a_s">
            <div class="summary_header">Assessment Summary</div>
            <div class="scrollable">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="summary-item">
                            <div class="summary-details">
                                <h3>Date: <?= date('d/m/Y', strtotime($row['assessment_date'])); ?></h3>
                                <p>Score: <?= $row['score']; ?></p>
                                <p>Correct answers: <?= $row['correct_answers']; ?></p>
                                <p>Wrong answers: <?= $row['wrong_answers']; ?></p>
                            </div>
                            <a href="view_answers.php?assessment_date=<?= urlencode($row['assessment_date']); ?>" class="view-answers-button">View answers</a>
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
