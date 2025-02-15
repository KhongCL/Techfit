<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit - Home</title>
    <link rel="stylesheet" href="styles.css?v=2.0">
    <style>
        main {
            position: relative;
            min-height: 70vh;
            padding: 0; /* Remove padding */
        }

        #home {
            position: relative;
            width: 100%;
            min-height: 70vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            color: white;
            padding: 0;
            overflow: hidden;
            background-image: url('images/office_background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        #home::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image: url('images/office_background.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            filter: blur(4px);
            z-index: 1;
        }

        /* Update the home-content styles to make text sharper */
        #home-content {
            position: relative;
            z-index: 3; /* Increase z-index to ensure it's above the blur */
            padding: 2rem;
            background: rgba(0, 0, 0, 0.4);
            width: 100%;
            height: 40vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(0); /* Ensure content isn't blurred */
        }

        /* Enhance text styles for better focus */
        #home h2 {
            font-size: 3rem; /* Slightly larger */
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); /* Stronger shadow */
            margin-bottom: 1rem;
            font-weight: bold;
            letter-spacing: 1px;
        }

        #home p {
            font-size: 1.3rem; /* Slightly larger */
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.7); /* Stronger shadow */
            margin-bottom: 2rem;
            letter-spacing: 0.5px;
        }

        #home button {
            padding: 1rem 2.5rem; /* Slightly wider */
            font-size: 1.2rem;
            border: none;
            border-radius: 5px;
            background-color: var(--primary-color);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2); /* Add shadow to button */
        }

        #home button:hover {
            background-color: var(--accent-color);
            transform: translateY(-2px); /* Slight lift effect */
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
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
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="useful_links.php">Useful Links</a></li>
                            <li><a href="faq.php">FAQ</a></li>
                            <li><a href="sitemap.php">Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="profile.php" id="profile-link" style="display:none;">Profile</a>
                        <ul class="dropdown" id="profile-dropdown" style="display:none;">
                            <li><a href="settings.php">Settings</a></li>
                            <li><a href="logout.php">Logout</a></li>
                        </ul>
                    </li>
                    <li><a href="login.php" id="login-link">Login/Register</a></li>
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
        <section id="home">
            <div id="home-content">
                <h2>Welcome to TechFit</h2>
                <p>Bridging the Gap Between IT Talent and Top Employers.</p>
                <button onclick="location.href='login.php'">Get Started</button>
            </div>
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

    <script src="scripts.js?v=1.0"></script>
</body>
</html>
