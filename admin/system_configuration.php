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
        li {
                color: white;
            }

        .content {
            width: 75%;
            float: right;
            padding: 20px;
        }

        .form-section {
            margin-bottom: 20px;
            max-width: 100%;
            box-sizing: border-box;
            overflow-x: hidden;
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
            background-color: var(--button-color-hover);
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
            background-color: var(--background-color-light);
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
            background-color: var(--danger-color-hover);
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
            background-color: var(--button-color-hover);
        }

        main {
            display: flex;
            padding: 40px;
            min-height: calc(100vh - 60px - 200px); 
            background-color: var(--background-color);
        }

        .content {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
            background-color: var(--background-color-medium);
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .content h1 {
            font-size: 2.4rem;
            color: var(--text-color);
            margin-bottom: 40px;
            text-align: center;
        }

        .form-section {
            background-color: var(--background-color);
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 40px;
        }

        .form-section h2 {
            font-size: 1.8rem;
            color: var(--primary-color);
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }

        .form-section label {
            font-size: 1.1rem;
            margin-bottom: 12px;
            color: var(--text-color);
        }

        .form-section select {
            height: 45px;
            margin-bottom: 25px;
            font-size: 1rem;
        }

        .form-section button {
            width: 300px;
            height: 45px;
            margin-top: 20px;
            font-size: 1.1rem;
            font-weight: 500;
            display: block;
            margin-left: auto;
            margin-right: auto;
            transition: all 0.3s ease;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #default-time-limit, #passing-score {
            height: 45px;
            font-size: 1rem;
            background-color: var(--background-color-light);
        }

        @media (max-width: 768px) {
            main {
                padding: 20px;
            }

            .content {
                width: 100%;
                padding: 20px;
                margin-top: 60px;
            }

            .form-section {
                padding: 15px;
            }

            .form-section button {
                width: 100%;
            }

            .content h1 {
                font-size: 1.8rem;
            }

            .form-section h2 {
                font-size: 1.4rem;
            }

            .form-section select {
                height: 40px;
                font-size: 0.9rem;
            }
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
                        <li><a>Settings</a>
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
    <form id="logout-form" action="manage_profile.php" method="post">
        <input type="hidden" name="logout" value="1">
        <button type="submit" class="close-button">Yes</button>
        <button type="button" class="cancel-button" onclick="closePopup('logout-popup')">No</button>
    </form>
</div>
<main>
    <div class="content">
        <h1>System Configuration Settings</h1>

        <div class="form-section">
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