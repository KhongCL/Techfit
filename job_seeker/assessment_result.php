<?php
session_start();

$db_host = 'localhost';
$db_user = 'root'; 
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$job_seeker_id = $_SESSION['job_seeker_id'];

function evaluateAnswers($conn, $job_seeker_id) {
    // For multiple choice questions (Section 2)
    $mc_sql = "UPDATE Answer a 
               JOIN Question q ON a.question_id = q.question_id 
               JOIN Choices c ON (a.answer_text = c.choice_id)
               SET a.is_correct = (TRIM(c.choice_text) = TRIM(q.correct_answer))
               WHERE a.job_seeker_id = ? 
               AND q.assessment_id = 'AS76'";
    
    $stmt = $conn->prepare($mc_sql);
    $stmt->bind_param("s", $job_seeker_id);
    $stmt->execute();

    // For code questions (Section 3)
    $code_sql = "SELECT a.answer_id, a.question_id, a.answer_text, q.correct_answer 
                 FROM Answer a 
                 JOIN Question q ON a.question_id = q.question_id 
                 WHERE a.job_seeker_id = ? 
                 AND q.assessment_id IN ('AS77', 'AS78', 'AS79', 'AS80')";
    
    $stmt = $conn->prepare($code_sql);
    $stmt->bind_param("s", $job_seeker_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $user_answers = explode('<<ANSWER_BREAK>>', $row['answer_text']);
        $correct_answers = explode('<<ANSWER_BREAK>>', $row['correct_answer']);
        
        // Calculate partial score based on correct blanks
        $correct_count = 0;
        $total_blanks = count($correct_answers);
        
        for ($i = 0; $i < $total_blanks; $i++) {
            if (isset($user_answers[$i]) && trim($user_answers[$i]) === trim($correct_answers[$i])) {
                $correct_count++;
            }
        }

        // Consider it correct if at least half the blanks are correct
        $is_correct = ($correct_count / $total_blanks) >= 0.5 ? 1 : 0;
        
        // Update is_correct
        $update_sql = "UPDATE Answer SET is_correct = ? WHERE answer_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("is", $is_correct, $row['answer_id']);
        $update_stmt->execute();
    }
}

evaluateAnswers($conn, $job_seeker_id);

// Get assessment settings and time info
$settings_sql = "SELECT passing_score_percentage FROM Assessment_Settings WHERE setting_id = '1'";
$settings_result = $conn->query($settings_sql);
$settings = $settings_result->fetch_assoc();
$passing_score = $settings['passing_score_percentage'];

// Get assessment duration
$time_sql = "SELECT TIMESTAMPDIFF(MINUTE, start_time, end_time) as duration 
             FROM Assessment_Job_Seeker 
             WHERE job_seeker_id = ? 
             ORDER BY end_time DESC LIMIT 1";
$stmt = $conn->prepare($time_sql);
$stmt->bind_param("s", $job_seeker_id);
$stmt->execute();
$time_result = $stmt->get_result();
$time_info = $time_result->fetch_assoc();
$duration = $time_info['duration'];

// Get section scores
$section_scores = [];
$total_score = 0;
$total_questions = 0;

// Only count sections 2 and 3 for scoring
$score_sql = "SELECT 
    q.assessment_id,
    COUNT(CASE WHEN a.is_correct = 1 THEN 1 END) as correct,
    COUNT(*) as total
FROM Answer a
JOIN Question q ON a.question_id = q.question_id 
WHERE a.job_seeker_id = ?
AND q.assessment_id IN ('AS76', 'AS77', 'AS78', 'AS79', 'AS80')
GROUP BY q.assessment_id";

$stmt = $conn->prepare($score_sql);
$stmt->bind_param("s", $job_seeker_id);
$stmt->execute();
$score_result = $stmt->get_result();

while ($row = $score_result->fetch_assoc()) {
    $section_scores[$row['assessment_id']] = [
        'correct' => $row['correct'],
        'total' => $row['total'],
        'percentage' => ($row['total'] > 0) ? ($row['correct'] / $row['total']) * 100 : 0
    ];
    $total_score += $row['correct'];
    $total_questions += $row['total'];
}

$overall_score = ($total_questions > 0) ? ($total_score / $total_questions) * 100 : 0;
$passed = $overall_score >= $passing_score;

