<?php
session_start(); 


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


if ($_SESSION['role'] !== 'Employer') {
    displayLoginMessage(); 
}


session_write_close();
?>

<?php
session_start();


if (!isset($_SESSION['username']) || !isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
$email = $_SESSION['email'];
$user_id = $_SESSION['user_id'];


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header('Location: /Techfit'); 
    exit();
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_username'])) {
    $new_username = $_POST['new_username'];

    
    if (preg_match('/^[a-zA-Z0-9_]{5,20}$/', $new_username)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        
        $stmt = $conn->prepare("SELECT * FROM User WHERE username=?");
        $stmt->bind_param("s", $new_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Username already exists. Please choose a different username.";
        } else {
            
            $stmt = $conn->prepare("UPDATE User SET username=? WHERE user_id=?");
            $stmt->bind_param("ss", $new_username, $user_id); 
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


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_email'])) {
    $new_email = $_POST['new_email'];

    
    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        
        $stmt = $conn->prepare("SELECT * FROM User WHERE email=?");
        $stmt->bind_param("s", $new_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Email already exists. Please choose a different email.";
        } else {
            
            $stmt = $conn->prepare("UPDATE User SET email=? WHERE user_id=?");
            $stmt->bind_param("ss", $new_email, $user_id); 
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


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];

    
    if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE User SET password=? WHERE user_id=?");
        $stmt->bind_param("ss", $hashed_password, $user_id); 
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


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_linkedin'])) {
    $new_linkedin = $_POST['new_linkedin'];

    
    if (filter_var($new_linkedin, FILTER_VALIDATE_URL)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        
        $stmt = $conn->prepare("UPDATE Employer SET linkedin_link=? WHERE user_id=?");
        $stmt->bind_param("ss", $new_linkedin, $user_id); 
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


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_company_name'])) {
    $new_company_name = $_POST['new_company_name'];

    
    if (!empty($new_company_name)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        
        $stmt = $conn->prepare("UPDATE Employer SET company_name=? WHERE user_id=?");
        $stmt->bind_param("ss", $new_company_name, $user_id); 
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


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_company_type'])) {
    $new_company_type = $_POST['new_company_type'];

    
    if (!empty($new_company_type)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        
        $stmt = $conn->prepare("UPDATE Employer SET company_type=? WHERE user_id=?");
        $stmt->bind_param("ss", $new_company_type, $user_id); 
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
    <title>Employer Profile - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        color: #e0e0e0;
        background-color: #121212;
    }

    #profile {
        display: flex;
        align-items: flex-start;
        justify-content: flex-start;
        margin-top: 80px;
        margin-bottom: 80px;
    }
    .profile-details {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .profile-details h2 {
        margin: 0;
        margin-bottom: 30px;
        font-size: 35px;
        color: #e0e0e0;
    }
    .profile-details .detail-line {
        display: flex;
        align-items: center;
        margin-bottom: 20px;
    }
    .profile-details .detail-line i {
        margin-right: 10px;
    }
    .profile-details .detail-line span,
    .profile-details .detail-line a {
        font-size: 20px;
        color: #e0e0e0;
    }
    .profile-details .edit-button {
        margin-left: 100px;
        padding: 5px 10px;
        font-size: 14px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: fit-content;
    }
    .profile-details .edit-button:hover {
        background-color: #0056b3;
    }
    .logout-button {
        margin-top: 20px;
        padding: 10px 20px;
        font-size: 14px;
        background-color: #dc3545;
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
        color: #fff;
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
        margin-bottom: 50px;
        margin-left: 290px;
        margin-top: -50px;
    }
    .bottom-edit-button:hover {
        background-color: #0056b3;
    }

    @media (max-width: 768px) {
        /* Profile section styles */
        #profile {
            margin: 20px 0;
            padding: 10px;
            flex-direction: column;
        }

        .profile-details {
            padding-left: 20px !important;
            width: 100%;
        }

        .profile-details h2 {
            font-size: 24px;
            text-align: center;
        }

        .detail-line {
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-start;
            width: 100%;
            margin-bottom: 15px;
        }

        .detail-line span, 
        .detail-line a {
            font-size: 16px;
            width: 100%;
            order: 2;
        }

        .detail-line i,
        .detail-line img {
            order: 1;
        }

        .edit-button {
            margin-left: 0 !important;
            width: 100% !important;
            margin-top: 5px;
            order: 3;
        }

        .logout-button {
            width: 100%;
            margin-top: 30px;
        }

        /* Popup styles */
        .popup {
            width: 90%;
            max-width: 350px;
        }

        .popup input[type="text"],
        .popup input[type="password"] {
            width: 100%;
        }

        .popup form {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .popup input[type="submit"],
        .popup .close-button,
        .popup .cancel-button {
            width: 100%;
        }

        /* Navigation styles */
        .nav-container {
            position: relative;
        }

        .nav-list {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: fixed;
            top: 0;
            right: -100%;
            height: 100%;
            background-color: var(--background-color-medium);
            width: 350px;
            z-index: 1000;
            padding: 60px 20px;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5);
            justify-content: flex-start;
            transition: right 0.3s ease;
            margin-top: 0;
        }

        .nav-list.active {
            right: 0;
        }

        .nav-list li {
            width: 100%;
            margin: 5px 0;
        }

        .nav-list .dropdown {
            position: static;
            background-color: var(--background-color);
            margin-top: 5px;
            border-radius: 0;
            box-shadow: none;
            width: 100%;
            display: none;
        }

        .nav-list li.active > .dropdown {
            display: block;
        }

        .hamburger {
            display: flex;
            position: fixed;
            top: 25px;
            right: 20px;
            z-index: 1002;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
        }

        .hamburger span {
            display: block;
            width: 25px;
            height: 3px;
            background-color: var(--text-color);
            transition: 0.3s;
        }

        .hamburger.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .hamburger.active span:nth-child(2) {
            opacity: 0;
        }

        .hamburger.active span:nth-child(3) {
            transform: rotate(-45deg) translate(5px, -5px);
        }

        /* Additional responsive fixes */
        .bottom-edit-button {
            margin-left: 0;
            margin-top: 10px;
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 480px) {
        .profile-details {
            padding-left: 10px !important;
        }

        .detail-line i {
            margin-right: 5px;
        }

        .popup {
            padding: 15px;
        }

        .popup h2 {
            font-size: 18px;
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
            <div class="detail-line">
                <i class="fas fa-building"></i>
                <span><?php echo htmlspecialchars($company_name ? $company_name : 'Company Name'); ?></span>
                <button class="edit-button" onclick="openPopup('company-name-popup')"><i class="fas fa-edit"></i> Edit Company Name</button>
            </div>
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

<div id="linkedin-popup" class="popup">
    <form action="profile.php" method="post">
        <h2>Edit LinkedIn Profile</h2>
        <input type="text" name="new_linkedin" placeholder="LinkedIn Profile URL" required>
        <input type="submit" value="Update">
        <button type="button" class="close-button" onclick="closePopup('linkedin-popup')">Cancel</button>
    </form>
</div>

<div id="company-name-popup" class="popup">
    <form action="profile.php" method="post">
        <h2>Edit Company Name</h2>
        <input type="text" name="new_company_name" placeholder="New Company Name" required>
        <input type="submit" value="Update">
        <button type="button" class="close-button" onclick="closePopup('company-name-popup')">Cancel</button>
    </form>
</div>

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
                    <p><a href="mailto:/a></p>techfit@gmail.com">techfit@gmail.com</a></p>
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