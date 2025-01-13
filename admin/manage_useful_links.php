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
            $title = trim($_POST['title']);
            $link = trim($_POST['link']);
            $category = trim($_POST['category']);

            if ($title && $link && $category) {
                $resourceId = generateResourceId($mysqli);
                $stmt = $mysqli->prepare("INSERT INTO resource (resource_id, type, title, link, category) VALUES (?, 'usefulLink', ?, ?, ?)");
                $stmt->bind_param("ssss", $resourceId, $title, $link, $category);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'message' => 'Useful link added successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            }
        } elseif ($action === 'edit') {
            $id = $_POST['id'];
            $title = trim($_POST['title']);
            $link = trim($_POST['link']);
            $category = trim($_POST['category']);

            if ($id && $title && $link && $category) {
                $stmt = $mysqli->prepare("UPDATE resource SET title = ?, link = ?, category = ? WHERE resource_id = ?");
                $stmt->bind_param("ssss", $title, $link, $category, $id);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'message' => 'Useful link updated successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'];
            if ($id) {
                $stmt = $mysqli->prepare("DELETE FROM resource WHERE resource_id = ?");
                $stmt->bind_param("s", $id);
                $stmt->execute();
                echo json_encode(['status' => 'success', 'message' => 'Useful link deleted successfully']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid ID.']);
            }
        }
        exit;
    }
}

// Fetch Useful Links for Display
$result = $mysqli->query("SELECT * FROM resource WHERE type = 'usefulLink' ORDER BY category, resource_id");
$usefulLinks = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin - Useful Links Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<header>
        <div class="logo">
            <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
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
                            <li><a href="create_assessment.html">Create New Assessment</a></li>
                            <li><a href="manage_assessments.php">Manage Assessments</a></li>
                            <li><a href="view_assessment_results.html">View Assessment Results</a></li>
                        </ul>
                    </li>
                    <li><a href="#">Users</a>
                        <ul class="dropdown">
                            <li><a href="manage_users.php">Manage Users</a></li>
                            <li><a href="user_feedback.html">User Feedback</a></li>
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
                            <li><a href="useful_links.html">Manage Useful Links</a></li>
                            <li><a href="faq.html">Manage FAQs</a></li>
                            <li><a href="sitemap.html">Manage Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.html">About</a></li>
                    <li>
                        <a href="#" id="profile-link">
                            <div class="profile-info">
                                <span class="username" id="username">Admin</span>
                                <img src="images/usericon.png" alt="Profile" class="profile-image" id="profile-image">
                            </div>
                        </a>
                        <ul class="dropdown" id="profile-dropdown">
                            <li><a href="settings.html">Settings</a>
                                <ul class="dropdown">
                                    <li><a href="manage_profile.html">Manage Profile</a></li>
                                    <li><a href="system_configuration.html">System Configuration Settings</a></li>
                                </ul>
                            </li>
                            <li><a href="logout.html">Logout</a></li>
                        </ul>
                    </li>                    
                </ul>
            </div>
        </nav>
    </header>  
<body>
    <h1 style="text-align: center; padding-top: 60px;">Manage Useful Links</h1>
    <form id="faqForm">
    <input type="hidden" name="action" value="add">
    <label>Title:</label><br>
    <textarea name="title" required></textarea><br>
    <label>Link:</label><br>
    <input type="url" name="link" required oninput="toggleCategoryAccess()"><br>
    <label>Category:</label><br>
    <select name="category" required disabled>
        <option value="" disabled selected>Select Category</option>
        <option value="jobSeeker">Job Seeker</option>
        <option value="employer">Employer</option>
    </select><br><br>
    <button type="button" onclick="submitUsefulLink()" disabled id="submitBtn">Add Useful Link</button>
</form>

<script>
    function toggleCategoryAccess() {
        const linkInput = document.querySelector('input[name="link"]');
        const categorySelect = document.querySelector('select[name="category"]');
        const submitButton = document.getElementById('submitBtn');
        
        // Check if the link is a valid URL
        const validURL = /^https?:\/\/[^\s]+$/i.test(linkInput.value.trim());
        
        if (validURL) {
            categorySelect.removeAttribute('disabled');
            submitButton.removeAttribute('disabled');
        } else {
            categorySelect.setAttribute('disabled', 'disabled');
            submitButton.setAttribute('disabled', 'disabled');
        }
    }
</script>
    <h2 style="text-align: center;">Existing Useful Links</h2>
    <div id="faq">
        <?php foreach (['jobSeeker', 'employer'] as $category): ?>
            <div class="faq-category">
                <h3>For <?= ucfirst($category) ?>s</h3>
                <?php 
                $categoryLinks = array_filter($usefulLinks, fn($link) => $link['category'] === $category);
                if ($categoryLinks): 
                    foreach ($categoryLinks as $link): ?>
                        <div class="faq-item" data-id="<?= $link['resource_id'] ?>">
                            <strong>Title:</strong> <?= htmlspecialchars($link['title']) ?><br>
                            <strong>Link:</strong> <?= htmlspecialchars($link['link']) ?><br>
                            <button onclick="editUsefulLink('<?= $link['resource_id'] ?>')">Edit</button>
                            <button onclick="deleteUsefulLink('<?= $link['resource_id'] ?>')">Delete</button>
                        </div>
                    <?php endforeach; 
                else: ?>
                    <p>No useful links yet for this category.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div style="text-align: center; margin-top: 30px; padding-bottom: 30px;">
        <a href="useful_links.php" id="manage_useful_links_button" style="background-color: #4CAF50; padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px;">Back to Useful Links</a>
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
        function submitUsefulLink() {
            const formData = new FormData(document.getElementById('faqForm'));
            fetch('manage_useful_links.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.status === 'success') location.reload();
                });
        }
        function editUsefulLink(id) {
            const usefulLinkItem = document.querySelector(`.faq-item[data-id="${id}"]`);
            const title = usefulLinkItem.querySelector('strong:nth-of-type(1)').nextSibling.textContent.trim();
            const link = usefulLinkItem.querySelector('strong:nth-of-type(2)').nextSibling.textContent.trim();
            const category = usefulLinkItem.closest('.faq-category').querySelector('h3').textContent.includes('Job Seeker') ? 'jobSeeker' : 'employer';

            // Populate the form with the Useful Link details
            const form = document.getElementById('faqForm');
            form.querySelector('[name="action"]').value = 'edit';
            form.querySelector('[name="title"]').value = title;
            form.querySelector('[name="link"]').value = link;
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
            submitButton.onclick = submitUsefulLink;
        }
        
        function deleteUsefulLink(id) {
            if (confirm('Are you sure you want to delete this Useful Link?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);
                fetch('manage_useful_links.php', { method: 'POST', body: formData })
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