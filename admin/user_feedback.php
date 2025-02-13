<?php
session_start();

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

session_write_close();
?>


<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit'; 

$conn = new mysqli($host, $username, $password, $database);

$sql = "SELECT f.feedback_id, f.text, f.timestamp, fm.action_type, fm.response_text, a.admin_id
        FROM Feedback f
        LEFT JOIN feedback_management fm ON f.feedback_id = fm.feedback_id
        LEFT JOIN Admin a ON fm.admin_id = a.admin_id";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TechFit</title>
    <link rel="stylesheet" href="styles.css">   
        <style>
            .feedback-container {
                display: flex;
                justify-content: center;
            }
            .main-content {
                flex-grow: 1;
                padding: 70px;
                background: #f4f4f4;
                position: relative;
            }
            nav {
                display: flex;
                gap: 20px;
            }
            nav a {
                text-decoration: none;
                color: #007bff;
            }
            nav .active {
                font-weight: bold;
                text-decoration: underline;
            }
            h2 {
                margin-top: -40px;
            }
            .delete-btn {
                position: absolute;
                right: 20px;
                top: 20px;
                background: red;
                color: white;
                border: none;
                padding: 5px 10px;
                cursor: pointer;
                height: 40px;
                display: flex;
                align-items: center;
                border-radius: 3px;
            }
            .feedback-list {
                margin-top: 10px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }   
            .feedback-item {
                display: flex;
                align-items: flex-start; 
                justify-content: flex-start; 
                background: white;
                padding: 40px;
                margin: 5px 0;
                border-radius: 5px;
                width: 60%;
                height: 40px;
            }

            .feedback-checkbox {
                margin-top: -9px;
                width: 20px;
                height: 20px;
                cursor: pointer;
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

    <section class="feedback-container">
        <div class="main-content">
            <h2>FEEDBACK</h2>
            <button class="delete-btn">Delete</button>
            <div class="feedback-list">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="feedback-item">';
                        echo '<input type="checkbox" class="feedback-checkbox">';
                        echo '<div class="feedback-content">';
                        echo '<p><strong>Feedback ID:</strong> ' . $row['feedback_id'] . '</p>';
                        echo '<p><strong>Text:</strong> ' . $row['text'] . '</p>';
                        echo '<p><strong>Timestamp:</strong> ' . $row['timestamp'] . '</p>';
                        if ($row['action_type']) {
                            echo '<p><strong>Action:</strong> ' . $row['action_type'] . '</p>';
                            echo '<p><strong>Response:</strong> ' . $row['response_text'] . '</p>';
                            echo '<p><strong>Admin ID:</strong> ' . $row['admin_id'] . '</p>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>No feedback available.</p>';
                }
                ?>
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