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
    $response = array();
    
    if (preg_match('/^[a-zA-Z0-9_]{5,20}$/', $new_username)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            $response['success'] = false;
            $response['message'] = "Connection failed: " . $conn->connect_error;
            echo json_encode($response);
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM User WHERE username=?");
        $stmt->bind_param("s", $new_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response['success'] = false;
            $response['message'] = "Username already exists. Please choose a different username.";
        } else {
            $stmt = $conn->prepare("UPDATE User SET username=? WHERE user_id=?");
            $stmt->bind_param("ss", $new_username, $user_id);
            if ($stmt->execute()) {
                $_SESSION['username'] = $new_username;
                $username = $new_username;
                $response['success'] = true;
                $response['message'] = "Username updated successfully.";
            } else {
                $response['success'] = false;
                $response['message'] = "Failed to update username: " . $stmt->error;
            }
        }

        $stmt->close();
        $conn->close();
    } else {
        $response['success'] = false;
        $response['message'] = "Invalid username format";
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_email'])) {
    $new_email = $_POST['new_email'];
    $response = array();

    if (filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            $response['success'] = false;
            $response['message'] = "Connection failed: " . $conn->connect_error;
            echo json_encode($response);
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM User WHERE email=?");
        $stmt->bind_param("s", $new_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $response['success'] = false;
            $response['message'] = "Email already exists. Please choose a different email.";
        } else {
            $stmt = $conn->prepare("UPDATE User SET email=? WHERE user_id=?");
            $stmt->bind_param("ss", $new_email, $user_id);
            if ($stmt->execute()) {
                $_SESSION['email'] = $new_email;
                $email = $new_email;
                $response['success'] = true;
                $response['message'] = "Email updated successfully.";
            } else {
                $response['success'] = false;
                $response['message'] = "Failed to update email: " . $stmt->error;
            }
        }

        $stmt->close();
        $conn->close();
    } else {
        $response['success'] = false;
        $response['message'] = "Invalid email format";
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_password'])) {
    $new_password = $_POST['new_password'];
    $response = array();

    if (preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $new_password)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            $response['success'] = false;
            $response['message'] = "Connection failed: " . $conn->connect_error;
            echo json_encode($response);
            exit;
        }

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE User SET password=? WHERE user_id=?");
        $stmt->bind_param("ss", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $response['success'] = true;
            $response['message'] = "Password updated successfully.";
        } else {
            $response['success'] = false;
            $response['message'] = "Failed to update password: " . $stmt->error;
        }

        $stmt->close();
        $conn->close();
    } else {
        $response['success'] = false;
        $response['message'] = "Invalid password format";
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_linkedin'])) {
    $new_linkedin = $_POST['new_linkedin'];
    $linkedin_pattern = '/^https?:\/\/(?:www\.)?linkedin\.com\/(?:in|profile)\/[A-Za-z0-9\-]+\/?$/';
    $response = array();
    
    if (filter_var($new_linkedin, FILTER_VALIDATE_URL) && preg_match($linkedin_pattern, $new_linkedin)) {
        $conn = new mysqli("localhost", "root", "", "techfit");
        if ($conn->connect_error) {
            $response['success'] = false;
            $response['message'] = "Connection failed: " . $conn->connect_error;
        } else {
            // Check for uniqueness first
            $stmt = $conn->prepare("SELECT user_id FROM " . ($_SESSION['role'] === 'Employer' ? 'Employer' : 'Job_Seeker') . " WHERE linkedin_link = ? AND user_id != ?");
            $stmt->bind_param("ss", $new_linkedin, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $response['success'] = false;
                $response['message'] = "This LinkedIn profile is already linked to another account.";
            } else {
                $stmt = $conn->prepare("UPDATE " . ($_SESSION['role'] === 'Employer' ? 'Employer' : 'Job_Seeker') . " SET linkedin_link=? WHERE user_id=?");
                $stmt->bind_param("ss", $new_linkedin, $user_id);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = "LinkedIn profile updated successfully.";
                } else {
                    $response['success'] = false;
                    $response['message'] = "Failed to update LinkedIn profile.";
                }
            }
            $stmt->close();
            $conn->close();
        }
    } else {
        $response['success'] = false;
        $response['message'] = "Invalid LinkedIn URL format.";
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
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
        color: #e0e0e0;
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
    .popup input[type="file"] {
        color: var(--text-color);
        background-color: var(--background-color);
        border: 1px solid var(--background-color-light);
        padding: 8px;
        border-radius: 4px;
        width: 100%;
        margin: 10px 0;
    }

    /* Style the file input button */
    .popup input[type="file"]::-webkit-file-upload-button {
        background-color: var(--primary-color);
        color: var(--text-color);
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin-right: 10px;
    }

    .popup input[type="file"]::-webkit-file-upload-button:hover {
        background-color: var(--primary-color-hover);
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

    .education-options {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin: 20px 0;
        max-height: 250px; /* Set maximum height */
        overflow-y: auto; /* Enable vertical scrolling */
        padding-right: 10px; /* Add padding for scrollbar */
    }

    /* Add custom scrollbar styling */
    .education-options::-webkit-scrollbar {
        width: 8px;
    }

    .education-options::-webkit-scrollbar-track {
        background: #333;
        border-radius: 4px;
    }

    .education-options::-webkit-scrollbar-thumb {
        background: #666;
        border-radius: 4px;
    }

    .education-options::-webkit-scrollbar-thumb:hover {
        background: #888;
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

    @media (max-width: 768px) {
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

        .popup {
            width: 70%;
            max-width: 250px;
            padding: 20px;
            text-align: left;
            border-radius: 10px;
            background-color: var(--background-color-light); /* Changed to match index.php */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }

        /* Updated logout popup styles to match index.php */
        #logout-popup h2 {
            margin-bottom: 20px;
            font-size: 22px; /* Increased font size */
            color: var(--text-color);
            font-weight: bold;
            text-align: left;
        }

        #logout-popup .button-container {
            display: flex;
            justify-content: flex-start; /* Changed from space-between */
            gap: 10px;
            margin-top: 20px;
        }

        #logout-popup .close-button,
        #logout-popup .cancel-button {
            display: inline-block;
            width: calc(45% - 10px); /* Made buttons smaller */
            margin: 0px;
            padding: 8px 0; /* Reduced padding */
            font-size: 14px; /* Smaller font size */
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }

        /* Other popup button styles */
        .popup .close-button,
        .popup .cancel-button {
            width: 100%;
            margin: 5px 0;
            padding: 10px 0;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            border: none;
        }

        .popup .close-button {
            background-color: #dc3545;
            color: #fff;
        }

        .popup .cancel-button {
            background-color: #007bff;
            color: #fff;
        }

        .popup .close-button:hover {
            background-color: #c82333;
        }

        .popup .cancel-button:hover {
            background-color: #0056b3;
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
        <button class="close-button" id="logout-confirm-button">Yes</button>
        <button class="cancel-button" id="logout-cancel-button">No</button>
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
    <script src="scripts.js"></script>

    <script>
        function openPopup(popupId) {
            document.getElementById(popupId).style.display = 'block';
        }

        function closePopup(popupId) {
            const popup = document.getElementById(popupId);
            popup.style.display = 'none';
            
            // Clear any error messages
            const errorMessage = popup.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.remove();
            }
        }

        function logoutUser() {
            document.getElementById('logout-form').submit();
        }

        function showPageMessage(message, type) {
            // Remove any existing messages first
            const existingMessages = document.querySelectorAll('.success-message, .error-message');
            existingMessages.forEach(msg => msg.remove());
            
            const messageDiv = document.createElement('p');
            messageDiv.className = type === 'success' ? 'success-message' : 'error-message';
            messageDiv.textContent = message;
            
            const profileDetails = document.querySelector('.profile-details');
            const firstDetailLine = profileDetails.querySelector('.detail-line');
            
            // Insert message and force layout reflow
            profileDetails.insertBefore(messageDiv, firstDetailLine);
            messageDiv.offsetHeight; // Force reflow
            
            // Only set timeout for success messages
            if (type === 'success') {
                // Remove previous timeout if exists
                if (window.messageTimeout) {
                    clearTimeout(window.messageTimeout);
                }
                
                // Set new timeout
                window.messageTimeout = setTimeout(() => {
                    if (messageDiv && messageDiv.parentNode) {
                        messageDiv.remove();
                    }
                }, 3000); // Increased to 3 seconds
            }
            
            // Return the message div for reference
            return messageDiv;
        }

        function validateProfileUpdate(type, value) {
                let errorMessage = "";
                let isValid = true;

                const popupTypeMap = {
            'username-popup': 'username',
            'email-popup': 'email',
            'password-popup': 'password',
            'linkedin-popup': 'linkedin',
            'company-name-popup': 'company_name',
            'company-type-popup': 'company_type'
        };

        // Get the actual type if it exists in the map
        const validationType = popupTypeMap[type] || type;

            switch(type) {
                case 'username':
                    // Add case-sensitive check
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

                case 'linkedin':
                    const linkedinRegex = /^https?:\/\/(?:www\.)?linkedin\.com\/(?:in|profile)\/[A-Za-z0-9\-]+\/?$/;
                    if (!linkedinRegex.test(value)) {
                        errorMessage = "Invalid LinkedIn URL format. Example:<br>" +
                            "https://www.linkedin.com/in/username<br>" +
                            "or<br>" +
                            "https://linkedin.com/in/username";
                        isValid = false;
                    }
                    break;
            }

            return { isValid, errorMessage };
        }

        function handleFormSubmission(form, popupId, updateCallback) {
            const formData = new FormData(form);
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update display if callback exists
                    if (updateCallback) {
                        updateCallback(data);
                    }
                    
                    // Remove any existing success messages first
                    const existingMessages = document.querySelectorAll('.success-message, .error-message');
                    existingMessages.forEach(msg => msg.remove());
                    
                    // Create and show success message on page
                    const successMessage = document.createElement('p');
                    successMessage.className = 'success-message';
                    successMessage.textContent = data.message;
                    document.querySelector('.profile-details').insertBefore(successMessage, document.querySelector('.detail-line'));
                    
                    // Remove success message after 3 seconds instead of 2
                    setTimeout(() => successMessage.remove(), 3000);
                    
                    // Clear form and close popup
                    form.reset();
                    closePopup(popupId);
                    
                    // Update display immediately
                    if (data.company_name) {
                        document.querySelector('.detail-line i.fa-building').nextElementSibling.textContent = data.company_name;
                    }
                } else {
                    showError(data.message, popupId);
                }
            })
            .catch(error => {
                showError('An error occurred. Please try again.', popupId);
            });
        }

        document.querySelectorAll('.popup form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const popupId = this.closest('.popup').id;
                let input = this.querySelector('input[type="text"], input[type="password"], input[type="email"]');
                if (!input) return;

                let inputType = input.name.replace('new_', '');
                let validation = validateProfileUpdate(inputType, input.value);

                if (!validation.isValid) {
                    showError(validation.errorMessage, popupId);
                    return;
                }

                let formData = new FormData(this);
                
                fetch('profile.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update the display
                        if (input.name === 'new_username') {
                            document.getElementById('username-display').textContent = input.value;
                            document.querySelector('.username').textContent = input.value;
                        } else if (input.name === 'new_email') {
                            document.querySelector('.detail-line .fa-envelope').nextElementSibling.textContent = input.value;
                        }
                        
                        // Show success message on page
                        showPageMessage(data.message, 'success');
                        
                        // Clear form and close popup
                        this.reset();
                        closePopup(popupId);
                        
                        // Reload page if needed
                        if (input.name === 'new_linkedin') {
                            window.location.reload();
                        }
                    } else {
                        showError(data.message, popupId);
                    }
                })
                .catch(error => {
                    showError('An error occurred. Please try again.', popupId);
                });
            });
        });

        document.querySelector('#password-popup form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const popupId = this.closest('.popup').id;
            const input = this.querySelector('input[type="password"]');
            
            let validation = validateProfileUpdate('password', input.value);
            if (!validation.isValid) {
                showError(validation.errorMessage, popupId);
                return;
            }

            const formData = new FormData(this);
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message on page
                    showPageMessage(data.message, 'success');
                    
                    // Close popup and reset form
                    this.reset();
                    closePopup(popupId);
                } else {
                    showError(data.message || 'Failed to update password', popupId);
                }
            })
            .catch(error => {
                showError('An error occurred. Please try again.', popupId);
            });
        });

        document.querySelector('#linkedin-popup form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const input = this.querySelector('input[type="text"]');
            const form = this;
            
            let validation = validateProfileUpdate('linkedin', input.value);
            if (!validation.isValid) {
                showError(validation.errorMessage, 'linkedin-popup');
                return;
            }

            const formData = new FormData(this);
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update LinkedIn display immediately
                    const linkedinSpan = document.querySelector('.detail-line a[href^="http"]');
                    if (linkedinSpan) {
                        linkedinSpan.href = input.value;
                        linkedinSpan.textContent = input.value;
                    } else {
                        // If no LinkedIn link exists yet, create one
                        const linkedinContainer = document.createElement('div');
                        linkedinContainer.className = 'detail-line';
                        linkedinContainer.innerHTML = `<a href="${input.value}" target="_blank" style="color: #007bff;">${input.value}</a>`;
                        document.querySelector('.detail-line img[alt="LinkedIn"]').closest('.detail-line').after(linkedinContainer);
                    }
                    
                    // Reset form and close popup
                    form.reset();
                    closePopup('linkedin-popup');

                    // Show alert message instead of success message
                    alert(data.message);
                    
                    // Reload page
                    window.location.reload();
                } else {
                    showError(data.message, 'linkedin-popup');
                }
            })
            .catch(error => {
                console.error('LinkedIn update error:', error);
                showError('Failed to update LinkedIn profile. Please try again.', 'linkedin-popup');
            });
        });

        document.querySelector('#resume-popup form')?.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const form = this;
            
            fetch('profile.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Show success message on page
                showPageMessage("Resume uploaded successfully.", 'success');
                
                // Close popup immediately
                form.reset();
                closePopup('resume-popup');
                
                // Delay page reload
                setTimeout(() => {
                    window.location.reload();
                }, 3000);
            })
            .catch(error => {
                showError('Failed to upload resume. Please try again.', 'resume-popup');
            });
        });

        function showSuccess(message, popupId) {
            // Remove any existing messages
            removeMessages(popupId);
            
            // Create and show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'success-message';
            successDiv.innerHTML = message;
            
            const form = document.querySelector(`#${popupId} form`);
            form.insertBefore(successDiv, form.firstChild);
        }

        function showError(message, popupId) {
            // Remove any existing messages
            removeMessages(popupId);
            
            // Create and show error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message';
            errorDiv.innerHTML = message;
            
            const form = document.querySelector(`#${popupId} form`);
            form.insertBefore(errorDiv, form.firstChild);
        }

        function removeMessages(popupId) {
            const popup = document.getElementById(popupId);
            const messages = popup.querySelectorAll('.error-message, .success-message');
            messages.forEach(msg => msg.remove());
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
    </script>
</body>
</html>