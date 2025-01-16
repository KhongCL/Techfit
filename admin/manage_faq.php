<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";

$mysqli = new mysqli($servername, $username, $password, $dbname);
if ($mysqli->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
}

// Get the user_id from the session
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'User not logged in']));
}
$user_id = $_SESSION['user_id'];

// Fetch admin_id using user_id
$admin_id_query = $mysqli->prepare("SELECT admin_id FROM admin WHERE user_id = ?");
$admin_id_query->bind_param("s", $user_id);
$admin_id_query->execute();
$admin_id_query->bind_result($admin_id);
$admin_id_query->fetch();
$admin_id_query->close();

error_log('Request received: ' . print_r($_POST, true));
error_log('Request FILES: ' . print_r($_FILES, true));

if (empty($admin_id)) {
    die(json_encode(['status' => 'error', 'message' => 'Admin ID not found']));
}

// Fetch FAQs from the database
$faqs = [];
$result = $mysqli->query("SELECT resource_id, question, answer, category FROM resource WHERE type = 'faq'");
while ($row = $result->fetch_assoc()) {
    $faqs[] = $row;
}

$faqDescriptions = [];
$result = $mysqli->query("SELECT resource_id, description FROM admin_resource");
while ($row = $result->fetch_assoc()) {
    $faqDescriptions[$row['resource_id']] = $row['description'];
}

function generateResourceId($mysqli) {
    $prefix = "R";
    $new_id = 1;
    $resource_id = '';

    do {
        $resource_id = $prefix . str_pad($new_id, 2, "0", STR_PAD_LEFT);
        $result = $mysqli->query("SELECT resource_id FROM resource WHERE resource_id = '$resource_id'");
        $new_id++;
    } while ($result->num_rows > 0);

    return $resource_id;
}

function generateAdminResourceId($mysqli) {
    $prefix = "AR";
    $new_id = 1;
    $admin_resource_id = '';

    do {
        $admin_resource_id = $prefix . str_pad($new_id, 3, "0", STR_PAD_LEFT);
        $result = $mysqli->query("SELECT admin_resource_id FROM Admin_Resource WHERE admin_resource_id = '$admin_resource_id'");
        $new_id++;
    } while ($result->num_rows > 0);

    return $admin_resource_id;
}

