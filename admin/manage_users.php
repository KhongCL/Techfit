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
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit'; 

$conn = new mysqli($host, $username, $password, $database);


$jobSeekerQuery = "
    SELECT 
        job_seeker.user_id AS 'name', 
        job_seeker.education_level AS 'education level', 
        job_seeker.year_of_experience AS 'year of experience', 
        assessment_job_seeker.score AS 'assessment score' 
    FROM job_seeker 
    LEFT JOIN assessment_job_seeker 
    ON job_seeker.job_seeker_id = assessment_job_seeker.job_seeker_id
    INNER JOIN User
    ON job_seeker.user_id = User.user_id
    WHERE User.is_active = 1";
$jobSeekerResult = $conn->query($jobSeekerQuery);


$employerQuery = "
    SELECT 
        employer.user_id AS 'name', 
        employer.company_name AS 'company name', 
        employer.company_type AS 'company type'
    FROM employer
    INNER JOIN User
    ON employer.user_id = User.user_id
    WHERE User.is_active = 1";
$employerResult = $conn->query($employerQuery);


$restoreUserQuery = "
    SELECT 
        user_id AS 'name', 
        role 
    FROM User
    WHERE is_active = 0";
$restoreUserResult = $conn->query($restoreUserQuery);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $userIds = $_POST['user_ids'];
        foreach ($userIds as $userId) {
            
            $updateQuery = "UPDATE User SET is_active = 0 WHERE user_id = '$userId'";
            $conn->query($updateQuery);
        }
        
        header('Location: manage_users.php');
        exit();
    } elseif (isset($_POST['restore'])) {
        $userIds = $_POST['restore_user_ids'];
        foreach ($userIds as $userId) {
            
            $updateQuery = "UPDATE User SET is_active = 1 WHERE user_id = '$userId'";
            $conn->query($updateQuery);
        }
        
        header('Location: manage_users.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Techfit</title>
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

    <div class="content">
        <div class="tabs">
            <button class="tab active">Manage User</button>
        </div>

        <form method="POST" action="manage_users.php">
            <div class="delete_button">
                <h2 class="section-title">USER <button type="submit" name="delete" class="delete-link">Delete</button></h2>

                <div class="user-section">
                    <h3 class="user-type">Job Seeker</h3>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                                <th>Education Level</th>
                                <th>Year of Experience</th>
                                <th>Assessment Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $jobSeekerResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><input type="checkbox" name="user_ids[]" value="<?php echo htmlspecialchars($row['name']); ?>"></td>
                                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                                    <td><?php echo htmlspecialchars($row['education level']); ?></td>
                                    <td><?php echo htmlspecialchars($row['year of experience']); ?></td>
                                    <td><?php echo htmlspecialchars($row['assessment score']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="user-section">
                    <h3 class="user-type">Employer</h3>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                                <th>Company Name</th>
                                <th>Company Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $employerResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['company name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['company type']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>   
                
            <div class="restore_button">
                <h2 class="section-title">RESTORE USER <button type="submit" name="restore" class="restore-link">Restore</button></h2>

                <div class="user-section">
                    <h3 class="user-type"></h3>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $restoreUserResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><input type="checkbox" name="restore_user_ids[]" value="<?php echo htmlspecialchars($row['name']); ?>"></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>     
            </div>
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