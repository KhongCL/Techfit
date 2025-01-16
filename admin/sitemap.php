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

    // Function to fetch FAQs by category
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
            <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
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
                            <li><a href="create_assessment.html">Create New Assessment</a></li>
                            <li><a href="manage_assessments.php">Manage Assessments</a></li>
                            <li><a href="view_assessment_results.html">View Assessment Results</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Users</a>
                        <ul class="dropdown">
                            <li><a href="manage_users.php">Manage Users</a></li>
                            <li><a href="user_feedback.html">User Feedback</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Reports</a>
                        <ul class="dropdown">
                            <li><a href="assessment_performance.html">Assessment Performance</a></li>
                            <li><a href="user_engagement.html">User Engagement Statistics</a></li>
                            <li><a href="feedback_analysis.html">Feedback Analysis</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="useful_links.html">Manage Useful Links</a></li>
                            <li><a href="faq.html">Manage FAQs</a></li>
                            <li><a href="sitemap.html">Manage Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.html">About</a></li>
                    <li>
                        <a href="#" id="profile-link">
                            <div class="profile-info">
                                <span class="username" id="username">Admin</span>
                                <img src="images/usericon.png" alt="Profile" class="profile-image" id="profile-image">
                            </div>
                        </a>
                        <ul class="dropdown" id="profile-dropdown">
                            <li><a href="settings.html">Settings</a>
                                <ul class="dropdown">
                                    <li><a href="manage_profile.html">Manage Profile</a></li>
                                    <li><a href="system_configuration.html">System Configuration Settings</a></li>
                                </ul>
                            </li>
                            <li><a href="logout.html">Logout</a></li>
                        </ul>
                    </li>                    
                </ul>
            </div>
        </nav>
    </header>

    <section id="sitemap">
        <h2>Sitemap</h2>
        <p>Explore the structure of our website:</p>
        <h1 class="large-font">Job Seeker</h1>
        <div class="sitemap-container">
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "techfit";

            $mysqli = new mysqli($servername, $username, $password, $dbname);

            if ($mysqli->connect_error) {
                die("Database connection failed: " . $mysqli->connect_error);
            }

            $result = $mysqli->query("SELECT * FROM resource WHERE type = 'sitemap' AND category = 'jobSeeker' ORDER BY resource_id DESC");
            if ($result->num_rows > 0) {
                while ($sitemap = $result->fetch_assoc()) {
                    echo '<img src="data:image/jpeg;base64,' . base64_encode($sitemap['image']) . '" alt="Website Sitemap" class="sitemap-image" />';
                }
            } else {
                echo '<p>No sitemap available for Job Seekers.</p>';
            }

            $mysqli->close();
            ?>
        </div>
        <h1 class="large-font">Employer</h1>
        <div class="sitemap-container">
            <?php
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "techfit";

            $mysqli = new mysqli($servername, $username, $password, $dbname);

            if ($mysqli->connect_error) {
                die("Database connection failed: " . $mysqli->connect_error);
            }

            $result = $mysqli->query("SELECT * FROM resource WHERE type = 'sitemap' AND category = 'employer' ORDER BY resource_id DESC");
            if ($result->num_rows > 0) {
                while ($sitemap = $result->fetch_assoc()) {
                    echo '<img src="data:image/jpeg;base64,' . base64_encode($sitemap['image']) . '" alt="Website Sitemap" class="sitemap-image" />';
                }
            } else {
                echo '<p>No sitemap available for Employers.</p>';
            }

            $mysqli->close();
            ?>
        </div>
        <div style="text-align: center; margin-top: 30px; padding-bottom: 30px;">
            <a href="manage_sitemap.php" id="manage_sitemap_button" style="background-color: #4CAF50; padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px;">Manage Sitemap</a>
            <div style="height: 205px;"></div>
        </div>
    </section>

    <footer>
        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-logo">
                    <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
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
                        <li><a href="create_assessment.html">Create New Assessment</a></li>
                        <li><a href="manage_assessments.php">Manage Assessments</a></li>
                        <li><a href="view_assessment_results.html">View Assessment Results</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Users</h3>
                    <ul>
                        <li><a href="manage_users.html">Manage Users</a></li>
                        <li><a href="user_feedback.html">User Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Reports</h3>
                    <ul>
                        <li><a href="assessment_performance.html">Assessment Performance</a></li>
                        <li><a href="user_engagement.html">User Engagement Statistics</a></li>
                        <li><a href="feedback_analysis.html">Feedback Analysis</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="useful_links.html">Manage Useful Links</a></li>
                        <li><a href="faq.html">Manage FAQs</a></li>
                        <li><a href="sitemap.html">Manage Sitemap</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>About</h3>
                    <ul>
                        <li><a href="about.html">About</a></li>
                        <li><a href="contact.html">Contact Us</a></li>
                        <li><a href="terms.html">Terms & Condition</a></li>
                        <li><a href="privacy.html">Privacy Policy</a></li>
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