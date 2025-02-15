<?php
session_start();
ob_start();

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'techfit';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); 
}

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

$user_id = $_SESSION['user_id'];
$admin_query = "SELECT admin_id FROM Admin WHERE user_id = ?";
$stmt = $conn->prepare($admin_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin) {
    die("Error: Admin not found for the current user."); 
}

$admin_id = $admin['admin_id'];

function generateFeedbackManagementId($conn) {
    $prefix = 'FM';
    $length = 3;

    $last_id_query = "SELECT feedback_management_id FROM Feedback_Management ORDER BY feedback_management_id DESC LIMIT 1";
    $last_id_result = $conn->query($last_id_query);
    $next_number = 1;

    if ($last_id_result && $last_id_result->num_rows > 0) {
        $last_id_row = $last_id_result->fetch_assoc();
        $last_id = $last_id_row['feedback_management_id'];
        $numeric_part = intval(substr($last_id, strlen($prefix)));
        $next_number = $numeric_part + 1;
    }

    $numeric_string = sprintf('%0' . $length . 'd', $next_number);
    $new_id = $prefix . $numeric_string;

    return $new_id;
}

$sql_pending = "SELECT f.feedback_id, f.text, f.timestamp, u.username AS user_name
                FROM Feedback f
                JOIN User u ON f.user_id = u.user_id
                WHERE f.feedback_id NOT IN
                (SELECT feedback_id FROM Feedback_Management WHERE action_type IN ('resolved', 'responded', 'reviewed'))";

$result_pending = $conn->query($sql_pending);
if (!$result_pending) {
    echo "<script>alert('Database query failed: " . $conn->error . "');</script>"; 
}

$sql_responded = "SELECT f.feedback_id, f.text, f.timestamp, fm.response_text, u.username AS user_name, fm.timestamp as management_timestamp, fm.feedback_management_id
                    FROM Feedback f
                    JOIN Feedback_Management fm ON f.feedback_id = fm.feedback_id
                    JOIN User u ON f.user_id = u.user_id
                    WHERE fm.action_type = 'responded'";

$result_responded = $conn->query($sql_responded);
if (!$result_responded) {
    echo "<script>alert('Database query failed: " . $conn->error . "');</script>"; 
}

$sql_resolved = "SELECT f.feedback_id, f.text, f.timestamp, fm.response_text, u.username AS user_name, fm.timestamp as management_timestamp, fm.feedback_management_id
                    FROM Feedback f
                    JOIN Feedback_Management fm ON f.feedback_id = fm.feedback_id
                    JOIN User u ON f.user_id = u.user_id
                    WHERE fm.action_type = 'resolved'";

