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
if ($_SESSION['role'] !== 'Employer') {
    displayLoginMessage(); // Display message and options if the role is not Employer
}

// Close the session
session_write_close();
?>

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

// Handle LinkedIn link update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_linkedin'])) {
    $new_linkedin = $_POST['new_linkedin'];

    // Validate LinkedIn URL
    if (filter_var($new_linkedin, FILTER_VALIDATE_URL)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Update the LinkedIn link for the logged-in user
        $stmt = $conn->prepare("UPDATE Employer SET linkedin_link=? WHERE user_id=?");
        $stmt->bind_param("ss", $new_linkedin, $user_id); // Treat user_id as a string
        if ($stmt->execute()) {
            $success_message = "LinkedIn profile updated successfully.";
        } else {
            $error_message = "Failed to update LinkedIn profile: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        $error_message = "Invalid LinkedIn URL.";
    }
}

// Handle company name update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_company_name'])) {
    $new_company_name = $_POST['new_company_name'];

    // Validate company name
    if (!empty($new_company_name)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Update the company name for the logged-in user
        $stmt = $conn->prepare("UPDATE Employer SET company_name=? WHERE user_id=?");
        $stmt->bind_param("ss", $new_company_name, $user_id); // Treat user_id as a string
        if ($stmt->execute()) {
            $success_message = "Company name updated successfully.";
        } else {
            $error_message = "Failed to update company name: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        $error_message = "Invalid company name.";
    }
}

// Handle company type update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_company_type'])) {
    $new_company_type = $_POST['new_company_type'];

    // Validate company type
    if (!empty($new_company_type)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Update the company type for the logged-in user
        $stmt = $conn->prepare("UPDATE Employer SET company_type=? WHERE user_id=?");
        $stmt->bind_param("ss", $new_company_type, $user_id); // Treat user_id as a string
        if ($stmt->execute()) {
            $success_message = "Company type updated successfully.";
        } else {
            $error_message = "Failed to update company type: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        $error_message = "Invalid company type.";
    }
}

// Fetch LinkedIn link, company name, and company type for display
$conn = new mysqli("localhost", "root", "", "techfit");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt = $conn->prepare("SELECT linkedin_link, company_name, company_type FROM Employer WHERE user_id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$linkedin_link = $row['linkedin_link'];
$company_name = $row['company_name'];
$company_type = $row['company_type'];
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Employer Profile</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        color: #e0e0e0; /* White color for text */
        background-color: #121212; /* Very Dark Grey */
    }

    #profile {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        margin-top: 80px; /* Add space between header and image */
        margin-bottom: 80px; /* Add space between image and footer */
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
        color: #e0e0e0; /* White color for text */
    }
    .profile-details .detail-line {
        display: flex;
        align-items: center;
        margin-bottom: 20px; /* Space between lines */
    }
    .profile-details .detail-line i {
        margin-right: 10px; /* Space between icon and text */
    }
    .profile-details .detail-line span,
    .profile-details .detail-line a {
        font-size: 20px; /* Font size for the text */
        color: #e0e0e0; /* White color for text */
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
        margin-top: -50px;
    }
    .bottom-edit-button:hover {
        background-color: #0056b3;
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
                <li><a href="#">Candidates</a>
                    <ul class="dropdown">
                        <li><a href="search_candidate.php">Search Candidates</a></li>
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

<main>
    <section id="profile">
        <div class="profile-details" style="padding-left: 50px;">
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
            <!-- New LinkedIn Profile Row -->
            <div class="detail-line">
                <img src="images/linkedin.png" alt="LinkedIn" style="width: 20px; height: 20px; margin-right: 10px;">
                <span>LinkedIn Profile</span>
                <button class="edit-button" onclick="openPopup('linkedin-popup')"><i class="fas fa-edit"></i> Edit Link</button>
            </div>
            <?php if ($linkedin_link): ?>
            <div class="detail-line">
                <a href="<?php echo htmlspecialchars($linkedin_link); ?>" target="_blank" style="color: #007bff;"><?php echo htmlspecialchars($linkedin_link); ?></a>
            </div>
            <?php endif; ?>
            <!-- New Company Name Row -->
            <div class="detail-line">
                <i class="fas fa-building"></i>
                <span><?php echo htmlspecialchars($company_name ? $company_name : 'Company Name'); ?></span>
                <button class="edit-button" onclick="openPopup('company-name-popup')"><i class="fas fa-edit"></i> Edit Company Name</button>
            </div>
            <!-- New Company Type Row -->
            <div class="detail-line">
                <i class="fas fa-industry"></i>
                <span><?php echo htmlspecialchars($company_type ? $company_type : 'Company Type'); ?></span>
                <button class="edit-button" onclick="openPopup('company-type-popup')"><i class="fas fa-edit"></i> Edit Company Type</button>
            </div>
            <button class="logout-button" onclick="openPopup('logout-popup')">Logout</button>
        </div>
    </section>
</main>

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

<!-- New LinkedIn Popup -->
<div id="linkedin-popup" class="popup">
    <form action="profile.php" method="post">
        <h2>Edit LinkedIn Profile</h2>
        <input type="text" name="new_linkedin" placeholder="LinkedIn Profile URL" required>
        <input type="submit" value="Update">
        <button type="button" class="close-button" onclick="closePopup('linkedin-popup')">Cancel</button>
    </form>
</div>

<!-- New Company Name Popup -->
<div id="company-name-popup" class="popup">
    <form action="profile.php" method="post">
        <h2>Edit Company Name</h2>
        <input type="text" name="new_company_name" placeholder="New Company Name" required>
        <input type="submit" value="Update">
        <button type="button" class="close-button" onclick="closePopup('company-name-popup')">Cancel</button>
    </form>
</div>

<!-- New Company Type Popup -->
<div id="company-type-popup" class="popup">
    <form action="profile.php" method="post">
        <h2>Edit Company Type</h2>
        <input type="text" name="new_company_type" placeholder="New Company Type" required>
        <input type="submit" value="Update">
        <button type="button" class="close-button" onclick="closePopup('company-type-popup')">Cancel</button>
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
                <h3>Candidate</h3>
                <ul>
                    <li><a href="search_candidate.php">Search Candidates</a></li>
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