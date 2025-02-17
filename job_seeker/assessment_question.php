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


if ($_SESSION['role'] !== 'Job Seeker') {
    displayLoginMessage(); 
}


if (!isset($_SESSION['job_seeker_id'])) {
    displayLoginMessage(); 
}


session_write_close();

if (!isset($_SESSION['job_seeker_id'])) {
    die("ERROR: No job seeker ID in session");
}

if (!isset($_SESSION['last_login']) || $_SESSION['last_login'] !== $_SESSION['user_id']) {
    echo "<script>
        sessionStorage.clear();
        localStorage.clear();
    </script>";
    
    $_SESSION['last_login'] = $_SESSION['user_id'];
}

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Connection to techfit database failed: ' . $conn->connect_error);
}

$check_assessment_sql = "SELECT COUNT(*) as completed 
                        FROM Assessment_Job_Seeker 
                        WHERE job_seeker_id = ? AND end_time IS NOT NULL";

$stmt = $conn->prepare($check_assessment_sql);
$stmt->bind_param("s", $_SESSION['job_seeker_id']);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if ($row['completed'] > 0) {
    echo '<script>
        if (confirm("You have already completed an assessment. Would you like to view your assessment history?")) {
            window.location.href = "assessment_history.php";
        } else {
            window.location.href = "index.php";
        }
    </script>';
    exit();
}

function fetchSectionQuestions($conn, $assessment_id) {
    $questions = [];
    $sql = "SELECT q.*, c.choice_id, c.choice_text 
    FROM Question q 
    LEFT JOIN Choices c ON q.question_id = c.question_id 
    WHERE q.assessment_id = ? AND q.is_active = 1 
    ORDER BY q.question_id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $assessment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        if (!isset($questions[$row['question_id']])) {
            $questions[$row['question_id']] = [
                'question_id' => $row['question_id'],
                'question_text' => $row['question_text'],
                'answer_type' => $row['answer_type'],
                'choices' => []
            ];
        }
        
        if ($row['choice_id']) {
            $questions[$row['question_id']]['choices'][] = [
                'choice_id' => $row['choice_id'],
                'choice_text' => $row['choice_text']
            ];
        }
    }
    
    return array_values($questions);
}

$questions = fetchSectionQuestions($conn, 'AS75');

$settings_query = "SELECT default_time_limit, passing_score_percentage 
                  FROM Assessment_Settings 
                  WHERE setting_id = '1'";
$settings_result = $conn->query($settings_query);
$assessment_settings = $settings_result->fetch_assoc();

