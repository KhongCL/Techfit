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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms of Service - TechFit</title>
    <link rel="stylesheet" href="styles.css?v=2.0">
</head>


<style>
    li {
        color: white;
    }
</style>

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

    <section id="terms">
        <div class="container">
            <h2>TechFit - Terms of Service</h2>
            <div id="last-updated">
                Last Updated: December 18, 2024
            </div>
            
            <div id="terms-header">
                By using our website, you agree to the following Terms of Service:
            </div>
    
            <div id="terms-section">
                <h3>Content Disclaimer</h3>
            </div>
    
            <div id="terms-text">
                <ol>
                    <li>The content on our website is for informational purposes only and should not be construed as legal advice.</li>
                    <li>We do not warrant the accuracy, reliability, or completeness of any information on our website.</li>
                    <li>We reserve the right to modify or update these Terms of Service at any time.</li>
                    <li>By using our website, you agree to be bound by these Terms of Service.</li>
                    <li>If you have any questions or concerns about these Terms of Service, please contact us.</li>
                </ol>
            </div>
    
            <div id="terms-section">
                <h3>Acceptance of Terms</h3>
            </div>
    
            <div id="terms-text">
                <ol>
                    <li>The services that TechFit provides to you are subject to the following Terms of Use ("TOU").</li>
                    <li>TechFit reserves the right to update and modify the TOU at any time without notice to you.</li>
                    <li>By using the website after a new version of the TOU has been posted, you agree to the terms of such new version.</li>
                </ol>
            </div>
    
            <div id="terms-section">
                <h3>Description of Services</h3>
            </div>
    
            <div id="terms-text">
                <ol>
                    <li>Through its network of web properties, TechFit provides you with access to a variety of resources, including job postings, employer profiles, and user assessments.</li>
                    <li>The Services, including any updates, enhancements, and new features, are subject to these TOU.</li>
                </ol>
            </div>
    
            <div id="terms-section">
                <h3>Personal and Non-Commercial Use Limitation</h3>
            </div>
    
            <div id="terms-text">
                <ol>
                    <li>Unless otherwise specified, the Services are for your personal and non-commercial use.</li>
                    <li>You may not modify, copy, distribute, transmit, display, perform, reproduce, publish, license, create derivative works from, transfer, or sell any information, software, products, or services obtained from the Services.</li>
                </ol>
            </div>
    
            <div id="terms-section">
                <h3>Content</h3>
            </div>
    
            <div id="terms-text">
                <ol>
                    <li>All content included in or made available through the Services, such as text, graphics, logos, icons, images, and documents is the exclusive property of TechFit or its content suppliers.</li>
                    <li>All rights not expressly granted to you in these TOU are reserved and retained by TechFit or its licensors, suppliers, publishers, rightsholders, or other content providers.</li>
                </ol>
            </div>
    
            <div id="terms-section">
                <h3>Software</h3>
            </div>
    
            <div id="terms-text">
                <ol>
                    <li>Any software that is made available to download from the Services is the copyrighted work of TechFit and/or its suppliers.</li>
                    <li>Use of the Software is governed by the terms of the end user license agreement, if any, which accompanies or is included with the Software.</li>
                    <li>Any reproduction or redistribution of the Software not in accordance with the License Agreement is expressly prohibited by law, and may result in severe civil and criminal penalties.</li>
                    <li>Violators will be prosecuted to the maximum extent possible.</li>
                </ol>
            </div>
    
            <div id="terms-section">
                <h3>Documents</h3>
            </div>
    
            <div id="terms-text">
                <ol>
                    <li>Permission to use Documents (such as white papers, press releases, datasheets, and FAQs) from the Services is granted, provided that (1) the below copyright notice appears in all copies and that both the copyright notice and this permission notice appear, (2) unless explicitly covered by another license or agreement, use of such Documents from the Services is for informational and non-commercial or personal use only, and (3) no modifications of any Documents are made.</li>
                    <li>Accredited educational institutions may download and reproduce the Documents for distribution in the classroom. Distribution outside the classroom requires express written permission.</li>
                </ol>
            </div>
    
            <div id="terms-section">
                <h3>Representations and Warranties</h3>
            </div>
    
            <div id="terms-text">
                <ol>
                    <li>Software is warranted, if at all, only according to the terms of the license agreement.</li>
                    <li>Except as warranted in the license agreement, TechFit hereby disclaims all warranties and conditions with regard to the software, including all warranties and conditions of merchantability, whether express, implied, or statutory, fitness for a particular purpose, title and non-infringement.</li>
                    <li>TechFit makes no representations about the suitability of the information contained in the documents and related graphics published as part of the Services for any purpose. All such documents and related graphics are provided "as is" without warranty of any kind.</li>
                    <li>In no event shall TechFit be liable for any special, indirect or consequential damages or any damages whatsoever resulting from loss of use, data, or profits, whether in an action of contract, negligence, or other tortious action, arising out of or in connection with the use or performance of information available from the Services.</li>
                </ol>
            </div>
        </div>
    </section>
    
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

    <script src="scripts.js?v=1.0"></script>
</body>
</html>
