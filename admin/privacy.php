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
    <title>Privacy Policy - TechFit</title>
    <link rel="stylesheet" href="styles.css?v=2.0">
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

    <section id="privacy-policy">
        <div class="container">
            <h2>TechFit - Privacy Policy</h2>
            
            <div id="last-updated">
                Last Updated: December 18, 2024
            </div>
            
            <div id="privacy-header">
                TechFit is committed to protecting your privacy. This Privacy Policy explains how we collect, use, and disclose information about you when you use our website and services. By using our website and services, you consent to the collection, use, and disclosure of your information as described in this Privacy Policy.
            </div>
            
            <div id="privacy-section">
                <h3>Information We Collect</h3>
            </div>
    
            <div id="privacy-text">
                <ul>
                    <li><strong>Personal Information:</strong> We collect personal information that you provide to us, such as your name, email address, phone number, and other contact details. This may include information you provide when you register for an account, fill out forms, or communicate with us.</li>
                    <li><strong>Assessment Information:</strong> We collect information about the assessments you take on our website, including your answers to assessment questions and your assessment results. This helps us understand your skills and preferences to better match you with potential employers.</li>
                    <li><strong>Device Information:</strong> We collect information about the devices you use to access our website, such as your IP address, browser type, operating system, and unique device identifiers. This helps us ensure compatibility and provide you with a seamless experience.</li>
                    <li><strong>Usage Information:</strong> We collect information about how you use our website, such as the pages you visit, the features you use, and the time you spend on our site. This helps us improve our services and tailor them to your needs.</li>
                    <li><strong>Cookies:</strong> We use cookies and similar tracking technologies to collect information about your interactions with our website. Cookies help us remember your preferences and improve your experience. You can manage your cookie preferences through your browser settings.</li>
                </ul>
            </div>
    
            <div id="privacy-section">
                <h3>How We Use Your Information</h3>
            </div>
    
            <div id="privacy-text">
                <ul>
                    <li>To personalize your experience on our website and provide content relevant to your interests.</li>
                    <li>To communicate with you about our website, services, and any changes to our policies.</li>
                    <li>To analyze and improve our website and services, making them more efficient and user-friendly.</li>
                    <li>To prevent fraud, abuse, and other harmful activities that could impact our website and services.</li>
                    <li>To comply with legal requirements and protect our legal rights.</li>
                    <li>To conduct research and analysis to understand user behavior and preferences.</li>
                    <li>To provide customer support and respond to your inquiries and requests.</li>
                </ul>
            </div>
    
            <div id="privacy-section">
                <h3>How We Share Your Information</h3>
            </div>
    
            <div id="privacy-text">
                <ul>
                    <li><strong>Service Providers:</strong> We may share your information with third-party service providers that help us operate, maintain, and improve our website and services. These providers are bound by confidentiality agreements and are only permitted to use your information for the purposes we specify.</li>
                    <li><strong>Legal Requirements:</strong> We may share your information when we believe it is necessary to comply with legal requirements, such as responding to subpoenas or court orders, or to protect our rights, property, or safety, or the rights, property, or safety of others.</li>
                    <li><strong>Business Transfers:</strong> If we are involved in a merger, acquisition, or sale of all or a portion of our assets, your information may be transferred as part of that transaction. We will notify you of any such change in ownership or control of your personal information.</li>
                    <li><strong>With Your Consent:</strong> We may share your information with third parties when you have given us your explicit consent to do so.</li>
                </ul>
            </div>
    
            <div id="privacy-section">
                <h3>Your Rights and Choices</h3>
            </div>
    
            <div id="privacy-text">
                <ul>
                    <li><strong>Access and Correction:</strong> You have the right to access and correct your personal information. You can update your information through your account settings or by contacting us directly.</li>
                    <li><strong>Data Portability:</strong> You have the right to request a copy of the personal information we hold about you in a structured, commonly used, and machine-readable format.</li>
                    <li><strong>Deletion:</strong> You have the right to request the deletion of your personal information. We will comply with your request unless we are required to retain certain information by law or for legitimate business purposes.</li>
                    <li><strong>Opt-Out:</strong> You can opt-out of receiving marketing communications from us by following the unsubscribe instructions included in those communications or by contacting us directly.</li>
                    <li><strong>Cookie Preferences:</strong> You can manage your cookie preferences through your browser settings. Please note that disabling cookies may affect the functionality of our website.</li>
                </ul>
            </div>
    
            <div id="privacy-section">
                <h3>Security of Your Information</h3>
            </div>
    
            <div id="privacy-text">
                <ul>
                    <li>We implement reasonable security measures to protect your personal information from unauthorized access, use, or disclosure. This includes technical, administrative, and physical safeguards.</li>
                    <li>However, no method of transmission over the Internet or electronic storage is completely secure. While we strive to protect your personal information, we cannot guarantee its absolute security.</li>
                </ul>
            </div>
    
            <div id="privacy-section">
                <h3>Changes to This Privacy Policy</h3>
            </div>
    
            <div id="privacy-text">
                <ul>
                    <li>We may update this privacy policy from time to time. When we do, we will revise the "Last Updated" date at the top of this page. We encourage you to review this policy periodically to stay informed about how we are protecting your information.</li>
                    <li>Your continued use of our website and services after any changes to this policy will constitute your acknowledgment of the changes and your consent to abide and be bound by the updated policy.</li>
                </ul>
            </div>
    
            <div id="privacy-section">
                <h3>Contact Us</h3>
            </div>
    
            <div id="privacy-text">
                <ul>
                    <li>If you have any questions or concerns about this privacy policy, please contact us at <a href="mailto:techfit@gmail.com">techfit@gmail.com</a>.</li>
                </ul>
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

    <script src="scripts.js?v=1.0"></script>
</body>
</html>
