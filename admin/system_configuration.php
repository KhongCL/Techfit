<?php
session_start(); 

function displayLoginMessage() {
    echo '<script>
        if (confirm("You need to log in to access this page. Go to Login Page? Click cancel to go to home page.")) {
            window.location.href = "../admin_login.php?key=techfit";
        } else {
            window.location.href = "../index.php";
        }
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
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$assessment_settings = $conn->query("SELECT * FROM Assessment_Settings WHERE setting_id = '1'")->fetch_assoc();

if (!$assessment_settings) {
    $sql = "INSERT INTO Assessment_Settings (setting_id, default_time_limit, passing_score_percentage) 
            VALUES ('1', 30, 70)";
    if ($conn->query($sql) === TRUE) {
        $assessment_settings = $conn->query("SELECT * FROM Assessment_Settings WHERE setting_id = '1'")->fetch_assoc();
    } else {
        $assessment_settings = [
            'default_time_limit' => 30,
            'passing_score_percentage' => 70
        ];
    }
}

$notification_settings = $conn->query("SELECT * FROM Notification_Settings")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Configuration Settings - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        :root {
            --primary-color: #007bff;
            --secondary-color: #1e1e1e;
            --accent-color: #0056b3;
            --text-color: #e0e0e0;
            --background-color: #121212;
            --border-color: #333;
            --hover-background-color: #333;
            --hover-text-color: #fff;
            --button-hover-color: #80bdff;
            --popup-background-color: #1a1a1a;
            --popup-border-color: #444;
            --danger-color: #dc3545;
            --danger-hover-color: #c82333;
            --success-color: #28a745;
            --success-hover-color: #218838;
        }

        body {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        header, footer {
            background-color: var(--secondary-color);
        }

        .sidebar {
            width: 20%;
            float: left;
            background-color: var(--secondary-color);
            padding: 20px;
        }

        .sidebar a {
            display: block;
            color: var(--text-color);
            padding: 10px;
            text-decoration: none;
        }

        .sidebar a:hover {
            background-color: var(--hover-background-color);
            color: var(--hover-text-color);
        }

        .content {
            width: 75%;
            float: right;
            padding: 20px;
        }

        .form-section {
            margin-bottom: 20px;
        }

        .form-section h2 {
            color: var(--primary-color);
        }

        .form-section label {
            display: block;
            margin-bottom: 5px;
        }

        .form-section input, .form-section select, .form-section textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .form-section button {
            background-color: var(--primary-color);
            color: var(--text-color);
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .form-section button:hover {
            background-color: var(--button-hover-color);
        }

        .notification-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 5px 0;
            flex-wrap: nowrap;
        }
        .notification-option label {
            flex-grow: 1;
            white-space: nowrap;
        }
        .notification-option input {
            margin-left: 10px;
            flex-shrink: 0;
            transform: translateX(-670px) translateY(6px);
        }

        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: var(--popup-background-color);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .popup h2 {
            color: #fff;
        }
        .popup .close-button {
            background-color: var(--danger-color);
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }
        .popup .close-button:hover {
            background-color: var(--danger-hover-color);
        }
        .popup .cancel-button {
            background-color: var(--primary-color);
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }
        .popup .cancel-button:hover {
            background-color: var(--button-hover-color);
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
    <form id="logout-form" action="manage_profile.php" method="post">
        <input type="hidden" name="logout" value="1">
        <button type="submit" class="close-button">Yes</button>
        <button type="button" class="cancel-button" onclick="closePopup('logout-popup')">No</button>
    </form>
</div>
<main>
    <div class="sidebar">
        <a href="#assessment-settings">Assessment Settings</a>
        <a href="#notification-settings">Notification Settings</a>
    </div>
    <div class="content">
        <h1>System Configuration Settings</h1>

        <div id="assessment-settings" class="form-section">
            <h2>Assessment Settings</h2>
            <form method="POST" action="save_assessment_settings.php">
                <label for="default-time-limit">Default Time Limit (minutes):</label>
                <select id="default-time-limit" name="default_time_limit">
                    <option value="10" <?php if ($assessment_settings['default_time_limit'] == 10) echo 'selected'; ?>>10</option>
                    <option value="20" <?php if ($assessment_settings['default_time_limit'] == 20) echo 'selected'; ?>>20</option>
                    <option value="30" <?php if ($assessment_settings['default_time_limit'] == 30) echo 'selected'; ?>>30</option>
                    <option value="60" <?php if ($assessment_settings['default_time_limit'] == 60) echo 'selected'; ?>>60</option>
                    <option value="90" <?php if ($assessment_settings['default_time_limit'] == 90) echo 'selected'; ?>>90</option>
                    <option value="120" <?php if ($assessment_settings['default_time_limit'] == 120) echo 'selected'; ?>>120</option>
                </select>

                <label for="passing-score">Passing Score (%):</label>
                <select id="passing-score" name="passing_score">
                    <option value="50" <?php if ($assessment_settings['passing_score_percentage'] == 50) echo 'selected'; ?>>50%</option>
                    <option value="60" <?php if ($assessment_settings['passing_score_percentage'] == 60) echo 'selected'; ?>>60%</option>
                    <option value="70" <?php if ($assessment_settings['passing_score_percentage'] == 70) echo 'selected'; ?>>70%</option>
                    <option value="80" <?php if ($assessment_settings['passing_score_percentage'] == 80) echo 'selected'; ?>>80%</option>
                    <option value="90" <?php if ($assessment_settings['passing_score_percentage'] == 90) echo 'selected'; ?>>90%</option>
                </select>

                <button type="submit">Save Assessment Settings</button>
            </form>
        </div>

        <div id="notification-settings" class="form-section">
            <h2>Notification Settings</h2>
            <form method="POST" action="save_notification_settings.php">
                <label for="notification-events">Enable Notifications for Events:</label>
                <div id="notification-events">
                    <?php
                    $events = ['Assessment Results', 'System Issues', 'User Feedback'];
                    foreach ($events as $event) {
                        $is_enabled = false;
                        $template = '';
                        foreach ($notification_settings as $setting) {
                            if ($setting['event_name'] == $event) {
                                $is_enabled = $setting['is_enabled'];
                                $template = $setting['email_template'];
                                break;
                            }
                        }
                        echo '<div class="notification-option">
                                <label>' . $event . '</label>
                                <input type="checkbox" name="notification_events[]" value="' . $event . '" ' . ($is_enabled ? 'checked' : '') . '>
                            </div>';
                        echo '<label for="email-template-' . $event . '">Email Template for ' . $event . ':</label>';
                        echo '<textarea id="email-template-' . $event . '" name="email_template[' . $event . ']" rows="5">' . htmlspecialchars($template) . '</textarea>';
                    }
                    ?>
                </div>

                <button type="submit">Save Notification Settings</button>
            </form>
        </div>
    </div>
</main>
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

<script>
    function openPopup(popupId) {
        document.getElementById(popupId).style.display = 'block';
    }

    function closePopup(popupId) {
        document.getElementById(popupId).style.display = 'none';
    }

    function logoutUser() {
        document.getElementById('logout-form').submit();
    }
</script>
</body>
</html>