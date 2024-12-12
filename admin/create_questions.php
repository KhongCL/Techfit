<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Questions - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        let questionCount = 0;

        function addQuestion() {
            questionCount++;
            const questionDiv = document.createElement('div');
            questionDiv.id = `question-${questionCount}`;
            questionDiv.innerHTML = `
                <label for="question_text_${questionCount}">Question Text:</label>
                <textarea id="question_text_${questionCount}" name="question_text[]" required></textarea><br>

                <label for="answer_type_${questionCount}">Answer Type:</label>
                <select id="answer_type_${questionCount}" name="answer_type[]" onchange="showAnswerOptions(${questionCount})" required>
                    <option value="multiple choice">Multiple Choice</option>
                    <option value="true/false">True/False</option>
                    <option value="fill in the blank">Fill in the Blank</option>
                    <option value="essay">Essay</option>
                    <option value="code">Code</option>
                </select><br>

                <div id="answer_options_${questionCount}"></div>
                <button type="button" onclick="removeQuestion(${questionCount})">Remove Question</button>
                <hr>
            `;
            document.getElementById('questions').appendChild(questionDiv);
        }

        function removeQuestion(id) {
            const questionDiv = document.getElementById(`question-${id}`);
            questionDiv.remove();
        }

        function showAnswerOptions(id) {
            const answerType = document.getElementById(`answer_type_${id}`).value;
            const answerOptionsDiv = document.getElementById(`answer_options_${id}`);
            answerOptionsDiv.innerHTML = '';

            if (answerType === 'multiple choice') {
                answerOptionsDiv.innerHTML = `
                    <label for="choices_${id}">Choices:</label>
                    <div id="choices_${id}">
                        <input type="text" name="choices_${id}[]" required>
                        <button type="button" onclick="addChoice(${id})">Add Choice</button>
                    </div>
                    <label for="correct_choice_${id}">Correct Choice:</label>
                    <select id="correct_choice_${id}" name="correct_choice_${id}" required></select>
                `;
            } else if (answerType === 'true/false') {
                answerOptionsDiv.innerHTML = `
                    <label for="true_false_${id}">Answer:</label>
                    <select id="true_false_${id}" name="true_false_${id}" required>
                        <option value="true">True</option>
                        <option value="false">False</option>
                    </select>
                `;
            } else if (answerType === 'fill in the blank') {
                answerOptionsDiv.innerHTML = `
                    <label for="blank_${id}">Blank:</label>
                    <input type="text" id="blank_${id}" name="blank_${id}" required>
                `;
            } else if (answerType === 'essay') {
                answerOptionsDiv.innerHTML = `
                    <label for="essay_${id}">Correct Answer:</label>
                    <textarea id="essay_${id}" name="essay_${id}" required></textarea>
                `;
            } else if (answerType === 'code') {
                answerOptionsDiv.innerHTML = `
                    <label for="code_${id}">Correct Answer:</label>
                    <textarea id="code_${id}" name="code_${id}" required></textarea>
                `;
            }
        }

        function addChoice(id) {
            const choicesDiv = document.getElementById(`choices_${id}`);
            const input = document.createElement('input');
            input.type = 'text';
            input.name = `choices_${id}[]`;
            input.required = true;
            choicesDiv.insertBefore(input, choicesDiv.lastElementChild);

            // Update the correct choice dropdown
            updateCorrectChoiceDropdown(id);
        }

        function updateCorrectChoiceDropdown(id) {
            const choices = document.getElementsByName(`choices_${id}[]`);
            const correctChoiceDropdown = document.getElementById(`correct_choice_${id}`);
            correctChoiceDropdown.innerHTML = '';

            choices.forEach((choice, index) => {
                const option = document.createElement('option');
                option.value = choice.value;
                option.text = choice.value;
                correctChoiceDropdown.appendChild(option);
            });
        }

        function saveAssessment() {
            document.getElementById('questions-form').submit();
        }
    </script>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
        </div>
        <nav>
            <div class="nav-container">
                <ul class="nav-list">
                    <li><a href="#">Assessments</a>
                        <ul class="dropdown">
                            <li><a href="#">Manage Assessments</a>
                                <ul class="dropdown">
                                    <li><a href="manage_assessments.php">Manage Assessments</a></li>
                                    <li><a href="create_assessment.html">Create New Assessment</a></li>
                                    <li><a href="edit_assessment.html">Edit Existing Assessments</a></li>
                                    <li><a href="delete_assessment.html">Delete Assessments</a></li>
                                    <li><a href="view_assessment_results.html">View Assessment Results</a></li>
                                </ul>
                            </li>
                            <li><a href="#">Manage Questions</a>
                                <ul class="dropdown">
                                    <li><a href="manage_questions.html">Manage Questions</a></li>
                                    <li><a href="add_question.html">Add New Question</a></li>
                                    <li><a href="edit_question.html">Edit Existing Questions</a></li>
                                    <li><a href="delete_question.html">Delete Questions</a></li>
                                </ul>
                            </li>
                        </ul>
                    </li>
                    <li><a href="#">Users</a>
                        <ul class="dropdown">
                            <li><a href="#">Manage Users</a>
                                <ul class="dropdown">
                                    <li><a href="manage_users.html">Manage Users</a></li>
                                    <li><a href="view_all_users.html">View All Users</a></li>
                                    <li><a href="edit_user_profile.html">Edit User Profiles</a></li>
                                    <li><a href="delete_user_account.html">Delete User Accounts</a></li>
                                </ul>
                            </li>
                            <li><a href="#">User Feedback</a>
                                <ul class="dropdown">
                                    <li><a href="user_feedback.html">User Feedback</a></li>
                                    <li><a href="manage_feedback.html">Manage Feedback</a></li>
                                </ul>
                            </li>
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
                            <li><a href="add_resource.html">Add New Resource</a></li>
                            <li><a href="edit_resource.html">Edit Existing Resources</a></li>
                        </ul>
                    </li>
                    <li><a href="about.html">About</a></li>
                    <li><a href="profile.html" id="profile-link">Profile</a>
                        <ul class="dropdown" id="profile-dropdown">
                            <li><a href="settings.html">Settings</a></li>
                            <li><a href="logout.html">Logout</a></li>
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
    <main>
        <h1>Create Questions for Assessment</h1>
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
                        <li><a href="manage_assessments.php">Manage Assessments</a></li>
                        <li><a href="create_assessment.html">Create New Assessment</a></li>
                        <li><a href="edit_assessment.html">Edit Existing Assessments</a></li>
                        <li><a href="delete_assessment.html">Delete Assessments</a></li>
                        <li><a href="view_assessment_results.html">View Assessment Results</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Users</h3>
                    <ul>
                        <li><a href="manage_users.html">Manage Users</a></li>
                        <li><a href="view_all_users.html">View All Users</a></li>
                        <li><a href="edit_user_profile.html">Edit User Profiles</a></li>
                        <li><a href="delete_user_account.html">Delete User Accounts</a></li>
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
                    <h3>About</h3>
                    <ul>
                        <li><a href="about.html">About</a></li>
                        <li><a href="contact.html">Contact Us</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TechPathway: TechFit. All rights reserved.</p>
        </div>
    </footer>