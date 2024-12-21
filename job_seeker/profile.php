<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username']) || !isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$email = $_SESSION['email'];
$user_id = $_SESSION['user_id'];

// Handle logout
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: /Techfit'); // Redirect to the root directory
    exit();
}

// Handle username update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_username'])) {
    $new_username = $_POST['new_username'];

    // Validate new username
    if (preg_match('/^[a-zA-Z0-9_]{5,20}$/', $new_username)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the new username already exists
        $stmt = $conn->prepare("SELECT * FROM User WHERE username=?");
        $stmt->bind_param("s", $new_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Username already exists. Please choose a different username.";
        } else {
            // Update the username for the logged-in user
            $stmt = $conn->prepare("UPDATE User SET username=? WHERE user_id=?");
            $stmt->bind_param("ss", $new_username, $user_id); // Treat user_id as a string
            if ($stmt->execute()) {
                $_SESSION['username'] = $new_username;
                $username = $new_username;
                $success_message = "Username updated successfully.";
            } else {
                $error_message = "Failed to update username: " . $stmt->error;
            }
        }

        $stmt->close();
        $conn->close();
    } else {
        $error_message = "Invalid username. Must be 5-20 characters long and contain only letters, numbers, and underscores.";
    }
}

// Handle email update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_email'])) {
    $new_email = $_POST['new_email'];

    // Validate new email
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the new email already exists
        $stmt = $conn->prepare("SELECT * FROM User WHERE email=?");
        $stmt->bind_param("s", $new_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Email already exists. Please choose a different email.";
        } else {
            // Update the email for the logged-in user
            $stmt = $conn->prepare("UPDATE User SET email=? WHERE user_id=?");
            $stmt->bind_param("ss", $new_email, $user_id); // Treat user_id as a string
            if ($stmt->execute()) {
                $_SESSION['email'] = $new_email;
                $email = $new_email;
                $success_message = "Email updated successfully.";
            } else {
                $error_message = "Failed to update email: " . $stmt->error;
            }
        }

        $stmt->close();
        $conn->close();
    } else {
        $error_message = "Invalid email format.";
    }
}

