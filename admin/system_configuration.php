<?php
session_start(); // Start the session to access session variables

// Function to display the message
function displayLoginMessage() {
    echo '<script>
        alert("You need to log in to access this page.");
    </script>';
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); // Display message if not logged in
}

// Check if the user has the correct role
if ($_SESSION['role'] !== 'Admin') {
    displayLoginMessage(); // Display message if the role is not Admin
}

// Close the session
session_write_close();
?>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve current settings from the database
$assessment_settings = $conn->query("SELECT * FROM Assessment_Settings WHERE setting_id = '1'")->fetch_assoc();
$security_settings = $conn->query("SELECT * FROM Security_Settings WHERE setting_id = '1'")->fetch_assoc();
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
        /* Color Theme */
        :root {
            --primary-color: #007bff; /* Blue */
            --secondary-color: #1e1e1e; /* Dark Grey */
            --accent-color: #0056b3; /* Darker Blue */
            --text-color: #e0e0e0; /* Slightly Darker White */
            --background-color: #121212; /* Very Dark Grey */
            --border-color: #333; /* Dark Grey */
            --hover-background-color: #333; /* Slightly Lighter Dark Grey */
            --hover-text-color: #fff; /* White */
            --button-hover-color: #80bdff; /* Lighter Blue */
            --popup-background-color: #1a1a1a; /* Slightly Lighter Dark Grey */
            --popup-border-color: #444; /* Slightly Lighter Dark Grey */
            --danger-color: #dc3545; /* Red */
            --danger-hover-color: #c82333; /* Darker Red */
            --success-color: #28a745; /* Green */
            --success-hover-color: #218838; /* Darker Green */
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
    <main>
    <div class="sidebar">
        <a href="#assessment-settings">Assessment Settings</a>
        <a href="#security-settings">Security Settings</a>
        <a href="#notification-settings">Notification Settings</a>
    </div>
    <div class="content">
        <h1>System Configuration Settings</h1>

        <!-- Assessment Settings Section -->
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

                <label for="question-types">Allowed Question Types:</label>
                <div id="question-types">
                    <?php
                    $allowed_question_types = json_decode($assessment_settings['allowed_question_types'], true);
                    ?>
                    <input type="checkbox" name="question_types[]" value="Multiple Choice" <?php if (in_array('Multiple Choice', $allowed_question_types)) echo 'checked'; ?>> Multiple Choice<br>
                    <input type="checkbox" name="question_types[]" value="True/False" <?php if (in_array('True/False', $allowed_question_types)) echo 'checked'; ?>> True/False<br>
                    <input type="checkbox" name="question_types[]" value="Fill in the Blank" <?php if (in_array('Fill in the Blank', $allowed_question_types)) echo 'checked'; ?>> Fill in the Blank<br>
                    <input type="checkbox" name="question_types[]" value="Essay" <?php if (in_array('Essay', $allowed_question_types)) echo 'checked'; ?>> Essay<br>
                    <input type="checkbox" name="question_types[]" value="Coding" <?php if (in_array('Coding', $allowed_question_types)) echo 'checked'; ?>> Coding<br>
                </div>

                <button type="submit">Save Assessment Settings</button>
            </form>
        </div>

        <!-- Security Settings Section -->
        <div id="security-settings" class="form-section">
            <h2>Security Settings</h2>
            <form method="POST" action="save_security_settings.php">
                <label for="min-password-length">Minimum Password Length:</label>
                <select id="min-password-length" name="min_password_length">
                    <option value="6" <?php if ($security_settings['min_password_length'] == 6) echo 'selected'; ?>>6</option>
                    <option value="8" <?php if ($security_settings['min_password_length'] == 8) echo 'selected'; ?>>8</option>
                    <option value="10" <?php if ($security_settings['min_password_length'] == 10) echo 'selected'; ?>>10</option>
                    <option value="12" <?php if ($security_settings['min_password_length'] == 12) echo 'selected'; ?>>12</option>
                    <option value="15" <?php if ($security_settings['min_password_length'] == 15) echo 'selected'; ?>>15</option>
                </select>

                <label for="complexity-requirements">Complexity Requirements:</label>
                <div id="complexity-requirements">
                    <input type="checkbox" name="complexity_requirements[]" value="Uppercase" <?php if ($security_settings['require_uppercase']) echo 'checked'; ?>> Uppercase Letters<br>
                    <input type="checkbox" name="complexity_requirements[]" value="Special Characters" <?php if ($security_settings['require_special_char']) echo 'checked'; ?>> Special Characters<br>
                    <input type="checkbox" name="complexity_requirements[]" value="Numbers" <?php if ($security_settings['require_numbers']) echo 'checked'; ?>> Numbers<br>
                </div>

                <label for="password-expiration">Password Expiration Period (days):</label>
                <select id="password-expiration" name="password_expiration">
                    <option value="30" <?php if ($security_settings['password_expiration_days'] == 30) echo 'selected'; ?>>30</option>
                    <option value="60" <?php if ($security_settings['password_expiration_days'] == 60) echo 'selected'; ?>>60</option>
                    <option value="90" <?php if ($security_settings['password_expiration_days'] == 90) echo 'selected'; ?>>90</option>
                    <option value="120" <?php if ($security_settings['password_expiration_days'] == 120) echo 'selected'; ?>>120</option>
                    <option value="0" <?php if ($security_settings['password_expiration_days'] == 0) echo 'selected'; ?>>No Expiration</option>
                </select>

                <button type="submit">Save Security Settings</button>
            </form>
        </div>

        <!-- Notification Settings Section -->
        <div id="notification-settings" class="form-section">
            <h2>Notification Settings</h2>
            <form method="POST" action="save_notification_settings.php">
                <label for="notification-events">Enable Notifications for Events:</label>
                <div id="notification-events">
                    <?php
                    $events = ['Assessment Results', 'System Issues', 'User Feedback'];
                    foreach ($events as $event) {
                        $is_enabled = false;
                        foreach ($notification_settings as $setting) {
                            if ($setting['event_name'] == $event && $setting['is_enabled']) {
                                $is_enabled = true;
                                break;
                            }
                        }
                        echo '<input type="checkbox" name="notification_events[]" value="' . $event . '" ' . ($is_enabled ? 'checked' : '') . '> ' . $event . '<br>';
                    }
                    ?>
                </div>

                <label for="email-template">Email Template:</label>
                <textarea id="email-template" name="email_template" rows="5"><?php echo htmlspecialchars($notification_settings[0]['email_template']); ?></textarea>

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
</body>
</html>
