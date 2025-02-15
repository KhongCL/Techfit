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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    displayLoginMessage();
}

// Check if user is a job seeker
if ($_SESSION['role'] !== 'Job Seeker') {
    displayLoginMessage();
}

// Check if job_seeker_id exists
if (!isset($_SESSION['job_seeker_id'])) {
    displayLoginMessage();
}

$db_host = 'localhost';
$db_user = 'root'; 
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

$check_completion_sql = "SELECT COUNT(*) as completed 
                        FROM Assessment_Job_Seeker 
                        WHERE job_seeker_id = ? AND end_time IS NOT NULL";

$stmt = $conn->prepare($check_completion_sql);
$stmt->bind_param("s", $_SESSION['job_seeker_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['completed'] == 0) {
    // No completed assessment found
    echo '<script>
        if (confirm("You have not completed any assessment yet. Would you like to start an assessment?")) {
            window.location.href = "start_assessment.php";
        } else {
            window.location.href = "index.php";
        }
    </script>';
    exit();
}

// Check if user has any answers
$check_answers_sql = "SELECT COUNT(*) as has_answers 
                     FROM Answer
                     WHERE job_seeker_id = ?";

$stmt = $conn->prepare($check_answers_sql);
$stmt->bind_param("s", $_SESSION['job_seeker_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['has_answers'] == 0) {
    // No answers found
    echo '<script>
        if (confirm("No answers found for your assessment. Would you like to start a new assessment?")) {
            window.location.href = "start_assessment.php";
        } else {
            window.location.href = "index.php";
        }
    </script>';
    exit();
}

$job_seeker_id = $_SESSION['job_seeker_id'];

function calculateScore($conn, $job_seeker_id) {
    // First evaluate multiple choice questions
    $mc_sql = "UPDATE Answer a 
               JOIN Question q ON a.question_id = q.question_id 
               JOIN Choices c ON (a.answer_text = c.choice_id)
               SET a.is_correct = (c.choice_text = q.correct_answer),
                   a.score_percentage = CASE 
                       WHEN c.choice_text = q.correct_answer THEN 100
                       ELSE 0 
                   END
               WHERE a.job_seeker_id = ? 
               AND q.assessment_id = 'AS76'";

    $stmt = $conn->prepare($mc_sql);
    $stmt->bind_param("s", $job_seeker_id);
    $stmt->execute();

    // Then evaluate code questions
    $code_sql = "SELECT a.answer_id, a.question_id, a.answer_text, q.correct_answer 
                 FROM Answer a 
                 JOIN Question q ON a.question_id = q.question_id 
                 WHERE a.job_seeker_id = ? 
                 AND q.assessment_id IN ('AS77', 'AS78', 'AS79', 'AS80')";

    $stmt = $conn->prepare($code_sql);
    $stmt->bind_param("s", $job_seeker_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Prepare update statement once
    $update_sql = "UPDATE Answer SET is_correct = ?, score_percentage = ? WHERE answer_id = ?";
    $update_stmt = $conn->prepare($update_sql);

    while ($row = $result->fetch_assoc()) {
        $user_answers = explode('<<ANSWER_BREAK>>', $row['answer_text']);
        $correct_answers = explode('<<ANSWER_BREAK>>', $row['correct_answer']); 
        
        $correct_count = 0;
        $total_blanks = count($correct_answers);
        $points_per_blank = round(100 / $total_blanks, 2); // Round to 2 decimal places
        
        for ($i = 0; $i < $total_blanks; $i++) {
            if (isset($user_answers[$i]) && trim($user_answers[$i]) === trim($correct_answers[$i])) {
                $correct_count++;
            }
        }

        // Calculate total score for this question
        $score_percentage = $correct_count * $points_per_blank;
        $is_correct = ($score_percentage == 100) ? 1 : 0;

        // Update the answer record
        $update_stmt->bind_param("ids", $is_correct, $score_percentage, $row['answer_id']);
        $update_stmt->execute();

        // Add debug logging
        error_log("Updating answer {$row['answer_id']}: correct_count=$correct_count, total_blanks=$total_blanks, score=$score_percentage%");
    }
}

calculateScore($conn, $job_seeker_id);

// Get assessment settings and time info
$settings_sql = "SELECT passing_score_percentage FROM Assessment_Settings WHERE setting_id = '1'";
$settings_result = $conn->query($settings_sql);
$settings = $settings_result->fetch_assoc();
$passing_score = $settings['passing_score_percentage'];

// Get assessment duration
$time_sql = "SELECT TIMESTAMPDIFF(SECOND, start_time, end_time) as duration 
             FROM Assessment_Job_Seeker 
             WHERE job_seeker_id = ? 
             ORDER BY end_time DESC LIMIT 1";
$stmt = $conn->prepare($time_sql);
$stmt->bind_param("s", $job_seeker_id);
$stmt->execute();
$time_result = $stmt->get_result();
$time_info = $time_result->fetch_assoc();
$duration_seconds = $time_info['duration']; // Changed from $duration to $duration_seconds

// Calculate minutes and seconds
$minutes = floor($duration_seconds / 60);
$seconds = $duration_seconds % 60;

// Get section scores
$section_scores = [];
$total_score = 0;
$total_questions = 0;

// Only count sections 2 and 3 for scoring
$score_sql = "SELECT 
    q.assessment_id,
    COUNT(*) as total,
    SUM(CASE 
        WHEN q.answer_type = 'code' THEN a.score_percentage/100
        WHEN a.is_correct = 1 THEN 1 
        ELSE 0 
    END) as correct,
    COALESCE(
        AVG(CASE 
            WHEN q.answer_type = 'code' THEN a.score_percentage
            WHEN a.is_correct = 1 THEN 100
            ELSE 0
        END)
    , 0) as percentage
FROM Answer a
JOIN Question q ON a.question_id = q.question_id 
WHERE a.job_seeker_id = ?
AND q.assessment_id IN ('AS76', 'AS77', 'AS78', 'AS79', 'AS80')
GROUP BY q.assessment_id";

$stmt = $conn->prepare($score_sql);
$stmt->bind_param("s", $job_seeker_id);
$stmt->execute();
$score_result = $stmt->get_result();

error_log("Calculating section scores:");
while ($row = $score_result->fetch_assoc()) {
    error_log("Section {$row['assessment_id']}: correct={$row['correct']}, total={$row['total']}, percentage={$row['percentage']}");
    
    $section_scores[$row['assessment_id']] = [
        'correct' => round($row['correct'], 1),
        'total' => $row['total'], 
        'percentage' => round($row['percentage'], 1)
    ];
    
    $total_score += $row['correct'];
    $total_questions += $row['total'];
}

$overall_score = ($total_questions > 0) ? ($total_score / $total_questions) * 100 : 0;
error_log("Final totals: score=$total_score, questions=$total_questions, overall_score=$overall_score%");

$final_score = round($overall_score);
$passed = $final_score >= $passing_score;

$update_sql = "UPDATE Assessment_Job_Seeker 
               SET score = ?
               WHERE job_seeker_id = ? 
               AND end_time IS NOT NULL
               ORDER BY start_time DESC LIMIT 1";

$stmt = $conn->prepare($update_sql);
$stmt->bind_param("is", $final_score, $job_seeker_id);
$stmt->execute();

$programming_sql = "SELECT DISTINCT q.assessment_id 
FROM Answer a 
JOIN Question q ON a.question_id = q.question_id 
WHERE a.job_seeker_id = ? 
AND q.assessment_id IN ('AS77', 'AS78', 'AS79', 'AS80')";
$stmt = $conn->prepare($programming_sql);
$stmt->bind_param("s", $job_seeker_id);
$stmt->execute();
$prog_result = $stmt->get_result();
$row = $prog_result->fetch_assoc();
$programming_section = $row ? $row['assessment_id'] : null; 

// Define sections, only including the taken programming section
$sections = [
'AS75' => 'General Questions',
'AS76' => 'Scenario-Based Questions'
];

// Add the specific programming section
$programming_names = [
'AS77' => 'Python Programming',
'AS78' => 'Java Programming',
'AS79' => 'JavaScript Programming',
'AS80' => 'C++ Programming'
];
if ($programming_section) {
$sections[$programming_section] = $programming_names[$programming_section];
}

$sections['AS81'] = 'Work-Style and Personality';

$check_answers_sql = "SELECT COUNT(*) as answer_count 
                     FROM Answer 
                     WHERE job_seeker_id = ?";
$stmt = $conn->prepare($check_answers_sql);
$stmt->bind_param("s", $job_seeker_id);
$stmt->execute();
$answer_count_result = $stmt->get_result();
$answer_count = $answer_count_result->fetch_assoc()['answer_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Results - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .main-content {
            display: flex;
            gap: 40px;
            align-items: flex-start;
            width: 100%;
        }

        .main-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content-wrapper {
            flex: 1;
            margin-bottom: 0; /* Remove bottom margin */
            padding-bottom: 40px; /* Reduce padding */
        }

        .assessment-container {
            display: flex;
            flex-direction: column;
            gap: 40px;
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
            align-items: center;
            min-height: fit-content;
        }

        .questions-section {
            flex: 2;
            width: 100%;
            min-width: 0;
            background-color: var(--background-color-medium);
            color: var(--text-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow-y: auto;
            position: sticky;
            top: 20px;
            max-height: calc(100vh - 140px); /* Adjust for header and some padding */
            height: fit-content;
        }

        .results-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 300px;
            background-color: var(--background-color-medium);
            color: var(--text-color);
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
            background-color: var(--background-color-light);
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
            background-color: var(--background-color-light);
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

        .section-questions {
            scroll-margin-top: 100px;
        }

        .button-container {
            width: 100%;
            max-width: 400px; 
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
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
            min-width: 120px; 
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
            border-color: var(--background-color-light);
            background-color: var(--background-color);
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
            background-color: var(--background-color-light);
            color: var(--text-color);
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

        .code-container {
            font-family: 'Consolas', monospace;
            background-color: var(--background-color);
            color: var(--text-color);
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
            background-color: var(--background-color-light);
            color: var(--text-color);
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

        .section-navigator {
            width: 100%;
            position: static;
            top: 20px;
            background-color: var(--background-color-medium);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid var(--background-color-light);
        }

        .section-nav-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .section-nav-item {
            padding: 8px 12px;
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

        .no-answers-message, .no-scores-message {
            background-color: var(--background-color-light);
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .no-answers-message h3 {
            margin-bottom: 10px;
        }

        .no-answers-message p {
            color: var(--text-color);
            line-height: 1.5;
        }

        @media screen and (max-width: 1024px) {
            .main-content {
                flex-direction: column;
            }

            .assessment-container {
                flex-direction: column;
            }
            
            .results-container {
                position: relative;
                top: 0;
                width: 100%;
            }
            
            .questions-section {
                width: 100%;
            }
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
                <div class="section-navigator">
                    <ul class="section-nav-list">
                        <?php foreach ($sections as $id => $name): ?>
                            <li class="section-nav-item" onclick="scrollToSection('<?php echo $id; ?>')"><?php echo $name; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="main-content">
                    <div class="questions-section">
                        <?php if ($answer_count === 0): ?>
                            <div class="no-answers-message" style="text-align: center; padding: 20px;">
                                <h3 style="color: var(--danger-color);">No Questions Answered</h3>
                                <p>You did not provide any answers during the assessment.</p>
                            </div>
                        <?php else: ?>
                            <?php
                            // Get questions and answers grouped by section
                            $sections_sql = "SELECT 
                                q.assessment_id,
                                q.question_text,
                                q.answer_type,
                                q.correct_answer,
                                q.programming_language,
                                q.code_template,
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

                            while ($row = $result->fetch_assoc()) {
                                if ($current_section !== $row['assessment_id']) {
                                    if ($current_section !== '') {
                                        echo "</div>";
                                    }
                                    $current_section = $row['assessment_id'];
                                    echo "<h2 class='section-header'>{$sections[$current_section]}</h2>";
                                    echo "<div id='{$current_section}' class='section-questions'>";
                                }
                            
                                echo "<div class='question-item'>";
                                echo "<p class='question-text'>" . htmlspecialchars($row['question_text']) . "</p>";
                                
                                echo "<div class='answer-pair'>";
                                if ($row['answer_type'] !== 'code') {
                                    echo "<div class='your-answer'>Your Answer: " . htmlspecialchars($row['display_answer']) . "</div>";
                                }
                                
                                // Show correct answer for all questions except AS75 and AS81 (which are not scored)
                                if (in_array($row['assessment_id'], ['AS76', 'AS77', 'AS78', 'AS79', 'AS80'])) {
                                    if ($row['answer_type'] === 'code') {
                                        // Code question display logic (keep existing code block display)
                                        if (!empty($row['programming_language'])) {
                                            echo "<div class='language-indicator'>";
                                            echo "Language: " . ucfirst($row['programming_language']);
                                            echo "</div>";
                                        }
                                
                                        if (!empty($row['code_template'])) {
                                            echo "<div class='code-container'>";
                                            echo "<pre class='code-template'>" . htmlspecialchars($row['code_template']) . "</pre>";
                                        }
                                        
                                        echo "<div class='answers-section'>";
                                        echo "<h4>Your Answers:</h4>";
                                        $user_answers = explode('<<ANSWER_BREAK>>', $row['display_answer']);
                                        $correct_answers = explode('<<ANSWER_BREAK>>', $row['correct_answer']);
                                        
                                        echo "<ol class='answer-list'>";
                                        foreach ($user_answers as $index => $answer) {
                                            $is_correct = trim($answer) === trim($correct_answers[$index]);
                                            $point_value = round(100/count($correct_answers))/100; // Convert to decimal
                                            echo "<li>" . htmlspecialchars($answer);
                                            echo "<span class='status-indicator " . 
                                                ($is_correct ? 'status-correct' : 'status-incorrect') . "'>" .
                                                ($is_correct ? '✓' : '✗') . "</span>";
                                            // Format point value with 2 decimal places
                                            echo "<span class='point-value'>(" . number_format($point_value, 2) . " points)</span></li>";
                                        }
                                        echo "</ol>";
                                        
                                        // Add correct answers section
                                        echo "<h4>Correct Answers:</h4>";
                                        echo "<ol class='answer-list'>";
                                        foreach ($correct_answers as $answer) {
                                            echo "<li class='correct-answer'>" . htmlspecialchars($answer) . "</li>";
                                        }
                                        echo "</ol>";
                                        echo "</div>";
                            
                                        if (!empty($row['code_template'])) {
                                            echo "</div>"; // Close code-container
                                        }
                                    } else {
                                        // Non-code questions (multiple choice, etc.)
                                        $status_class = $row['is_correct'] ? 'status-correct' : 'status-incorrect';
                                        $status_symbol = $row['is_correct'] ? '✓' : '✗';
                                        echo "<span class='status-indicator {$status_class}'>{$status_symbol}</span>";
                                        echo "<div class='correct-answer'>Correct Answer: " . htmlspecialchars($row['correct_answer']) . "</div>";
                                    }
                                }
                                
                                echo "</div></div>";
                            }
                            
                            if ($current_section !== '') {
                                echo "</div>";
                            }
                            ?>
                        <?php endif; ?>
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
                            <h3>Time Spent: <?php echo $minutes; ?> minutes <?php echo $seconds; ?> seconds</h3>
                            </div>
                            
                            <div class="section-scores">
                                <h3>Section Scores</h3>
                                <?php if ($answer_count === 0): ?>
                                    <div class="no-scores-message" style="text-align: center; padding: 10px; color: var(--danger-color);">
                                        <p>No section scores available - no questions were answered</p>
                                    </div>
                                <?php else: ?>
                                    <?php
                                    $section_names = [
                                        'AS76' => 'Scenario-Based Questions',
                                        'AS77' => 'Python Programming',
                                        'AS78' => 'Java Programming', 
                                        'AS79' => 'JavaScript Programming',
                                        'AS80' => 'C++ Programming'
                                    ];

                                    foreach ($section_scores as $assessment_id => $score) {
                                        $section_name = $section_names[$assessment_id] ?? 'Unknown Section';
                                        echo "<div class='section-score'>";
                                        echo "<span>{$section_name}</span>";
                                        echo "<span>" . number_format($score['percentage'], 1) . "%</span>";
                                        echo "</div>";
                                    }
                                    ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="button-container">
                            <button class="continue" onclick="window.location.href='assessment_history.php'">Continue</button>
                        </div>
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
                        <p><a href="mailto:techfit@gmail.com">techfit@gmail.com</a></p>
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
</body>
</html>