<?php
session_start();

function displayLoginMessage() {
    echo '<script>
        if (confirm("You need to log in to access this page. Go to Login Page? Click cancel to go to home page.")) {
            window.location.href = "../login.php";
        } else {
            window.location.href = "../index.php";
        }
    </script>';
    exit();
}

if (!isset($_SESSION['user_id'])) {
    displayLoginMessage();
}

if ($_SESSION['role'] !== 'Employer') {
    displayLoginMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Profile & Answers - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="employer.css">
        <style>
        .job-seeker-answer pre,
        .correct-answer pre,
        .code-container {
            overflow-x: auto;
            min-width: 100%;
        }

        .code-container {
            overflow-x: auto;
            min-width: 100%;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            background-color: var(--background-color);
        }

        .job-seeker-answer pre,
        .correct-answer pre,
        .code-template {
            white-space: pre; /* Change from pre-wrap to pre */
            word-break: normal; /* Change from break-all */
            overflow-x: auto; /* Ensure horizontal scroll */
            display: block;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: 'Consolas', 'Monaco', monospace; /* Specify monospace fonts */
            font-size: 0.9em;
            tab-size: 4; /* Add tab size */
            -moz-tab-size: 4; /* Firefox support */
            text-align: left; /* Add this */
            line-height: 1.5; /* Add this for better readability */
        }

        .code-template {
            background-color: var(--background-color-extra-light);
            color: var(--text-color);
        }

        .score-passed {
            color: var(--success-color) !important;
        }

        .score-failed {
            color: var(--danger-color) !important;
        }

        .language-indicator {
            padding: 5px 10px;
            border-radius: 4px 4px 0 0;
            font-weight: bold;
            margin-bottom: 0;
            display: inline-block;
            color: white;
        }

        .question {
            margin-bottom: 20px; 
            padding: 15px;   
            border: 1px solid #ddd; 
            border-radius: 8px;  

            width: 100%; 
            display: block;  
            box-sizing: border-box; 
        }

        .question-text {
            font-weight: bold;
            margin-bottom: 10px;
            text-align: left; /* Add this */
        }

        .job-seeker-answer,
        .correct-answer {
            margin-top: 10px;
            text-align: left; /* Add this */
        }

        .job-seeker-answer strong, 
        .correct-answer strong {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            text-align: left; /* Add this */
        }

        .section-navigator {
            padding: 20px;
            background-color: var(--background-color-medium);
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .section-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .section-nav-item {
            padding: 8px 16px;
            background-color: var(--background-color-light);
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            color: var(--text-color);
        }

        .section-nav-item:hover,
        .section-nav-item.active {
            background-color: var(--primary-color);
        }

        .section-questions {
            margin-bottom: 40px;
            scroll-margin-top: 100px;
        }

        .section-questions h2 {
            color: var(--text-color);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--background-color-light);
        }

        .page-wrapper {
            width: 95%;
            margin: 0 auto;
            min-width: auto;
            max-width: 1200px;
        }

        .assessment-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 10px;
            width: 100%;
            margin: 0 auto;
        }

        .questions-section {
            flex: 2;
            min-width: 0;
            background-color: var(--background-color-medium);
            color: var(--text-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-y: auto;
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 140px);
        }

        .question {
            background-color: var(--background-color);
            border-color: var(--background-color-light);
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid var(--background-color-light);
            border-radius: 4px;
            max-width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        .summary_header {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            padding: 20px;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }

        .candidate-info {
            width: 100%;
            max-width: 100%;
            padding: 10px;
            margin: 0 auto 20px;
            text-align: center;
        }

        .profile-section {
            margin-bottom: 20px;
        }

        .profile-section img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .name {
            font-size: 1.5em;
            font-weight: bold;
            margin-bottom: 15px;
        }

        .details-container {
            background-color: var(--background-color-light);
            border-radius: 8px;
            padding: 20px;
        }

        .details {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }

        .detail-item {
            padding: 8px 16px;
            background-color: var(--background-color);
            border-radius: 4px;
            white-space: nowrap;
        }

        .score-time {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid var(--background-color);
        }

        @media screen and (max-width: 1024px) {
            .details {
                gap: 10px;
            }

            .detail-item {
                font-size: 0.9em;
                padding: 6px 12px;
            }

            .section-nav-list {
                gap: 5px;
            }

            .section-nav-item {
                padding: 6px 12px;
                font-size: 0.9em;
            }
        }

        @media screen and (max-width: 768px) {
            .page-wrapper {
                width: 100%;
                padding: 10px;
            }

            .assessment-container {
                padding: 5px;
            }

            .summary_header {
                padding: 10px;
            }

            .details {
                flex-direction: column;
                align-items: stretch;
            }

            .detail-item {
                width: 100%;
                text-align: center;
            }

            .questions-section {
                padding: 10px;
            }

            /* Make code sections responsive */
            .code-container, 
            .job-seeker-answer pre,
            .correct-answer pre {
                font-size: 0.8em;
                max-width: 100%;
                overflow-x: auto;
            }

            .section-nav-list {
                flex-direction: column;
                align-items: stretch;
            }
            
            .section-nav-item {
                text-align: center;
                width: 100%;
            }

            .details, .score-time {
                flex-direction: column;
                gap: 10px;
            }
            
            .divider {
                display: none;
            }
        }

        @media screen and (max-width: 480px) {
            .profile-section img {
                width: 80px;
                height: 80px;
            }

            .name {
                font-size: 1.2em;
            }

            .assessment-title {
                font-size: 1.2em;
            }

            .question-text {
                font-size: 0.9em;
            }

            /* Adjust footer for mobile */
            .footer-content {
                flex-direction: column;
                text-align: center;
            }

            .footer-right {
                margin-top: 20px;
            }

            .footer-column {
                width: 100%;
                margin-bottom: 20px;
            }
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
                <li><a href="#">Candidates</a>
                    <ul class="dropdown">
                        <li><a href="search_candidate.php">Search Candidates</a></li>
                    </ul>
                </li>
                <li><a href="#">Resources</a>
                    <ul class="dropdown">
                        <li><a href="useful_links.php">Useful Links</a></li>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="sitemap.php">Sitemap</a></li>
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

<div id="logout-popup" class="popup">
    <h2>Are you sure you want to Log Out?</h2>
    <button class="close-button" onclick="logoutUser()">Yes</button>
    <button class="cancel-button" onclick="closePopup('logout-popup')">No</button>
</div>

<div class="page-wrapper">
    <div class="assessment-container">
        <div class="summary_header">
            <div class="assessment-title">Candidate Profile & Answers</div>
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

            $job_seeker_id = $_GET['job_seeker_id'];

            // Get candidate info
            $sql_candidate = "SELECT u.first_name, u.last_name, js.education_level, js.year_of_experience, js.linkedin_link
                            FROM User u
                            JOIN Job_Seeker js ON u.user_id = js.user_id
                            WHERE js.job_seeker_id = ?";
            
            $stmt = $conn->prepare($sql_candidate);
            $stmt->bind_param("s", $job_seeker_id);
            $stmt->execute();
            $result_candidate = $stmt->get_result();

            if ($result_candidate->num_rows > 0) {
                $row_candidate = $result_candidate->fetch_assoc();
                
                // Get score and time info
                $score = "N/A";
                $time_used = "N/A";
            
                $sql_score_time = "SELECT ajs.score, TIMEDIFF(ajs.end_time, ajs.start_time) AS time_used,
                                            ast.passing_score_percentage
                                    FROM Assessment_Job_Seeker ajs
                                    CROSS JOIN Assessment_Settings ast
                                    WHERE ajs.job_seeker_id = ? 
                                    AND ast.setting_id = '1'";
                        
                $stmt = $conn->prepare($sql_score_time);
                $stmt->bind_param("s", $job_seeker_id);
                $stmt->execute();
                $result_score_time = $stmt->get_result();
            
                if ($result_score_time->num_rows > 0) {
                    $row_score_time = $result_score_time->fetch_assoc();
                    $score = !is_null($row_score_time['score']) ? $row_score_time['score'] : 'N/A';
                    $time_used_value = $row_score_time['time_used'];
                    $time_used = !empty($time_used_value) ? $time_used_value : 'N/A';
                    $passing_score = $row_score_time['passing_score_percentage'];
                    
                    // Create score display with color coding
                    $score_class = ($score !== 'N/A' && $score >= $passing_score) ? 'score-passed' : 'score-failed';
                    echo "<div class='detail-item'>Score: <span class='" . $score_class . "'>" . $score . "/100</span></div>";
                } else {
                    echo "<div class='detail-item'>Score: N/A</div>";
                }
            
                // Display candidate info
                echo "<div class='candidate-info'>";
                echo "<div class='profile-section'>";
                echo "<img src='images/usericon.png' alt='User Icon'>";
                echo "<div class='name'>" . htmlspecialchars($row_candidate['first_name']) . " " . htmlspecialchars($row_candidate['last_name']) . "</div>";
                echo "</div>";
                
                echo "<div class='details-container'>";
                echo "<div class='details'>";
                
                if (!empty($row_candidate['linkedin_link'])) {
                    echo "<div class='detail-item'><a href='" . htmlspecialchars($row_candidate['linkedin_link']) . "' target='_blank'>LinkedIn Profile</a></div>";
                } else {
                    echo "<div class='detail-item'>LinkedIn Profile: N/A</div>";
                }
                
                $education_level = !empty($row_candidate['education_level']) ? htmlspecialchars($row_candidate['education_level']) : 'N/A';
                echo "<div class='detail-item'>Education Level: " . $education_level . "</div>";
                
                $experience = (!empty($row_candidate['year_of_experience']) || $row_candidate['year_of_experience'] === '0') ? htmlspecialchars($row_candidate['year_of_experience']) . " Years" : 'N/A';
                echo "<div class='detail-item'>Years of Experience: " . $experience . "</div>";
                

                echo "<div class='detail-item'>Time Used: " . $time_used . "</div>";
                
                echo "</div>";
                
                echo "</div>"; // Close details-container
                echo "</div>"; // Close candidate-info
            } else {
                echo "<h1>Candidate not found</h1>";
            }

            // Get score and time info
            $score = "N/A";
            $time_used = "N/A";

            $sql_score_time = "SELECT score, TIMEDIFF(end_time, start_time) AS time_used
                              FROM Assessment_Job_Seeker 
                              WHERE job_seeker_id = ?";
            
            $stmt = $conn->prepare($sql_score_time);
            $stmt->bind_param("s", $job_seeker_id);
            $stmt->execute();
            $result_score_time = $stmt->get_result();

            if ($result_score_time->num_rows > 0) {
                $row_score_time = $result_score_time->fetch_assoc();
                $score = !is_null($row_score_time['score']) ? $row_score_time['score'] : 'N/A';
                $time_used_value = $row_score_time['time_used'];
                $time_used = !empty($time_used_value) ? $time_used_value : 'N/A';
            }
            ?>
        </div>

        <div class="section-navigator">
            <ul class="section-nav-list">
                <?php
                $sections = [
                    'general' => 'General Questions',
                    'scenario' => 'Scenario-Based Questions', 
                    'programming' => 'Programming Questions',
                    'personality' => 'Work-Style and Personality'
                ];

                foreach ($sections as $id => $name): ?>
                    <li class="section-nav-item" onclick="scrollToSection('<?php echo $id; ?>')"><?php echo $name; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="questions-section">
            <?php
            // Get questions and answers
            $sql_questions = "SELECT q.question_id, q.question_text, q.correct_answer, q.answer_type,
                                    q.programming_language, q.code_template,
                                    a.answer_text, a.is_correct,
                                    c.choice_text AS job_seeker_choice_text,
                                    q.assessment_id
                            FROM question q
                            INNER JOIN answer a ON q.question_id = a.question_id
                            LEFT JOIN choices c ON a.answer_text = c.choice_id
                            WHERE a.job_seeker_id = ?
                            ORDER BY q.assessment_id, q.question_id";
            
            $stmt = $conn->prepare($sql_questions);
            $stmt->bind_param("s", $job_seeker_id);
            $stmt->execute();
            $result_questions = $stmt->get_result();

            if ($result_questions && $result_questions->num_rows > 0) {
                foreach ($sections as $id => $name) {
                    echo "<div id='$id' class='section-questions'>";
                    echo "<h2>$name</h2>";
                    
                    // Map sections to assessment IDs
                    $section_mapping = [
                        'general' => 'AS75',
                        'scenario' => 'AS76',
                        'programming' => ['AS77', 'AS78', 'AS79', 'AS80'],
                        'personality' => 'AS81'
                    ];
                    
                    // Reset result pointer and counters
                    $result_questions->data_seek(0);
                    $question_counter = 1;
                    
                    while ($row_question = $result_questions->fetch_assoc()) {
                        $current_section = $row_question['assessment_id'];
                        
                        // Check if question belongs to current section
                        $show_question = false;
                        if (is_array($section_mapping[$id])) {
                            $show_question = in_array($current_section, $section_mapping[$id]);
                        } else {
                            $show_question = ($current_section === $section_mapping[$id]);
                        }
                        
                        if ($show_question) {
                            echo "<div class='question'>";
                            echo "<div class='question-text'><strong>Question " . $question_counter . ":</strong> " . 
                                 htmlspecialchars($row_question['question_text']) . "</div>";

                            if ($row_question['answer_type'] === 'code') {
                                if (!empty($row_question['programming_language'])) {
                                    echo "<div class='language-indicator'>";
                                    echo "Language: " . ucfirst(htmlspecialchars($row_question['programming_language']));
                                    echo "</div>";
                                }
                                if (!empty($row_question['code_template'])) {
                                    echo "<div class='code-container'>";
                                    echo "<pre class='code-template'>" . htmlspecialchars($row_question['code_template']) . "</pre>";
                                    echo "</div>";
                                }
                            }

                            echo "<div class='job-seeker-answer'>";
                            echo "<strong>Job Seeker's Answer:</strong> <pre>";
                            if ($row_question['answer_text'] !== null) {
                                $answer_to_display = '';
                                if ($row_question['answer_type'] === 'multiple choice') {
                                    $answer_to_display = $row_question['job_seeker_choice_text'];
                                    if (empty($answer_to_display)) {
                                        $answer_to_display = "Choice ID: " . htmlspecialchars($row_question['answer_text']) . 
                                                           " (Choice Text Not Found)";
                                    }
                                } else {
                                    $answer_to_display = $row_question['answer_text'];
                                }
                                $answer_text_with_breaks = str_replace("<<ANSWER_BREAK>>", "\n", $answer_to_display);
                                echo htmlspecialchars($answer_text_with_breaks);
                            } else {
                                echo "No answer provided.";
                            }
                            echo "</pre></div>";

                            if (!in_array($current_section, ['AS75', 'AS81'])) {
                                echo "<div class='correct-answer'>";
                                echo "<strong>Correct Answer:</strong> <pre>";
                                if ($row_question['correct_answer'] !== null) {
                                    $correct_answer_with_breaks = str_replace("<<ANSWER_BREAK>>", "\n", $row_question['correct_answer']);
                                    echo htmlspecialchars($correct_answer_with_breaks);
                                } else {
                                    echo "No correct answer provided.";
                                }
                                echo "</pre></div>";
                            }

                            echo "</div>";
                            $question_counter++;
                        }
                    }
                    echo "</div>";
                }
            } else {
                echo "<div>No questions found or no answers provided by the job seeker.</div>";
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>
    </div>
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
                    <h3>Candidate</h3>
                    <ul>
                        <li><a href="search_candidate.php">Search Candidates</a></li>
                    </ul>
                </div>
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
    <script>
        function updateAssessment() {
            const assessmentSelect = document.getElementById('assessment-select');
            const selectedAssessmentId = assessmentSelect.value;
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('assessment_id', selectedAssessmentId);
            window.location.search = urlParams.toString();
        }

        function scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                // Update active state
                document.querySelectorAll('.section-nav-item').forEach(item => {
                    item.classList.remove('active');
                });
                const activeItem = document.querySelector(`[onclick="scrollToSection('${sectionId}')"]`);
                if (activeItem) {
                    activeItem.classList.add('active');
                }
            }
        }

        // Track scroll position to update active section
        document.addEventListener('scroll', () => {
            const sections = document.querySelectorAll('.section-questions');
            let currentSection = '';
            
            sections.forEach(section => {
                const rect = section.getBoundingClientRect();
                if (rect.top <= 100) {
                    currentSection = section.id;
                }
            });
            
            if (currentSection) {
                document.querySelectorAll('.section-nav-item').forEach(item => {
                    item.classList.remove('active');
                });
                document.querySelector(`[onclick="scrollToSection('${currentSection}')"]`)?.classList.add('active');
            }
        });
    </script>
    <script src="scripts.js"></script>
</body>
</html>
```