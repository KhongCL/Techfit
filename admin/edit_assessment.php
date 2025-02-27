<?php
session_start(); 


function displayLoginMessage() {
    echo '<script>
        alert("You need to log in to access this page.");
    </script>';
    exit();
}


if (!isset($_SESSION['user_id'])) {
    displayLoginMessage(); 
}


if ($_SESSION['role'] !== 'Admin') {
    displayLoginMessage(); 
}


session_write_close();
?>

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
                <input type="hidden" id="question_id_${questionCount}" name="question_id[]" value="">
                <p>Question ID: <strong id="display_question_id_${questionCount}"></strong></p>
                <label for="question_text_${questionCount}">Question Text:</label>
                <textarea id="question_text_${questionCount}" "style=border: 1px solid white; background-color: var(--background-color)" name="question_text[]" required></textarea><br>

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
                        <select id="answer_type_${questionCount}" style="border: 1px solid white; background-color: var(--background-color); width: 91%;" name="answer_type[]" onchange="showAnswerOptions(${questionCount})" required>
                            <option value="multiple choice">Multiple Choice</option>
                            <option value="true/false">True/False</option>
                            <option value="fill in the blank">Fill in the Blank</option>
                            <option value="essay">Essay</option>
                            <option value="code">Code</option>
                        </select>
                    </div>
                </div>

                <div id="answer_options_${questionCount}">
                    ${getMultipleChoiceOptions(questionCount, false)}
                </div>
                <button type="button" class="danger" onclick="removeQuestion(${questionCount})">Remove Question</button>
                <hr>
            `;
            document.getElementById('questions').appendChild(questionDiv);
            console.log('addQuestion:', questionDiv.innerHTML); 
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

                    if (questionText.value.trim() === '' || questionType.value.trim() === '' || answerType.value.trim() === '' || correctChoice.value.trim() === '') {
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

                        
                        const answers = correctAnswers.value.split('<<ANSWER_BREAK>>');
                        if (answers.length < 2) {
                            alert('Please provide at least two answers separated by <<ANSWER_BREAK>>');
                            return; 
                        }
                        if (answers.some(a => a.trim() === '')) {
                            alert('Empty or blank answers are not allowed. Please provide valid answers separated by <<ANSWER_BREAK>>');
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
                const formData = new FormData(form);

                
                for (let pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_questions.php', true);
                xhr.onload = function () {
                    if (xhr.status === 200) {
                        alert('Assessment updated successfully.');
                        window.location.href = 'manage_assessments.php';
                    } else {
                        alert('Failed to update assessment.');
                    }
                };
                xhr.onerror = function () {
                    alert('An error occurred while updating the assessment.');
                };
                xhr.send(formData);
            }
        }

        function showAnswerOptions(id, includeEmptyChoice = true) {
            const answerType = document.getElementById(`answer_type_${id}`).value;
            const answerOptionsDiv = document.getElementById(`answer_options_${id}`);
            answerOptionsDiv.innerHTML = '';

            if (answerType === 'multiple choice') {
                answerOptionsDiv.innerHTML = getMultipleChoiceOptions(id, includeEmptyChoice);
                console.log('showAnswerOptions (multiple choice):', answerOptionsDiv.innerHTML); 
            } else if (answerType === 'true/false') {
                answerOptionsDiv.innerHTML = `
                    <label for="true_false_${id}">Answer:</label>
                    <select id="true_false_${id}" style="border: 1px solid white; background-color: var(--background-color)" name="correct_choice[]" required>
                        <option value="true">True</option>
                        <option value="false">False</option>
                    </select>
                `;
            } else if (answerType === 'fill in the blank') {
                answerOptionsDiv.innerHTML = `
                    <label for="blank_${id}">Blank:</label>
                    <input type="text" id="blank_${id}" style="border: 1px solid white; background-color: var(--background-color)" name="correct_choice[]" required>
                `;
            } else if (answerType === 'essay') {
                answerOptionsDiv.innerHTML = `
                    <label for="essay_${id}">Correct Answer:</label>
                    <textarea id="essay_${id}" style="border: 1px solid white; background-color: var(--background-color)" name="correct_choice[]" required></textarea>
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
                    <input type="text" style="border: 1px solid white; background-color: var(--background-color); name="choices_${id}[]" required>
                `;
            }
            choicesHtml += `
                    <button type="button" onclick="addChoice(${id})">Add Choice</button>
                </div>
                <label for="correct_choice_${id}">Correct Choice:</label>
                <select id="correct_choice_${id}" style="border: 1px solid white; background-color: var(--background-color)" name="correct_choice[]" required></select>
            `;
            console.log('getMultipleChoiceOptions:', choicesHtml); 
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

        function addChoice(id, choiceId = '', choiceText = '') {
            const choicesDiv = document.getElementById(`choices_${id}`);
            const choiceContainer = document.createElement('div');
            choiceContainer.className = 'choice-container';
            const input = document.createElement('input');
            input.type = 'text';
            input.name = `choices_${id}[]`;
            input.required = true;
            input.value = choiceText; 

            const choiceIdInput = document.createElement('input');
            choiceIdInput.type = 'hidden';
            choiceIdInput.name = `choice_id_${id}[]`;
            choiceIdInput.value = choiceId;

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

            input.oninput = function() {
                updateCorrectChoiceDropdown(id);
            };

            choiceContainer.appendChild(input);
            choiceContainer.appendChild(choiceIdInput);
            choiceContainer.appendChild(removeButton);
            choicesDiv.insertBefore(choiceContainer, choicesDiv.lastElementChild);

            
            updateCorrectChoiceDropdown(id);
            isFormDirty = true;
            console.log('addChoice:', choiceContainer); 
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
            console.log('updateCorrectChoiceDropdown:', correctChoiceDropdown); 
        }

        
        document.addEventListener('DOMContentLoaded', function() {
            const assessmentId = "<?php echo htmlspecialchars($_GET['assessment_id']); ?>";
            fetch(`get_questions.php?assessment_id=${assessmentId}`)
                .then(response => response.json())
                .then(data => {
                    console.log('Fetched questions:', data); 
                    data.forEach(question => {
                        addQuestion();
                        document.getElementById(`question_id_${questionCount}`).value = question.question_id;
                        document.getElementById(`display_question_id_${questionCount}`).textContent = question.question_id;
                        document.getElementById(`question_text_${questionCount}`).value = question.question_text;
                        document.getElementById(`question_type_${questionCount}`).value = question.question_type;
                        document.getElementById(`answer_type_${questionCount}`).value = question.answer_type;
                        showAnswerOptions(questionCount, false); 
                        if (question.answer_type === 'multiple choice') {
                            
                            console.log('Fetched choices for question:', question.question_id, question.choices); 
                            question.choices.forEach(choice => {
                                addChoice(questionCount, choice.choice_id, choice.choice_text);
                            });
                            updateCorrectChoiceDropdown(questionCount); 
                            document.getElementById(`correct_choice_${questionCount}`).value = question.correct_answer;
                        } else if (question.answer_type === 'code') {
                            
                            document.getElementById(`code_${questionCount}`).value = question.code_template;
                            document.getElementById(`code_language_${questionCount}`).value = question.programming_language;
                            document.getElementById(`correct_code_${questionCount}`).value = question.correct_answer;
                        } else if (question.answer_type === 'true/false') {
                            document.getElementById(`true_false_${questionCount}`).value = question.correct_answer;
                        } else if (question.answer_type === 'fill in the blank') {
                            document.getElementById(`blank_${questionCount}`).value = question.correct_answer;
                        } else if (question.answer_type === 'essay') {
                            document.getElementById(`essay_${questionCount}`).value = question.correct_answer;
                        }
                    });
                });
        });


        
        function viewDeletedQuestions() {
            const assessmentId = "<?php echo htmlspecialchars($_GET['assessment_id']); ?>";
            fetch(`get_deleted_questions.php?assessment_id=${assessmentId}`)
                .then(response => response.json())
                .then(data => {
                    const deletedQuestionsTableBody = document.getElementById('deletedQuestionsTableBody');
                    const popup = document.getElementById('deleted-questions-popup');

                    
                    deletedQuestionsTableBody.innerHTML = '';

                    
                    if (data.length > 0) {
                        deletedQuestionsTableBody.innerHTML = data.map(question => `
                            <tr>
                                <td><input type="checkbox" class="selectDeletedQuestion" name="restore_questions[]" value="${question.question_id}"></td>
                                <td>${question.question_id}</td>
                                <td class="editable">${question.question_text}</td>
                                <td>${question.question_type || 'N/A'}</td>
                                <td>${question.answer_type || 'N/A'}</td>
                                <td>
                                    ${question.answer_type === 'code'
                                        ? `Code Template: ${question.code_template}<br>
                                        Language: ${question.programming_language}<br>
                                        Answers: ${question.correct_answer}`
                                        : (question.correct_answer || 'N/A')
                                    }
                                </td>
                                <td>
                                    ${question.answer_type === 'multiple choice'
                                        ? (question.choices.length > 0
                                            ? question.choices.map(choice => `<div>${choice}</div>`).join('')
                                            : 'No choices available')
                                        : question.answer_type === 'code'
                                            ? `<div>Code Template with ${(question.code_template.match(/__BLANK__/g) || []).length} blank(s)</div>`
                                            : 'N/A'
                                    }
                                </td>
                            </tr>
                        `).join('');
                    } else {
                        deletedQuestionsTableBody.innerHTML = '<tr><td colspan="7">No deleted questions found</td></tr>';
                    }

                    
                    popup.style.display = 'block';

                    
                    popup.addEventListener('change', function (event) {
                        if (event.target.id === 'select-all-deleted') {
                            const checkboxes = document.querySelectorAll('input[name="restore_questions[]"]');
                            checkboxes.forEach(checkbox => checkbox.checked = event.target.checked);
                        }
                    });

                    
                    popup.addEventListener('input', function (event) {
                        if (event.target.id === 'searchDeletedQuestions') {
                            const filter = event.target.value.toLowerCase();
                            const rows = document.querySelectorAll('#deletedQuestionsTableBody tr');
                            let matchFound = false;

                            rows.forEach(row => {
                                const cells = row.querySelectorAll('td');
                                const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(filter));
                                row.style.display = match ? '' : 'none';
                                if (match) matchFound = true;
                            });

                            const noMatchesPopup = document.getElementById('deletedNoMatchesPopup');
                            if (!matchFound) {
                                noMatchesPopup.style.display = 'block';
                                noMatchesPopup.style.opacity = '1';
                            } else {
                                noMatchesPopup.style.display = 'none';
                            }

                            document.getElementById('deletedClearSearch').style.display = filter ? 'block' : 'none';
                        }
                    });

                    
                    popup.addEventListener('click', function (event) {
                        if (event.target.id === 'deletedClearSearch') {
                            document.getElementById('searchDeletedQuestions').value = '';
                            const rows = document.querySelectorAll('#deletedQuestionsTableBody tr');
                            rows.forEach(row => row.style.display = '');
                            event.target.style.display = 'none';
                            document.getElementById('deletedNoMatchesPopup').style.display = 'none';
                        }
                    });

                    
                    popup.querySelectorAll('th[data-column]').forEach(th => {
                        th.addEventListener('click', function () {
                            const column = this.getAttribute('data-column');
                            const order = this.dataset.order = -(this.dataset.order || -1);
                            const rows = Array.from(document.querySelectorAll('#deletedQuestionsTableBody tr'));

                            rows.sort((a, b) => {
                                const aText = a.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                                const bText = b.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                                return aText.localeCompare(bText, undefined, { numeric: true }) * order;
                            });

                            rows.forEach(row => deletedQuestionsTableBody.appendChild(row));

                            
                            popup.querySelectorAll('th[data-column]').forEach(th => th.classList.remove('asc', 'desc'));
                            this.classList.add(order === 1 ? 'asc' : 'desc');
                        });
                    });

                    
                    let lastDeletedChecked = null;
                    popup.addEventListener('click', function (event) {
                        if (event.target.classList.contains('selectDeletedQuestion')) {
                            if (event.shiftKey && lastDeletedChecked) {
                                const checkboxes = Array.from(document.querySelectorAll('.selectDeletedQuestion'));
                                const start = checkboxes.indexOf(event.target);
                                const end = checkboxes.indexOf(lastDeletedChecked);

                                checkboxes.slice(Math.min(start, end), Math.max(start, end) + 1)
                                    .forEach(checkbox => checkbox.checked = lastDeletedChecked.checked);
                            }
                            lastDeletedChecked = event.target;
                        }
                    });
                })
                .catch(error => console.error('Error fetching deleted questions:', error));
        }

        function closeDeletedQuestions() {
            document.getElementById('deleted-questions-popup').style.display = 'none';
        }

        function restoreSelectedQuestions() {
            const selected = document.querySelectorAll('input[name="restore_questions[]"]:checked');
            if (selected.length === 0) {
                alert('Please select at least one question to restore.');
                return;
            }

            if (confirm('Are you sure you want to restore the selected questions?')) {
                const form = document.getElementById('restore-form');
                const formData = new FormData(form);

                fetch('restore_questions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Selected questions restored successfully.');
                        location.reload(); 
                    } else {
                        alert('Failed to restore selected questions.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while restoring the questions.');
                });
            }
        }
    </script>
        <style>
            li {
            color: white;
            }
           
            body {
                font-family: Arial, sans-serif;
                color: var(--text-color);
                background-color: var(--background-color);
            }

            main {
                padding: 20px;
            }

           
            .header-controls {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
                border-bottom: 1px solid var(--text-color);
            }

            .header-controls p {
                margin: 0;
            }

            .header-controls button {
                margin-left: 20px;
            }

            .action-controls {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 10px;
            }

            .deleted-search-container {
                position: relative;
                flex-grow: 1;
                display: flex;
                justify-content: flex-end;
            }

            .search-field-container {
                position: relative;
            }

            #searchDeletedQuestions {
                padding-right: 40px;
                padding: 10px 10px 10px 40px;
                border: 1px solid var(--border-color);
                border-radius: 5px;
                background: url('images/search_icon.png') no-repeat 10px center;
                background-size: 20px;
                transition: border-color 0.3s ease;
                color: var(--text-color);
                background-color: var(--secondary-color);
            }

            #searchDeletedQuestions:hover {
                border-color: var(--primary-color);
            }

            #deletedClearSearch {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                cursor: pointer;
                display: none;
            }

            #deletedNoMatchesPopup {
                display: none;
                position: absolute;
                top: calc(100% + 10px);
                left: 0;
                background: var(--popup-background-color);
                color: var(--text-color);
                padding: 10px;
                border: 1px solid var(--popup-border-color);
                border-radius: 5px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                transition: opacity 0.3s ease;
                z-index: 1000;
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


           
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            th, td {
                text-align: left;
                padding: 12px;
                border-bottom: 1px solid var(--border-color);
            }

            th {
                background-color: var(--secondary-color);
                cursor: pointer;
                position: relative;
                transition: background-color 0.3s ease;
                padding-right: 20px;
            }

            th[data-column]:hover {
                background-color: var(--hover-background-color);
                color: var(--hover-text-color);
            }

            tr:hover {
                background-color: var(--hover-background-color);
                color: var(--hover-text-color);
            }

           
            th[data-column]::after {
                content: '';
                position: absolute;
                right: 8px;
                top: 50%;
                transform: translateY(-50%);
                border: 5px solid transparent;
                display: none;
            }

            th[data-column].asc::after {
                display: inline-block;
                border-bottom-color: var(--text-color);
            }

            th[data-column].desc::after {
                display: inline-block;
                border-top-color: var(--text-color);
            }

            th[data-column]:hover.asc::after {
                border-bottom-color: transparent;
                border-top-color: var(--hover-text-color);
            }

            th[data-column]:hover.desc::after {
                border-top-color: transparent;
                border-bottom-color: var(--hover-text-color);
            }

           
            #deleted-questions-popup {
                display: none;
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: var(--background-color);
                padding: 20px;
                border: 1px solid var(--border-color);
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                max-height: 80vh;
                overflow-y: auto;
                z-index: 1000;
                width: 90%;
                transition: opacity 0.3s ease;
            }

            #deleted-questions-popup.show {
                display: block;
                opacity: 1;
            }

            .assessment-close-button {
                position: absolute;
                top: 10px;
                right: 10px;
                background: none;
                border: none;
                color: var(--text-color);
                font-size: 24px;
                cursor: pointer;
                transition: color 0.3s ease, transform 0.3s ease;
            }

            .assessment-close-button:hover {
                color: var(--accent-color);
                transform: scale(1.1);
                background: none;
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

            @media (max-width: 768px) {
                #deleted-questions-popup {
                width: 95%;
                height: 90vh;
                padding: 15px;
                overflow-y: auto;
                display: none;
                flex-direction: column;
            }

            #deleted-questions-popup .header-controls {
                margin-bottom: 20px;
            }

            #deleted-questions-popup .action-controls {
                display: flex;
                flex-direction: column;
                gap: 15px;
            }

            #deleted-questions-popup .deleted-search-container {
                width: 100%;
                margin-left: 0;
            }

            #restoreSelectedButton {
                width: 100%;
                margin: 0;
            }

            #deleted-questions-popup table {
                width: 100%;
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            #deleted-questions-popup th,
            #deleted-questions-popup td {
                font-size: 14px;
                padding: 8px;
                text-align: left;
                vertical-align: top;
            }

            #deleted-questions-popup .assessment-close-button {
                top: 5px;
                right: 5px;
                font-size: 20px;
            }

            #deletedSearchInput {
                width: 100%;
                margin: 0;
            }

            #deleted-questions-popup form {
                margin-top: 15px;
            }

            #deleted-questions-popup {
                width: 98%;
                padding: 10px;
            }

            #deleted-questions-popup h3 {
                font-size: 18px;
            }

            #deleted-questions-popup th,
            #deleted-questions-popup td {
                font-size: 12px;
                padding: 6px;
            }

            .deleted-search-container {
                justify-content: center;
                align-items: center;
            }

            .search-field-container {
                width: 100%;
                position: relative;
            }

            .action-controls .success{
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
        <h1>Edit Questions for Assessment</h1>
        <div class="header-controls">
            <p>Assessment ID: <strong><?php echo htmlspecialchars($_GET['assessment_id']); ?></strong></p>
            <button type="button" onclick="viewDeletedQuestions()">View Deleted Questions</button>
        </div>
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
        <div id="deleted-questions-popup">
            <div class="header-controls">
                <h3>Deleted Questions</h3>
            </div>
            <div class="action-controls">
                <button type="button" class="success" onclick="restoreSelectedQuestions()">Restore Selected Questions</button>
                <div class="deleted-search-container">
                    <div class="search-field-container">
                        <input type="text" id="searchDeletedQuestions" placeholder="Search...">
                        <span id="deletedClearSearch">&#x2715;</span>
                        <div id="deletedNoMatchesPopup">No matches found.</div>
                    </div>
                </div>
            </div>
            <form id="restore-form">
                <table>
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="select-all-deleted"></th>
                            <th data-column="question_id">Question ID</th>
                            <th data-column="question_text">Question Text</th>
                            <th data-column="question_type">Question Type</th>
                            <th data-column="answer_type">Answer Type</th>
                            <th data-column="correct_answer">Correct Answer</th>
                            <th data-column="choices">Choices</th>
                        </tr>
                    </thead>
                    <tbody id="deletedQuestionsTableBody">
                    </tbody>
                </table>
            </form>
            <button type="button" class="assessment-close-button" onclick="closeDeletedQuestions()">&#x2715;</button>
        </div>
        <form id="questions-form" action="update_questions.php" method="post">
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