$result_resolved = $conn->query($sql_resolved);
if (!$result_resolved) {
    echo "<script>alert('Database query failed: " . $conn->error . "');</script>"; 
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['respond'])) {
        if (!empty($_POST['respond_feedback_ids']) && !empty(trim($_POST['response_text']))) { 
            $response_text = trim($_POST['response_text']);
            $feedbackIds = $_POST['respond_feedback_ids']; 

            $responded_feedback_count = 0; 
            foreach ($feedbackIds as $feedbackId) {
                $action_type = 'responded';
                $timestamp = date("Y-m-d H:i:s");
                $feedback_management_id = generateFeedbackManagementId($conn);

                $stmt = $conn->prepare("INSERT INTO Feedback_Management (feedback_management_id, feedback_id, admin_id, action_type, timestamp, response_text)
                                                            VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $feedback_management_id, $feedbackId, $admin_id, $action_type, $timestamp, $response_text);

                if ($stmt->execute()) {
                    $responded_feedback_count++;
                } else {
                    echo "<script>alert('Error inserting response for Feedback ID " . $feedbackId . ": " . $conn->error . "');</script>"; 
                }
            }
            echo "<script>
                    alert('" . $responded_feedback_count . " feedback(s) successfully responded to.');
                    window.location.href = 'user_feedback.php'; 
                  </script>";
            exit();


        } else {
            echo "<script>alert('Please select feedback and enter a response.');</script>";
        }
    } elseif (isset($_POST['resolve'])) {
        if (!empty($_POST['resolve_feedback_ids'])) {
            $feedbackIds = $_POST['resolve_feedback_ids']; 
            $resolved_feedback_count = 0; 
            foreach ($feedbackIds as $feedbackId) {
                $action_type = 'resolved';
                $timestamp = date("Y-m-d H:i:s");
                $feedback_management_id = generateFeedbackManagementId($conn);

                $stmt = $conn->prepare("INSERT INTO Feedback_Management (feedback_management_id, feedback_id, admin_id, action_type, timestamp)
                                                            VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $feedback_management_id, $feedbackId, $admin_id, $action_type, $timestamp);

                if ($stmt->execute()) {
                    $resolved_feedback_count++;
                } else {
                    echo "<script>alert('Error resolving Feedback ID " . $feedbackId . ": " . $conn->error . "');</script>"; 
                }
            }
            echo "<script>
                    alert('" . $resolved_feedback_count . " feedback(s) successfully resolved.');
                    window.location.href = 'user_feedback.php'; 
                  </script>";
            exit();
        } else {
            echo "<script>alert('Please select feedback to resolve.');</script>";
        }
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Feedback - TechFit</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="admin.css">
</head>

    <style>
        li {
                color: white;
            }
    </style>

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

    <section class="feedback-container">
        <div class="main-content">
            <h2 style="text-align: center;">Manage Feedback</h2>
            <div class="feedback-category">
            <h3>Pending Feedback</h3>
            <form method="POST" action="user_feedback.php" id="feedbackForm">
                <div class="feedback-action-bar">
                    <div class="feedback-list">
                        <?php while ($row = $result_pending->fetch_assoc()): ?>
                            <div class="feedback-item feedback-selectable" data-feedback-id="<?= $row['feedback_id'] ?>">
                                <div class="feedback-content">
                                    <p><strong>User:</strong> <?= $row['user_name'] ?></p>
                                    <p><strong>Feedback ID:</strong> <?= $row['feedback_id'] ?></p>
                                    <p><strong>Text:</strong> <?= $row['text'] ?></p>
                                    <p><strong>Timestamp:</strong> <?= $row['timestamp'] ?></p>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php if ($result_pending->num_rows === 0): ?>
                                <p>No pending feedback.</p>
                                <?php endif; ?>
                                <div class="feedback-category">
                                    <textarea name="response_text" rows="3" placeholder="Type your response here (for 'Respond')..."></textarea>
                                    <div style="display:none;" id="selected_feedback_ids_container">
                                        </div>
                                <button type="submit" name="respond" class="action-button respond-button">Respond to Selected</button>
                                <button type="submit" name="resolve" class="action-button resolve-button">Resolve Selected</button>
                                </div>
                                
                    </div>
                </form>
            </div>

            <div class="feedback-category">
               <h3>Responded Feedback</h3>
               <div class="feedback-list handled-feedback">
                <?php while ($row = $result_responded->fetch_assoc()): ?>
                   <div class="feedback-item handled">
                    <div class="feedback-content">
                        <p><strong>User:</strong> <?= $row['user_name'] ?></p>
                        <p><strong>Feedback ID:</strong> <?= $row['feedback_id'] ?></p>
                        <p><strong>Management ID:</strong> <?= $row['feedback_management_id'] ?></p>
                        <p><strong>Text:</strong> <?= $row['text'] ?></p>
                        <p><strong>Timestamp:</strong> <?= $row['timestamp'] ?></p>
                        <p><strong>Response:</strong> <?= $row['response_text'] ?></p>
                        <p><strong>Handled Timestamp:</strong> <?= $row['management_timestamp'] ?></p>
                        <p><strong>Action:</strong> Responded</p>
                    </div>
                   </div>
                <?php endwhile; ?>
                <?php if ($result_responded->num_rows === 0): ?>
                   <p>No responded feedback.</p>
                <?php endif; ?>
               </div>
            </div>

            <div class="feedback-category">
               <h3>Resolved Feedback</h3>
               <div class="feedback-list handled-feedback">
                <?php while ($row = $result_resolved->fetch_assoc()): ?>
                   <div class="feedback-item handled">
                    <div class="feedback-content">
                        <p><strong>User:</strong> <?= $row['user_name'] ?></p>
                        <p><strong>Feedback ID:</strong> <?= $row['feedback_id'] ?></p>
                        <p><strong>Management ID:</strong> <?= $row['feedback_management_id'] ?></p>
                        <p><strong>Text:</strong> <?= $row['text'] ?></p>
                        <p><strong>Timestamp:</strong> <?= $row['timestamp'] ?></p>
                        <p><strong>Handled Timestamp:</strong> <?= $row['management_timestamp'] ?></p>
                        <p><strong>Action:</strong> Resolved</p>
                    </div>
                   </div>
                <?php endwhile; ?>
                <?php if ($result_resolved->num_rows === 0): ?>
                   <p>No resolved feedback.</p>
                <?php endif; ?>
               </div>
            </div>

        </div>
    </section>

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
                        <li><a href="user_engagement.php">User Engagement Statistics</a></li>
                        <li><a href="feedback_analysis.php">Feedback Analysis</a></li>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const feedbackItems = document.querySelectorAll('.feedback-item.feedback-selectable');
        const selectedFeedbackIdsContainer = document.getElementById('selected_feedback_ids_container');
        let selectedIdsForRespond = []; 
        let selectedIdsForResolve = [];

        feedbackItems.forEach(item => {
            item.addEventListener('click', function() {
                this.classList.toggle('selected');
                const feedbackId = this.dataset.feedbackId;

                if (this.classList.contains('selected')) {
                    selectedIdsForRespond.push(feedbackId); 
                    selectedIdsForResolve.push(feedbackId); 
                } else {
                    selectedIdsForRespond = selectedIdsForRespond.filter(id => id !== feedbackId); 
                    selectedIdsForResolve = selectedIdsForResolve.filter(id => id !== feedbackId); 
                }
                updateHiddenInputs(); 
            });
        });

        function updateHiddenInputs() {
            selectedFeedbackIdsContainer.innerHTML = ''; 

            selectedIdsForRespond.forEach(id => {
                let inputRespond = document.createElement('input');
                inputRespond.type = 'hidden';
                inputRespond.name = 'respond_feedback_ids[]';
                inputRespond.value = id;
                selectedFeedbackIdsContainer.appendChild(inputRespond);
            });
            selectedIdsForResolve.forEach(id => {
                let inputResolve = document.createElement('input');
                inputResolve.type = 'hidden';
                inputResolve.name = 'resolve_feedback_ids[]';
                inputResolve.value = id;
                selectedFeedbackIdsContainer.appendChild(inputResolve);
            });
        }


        document.querySelector('form').addEventListener('submit', function(event) {
            if (event.submitter && event.submitter.name === 'respond') {
                if (selectedIdsForRespond.length === 0) {
                    alert('Please select at least one feedback to respond.');
                    event.preventDefault();
                } else if (!document.querySelector('textarea[name="response_text"]').value.trim()) {
                    alert('Please enter a response message.');
                    event.preventDefault();
                }
            } else if (event.submitter && event.submitter.name === 'resolve') {
                if (selectedIdsForResolve.length === 0) {
                    alert('Please select at least one feedback to resolve.');
                    event.preventDefault();
                }
            }
        });
    });
    </script>
    <script src="scripts.js"></script>
</body>
</html>