// Update Assessment_Job_Seeker table
$update_sql = "UPDATE Assessment_Job_Seeker 
               SET score = ?, 
                   end_time = NOW()
               WHERE job_seeker_id = ? 
               ORDER BY start_time DESC 
               LIMIT 1";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("is", $overall_score, $job_seeker_id);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Results - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <style>

        .main-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content-wrapper {
            flex: 1;
            margin-bottom: 40px;
        }

        .assessment-container {
            display: flex;
            gap: 40px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .questions-section {
            flex: 2; /* Reduce from 3 to 2 */
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .results-container {
            flex: 1; /* Reduce from 2 to 1 */
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
            height: fit-content;
        }

        .score-summary {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .result-status {
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
        }

        .passed { color: var(--success-color); }
        .failed { color: var(--danger-color); }

        .time-spent {
            margin: 20px 0;
            padding: 15px;
            background: #e9ecef;
            border-radius: 4px;
        }

        .section-scores {
            margin: 20px 0;
        }

        .section-score {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            border-bottom: 1px solid #dee2e6;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .button-container button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }

        .view-results {
            background-color: var(--primary-color);
            color: white;
        }

        .continue {
            background-color: var(--success-color);
            color: white;
        }

        .close {
            position: absolute;
            right: 20px;
            top: 20px;
            font-size: 24px;
            cursor: pointer;
        }

        .answer-status {
            display: inline-block;
            margin-left: 10px;
        }

        .correct-mark {
            color: var(--success-color);
        }

        .wrong-mark {
            color: var(--danger-color);
        }

        .score-section {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
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

        .answer-pair {
            display: flex;
            flex-direction: column;
            gap: 10px;
            margin-top: 10px;
        }

        .your-answer, .correct-answer {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }

        .correct-answer {
            color: var(--success-color);
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

        .modal, .modal-content, .close {
            display: none;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
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
                                        <?php echo isset($_SESSION['username']) ? $_SESSION['username'] : "Guest"; ?>
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

        <!-- Logout Popup -->
        <div id="logout-popup" class="popup">
            <h2>Are you sure you want to Log Out?</h2>
            <button class="close-button" onclick="logoutUser()">Yes</button>
            <button class="cancel-button" onclick="closePopup('logout-popup')">No</button>
        </div>

        <div class="content-wrapper">
            <div class="assessment-container">
                <div class="questions-section">
                    <?php
                    // Get questions and answers grouped by section
                    $sections_sql = "SELECT 
                        q.assessment_id,
                        q.question_text,
                        q.answer_type,
                        q.correct_answer,
                        a.answer_text,
                        a.is_correct,
                        CASE 
                            WHEN q.answer_type = 'multiple choice' THEN c.choice_text
                            ELSE a.answer_text 
                        END as display_answer
                    FROM Answer a
                    JOIN Question q ON a.question_id = q.question_id
                    LEFT JOIN Choices c ON (q.answer_type = 'multiple choice' AND a.answer_text = c.choice_id)
                    WHERE a.job_seeker_id = ?
                    ORDER BY q.assessment_id, q.question_id";

                    $stmt = $conn->prepare($sections_sql);
                    $stmt->bind_param("s", $job_seeker_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

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

                    while ($row = $result->fetch_assoc()) {
                        if ($current_section !== $row['assessment_id']) {
                            if ($current_section !== '') {
                                echo "</div>";
                            }
                            $current_section = $row['assessment_id'];
                            echo "<h2 class='section-header'>{$sections[$current_section]}</h2>";
                            echo "<div class='section-questions'>";
                        }

                        echo "<div class='question-item'>";
                        echo "<p class='question-text'>" . htmlspecialchars($row['question_text']) . "</p>";
                        
                        echo "<div class='answer-pair'>";
                        echo "<div class='your-answer'>Your Answer: " . htmlspecialchars($row['display_answer']) . "</div>";
                        
                        if (in_array($row['assessment_id'], ['AS76', 'AS77', 'AS78', 'AS79', 'AS80'])) {
                            echo "<div class='correct-answer'>Correct Answer: " . htmlspecialchars($row['correct_answer']) . "</div>";
                            echo "<span class='status-indicator " . 
                                 ($row['is_correct'] ? 'status-correct' : 'status-incorrect') . "'>" .
                                 ($row['is_correct'] ? '✓' : '✗') . "</span>";
                        }
                        
                        echo "</div></div>";
                    }
                    
                    if ($current_section !== '') {
                        echo "</div>";
                    }
                    ?>
                </div>

                <div class="results-container">
                    <h1>Assessment Results</h1>
                    
                    <div class="score-summary">
                        <h2>Final Score: <?php echo number_format($overall_score, 1); ?>%</h2>
                        <p class="result-status <?php echo $passed ? 'passed' : 'failed'; ?>">
                            <?php echo $passed ? 'PASSED' : 'FAILED'; ?>
                        </p>
                        <p>Passing Score: <?php echo $passing_score; ?>%</p>
                        
                        <div class="time-spent">
                            <h3>Time Spent: <?php echo $duration; ?> minutes</h3>
                        </div>
                        
                        <div class="section-scores">
                            <h3>Section Scores</h3>
                            <?php
                            foreach ($section_scores as $assessment_id => $score) {
                                $section_name = ($assessment_id === 'AS76') ? 'Scenario-Based Questions' : 'Programming Questions';
                                echo "<div class='section-score'>";
                                echo "<span>$section_name</span>";
                                echo "<span>" . number_format($score['percentage'], 1) . "% (" . 
                                     $score['correct'] . "/" . $score['total'] . ")</span>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>

                    <div class="button-container">
                        <button class="continue" onclick="window.location.href='assessment_summary.php'">Continue</button>
                    </div>
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
    </div>

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