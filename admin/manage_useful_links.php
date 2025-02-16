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
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed']));
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

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'User not logged in']));
}
$user_id = $_SESSION['user_id'];

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

$usefulLinks = [];
$result = $mysqli->query("SELECT resource_id, title, link, category FROM resource WHERE type = 'useful_link'");
while ($row = $result->fetch_assoc()) {
    $usefulLinks[] = $row;
}

$usefulLinkDescriptions = [];
$result = $mysqli->query("SELECT resource_id, description FROM admin_resource");
while ($row = $result->fetch_assoc()) {
    $usefulLinkDescriptions[$row['resource_id']] = $row['description'];
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;

    if (!$action) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }

    if ($action === 'delete') {
        $resource_id = $_POST['id'] ?? null;
        logAdminAction($mysqli, $admin_id, $resource_id, 'deleted', 'Useful Link deleted');

        if (!$resource_id) {
            echo json_encode(['status' => 'error', 'message' => 'Resource ID is required for deletion']);
            exit;
        }

        $stmt = $mysqli->prepare("DELETE FROM Admin_Resource WHERE resource_id = ?");
        $stmt->bind_param("s", $resource_id);
        if (!$stmt->execute()) {
            error_log('Error deleting from Admin_Resource: ' . $stmt->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete related admin resources.']);
            exit;
        }
        $stmt->close();

        $stmt = $mysqli->prepare("DELETE FROM Resource WHERE resource_id = ?");
        $stmt->bind_param("s", $resource_id);
        if (!$stmt->execute()) {
            error_log('Error deleting from Resource: ' . $stmt->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete the resource.']);
            exit;
        }
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Useful Link deleted successfully']);
        exit;
    } elseif ($action === 'add') {
        $resource_id = $_POST['id'] ?? null;
        $description = trim($_POST['description'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $category = trim($_POST['category'] ?? '');

        if (!$title || !$link || !$category) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required for adding a Useful Link']);
            exit;
        }

        $admin_resource_id = generateAdminResourceId($mysqli);
        $resource_id = generateResourceId($mysqli);

        $stmt = $mysqli->prepare("INSERT INTO resource (resource_id, type, title, link, category) VALUES (?, 'useful_link', ?, ?, ?)");
        $stmt->bind_param("ssss", $resource_id, $title, $link, $category);
        $stmt->execute();
        $stmt->close();

        logAdminAction($mysqli, $admin_id, $resource_id, 'added', $description);

        echo json_encode(['status' => 'success', 'message' => 'Useful Link added successfully']);
        exit;
    } elseif ($action === 'edit') {
        $resource_id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $link = trim($_POST['link'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
    
        if (!$resource_id || !$title || !$link || !$category) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required for editing a Useful Link']);
            exit;
        }
    
        $stmt = $mysqli->prepare("UPDATE resource SET title = ?, link = ?, category = ? WHERE resource_id = ?");
        if (!$stmt) {
            error_log('Prepare failed: ' . $mysqli->error);
            echo json_encode(['status' => 'error', 'message' => 'Database error']);
            exit;
        }
        $stmt->bind_param("ssss", $title, $link, $category, $resource_id);
        if (!$stmt->execute()) {
            error_log('Error updating resource: ' . $stmt->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to update the resource.']);
            exit;
        }
        $stmt->close();
    
        logAdminAction($mysqli, $admin_id, $resource_id, 'edited', $description);
    
        echo json_encode(['status' => 'success', 'message' => 'Useful Link updated successfully']);
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Useful Links Management - TechFit</title>
    <link rel="stylesheet" href="styles.css">
</head>
    <style>
        li {
                color: white;
            }
            .wrapped-link {
                word-wrap: break-word;
                overflow-wrap: break-word;
                word-break: break-all;
                white-space: normal;
                display: inline-block;
                width: 100%;
            }

            #formContainer textarea,
            #formContainer input[type="url"],
            #formContainer select {
                background-color: var(--background-color);
                color: var(--text-color);
                border: 1px solid var(--background-color-light);
                padding: 8px;
                border-radius: 4px;
                width: calc(100% - 12px); /* Account for margin-left */
                font-size: 14px;
            }

            #formContainer textarea:focus,
            #formContainer input[type="url"]:focus,
            #formContainer select:focus {
                outline: none;
                border-color: var(--primary-color);
                box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
            }

            #formContainer select option {
                background-color: var(--background-color);
                color: var(--text-color);
            }

            #formContainer label {
                color: var(--text-color);
                display: block;
                margin-bottom: 5px;
            }

            /* Button styling */
            #formContainer button {
                background-color: var(--primary-color);
                color: var(--text-color);
                padding: 10px 20px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            #formContainer button:disabled {
                background-color: var(--background-color-light);
                cursor: not-allowed;
            }

            #formContainer button:hover:not(:disabled) {
                background-color: var(--accent-color);
            }

            .cancel-button {
                background-color: var(--danger-color) !important;
                margin-left: 10px;
            }

            .cancel-button:hover {
                background-color: var(--danger-color-hover) !important;
            }
    </style>
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
<body>
<div id="formContainer">
    <h1 style='text-align:center'>Manage Useful Links</h1>
    <form id="faqForm">
        <input type="hidden" name="action" value="add">
        
        <label style="margin-left: 6px;">Title:</label><br>
        <textarea name="title" required oninput="toggleCategoryAccess()" style="resize: vertical; margin-left: 6px;"></textarea><br>

        <label style="margin-left: 6px;">Link:</label><br>
        <input type="url" name="link" required oninput="toggleCategoryAccess()" style="margin-left: 6px; width: 100%;"><br><br>

        <label style="margin-left: 6px;">Category:</label><br>
        <select name="category" required oninput="toggleCategoryAccess()" style="margin-left: 6px;">
            <option value="" disabled selected>Select Category</option>
            <option value="jobSeeker">Job Seeker</option>
            <option value="employer">Employer</option>
        </select><br><br>

        <label style="margin-left: 6px;">Description:</label><br>
        <textarea name="description" required oninput="toggleCategoryAccess()" style="resize: vertical; margin-left: 6px;"></textarea><br><br>

        <div style="text-align: center;">
            <button type="button" onclick="submitUsefulLink()" disabled id="submitBtn">Add Useful Link</button>
        </div>
    </form>
