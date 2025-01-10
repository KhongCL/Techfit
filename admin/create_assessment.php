<?php
session_start(); // Start the session to access session variables

// Function to display the message
function displayLoginMessage() {
    echo '<script>
        alert("You need to log in to access this page.");
    </script>';
    exit();
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); // Display message if not logged in
}

// Check if the user has the correct role
if ($_SESSION['role'] !== 'Admin') {
    displayLoginMessage(); // Display message if the role is not Admin
}

// Close the session
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

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate the next ID with a given prefix
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
    $admin_user_id = $_SESSION['user_id']; // Assuming the admin is logged in and their user_id is stored in the session

    // Retrieve the admin_id using the user_id
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

    // Insert the assessment into the database
    $sql = "INSERT INTO Assessment_Admin (assessment_id, admin_id, assessment_name, description, timestamp, is_active)
            VALUES ('$assessment_id', '$admin_id', '$assessment_name', '$description', '$timestamp', TRUE)";
    
    if ($conn->query($sql) === TRUE) {
        // Redirect to create_questions.php with the assessment_id
        $_SESSION['success_message'] = "Assessment created successfully.";
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
                                <span class="username" id="username">Admin</span>
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
                            <li><a href="logout.php">Logout</a></li>
                        </ul>
                    </li>                    
                </ul>
            </div>
        </nav>
    </header>    
    
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
                    <p>techfit@gmail.com</p>
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
                        <li><a href="terms.php">Terms & Condition</a></li>
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