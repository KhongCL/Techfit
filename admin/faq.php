<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    function getFAQs($conn, $category) {
        $stmt = $conn->prepare("SELECT question, answer FROM resource WHERE type = 'faq' AND category = :category");
        $stmt->bindParam(':category', $category);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $jobSeekerFAQs = getFAQs($conn, 'jobSeeker');
    $employerFAQs = getFAQs($conn, 'employer');

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - TechFit</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><img src="images/logo.jpg" alt="TechFit Logo"></a>
        </div>
        <nav>
            <div class="nav-container">
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <ul class="nav-list">
                    <li><a href="#">Assessments</a>
                        <ul class="dropdown">
                            <li><a href="create_assessment.php">Create New Assessment</a></li>
                            <li><a href="manage_assessments.php">Manage Assessments</a></li>
                            <li><a href="view_assessment_results.php">View Assessment Results</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Users</a>
                        <ul class="dropdown">
                            <li><a href="manage_users.php">Manage Users</a></li>
                            <li><a href="user_feedback.php">User Feedback</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Reports</a>
                        <ul class="dropdown">
                            <li><a href="assessment_performance.php">Assessment Performance</a></li>
                            <li><a href="user_engagement.php">User Engagement Statistics</a></li>
                            <li><a href="feedback_analysis.php">Feedback Analysis</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="useful_links.php">Manage Useful Links</a></li>
                            <li><a href="faq.php">Manage FAQs</a></li>
                            <li><a href="sitemap.php">Manage Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.php">About</a></li>
                    <li>
                        <a href="#" id="profile-link">
                            <div class="profile-info">
                                <span class="username" id="username">Admin</span>
                                <img src="images/usericon.png" alt="Profile" class="profile-image" id="profile-image">
                            </div>
                        </a>
                        <ul class="dropdown" id="profile-dropdown">
                            <li><a href="settings.php">Settings</a>
                                <ul class="dropdown">
                                    <li><a href="manage_profile.php">Manage Profile</a></li>
                                    <li><a href="system_configuration.php">System Configuration Settings</a></li>
                                </ul>
                            </li>
                            <li><a href="logout.php">Logout</a></li>
                        </ul>
                    </li>                    
                </ul>
            </div>
        </nav>
    </header>

    <section id="faq">
        <h2>Frequently Asked Questions</h2>

        <div class="faq-container" id="job-seeker-faq-container">
            <h1 style="text-align: center;">For Job Seekers</h1>
            <?php if (empty($jobSeekerFAQs)): ?>
            <div class="faq-category">
                <h3>This category is empty.</h3>
                <p>No questions found.</p>
            </div>
            <?php else: ?>
            <?php foreach ($jobSeekerFAQs as $faq): ?>
                <div class="faq-item">
                <div class="faq-question">
                    <span><?php echo $faq['question']; ?></span>
                    <div class="dropdown-arrow-wrapper">
                    <span class="dropdown-arrow">&#9660;</span>
                    </div>
                </div>
                <div class="faq-answer">
                    <p><?php echo $faq['answer']; ?></p>
                </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="faq-container" id="employer-faq-container">
            <h1 style="text-align: center;">For Employers</h1>
            <?php if (empty($employerFAQs)): ?>
            <div class="faq-category">
                <h3>This category is empty.</h3>
                <p>No questions found.</p>
            </div>
            <?php else: ?>
            <?php foreach ($employerFAQs as $faq): ?>
                <div class="faq-item">
                <div class="faq-question">
                    <span><?php echo $faq['question']; ?></span>
                    <div class="dropdown-arrow-wrapper">
                    <span class="dropdown-arrow">&#9660;</span>
                    </div>
                </div>
                <div class="faq-answer">
                    <p><?php echo $faq['answer']; ?></p>
                </div>
                </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div style="text-align: center; margin-top: 30px; padding-bottom: 30px;">
            <a href="manage_faq.php" id="manage_faq_button" style="background-color: #4CAF50; padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px;">Manage FAQs</a>
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
                    <h3>Assessments</h3>
                    <ul>
                        <li><a href="create_assessment.php">Create New Assessment</a></li>
                        <li><a href="manage_assessments.php">Manage Assessments</a></li>
                        <li><a href="view_assessment_results.php">View Assessment Results</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Users</h3>
                    <ul>
                        <li><a href="manage_users.php">Manage Users</a></li>
                        <li><a href="user_feedback.php">User Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Reports</h3>
                    <ul>
                        <li><a href="assessment_performance.php">Assessment Performance</a></li>
                        <li><a href="user_engagement.php">User Engagement Statistics</a></li>
                        <li><a href="feedback_analysis.php">Feedback Analysis</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="useful_links.php">Manage Useful Links</a></li>
                        <li><a href="faq.php">Manage FAQs</a></li>
                        <li><a href="sitemap.php">Manage Sitemap</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>About</h3>
                    <ul>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                        <li><a href="terms.php">Terms & Condition</a></li>
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
</body>
</html>
<?php $conn = null; ?>