// Function to log admin actions
function logAdminAction($mysqli, $admin_id, $resource_id, $action_type, $description) {
    $timestamp = date('Y-m-d H:i:s');
    $admin_resource_id = generateAdminResourceId($mysqli);
    $stmt = $mysqli->prepare(
        "INSERT INTO Admin_Resource (admin_resource_id, admin_id, resource_id, action_type, timestamp, description) 
        VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssss", $admin_resource_id, $admin_id, $resource_id, $action_type, $timestamp, $description);
    $stmt->execute();
    $stmt->close();
}

// Handle Add/Edit/Delete Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if (!$action) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }

    if ($action === 'delete') {
        $resource_id = $_POST['id'] ?? null;
        logAdminAction($mysqli, $admin_id, $resource_id, 'deleted', 'Sitemap deleted');
        
        // Validate the resource ID
        if (!$resource_id) {
            echo json_encode(['status' => 'error', 'message' => 'Resource ID is required for deletion']);
            exit;
        }

        // First, delete related rows in Admin_Resource
        $stmt = $mysqli->prepare("DELETE FROM Admin_Resource WHERE resource_id = ?");
        $stmt->bind_param("s", $resource_id);
        if (!$stmt->execute()) {
            error_log('Error deleting from Admin_Resource: ' . $stmt->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete related admin resources.']);
            exit;
        }
        $stmt->close();

        // Then, delete the row in Resource
        $stmt = $mysqli->prepare("DELETE FROM Resource WHERE resource_id = ?");
        $stmt->bind_param("s", $resource_id);
        if (!$stmt->execute()) {
            error_log('Error deleting from Resource: ' . $stmt->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete the resource.']);
            exit;
        }
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Sitemap deleted successfully']);
        exit;
    } elseif ($action === 'add') {
        // Validate for 'add' action
        $resource_id = $_POST['id'] ?? null;
        $description = trim($_POST['description'] ?? '');
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? '');

        if (!$question || !$answer || !$category) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required for adding a FAQ']);
            exit;
        }

        // Proceed with adding logic
        $admin_resource_id = generateAdminResourceId($mysqli);
        $resource_id = generateResourceId($mysqli);

        $stmt = $mysqli->prepare("INSERT INTO resource (resource_id, type, question, answer, category) VALUES (?, 'faq', ?, ?, ?)");
        $stmt->bind_param("ssss", $resource_id, $question, $answer, $category);
        $stmt->execute();
        $stmt->close();

        logAdminAction($mysqli, $admin_id, $resource_id, 'added', $description);

        echo json_encode(['status' => 'success', 'message' => 'FAQ added successfully']);
        exit;
    } elseif ($action === 'edit') {
        // Validate for 'edit' action
        $resource_id = $_POST['id'] ?? null;
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (!$resource_id || !$question || !$answer || !$category) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required for editing a FAQ']);
            exit;
        }

        // Proceed with editing logic
        $stmt = $mysqli->prepare("UPDATE resource SET question = ?, answer = ?, category = ? WHERE resource_id = ?");
        $stmt->bind_param("ssss", $question, $answer, $category, $resource_id);
        $stmt->execute();
        $stmt->close();

        logAdminAction($mysqli, $admin_id, $resource_id, 'edited', $description);

        echo json_encode(['status' => 'success', 'message' => 'FAQ updated successfully']);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }
}
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
        <label>Description:</label><br>
        <textarea name="description" required></textarea><br><br>
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
                            <strong>Q:</strong> <?= htmlspecialchars($faq['question']) ?><br><br>
                            <strong>A:</strong> <?= htmlspecialchars($faq['answer']) ?><br><br>
                            <p style="margin: 0; color: #555; font-size: 14px; text-align: justify;">
                                <strong>Description:</strong> <?= htmlspecialchars($faqDescriptions[$faq['resource_id']] ?? 'No description available', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <div style="text-align: center; margin-top: 10px;">
                                <button style="background-color: green; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;" onclick="editFAQ('<?= $faq['resource_id'] ?>')">Edit</button>
                                <button style="background-color: red; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;" onclick="deleteFAQ('<?= $faq['resource_id'] ?>')">Delete</button>
                            </div>
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
            try {
            const faqItem = document.querySelector(`.faq-item[data-id="${id}"]`);
            if (!faqItem) {
                alert('FAQ item not found');
                return;
            }
            const categoryText = faqItem.closest('.faq-category').querySelector('h3').textContent.trim().toLowerCase();
            const category = categoryText.includes('job') ? 'jobSeeker' : 'employer';

            const form = document.getElementById('faqForm');
            form.querySelector('[name="action"]').value = 'edit';
            form.querySelector('[name="category"]').value = category;

            const question = faqItem.querySelector('strong').nextSibling.nodeValue.trim();
            const answer = faqItem.querySelector('strong + br + br + strong').nextSibling.nodeValue.trim();
            const description = faqItem.querySelector('p').textContent.replace('Description:', '').trim();
            form.querySelector('[name="question"]').value = question;
            form.querySelector('[name="answer"]').value = answer;
            form.querySelector('[name="description"]').value = description;

            // Add a hidden input for the resource ID
            let idField = form.querySelector('[name="id"]');
            if (!idField) {
                idField = document.createElement('input');
                idField.type = 'hidden';
                idField.name = 'id';
                form.appendChild(idField);
            }
            idField.value = id;

            // Update the submit button to "Save FAQ Changes"
            const submitButton = form.querySelector('button[type="button"]');
            submitButton.textContent = 'Save FAQ Changes';

            // Handle form submission for "Save FAQ Changes"
            submitButton.onclick = () => {
                const formData = new FormData(form);

                fetch('manage_faq.php', {
                method: 'POST',
                body: formData,
                })
                .then(response => response.json())
                .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                    location.reload();
                } else {
                    alert('Failed to save changes: ' + data.message);
                }
                })
                .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                });
            };

            // Add a cancel button to reset the form
            let cancelButton = form.querySelector('button.cancel-button');
            if (!cancelButton) {
                cancelButton = document.createElement('button');
                cancelButton.type = 'button';
                cancelButton.className = 'cancel-button';
                cancelButton.textContent = 'Cancel';
                cancelButton.onclick = () => {
                form.reset();
                submitButton.textContent = 'Add FAQ';
                cancelButton.remove();
                };
                form.appendChild(cancelButton);
            }

            // Scroll to the top for visibility
            window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (error) {
            console.error('Error in editFAQ function:', error);
            alert('An error occurred. Please try again.');
            }
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