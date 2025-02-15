<?php
session_start();
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

function displayLoginMessage() {
    echo '<script>
        alert("You need to log in to access this page.");
    </script>';
    exit();
}

if (!isset($_SESSION['user_id'])) {
    displayLoginMessage();
}

if ($_SESSION['role'] !== 'Admin') {
    displayLoginMessage();
}

function generateResourceId($mysqli) {
    $result = $mysqli->query("SELECT resource_id FROM resource ORDER BY resource_id DESC LIMIT 1");
    $lastId = $result->fetch_assoc()['resource_id'];

    $prefix = "R";
    $newId = 1;
    if ($lastId) {
        $numericPart = intval(substr($lastId, strlen($prefix)));
        $newId = $numericPart + 1;
    }

    return $prefix . str_pad($newId, 2, "0", STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'add') {
            $title = trim($_POST['title']);
            $link = trim($_POST['link']);
            $category = trim($_POST['category']);

            if ($title && $link && $category) {
                $resourceId = generateResourceId($mysqli);
                $stmt = $mysqli->prepare("INSERT INTO resource (resource_id, type, title, link, category) VALUES (?, '$useful_link', ?, ?, ?)");
                $stmt->bind_param("ssss", $resourceId, $title, $link, $category);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'message' => 'Useful link added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'];
            $title = trim($_POST['title']);
            $link = trim($_POST['link']);
            $category = trim($_POST['category']);

            if ($id && $title && $link && $category) {
                $stmt = $mysqli->prepare("UPDATE resource SET title = ?, link = ?, category = ? WHERE resource_id = ?");
                $stmt->bind_param("ssss", $title, $link, $category, $id);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'message' => 'Useful link updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            if ($id) {
                $stmt = $mysqli->prepare("DELETE FROM resource WHERE resource_id = ?");
                $stmt->bind_param("s", $id);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'message' => 'Useful link deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            }
        }
        exit;
    }
}

$result = $mysqli->query("SELECT * FROM resource WHERE type = 'useful_link' ORDER BY category, resource_id");
$usefulLinks = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Useful Links Management - TechFit</title>
    <link rel="stylesheet" href="styles.css">
</head>
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
                            <li><a href="settings.php">Settings</a>
                                <ul class="dropdown">
                                    <li><a href="manage_profile.php">Manage Profile</a></li>
                                    <li><a href="system_configuration.php">System Configuration Settings</a></li>
                                </ul>
                            </li>
                            <li><a href="#" >Logout</a></li>
                        </ul>
                    </li>                    
                </ul>
            </div>
        </nav>
    </header>  

    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <button class="close-button" id="logout-confirm-button">Yes</button>
        <button class="cancel-button" id="logout-cancel-button">No</button>
    </div>

<body>
        <section id="resources">
            <h2>Useful Links</h2>
            <p>Here are some useful links to help you get started on your career in IT:</p>

            <div class="resource-columns">
                <div class="resource-column">
                    <h3>For Job Seekers</h3>
                    <ul>
                        <?php 
                        $jobSeekerLinks = array_filter($usefulLinks, function($link) {
                            return $link['category'] === 'jobSeeker';
                        });
                        if ($jobSeekerLinks):
                            foreach ($jobSeekerLinks as $link): ?>
                                <li><a href="<?= htmlspecialchars($link['link']) ?>"><?= htmlspecialchars($link['title']) ?></a></li>
                            <?php endforeach;
                        else: ?>
                            <li style="color: grey; font-style: italic;">No useful links yet for this category.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="resource-column">
                    <h3>For Employers</h3>
                    <ul>
                        <?php 
                        $employerLinks = array_filter($usefulLinks, function($link) {
                            return $link['category'] === 'employer';
                        });
                        if ($employerLinks):
                            foreach ($employerLinks as $link): ?>
                                <li><a href="<?= htmlspecialchars($link['link']) ?>"><?= htmlspecialchars($link['title']) ?></a></li>
                            <?php endforeach;
                        else: ?>
                            <li style="color: grey; font-style: italic;">No useful links yet for this category.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </section>
    <div style="text-align: center; margin-top: 30px; padding-bottom: 30px;">
        <a href="manage_useful_links.php" id="manage_useful_links_button" style="padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px;">Manage Useful Links</a>
        <div style="height: 110px;"></div>
    </div>
    
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