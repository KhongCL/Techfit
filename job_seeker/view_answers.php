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

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve assessment ID from query parameter
$assessment_id = $_GET['assessment_id'];

// Fetch assessment details and answers
$sql = "
    SELECT 
    Assessment_Job_Seeker.end_time AS assessment_date, 
    Assessment_Job_Seeker.score,
    Question.question_text,
    Answer.answer_text AS user_answer,
    Question.correct_answer,
    Answer.is_correct
    
    FROM Assessment_Job_Seeker
    JOIN Question ON Question.assessment_id = Assessment_Job_Seeker.assessment_id
    LEFT JOIN Answer ON Answer.job_seeker_id = Assessment_Job_Seeker.job_seeker_id AND Answer.question_id = Question.question_id
    WHERE Assessment_Job_Seeker.assessment_id = ? AND Assessment_Job_Seeker.job_seeker_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $assessment_id, $_SESSION['job_seeker_id']);
$stmt->execute();
$result = $stmt->get_result();

$assessment_details = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        #assessment-summary {
            padding: 20px;
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }

        .container_a_s {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        /* Header styling */
        .summary_header {
            padding: 20px;
            border-bottom: 2px solid #e0e0e0;
            text-align: center;
        }

        .summary_header h2 {
            margin: 0;
            color: #333;
            font-size: 1.5em;
        }

        .summary_header p {
            margin: 5px 0;
            color: #666;
            font-size: 1em;
        }

        /* Scrollable area styling */
        .scrollable {
            max-height: 400px;
            overflow-y: auto;
            padding: 20px;
        }

        /* Individual summary items */
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .summary-details h3 {
            margin: 0;
            color: #333;
            font-size: 1.2em;
        }

        .summary-details p {
            margin: 5px 0;
            color: #555;
            font-size: 1em;
        }

        /* Badge styling */
        .summary-item span {
            font-size: 0.9em;
            padding: 5px 15px;
            border-radius: 15px;
            color: white;
            font-weight: bold;
        }

        .summary-item span.green {
            background-color: #4caf50;
        }

        .summary-item span.red {
            background-color: #f44336;
        }

        /* Scrollbar styling (optional) */
        .scrollable::-webkit-scrollbar {
            width: 10px;
        }

        .scrollable::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .scrollable::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 10px;
        }

        .scrollable::-webkit-scrollbar-thumb:hover {
            background: #888;
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

    
    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <button class="close-button" onclick="logoutUser()">Yes</button>
        <button class="cancel-button" onclick="closePopup('logout-popup')">No</button>
    </div>

    <section id="assessment-summary">
        <div class="container_a_s">
            <div class="summary_header">
                <h2>View Answers</h2>
                <?php if (!empty($assessment_details)): ?>
                    <p>Assessment ID: <?= $assessment_id; ?></p>
                    <p>Assessment Date: <?= date('d/m/Y', strtotime($assessment_details[0]['assessment_date'])); ?></p>
                    <p>Score: <?= $assessment_details[0]['score']; ?>%</p>
                <?php endif; ?>
            </div>
            <div class="scrollable">
                <?php if (!empty($assessment_details)): ?>
                    <?php foreach ($assessment_details as $detail): ?>
                        <div class="summary-item">
                            <div class="summary-details">
                                <h3>Question: <?= htmlspecialchars($detail['question_text']); ?></h3>
                                <p>Your Answer: <?= htmlspecialchars($detail['user_answer']); ?></p>
                                <p>Correct Answer: <?= htmlspecialchars($detail['correct_answer']); ?></p>
                            </div>
                            <div>
                                <span class="<?= $detail['is_correct'] ? 'green' : 'red'; ?>">
                                    <?= $detail['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No answers found for this assessment.</p>
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

</body>
</html>