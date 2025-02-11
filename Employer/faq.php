<?php
session_start(); // Start the session to access session variables
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

$result = $mysqli->query("SELECT * FROM resource WHERE type = 'usefulLink' ORDER BY category, resource_id");
$usefulLinks = $result->fetch_all(MYSQLI_ASSOC);

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

if ($_SESSION['role'] !== 'Employer') {
    displayLoginMessage();
}

session_write_close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - TechFit</title>
    <link rel="stylesheet" href="styles.css?v=2.0">
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.php"><img src="images/logo.jpg" alt="TechFit Logo"></a>
        </div>
        <nav>
            <div class="nav-container">
                <ul class="nav-list">
                    <li><a href="#">Candidates</a></li>
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
                                <span class="username" id="username">Employer</span>
                                <img src="images/usericon.png" alt="Profile" class="profile-image" id="profile-image">
                            </div>
                        </a>
                        <ul class="dropdown" id="profile-dropdown">
                            <li><a href="profile.php">Settings</a></li>
                            <li><a href="logout.php">Logout</a></li>
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
                    <h3>Candidate</h3>
                    <ul>
                        <li><a href="candidates.php">Candidates</a></li>
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
</body>
</html>
