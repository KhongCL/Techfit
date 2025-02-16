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

function displayErrorMessage() {
    echo '<script>
        if (confirm("You need to access this page from assessment history. Go to Assessment History? Click cancel to go to home page.")) {
            window.location.href = "./assessment_history.php";
        } else {
            window.location.href = "./index.php";
        }
    </script>';
    exit();
}


if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); 
}


if ($_SESSION['role'] !== 'Job Seeker') {
    displayLoginMessage(); 
}


if (!isset($_SESSION['job_seeker_id'])) {
    displayLoginMessage(); 
}

if (!isset($_GET['assessment_id']) || trim($_GET['assessment_id']) === '') {
    displayErrorMessage();
}

$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'assessment_history.php') === false) {
    displayErrorMessage();
}


session_write_close();


$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$assessment_id = $_GET['assessment_id'];


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

$programming_sql = "SELECT DISTINCT q.assessment_id, q.programming_language 
FROM Question q
JOIN Answer a ON a.question_id = q.question_id
WHERE q.assessment_id IN ('AS77', 'AS78', 'AS79', 'AS80')
AND a.job_seeker_id = ? 
AND job_seeker_id IN (
    SELECT job_seeker_id 
    FROM Assessment_Job_Seeker 
    WHERE result_id = ?
)";

$stmt = $conn->prepare($programming_sql);
$stmt->bind_param("ss", $_SESSION['job_seeker_id'], $assessment_id);
$stmt->execute();
$prog_result = $stmt->get_result();
$row = $prog_result->fetch_assoc();

$programming_section = null;
$programming_language = null;
if ($row) {
    $programming_section = $row['assessment_id'];
    $programming_language = $row['programming_language'];
}


$sections = [
    'AS75' => 'General Questions',
    'AS76' => 'Scenario-Based Questions'
];


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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Answers - TechFit</title>
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
            background-color: var(--background-color-medium);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            margin-bottom: 15px;
            width: 100%;
        }

        .summary_header h2 {
            margin: 0;
            color: var(--text-color);
            font-size: 1.5em;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--background-color-light);
        }

        .summary_header p {
            margin: 5px 0;
            color: #666;
            font-size: 1em;
            line-height: 1.4;
        }

        .summary_details {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
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

        .your-answer, .correct-answer {
            background-color: var(--background-color-light);
            color: var(--text-color);
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

        .page-wrapper {
            width: 75%;
            margin: 0 auto;
            min-width: 900px;
        }

        .assessment-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 20px;
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
            background-color: var(--background-color);
            border-color: var(--background-color-light);
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid var(--background-color-light);
            border-radius: 4px;
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

        .detail_item {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background-color: var(--background-color);
            border-radius: 6px;
            flex: 1;
            min-width: 200px;
        }

        .detail_label {
            color: var(--text-color-dark);
            font-weight: 500;
            font-size: 0.75em;
        }

        .detail_value {
            color: var(--text-color);
            font-weight: 600;
            font-size: 0.75em;
        }

        .detail_value.score {
            color: var(--success-color);
            font-size: 0.85em;
        }

        @media screen and (max-width: 1024px) {
            .page-wrapper {
                width: 90%;
                min-width: auto;
            }

            .assessment-container {
                flex-direction: column;
            }
            
            .questions-section {
                width: 100%;
            }
        }

        @media screen and (max-width: 768px) {
            .summary_header {
                padding: 20px;
            }

            .detail_item {
                flex-direction: column;
                align-items: flex-start;
                gap: 5px;
            }

            .detail_label {
                min-width: auto;
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
                <h2>View Answers</h2>
                <?php if (!empty($assessment_details)): ?>
                    <div class="summary_details">
                        <div class="detail_item">
                            <span class="detail_label">Assessment ID:</span>
                            <span class="detail_value"><?= $assessment_id; ?></span>
                        </div>
                        <div class="detail_item">
                            <span class="detail_label">Assessment Date:</span>
                            <span class="detail_value"><?= date('d/m/Y', strtotime($assessment_details[0]['assessment_date'])); ?></span>
                        </div>
                        <div class="detail_item">
                            <span class="detail_label">Score:</span>
                            <span class="detail_value score"><?= $assessment_details[0]['score']; ?>%</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <div class="section-navigator">
                <ul class="section-nav-list">
                    <?php foreach ($sections as $id => $name): ?>
                        <li class="section-nav-item" onclick="scrollToSection('<?php echo $id; ?>')"><?php echo $name; ?></li>
                    <?php endforeach; ?>
                </ul>
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
                        // Only show questions for the correct programming language section
                        if ($detail['assessment_id'] === $programming_section && 
                            $detail['programming_language'] !== $programming_language) {
                            continue;
                        }
                    
                        if ($detail['assessment_id'] !== $current_section) {
                            if ($current_section !== '') {
                                echo "</div>";
                            }
                            $current_section = $detail['assessment_id'];
                            $section_name = $sections[$current_section];
                            
                            // Add programming language to section name if applicable
                            if (in_array($current_section, ['AS77', 'AS78', 'AS79', 'AS80'])) {
                                $section_name = str_replace(
                                    ['Python', 'Java', 'JavaScript', 'C++'],
                                    ucfirst($programming_language),
                                    $section_name
                                );
                            }
                            
                            echo "<h2 class='section-header'>{$section_name}</h2>";
                            echo "<div id='{$current_section}' class='section-questions'>";
                        }
                    ?>
                        <div class="question-item">
                            <p class="question-text"><?= htmlspecialchars($detail['question_text']); ?></p>
                            <div class="answer-pair">
                                <?php if ($detail['answer_type'] === 'code'): ?>
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

        function scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({ behavior: 'smooth', block: 'start' });
                
                
                document.querySelectorAll('.section-nav-item').forEach(item => {
                    item.classList.remove('active');
                });
                const activeItem = document.querySelector(`[onclick="scrollToSection('${sectionId}')"]`);
                if (activeItem) {
                    activeItem.classList.add('active');
                }
            }
        }

        
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