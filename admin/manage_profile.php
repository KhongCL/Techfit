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

$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';


if (empty($email)) {
    $conn = new mysqli("localhost", "root", "", "techfit");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT email FROM User WHERE user_id=?");
    $stmt->bind_param("s", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $_SESSION['email'] = $email;
    }

    $stmt->close();
    $conn->close();
}

$username = $_SESSION['username'];
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$user_id = $_SESSION['user_id'];


if (empty($email)) {
    $conn = new mysqli("localhost", "root", "", "techfit");
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("SELECT email FROM User WHERE user_id=?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $email = $row['email'];
        $_SESSION['email'] = $email;
    }

    $stmt->close();
    $conn->close();
}


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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
    #profile {
        display: flex;
        justify-content: flex-start;
        margin-top: 80px;
        margin-bottom: 80px;
        padding-left: 100px;
    }

    .profile-details {
        display: flex;
        flex-direction: column;
        justify-content: flex-start;
        max-width: 800px;
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
        margin-left: 50px;
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

    .separate-config-button {
        margin-top: 20px;
        padding: 10px 20px;
        font-size: 14px;
        background-color: var(--primary-color);
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
    .popup input[type="password"],
    .popup input[type="email"] {
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

    .profile-info .profile-image {
        margin-left: 10px;
        width: 30px;
        height: 30px;
        border-radius: 20%;
    }


    .success-message,
    .error-message {
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-weight: bold;
        width: 100%;
    }

    .success-message {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
        border: 1px solid #28a745;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-weight: bold;
        width: 100%;
    }

    .error-message {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid #dc3545;
        padding: 10px;
        margin-bottom: 20px;
        border-radius: 5px;
        font-weight: bold;
        width: 100%;
    }

    .popup .success-message,
    .popup .error-message {
        margin: 10px 0;
        padding: 10px;
        border-radius: 5px;
        font-size: 14px;
        width: 100%;
    }

    .popup .success-message {
        background-color: rgba(40, 167, 69, 0.2);
        color: #28a745;
        border: 1px solid #28a745;
    }

    .popup .error-message {
        background-color: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid #dc3545;
    }

    .button-container {
        display: flex;
        gap: 15px;
    }

    .separate-config-button {
        padding: 10px 20px;
    }


    @media (max-width: 768px) {
        #profile {
            margin: 20px 0;
            padding: 10px;
            flex-direction: column;
            padding-left: 0;
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

        .logout-button,
        .separate-config-button {
            width: 100%;
            margin-top: 10px;
        }

        .button-container {
            flex-direction: column;
            gap: 10px;
        }


        .popup {
            width: 90%;
            max-width: 350px;
        }

        .popup input[type="text"],
        .popup input[type="password"],
        .popup input[type="email"] {
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
        <div class="profile-details">
            <h2>Edit Profile</h2>
            <div id="profile-message-container">
                <?php if (isset($success_message)): ?>
                    <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
                <?php endif; ?>
                <?php if (isset($error_message)): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
            </div>
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
            <div class="button-container">
                <button class="logout-button" onclick="openPopup('logout-popup')">Logout</button>
                <button class="config-button separate-config-button" onclick="window.location.href='system_configuration.php'">Edit System Configuration Settings</button>
            </div>
        </div>
    </section>
</main>

<div id="username-popup" class="popup">
    <form id="username-form" action="manage_profile.php" method="post">
        <h2>Edit Username</h2>
        <div id="username-message" class="message-container"></div>
        <input type="text" name="new_username" placeholder="New Username" required>
        <input type="submit" value="Update">
        <button type="button" class="close-button" onclick="closePopup('username-popup')">Cancel</button>
    </form>
</div>

<div id="email-popup" class="popup">
    <form id="email-form" action="manage_profile.php" method="post">
        <h2>Edit Email</h2>
        <div id="email-message" class="message-container"></div>
        <input type="email" name="new_email" placeholder="New Email" required>
        <input type="submit" value="Update">
        <button type="button" class="close-button" onclick="closePopup('email-popup')">Cancel</button>
    </form>
</div>

<div id="password-popup" class="popup">
    <form id="password-form" action="manage_profile.php" method="post">
        <h2>Edit Password</h2>
        <div id="password-message" class="message-container"></div>
        <input type="password" name="new_password" placeholder="New Password" required>
        <input type="submit" value="Update">
        <button type="button" class="close-button" onclick="closePopup('password-popup')">Cancel</button>
    </form>
</div>

<div id="logout-popup" class="popup">
    <h2>Are you sure you want to Log Out?</h2>
    <form id="logout-form" action="manage_profile.php" method="post">
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
        removeMessages(popupId); 
    }

    function logoutUser() {
        document.getElementById('logout-form').submit();
    }

    function showPageMessage(message, type) {
        const messageDiv = document.createElement('p');
        messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
        messageDiv.innerHTML = message;

        const profileDetails = document.querySelector('.profile-details');
        const firstDetailLine = profileDetails.querySelector('.detail-line');

        
        profileDetails.insertBefore(messageDiv, firstDetailLine);
        messageDiv.offsetHeight; 

        
        if (type === 'success') {
            
            if (window.messageTimeout) {
                clearTimeout(window.messageTimeout);
            }

            
            window.messageTimeout = setTimeout(() => {
                if (messageDiv && messageDiv.parentNode) {
                    messageDiv.remove();
                }
            }, 3000); 
        }

        
        return messageDiv;
    }

    function validateProfileUpdate(type, value) {
        let errorMessage = "";
        let isValid = true;

        const popupTypeMap = {
            'username-popup': 'username',
            'email-popup': 'email',
            'password-popup': 'password'
        };

        
        const validationType = popupTypeMap[type] || type;

        switch(validationType) {
            case 'username':
                
                if (!/^[a-zA-Z0-9_]{5,20}$/.test(value)) {
                    errorMessage = "Username requirements:<br>" +
                        "- Length: 5-20 characters<br>" +
                        "- Allowed characters: letters (case-sensitive), numbers, underscore<br>" +
                        "- No spaces or special characters allowed";
                    isValid = false;
                }
                break;

            case 'email':
                if (!/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/.test(value)) {
                    errorMessage = "Invalid email format. Please enter a valid email address:<br>" +
                        "- Must contain @ symbol<br>" +
                        "- Must have valid domain (e.g., example.com)<br>" +
                        "- No spaces allowed";
                    isValid = false;
                }
                break;

            case 'password':
                const passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
                if (!passwordRegex.test(value)) {
                    errorMessage = "Password requirements:<br>" +
                        "- Minimum 8 characters long<br>" +
                        "- At least 1 letter (a-z, A-Z)<br>" +
                        "- At least 1 number<br>" +
                        "- At least 1 special character (@$!%*?&)<br>" +
                        "- No spaces allowed";
                    isValid = false;
                }
                break;
        }

        return { isValid, errorMessage };
    }

    function showError(message, popupId) {
        removeMessages(popupId); 
        const messageContainer = document.querySelector(`#${popupId} .message-container`);
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.innerHTML = message;
        messageContainer.appendChild(errorDiv);
    }

    function removeMessages(popupId) {
        const messageContainer = document.querySelector(`#${popupId} .message-container`);
        messageContainer.innerHTML = ''; 
    }

    
    document.querySelectorAll('.popup form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const popupId = this.closest('.popup').id;
            let input = this.querySelector('input[type="text"], input[type="password"], input[type="email"]');
            if (!input) return;

            let inputType = input.name.replace('new_', '');
            let validation = validateProfileUpdate(popupId, input.value);

            if (!validation.isValid) {
                showError(validation.errorMessage, popupId);
                return;
            }

            
            this.submit(); 
        });
    });
</script>
</body>
</html>