<?php
session_start(); // Start the session to access session variables

// Function to display the message and options
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

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); // Display message and options if not logged in
}

// Check if the user has the correct role
if ($_SESSION['role'] !== 'Job Seeker') {
    displayLoginMessage(); // Display message and options if the role is not Job Seeker
}

// Check if the job seeker ID is set
if (!isset($_SESSION['job_seeker_id'])) {
    displayLoginMessage(); // Display message and options if job seeker ID is not set
}

// Close the session
session_write_close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment History - TechFit</title>
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
            --lighter-text-color: #f5f5f5; /* Lighter White */
        }

        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            color: var(--text-color);
            background-color: var(--background-color);
        }

        main {
            padding: 20px;
        }

        /* Popup Styles */
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
            color: var(--lighter-text-color);
        }
        .popup button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        .popup .close-button {
            background-color: var(--danger-color);
            color: var(--hover-text-color);
        }
        .popup .cancel-button {
            background-color: var(--primary-color);
            color: var(--hover-text-color);
        }
        .popup .close-button:hover {
            background-color: var(--danger-hover-color);
        }
        .popup .cancel-button:hover {
            background-color: var(--accent-color);
        }

        /* Start Assessment */
        #start-assessment-container {
            background-color: var(--secondary-color);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 700px;
            margin: 20px auto;
            text-align: center;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        #start-assessment-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 30px;
            color: var(--lighter-text-color);
        }

        #start-assessment-container h2 {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--lighter-text-color);
        }

        #start-assessment-container h3 {
            font-size: 1.6rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: var(--lighter-text-color);
        }

        #start-assessment-rules {
            text-align: left;
            font-size: 1.0rem;
            color: var(--text-color);
            margin-bottom: 40px;
        }

        #start-assessment-rules ul {
            list-style-type: disc;
            margin-left: 20px;
            margin-bottom: 30px;
        }

        #agree-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            font-size: 1.1rem;
            color: var(--text-color);
        }

        #agree-checkbox input {
            margin-right: 15px;
        }

        #start-assessment-button {
            background-color: var(--primary-color);
            color: var(--hover-text-color);
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            align-self: flex-end;
            margin-top: auto;
            font-size: 1.1rem;
        }

        #start-assessment-button:disabled {
            background-color: var(--border-color);
            cursor: not-allowed;
        }

        #start-assessment-button:hover:not(:disabled) {
            background-color: var(--button-hover-color);
            color: var(--hover-text-color);
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
                                    // Check if the user is logged in and display their username
                                    if (isset($_SESSION['username'])) {
                                        echo $_SESSION['username'];  // Display the username from session
                                    } else {
                                        echo "Guest";  // Default if not logged in
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

    <!-- Logout Popup -->
    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <button class="close-button" onclick="logoutUser()">Yes</button>
        <button class="cancel-button" onclick="closePopup('logout-popup')">No</button>
    </div>

    <section id="start-assessment-container">
        <h2 id="start-assessment-title">Assessment</h2>
        <div id="start-assessment-rules">
            <h3>Rules and Regulations</h3>
            <p>Please read the following rules and regulations carefully before starting the assessment:</p>
            <ul>
                <li>Ensure you have a stable internet connection.</li>
                <li>Do not refresh the page during the assessment.</li>
                <li>Answer all questions to the best of your ability.</li>
                <li>Do not use any external resources or assistance.</li>
                <li>Complete the assessment within the given time frame.</li>
            </ul>
        </div>
        <div id="agree-checkbox">
            <input type="checkbox" id="agree" name="agree">
            <label for="agree">I have read and understood the rules and regulations.</label>
        </div>
        <button id="start-assessment-button" disabled>Start Assessment</button>
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
    <script src="scripts.js"></script>
    <script>
        function openPopup(popupId) {
            document.getElementById(popupId).style.display = 'block';
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        function logoutUser() {
            window.location.href = '/Techfit'; // Redirect to the root directory
        }
    </script>
</body>
</html>