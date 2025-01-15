<?php
session_start(); // Start the session to access session variables

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Connection to techfit database failed: ' . $conn->connect_error);
}

function fetchQuestionsAndChoices($conn) {
    $assessment_id = 'AS83';
    $questions = [];

    // Fetch questions based on the assessment_id
    $query = "SELECT * FROM question WHERE assessment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($question = $result->fetch_assoc()) {
        $question_id = $question['question_id'];
        $question_text = $question['question_text'];
        $question_type = $question['question_type'];

        // Fetch choices based on the question_id
        $query2 = "SELECT * FROM choices WHERE question_id = ?";
        $stmt2 = $conn->prepare($query2);
        $stmt2->bind_param("i", $question_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        $choices = [];
        while ($choice = $result2->fetch_assoc()) {
            $choices[] = $choice['choice_text'];
        }
        $stmt2->close();

        $questions[] = [
            'question_id' => $question_id,
            'question_text' => $question_text,
            'question_type' => $question_type,
            'choices' => $choices
        ];
    }
    $stmt->close();

    return $questions;
}

$questions = fetchQuestionsAndChoices($conn);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Questions - TechFit</title>
    <link rel="stylesheet" href="styles.css">

    <script>
        let questions = <?php echo json_encode($questions); ?>;
        let currentQuestionIndex = 0;
        let countdownTime = 300; // 5 minutes for each question

        function displayQuestion() {
            if (currentQuestionIndex < questions.length) {
                let question = questions[currentQuestionIndex];
                let questionContainer = document.querySelector('.question');
                questionContainer.innerHTML = question.question_text + " (" + question.question_type + ")";

                let choicesContainer = document.createElement('div');
                choicesContainer.className = 'options';

                if (question.question_type === 'multiple choice') {
                    question.choices.forEach((choice, index) => {
                        let choiceElement = document.createElement('div');
                        choiceElement.className = 'option';
                        choiceElement.innerHTML = `<input type="radio" name="choice" value="${choice}"> ${choice}`;
                        choicesContainer.appendChild(choiceElement);
                    });
                } else if (question.question_type === 'true/false') {
                    let trueOption = document.createElement('div');
                    trueOption.className = 'option';
                    trueOption.innerHTML = `<input type="radio" name="choice" value="true"> True`;
                    choicesContainer.appendChild(trueOption);

                    let falseOption = document.createElement('div');
                    falseOption.className = 'option';
                    falseOption.innerHTML = `<input type="radio" name="choice" value="false"> False`;
                    choicesContainer.appendChild(falseOption);
                } else if (question.question_type === 'fill in the blank') {
                    let inputElement = document.createElement('input');
                    inputElement.type = 'text';
                    inputElement.name = 'choice';
                    inputElement.className = 'option';
                    choicesContainer.appendChild(inputElement);
                } else if (question.question_type === 'essay') {
                    let textareaElement = document.createElement('textarea');
                    textareaElement.name = 'choice';
                    textareaElement.className = 'option';
                    choicesContainer.appendChild(textareaElement);
                }

                questionContainer.appendChild(choicesContainer);
                startTimer(countdownTime, document.getElementById('timer'), document.querySelector('.submit_but button'));
            } else {
                document.querySelector('.question').innerHTML = '<p>All questions answered. Thank you!</p>';
                document.querySelector('.submit_but button').disabled = true;
            }
        }

        function submitAnswer() {
            // Handle answer submission logic here (e.g., save the answer to the database)
            currentQuestionIndex++;
            displayQuestion();
        }

        function startTimer(duration, display, button) {
            let timer = duration, minutes, seconds;
            let interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = "Time Remaining: " + minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    button.disabled = true;
                    display.textContent = "Time's up!";
                }
            }, 1000);
        }

        window.onload = function() {
            displayQuestion();
        }
    </script>
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="about.php">About</a></li>
                <li>
                    <a href="#" id="profile-link">
                        <div class="profile-info">
                            <span class="username" id="username">Profile</span>
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
        </nav>
    </header>

    <!-- Logout Popup -->
    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <button class="close-button" onclick="logoutUser()">Yes</button>
        <button class="cancel-button" onclick="closePopup('logout-popup')">No</button>
    </div>

    <!-- Questions Container -->
    <div class="questions-container">
        <div id="question-container">
            <h3 id="question-text"></h3>
            <div id="choices-container" class="options"></div>
            <button onclick="submitAnswer()">Next</button>
        </div>
    </div>

    <div class="que_container">
        <div class="left-section">
            <div class="question">
                <!-- The question text and answer container will be dynamically inserted here -->
            </div>
            <div class="answer_area"></div>
        </div>
        <div class="right-section">
            <div id="timer" class="timer">Time Remaining: 0:00</div>
            <div class="question-list">
                <div>
                    <label>Question 1</label>
                    <input type="checkbox">
                </div>
                <div>
                    <label>Question 2</label>
                    <input type="checkbox">
                </div>
                <div>
                    <label>Question 3</label>
                    <input type="checkbox">
                </div>
            </div>
            <div class="submit_but">
                <button onclick="submitAnswer()">Submit</button>
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
    <script src="scripts.js"></script>
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

        const answers = document.querySelectorAll('input[name="answer"]');
        const feedback = document.getElementById('feedback');

        // Enable submit button when an answer is selected
        answers.forEach(answer => {
            answer.addEventListener('change', () => {
                document.querySelector('.submit_but button').disabled = false;
            });
        });

        // Timer logic
        startTimer(countdownTime, document.getElementById('timer'), document.querySelector('.submit_but button'));
    </script>
</body>
</html>