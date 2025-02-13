<?php
session_start();

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'techfit';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die('Connection to techfit database failed: ' . $conn->connect_error);
}

$job_seeker_id = $_SESSION['job_seeker_id'];

// Get assessment settings
$settings_sql = "SELECT passing_score_percentage FROM Assessment_Settings WHERE setting_id = '1'";
$settings_result = $conn->query($settings_sql);
$settings = $settings_result->fetch_assoc();
$passing_score = $settings['passing_score_percentage'];

// Get all answers with questions
$sql = "SELECT a.*, q.question_text, q.answer_type, q.correct_answer 
        FROM Answer a
        JOIN Question q ON a.question_id = q.question_id
        WHERE a.job_seeker_id = ?
        ORDER BY q.question_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $job_seeker_id);
$stmt->execute();
$result = $stmt->get_result();

$total_questions = 0;
$correct_answers = 0;
$answers = [];

while ($row = $result->fetch_assoc()) {
    $total_questions++;
    if ($row['is_correct']) {
        $correct_answers++;
    }
    $answers[] = $row;
}

$score_percentage = ($total_questions > 0) ? ($correct_answers / $total_questions) * 100 : 0;
$passed = $score_percentage >= $passing_score;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assessment Results - TechFit</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="results-container">
        <h1>Assessment Results</h1>
        <div class="score-summary">
            <h2>Final Score: <?php echo number_format($score_percentage, 1); ?>%</h2>
            <p class="result-status <?php echo $passed ? 'passed' : 'failed'; ?>">
                <?php echo $passed ? 'PASSED' : 'FAILED'; ?>
            </p>
            <p>Passing Score: <?php echo $passing_score; ?>%</p>
        </div>
        
        <div class="answers-review">
            <h3>Review Your Answers</h3>
            <?php foreach ($answers as $answer): ?>
                <div class="question-answer">
                    <p class="question"><?php echo htmlspecialchars($answer['question_text']); ?></p>
                    <p class="answer">Your Answer: <?php echo htmlspecialchars($answer['answer_text']); ?></p>
                    <?php if ($answer['is_correct'] !== null): ?>
                        <p class="status <?php echo $answer['is_correct'] ? 'correct' : 'incorrect'; ?>">
                            <?php echo $answer['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>