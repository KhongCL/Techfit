<?php
session_start(); // Start the session to access session variables

// Function to display the message and options
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

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); // Display message and options if not logged in
}

// Check if the user has the correct role
if ($_SESSION['role'] !== 'Job Seeker') {
    displayLoginMessage(); // Display message and options if the role is not Job Seeker
}

// Check if the job seeker ID is set
if (!isset($_SESSION['job_seeker_id'])) {
    displayLoginMessage(); // Display message and options if job seeker ID is not set
}

// Close the session
session_write_close();

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve assessment ID from query parameter
$assessment_id = $_GET['assessment_id'];

// Fetch assessment details and answers
$sql = "
    SELECT 
    Assessment_Job_Seeker.end_time AS assessment_date, 
    Assessment_Job_Seeker.score,
    Question.assessment_id,
    Question.question_text,
    Question.answer_type,
    Question.programming_language,
    Question.code_template,
    Answer.answer_text AS user_answer,
    Question.correct_answer,
    Answer.is_correct,
    CASE 
        WHEN Question.answer_type = 'multiple choice' THEN c.choice_text 
        ELSE Answer.answer_text 
    END as display_answer
    FROM Assessment_Job_Seeker
    JOIN Question ON Question.assessment_id IN ('AS75', 'AS76', 'AS77', 'AS78', 'AS79', 'AS80', 'AS81')
    LEFT JOIN Answer ON Answer.job_seeker_id = Assessment_Job_Seeker.job_seeker_id 
        AND Answer.question_id = Question.question_id
    LEFT JOIN Choices c ON (Question.answer_type = 'multiple choice' AND Answer.answer_text = c.choice_id)
    WHERE Assessment_Job_Seeker.result_id = ? 
    AND Assessment_Job_Seeker.job_seeker_id = ?
    ORDER BY Question.assessment_id, Question.question_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $assessment_id, $_SESSION['job_seeker_id']);
$stmt->execute();
$result = $stmt->get_result();

