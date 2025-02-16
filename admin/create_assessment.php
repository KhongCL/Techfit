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
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


function generateNextId($conn, $table, $column, $prefix) {
    $sql = "SELECT MAX(CAST(SUBSTRING($column, LENGTH('$prefix') + 1) AS UNSIGNED)) AS max_id FROM $table WHERE $column LIKE '$prefix%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $max_id = $row['max_id'] ? $row['max_id'] : 0;
    $next_id = $prefix . str_pad($max_id + 1, 2, '0', STR_PAD_LEFT);
    return $next_id;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $assessment_id = generateNextId($conn, 'Assessment_Admin', 'assessment_id', 'AS');
    $admin_user_id = $_SESSION['user_id']; 

    
    $admin_sql = "SELECT admin_id FROM Admin WHERE user_id = '$admin_user_id'";
    $admin_result = $conn->query($admin_sql);
    if ($admin_result->num_rows > 0) {
        $admin_row = $admin_result->fetch_assoc();
        $admin_id = $admin_row['admin_id'];
    } else {
        $_SESSION['error_message'] = "Admin not found.";
        header("Location: create_assessment.php");
        exit();
    }

    $assessment_name = $_POST['assessment_name'];
    $description = $_POST['description'];
    $timestamp = date('Y-m-d H:i:s');

    
    $sql = "INSERT INTO Assessment_Admin (assessment_id, admin_id, assessment_name, description, timestamp, is_active)
            VALUES ('$assessment_id', '$admin_id', '$assessment_name', '$description', '$timestamp', TRUE)";
    
    if ($conn->query($sql) === TRUE) {
        
        header("Location: create_questions.php?assessment_id=$assessment_id");
        exit();
    } else {
        $_SESSION['error_message'] = "Error: " . $conn->error;
        header("Location: create_assessment.php");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Assessment - TechFit</title>
    <link rel="stylesheet" href="styles.css">
        <style>
       
        body {
            font-family: Arial, sans-serif;
            color: var(--text-color);
            background-color: var(--background-color);
        }

        main {
            padding: 20px;
        }

       
        .header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .header-controls p {
            margin: 0;
        }

        .header-controls button {
            margin-left: 20px;
        }

        .action-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

       
        button {
            background-color: var(--primary-color);
            color: var(--text-color);
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            transition: background-color 0.3s ease, color 0.3s ease;
            border-radius: 5px;
            font-weight: bold;
            box-sizing: border-box;
        }

        button:hover {
            background-color: var(--button-hover-color);
            color: var(--hover-text-color);
        }

        button.danger {
            background-color: var(--danger-color);
        }

        button.danger:hover {
            background-color: var(--danger-hover-color);
        }

        button.success {
            background-color: var(--success-color);
        }

        button.success:hover {
            background-color: var(--success-hover-color);
        }

        button[type="button"] {
            margin-right: 10px;
        }

       
        input[type="text"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            background-color: var(--secondary-color);
            color: var(--text-color);
            transition: border-color 0.3s ease, background-color 0.3s ease, color 0.3s ease;
            box-sizing: border-box;
        }

        input[type="text"]:hover, textarea:hover, select:hover {
            border-color: var(--primary-color);
        }

        textarea {
            resize: vertical;
        }

        label, textarea, select, input[type="text"], button {
            margin-bottom: 15px;
        }

       
        #assessment-name-label, #description-label {
            color: var(--lighter-text-color);
        }

       
        .form-group {
            margin-bottom: 20px;
        }

        li {
            color: white;
        }

        /* Form styling */
        #create-assessment-form {
            background-color: var(--background-color);
            padding: 20px;
            border-radius: 5px;
            box-sizing: border-box;
        }

        #create-assessment-form textarea,
        #create-assessment-form input[type="text"],
        #create-assessment-form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid var(--text-color);
            border-radius: 5px;
            background-color: var(--background-color);
            color: var(--text-color);
            transition: border-color 0.3s ease, background-color 0.3s ease, color 0.3s ease;
            box-sizing: border-box;
            font-size: 14px;
        }

        #create-assessment-form textarea:focus,
        #create-assessment-form input[type="text"]:focus,
        #create-assessment-form select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        #create-assessment-form select option {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        #create-assessment-form label {
            color: var(--text-color);
            display: block;
            margin-bottom: 5px;
        }

        /* Button styling */
        #create-assessment-form button {
            background-color: var(--primary-color);
            color: var(--text-color);
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #create-assessment-form button:disabled {
            background-color: var(--background-color-light);
            cursor: not-allowed;
        }

        #create-assessment-form button:hover:not(:disabled) {
            background-color: var(--accent-color);
        }

        /* Form group spacing */
        .form-group {
            margin-bottom: 20px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
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
        <button class="close-button" id="logout-confirm-button">Yes</button>
        <button class="cancel-button" id="logout-cancel-button">No</button>
    </div>   
    
    <main id="create-assessment-main">
        <h2 id="create-assessment-title">Create New Assessment</h2>
        <section id="create-assessment-section">
            <form action="create_assessment.php" method="post" id="create-assessment-form">
                <div id="assessment-name-group" class="form-group">
                    <label for="assessment_name" id="assessment-name-label">Assessment Name:</label>
                    <input type="text" id="assessment_name" name="assessment_name" placeholder="Enter assessment name" required>
                </div>
    
                <div id="description-group" class="form-group">
                    <label for="description" id="description-label">Description:</label>
                    <textarea id="description" name="description" placeholder="Enter a brief description" required></textarea>
                </div>
    
                <button type="submit" id="submit-button">Create Assessment</button>
            </form>
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