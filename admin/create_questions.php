<?php
session_start(); 


function displayLoginMessage() {
    echo '<script>
        alert("You need to log in to access this page.");
    </script>';
    exit();
}

function displayErrorMessage() {
    echo '<script>
        if (confirm("You need to access this page from create assessment. Go to Create Assessment? Click cancel to go to home page.")) {
            window.location.href = "./create_assessment.php";
        } else {
            window.location.href = "./index.php";
        }
    </script>';
    exit();
}

if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); 
}


if ($_SESSION['role'] !== 'Admin') {
    displayLoginMessage(); 
}

if (!isset($_GET['assessment_id']) || trim($_GET['assessment_id']) === '') {
    displayErrorMessage();
}

$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'create_assessment.php') === false) {
    displayErrorMessage();
}

session_write_close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Questions for Assessment - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        let questionCount = 0;
        let isFormDirty = false;
        const ANSWER_DELIMITER = '<<ANSWER_BREAK>>';

        window.addEventListener('beforeunload', function (e) {
            if (isFormDirty) {
                const confirmationMessage = 'You have unsaved changes. Are you sure you want to leave this page?';
                e.returnValue = confirmationMessage; 
                return confirmationMessage; 
            }
        });

        function addQuestion() {
            isFormDirty = true;
            questionCount++;
            const questionDiv = document.createElement('div');
            questionDiv.id = `question-${questionCount}`;
            questionDiv.innerHTML = `
                <p>Question ${questionCount}:</p>
                <label for="question_text_${questionCount}">Question Text:</label>
                <textarea id="question_text_${questionCount}" style="border: 1px solid white; background-color: var(--background-color)" name="question_text[]" required></textarea><br>

                <div class="dropdown-container">
                    <div class="dropdown-item">
                        <label for="question_type_${questionCount}">Question Type:</label>
                        <select id="question_type_${questionCount}" style="border: 1px solid white; background-color: var(--background-color)" name="question_type[]" required>
                            <option value="preliminary">Preliminary</option>
                            <option value="experience">Experience</option>
                            <option value="employer_score">Employer Score</option>
                            <option value="detailed">Detailed</option>
                            <option value="technical">Technical</option>
                        </select>
                    </div>
                    <div class="dropdown-item">
                        <label for="answer_type_${questionCount}">Answer Type:</label>
                        <select id="answer_type_${questionCount}" style="border: 1px solid white; background-color: var(--background-color); width: 94%;" name="answer_type[]" onchange="showAnswerOptions(${questionCount})" required>
                            <option value="multiple choice">Multiple Choice</option>
                            <option value="true/false">True/False</option>
                            <option value="fill in the blank">Fill in the Blank</option>
                            <option value="essay">Essay</option>
                            <option value="code">Code</option>
                        </select>
                    </div>
                </div>

                <div id="answer_options_${questionCount}">
                    ${getMultipleChoiceOptions(questionCount, true)}
                </div>
                <button type="button" class="danger" onclick="removeQuestion(${questionCount})">Remove Question</button>
                <hr>
            `;
            document.getElementById('questions').appendChild(questionDiv);
            updateCorrectChoiceDropdown(questionCount); 
        }

        function removeQuestion(id) {
            if (confirm('Are you sure you want to remove this question?')) {
                const questionDiv = document.getElementById(`question-${id}`);
                questionDiv.remove();
                isFormDirty = true;
                updateQuestionNumbers();
            }
        }

        function updateQuestionNumbers() {
            const questionDivs = document.querySelectorAll('div[id^="question-"]');
            questionDivs.forEach((div, index) => {
                const questionNumber = index + 1;
                div.querySelector('p').textContent = `Question ${questionNumber}:`;
            });
        }

        function showAnswerOptions(id, includeEmptyChoice = true) {
            const answerType = document.getElementById(`answer_type_${id}`).value;
            const answerOptionsDiv = document.getElementById(`answer_options_${id}`);
            answerOptionsDiv.innerHTML = '';

            if (answerType === 'multiple choice') {
                answerOptionsDiv.innerHTML = getMultipleChoiceOptions(id, includeEmptyChoice);
                updateCorrectChoiceDropdown(id); 
            } else if (answerType === 'true/false') {
                answerOptionsDiv.innerHTML = `
                    <label for="true_false_${id}">Answer:</label>
                    <select id="true_false_${id}" style="border: 1px solid white; background-color: var(--background-color); name="correct_choice[]" required>
                        <option value="true">True</option>
                        <option value="false">False</option>
                    </select>
                `;
            } else if (answerType === 'fill in the blank') {
                answerOptionsDiv.innerHTML = `
                    <label for="blank_${id}">Blank:</label>
                    <input type="text" id="blank_${id}" style="border: 1px solid white; background-color: var(--background-color); name="correct_choice[]" required>
                `;
            } else if (answerType === 'essay') {
                answerOptionsDiv.innerHTML = `
                    <label for="essay_${id}">Correct Answer:</label>
                    <textarea id="essay_${id}" style="border: 1px solid white; background-color: var(--background-color); name="correct_choice[]" required></textarea>
                `;
            } else if (answerType === 'code') {
                answerOptionsDiv.innerHTML = getCodeQuestionOptions(id, includeEmptyChoice);
            }
        }

        function getMultipleChoiceOptions(id, includeEmptyChoice = true) {
            let choicesHtml = `
                <label for="choices_${id}">Choices:</label>
                <div id="choices_${id}">
            `;
            if (includeEmptyChoice) {
                choicesHtml += `
                    <div class="choice-container">
                        <input type="text" name="choices_${id}[]" required oninput="updateCorrectChoiceDropdown(${id})">
                        <button type="button" class="remove-icon" title="Remove Choice" onclick="removeChoice(this, ${id})">&#x2715;</button>
                    </div>
                `;
            }
            choicesHtml += `
                    <button type="button" onclick="addChoice(${id})">Add Choice</button>
                </div>
                <label for="correct_choice_${id}">Correct Choice:</label>
                <select id="correct_choice_${id}" style="border: 1px solid white; background-color: var(--background-color); width: 97%;" name="correct_choice[]" required></select>
            `;
            return choicesHtml;
        }

        function getCodeQuestionOptions(id) {
            return `
                <label for="code_language_${id}">Select Language:</label>
                <select id="code_language_${id}" style="border: 1px solid white; background-color: var(--background-color)" name="code_language[]" required>
                    <option value="python">Python</option>
                    <option value="javascript">JavaScript</option>
                    <option value="java">Java</option>
                    <option value="cpp">C++</option>
                </select><br>

                <label for="code_${id}">Code Template:</label>
                <textarea id="code_${id}" style="border: 1px solid white; background-color: var(--background-color)" name="code_template[]" required 
                    placeholder="Enter code with __BLANK__ placeholders"></textarea><br>

                <label for="correct_code_${id}">Correct Answers:</label>
                <textarea id="correct_code_${id}" style="border: 1px solid white; background-color: var(--background-color)" name="correct_choice[]" required 
                    placeholder="Enter correct answers separated by <<ANSWER_BREAK>>"
                    title="Enter the answers that should go in each __BLANK__ placeholder, separated by <<ANSWER_BREAK>>"></textarea>
            `;
        }

        function addChoice(id) {
            const choicesDiv = document.getElementById(`choices_${id}`);
            const choiceContainer = document.createElement('div');
            choiceContainer.className = 'choice-container';
            const input = document.createElement('input');
            input.type = 'text';
            input.name = `choices_${id}[]`;
            input.required = true;
            input.oninput = function() {
                updateCorrectChoiceDropdown(id);
            };

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'remove-icon';
            removeButton.innerHTML = '&#x2715;'; 
            removeButton.title = 'Remove Choice'; 
            removeButton.onclick = function() {
                choiceContainer.remove();
                updateCorrectChoiceDropdown(id);
                isFormDirty = true;
            };

            choiceContainer.appendChild(input);
            choiceContainer.appendChild(removeButton);
            choicesDiv.insertBefore(choiceContainer, choicesDiv.lastElementChild);

            
            updateCorrectChoiceDropdown(id);
            isFormDirty = true;
        }

        function removeChoice(button, id) {
            const choiceContainer = button.parentElement;
            choiceContainer.remove();
            updateCorrectChoiceDropdown(id);
            isFormDirty = true;
        }

        function updateCorrectChoiceDropdown(id) {
            const choices = document.getElementsByName(`choices_${id}[]`);
            const correctChoiceDropdown = document.getElementById(`correct_choice_${id}`);
            correctChoiceDropdown.innerHTML = '';

            choices.forEach((choice, index) => {
                if (choice.value.trim() !== '') { 
                    const option = document.createElement('option');
                    option.value = choice.value;
                    option.text = choice.value;
                    correctChoiceDropdown.appendChild(option);
                }
            });
        }

        function saveAssessment() {
            if (confirm('Are you sure you want to save the changes?')) {
                const form = document.getElementById('questions-form');
                
                
                const questionDivs = form.querySelectorAll('div[id^="question-"]');
                const removedQuestions = form.querySelectorAll('input[name="removed_questions[]"]');
                const removedQuestionIds = Array.from(removedQuestions).map(input => input.value);

                for (let i = 0; i < questionDivs.length; i++) {
                    const questionDiv = questionDivs[i];
                    const questionId = questionDiv.id.split('-')[1];

                    if (removedQuestionIds.includes(questionId)) {
                        continue;
                    }

                    const questionText = questionDiv.querySelector('textarea[name="question_text[]"]');
                    const questionType = questionDiv.querySelector('select[name="question_type[]"]');
                    const answerType = questionDiv.querySelector('select[name="answer_type[]"]');
                    const correctChoice = questionDiv.querySelector('textarea[name="correct_choice[]"], input[name="correct_choice[]"], select[name="correct_choice[]"]');

                    if (questionText.value.trim() === '' || questionType.value.trim() === '' || 
                        answerType.value.trim() === '' || correctChoice.value.trim() === '') {
                        alert('All fields are required.');
                        return;
                    }

                    
                    if (answerType.value === 'multiple choice') {
                        const choices = questionDiv.querySelectorAll(`input[name="choices_${i + 1}[]"]`);
                        for (let choice of choices) {
                            if (choice.value.trim() === '') {
                                alert('All choice fields are required.');
                                return;
                            }
                        }
                    }

                    
                    if (answerType.value === 'code') {
                        const codeTemplate = questionDiv.querySelector(`textarea[name="code_template[]"]`);
                        const correctAnswers = questionDiv.querySelector(`textarea[name="correct_choice[]"]`);
                        const language = questionDiv.querySelector(`select[name="code_language[]"]`);

                        
                        if (!codeTemplate.value.trim() || !correctAnswers.value.trim() || !language.value) {
                            alert('Code template, answers and programming language are required for code questions.');
                            return;
                        }

                        
                        if (!codeTemplate.value.includes('__BLANK__')) {
                            alert('Code template must include at least one __BLANK__ placeholder.');
                            return;
                        }

                        const answers = correctAnswers.value.split(ANSWER_DELIMITER);
                        if (answers.length < 2) {
                            alert('Please provide at least two answers separated by <<ANSWER_BREAK>>');
                            return;
                        }

                        if (answers.some(a => a.trim() === '')) {
                            alert('Empty or blank answers are not allowed. Please provide valid answers separated by <<ANSWER_BREAK>>');
                            return;
                        }

                        if (codeTemplate.value.includes(ANSWER_DELIMITER)) {
                            alert('Code template cannot contain the sequence <<ANSWER_BREAK>>');
                            return;
                        }
                        
                        
                        if (correctAnswers.value.includes(ANSWER_DELIMITER + ANSWER_DELIMITER)) {
                            alert('Answers cannot contain consecutive delimiters');
                            return;
                        }

                        
                        const blankCount = (codeTemplate.value.match(/__BLANK__/g) || []).length;
                        const answerCount = answers.length;
                        if (blankCount !== answerCount) {
                            alert(`Number of blanks (${blankCount}) must match number of answers (${answerCount}).`);
                            return;
                        }
                    }
                }

                isFormDirty = false;
                form.submit();
            }
        }
    </script>

        <style>
            body {
                font-family: Arial, sans-serif;
                color: var(--text-color);
                background-color: var(--background-color);
            }

            main {
                padding: 20px;
            }

           
            button {
                background-color: var(--primary-color);
                color: var(--text-color);
                border: none;
                padding: 10px 20px;
                cursor: pointer;
                transition: background-color 0.3s ease, color 0.3s ease;
                border-radius: 5px;
                font-weight: bold;
                box-sizing: border-box;
            }

            button:hover {
                background-color: var(--button-color-hover);
                color: var(--hover-text-color);
            }

            button.danger {
                background-color: var(--danger-color);
            }

            button.danger:hover {
                background-color: var(--danger-color-hover);
            }

            button.success {
                background-color: var(--success-color);
            }

            button.success:hover {
                background-color: var(--success-color-hover);
            }

            button.remove-icon {
                background: none;
                border: none;
                color: var(--danger-color);
                font-size: 16px;
                cursor: pointer;
                margin-left: 10px;
                vertical-align: middle;
                box-sizing: border-box;
            }

            button.remove-icon:hover {
                color: var(--danger-hover-color);
            }

            button[type="button"] {
                margin-right: 10px;
            }

           
            input[type="text"], textarea, select {
                width: 100%;
                padding: 10px;
                margin-bottom: 10px;
                border: 1px solid var(--border-color);
                border-radius: 5px;
                background-color: var(--secondary-color);
                color: var(--text-color);
                transition: border-color 0.3s ease, background-color 0.3s ease, color 0.3s ease;
                box-sizing: border-box;
            }

            input[type="text"]:hover, textarea:hover, select:hover {
                border-color: var(--primary-color);
            }

            textarea {
                resize: vertical;
            }

            label, textarea, select, input[type="text"], button {
                margin-bottom: 15px;
            }

           
            .dropdown-container {
                display: flex;
                justify-content: space-between;
                gap: 20px;
                margin-bottom: 15px;
                width: 100%;
                box-sizing: border-box;
                padding: 0;
            }

            .dropdown-item {
                flex: 1;
                margin: 0;
            }

            .dropdown-item select {
                width: 100%;
                box-sizing: border-box;
            }

           
            .choice-container {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
                box-sizing: border-box;
            }

            .choice-container input {
                flex-grow: 1;
                box-sizing: border-box;
                border: 1px solid var(--text-color);
                background-color: var(--background-color);
            }
           
            button.remove-icon[title]:hover::after {
                content: attr(title);
                position: absolute;
                background: var(--popup-background-color);
                color: var(--text-color);
                padding: 5px;
                border-radius: 5px;
                font-size: 12px;
                top: 100%;
                left: 50%;
                transform: translateX(-50%);
                white-space: nowrap;
                z-index: 1000;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                box-sizing: border-box;
            }

           
            .form-group {
                margin-bottom: 20px;
            }

            li {
            color: white;
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
                <div class="hamburger" id="hamburger">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <ul class="nav-list">
                    <li><a href="#">Assessments</a>
                        <ul class="dropdown">
                            <li><a href="create_assessment.php">Create New Assessment</a></li>
                            <li><a href="manage_assessments.php">Manage Assessments</a></li>
                            <li><a href="view_assessment_results.php">View Assessment Results</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Users</a>
                        <ul class="dropdown">
                            <li><a href="manage_users.php">Manage Users</a></li>
                            <li><a href="user_feedback.php">User Feedback</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Reports</a>
                        <ul class="dropdown">
                            <li><a href="assessment_performance.php">Assessment Performance</a></li>
                      
                        </ul>
                    </li>
                    <li><a href="#">Resources</a>
                        <ul class="dropdown">
                            <li><a href="useful_links.php">Manage Useful Links</a></li>
                            <li><a href="faq.php">Manage FAQs</a></li>
                            <li><a href="sitemap.php">Manage Sitemap</a></li>
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
                        <li><a>Settings</a>
                                <ul class="dropdown">
                                    <li><a href="manage_profile.php">Manage Profile</a></li>
                                    <li><a href="system_configuration.php">System Configuration Settings</a></li>
                                </ul>
                            </li>
                            <li><a href="#" >Logout</a></li>
                        </ul>
                    </li>                    
                </ul>
            </div>
        </nav>
    </header>   
    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <button class="close-button" id="logout-confirm-button">Yes</button>
        <button class="cancel-button" id="logout-cancel-button">No</button>
    </div> 
    <div id="editContainer">
    <main>
        <h1>Create Questions for Assessment</h1>
        <p>Assessment ID: <strong><?php echo htmlspecialchars($_GET['assessment_id']); ?></strong></p=>
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
            <button type="button" class="success" onclick="saveAssessment()">Save Assessment</button>
        </form>
    </main>
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
                    <h3>Assessments</h3>
                    <ul>
                        <li><a href="create_assessment.php">Create New Assessment</a></li>
                        <li><a href="manage_assessments.php">Manage Assessments</a></li>
                        <li><a href="view_assessment_results.php">View Assessment Results</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Users</h3>
                    <ul>
                        <li><a href="manage_users.php">Manage Users</a></li>
                        <li><a href="user_feedback.php">User Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Reports</h3>
                    <ul>
                        <li><a href="assessment_performance.php">Assessment Performance</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="useful_links.php">Manage Useful Links</a></li>
                        <li><a href="faq.php">Manage FAQs</a></li>
                        <li><a href="sitemap.php">Manage Sitemap</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>About</h3>
                    <ul>
                        <li><a href="about.php">About</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
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
</body>
</html>