</div>

<script>
    function toggleCategoryAccess() {
        const linkInput = document.querySelector('input[name="link"]');
        const titleInput = document.querySelector('textarea[name="title"]');
        const categorySelect = document.querySelector('select[name="category"]');
        const descriptionInput = document.querySelector('textarea[name="description"]');
        const submitButton = document.getElementById('submitBtn');

        
        const validURL = /^https?:\/\/[^\s]+$/i.test(linkInput.value.trim());

        
        const titleFilled = titleInput.value.trim() !== '';
        const categorySelected = categorySelect.value !== '';
        const descriptionFilled = descriptionInput.value.trim() !== '';

        
        console.log("Valid URL:", validURL);
        console.log("Title Filled:", titleFilled);
        console.log("Category Selected:", categorySelected);
        console.log("Description Filled:", descriptionFilled);

        
        if (validURL && titleFilled && categorySelected && descriptionFilled) {
            submitButton.removeAttribute('disabled');
            console.log("All fields are valid. Enabling submit button.");
        }
    }


    function submitUsefulLink() {
        const submitButton = document.getElementById('submitBtn');
        submitButton.disabled = true;
        const formData = new FormData(document.getElementById('faqForm'));
        console.log("Form Data being sent:", formData);
        fetch('manage_useful_links.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') location.reload();
                else submitButton.disabled = false;
            })
            .catch(() => {
                submitButton.disabled = false;
            });
    }

    function editUsefulLink(id) {
        try {
        const usefulLinkItem = document.querySelector(`.faq-item[data-id="${id}"]`);
        if (!usefulLinkItem) {
            alert('Useful Link item not found');
            return;
        }
        const title = usefulLinkItem.querySelector('strong:nth-of-type(1)').nextSibling.textContent.trim();
        const link = usefulLinkItem.querySelector('a').getAttribute('href').trim();
        const category = usefulLinkItem.closest('.faq-category').querySelector('h3').textContent.toLowerCase().includes('job') ? 'jobSeeker' : 'employer';
        const description = usefulLinkItem.querySelector('p').textContent.replace('Description:', '').trim();

        const form = document.getElementById('faqForm');
        form.querySelector('[name="action"]').value = 'edit';
        form.querySelector('[name="title"]').value = title;
        form.querySelector('[name="link"]').value = link;
        form.querySelector('[name="category"]').value = category;
        form.querySelector('[name="description"]').value = description;

        form.querySelector('[name="category"]').removeAttribute('disabled');
        form.querySelector('[name="description"]').removeAttribute('disabled');

        let idField = form.querySelector('[name="id"]');
        if (!idField) {
            idField = document.createElement('input');
            idField.type = 'hidden';
            idField.name = 'id';
            form.appendChild(idField);
        }
        idField.value = id;

        const submitButton = form.querySelector('button[type="button"]');
        submitButton.textContent = 'Save Changes';
        submitButton.onclick = () => {
        const formData = new FormData(form);
            fetch('manage_useful_links.php', {
                method: 'POST',
                body: formData,
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => { throw new Error(text) });
                }
                return response.json();
            })
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
                alert('An error occurred. Please check the console for details.');
            });
        };

        let cancelButton = form.querySelector('button.cancel-button');
        if (!cancelButton) {
            cancelButton = document.createElement('button');
            cancelButton.type = 'button';
            cancelButton.className = 'cancel-button';
            cancelButton.textContent = 'Cancel';
            cancelButton.onclick = () => {
            form.reset();
            submitButton.textContent = 'Add Useful Link';
            cancelButton.remove();
            };
            form.appendChild(cancelButton);
        }

        window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (error) {
        console.error('Error in editUsefulLink function:', error);
        alert('An error occurred. Please try again.');
        }
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

    <div id="faq">
        <h2 style="text-align: center;">Existing Useful Links</h2>
        <?php foreach (['jobSeeker', 'employer'] as $category): ?>
            <div class="faq-category">
            <h3>For <?= ucfirst(str_replace('jobSeeker', 'Job Seeker', $category)) ?>s</h3>
                <?php
                $categoryLinks = array_filter($usefulLinks, fn($usefulLink) => $usefulLink['category'] === $category);
                if ($categoryLinks):
                    foreach ($categoryLinks as $usefulLink): ?>
                        <div class="faq-item" data-id="<?= $usefulLink['resource_id'] ?>">
                            <div style="color:white;">
                                <strong>Title: </strong><?= htmlspecialchars($usefulLink['title'])?><br><br>
                                <strong>Link: </strong> 
                                <a href="<?= htmlspecialchars($usefulLink['link']) ?>" target="_blank" class="wrapped-link">
                                    <?= htmlspecialchars($usefulLink['link']) ?>
                                </a>
                            </div>
                            <p>
                            <strong>Description: </strong> <?= htmlspecialchars($usefulLinkDescriptions[$usefulLink['resource_id']] ?? 'No description available', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <div style="text-align: center; margin-top: 10px;">
                                <button style="background-color: green; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;" onclick="editUsefulLink('<?= $usefulLink['resource_id'] ?>')">Edit</button>
                                <button style="background-color: red; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;" onclick="deleteUsefulLink('<?= $usefulLink['resource_id'] ?>')">Delete</button>
                            </div>
                        </div>
                    <?php endforeach;
                else: ?>
                    <p>No useful links yet for this category.</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
            <div style="text-align: center; margin-top: 30px; padding-bottom: 30px;">
                <a href="useful_links.php" id="manage_useful_links_button" style="padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px;">Back to Useful Links</a>
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