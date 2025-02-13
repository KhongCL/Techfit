<?php
session_start(); 

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
$check_assessment_sql = "SELECT COUNT(*) as completed 
                        FROM Assessment_Job_Seeker 
                        WHERE job_seeker_id = ? AND end_time IS NOT NULL";

$stmt = $conn->prepare($check_assessment_sql);
$stmt->bind_param("s", $_SESSION['job_seeker_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['completed'] > 0) {
    echo '<script>
        if (confirm("You have already completed an assessment. Would you like to view your assessment history?")) {
            window.location.href = "assessment_history.php";
        } else {
            window.location.href = "index.php";
        }
    </script>';
    exit();
}
$conn->close();

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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment History - TechFit</title>
    <link rel="stylesheet" href="styles.css">

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

    <section id="start-assessment-container">
        <h2 id="start-assessment-title">Assessment</h2>
        <div id="start-assessment-rules">
            <h3>Rules and Regulations</h3>
            <p>Please read the following rules and regulations carefully before starting the assessment:</p>
            <ul>
                <li>Ensure you have a stable internet connection.</li>
                <li>Do not refresh the page during the assessment.</li>
                <li>Answer all questions to the best of your ability.</li>
                <li>Do not use any external resources or assistance.</li>
                <li>Complete the assessment within the given time frame.</li>
            </ul>
        </div>
        <div id="agree-checkbox">
            <input type="checkbox" id="agree" name="agree">
            <label for="agree">I have read and understood the rules and regulations.</label>
        </div>
        <button id="start-assessment-button" disabled>Start Assessment</button>
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

    <script src="scripts.js"></script>
    <script>
    // Enable/disable start button based on checkbox
    document.getElementById('agree').addEventListener('change', function() {
        document.getElementById('start-assessment-button').disabled = !this.checked;
    });

    // Handle start assessment button click
    document.getElementById('start-assessment-button').addEventListener('click', function() {
        if (!document.getElementById('agree').checked) {
            alert('Please agree to the rules and regulations first.');
            return;
        }

        fetch('start_assessment_session.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'assessment_question.php';
                } else {
                    alert('Error starting assessment: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to start assessment. Please try again.');
            });
    });
    </script>
</body>
</html>