$countdownTime = ($assessment_settings['default_time_limit'] ?? 90) * 60;
$conn->close(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Questions - TechFit</title>
    <link rel="stylesheet" href="styles.css">


    <style>

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

        .main-wrapper {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .content-wrapper {
            flex: 1;
            margin-bottom: 0;
            padding-bottom: 40px;
        }

        .assessment-container {
            display: flex;
            gap: 40px;
            padding: 20px;
            width: 100%;
            margin: 0 auto;
            min-width: 900px;
        }

        .question-section {
            flex: 2;
            background-color: var(--background-color-medium);
            color: var(--text-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navigation-panel {
            flex: 1;
            min-width: 300px;
            background-color: var(--background-color-medium);
            color: var(--text-color);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .timer {
            font-size: 1.2em;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: var(--background-color-light);
            color: var(--text-color);
            border-radius: 8px;
        }

        .section-navigation {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .section-box {
            padding: 10px;
            text-align: center;
            background-color: var(--background-color-light);
            color: var(--text-color);
            border-radius: 4px;
            cursor: pointer;
        }

        .section-box.active {
            background-color: var(--primary-color);
            color: white;
        }

        .section-box.locked {
            background: #ccc;
            cursor: not-allowed;
        }

        .question-list-container {
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: var(--background-color);
            border-color: var(--background-color-light);
        }

        .question-list-header {
            padding: 10px;
            background-color: var(--background-color-light);
            color: var(--text-color);
            cursor: pointer;
        }

        .question-list {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 5px;
            padding: 10px;
        }

        .question-box {
            padding: 10px;
            text-align: center;
            background-color: var(--background-color-light);
            color: var(--text-color);
            border-radius: 4px;
            cursor: pointer;
        }

        .question-box.completed {
            background: #28a745;
            color: white;
        }

        .essay-input {
            width: 100%;
            min-height: 150px;
            resize: none;
            padding: 10px;
            background-color: var(--background-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
            border-radius: 4px;
            margin-top: 10px;
            font-family: inherit;
            font-size: inherit;
            overflow-y: hidden;
        }

        .grid-2x2 {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            grid-gap: 15px;
            margin-top: 20px;
            width: 100%;
        }

        .grid-2x2 .option {
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .progress-container {
            width: 100%;
            margin: 10px auto;
            background-color: var(--background-color-medium);
            border-radius: 8px;
            padding: 15px;
        }

        .progress-bar {
            width: 0%;
            height: 10px;
            background-color: #28a745;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .progress-text {
            text-align: center;
            margin-top: 5px;
            font-size: 14px;
            color: var(--text-color);
        }

        .navigation-buttons {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 20px;
        }

        .navigation-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            background-color: var(--primary-color);
            color: white;
        }

        .navigation-buttons button:hover {
            background-color: var(--accent-color);
        }

        .navigation-buttons button:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .navigation-buttons button[onclick="submitAssessment"] {
            background-color: var(--success-color);
        }

        .navigation-buttons button[onclick="submitAssessment"]:hover {
            background-color: var(--success-color-hover);
        }
        
        .question-box.current {
            border: 2px solid #007bff;
        }

        .code-block {
            font-family: monospace;
            background-color: var(--background-color);
            color: var(--text-color);
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            white-space: pre;
            line-height: 1.5;
            position: relative;
            border: 1px solid var(--border-color);
        }

        .code-block input {
            font-family: monospace;
            background-color: var(--background-color);
            color: var(--text-color);
            border: 1px solid #555;
            padding: 2px 4px;
            margin: 0 4px;
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .code-block input:hover {
            border-color: #666;
        }

        .code-block input:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: var(--hover-background-color);
            box-shadow: 0 0 0 1px var(--primary-color);
        }

        .code-blank {
            width: 120px;
            padding: 2px 5px;
            margin: 0 5px;
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: inherit;
            background-color: var(--background-color);
            color: var(--text-color);
            border: 1px solid #555; 
            border-radius: 3px;
            transition: all 0.2s ease;
        }

        .code-blank:hover {
            border-color: #666;
        }

        .code-blank:focus {
            outline: none;
            border-color: var(--primary-color);
            background-color: var(--hover-background-color);
            box-shadow: 0 0 0 1px var(--primary-color);
        }

        .code-container {
            font-family: 'Consolas', 'Monaco', monospace;
            background-color: var(--background-color);
            color: var(--text-color);
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            white-space: pre;
            line-height: 1.5;
            tab-size: 4;
            border: 1px solid var(--border-color);
        }

        .code-container pre {
            margin: 0;
            display: inline;
            background: transparent;
            padding: 0;
            white-space: pre-wrap;
        }

        .language-indicator {
            background-color: var(--background-color-light);
            color: var(--text-color);
            padding: 8px 12px;
            border-radius: 4px 4px 0 0;
            margin-bottom: 0;
            font-weight: bold;
        }

        .code-block {
            font-family: monospace;
            background-color: var(--background-color);
            color: var(--text-color);
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            white-space: pre;
            line-height: 1.5;
            position: relative;
            border: 1px solid var(--border-color);
        }

        input[type="radio"] {
            accent-color: var(--primary-color);
        }

        .option {
            background-color: var(--background-color);
            color: var(--text-color);
            border: 1px solid var(--border-color);
        }

        .option:hover {
            background-color: var(--hover-background-color);
        }

        @media screen and (max-width: 1024px) {
            .assessment-container {
                width: 90%;
                min-width: auto;
                flex-direction: column;
            }
            
            .navigation-panel {
                width: 100%;
            }
        }
    </style>

    <script>

        const DEBUG = true;
        function log(...args) {
        if (DEBUG) console.log(...args);
        }

        let questions = <?php echo json_encode($questions); ?>;
        let currentQuestionIndex = 0;
        let isQuestionListVisible = true;
        let countdownTime = <?php echo $countdownTime; ?>;
        let timerInterval;
        let savedAnswers = {};
        let currentSection = 1;
        let startTime = <?php echo time(); ?>;
        let totalTime = <?php echo $countdownTime; ?>;
        let sectionLastQuestions = {
            '1': 4,
            '2': 4,
            '3': 4,
            '4': 4
        };
        const ANSWER_DELIMITER = '<<ANSWER_BREAK>>';
        let isSubmitting = false;

        function displayQuestion() {
            if (!questions || !questions.length) {
                log('No questions available');
                return;
            }
            
            if (currentQuestionIndex < questions.length) {
                let question = questions[currentQuestionIndex];
                if (!question) {
                    log('Invalid question index');
                    return;
                }

                if (question.question_id === 'Q209' && 
                    document.querySelector('[data-section="3"]').classList.contains('active')) {
                    alert('You cannot modify your programming language choice after accessing Section 3.');
                    if (currentQuestionIndex > 0) {
                        currentQuestionIndex--;
                    } else {
                        currentQuestionIndex++;
                    }
                    displayQuestion();
                    return;
                }

                let questionContainer = document.querySelector('.question');
                if (!questionContainer) {
                    log('Question container not found');
                    return;
                }
                
                questionContainer.innerHTML = '';
                
                const questionNumber = ((currentSection - 1) * 5) + (currentQuestionIndex + 1);
                const questionText = document.createElement('h3');
                questionText.textContent = `Question ${questionNumber}: ${question.question_text}`;
                questionContainer.appendChild(questionText);

                let answerContainer = document.createElement('div');
                answerContainer.className = question.answer_type === 'multiple choice' ? 'options grid-2x2' : 'options';
                
                if (question.answer_type === 'multiple choice') {
                    question.choices.forEach((choice, index) => {
                        const choiceEl = document.createElement('div');
                        choiceEl.className = 'option';
                        choiceEl.innerHTML = `
                            <input type="radio" name="choice" value="${choice.choice_id}" id="choice${index}"
                                ${savedAnswers[question.question_id] === choice.choice_id ? 'checked' : ''}>
                            <label for="choice${index}">${['A', 'B', 'C', 'D'][index]}. ${choice.choice_text}</label>
                        `;
                        answerContainer.appendChild(choiceEl);
                    });
                } else if (question.answer_type === 'essay') {
                    const textarea = document.createElement('textarea');
                    textarea.name = 'answer';
                    textarea.className = 'essay-input';
                    textarea.value = savedAnswers[question.question_id] || '';
                    answerContainer.appendChild(textarea);
                    autoExpandTextarea(textarea);
                } else if (question.answer_type === 'code') {
                    const codeBlock = document.createElement('div');
                    codeBlock.className = 'code-block';
                    
                    
                    const languageIndicator = document.createElement('div');
                    languageIndicator.className = 'language-indicator';
                    const language = question.programming_language.toLowerCase();
                    languageIndicator.textContent = `Language: ${language.charAt(0).toUpperCase() + language.slice(1)}`;
                    questionContainer.appendChild(languageIndicator);
                    
                    
                    const codeContainer = document.createElement('div');
                    codeContainer.className = 'code-container';
                    
                    
                    const template = question.code_template
                        .replace(/\\u([0-9a-fA-F]{4})/g, (match, group) => 
                            String.fromCharCode(parseInt(group, 16)))  
                        .replace(/\\n/g, '\n')  
                        .split('__BLANK__');
                        
                        const savedValues = savedAnswers[question.question_id] ? 
                            savedAnswers[question.question_id].split(ANSWER_DELIMITER) : [];
                    
                    template.forEach((part, index) => {
                        
                        const codePart = document.createElement('pre');
                        codePart.className = 'code-segment';
                        codePart.textContent = part;
                        codeContainer.appendChild(codePart);
                        
                        if (index < template.length - 1) {
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.className = 'code-blank';
                            input.dataset.blankIndex = index;
                            input.value = savedValues[index] || '';
                            codeContainer.appendChild(input);
                        }
                    });
                    
                    
                    const style = document.createElement('style');
                    style.textContent = `
                        .code-segment {
                            white-space: pre;
                            display: inline;
                            font-family: 'Consolas', 'Monaco', monospace;
                            margin: 0;
                            padding: 0;
                            tab-size: 4;
                            color: var(--text-color);
                        }

                        .code-container {
                            font-family: 'Consolas', 'Monaco', monospace;
                            background-color: var(--background-color); 
                            color: var(--text-color);
                            padding: 15px;
                            border-radius: 4px;
                            margin: 10px 0;
                            line-height: 1.5;
                            white-space: pre;
                            tab-size: 4;
                            overflow-x: auto;
                            border: 1px solid var(--border-color);
                        }

                        .code-blank {
                            width: 120px;
                            padding: 2px 5px;
                            margin: 0 5px;
                            font-family: 'Consolas', 'Monaco', monospace;
                            font-size: inherit;
                            background-color: var(--background-color);
                            color: var(--text-color);
                            border: 1px solid #555;
                            border-radius: 3px;
                            transition: all 0.2s ease;
                        }

                        .code-blank:hover {
                            border-color: #666;
                        }

                        .code-blank:focus {
                            outline: none;
                            border-color: var(--primary-color);
                            background-color: var(--hover-background-color);
                            box-shadow: 0 0 0 1px var(--primary-color);
                        }
                    `;
                    document.head.appendChild(style);
                    
                    codeBlock.appendChild(codeContainer);
                    answerContainer.appendChild(codeBlock);
                }
                
                questionContainer.appendChild(answerContainer);
                updateNavigationButtons();
                updateQuestionList();
                checkAndUnlockSection3();
            }
        }

        function getProgrammingAssessmentId() {
            
            log('Q209 answer:', savedAnswers['Q209']);
            
            
            const assessmentMap = {
                'C101': 'AS77', 
                'C102': 'AS78', 
                'C103': 'AS79', 
                'C104': 'AS80'  
            };
            
            return savedAnswers['Q209'] ? assessmentMap[savedAnswers['Q209']] : null;
        }
        
        function loadSectionQuestions(sectionId) {
            return new Promise((resolve, reject) => {
                if (!sectionId) {
                    log('Error: Invalid section ID');
                    reject(new Error('Invalid section ID'));
                    return;
                }

                const assessmentIds = {
                    '1': 'AS75',
                    '2': 'AS76', 
                    '3': getProgrammingAssessmentId(),
                    '4': 'AS81'
                };

                const assessmentId = assessmentIds[sectionId];

                if (!assessmentId) {
                    reject(new Error('Invalid assessment ID'));
                    return;
                }

                saveAllAnswers().then(() => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('GET', `get_section_questions.php?assessment_id=${assessmentId}`, true);
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                const responseText = xhr.responseText.trim();
                                if (!responseText) {
                                    log('Error: Empty response from server');
                                    reject(new Error('Empty response from server'));
                                    return;
                                }

                                const QUESTION_DELIMITER = '<<QUESTION_BREAK>>';
                                const FIELD_DELIMITER = '<<FIELD>>';

                                const questionsData = responseText.split(QUESTION_DELIMITER);
                                questions = questionsData.map(q => {
                                    const parts = q.split(FIELD_DELIMITER);
                                    log('Raw question parts:', parts);

                                    
                                    const [
                                        questionId = '',
                                        questionText = '',
                                        answerType = '',
                                        choicesStr = '',
                                        codeTemplate = '',
                                        language = ''
                                    ] = parts;

                                    
                                    const cleanLanguage = language.toLowerCase().trim();

                                    
                                    const choices = choicesStr ? choicesStr.split('~').map(choice => {
                                        const [choiceId, choiceText] = choice.split('=');
                                        return { choice_id: choiceId, choice_text: choiceText };
                                    }) : [];

                                    
                                    return {
                                        question_id: questionId,
                                        question_text: questionText,
                                        answer_type: answerType,
                                        code_template: codeTemplate.replace(/\\n/g, '\n'),
                                        programming_language: cleanLanguage,
                                        choices: choices
                                    };
                                });

                                currentQuestionIndex = 0;
                                currentSection = parseInt(sectionId);
                                
                                
                                displayQuestion();
                                updateQuestionList();
                                loadSavedAnswers(assessmentId);
                                updateNavigationButtons();
                                updateSectionUI(sectionId);
                                resolve();

                            } catch (e) {
                                log('Error parsing questions:', e);
                                reject(e);
                            }
                        } else {
                            log('Error loading section:', xhr.statusText);
                            reject(new Error(`HTTP error! status: ${xhr.status}`));
                        }
                    };

                    xhr.onerror = () => reject(new Error('Network error'));
                    xhr.send();
                }).catch(error => reject(error));
            });
        }

        function updateSectionUI(sectionId) {
            const sections = {
                '1': 'General Questions',
                '2': 'Scenario-Based Questions', 
                '3': 'Programming Questions',
                '4': 'Work-Style and Personality'
            };
            
            log('Updating section UI for section:', sectionId);

            
            document.getElementById('section-title').textContent = 
                `Section ${sectionId}: ${sections[sectionId]}`;
                
            
            document.querySelectorAll('.section-box').forEach(box => {
                box.classList.remove('active');
            });
            document.querySelector(`[data-section="${sectionId}"]`).classList.add('active');
        }

        function loadSavedAnswers(assessmentId) {
            if (!assessmentId) {
                log('Error: No assessment ID provided');
                return;
            }

            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_answers.php?assessment_id=${assessmentId}&job_seeker_id=<?php echo $_SESSION["job_seeker_id"]; ?>`, true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        log('Raw server response:', xhr.responseText);
                        const answers = xhr.responseText.trim() ? xhr.responseText.split('<<ANSWER_SET>>').map(a => {
                            const [qid, ans] = a.split('<<QA_BREAK>>');
                            log('Parsing answer:', {qid, ans});
                            return [qid, ans];
                        }) : [];
                        
                        savedAnswers = {};
                        answers.forEach(([questionId, answer]) => {
                            if (!questionId || !answer) return;
                            
                            const question = questions.find(q => q.question_id === questionId);
                            log('Processing answer:', {questionId, answer, type: question?.answer_type});
                            
                            
                            if (answer && question?.answer_type === 'code') {
                                
                                const parts = answer.split(ANSWER_DELIMITER);
                                savedAnswers[questionId] = parts.join(ANSWER_DELIMITER);
                            } else {
                                savedAnswers[questionId] = answer;
                            }
                        });

                        log('Final savedAnswers:', savedAnswers);
                        displaySavedAnswers();
                        checkAndUnlockSection3();
                        updateProgress();
                    } catch (e) {
                        log('Error parsing saved answers:', e);
                        alert('Failed to load saved answers. Please try again.');
                    }
                }
            };
            xhr.send();
        }

        function displaySavedAnswers() {
            const questionContainer = document.querySelector('.question');
            const currentQuestion = questions[currentQuestionIndex];
            
            if (!currentQuestion) return;
            
            log('Displaying answer for:', {
                questionId: currentQuestion.question_id,
                type: currentQuestion.answer_type,
                savedAnswer: savedAnswers[currentQuestion.question_id]
            });

            const savedAnswer = savedAnswers[currentQuestion.question_id];
            
            if (savedAnswer) {
                if (currentQuestion.answer_type === 'multiple choice') {
                    const radio = questionContainer.querySelector(`input[value="${savedAnswer}"]`);
                    if (radio) radio.checked = true;
                } else if (currentQuestion.answer_type === 'essay') {
                    const textarea = questionContainer.querySelector('textarea');
                    if (textarea) {
                        textarea.value = savedAnswer;
                        autoExpandTextarea(textarea);
                    }
                } else if (currentQuestion.answer_type === 'code') {
                    const inputs = questionContainer.querySelectorAll('.code-blank');
                    
                    const savedValues = savedAnswer.split(ANSWER_DELIMITER);
                    
                    inputs.forEach((input, index) => {
                        input.value = savedValues[index] || '';
                    });
                }
                
                updateQuestionList();
            }
        }

        function checkProgrammingLanguages() {
            fetch('get_programming_answer.php?question_id=Q209')
                .then(response => response.json())
                .then(data => {
                    if (data.answer) {
                        document.querySelector('[data-section="3"]').classList.remove('locked');
                        
                        log('Programming language selected:', data.answer);
                    }
                })
                .catch(error => {
                    log('Error checking programming answer:', error);
                });
        }

        function handleSection3Access() {
            const section3Box = document.querySelector('[data-section="3"]');
            log('Handling Section 3 access, Q209 answer:', savedAnswers['Q209']);
            
            return fetch('get_programming_answer.php?question_id=Q209')
                .then(response => response.json())
                .then(data => {
                    log('Programming language data:', data);
                    if (data.answer) {
                        savedAnswers['Q209'] = data.answer;
                        section3Box.classList.remove('locked');
                        return loadSectionQuestions('3');
                    } else {
                        throw new Error('No programming language selected');
                    }
                })
                .then(() => {
                    updateNavigationButtons();
                    updateSectionUI('3');
                })
                .catch(error => {
                    log('Section 3 access error:', error);
                    alert('Please complete Question 4 in Section 1 to access the programming section.');
                });
        }

        function loadSection3Questions(language) {
            const languageAssessments = {
                'C101': 'AS77', 
                'C102': 'AS78', 
                'C103': 'AS79', 
                'C104': 'AS80'  
            };
            
            
            const assessmentId = languageAssessments[savedAnswers['Q209']];
            if (assessmentId) {
                loadSectionQuestions('3'); 
            } else {
                log('Invalid programming language choice');
            }
        }

        function startTimer(display) {
            const endTime = startTime + totalTime;
            let hasExpired = false;  

            function updateDisplay() {
                const now = Math.floor(Date.now() / 1000);
                const remaining = endTime - now;
                
                log('Timer check:', {
                    now,
                    endTime,
                    remaining,
                    hasExpired
                });
                
                if (remaining <= 0 && !hasExpired) {
                    
                    hasExpired = true;  
                    clearInterval(timerInterval);
                    display.textContent = "Time's up!";
                    submitAssessment(true);
                    return;
                }
                
                if (remaining > 0) {
                    const minutes = Math.floor(remaining / 60);
                    const seconds = remaining % 60;
                    display.textContent = `Time Remaining: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
                }
            }
            
            updateDisplay();
            timerInterval = setInterval(updateDisplay, 1000);
        }

        function saveTimerState() {
            sessionStorage.setItem('assessmentTimer', JSON.stringify({
                startTime: startTime,
                totalTime: totalTime
            }));
        }

        function saveProgress() {
            sessionStorage.setItem('assessmentProgress', JSON.stringify({
                savedAnswers,
                currentSection,
                currentQuestionIndex
            }));
        }

        function loadProgress() {
            const saved = sessionStorage.getItem('assessmentProgress');
            if (saved) {
                const progress = JSON.parse(saved);
                savedAnswers = progress.savedAnswers;
                currentSection = progress.currentSection;
                currentQuestionIndex = progress.currentQuestionIndex;
            }
        }

        window.onload = function() {
            const savedState = sessionStorage.getItem('assessmentState');
            if (savedState) {
                const state = JSON.parse(savedState);
                currentSection = state.currentSection;
                currentQuestionIndex = state.currentQuestionIndex;
                savedAnswers = state.savedAnswers;
                startTime = state.startTime;
                totalTime = state.totalTime;
                
                loadSectionQuestions(currentSection.toString());
            } else {

                currentSection = 1;
                loadSectionQuestions('1');
            }
            
            startTimer(document.getElementById('timer'));
            updateQuestionList();
            checkProgrammingLanguages();
        }

        function updateProgress() {
            const allAnswers = JSON.parse(sessionStorage.getItem('allAnswers') || '{}');
            Object.assign(allAnswers, savedAnswers);
            sessionStorage.setItem('allAnswers', JSON.stringify(allAnswers));
            
            const totalQuestions = 20;
            const completed = Object.keys(allAnswers).length;
            const progress = (completed / totalQuestions) * 100;
            
            document.querySelector('.progress-bar').style.width = `${progress}%`;
            document.querySelector('.progress-text').textContent = 
                `${completed}/${totalQuestions} questions answered`;
        }

        function saveAnswer(questionId, answer) {
            const currentQuestion = questions[currentQuestionIndex];

            log('Attempting to save answer:', {
                questionId,
                answer,
                type: currentQuestion?.answer_type
            });

            const hasConfirmedLanguage = sessionStorage.getItem('confirmedLanguageChoice');
            const isSection3Active = document.querySelector('[data-section="3"]').classList.contains('active');

            if (currentQuestion?.answer_type === 'code' && (!answer || answer === '||')) {
                log('Skipping empty code answer');
                return;
            }

            
            if (questionId === 'Q209' && isSection3Active) {
                alert('You cannot modify your programming language choice after accessing Section 3.');
                
                const inputs = document.querySelectorAll('input[type="radio"]');
                inputs.forEach(input => {
                    input.checked = input.value === savedAnswers[questionId];
                });
                return;
            }

            
            if (questionId === 'Q209' && hasConfirmedLanguage && answer !== savedAnswers['Q209']) {
                alert('You cannot modify your programming language choice after confirmation.');
                
                const inputs = document.querySelectorAll('input[type="radio"]');
                inputs.forEach(input => {
                    input.checked = input.value === savedAnswers[questionId];
                });
                return;
            }

            let formData = new FormData();

            if (!answer || answer.trim() === '') {
                if (savedAnswers[questionId]) {
                    delete savedAnswers[questionId];
                    updateProgress();
                    updateQuestionList();
                    
                    const allQuestionBoxes = document.querySelectorAll('.question-box');
                    const currentQuestionNumber = ((currentSection - 1) * 5) + currentQuestionIndex;
                    if (allQuestionBoxes[currentQuestionNumber]) {
                        allQuestionBoxes[currentQuestionNumber].classList.remove('completed');
                    }
                }
                return;
            }

            if (!questionId || !answer) {
                log('Invalid question ID or answer');
                return;
            }

            
            if (questionId === 'Q209' && !hasConfirmedLanguage) {
                const languageMap = {
                    'C101': 'Python', 
                    'C102': 'Java',
                    'C103': 'JavaScript',
                    'C104': 'C++'
                };

                if (!confirm(`You have selected ${languageMap[answer]} as your programming language. This choice will determine your programming questions in Section 3 and cannot be changed after confirmation. Are you sure?`)) {
                    
                    const inputs = document.querySelectorAll('input[type="radio"]');
                    inputs.forEach(input => {
                        input.checked = input.value === savedAnswers[questionId];
                    });
                    return;
                }
                
                sessionStorage.setItem('confirmedLanguageChoice', 'true');
            }

            
            if (currentQuestion && currentQuestion.answer_type === 'code') {
                const inputs = document.querySelectorAll('.code-blank');
                const answerText = Array.from(inputs)
                    .map(input => input.value.trim())
                    .join(ANSWER_DELIMITER);
                
                formData.append('question_id', questionId);
                formData.append('answer_text', answerText);
                formData.append('answer_type', 'code');
                formData.append('job_seeker_id', '<?php echo $_SESSION["job_seeker_id"]; ?>');
            } else {
                formData.append('question_id', questionId);
                formData.append('answer_text', answer);
                formData.append('job_seeker_id', '<?php echo $_SESSION["job_seeker_id"]; ?>');
            }

            
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'save_answer.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const response = xhr.responseText;
                    if (response.startsWith('SUCCESS')) {
                        savedAnswers[questionId] = answer;
                        updateProgress();
                        updateQuestionList();
                        
                        
                        if (questionId === 'Q209') {
                            checkAndUnlockSection3();
                            
                            const section3Box = document.querySelector('[data-section="3"]');
                            if (section3Box) {
                                section3Box.classList.remove('locked');
                                section3Box.style.cursor = 'pointer';
                            }
                        }
                        
                        log('Answer saved successfully');
                    } else {
                        log('Failed to save answer:', response);
                        alert('Failed to save answer. Please try again.');
                    }
                } else {
                    log('Failed to save answer:', xhr.statusText);
                    alert('Failed to save answer. Please try again.');
                }
            };

            xhr.onerror = function() {
                console.error('Error saving answer');
                alert('Network error while saving answer. Please try again.');
            };
            xhr.send(formData);
        }

        function autoExpandTextarea(element) {
            element.style.height = 'auto';
            element.style.height = (element.scrollHeight) + 'px';
        }

        document.addEventListener('input', function(e) {
            if (e.target.matches('.code-blank')) {
                const currentQuestion = questions[currentQuestionIndex];
                const inputs = document.querySelectorAll('.code-blank');
                const answer = Array.from(inputs)
                    .map(input => input.value.trim())
                    .join(ANSWER_DELIMITER);
                saveAnswer(currentQuestion.question_id, answer);
            }
        });

        
        document.addEventListener('input', function(e) {
            if (e.target.matches('textarea.essay-input')) {
                const currentQuestion = questions[currentQuestionIndex];
                autoExpandTextarea(e.target);
                saveAnswer(currentQuestion.question_id, e.target.value);
            }
        });

        document.addEventListener('input', function(e) {
            if (e.target.matches('textarea.essay-input')) {
                const currentQuestion = questions[currentQuestionIndex];
                log('Essay input changed:', e.target.value);
                saveAnswer(currentQuestion.question_id, e.target.value);
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.target.matches('textarea.essay-input') && e.key === 'Enter') {
                autoExpandTextarea(e.target);
            }
        });

        
        document.addEventListener('change', function(e) {
            if (e.target.matches('input[type="radio"][name="choice"]')) {
                const currentQuestion = questions[currentQuestionIndex];
                saveAnswer(currentQuestion.question_id, e.target.value);
            }
        });

        function saveAllAnswers() {
            const answers = Object.entries(savedAnswers);
            if (answers.length === 0) {
                log('No answers to save');
                return Promise.resolve();
            }

            log('Saving all answers:', answers.length);

            const savePromises = answers.map(([questionId, answer]) => {
                return new Promise((resolve, reject) => {
                    const formData = new FormData();
                    formData.append('question_id', questionId);
                    formData.append('answer_text', answer);
                    formData.append('job_seeker_id', '<?php echo $_SESSION["job_seeker_id"]; ?>');
                    
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'save_answer.php', true);
                    
                    xhr.onload = function() {
                        log('Save answer response:', questionId, xhr.status);
                        if (xhr.status === 200) {
                            resolve();
                        } else {
                            reject(new Error(`HTTP error! status: ${xhr.status}`));
                        }
                    };
                    
                    xhr.onerror = function(e) {
                        log('Network error saving answer:', questionId, e);
                        reject(new Error('Network error'));
                    };
                    
                    xhr.send(formData);
                });
            });

            return Promise.all(savePromises)
                .then(() => {
                    log('All answers saved successfully');
                })
                .catch(error => {
                    log('Error saving answers:', error);
                    throw error;
                });
        }

        window.addEventListener('beforeunload', function(e) {
            if (isSubmitting) {
                return;
            }

            try {
                const message = 'Are you sure you want to leave? Your changes may not be saved.';
                e.preventDefault();
                e.returnValue = message;
                saveAllAnswers();
                saveTimerState();
                return message;
            } catch (error) {
                console.error('Error in beforeunload:', error);
            }
        });

        function toggleQuestionList() {
            const questionList = document.getElementById('question-list');
            const header = document.querySelector('.question-list-header');
            
            isQuestionListVisible = !isQuestionListVisible;
            questionList.style.display = isQuestionListVisible ? 'grid' : 'none';
            header.innerHTML = `Question List ${isQuestionListVisible ? '▼' : '▲'}`;
        }

        function updateNavigationButtons() {
            
            const prevBtn = document.getElementById('prev-btn');
            prevBtn.style.display = (currentQuestionIndex === 0 && currentSection === 1) ? 'none' : 'block';
            
            
            const nextBtn = document.getElementById('next-btn');
            if (currentSection === 4 && currentQuestionIndex === questions.length - 1) {
                nextBtn.textContent = 'Submit';
                nextBtn.onclick = () => {
                    log('Submit button clicked - calling submitAssessment with isTimeUp=false');
                    submitAssessment(false);
                };
                
                nextBtn.style.backgroundColor = 'var(--success-color)';
                nextBtn.style.color = 'white';
                nextBtn.addEventListener('mouseover', function() {
                    this.style.backgroundColor = 'var(--success-color-hover)';
                });
                nextBtn.addEventListener('mouseout', function() {
                    this.style.backgroundColor = 'var(--success-color)';
                });
            } else {
                nextBtn.textContent = 'Next';
                nextBtn.onclick = nextQuestion;
                
                nextBtn.style.backgroundColor = 'var(--primary-color)';
                nextBtn.style.color = 'white';
                nextBtn.addEventListener('mouseover', function() {
                    this.style.backgroundColor = 'var(--accent-color)';
                });
                nextBtn.addEventListener('mouseout', function() {
                    this.style.backgroundColor = 'var(--primary-color)';
                });
            }
        }

        function updateQuestionList() {
            const questionList = document.getElementById('question-list');
            questionList.innerHTML = '';
            
            const allAnswers = JSON.parse(sessionStorage.getItem('allAnswers') || '{}');
            
            
            const validQuestions = questions.filter(q => q && q.question_id);
            
            validQuestions.forEach((question, index) => {
                const box = document.createElement('div');
                const isQ209 = question.question_id === 'Q209';
                const isSection3Active = document.querySelector('[data-section="3"]').classList.contains('active');
                
                
                const globalQuestionNumber = ((currentSection - 1) * 5) + (index + 1);
                
                
                if (globalQuestionNumber <= 20) {
                    box.className = `question-box${
                        (savedAnswers[question.question_id] || allAnswers[question.question_id]) ? ' completed' : ''
                    }${index === currentQuestionIndex ? ' current' : ''}${
                        isQ209 && isSection3Active ? ' locked' : ''
                    }`;
                    
                    box.textContent = globalQuestionNumber;
                    box.onclick = () => jumpToQuestion(index);
                    
                    if (isQ209 && isSection3Active) {
                        box.style.cursor = 'not-allowed';
                        box.title = 'This question cannot be modified after accessing Section 3';
                    }
                    
                    questionList.appendChild(box);
                }
            });
        }

        function isQuestionAnswered(questionId) {
            
            return savedAnswers.hasOwnProperty(questionId);
        }

        function submitAssessment(isTimeUp = false) {
            isSubmitting = true;
            if (document.querySelectorAll('button, input, textarea')[0].disabled) {
                return;
            }

            log('submitAssessment entry point:', {
                isTimeUp,
                calledFrom: new Error().stack,
                currentSection,
                currentQuestionIndex
            });

            
            if (!isTimeUp) {
                const allAnswers = JSON.parse(sessionStorage.getItem('allAnswers') || '{}');
                const totalAnswered = Object.keys(allAnswers).length;
                const totalQuestions = 20;
                
                if (totalAnswered < totalQuestions) {
                    const remaining = totalQuestions - totalAnswered;
                    alert(`Please answer all questions before submitting. You still have ${remaining} unanswered ${remaining === 1 ? 'question' : 'questions'}.`);
                    return;
                }

                if (!confirm('Are you sure you want to submit your assessment? This action cannot be undone.')) {
                    return;
                }
            }

            
            document.querySelectorAll('button, input, textarea').forEach(el => el.disabled = true);

            
            saveAllAnswers()
                .then(() => {
                    log('All answers saved, updating assessment time');
                    return new Promise((resolve, reject) => {
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'update_assessment_time.php', true);
                        xhr.onload = () => resolve(xhr.status === 200);
                        xhr.onerror = () => reject(new Error('Network error'));
                        xhr.send();
                    });
                })
                .then((success) => {
                    log('Assessment time update complete:', success);
                    if (isTimeUp) {
                        alert("Time's up! Your assessment will be submitted automatically.");
                    }
                    return cleanupAndRedirect(); 
                })
                .catch(error => {
                    isSubmitting = false;
                    log('Error during submission:', error);
                    
                    document.querySelectorAll('button, input, textarea').forEach(el => el.disabled = false);
                    if (!isTimeUp) {
                        alert('There was an error submitting your assessment. Please try again.');
                    }
                });
        }

        function cleanupAndRedirect() {
            log('Starting cleanup before redirect');
            
            return new Promise((resolve) => {
                
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
                
                
                window.removeEventListener('beforeunload', saveState);
                
                
                sessionStorage.clear();
                
                
                setTimeout(() => {
                    log('Cleanup complete, redirecting to results page');
                    window.location.replace('assessment_result.php');
                    resolve();
                }, 500);
            });
        }

        function nextQuestion() {
            const currentQuestion = questions[currentQuestionIndex];
            if (!currentQuestion) return;
            
            const answer = getAnswerValue();
                if (answer !== null && answer !== '') {
                    log('Saving answer:', {
                        questionId: currentQuestion.question_id,
                        answer: answer
                    });
                    saveAnswer(currentQuestion.question_id, answer);
                }
            
            if (currentQuestionIndex < questions.length - 1) {
                currentQuestionIndex++;
                displayQuestion();
            } else if (currentSection < 4) {
                const nextSection = currentSection + 1;
                if (nextSection === 3) {
                    
                    fetch('get_programming_answer.php?question_id=Q209')
                        .then(response => response.json())
                        .then(data => {
                            if (data.answer) {
                                savedAnswers['Q209'] = data.answer;
                                handleSection3Access();
                            } else {
                                alert('Please complete Question 4 in Section 1 to access the programming section.');
                            }
                        })
                        .catch(error => {
                            log('Error checking programming answer:', error);
                            alert('Please complete Question 4 in Section 1 to access the programming section.');
                        });
                } else {
                    switchSection(nextSection.toString());
                }
            } else {
                document.getElementById('next-btn').textContent = 'Submit';
                document.getElementById('next-btn').onclick = submitAssessment;
            }
            
            saveState();
        }

        function previousQuestion() {
            const currentQuestion = questions[currentQuestionIndex];
            if (!currentQuestion) return;

            
            const answer = getAnswerValue();
            if (answer !== null && answer !== '') {
                saveAnswer(currentQuestion.question_id, answer);
            }
            
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                displayQuestion();
            } else if (currentSection > 1) {
                const prevSection = currentSection - 1;
                if (currentSection === 4) {
                    
                    fetch('get_programming_answer.php?question_id=Q209')
                        .then(response => response.json())
                        .then(data => {
                            if (!data.answer) {
                                alert('Please complete Question 4 in Section 1 to access the programming section.');
                                return;
                            }
                            savedAnswers['Q209'] = data.answer;
                            if (prevSection === 3) {
                                
                                switchSection('3', 4); 
                            } else {
                                switchSection(prevSection.toString(), 4);
                            }
                        })
                        .catch(error => {
                            log('Error checking programming answer:', error);
                            alert('Please complete Question 4 in Section 1 to access the programming section.');
                        });
                } else {
                    switchSection(prevSection.toString(), 4);
                }
            }
            
            saveState();
        }

        function getAnswerValue() {
            const questionContainer = document.querySelector('.question');
            const currentQuestion = questions[currentQuestionIndex];
            
            if (!currentQuestion) {
                log('No current question found');
                return null;
            }
            
            if (currentQuestion.answer_type === 'multiple choice') {
                const radio = questionContainer.querySelector('input[name="choice"]:checked');
                if (radio) {
                    log('Got multiple choice answer:', radio.value);
                    return radio.value;
                }
            } else if (currentQuestion.answer_type === 'essay') {
                const textarea = questionContainer.querySelector('textarea');
                if (textarea && textarea.value.trim()) {
                    log('Got essay answer:', textarea.value);
                    return textarea.value.trim();
                }
            }

            if (currentQuestion.answer_type === 'code') {
                const inputs = questionContainer.querySelectorAll('.code-blank');
                const values = Array.from(inputs)
                    .map(input => input.value.trim());
                
                
                if (values.some(v => v !== '')) {
                    log('Got code answer:', values.join(ANSWER_DELIMITER));
                    return values.join(ANSWER_DELIMITER);
                }
                log('No code answer found');
                return null;
            }
            
            log('No answer value found');
            return null;
        }

        function jumpToQuestion(index) {
            if (index >= 0 && index < questions.length) {
                
                if (questions[index].question_id === 'Q209' && 
                    document.querySelector('[data-section="3"]').classList.contains('active')) {
                    alert('You cannot modify your programming language choice after accessing Section 3.');
                    return;
                }
                currentQuestionIndex = index;
                displayQuestion();
            }
        }

        function switchSection(sectionId, startIndex = 0) {
            log('Switching to section:', sectionId);

            
            if (!['1', '2', '3', '4'].includes(sectionId)) {
                log('Invalid section ID:', sectionId);
                return;
            }

            
            if (sectionId === '3' && !savedAnswers['Q209']) {
                alert('Please complete Question 4 in Section 1 to access the programming section.');
                return;
            }

            
            if (sectionId === '1' && savedAnswers['Q209'] && 
                document.querySelector('[data-section="3"]').classList.contains('active')) {
                alert('You cannot modify your programming language after accessing Section 3.');
                return;
            }

            
            currentSection = parseInt(sectionId);
            sessionStorage.setItem('currentSection', sectionId);
            
            
            loadSectionQuestions(sectionId)
                .then(() => {
                    currentQuestionIndex = startIndex;
                    displayQuestion();
                    updateNavigationButtons();
                    updateSectionUI(sectionId);
                })
                .catch(error => {
                    log('Error loading section questions:', error);
                    alert('Error loading questions. Please try again.');
                });
        }

        function saveState() {
            const userId = '<?php echo $_SESSION["job_seeker_id"]; ?>';
            const state = {
                currentSection: currentSection,
                currentQuestionIndex: currentQuestionIndex,
                savedAnswers: savedAnswers,
                startTime: startTime,
                totalTime: totalTime,
                userId: userId
            };
            sessionStorage.setItem(`assessment_state_${userId}`, JSON.stringify(state));
        }

        function loadState() {
            const userId = '<?php echo $_SESSION["job_seeker_id"]; ?>';
            const state = JSON.parse(sessionStorage.getItem(`assessment_state_${userId}`));
            
            if (state && state.userId === userId) {
                currentSection = state.currentSection;
                currentQuestionIndex = state.currentQuestionIndex;
                savedAnswers = state.savedAnswers;
                startTime = state.startTime;
                totalTime = state.totalTime;
            } else {

                currentSection = 1;
                savedAnswers = {};
                startTime = <?php echo time(); ?>;
                totalTime = <?php echo $countdownTime; ?>;
                sessionStorage.removeItem('allAnswers');
            }
            
            loadSectionQuestions(currentSection.toString());
        }

        function checkAndUnlockSection3() {
            if (savedAnswers['Q209']) {
                const section3Box = document.querySelector('[data-section="3"]');
                const hasConfirmedLanguage = sessionStorage.getItem('confirmedLanguageChoice');
                const isSection3Active = document.querySelector('[data-section="3"]').classList.contains('active');
                const languageMap = {
                    'C101': 'Python',
                    'C102': 'Java',
                    'C103': 'JavaScript',
                    'C104': 'C++'
                };
                
                
                const currentQuestion = questions[currentQuestionIndex];
                if (currentQuestion && currentQuestion.question_id === 'Q209') {
                    const radioInputs = document.querySelectorAll('input[type="radio"][name="choice"]');
                    radioInputs.forEach(input => {
                        if (input.value === savedAnswers['Q209']) {
                            input.checked = true;
                        }
                        
                        input.disabled = (currentSection === 1 && (isSection3Active || hasConfirmedLanguage));
                    });
                }
                
                if (section3Box) {
                    section3Box.classList.remove('locked');
                    section3Box.title = `Selected language: ${languageMap[savedAnswers['Q209']]}`;
                    log('Section 3 unlocked with language:', languageMap[savedAnswers['Q209']]);
                }
            }
        }

        
        window.addEventListener('beforeunload', saveState);

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.section-box').forEach(box => {
                box.addEventListener('click', () => {
                    const sectionId = box.dataset.section;
                    log('Section box clicked:', sectionId);
                    
                    if (box.classList.contains('locked')) {
                        log('Section is locked');
                        return;
                    }

                    if (sectionId === '3') {
                        log('Attempting to access section 3');
                        handleSection3Access();
                    } else {
                        log('Switching to section:', sectionId);
                        switchSection(sectionId);
                    }
                });
            });
        });
    </script>
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

    <div class="progress-container">
        <div class="progress-bar"></div>
        <div class="progress-text">0/5 questions answered</div>
    </div>

    <div class="assessment-container">
        <div class="question-section">
            <h2 id="section-title">Section 1: General Questions</h2>
            <div class="question"></div>
            <div class="navigation-buttons">
                <button id="prev-btn" onclick="previousQuestion()">Previous</button>
                <button id="next-btn" onclick="nextQuestion()">Next</button>
            </div>
        </div>

        <div class="navigation-panel">
            <div id="timer" class="timer">Time Remaining: 00:00</div>

            <div class="section-navigation">
                <div class="section-box active" data-section="1">Section 1</div>
                <div class="section-box" data-section="2">Section 2</div>
                <div class="section-box locked" data-section="3">Section 3</div>
                <div class="section-box" data-section="4">Section 4</div>
            </div>

            <div class="question-list-container">
                <div class="question-list-header" onclick="toggleQuestionList()">
                    Question List ▼
                </div>
                <div id="question-list" class="question-list">
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
    <script src="scripts.js"></script>
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

        const answers = document.querySelectorAll('input[name="answer"]');
        const feedback = document.getElementById('feedback');

        
        answers.forEach(answer => {
            answer.addEventListener('change', () => {
                document.querySelector('.submit_but button').disabled = false;
            });
        });

        
        startTimer(countdownTime, document.getElementById('timer'), document.querySelector('.submit_but button'));
    </script>
</body>
</html>