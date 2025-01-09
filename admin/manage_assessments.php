<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assessments - TechFit</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
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
                            <li><a href="manage_users.html">Manage Users</a></li>
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
        <main>
        <h1>Manage Assessments</h1>
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <button onclick="window.location.href='create_assessment.html'">Create New Assessment</button>
                <button id="deleteSelected">Delete Selected</button>
                <button id="viewDeleted">View Deleted Assessments</button>
            </div>
            <div style="display: flex; align-items: center; padding: 10px;">
                <select id="sortDropdown">
                    <option value="none">None</option>
                    <option value="assessment_id_asc">Assessment ID ASC</option>
                    <option value="assessment_id_desc">Assessment ID DESC</option>
                    <option value="admin_id_asc">Admin ID ASC</option>
                    <option value="admin_id_desc">Admin ID DESC</option>
                </select>
                <input type="text" id="searchInput" placeholder="Search..." style="margin-left: 10px;">
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th><input type="checkbox" id="selectAll"></th>
                    <th data-column="assessment_id">Assessment ID</th>
                    <th data-column="admin_id">Admin ID</th>
                    <th data-column="assessment_name">Assessment Name</th>
                    <th data-column="description">Description</th>
                    <th data-column="timestamp">Timestamp</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="assessmentsTableBody">
                <?php
                $servername = "localhost";
                $username = "root";
                $password = "";
                $dbname = "techfit";

                // Create connection
                $conn = new mysqli($servername, $username, $password, $dbname);

                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                $sql = "SELECT assessment_id, admin_id, assessment_name, description, timestamp FROM Assessment_Admin WHERE is_active = 1";
                $result = $conn->query($sql);

                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><input type='checkbox' class='selectAssessment' value='" . htmlspecialchars($row['assessment_id']) . "'></td>";
                        echo "<td>" . htmlspecialchars($row['assessment_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['admin_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['assessment_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                        echo "<td><a href='edit_assessment.php?assessment_id=" . htmlspecialchars($row['assessment_id']) . "'>Edit</a> | <a href='#' class='deleteAssessment' data-id='" . htmlspecialchars($row['assessment_id']) . "'>Delete</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No assessments found</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
        <div id="deleted-assessments-tab" style="display:none;">
            <h3>Deleted Assessments</h3>
            <label><input type="checkbox" id="select-all-deleted"> Select All</label>
            <form id="restore-form">
                <table>
                    <thead>
                        <tr>
                            <th>Select</th>
                            <th>Assessment ID</th>
                            <th>Assessment Name</th>
                            <th>Description</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                    <tbody id="deleted-assessments"></tbody>
                </table>
                <button type="button" onclick="restoreSelectedAssessments()">Restore Selected Assessments</button>
            </form>
            <button type="button" onclick="closeDeletedAssessments()">Close</button>
        </div>
    </main>

    <style>
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        text-align: left;
        padding: 8px;
        border-bottom: 1px solid #ddd;
    }

    th {
        background-color: #f2f2f2;
        cursor: pointer;
    }

    th[data-column]:hover {
        background-color: #e0e0e0;
    }

    #deleted-assessments-tab {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: white;
        padding: 20px;
        border: 1px solid #ccc;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
        max-height: 80vh;
        overflow-y: auto;
        z-index: 1000;
        width: 90%;
    }
    </style>

    <script>
    document.getElementById('selectAll').addEventListener('click', function() {
        var checkboxes = document.querySelectorAll('.selectAssessment');
        for (var checkbox of checkboxes) {
            checkbox.checked = this.checked;
        }
    });

    document.querySelectorAll('.deleteAssessment').forEach(function(element) {
        element.addEventListener('click', function(event) {
            event.preventDefault();
            var assessmentId = this.getAttribute('data-id');
            if (confirm('Are you sure you want to delete this assessment?')) {
                window.location.href = 'delete_assessment.php?assessment_id=' + assessmentId;
            }
        });
    });

    document.getElementById('deleteSelected').addEventListener('click', function() {
        var selected = [];
        document.querySelectorAll('.selectAssessment:checked').forEach(function(checkbox) {
            selected.push(checkbox.value);
        });
        if (selected.length > 0 && confirm('Are you sure you want to delete the selected assessments?')) {
            window.location.href = 'delete_assessment.php?assessment_ids=' + selected.join(',');
        }
    });

    document.getElementById('viewDeleted').addEventListener('click', function() {
        fetch('get_deleted_assessments.php')
            .then(response => response.json())
            .then(data => {
                const deletedAssessmentsDiv = document.getElementById('deleted-assessments');
                if (data.length > 0) {
                    deletedAssessmentsDiv.innerHTML = data.map(assessment => `
                        <tr>
                            <td><input type="checkbox" name="restore_assessments[]" value="${assessment.assessment_id}"></td>
                            <td>${assessment.assessment_id}</td>
                            <td>${assessment.assessment_name}</td>
                            <td>${assessment.description}</td>
                            <td>${assessment.timestamp}</td>
                        </tr>
                    `).join('');
                } else {
                    deletedAssessmentsDiv.innerHTML = '<tr><td colspan="5">No deleted assessments found</td></tr>';
                }
                document.getElementById('deleted-assessments-tab').style.display = 'block';

                // Add event listener for select all checkbox
                document.getElementById('select-all-deleted').addEventListener('change', function() {
                    const checkboxes = document.querySelectorAll('input[name="restore_assessments[]"]');
                    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
                });
            });
    });

    function closeDeletedAssessments() {
        document.getElementById('deleted-assessments-tab').style.display = 'none';
    }

    function restoreSelectedAssessments() {
        if (confirm('Are you sure you want to restore the selected assessments?')) {
            const form = document.getElementById('restore-form');
            const formData = new FormData(form);

            fetch('restore_assessments.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Selected assessments restored successfully.');
                    location.reload(); // Reload the page to update the restored assessments
                } else {
                    alert('Failed to restore selected assessments.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while restoring the assessments.');
            });
        }
    }

    document.getElementById('searchInput').addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#assessmentsTableBody tr');
        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(filter));
            row.style.display = match ? '' : 'none';
        });
    });

    document.getElementById('sortDropdown').addEventListener('change', function() {
        const value = this.value;
        const rows = Array.from(document.querySelectorAll('#assessmentsTableBody tr'));
        let columnIndex, order;

        switch (value) {
            case 'assessment_id_asc':
                columnIndex = 1;
                order = 1;
                break;
            case 'assessment_id_desc':
                columnIndex = 1;
                order = -1;
                break;
            case 'admin_id_asc':
                columnIndex = 2;
                order = 1;
                break;
            case 'admin_id_desc':
                columnIndex = 2;
                order = -1;
                break;
            default:
                return;
        }

        rows.sort((a, b) => {
            const aText = a.querySelector(`td:nth-child(${columnIndex + 1})`).textContent.trim();
            const bText = b.querySelector(`td:nth-child(${columnIndex + 1})`).textContent.trim();
            return aText.localeCompare(bText, undefined, {numeric: true}) * order;
        });

        rows.forEach(row => document.querySelector('#assessmentsTableBody').appendChild(row));
    });

    document.querySelectorAll('th[data-column]').forEach(th => {
        th.addEventListener('click', function() {
            const column = this.getAttribute('data-column');
            const order = this.dataset.order = -(this.dataset.order || -1);
            const rows = Array.from(document.querySelectorAll('#assessmentsTableBody tr'));
            rows.sort((a, b) => {
                const aText = a.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                const bText = b.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                return aText.localeCompare(bText, undefined, {numeric: true}) * order;
            });
            rows.forEach(row => document.querySelector('#assessmentsTableBody').appendChild(row));
        });
    });
    </script>

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
</body>
</html>