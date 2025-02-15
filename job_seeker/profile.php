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


if ($_SESSION['role'] !== 'Job Seeker') {
    displayLoginMessage(); 
}


if (!isset($_SESSION['job_seeker_id'])) {
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

    // Regular expression to validate LinkedIn URLs
    $linkedin_pattern = '/^https?:\/\/(?:www\.)?linkedin\.com\/(?:in|profile)\/[A-Za-z0-9\-]+\/?$/';
    
    if (filter_var($new_linkedin, FILTER_VALIDATE_URL) && preg_match($linkedin_pattern, $new_linkedin)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("UPDATE Job_Seeker SET linkedin_link=? WHERE user_id=?");
        $stmt->bind_param("ss", $new_linkedin, $user_id); 
        if ($stmt->execute()) {
            $success_message = "LinkedIn profile updated successfully.";
        } else {
            $error_message = "Failed to update LinkedIn profile: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        $error_message = "Invalid LinkedIn URL. Please enter a valid LinkedIn profile URL (e.g., https://www.linkedin.com/in/username)";
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_resume'])) {
    $new_resume = $_POST['new_resume'];

    
    if (filter_var($new_resume, FILTER_VALIDATE_URL)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        
        $stmt = $conn->prepare("UPDATE Job_Seeker SET resume=? WHERE user_id=?");
        $stmt->bind_param("ss", $new_resume, $user_id); 
        if ($stmt->execute()) {
            $success_message = "Resume link updated successfully.";
        } else {
            $error_message = "Failed to update resume link: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        $error_message = "Invalid resume URL.";
    }
}


$conn = new mysqli("localhost", "root", "", "techfit");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt = $conn->prepare("SELECT linkedin_link FROM Job_Seeker WHERE user_id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$linkedin_link = $result->fetch_assoc()['linkedin_link'];
$stmt->close();
$conn->close();

$conn = new mysqli("localhost", "root", "", "techfit");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt = $conn->prepare("SELECT linkedin_link, resume FROM Job_Seeker WHERE user_id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$linkedin_link = $row['linkedin_link'];
$resume = $row['resume'];
$stmt->close();
$conn->close();

$conn = new mysqli("localhost", "root", "", "techfit");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt = $conn->prepare("SELECT education_level FROM Job_Seeker WHERE user_id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$education_level = $row['education_level'];
$stmt->close();
$conn->close();

$conn = new mysqli("localhost", "root", "", "techfit");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$stmt = $conn->prepare("SELECT year_of_experience FROM Job_Seeker WHERE user_id=?");
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$year_of_experience = $row['year_of_experience'];
$stmt->close();
$conn->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['resume_file'])) {
    $resume_file = $_FILES['resume_file'];

    
    $allowed_types = ['application/pdf'];
    if (in_array($resume_file['type'], $allowed_types)) {
        $upload_dir = 'job_seeker/resumes/';
        
        
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = 'resume_' . $user_id . '.pdf';
        $upload_path = $upload_dir . $file_name;

        
        if (move_uploaded_file($resume_file['tmp_name'], $upload_path)) {
            $conn = new mysqli("localhost", "root", "", "techfit");
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            
            $stmt = $conn->prepare("UPDATE Job_Seeker SET resume=? WHERE user_id=?");
            $stmt->bind_param("ss", $file_name, $user_id); 
            if ($stmt->execute()) {
                $success_message = "Resume uploaded successfully.";
                echo "<script>updateResumeLink('$file_name');</script>";
            } else {
                $error_message = "Failed to update resume link: " . $stmt->error;
            }

            $stmt->close();
            $conn->close();
        } else {
            $error_message = "Failed to upload file.";
        }
    } else {
        $error_message = "Invalid file type. Only PDF files are allowed.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_education'])) {
    $new_education = $_POST['new_education'];
    
    $allowed_education_levels = [
        'No Formal Education',
        'Primary School',
        'High School',
        'Diploma / Foundation',
        'Bachelor\'s Degree',
        'Master\'s Degree',
        'Doctorate (Ph.D.)'
    ];

    if (in_array($new_education, $allowed_education_levels)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("UPDATE Job_Seeker SET education_level=? WHERE user_id=?");
        $stmt->bind_param("ss", $new_education, $user_id);
        
        $response = [];
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Education level updated successfully.";
            if (!headers_sent()) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Failed to update education level: " . $stmt->error;
            if (!headers_sent()) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        }

        $stmt->close();
        $conn->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_experience'])) {
    $new_experience = $_POST['new_experience'];
    
    if (is_numeric($new_experience) && $new_experience >= 0 && floor($new_experience) == $new_experience) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $stmt = $conn->prepare("UPDATE Job_Seeker SET year_of_experience=? WHERE user_id=?");
        $stmt->bind_param("is", $new_experience, $user_id);
        
        $response = [];
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Years of experience updated successfully.";
            if (!headers_sent()) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Failed to update years of experience: " . $stmt->error;
            if (!headers_sent()) {
                header('Content-Type: application/json');
                echo json_encode($response);
                exit;
            }
        }

        $stmt->close();
        $conn->close();
    } else {
        $response['success'] = false;
        $response['message'] = "Invalid input. Please enter a non-negative whole number.";
        if (!headers_sent()) {
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Profile - TechFit</title>
    <link rel="stylesheet" href="styles.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        color: #e0e0e0;
        background-color: #121212;
    }

    #profile {
        display: flex;
        justify-content: flex-start; /* Changed from center */
        margin-top: 80px;
        margin-bottom: 80px;
        padding-left: 100px; /* Add left padding */
    }
    .profile-details {
        display: flex;
        flex-direction: column;
        justify-content: flex-start; /* Changed from center */
        max-width: 800px; /* Optional: Add max-width to prevent content from stretching too wide */
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
   
    .profile-info .profile-image {
        margin-left: 10px;
        width: 30px;
        height: 30px;
        border-radius: 20%;
    }
    .nav-container {
        display: flex;
        justify-content: flex-end;
        padding-right: -50px;
    }

   
    .nav-list {
        list-style: none;
        display: flex;
        justify-content: flex-end;
        margin: 0;
        padding: 0;
    }
    .education-options {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin: 20px 0;
    }
    .education-option {
        padding: 10px;
        border: 1px solid #444;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        background-color: #333;
        color: #fff;
    }

    .education-option:hover {
        background-color: #444;
    }

    .education-option.selected {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    .experience-input {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 20px 0;
    }

    .experience-input input[type="number"] {
        width: 80px;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #333;
        color: #fff;
    }
    @media (max-width: 768px) {
        .hamburger {
            display: flex;
            position: fixed;
            top: 25px;
            right: 20px;
            z-index: 1002; /* Ensure it's above the nav-list */
        }

        .nav-list {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            position: fixed;
            top: 0;
            right: -100%;
            height: 100%;
            background-color: var(--background-color);
            width: 350px;
            z-index: 1000;
            padding: 60px 20px;
            box-shadow: -2px 0 5px rgba(0, 0, 0, 0.5);
            justify-content: flex-start;
            transition: right 0.3s ease;
            margin-top: 0; /* Reset any margin */
        }

        .nav-list.active {
            right: 0;
        }

        .nav-list li {
            width: 100%;
            margin: 5px 0;
        }

        .nav-container {
            padding-right: 0;
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
                <div class="detail-line">
                    <i class="fas fa-graduation-cap"></i>
                    <span><?php echo $education_level ? htmlspecialchars($education_level) : 'Education Level'; ?></span>
                    <button class="edit-button" onclick="openPopup('education-popup')"><i class="fas fa-edit"></i> Edit Education Level</button>
                </div>
                <div class="detail-line">
                    <i class="fas fa-briefcase"></i>
                    <span><?php 
                        if ($year_of_experience !== null) {
                            echo htmlspecialchars($year_of_experience) . ' Years of Experience';
                        } else {
                            echo 'Years of Working Experience';
                        }
                    ?></span>
                    <button class="edit-button" onclick="openPopup('experience-popup')">
                        <i class="fas fa-edit"></i> Edit Years of Experience
                    </button>
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
                <div class="detail-line" style="margin-bottom: 20px;">
                    <i class="fas fa-file-alt"></i>
                    <span>Resume</span>
                    <button class="edit-button" onclick="openPopup('resume-popup')"><i class="fas fa-edit"></i> Edit Resume</button>
                </div>
                <div class="detail-line" id="resume-link-container">
                    <?php if ($resume): ?>
                    <a href="job_seeker/resumes/<?php echo htmlspecialchars($resume); ?>" target="_blank" style="color: #007bff;">View Resume</a>
                    <?php endif; ?>
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

    <div id="resume-popup" class="popup">
        <form action="profile.php" method="post" enctype="multipart/form-data">
            <h2>Edit Resume</h2>
            <input type="file" name="resume_file" accept=".pdf" required>
            <input type="submit" value="Update">
            <button type="button" class="close-button" onclick="closePopup('resume-popup')">Cancel</button>
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

    <div id="education-popup" class="popup">
        <form action="profile.php" method="post">
            <h2>Select Education Level</h2>
            <div class="education-options">
                <?php
                $education_levels = [
                    'No Formal Education',
                    'Primary School',
                    'High School',
                    'Diploma / Foundation',
                    'Bachelor\'s Degree',
                    'Master\'s Degree',
                    'Doctorate (Ph.D.)'
                ];
                foreach ($education_levels as $level) {
                    $selected = ($education_level === $level) ? 'selected' : '';
                    echo "<div class='education-option $selected' onclick='selectEducation(this)' data-value='" . htmlspecialchars($level) . "'>" . htmlspecialchars($level) . "</div>";
                }
                ?>
            </div>
            <input type="hidden" name="new_education" id="selected_education">
            <input type="submit" value="Update">
            <button type="button" class="close-button" onclick="closePopup('education-popup')">Cancel</button>
        </form>
    </div>
    <div id="experience-popup" class="popup">
        <form action="profile.php" method="post" id="experience-form">
            <h2>Edit Years of Experience</h2>
            <div class="experience-input">
                <input type="number" name="new_experience" id="new_experience" min="0" step="1" required> Years
            </div>
            <input type="submit" value="Update">
            <button type="button" class="close-button" onclick="closePopup('experience-popup')">Cancel</button>
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

        function updateResumeLink(fileName) {
            const resumeLinkContainer = document.getElementById('resume-link-container');
            resumeLinkContainer.innerHTML = `<a href="job_seeker/resumes/${fileName}" target="_blank" style="color: #007bff;">View Resume</a>`;
        }
        function updateEducationLevel(newLevel) {
            const educationSpan = document.querySelector('.detail-line i.fa-graduation-cap').nextElementSibling;
            educationSpan.textContent = newLevel || 'Education Level';
        }

        function selectEducation(element) {
            // Remove selected class from all options
            document.querySelectorAll('.education-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Update hidden input value
            document.getElementById('selected_education').value = element.dataset.value;
        }

        // Modify your existing education popup form submission to use AJAX:
        document.querySelector('#education-popup form').addEventListener('submit', function(e) {
            e.preventDefault();
            const newEducation = document.getElementById('selected_education').value;
            
            fetch('profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'new_education=' + encodeURIComponent(newEducation)
            })
            .then(response => response.text())
            .then(data => {
                updateEducationLevel(newEducation);
                closePopup('education-popup');
                // Optional: Show success message
                const successMessage = document.createElement('p');
                successMessage.className = 'success-message';
                successMessage.textContent = 'Education level updated successfully.';
                document.querySelector('.profile-details').insertBefore(successMessage, document.querySelector('.detail-line'));
                setTimeout(() => successMessage.remove(), 3000);
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
        // Add to your existing script section
        function updateExperience(years) {
            const experienceSpan = document.querySelector('.detail-line i.fa-briefcase').nextElementSibling;
            experienceSpan.textContent = years ? `${years} Years of Experience` : 'Years of Working Experience';
        }

        // Add this to handle the form submission
        document.querySelector('#experience-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const newExperience = document.getElementById('new_experience').value;
            
            fetch('profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'new_experience=' + encodeURIComponent(newExperience)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateExperience(newExperience);
                    closePopup('experience-popup');
                    // Show success message
                    const successMessage = document.createElement('p');
                    successMessage.className = 'success-message';
                    successMessage.textContent = data.message;
                    document.querySelector('.profile-details').insertBefore(successMessage, document.querySelector('.detail-line'));
                    setTimeout(() => successMessage.remove(), 3000);
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        });
        // Add hamburger menu functionality
        document.addEventListener('DOMContentLoaded', function() {
            const hamburger = document.getElementById('hamburger');
            const navList = document.querySelector('.nav-list');
            
            hamburger.addEventListener('click', function() {
                hamburger.classList.toggle('active');
                navList.classList.toggle('active');
            });

            // Close menu when clicking outside
            document.addEventListener('click', function(e) {
                if (!hamburger.contains(e.target) && !navList.contains(e.target)) {
                    hamburger.classList.remove('active');
                    navList.classList.remove('active');
                }
            });

            // Handle dropdown menus in mobile view
            const dropdownParents = document.querySelectorAll('.nav-list li:has(.dropdown)');
            dropdownParents.forEach(parent => {
                parent.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        const dropdown = this.querySelector('.dropdown');
                        const allDropdowns = document.querySelectorAll('.nav-list .dropdown');
                        
                        allDropdowns.forEach(d => {
                            if (d !== dropdown) {
                                d.parentElement.classList.remove('active');
                            }
                        });
                        
                        this.classList.toggle('active');
                        e.stopPropagation();
                    }
                });
            });
        });
    </script>
</body>
</html>