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


if ($_SESSION['role'] !== 'Employer') {
    displayLoginMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidate Answer - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="employer.css">
</head>
<body>
<header>
    <div class="logo">
        <a href="index.php"><img src="images/logo.jpg" alt="TechFit Logo"></a>
    </div>
    <nav>
        <div class="nav-container">
            <ul class="nav-list">
                <li><a href="#">Candidates</a>
                    <ul class="dropdown">
                        <li><a href="search_candidate.php">Search Candidates</a></li>
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

<div class="container">
    <div class="main-content middle-section">
        <a href="search_candidate.php" class="back-arrow">
            &#8592;
        </a>

        <div class="row-1 assessment-area" style="color:white; font-size: 1.2em;">
            <div class="assessment-title">Candidate Profile</div>
        </div>

        <div class="row-2 candidate-score-row">
            <?php
            
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "techfit";

            $conn = new mysqli($servername, $username, $password, $dbname);

            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            $job_seeker_id = $_GET['job_seeker_id'];

            $sql_candidate = "SELECT u.first_name, u.last_name, js.education_level, js.year_of_experience, js.linkedin_link
                                         FROM User u
                                         JOIN Job_Seeker js ON u.user_id = js.user_id
                                         WHERE js.job_seeker_id = '$job_seeker_id'";
            $result_candidate = $conn->query($sql_candidate);

            if ($result_candidate->num_rows > 0) {
                $row_candidate = $result_candidate->fetch_assoc();
                echo "<div class='candidate-info'>";
                echo "<img src='images/usericon.png' alt='User Icon'>";
                echo "<div class='name'>" . $row_candidate['first_name'] . " " . $row_candidate['last_name'] . "</div>";
                echo "<div class='details'>";
                
                if (!empty($row_candidate['linkedin_link'])) {
                    echo "<a href='" . htmlspecialchars($row_candidate['linkedin_link']) . "' target='_blank'>LinkedIn Profile</a><br>";
                } else {
                    echo "LinkedIn Profile: N/A<br>"; 
                }
                
                $education_level = !empty($row_candidate['education_level']) ? htmlspecialchars($row_candidate['education_level']) : 'N/A';
                echo "<div class='education'>Education Level: " . $education_level . "</div>";
                
                $experience = (!empty($row_candidate['year_of_experience']) || $row_candidate['year_of_experience'] === '0') ? htmlspecialchars($row_candidate['year_of_experience']) . " Years" : 'N/A';
                echo "<div class='experience'>Years of Experience: " . $experience . "</div>";
                echo "</div>";
                echo "</div>";
            } else {
                echo "<h1>Candidate not found</h1>";
            }


            $score = "N/A";
            $time_used = "N/A";

            $sql_score_time = "SELECT score, TIMEDIFF(end_time, start_time) AS time_used
                                            FROM Assessment_Job_Seeker
                                            WHERE job_seeker_id = '$job_seeker_id'";
            $result_score_time = $conn->query($sql_score_time);

            if ($result_score_time->num_rows > 0) {
                $row_score_time = $result_score_time->fetch_assoc();
                $score = !is_null($row_score_time['score']) ? $row_score_time['score'] : 'N/A'; 
                $time_used_value = $row_score_time['time_used'];
                $time_used = !empty($time_used_value) ? $time_used_value : 'N/A'; 
            }
        


            echo "<div class='score-time'>";
            echo "<div class='score'>Score: " . $score . "/100</div>";
            echo "<div class='divider'></div>";
            echo "<div class='time-used'>Time Used: " . $time_used . "</div>";
            echo "<div class='divider'></div>";
            echo "</div>";


            $conn->close();
            ?>
        </div>

        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "techfit";

        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Connection failed: ". $conn->connect_error);
        }

        $job_seeker_id = isset($_GET['job_seeker_id'])? $_GET['job_seeker_id']: null;

        if ($job_seeker_id === null) {
            echo "<div>Job Seeker ID is missing.</div>";
            exit;
        }

        $sql_questions = "SELECT q.question_id, q.question_text, q.correct_answer, q.answer_type,
                           a.answer_text, a.is_correct,
                           c.choice_text AS job_seeker_choice_text
                    FROM question q
                    INNER JOIN answer a ON q.question_id = a.question_id
                    LEFT JOIN choices c ON a.answer_text = c.choice_id
                    WHERE a.job_seeker_id = '$job_seeker_id'";

        $result_questions = $conn->query($sql_questions);

        if ($result_questions) {
            if ($result_questions->num_rows > 0) {
                $question_counter = 1;
                while ($row_question = $result_questions->fetch_assoc()) {
                    echo "<div class='question'>";
                    echo "<div class='question-text'><strong>Question $question_counter:</strong> ". htmlspecialchars($row_question['question_text']). "</div>";

                    echo "<div class='job-seeker-answer'>";
                    echo "<strong>Job Seeker's Answer:</strong> <pre>";
                    if ($row_question['answer_text']!== null) {
                        $answer_to_display = '';
                        
                        if ($row_question['answer_type'] === 'multiple choice') {
                            
                            $answer_to_display = $row_question['job_seeker_choice_text'];
                            if (empty($answer_to_display)) { 
                                $answer_to_display = "Choice ID: " . htmlspecialchars($row_question['answer_text']) . " (Choice Text Not Found)";
                            }
                        } else {
                            
                            $answer_to_display = $row_question['answer_text'];
                        }
                        $answer_text_with_breaks = str_replace("<<ANSWER_BREAK>>", "\n", $answer_to_display);
                        echo htmlspecialchars($answer_text_with_breaks);
                    } else {
                        echo "No answer provided.";
                    }
                    echo "</pre>";
                    echo "</div>";

                    echo "<div class='correct-answer'>";
                    echo "<strong>Correct Answer:</strong> <pre>";
                    if ($row_question['correct_answer']!== null) {
                        $correct_answer_with_breaks = str_replace("<<ANSWER_BREAK>>", "\n", $row_question['correct_answer']);
                        echo htmlspecialchars($correct_answer_with_breaks);
                    } else {
                        echo "No correct answer provided.";
                    }
                    echo "</pre>";
                    echo "</div>";

                    echo "</div>";
                    $question_counter++;
                }
            } else {
                echo "<div>No questions found or no answers provided by the job seeker.</div>";
            }
        } else {
            echo "<div>Error executing query: ". $conn->error. "</div>";
        }

        $conn->close();?>
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
                    <p><a href="mailto:/a></p>techfit@gmail.com">techfit@gmail.com</a></p>
                </div>
            </div>
            <div class="footer-right">
                <div class="footer-column">
                    <h3>Candidate</h3>
                    <ul>
                        <li><a href="search_candidate.php">Search Candidates</a></li>
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
<script>
    function updateAssessment() {
        const assessmentSelect = document.getElementById('assessment-select');
        const selectedAssessmentId = assessmentSelect.value;
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('assessment_id', selectedAssessmentId);
        window.location.search = urlParams.toString();
    }
</script>
<script src="scripts.js"></script>
</body>
</html>
