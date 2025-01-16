<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

$mysqli = new mysqli($servername, $username, $password, $dbname);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Function to Generate Custom Resource IDs
function generateResourceId($mysqli) {
    // Fetch the last ID
    $result = $mysqli->query("SELECT resource_id FROM resource ORDER BY resource_id DESC LIMIT 1");
    $lastId = $result->fetch_assoc()['resource_id'];

    // Determine the numeric part and increment it
    $prefix = "R";
    $newId = 1; // Default for the first entry
    if ($lastId) {
        $numericPart = intval(substr($lastId, strlen($prefix)));
        $newId = $numericPart + 1;
    }

    // Return the new ID
    return $prefix . str_pad($newId, 2, "0", STR_PAD_LEFT);
}

// Handle Add/Edit/Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        if ($action === 'add') {
            $question = trim($_POST['question']);
            $answer = trim($_POST['answer']);
            $category = trim($_POST['category']);

            if ($question && $answer && $category) {
                $resourceId = generateResourceId($mysqli);
                $stmt = $mysqli->prepare("INSERT INTO resource (resource_id, type, question, answer, category) VALUES (?, 'faq', ?, ?, ?)");
                $stmt->bind_param("ssss", $resourceId, $question, $answer, $category);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'message' => 'FAQ added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'];
            $question = trim($_POST['question']);
            $answer = trim($_POST['answer']);
            $category = trim($_POST['category']);

            if ($id && $question && $answer && $category) {
                $stmt = $mysqli->prepare("UPDATE resource SET question = ?, answer = ?, category = ? WHERE resource_id = ?");
                $stmt->bind_param("ssss", $question, $answer, $category, $id);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'message' => 'FAQ updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            if ($id) {
                $stmt = $mysqli->prepare("DELETE FROM resource WHERE resource_id = ?");
                $stmt->bind_param("s", $id);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'message' => 'FAQ deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid FAQ ID.']);
            }
        }
        exit;
    }
}

// Fetch FAQs for Display
$result = $mysqli->query("SELECT * FROM resource WHERE type = 'faq' ORDER BY category, resource_id");
$faqs = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - FAQ Management</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to your CSS -->
</head>
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
                                    // Check if the user is logged in and display their username
                                    if (isset($_SESSION['username'])) {
                                        echo $_SESSION['username'];  // Display the username from session
                                    } else {
                                        echo "Guest";  // Default if not logged in
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
<body>
    <h1 style="text-align: center; padding-top: 60px;">Manage FAQs</h1>
    <form id="faqForm">
        <input type="hidden" name="action" value="add">
        <label>Question:</label><br>
        <textarea name="question" required></textarea><br>
        <label>Answer:</label><br>
        <textarea name="answer" required></textarea><br>
        <label>Category:</label><br>
        <select name="category" required>
            <option value="" disabled selected>Select Category</option>
            <option value="jobSeeker">Job Seeker</option>
            <option value="employer">Employer</option>
        </select><br><br>
        <button type="button" onclick="submitFAQ()">Add FAQ</button>
    </form>
    <h2 style="text-align: center;">Existing FAQs</h2>
    <div id="faq">
        <?php foreach (['jobSeeker', 'employer'] as $category): ?>
            <div class="faq-category">
                <h3>For <?= ucfirst($category) ?>s</h3>
                <?php 
                $categoryFaqs = array_filter($faqs, fn($faq) => $faq['category'] === $category);
                if ($categoryFaqs): 
                    foreach ($categoryFaqs as $faq): ?>
                        <div class="faq-item" data-id="<?= $faq['resource_id'] ?>">
                            <strong>Q:</strong> <?= htmlspecialchars($faq['question']) ?><br>
                            <strong>A:</strong> <?= htmlspecialchars($faq['answer']) ?><br>
                            <button onclick="editFAQ('<?= $faq['resource_id'] ?>')">Edit</button>
                            <button onclick="deleteFAQ('<?= $faq['resource_id'] ?>')">Delete</button>
                        </div>
                    <?php endforeach; 
                else: ?>
                    <p>No FAQs yet for this category.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div style="text-align: center; margin-top: 30px; padding-bottom: 30px;">
        <a href="faq.php" id="manage_faq_button" style="background-color: #4CAF50; padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px;">Back to FAQs</a>
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
                        <li><a href="terms.php">Terms & Condition</a></li>
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
        function submitFAQ() {
            const formData = new FormData(document.getElementById('faqForm'));
            fetch('manage_faq.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === 'success') location.reload();
                });
        }
        function editFAQ(id) {
            // Find the FAQ item using the data-id attribute
            const faqItem = document.querySelector(`.faq-item[data-id="${id}"]`);
            const question = faqItem.querySelector('strong:nth-of-type(1)').nextSibling.textContent.trim();
            const answer = faqItem.querySelector('strong:nth-of-type(2)').nextSibling.textContent.trim();
            const category = faqItem.closest('.faq-category').querySelector('h3').textContent.includes('Job Seeker') ? 'jobSeeker' : 'employer';

            // Populate the form with the FAQ details
            const form = document.getElementById('faqForm');
            form.querySelector('[name="action"]').value = 'edit'; // Change form action to 'edit'
            form.querySelector('[name="question"]').value = question;
            form.querySelector('[name="answer"]').value = answer;
            form.querySelector('[name="category"]').value = category;

            // Add a hidden field for the ID
            let idField = form.querySelector('[name="id"]');
            if (!idField) {
                idField = document.createElement('input');
                idField.type = 'hidden';
                idField.name = 'id';
                form.appendChild(idField);
            }
            idField.value = id;

            // Change the button to "Save Changes"
            const submitButton = form.querySelector('button[type="button"]');
            submitButton.textContent = 'Save Changes';
            submitButton.onclick = saveEditedFAQ;
        }
        function deleteFAQ(id) {
            if (confirm('Are you sure you want to delete this FAQ?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                fetch('manage_faq.php', { method: 'POST', body: formData })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.status === 'success') location.reload();
                    });
            }
        }
    </script>
</body>
</html>