// Handle password update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];

    // Validate new password
    if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Update the password for the logged-in user
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE User SET password=? WHERE user_id=?");
        $stmt->bind_param("ss", $hashed_password, $user_id); // Treat user_id as a string
        if ($stmt->execute()) {
            $success_message = "Password updated successfully.";
        } else {
            $error_message = "Failed to update password: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        $error_message = "Invalid password. Must be at least 8 characters long and contain at least one letter, one number, and one special character.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Profile</title>
    <link rel="stylesheet" href="styles.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        #profile {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            margin-top: 80px; /* Add space between header and image */
            margin-bottom: 80px; /* Add space between image and footer */
        }
        .profile-image {
            margin-right: 50px; /* Space between image and text */
            margin-left: 50px; /* Space between image and left wall */
            width: auto; /* Ensure the image retains its original width */
            height: auto; /* Ensure the image retains its original height */
            border-radius: 0; /* Remove any border-radius to keep the original shape */
        }
        .profile-details {
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .profile-details h2 {
            margin: 0;
            margin-bottom: 30px;
            font-size: 35px; /* Increase font size */
        }
        .profile-details .detail-line {
            display: flex;
            align-items: center;
            margin-bottom: 20px; /* Space between lines */
        }
        .profile-details .detail-line i {
            margin-right: 10px; /* Space between icon and text */
        }
        .profile-details .detail-line span {
            font-size: 20px; /* Font size for the text */
        }
        .profile-details .edit-button {
            margin-left: 100px; /* Space between text and button */
            padding: 5px 10px; /* Reduce padding */
            font-size: 14px; /* Reduce font size */
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: fit-content; /* Reduce horizontal size */
        }
        .profile-details .edit-button:hover {
            background-color: #0056b3;
        }
        .logout-button {
            margin-top: 20px; /* Space above the logout button */
            padding: 10px 20px;
            font-size: 14px;
            background-color: #dc3545; /* Red background color */
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: fit-content;
        }
        .logout-button:hover {
            background-color: #c82333;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .popup h2 {
            color: #fff; /* Set the heading text color to white */
        }
        .popup input[type="text"],
        .popup input[type="password"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #333;
            color: #fff;
        }
        .popup input[type="submit"] {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
        }
        .popup input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .popup .close-button {
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }
        .popup .close-button:hover {
            background-color: #c82333;
        }
        .popup .cancel-button {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }
        .popup .cancel-button:hover {
            background-color: #0056b3;
        }
        .bottom-edit-button {
            display: flex;
            justify-content: left;
            margin-bottom: 50px; /* Space above the footer */
            margin-left: 290px; /* Move the button 80px to the left */
            margin-top: -50px
            
        }

        .bottom-edit-button:hover {
        background-color: #0056b3;
    }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
        </div>
        <nav>
            <div class="nav-container">
                <ul class="nav-list">
                    <li><a href="#">Assessment</a>
                        <ul class="dropdown">
                            <li><a href="start_assessment.html">Start Assessment</a></li>
                            <li><a href="assessment_history.html">Assessment History</a></li>
                            <li><a href="assessment_summary.html">Assessment Summary</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="useful_links.html">Useful Links</a></li>
                            <li><a href="faq.html">FAQ</a></li>
                            <li><a href="sitemap.html">Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="profile.php" id="profile-link">Profile</a>
                        <ul class="dropdown" id="profile-dropdown">
                            <li><a href="profile.php">Settings</a></li>
                            <li><a href="profile.php">Logout</a></li>
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

    <main>
        <section id="profile">
            <img src="images/testprofile.png" alt="Your Profile" class="profile-image" />
            <div class="profile-details">
                <h2>Edit Profile</h2>
                <?php if (isset($success_message)) { echo '<p class="success-message">' . $success_message . '</p>'; } ?>
                <?php if (isset($error_message)) { echo '<p class="error-message">' . $error_message . '</p>'; } ?>
                <div class="detail-line">
                    <i class="fas fa-user"></i>
                    <span id="username-display"><?php echo htmlspecialchars($username); ?></span>
                    <button class="edit-button" onclick="openPopup('username-popup')"><i class="fas fa-edit"></i> Edit Username</button>
                </div>
                <div class="detail-line">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo htmlspecialchars($email); ?></span>
                    <button class="edit-button" onclick="openPopup('email-popup')"><i class="fas fa-edit"></i> Edit Email</button>
                </div>
                <div class="detail-line">
                    <i class="fas fa-lock"></i>
                    <span>Password</span>
                    <button class="edit-button" onclick="openPopup('password-popup')"><i class="fas fa-edit"></i> Edit Password</button>
                </div>
                <button class="logout-button" onclick="openPopup('logout-popup')">Logout</button>
            </div>
        </section>
    </main>

    <!-- Edit Profile Button at the Bottom -->
    <div class="bottom-edit-button">
        <button class="edit-button" onclick="openPopup('edit-profile-popup')"><i class="fas fa-edit"></i> Edit Profile</button>
    </div>

    <div id="username-popup" class="popup">
        <form action="profile.php" method="post">
            <h2>Edit Username</h2>
            <input type="text" name="new_username" placeholder="New Username" required>
            <input type="submit" value="Update">
            <button type="button" class="close-button" onclick="closePopup('username-popup')">Cancel</button>
        </form>
    </div>

    <div id="email-popup" class="popup">
        <form action="profile.php" method="post">
            <h2>Edit Email</h2>
            <input type="text" name="new_email" placeholder="New Email" required>
            <input type="submit" value="Update">
            <button type="button" class="close-button" onclick="closePopup('email-popup')">Cancel</button>
        </form>
    </div>

    <div id="password-popup" class="popup">
        <form action="profile.php" method="post">
            <h2>Edit Password</h2>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="submit" value="Update">
            <button type="button" class="close-button" onclick="closePopup('password-popup')">Cancel</button>
        </form>
    </div>

    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <form id="logout-form" action="profile.php" method="post">
            <input type="hidden" name="logout" value="1">
            <button type="submit" class="close-button">Yes</button>
            <button type="button" class="cancel-button" onclick="closePopup('logout-popup')">No</button>
        </form>
    </div>

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
                    <h3>Assessment</h3>
                    <ul>
                        <li><a href="start_assessment.html">Start Assessment</a></li>
                        <li><a href="assessment_history.html">Assessment History</a></li>
                        <li><a href="assessment_summary.html">Assessment Summary</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="resources.html">Resources</a></li>
                        <li><a href="about.html">About</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul>
                        <li><a href="contact.html">Contact Us</a></li>
                        <li><a href="feedback.html">Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="terms.html">Terms of Service</a></li>
                        <li><a href="privacy.html">Privacy Policy</a></li>
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