<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assessments - TechFit</title>
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
    <main>
        <h1>Manage Assessments</h1>
        <button onclick="window.location.href='create_assessment.html'">Create New Assessment</button>
        <table>
            <thead>
                <tr>
                    <th>Assessment ID</th>
                    <th>Assessment Name</th>
                    <th>Timestamp</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
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

                $sql = "SELECT assessment_id, assessment_name, timestamp FROM Assessment_Admin";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['assessment_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['assessment_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                        echo "<td><a href='edit_assessment.php?assessment_id=" . htmlspecialchars($row['assessment_id']) . "'>Edit</a> | <a href='delete_assessment.php?assessment_id=" . htmlspecialchars($row['assessment_id']) . "'>Delete</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No assessments found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </main>
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
</body>
</html>