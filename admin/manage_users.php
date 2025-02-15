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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Techfit</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        li {
            color: white;
        }

        :root {
            --primary-color: #007bff;
            --secondary-color: #1e1e1e;
            --accent-color: #0056b3;
            --text-color: #e0e0e0;
            --background-color: #121212;
            --border-color: #333;
            --hover-background-color: #333;
            --hover-text-color: #fff;
            --button-hover-color: #80bdff;
            --popup-background-color: #1a1a1a;
            --popup-border-color: #444;
            --danger-color: #dc3545;
            --danger-hover-color: #c82333;
            --success-color: #28a745;
            --success-hover-color: #218838;
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
        }

        .button-group {
            display: flex;
            gap: 10px;
        }

        .search-sort-controls {
            display: flex;
            align-items: center;
            padding: 10px;
            flex-wrap: nowrap;
        }

        .search-sort-controls span {
            margin-right: 10px;
            white-space: nowrap;
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
        }

        button:hover {
            background-color: var(--button-hover-color);
            color: var(--hover-text-color);
        }

        button.danger {
            background-color: var(--danger-color);
        }

        button.danger:hover {
            background-color: var(--danger-hover-color);
        }

        button.success {
            background-color: var(--success-color);
        }

        button.success:hover {
            background-color: var(--success-hover-color);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 40px; /* Add bottom margin to tables */
            table-layout: fixed;
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

        .resizer {
            display: inline-block;
            width: 5px;
            cursor: col-resize;
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 1;
        }

        th:first-child, td:first-child {
            width: 50px;
        }

        th[data-column="username"], td[data-column="username"],
        th[data-column="email"], td[data-column="email"] {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
        }

        th[data-column="actions"], td[data-column="actions"] {
            width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            position: relative;
        }

        td a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
            margin-right: 5px;
            transition: color 0.3s ease;
            display: inline-block;
        }

        td a:hover {
            color: var(--button-hover-color);
        }

        .action-separator {
            margin: 0 5px;
            color: var (--text-color);
            display: inline-block;
        }

        td a.deleteUser {
            color: var(--danger-color);
        }

        td a.deleteUser:hover {
            color: var(--danger-hover-color);
        }
        .header-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            margin-top: 40px; /* Add top margin to headers */
        }

        .header-controls h2 {
            margin: 0;
        }

        .button-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .button-group button {
            margin: 0;
        }
    </style>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        let lastChecked = null;

        document.getElementById('selectAllJobSeekers').addEventListener('click', function() {
            var checkboxes = document.querySelectorAll('.selectJobSeeker');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });

        document.querySelectorAll('.selectJobSeeker').forEach(function(checkbox) {
            checkbox.addEventListener('click', function(event) {
                if (!lastChecked) {
                    lastChecked = this;
                    return;
                }

                if (event.shiftKey) {
                    let checkboxes = Array.from(document.querySelectorAll('.selectJobSeeker'));
                    let start = checkboxes.indexOf(this);
                    let end = checkboxes.indexOf(lastChecked);

                    checkboxes.slice(Math.min(start, end), Math.max(start, end) + 1)
                        .forEach(checkbox => checkbox.checked = lastChecked.checked);
                }

                lastChecked = this;
            });
        });

        // Update the delete button event listener in manage_users.php
        document.getElementById('deleteSelectedJobSeekers').addEventListener('click', function() {
            var selected = [];
            document.querySelectorAll('.selectJobSeeker:checked').forEach(function(checkbox) {
                selected.push(checkbox.value);
            });
            if (selected.length > 0) {
                if (confirm('Are you sure you want to delete the selected Job Seekers?')) {
                    window.location.href = 'delete_job_seekers.php?user_ids=' + selected.join(',');
                }
            } else {
                alert('Please select at least one Job Seeker to delete.');
            }
        });

        document.getElementById('restoreAllJobSeekers').addEventListener('click', function() {
            if (confirm('Are you sure you want to restore all deleted job seekers?')) {
                fetch('restore_all_job_seekers.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while restoring the job seekers.');
                });
            }
        });
        document.getElementById('restoreAllEmployers').addEventListener('click', function() {
            if (confirm('Are you sure you want to restore all deleted employers?')) {
                fetch('restore_all_employers.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while restoring the employers.');
                });
            }
        });
        // Employer table functionality
        document.getElementById('selectAllEmployers').addEventListener('click', function() {
            var checkboxes = document.querySelectorAll('.selectEmployer');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });

        let lastCheckedEmployer = null;
        document.querySelectorAll('.selectEmployer').forEach(function(checkbox) {
            checkbox.addEventListener('click', function(event) {
                if (!lastCheckedEmployer) {
                    lastCheckedEmployer = this;
                    return;
                }

                if (event.shiftKey) {
                    let checkboxes = Array.from(document.querySelectorAll('.selectEmployer'));
                    let start = checkboxes.indexOf(this);
                    let end = checkboxes.indexOf(lastCheckedEmployer);

                    checkboxes.slice(Math.min(start, end), Math.max(start, end) + 1)
                        .forEach(checkbox => checkbox.checked = lastCheckedEmployer.checked);
                }

                lastCheckedEmployer = this;
            });
        });

        document.getElementById('deleteSelectedEmployers').addEventListener('click', function() {
            var selected = [];
            document.querySelectorAll('.selectEmployer:checked').forEach(function(checkbox) {
                selected.push(checkbox.value);
            });
            if (selected.length > 0) {
                if (confirm('Are you sure you want to delete the selected Employers?')) {
                    window.location.href = 'delete_employers.php?user_ids=' + selected.join(',');
                }
            } else {
                alert('Please select at least one Employer to delete.');
            }
        });
        // Deleted Users table functionality
        document.getElementById('selectAllDeletedUsers').addEventListener('click', function() {
            var checkboxes = document.querySelectorAll('.selectDeletedUser');
            for (var checkbox of checkboxes) {
                checkbox.checked = this.checked;
            }
        });

        let lastCheckedDeletedUser = null;
        document.querySelectorAll('.selectDeletedUser').forEach(function(checkbox) {
            checkbox.addEventListener('click', function(event) {
                if (!lastCheckedDeletedUser) {
                    lastCheckedDeletedUser = this;
                    return;
                }

                if (event.shiftKey) {
                    let checkboxes = Array.from(document.querySelectorAll('.selectDeletedUser'));
                    let start = checkboxes.indexOf(this);
                    let end = checkboxes.indexOf(lastCheckedDeletedUser);

                    checkboxes.slice(Math.min(start, end), Math.max(start, end) + 1)
                        .forEach(checkbox => checkbox.checked = lastCheckedDeletedUser.checked);
                }

                lastCheckedDeletedUser = this;
            });
        });

        // Update the restore button event listener in manage_users.php
        document.getElementById('restoreSelectedUsers').addEventListener('click', function() {
            // Get only the checked checkboxes
            const selectedCheckboxes = Array.from(document.querySelectorAll('.selectDeletedUser:checked'));
            
            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one user to restore.');
                return;
            }

            // Get the selected user IDs
            const selectedUserIds = selectedCheckboxes.map(cb => cb.value);
            
            // Debug logging
            console.log('Selected users:', {
                count: selectedCheckboxes.length,
                userIds: selectedUserIds
            });

            if (confirm(`Are you sure you want to restore ${selectedCheckboxes.length} selected user(s)?`)) {
                const formData = new FormData();
                
                // Add selected user IDs as individual array elements
                selectedUserIds.forEach(id => {
                    formData.append('restore_users[]', id);
                });

                // Debug - log the form data
                console.log('FormData entries:', Array.from(formData.entries()));

                fetch('restore_users.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log('Server response:', data);
                    
                    if (data.success) {
                        // Only remove the specifically selected rows
                        selectedUserIds.forEach(userId => {
                            const row = document.getElementById('deletedUserRow_' + userId);
                            if (row) {
                                row.remove();
                            }
                        });
                        
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Failed to restore users: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while restoring the users.');
                });
            }
        });
    });
    
    </script>
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
    <main>
        <h1>Manage Users</h1>
        <div class="header-controls">
            <h2>Job Seekers</h2>
            <button id="deleteSelectedJobSeekers" class="danger">Delete Selected Job Seekers</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAllJobSeekers"></th>
                    <th>User ID</th>
                    <th>Job Seeker ID</th>
                    <th>Username</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody id="jobSeekersTableBody">
                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "techfit";

                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT User.user_id, Job_Seeker.job_seeker_id, User.username, User.email 
                        FROM User 
                        JOIN Job_Seeker ON User.user_id = Job_Seeker.user_id 
                        WHERE User.role = 'Job Seeker' AND User.is_active = 1";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr id='jobSeekerRow_" . htmlspecialchars($row['user_id']) . "'>";
                        echo "<td><input type='checkbox' class='selectJobSeeker' value='" . htmlspecialchars($row['user_id']) . "'></td>";
                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['job_seeker_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No Job Seekers found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
        <div class="header-controls">
    <h2>Employers</h2>
            <button id="deleteSelectedEmployers" class="danger">Delete Selected Employers</button>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAllEmployers"></th>
                    <th>User ID</th>
                    <th>Employer ID</th>
                    <th>Username</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody id="employersTableBody">
                <?php
                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT User.user_id, Employer.employer_id, User.username, User.email 
                        FROM User 
                        JOIN Employer ON User.user_id = Employer.user_id 
                        WHERE User.role = 'Employer' AND User.is_active = 1";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr id='employerRow_" . htmlspecialchars($row['user_id']) . "'>";
                        echo "<td><input type='checkbox' class='selectEmployer' value='" . htmlspecialchars($row['user_id']) . "'></td>";
                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['employer_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No Employers found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
        <div class="header-controls">
            <h2>Deleted Users</h2>
            <div class="button-group">
                <button id="restoreAllEmployers" class="success">Restore All Employers</button>
                <button id="restoreAllJobSeekers" class="success">Restore All Job Seekers</button>
                <button id="restoreSelectedUsers" class="primary">Restore Selected Users</button>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAllDeletedUsers"></th>
                    <th>User ID</th>
                    <th>Job Seeker/Employer ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody id="deletedUsersTableBody">
                <?php
                $conn = new mysqli($servername, $username, $password, $dbname);

                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT User.user_id, User.username, User.email, User.role, 
                        CASE 
                            WHEN User.role = 'Job Seeker' THEN Job_Seeker.job_seeker_id 
                            WHEN User.role = 'Employer' THEN Employer.employer_id 
                        END AS role_specific_id 
                        FROM User 
                        LEFT JOIN Job_Seeker ON User.user_id = Job_Seeker.user_id AND User.role = 'Job Seeker'
                        LEFT JOIN Employer ON User.user_id = Employer.user_id AND User.role = 'Employer'
                        WHERE User.is_active = 0 AND (User.role = 'Job Seeker' OR User.role = 'Employer')";

                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr id='deletedUserRow_" . htmlspecialchars($row['user_id']) . "'>";
                        echo "<td><input type='checkbox' class='selectDeletedUser' value='" . htmlspecialchars($row['user_id']) . "'></td>";
                        echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['role_specific_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No deleted users found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </main>
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
    <script src="scripts.js"></script>
</body>
</html>