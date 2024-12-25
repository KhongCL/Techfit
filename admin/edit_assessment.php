<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Questions for Assessment - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        let questionCount = 0;
        let isFormDirty = false;

        window.addEventListener('beforeunload', function (e) {
            if (isFormDirty) {
                const confirmationMessage = 'You have unsaved changes. Are you sure you want to leave this page?';
                e.returnValue = confirmationMessage; // Gecko, Trident, Chrome 34+
                return confirmationMessage; // Gecko, WebKit, Chrome <34
            }
        });

        function addQuestion() {
            isFormDirty = true;
            questionCount++;
            const questionDiv = document.createElement('div');
            questionDiv.id = `question-${questionCount}`;
            questionDiv.innerHTML = `
                <input type="hidden" id="question_id_${questionCount}" name="question_id[]" value="">
                <label for="question_text_${questionCount}">Question Text:</label>
                <textarea id="question_text_${questionCount}" name="question_text[]" required></textarea><br>

                <label for="question_type_${questionCount}">Question Type:</label>
                <select id="question_type_${questionCount}" name="question_type[]" required>
                    <option value="preliminary">Preliminary</option>
                    <option value="experience">Experience</option>
                    <option value="employer_score">Employer Score</option>
                    <option value="detailed">Detailed</option>
                    <option value="technical">Technical</option>
                </select><br>

                <label for="answer_type_${questionCount}">Answer Type:</label>
                <select id="answer_type_${questionCount}" name="answer_type[]" onchange="showAnswerOptions(${questionCount})" required>
                    <option value="multiple choice">Multiple Choice</option>
                    <option value="true/false">True/False</option>
                    <option value="fill in the blank">Fill in the Blank</option>
                    <option value="essay">Essay</option>
                    <option value="code">Code</option>
                </select><br>

                <div id="answer_options_${questionCount}">
                    ${getMultipleChoiceOptions(questionCount)}
                </div>
                <button type="button" onclick="removeQuestion(${questionCount})">Remove Question</button>
                <hr>
            `;
            document.getElementById('questions').appendChild(questionDiv);
        }

        function removeQuestion(id) {
            if (confirm('Are you sure you want to remove this question?')) {
                const questionDiv = document.getElementById(`question-${id}`);
                questionDiv.style.display = 'none';
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'removed_questions[]';
                input.value = id;
                questionDiv.appendChild(input);
                isFormDirty = true;

                // Send AJAX request to update is_active to false
                const questionId = document.getElementById(`question_id_${id}`).value;
                fetch('update_question_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ question_id: questionId, is_active: false })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Question status updated successfully.');
                    } else {
                        console.error('Failed to update question status.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        }

        function saveAssessment() {
            isFormDirty = false;
            const form = document.getElementById('questions-form');
            const formData = new FormData(form);

            fetch('update_questions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Assessment updated successfully.');
                    window.location.reload();
                } else {
                    alert('Failed to update assessment.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        // Fetch existing questions for the assessment
        document.addEventListener('DOMContentLoaded', function() {
            const assessmentId = "<?php echo htmlspecialchars($_GET['assessment_id']); ?>";
            fetch(`get_questions.php?assessment_id=${assessmentId}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(question => {
                        addQuestion();
                        document.getElementById(`question_id_${questionCount}`).value = question.question_id;
                        document.getElementById(`question_text_${questionCount}`).value = question.question_text;
                        document.getElementById(`question_type_${questionCount}`).value = question.question_type;
                        document.getElementById(`answer_type_${questionCount}`).value = question.answer_type;
                        showAnswerOptions(questionCount);
                        if (question.answer_type === 'multiple choice') {
                            // Populate choices for multiple choice questions
                            const choicesDiv = document.getElementById(`choices_${questionCount}`);
                            question.choices.forEach(choice => {
                                addChoice(questionCount);
                                const choiceInputs = document.getElementsByName(`choices_${questionCount}[]`);
                                choiceInputs[choiceInputs.length - 2].value = choice.choice_text; // Set the value of the last added choice input
                            });
                            document.getElementById(`correct_choice_${questionCount}`).value = question.correct_answer;
                        } else if (question.answer_type === 'code') {
                            // Populate code question options
                            document.getElementById(`code_${questionCount}`).value = question.correct_answer;
                            // Fetch and populate test cases for code questions
                            fetch(`get_test_cases.php?question_id=${question.question_id}`)
                                .then(response => response.json())
                                .then(testCases => {
                                    testCases.forEach(testCase => {
                                        addTestCase(questionCount);
                                        const testCaseInputs = document.getElementsByName(`test_cases_${questionCount}[]`);
                                        const expectedOutputInputs = document.getElementsByName(`expected_output_${questionCount}[]`);
                                        testCaseInputs[testCaseInputs.length - 2].value = testCase.input;
                                        expectedOutputInputs[expectedOutputInputs.length - 2].value = testCase.expected_output;
                                    });
                                });
                        } else {
                            document.getElementById(`correct_choice_${questionCount}`).value = question.correct_answer;
                        }
                    });
                });
        });
</script>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
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
                            <li><a href="create_assessment.html">Create New Assessment</a></li>
                            <li><a href="manage_assessments.php">Manage Assessments</a></li>
                            <li><a href="view_assessment_results.html">View Assessment Results</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Users</a>
                        <ul class="dropdown">
                            <li><a href="manage_users.html">Manage Users</a></li>
                            <li><a href="user_feedback.html">User Feedback</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Reports</a>
                        <ul class="dropdown">
                            <li><a href="assessment_performance.html">Assessment Performance</a></li>
                            <li><a href="user_engagement.html">User Engagement Statistics</a></li>
                            <li><a href="feedback_analysis.html">Feedback Analysis</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="useful_links.html">Manage Useful Links</a></li>
                            <li><a href="faq.html">Manage FAQs</a></li>
                            <li><a href="sitemap.html">Manage Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.html">About</a></li>
                    <li>
                        <a href="#" id="profile-link">
                            <div class="profile-info">
                                <span class="username" id="username">Admin</span>
                                <img src="images/usericon.png" alt="Profile" class="profile-image" id="profile-image">
                            </div>
                        </a>
                        <ul class="dropdown" id="profile-dropdown">
                            <li><a href="settings.html">Settings</a>
                                <ul class="dropdown">
                                    <li><a href="manage_profile.html">Manage Profile</a></li>
                                    <li><a href="system_configuration.html">System Configuration Settings</a></li>
                                </ul>
                            </li>
                            <li><a href="logout.html">Logout</a></li>
                        </ul>
                    </li>                    
                </ul>
            </div>
        </nav>
    </header>    
    <main>
        <h1>Edit Questions for Assessment</h1>
        <p>Assessment ID: <strong><?php echo htmlspecialchars($_GET['assessment_id']); ?></strong></p>
        <?php
        if (isset($_SESSION['success_message'])) {
            echo '<p class="success-message">' . $_SESSION['success_message'] . '</p>';
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo '<p class="error-message">' . $_SESSION['error_message'] . '</p>';
            unset($_SESSION['error_message']);
        }
        ?>
        <form id="questions-form" action="save_questions.php" method="post">
            <input type="hidden" name="assessment_id" value="<?php echo htmlspecialchars($_GET['assessment_id']); ?>">
            <div id="questions"></div>
            <button type="button" onclick="addQuestion()">Add Question</button>
            <button type="button" onclick="saveAssessment()">Save Assessment</button>
        </form>
    </main>
    
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
                    <h3>Assessments</h3>
                    <ul>
                        <li><a href="create_assessment.html">Create New Assessment</a></li>
                        <li><a href="manage_assessments.php">Manage Assessments</a></li>
                        <li><a href="view_assessment_results.html">View Assessment Results</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Users</h3>
                    <ul>
                        <li><a href="manage_users.html">Manage Users</a></li>
                        <li><a href="user_feedback.html">User Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Reports</h3>
                    <ul>
                        <li><a href="assessment_performance.html">Assessment Performance</a></li>
                        <li><a href="user_engagement.html">User Engagement Statistics</a></li>
                        <li><a href="feedback_analysis.html">Feedback Analysis</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="useful_links.html">Manage Useful Links</a></li>
                        <li><a href="faq.html">Manage FAQs</a></li>
                        <li><a href="sitemap.html">Manage Sitemap</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>About</h3>
                    <ul>
                        <li><a href="about.html">About</a></li>
                        <li><a href="contact.html">Contact Us</a></li>
                        <li><a href="terms.html">Terms & Condition</a></li>
                        <li><a href="privacy.html">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TechPathway: TechFit. All rights reserved.</p>
        </div>
    </footer>
    <script src="scripts.js"></script>
</body>
</html>