$assessment_details = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        :root {
            --primary-color: #007bff;
            --accent-color: #5c7dff; 
            --danger-color: #e74c3c; 
            --danger-color-hover: #c0392b;
            --success-color: #28a745;
            --success-color-hover: #2ecc71;
            --background-color: #121212;
            --background-color-medium: #1E1E1E;
            --background-color-light: #444;
            --text-color: #fafafa;
            --text-color-dark: #b0b0b0;
        }

        #assessment-summary {
            padding: 20px;
            background-color: var(--background-color);
            font-family: Arial, sans-serif;
        }

        .container_a_s {
            max-width: 800px;
            margin: 0 auto;
            background: var(--background-color-medium);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            border-radius: 8px;
            overflow: hidden;
        }

        .summary_header {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            text-align: left;
            width: 100%;
        }

        .summary_header h2 {
            margin: 0;
            color: #333;
            font-size: 1.5em;
            margin-bottom: 15px;
        }

        .summary_header p {
            margin: 5px 0;
            color: #666;
            font-size: 1em;
            line-height: 1.4;
        }

        .summary-item {
            background-color: var(--background-color-light);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .summary-details h3 {
            margin: 0;
            color: var(--text-color);
            font-size: 1.2em;
        }

        .summary-details p {
            margin: 5px 0;
            color: var(--text-color-dark);
            font-size: 1em;
        }

        .summary-item span.green {
            background-color: var(--success-color);
        }

        .summary-item span.red {
            background-color: var(--danger-color);
        }

        .scrollable {
            max-height: 400px;
            overflow-y: auto;
            padding: 20px;
        }

        .scrollable::-webkit-scrollbar {
            width: 10px;
        }

        .scrollable::-webkit-scrollbar-track {
            background: var(--background-color-light);
            border-radius: 10px;
        }

        .scrollable::-webkit-scrollbar-thumb {
            background: var(--text-color-dark);
            border-radius: 10px;
        }

        .scrollable::-webkit-scrollbar-thumb:hover {
            background: var(--text-color);
        }

        .code-container {
            font-family: 'Consolas', monospace;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 4px;
            margin-top: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
            tab-size: 4;
        }

        .code-template {
            margin: 0;
            font-family: inherit;
            white-space: pre;
            tab-size: 4;
            line-height: 1.5;
        }

        .answers-section {
            margin-top: 20px;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }

        .answer-list {
            margin: 10px 0;
            padding-left: 20px;
        }

        .answer-list li {
            margin: 5px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .language-indicator {
            background: #e9ecef;
            padding: 8px 12px;
            border-radius: 4px 4px 0 0;
            font-weight: bold;
            margin-bottom: 0;
        }

        .point-value {
            color: #666;
            font-size: 0.9em;
            margin-left: 10px;
        }

        .status-indicator {
            margin-left: 10px;
            font-weight: bold;
        }

        .status-correct {
            color: var(--success-color);
        }

        .status-incorrect {
            color: var(--danger-color);
        }

        .assessment-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .questions-section {
            flex: 2;
            min-width: 0;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-y: auto;
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 140px);
            height: fit-content;
        }

        .section-header {
            font-size: 1.2em;
            font-weight: bold;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #eee;
        }

        .question-item {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 4px;
        }

        @media screen and (max-width: 1024px) {
            .assessment-container {
                flex-direction: column;
            }
            
            .questions-section {
                width: 100%;
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
                    <li><a href="#">Assessment</a>
                        <ul class="dropdown">
                            <li><a href="start_assessment.php">Start Assessment</a></li>
                            <li><a href="assessment_history.php">Assessment History</a></li>
                            <li><a href="assessment_summary.php">Assessment Summary</a></li>
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
                                    // Check if the user is logged in and display their username
                                    if (isset($_SESSION['username'])) {
                                        echo $_SESSION['username'];  // Display the username from session
                                    } else {
                                        echo "Guest";  // Default if not logged in
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

    <div class="assessment-container">
        <div class="summary_header">
            <h2>View Answers</h2>
            <?php if (!empty($assessment_details)): ?>
                <p>Assessment ID: <?= $assessment_id; ?></p>
                <p>Assessment Date: <?= date('d/m/Y', strtotime($assessment_details[0]['assessment_date'])); ?></p>
                <p>Score: <?= $assessment_details[0]['score']; ?>%</p>
            <?php endif; ?>
        </div>
        <div class="questions-section">
            <?php if (!empty($assessment_details)): ?>
                <?php
                $current_section = '';
                $sections = [
                    'AS75' => 'General Questions',
                    'AS76' => 'Scenario-Based Questions',
                    'AS77' => 'Python Programming',
                    'AS78' => 'Java Programming',
                    'AS79' => 'JavaScript Programming',
                    'AS80' => 'C++ Programming',
                    'AS81' => 'Work-Style and Personality'
                ];

                foreach ($assessment_details as $detail):
                    if (in_array($detail['assessment_id'], ['AS77', 'AS78', 'AS79']) && 
                        $detail['programming_language'] !== 'cpp') {
                        continue;
                    }

                    if ($detail['assessment_id'] !== $current_section) {
                        if ($current_section !== '') {
                            echo "</div>";
                        }
                        $current_section = $detail['assessment_id'];
                        echo "<h2 class='section-header'>{$sections[$current_section]}</h2>";
                        echo "<div class='section-questions'>";
                    }
                ?>
                    <div class="question-item">
                        <p class="question-text"><?= htmlspecialchars($detail['question_text']); ?></p>
                        <div class="answer-pair">
                            <?php if ($detail['answer_type'] === 'code'): ?>
                                <!-- Code question display -->
                                <?php if (!empty($detail['programming_language'])): ?>
                                    <div class="language-indicator">
                                        Language: <?= ucfirst($detail['programming_language']); ?>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($detail['code_template'])): ?>
                                    <div class="code-container">
                                        <pre class="code-template"><?= htmlspecialchars($detail['code_template']); ?></pre>
                                    </div>
                                <?php endif; ?>

                                <div class="answers-section">
                                    <h4>Your Answers:</h4>
                                    <?php
                                    $user_answers = explode('<<ANSWER_BREAK>>', $detail['user_answer']);
                                    $correct_answers = explode('<<ANSWER_BREAK>>', $detail['correct_answer']);
                                    ?>
                                    <ol class="answer-list">
                                        <?php foreach ($user_answers as $index => $answer): ?>
                                            <?php
                                            $is_correct = isset($correct_answers[$index]) && 
                                                        trim($answer) === trim($correct_answers[$index]);
                                            $point_value = round(100/count($correct_answers))/100;
                                            ?>
                                            <li>
                                                <?= htmlspecialchars($answer); ?>
                                                <?php if (!in_array($detail['assessment_id'], ['AS75', 'AS81'])): ?>
                                                    <span class="status-indicator <?= $is_correct ? 'status-correct' : 'status-incorrect' ?>">
                                                        <?= $is_correct ? '✓' : '✗' ?>
                                                    </span>
                                                    <span class="point-value">(<?= number_format($point_value, 2) ?> points)</span>
                                                <?php endif; ?>
                                            </li>
                                        <?php endforeach; ?>
                                    </ol>

                                    <?php if (!in_array($detail['assessment_id'], ['AS75', 'AS81'])): ?>
                                        <h4>Correct Answers:</h4>
                                        <ol class="answer-list">
                                            <?php foreach ($correct_answers as $answer): ?>
                                                <li class="correct-answer"><?= htmlspecialchars($answer); ?></li>
                                            <?php endforeach; ?>
                                        </ol>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <!-- Multiple choice question display -->
                                <div class="your-answer">Your Answer: <?= htmlspecialchars($detail['display_answer']); ?></div>
                                <?php if (!in_array($detail['assessment_id'], ['AS75', 'AS81'])): ?>
                                    <div class="correct-answer">
                                        Correct Answer: <?= htmlspecialchars($detail['correct_answer']); ?>
                                        <span class="status-indicator <?= $detail['is_correct'] ? 'status-correct' : 'status-incorrect' ?>">
                                            <?= $detail['is_correct'] ? '✓' : '✗' ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if ($current_section !== '') echo "</div>"; ?>
            <?php else: ?>
                <p>No answers found for this assessment.</p>
            <?php endif; ?>
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
                    <p>techfit@gmail.com</p>
                </div>
            </div>
            <div class="footer-right">
                <div class="footer-column">
                    <h3>Assessment</h3>
                    <ul>
                        <li><a href="start_assessment.php">Start Assessment</a></li>
                        <li><a href="assessment_history.php">Assessment History</a></li>
                        <li><a href="assessment_summary.php">Assessment Summary</a></li>
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

    <script src="scripts.js?v=1.0"></script>
    <script>
        function openPopup(popupId) {
            document.getElementById(popupId).style.display = 'block';
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        function logoutUser() {
            window.location.href = '/Techfit'; 
        }
    </script>

</body>
</html>