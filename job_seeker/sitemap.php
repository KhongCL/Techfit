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
                $stmt = $mysqli->prepare("INSERT INTO resource (resource_id, type, title, link, category) VALUES (?, 'usefulLink', ?, ?, ?)");
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


$result = $mysqli->query("SELECT * FROM resource WHERE type = 'usefulLink' ORDER BY category, resource_id");
$usefulLinks = $result->fetch_all(MYSQLI_ASSOC);


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
    <title>Sitemap - TechFit</title>
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

    <section id="sitemap">
        <h2>Sitemap</h2>
        <p>Explore the structure of our website:</p>
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

            $result = $mysqli->query("SELECT * FROM resource WHERE type = 'sitemap' AND category = 'jobSeeker' ORDER BY resource_id DESC LIMIT 1");
            $sitemap = $result->fetch_assoc();

            if ($sitemap) {
                echo '<img src="data:image/jpeg;base64,' . base64_encode($sitemap['image']) . '" alt="Website Sitemap" class="sitemap-image" />';
            } else {
                echo '<p>No sitemap available for Job Seekers.</p>';
            }

            $mysqli->close();
            ?>
        </div>
        <div style="height: 205px;"></div>
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
            window.location.href = '../'; 
        }
    </script>
</body>
</html>
