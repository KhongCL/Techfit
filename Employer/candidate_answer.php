<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit Employer - Candidate Answer</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .table-container {
            flex: 1;
        }
        header, footer {
            background-color: #333; /* Assuming the header's background color is #333 */
            color: #fff; /* Assuming the text color is white */
        }
        footer {
            padding: 20px;
            text-align: center;
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
        .popup button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
        .candidate-info {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
            position: absolute;
            top: 20px;
            left: -110px;
            padding-right: 65px; /* Add some padding to the right for spacing */
            height: calc(100vh - 400px); /* Adjust height to be full viewport height minus some space */
        }
        .candidate-info::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 2px;
            background-color: #ccc;
            margin-top: -10px; /* Space from the header */
            margin-bottom: 20px; /* Space from the footer */
        }
        .candidate-info img {
            width: 150px; /* Shrink by 50% */
            height: 150px; /* Shrink by 50% */
            border-radius: 10%;
            margin-right: 10px;
        }
        .candidate-info .name {
            font-size: 30px; /* Smaller font size */
            font-weight: bold;
            margin-left: -10px; /* Move the name to the left */
            margin-top: 22px; /* Add some space between the image and the name */
        }
        .candidate-info .details {
            font-size: 18px; /* Increase font size */
            text-align: left;
            margin-top: 10px;
        }
        .candidate-info .details a {
            color: #007bff;
            text-decoration: none;
            display: block;
            margin-top: 20px; /* Space between name and LinkedIn link */
        }
        .candidate-info .details .education {
            margin-top: 0px; /* Space between LinkedIn and education */
        }
        .candidate-info .details .experience {
            margin-top: 15px; /* Space between education and experience */
        }
        .candidate-info .details a:hover {
            text-decoration: underline;
        }
        .assessment-title {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-top: 20px;
            position: relative;
            left: -50px; /* Move the title 50px to the left */
        }
        .assessment-dropdown {
            position: relative;
            left: 150px; /* Position the dropdown 150px to the right of the title */
            margin-top: -30px; /* Adjust the vertical alignment */
        }
        .questions-title {
            font-size: 20px;
            font-weight: bold;
            margin-top: 50px;
            margin-left: -670px; /* Align to the left but not beyond the divider */
        }
        .questions-container {
            margin-top: 10px;
            margin-left: 190px; /* Align to the left but not beyond the divider */
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f9f9f9;
            max-width: 70%; /* Reduce the size of the container */
        }
        .middle-section::after {
            content: '';
            position: absolute;
            top: 0;
            right: 150px; /* Move the divider 20px to the left */
            bottom: 0;
            width: 4px; /* Increase the thickness of the divider */
            background-color: #ccc;
            margin-top: 30px; /* Space from the header */
            margin-bottom: 15px; /* Space from the footer */
        }
        /* Existing styles... */
        .score-time {
            position: absolute;
            top: 100px; /* Adjust as needed */
            right: -120px; /* Adjust as needed */
            text-align: right;
            display: flex;
            flex-direction: column; /* Stack elements vertically */
            align-items: flex-end; /* Align items to the right */
        }
        .score-time .score, .score-time .time-used {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .score-time .divider {
            width: 100%; /* Full width */
            height: 2px; /* Adjust height as needed */
            background-color: #ccc;
            margin: 10px 0; /* Adjust spacing as needed */
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
        </div>
        <nav>
            <div class="nav-container">
                <ul class="nav-list">
                    <li><a href="#">Candidates</a>
                        <ul class="dropdown">
                            <li><a href="search_candidate.php">Search Candidates</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="useful_links.html">Useful Links</a></li>
                            <li><a href="faq.html">FAQ</a></li>
                            <li><a href="sitemap.html">Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.html">About</a></li>
                    <li>
                        <a href="#" id="profile-link">
                            <div class="profile-info">
                                <span class="username" id="username">Employer</span>
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

    <!-- Logout Popup -->
    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <button class="close-button" onclick="logoutUser()">Yes</button>
        <button class="cancel-button" onclick="closePopup('logout-popup')">No</button>
    </div>

    <div class="container">
        <div class="main-content middle-section">
            <div class="assessment-title">Assessment</div>
            <div class="assessment-dropdown">
                <select id="assessment-select" onchange="updateAssessment()">
                    <?php
                    // Database connection
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "techfit";

                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    if (!isset($_SESSION['employer_id'])) {
                        die("Employer not logged in.");
                    }

                    $job_seeker_id = $_GET['job_seeker_id'];
                    $assessment_id = isset($_GET['assessment_id']) ? $_GET['assessment_id'] : null;

                    $sql = "SELECT aj.assessment_id, aa.assessment_name 
                            FROM Assessment_Job_Seeker aj
                            JOIN Assessment_Admin aa ON aj.assessment_id = aa.assessment_id
                            WHERE aj.job_seeker_id = '$job_seeker_id'";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $selected = ($row['assessment_id'] == $assessment_id) ? 'selected' : '';
                            echo "<option value='" . $row['assessment_id'] . "' $selected>" . $row['assessment_name'] . "</option>";
                        }
                    } else {
                        echo "<option>No assessments found</option>";
                    }

                    $conn->close();
                    ?>
                </select>
            </div>
            <div class="questions-title">Questions</div>
            <div class="questions-container">
                <!-- Questions content will go here -->
                 Test Question 1<br>
                 Test Question 2<br>
                 Test Question 3<br>
                 Test Question 4<br>
            </div>
            <?php
            // Database connection
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "techfit";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            if (!isset($_SESSION['employer_id'])) {
                die("Employer not logged in.");
            }

            $job_seeker_id = $_GET['job_seeker_id'];
            $assessment_id = isset($_GET['assessment_id']) ? $_GET['assessment_id'] : null;

            $sql = "SELECT u.first_name, u.last_name, js.education_level, js.year_of_experience, js.linkedin_link 
                    FROM User u
                    JOIN Job_Seeker js ON u.user_id = js.user_id
                    WHERE js.job_seeker_id = '$job_seeker_id'";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                echo "<div class='candidate-info'>";
                echo "<img src='images/usericon.png' alt='User Icon'>";
                echo "<div class='name'>" . $row['first_name'] . " " . $row['last_name'] . "</div>";
                echo "<div class='details'>";
                if (!empty($row['linkedin_link'])) {
                    echo "<a href='" . $row['linkedin_link'] . "' target='_blank'>LinkedIn Profile</a><br>";
                }
                echo "<div class='education'>Education Level: " . $row['education_level'] . "</div>";
                echo "<div class='experience'>Years of Experience: " . $row['year_of_experience'] . " Years</div>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<h1>Candidate not found</h1>";
            }

            // Fetch score and time used
            if ($assessment_id) {
                $sql = "SELECT score, TIMEDIFF(end_time, start_time) AS time_used 
                        FROM Assessment_Job_Seeker 
                        WHERE job_seeker_id = '$job_seeker_id' AND assessment_id = '$assessment_id'";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $score = $row['score'];
                    $time_used = $row['time_used'];
                } else {
                    $score = "N/A";
                    $time_used = "N/A";
                }
            } else {
                $score = "N/A";
                $time_used = "N/A";
            }

            $conn->close();
            ?>
            <div class="score-time">
                <div class="score">Score: <?php echo $score; ?></div>
                <div class="divider"></div>
                <div class="time-used">Time Used: <?php echo $time_used; ?></div>
            </div>
        </div>
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
                    <h3>Candidate</h3>
                    <ul>
                        <li><a href="search_candidate.php">Search Candidates</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="useful_links.html">Useful Links</a></li>
                        <li><a href="faq.html">FAQ</a></li>
                        <li><a href="sitemap.html">Sitemap</a></li>
                        <li><a href="about.html">About</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul>
                        <li><a href="contact.html">Contact Us</a></li>
                        <li><a href="feedback.php">Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="terms.html">Terms of Service</a></li>
                        <li><a href="privacy.html">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TechPathway: TechFit. All rights reserved.</p>
        </div>
    </footer>
    <script>
        function openPopup(popupId) {
            document.getElementById(popupId).style.display = 'block';
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        function logoutUser() {
            window.location.href = '/Techfit'; // Redirect to the root directory
        }

        function updateAssessment() {
            const assessmentSelect = document.getElementById('assessment-select');
            const selectedAssessmentId = assessmentSelect.value;
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('assessment_id', selectedAssessmentId);
            window.location.search = urlParams.toString();
        }
    </script>
</body>
</html>