<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit'; 

$conn = new mysqli($host, $username, $password, $database);

// Fetch Job Seeker data
$jobSeekerQuery = "
    SELECT 
        job_seeker.user_id AS 'name', 
        job_seeker.education_level AS 'education level', 
        job_seeker.year_of_experience AS 'year of experience', 
        assessment_job_seeker.score AS 'assessment score' 
    FROM job_seeker 
    LEFT JOIN assessment_job_seeker 
    ON job_seeker.job_seeker_id = assessment_job_seeker.job_seeker_id";
$jobSeekerResult = $conn->query($jobSeekerQuery);

// Fetch Employer data
$employerQuery = "
    SELECT 
        employer.user_id AS 'name', 
        employer.company_name AS 'company name', 
        employer.company_type AS 'company type'
    FROM employer";
$employerResult = $conn->query($employerQuery);

// Fetch Disabled Job Seeker data
$disabledJobSeekerQuery = "
    SELECT 
        disabled_jobseeker.user_id AS 'name', 
        disabled_jobseeker.disabled_date AS 'disabled date'
    FROM disabled_jobseeker";
$disabledJobSeekerResult = $conn->query($disabledJobSeekerQuery);

// Fetch Disabled Employer data
$disabledEmployerQuery = "
    SELECT 
        disabled_employer.user_id AS 'name', 
        disabled_employer.disabled_date AS 'disabled date'
    FROM disabled_employer";
$disabledEmployerResult = $conn->query($disabledEmployerQuery);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $userIds = $_POST['user_ids'];
    $disabledDate = date('Y-m-d H:i:s');

    foreach ($userIds as $userId) {
        // Fetch user details
        $userQuery = "SELECT user_id FROM job_seeker WHERE user_id = '$userId' UNION SELECT user_id FROM employer WHERE user_id = '$userId'";
        $userResult = $conn->query($userQuery);
        $user = $userResult->fetch_assoc();

        if ($user) {
            // Check if the user is a job seeker or employer
            $jobSeekerQuery = "SELECT * FROM job_seeker WHERE user_id = '$userId'";
            $jobSeekerResult = $conn->query($jobSeekerQuery);
            if ($jobSeekerResult->num_rows > 0) {
                $jobSeeker = $jobSeekerResult->fetch_assoc();
                // Transfer user to disabled_jobseeker table
                $conn->query("INSERT INTO disabled_jobseeker (job_seeker_id,user_id, resume, linkedin_link, job_position_interested, education_level, year_of_experience, disabled_date) 
                VALUES ('{$jobSeeker['job_seeker_id']}','{$jobSeeker['user_id']}', '{$jobSeeker['resume']}', '{$jobSeeker['linkedin_link']}', '{$jobSeeker['job_position_interested']}', '{$jobSeeker['education_level']}', '{$jobSeeker['year_of_experience']}', '$disabledDate')");
                // Remove user from job_seeker table
                $conn->query("DELETE FROM job_seeker WHERE user_id = '$userId'");
            } else {
                // Fetch employer details
                $employerQuery = "SELECT * FROM employer WHERE user_id = '$userId'";
                $employerResult = $conn->query($employerQuery);
                if ($employerResult->num_rows > 0) {
                    $employer = $employerResult->fetch_assoc();
                    // Transfer user to disabled_employer table with all details
                    $conn->query("INSERT INTO disabled_employer (user_id, employer_id, company_name, linkedin_link, job_position_interested, company_type, disabled_date) 
                    VALUES ('{$employer['user_id']}', '{$employer['employer_id']}', '{$employer['company_name']}', '{$employer['linkedin_link']}', '{$employer['job_position_interested']}', '{$employer['company_type']}', '$disabledDate')");
                    // Remove user from employer table
                    $conn->query("DELETE FROM employer WHERE user_id = '$userId'");
                }
            }
        }
    }        
    // Refresh the page to reflect changes
    header('Location: manage_users.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Techfit</title>
    <link rel="stylesheet" href="styles.css">   
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
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
                            <li><a href="create_assessment.html">Create New Assessment</a></li>
                            <li><a href="manage_assessments.php">Manage Assessments</a></li>
                            <li><a href="view_assessment_results.html">View Assessment Results</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Users</a>
                        <ul class="dropdown">
                            <li><a href="manage_users.html">Manage Users</a></li>
                            <li><a href="user_feedback.html">User Feedback</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Reports</a>
                        <ul class="dropdown">
                            <li><a href="assessment_performance.html">Assessment Performance</a></li>
                            <li><a href="user_engagement.html">User Engagement Statistics</a></li>
                            <li><a href="feedback_analysis.html">Feedback Analysis</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="useful_links.html">Manage Useful Links</a></li>
                            <li><a href="faq.html">Manage FAQs</a></li>
                            <li><a href="sitemap.html">Manage Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.html">About</a></li>
                    <li>
                        <a href="#" id="profile-link">
                            <div class="profile-info">
                                <span class="username" id="username">Admin</span>
                                <img src="images/usericon.png" alt="Profile" class="profile-image" id="profile-image">
                            </div>
                        </a>
                        <ul class="dropdown" id="profile-dropdown">
                            <li><a href="settings.html">Settings</a>
                                <ul class="dropdown">
                                    <li><a href="manage_profile.html">Manage Profile</a></li>
                                    <li><a href="system_configuration.html">System Configuration Settings</a></li>
                                </ul>
                            </li>
                            <li><a href="logout.html">Logout</a></li>
                        </ul>
                    </li>                    
                </ul>
            </div>
        </nav>
    </header>    

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

                <div class="user-section">
                    <h3 class="user-type">Disabled Job Seekers</h3>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                                <th>Disabled Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $disabledJobSeekerResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['disabled date']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <div class="user-section">
                    <h3 class="user-type">Disabled Employers</h3>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>Select</th>
                                <th>Name</th>
                                <th>Disabled Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $disabledEmployerResult->fetch_assoc()) { ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['disabled date']); ?></td>
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
                    <h3>Assessments</h3>
                    <ul>
                        <li><a href="create_assessment.html">Create New Assessment</a></li>
                        <li><a href="manage_assessments.php">Manage Assessments</a></li>
                        <li><a href="view_assessment_results.html">View Assessment Results</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Users</h3>
                    <ul>
                        <li><a href="manage_users.html">Manage Users</a></li>
                        <li><a href="user_feedback.html">User Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Reports</h3>
                    <ul>
                        <li><a href="assessment_performance.html">Assessment Performance</a></li>
                        <li><a href="user_engagement.html">User Engagement Statistics</a></li>
                        <li><a href="feedback_analysis.html">Feedback Analysis</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="useful_links.html">Manage Useful Links</a></li>
                        <li><a href="faq.html">Manage FAQs</a></li>
                        <li><a href="sitemap.html">Manage Sitemap</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>About</h3>
                    <ul>
                        <li><a href="about.html">About</a></li>
                        <li><a href="contact.html">Contact Us</a></li>
                        <li><a href="terms.html">Terms & Condition</a></li>
                        <li><a href="privacy.html">Privacy Policy</a></li>
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