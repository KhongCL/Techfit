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

$sitemapDescriptions = [];
$result = $mysqli->query("SELECT resource_id, description FROM admin_resource");
while ($row = $result->fetch_assoc()) {
    $sitemapDescriptions[$row['resource_id']] = $row['description'];
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
        logAdminAction($mysqli, $admin_id, $resource_id, 'deleted', 'Sitemap deleted');
        
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

        echo json_encode(['status' => 'success', 'message' => 'Sitemap deleted successfully']);
        exit;
    } 
    if ($action === 'add') {
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $image = $_FILES['image']['tmp_name'] ?? null;
    
        if (!$description || !$category || !$image) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required for adding a sitemap']);
            exit;
        }
    
        $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM resource WHERE type = 'sitemap' AND category = ?");
        $checkStmt->bind_param("s", $category);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();
    
        if ($count > 0) {
            echo json_encode(['status' => 'error', 'message' => 'A sitemap already exists for this category. Please delete the existing one before uploading a new one.']);
            exit;
        }
    
        $resource_id = generateResourceId($mysqli);
        $image_data = file_get_contents($image);
    
        $stmt = $mysqli->prepare("INSERT INTO resource (resource_id, type, category, image) VALUES (?, 'sitemap', ?, ?)");
        $stmt->bind_param("sss", $resource_id, $category, $image_data);
        $stmt->execute();
        $stmt->close();
    
        logAdminAction($mysqli, $admin_id, $resource_id, 'added', $description);
    
        echo json_encode(['status' => 'success', 'message' => 'Sitemap added successfully']);
        exit;
    }
    
    elseif ($action === 'edit') {
        $resource_id = $_POST['id'] ?? null;
        $description = trim($_POST['description'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $image = $_FILES['image']['tmp_name'] ?? null;
    
        if (!$resource_id || !$description || !$category) {
            echo json_encode(['status' => 'error', 'message' => 'All fields are required for editing a sitemap']);
            exit;
        }
    
        $currentCategoryStmt = $mysqli->prepare("SELECT category FROM resource WHERE resource_id = ?");
        $currentCategoryStmt->bind_param("s", $resource_id);
        $currentCategoryStmt->execute();
        $currentCategoryStmt->bind_result($current_category);
        $currentCategoryStmt->fetch();
        $currentCategoryStmt->close();
    
        if ($current_category !== $category) {
            $checkStmt = $mysqli->prepare("SELECT COUNT(*) FROM resource WHERE type = 'sitemap' AND category = ?");
            $checkStmt->bind_param("s", $category);
            $checkStmt->execute();
            $checkStmt->bind_result($count);
            $checkStmt->fetch();
            $checkStmt->close();
    
            if ($count > 0) {
                echo json_encode(['status' => 'error', 'message' => 'A sitemap already exists for the new category. Please delete it first.']);
                exit;
            }
        }
    
        if ($image) {
            $image_data = file_get_contents($image);
            $stmt = $mysqli->prepare("UPDATE resource SET category = ?, image = ? WHERE resource_id = ?");
            $stmt->bind_param("sss", $category, $image_data, $resource_id);
        } else {
            $stmt = $mysqli->prepare("UPDATE resource SET category = ? WHERE resource_id = ?");
            $stmt->bind_param("ss", $category, $resource_id);
        }
        $stmt->execute();
        $stmt->close();
    
        logAdminAction($mysqli, $admin_id, $resource_id, 'edited', $description);
    
        echo json_encode(['status' => 'success', 'message' => 'Sitemap updated successfully']);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit;
    }
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

$result = $mysqli->query("SELECT * FROM resource WHERE type = 'sitemap' ORDER BY category, resource_id");
$sitemaps = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitemap Management - TechFit</title>
    <link rel="stylesheet" href="styles.css">
</head>
    <style>
        li {
            color: white;
        }

        #formContainer {
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        #faqForm {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: var(--background-color-medium);
            border-radius: 8px;
        }

        #faqForm label {
            color: var(--text-color);
            font-size: 16px;
            margin-bottom: 5px;
            display: block;
        }

        #faqForm select,
        #faqForm input[type="file"],
        #faqForm textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            background-color: var(--background-color-light);
            border: 1px solid var(--background-color-extra-light);
            border-radius: 4px;
            color: var(--text-color);
        }

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


        .sitemap-category {
            background-color: var(--background-color-medium);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .sitemap-item {
            margin-bottom: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: var(--background-color-light) !important;
        }

        .sitemap-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
        }

        .edit-button,
        .delete-button {
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin: 5px;
            border: none;
        }

        #manage_sitemap_button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: var(--primary-color);
            color: var(--text-color);
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }

        #manage_sitemap_button:hover {
            background-color: var(--primary-color-hover);
        }

        @media (max-width: 768px) {
            #formContainer {
                padding: 10px;
            }

            #faqForm {
                padding: 15px;
            }

            .sitemap-category {
                padding: 15px;
            }

            .sitemap-item {
                padding: 15px;
            }

            .sitemap-image {
                max-width: 100% !important;
            }

            .edit-button,
            .delete-button {
                width: 100%;
                margin: 5px 0;
            }

            .button-container {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
        }

        @media (max-width: 480px) {
            #faqForm label,
            #faqForm select,
            #faqForm input[type="file"],
            #faqForm textarea {
                font-size: 14px;
            }

            .sitemap-category h2 {
                font-size: 20px !important;
            }

            .sitemap-item p {
                font-size: 12px !important;
            }
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
    <h1 style="text-align: center; padding-top: 60px;">Manage Sitemaps</h1>
    <form id="faqForm" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <label>Category:</label><br>
        <select name="category" required oninput="toggleCategoryAccess()">
            <option value="" disabled selected>Select Category</option>
            <option value="jobSeeker">Job Seeker</option>
            <option value="employer">Employer</option>
        </select><br>
        <label>Image:</label><br>
        <input type="file" name="image" accept="image/*" required oninput="toggleCategoryAccess()"><br><br>
        <label>Description:</label><br>
        <textarea name="description" required oninput="toggleCategoryAccess()" style="resize: vertical"></textarea><br><br>
        <button type="button" onclick="submitSitemap()" disabled id="submitBtn" style="display: block; margin: 0 auto;">Add Sitemap</button>
    </form>
    <h1 style="padding-top: 150px;">Existing Sitemaps</h1>
    <div id="sitemap" style="padding: 20px; font-family: Arial, sans-serif;">
        <?php foreach (['jobSeeker', 'employer'] as $category): ?>
            <div class="sitemap-category" style="margin-bottom: 20px;">
                <h2 style="font-size: 24px; color: white;">For <?= ucfirst(str_replace('jobSeeker', 'Job Seeker', $category)) ?>s</h2>
                <?php 
                $categorySitemaps = array_filter($sitemaps, fn($sitemap) => $sitemap['category'] === $category);
                if ($categorySitemaps): 
                    foreach ($categorySitemaps as $sitemap): ?>
                        <div class="sitemap-item" data-id="<?= $sitemap['resource_id'] ?>" style="border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 5px; background-color: #121212;">
                            <strong style="display: block; margin-bottom: 10px;">Image:</strong>
                            <img src="data:image/jpeg;base64,<?= base64_encode($sitemap['image']) ?>" alt="Sitemap Image" class="sitemap-image" style="max-width: 50%; height: auto; margin-bottom: 10px;"><br>
                            <button class="edit-button" onclick="editSitemap('<?= $sitemap['resource_id'] ?>')" style="margin-right: 10px; padding: 5px 10px; background-color: #4CAF50; color: white; border: none; border-radius: 3px; cursor: pointer;">Edit</button>
                            <button class="delete-button" onclick="deleteSitemap('<?= $sitemap['resource_id'] ?>')" style="padding: 5px 10px; background-color: #f44336; color: white; border: none; border-radius: 3px; cursor: pointer;">Delete</button><br><br>
                            <strong style="display: block; margin-bottom: 5px; color: white;">Description:</strong>
                            <p style="margin: 0; font-size: 14px;">
                                <?= htmlspecialchars($sitemapDescriptions[$sitemap['resource_id']] ?? 'No description available', ENT_QUOTES, 'UTF-8') ?>
                            </p>
                        </div>
                    <?php endforeach; 
                else: ?>
                    <p style="color: #888; font-size: 14px;">No sitemaps yet for this category.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div style="text-align: center; margin-top: 30px; padding-bottom: 30px;">
        <a href="sitemap.php" id="manage_sitemap_button" style="background-color: #4CAF50; padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px;">Back to Sitemap</a>
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
    <script>
        function toggleCategoryAccess() {
            const categorySelect = document.querySelector('select[name="category"]');
            const imageInput = document.querySelector('input[name="image"]');
            const descriptionInput = document.querySelector('textarea[name="description"]');
            const submitButton = document.querySelector('button[type="button"]');
            
            const categorySelected = categorySelect.value !== '';
            const imageFilled = imageInput.files.length > 0;
            const descriptionFilled = descriptionInput.value.trim() !== '';
          
            if (categorySelected && imageFilled && descriptionFilled) {
            submitButton.removeAttribute('disabled');
            console.log("All fields are valid. Enabling submit button.");
            } else {
            submitButton.setAttribute('disabled', 'disabled');
            console.log("Some fields are missing. Disabling submit button.");
            }
        }

        function submitSitemap() {
            const submitButton = document.querySelector('button[type="button"]');
            submitButton.disabled = true;
            const formData = new FormData(document.getElementById('faqForm'));
            fetch('manage_sitemap.php', { method: 'POST', body: formData })
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

        function editSitemap(id) {
            try {
            const sitemapItem = document.querySelector(`.sitemap-item[data-id="${id}"]`);
            if (!sitemapItem) {
                alert('Sitemap item not found');
                return;
            }
            const categoryText = sitemapItem.closest('.sitemap-category').querySelector('h1').textContent.trim().toLowerCase();
            const category = categoryText.includes('job') ? 'jobSeeker' : 'employer';

            const form = document.getElementById('faqForm');
            form.querySelector('[name="action"]').value = 'edit';
            form.querySelector('[name="category"]').value = category;

            const description = sitemapItem.querySelector('p').innerText.trim();
            form.querySelector('[name="description"]').value = description;

            const imageSrc = sitemapItem.querySelector('img').src;
            const imageField = form.querySelector('[name="image"]');
            const imagePreview = document.createElement('img');
            imagePreview.src = imageSrc;
            imagePreview.alt = 'Image Preview';
            imagePreview.style.maxWidth = '100px';
            imagePreview.style.display = 'block';
            imagePreview.style.marginTop = '10px';

            const existingPreview = form.querySelector('img[alt="Image Preview"]');
            if (existingPreview) {
                existingPreview.remove();
            }

            imageField.insertAdjacentElement('afterend', imagePreview);

            let idField = form.querySelector('[name="id"]');
            if (!idField) {
                idField = document.createElement('input');
                idField.type = 'hidden';
                idField.name = 'id';
                form.appendChild(idField);
            }
            idField.value = id;

            const submitButton = form.querySelector('button[type="button"]');
            submitButton.textContent = 'Save Sitemap Changes';

            submitButton.onclick = () => {
                const formData = new FormData(form);

                fetch('manage_sitemap.php', {
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

            let cancelButton = form.querySelector('button.cancel-button');
            if (!cancelButton) {
                cancelButton = document.createElement('button');
                cancelButton.type = 'button';
                cancelButton.className = 'cancel-button';
                cancelButton.textContent = 'Cancel';
                cancelButton.onclick = () => {
                    form.reset();
                    submitButton.textContent = 'Add Sitemap';
                    cancelButton.remove();
                    const existingPreview = form.querySelector('img[alt="Image Preview"]');
                    if (existingPreview) {
                        existingPreview.remove();
                    }
                };
                form.appendChild(cancelButton);
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (error) {
            console.error('Error in editSitemap function:', error);
            alert('An error occurred. Please try again.');
            }
        }


        function deleteSitemap(id) {
            if (confirm('Are you sure you want to delete this Sitemap?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id); 

            fetch('manage_sitemap.php', { 
                method: 'POST', 
                body: formData 
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.status === 'success') {
                location.reload();
                } else {
                alert('Failed to delete the sitemap. Please try again.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to delete the sitemap. Please try again.');
            });
            }
        }

        function logAdminAction(actionType, resourceId) {
            const formData = new FormData();
            formData.append('action', 'log');
            formData.append('action_type', actionType);
            formData.append('resource_id', resourceId);

            fetch('manage_sitemap.php', {
            method: 'POST',
            body: formData
            })
            .then(response => response.json())
            .then(data => {
            if (data.status !== 'success') {
                console.error('Failed to log action:', data.message);
            }
            })
            .catch(error => {
            console.error('Error logging action:', error);
            });
        }
    </script>
</body>
</html>