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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    
    <style>
        main {
            position: relative;
            min-height: 70vh;
            padding: 0;
        }

        #home {
            position: relative;
            width: 100%;
            min-height: 70vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 0;
            overflow: hidden;
            background-image: url('images/office_background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        #home::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('images/office_background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(4px);
            z-index: 1;
        }

        #home-content {
            position: relative;
            z-index: 3;
            padding: 2rem;
            background: rgba(0, 0, 0, 0.4);
            width: 100%;
            height: 40vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

        #home h2 {
            font-size: 3rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            margin-bottom: 1rem;
            font-weight: bold;
            letter-spacing: 1px;
        }

        #home p {
            font-size: 1.3rem;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7);
            margin-bottom: 2rem;
            letter-spacing: 0.5px;
        }

        #home button {
            padding: 1rem 2.5rem;
            font-size: 1.2rem;
            border: none;
            border-radius: 5px;
            background-color: var(--primary-color);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }

        #home button:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
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

    <main>
        <section id="home">
            <div id="home-content">
                <h2>Admin Dashboard</h2>
                <p>Welcome, Admin! Use the navigation above to manage assessments, questions, and view results.</p>
                <button onclick="location.href='manage_assessments.php'">Manage Assessments</button>
            </div>
        </section>
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
    <script src="scripts.js"></script>
</body>
</html>