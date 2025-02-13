<?php
function getQuestionDetails($conn, $questionId) {
    $sql = "SELECT q.code_template, q.correct_answer, q.programming_language 
            FROM Question q
            WHERE q.question_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return [
        'template' => $row['code_template'] ?? '',
        'correct_answer' => $row['correct_answer'] ?? '',
        'language' => $row['programming_language'] ?? ''
    ];
}

function checkCodeAnswer($conn, $questionId, $userAnswers) {
    $questionDetails = getQuestionDetails($conn, $questionId);
    
    if (empty($questionDetails['correct_answer'])) {
        return false;
    }
    
    $userParts = explode('<<ANSWER_BREAK>>', $userAnswers);
    $correctParts = explode('<<ANSWER_BREAK>>', $questionDetails['correct_answer']); 
    
    // Check if number of answers matches
    if (count($userParts) !== count($correctParts)) {
        return false;
    }
    
    // Compare each answer
    for ($i = 0; $i < count($userParts); $i++) {
        if (trim($userParts[$i]) !== trim($correctParts[$i])) {
            return false;
        }
    }
    
    return true;
}

function getCodeTemplate($conn, $questionId) {
    $sql = "SELECT code_template 
            FROM Question 
            WHERE question_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $questionId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['code_template'] ?? '';
}
?>