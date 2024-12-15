<!-- // php code missing here // -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Profile</title>
    <link rel="stylesheet" href="/Techfit/Techfit/styles.css?v=2.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        #profile {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            margin-top: 80px; /* Add space between header and image */
            margin-bottom: 80px; /* Add space between image and footer */
        }
        .profile-image {
            margin-right: 50px; /* Space between image and text */
            margin-left: 50px; /* Space between image and left wall */
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
        }
        .profile-details .detail-line {
            display: flex;
            align-items: center;
            margin-bottom: 20px; /* Space between lines */
        }
        .profile-details .detail-line i {
            margin-right: 10px; /* Space between icon and text */
        }
        .profile-details .detail-line span {
            font-size: 20px; /* Font size for the text */
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
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="/Techfit/Techfit/index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
        </div>
        <nav>
            <div class="nav-container">
                <ul class="nav-list">
                    <li><a href="#">Assessment</a>
                        <ul class="dropdown">
                            <li><a href="/Techfit/Techfit/start_assessment.html">Start Assessment</a></li>
                            <li><a href="/Techfit/Techfit/assessment_history.html">Assessment History</a></li>
                            <li><a href="/Techfit/Techfit/assessment_summary.html">Assessment Summary</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="/Techfit/Techfit/useful_links.html">Useful Links</a></li>
                            <li><a href="/Techfit/Techfit/faq.html">FAQ</a></li>
                            <li><a href="/Techfit/Techfit/sitemap.html">Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="/Techfit/Techfit/about.html">About</a></li>
                    <li><a href="/Techfit/Techfit/job_seeker/profile.php" id="profile-link">Profile</a>
                        <ul class="dropdown" id="profile-dropdown">
                            <li><a href="/Techfit/Techfit/job_seeker/profile.php">Settings</a></li>
                            <li><a href="/Techfit/Techfit/logout.php">Logout</a></li>
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
            <img src="images/testprofile.png" alt="Your Profile" class="profile-image" />
            <div class="profile-details">
                <h2>Edit Profile</h2>
                <div class="detail-line">
                    <i class="fas fa-user"></i>
                    <span><?php echo htmlspecialchars($username); ?></span>
                    <button class="edit-button"><i class="fas fa-edit"></i> Edit</button>
                </div>
                <div class="detail-line">
                    <i class="fas fa-envelope"></i>
                    <span><?php echo htmlspecialchars($email); ?></span>
                    <button class="edit-button"><i class="fas fa-edit"></i> Edit</button>
                </div>
                <div class="detail-line">
                    <i class="fas fa-phone"></i>
                    <span>Phone Number</span>
                    <button class="edit-button"><i class="fas fa-edit"></i> Edit</button>
                </div>
                <div class="detail-line">
                    <i class="fas fa-lock"></i>
                    <span>Password</span>
                    <button class="edit-button"><i class="fas fa-edit"></i> Edit</button>
                </div>
                <button class="logout-button" onclick="window.location.href='/Techfit/Techfit/logout.php'">Logout</button>
            </div>
        </section>
    </main>

    <footer>
        <div class="footer-content">
            <div class="footer-left">
                <div class="footer-logo">
                    <a href="/Techfit/Techfit/index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
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
                    <h3>Assessment</h3>
                    <ul>
                        <li><a href="/Techfit/Techfit/start_assessment.html">Start Assessment</a></li>
                        <li><a href="/Techfit/Techfit/assessment_history.html">Assessment History</a></li>
                        <li><a href="/Techfit/Techfit/assessment_summary.html">Assessment Summary</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="/Techfit/Techfit/resources.html">Resources</a></li>
                        <li><a href="/Techfit/Techfit/about.html">About</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul>
                        <li><a href="/Techfit/Techfit/contact.html">Contact Us</a></li>
                        <li><a href="/Techfit/Techfit/feedback.html">Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="/Techfit/Techfit/terms.html">Terms of Service</a></li>
                        <li><a href="/Techfit/Techfit/privacy.html">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TechPathway: TechFit. All rights reserved.</p>
        </div>
    </footer>

    <script src="/Techfit/Techfit/scripts.js?v=1.0"></script>
</body>
</html>