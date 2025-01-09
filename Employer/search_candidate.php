<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechFit Employer - Search Candidates</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }
        .container {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .table-container {
            flex: 1;
        }
        header, footer {
            background-color: #333; /* Assuming the header's background color is #333 */
            color: #fff; /* Assuming the text color is white */
        }
        footer {
            padding: 20px;
            text-align: center;
        }
        .popup {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #1e1e1e;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
        .popup h2 {
            color: #fff;
        }
        .popup button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .popup .close-button {
            background-color: #dc3545;
            color: #fff;
        }
        .popup .cancel-button {
            background-color: #007bff;
            color: #fff;
        }
        .popup .close-button:hover {
            background-color: #c82333;
        }
        .popup .cancel-button:hover {
            background-color: #0056b3;
        }
        .actions .accept:hover {
            color: green;
        }
        .actions .reject:hover {
                color: red;
        }
        .remove-button {
            background: none;
            border: none;
            color: red;
            cursor: pointer;
            font-size: 16px;
        }
        .remove-button:hover {
            color: darkred;
        }
        .remove-button {
        background: none;
        border: none;
        color: red;
        cursor: pointer;
        font-size: 16px;
        }
        .remove-button:hover {
            color: darkred;
        }
        .view {
            background: none;
            border: none;
            color: black;
            cursor: pointer;
            font-size: 16px;
        }
        .view:hover {
            color: #555;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <a href="index.html"><img src="images/logo.jpg" alt="TechFit Logo"></a>
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
                            <li><a href="useful_links.html">Useful Links</a></li>
                            <li><a href="faq.html">FAQ</a></li>
                            <li><a href="sitemap.html">Sitemap</a></li>
                        </ul>
                    </li>
                    <li><a href="about.html">About</a></li>
                    <li>
                        <a href="#" id="profile-link">
                            <div class="profile-info">
                                <span class="username" id="username">Employer</span>
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

    <!-- Logout Popup -->
    <div id="logout-popup" class="popup">
        <h2>Are you sure you want to Log Out?</h2>
        <button class="close-button" onclick="logoutUser()">Yes</button>
        <button class="cancel-button" onclick="closePopup('logout-popup')">No</button>
    </div>

    <div class="container">
        <div class="table-container">
            <div class="tabs">
                <button class="active" onclick="showTab('active')">Active</button>
                <button onclick="showTab('interested')">Interested</button>
                <button onclick="showTab('uninterested')">Uninterested</button>
                <button>Reviewed</button>
                <button class="import">Import Candidates</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>Name</th>
                        <th>Education Level</th>
                        <th>Years of Experience</th>
                        <th>Assessment Score</th>
                        <th>Interested?</th>
                    </tr>
                <tbody id="active-tab">
                    <?php
                    // Database connection
                    $servername = "localhost";
                    $username = "root";
                    $password = "";
                    $dbname = "techfit";

                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    if (!isset($_SESSION['employer_id'])) {
                        die("Employer not logged in.");
                    }

                    $employer_id = $_SESSION['employer_id']; // Get the logged-in employer's ID from the session

                    $sql = "SELECT ajs.score, js.user_id, u.first_name, u.last_name, js.education_level, js.year_of_experience, js.job_seeker_id
                            FROM Assessment_Job_Seeker ajs
                            JOIN Job_Seeker js ON ajs.job_seeker_id = js.job_seeker_id
                            JOIN User u ON js.user_id = u.user_id
                            LEFT JOIN Employer_Interest ei ON js.job_seeker_id = ei.job_seeker_id AND ei.employer_id = '$employer_id'
                            WHERE ei.interest_status IS NULL
                            LIMIT 8"; // Limit to 8 rows
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr id='row-" . $row['job_seeker_id'] . "'>";
                            echo "<td><input type='checkbox'></td>";
                            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                            echo "<td>" . $row['education_level'] . "</td>";
                            echo "<td>" . $row['year_of_experience'] . "</td>";
                            echo "<td>" . $row['score'] . "</td>";
                            echo "<td class='actions'>";
                            if (isset($row['job_seeker_id'])) {
                                echo "<button class='accept' onclick='updateInterest(\"" . $row['job_seeker_id'] . "\", \"interested\")'>✔</button>";
                                echo "<button class='reject' onclick='updateInterest(\"" . $row['job_seeker_id'] . "\", \"uninterested\")'>✖</button>";
                                echo "<button class='view' onclick='viewProfile(\"" . $row['job_seeker_id'] . "\")'>⋮</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr id='no-candidates-active'><td colspan='6'>No candidates found</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
                <tbody id="interested-tab" style="display:none;">
                    <?php
                    // Database connection
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $sql = "SELECT ajs.score, js.user_id, u.first_name, u.last_name, js.education_level, js.year_of_experience, js.job_seeker_id
                            FROM Assessment_Job_Seeker ajs
                            JOIN Job_Seeker js ON ajs.job_seeker_id = js.job_seeker_id
                            JOIN User u ON js.user_id = u.user_id
                            JOIN Employer_Interest ei ON js.job_seeker_id = ei.job_seeker_id
                            WHERE ei.employer_id = '$employer_id' AND ei.interest_status = 'interested'
                            LIMIT 8"; // Limit to 8 rows
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr id='row-" . $row['job_seeker_id'] . "'>";
                            echo "<td><input type='checkbox'></td>";
                            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                            echo "<td>" . $row['education_level'] . "</td>";
                            echo "<td>" . $row['year_of_experience'] . "</td>";
                            echo "<td>" . $row['score'] . "</td>";
                            echo "<td class='actions'>";
                            if (isset($row['job_seeker_id'])) {
                                echo "<button class='view' onclick='viewProfile(\"" . $row['job_seeker_id'] . "\")'>⋮</button>";
                                echo "<button class='remove-button' onclick='removeInterest(\"" . $row['job_seeker_id'] . "\")'>🗑</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr id='no-candidates-interested'><td colspan='6'>No candidates found</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
                <tbody id="uninterested-tab" style="display:none;">
                    <?php
                    // Database connection
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $sql = "SELECT ajs.score, js.user_id, u.first_name, u.last_name, js.education_level, js.year_of_experience, js.job_seeker_id
                            FROM Assessment_Job_Seeker ajs
                            JOIN Job_Seeker js ON ajs.job_seeker_id = js.job_seeker_id
                            JOIN User u ON js.user_id = u.user_id
                            JOIN Employer_Interest ei ON js.job_seeker_id = ei.job_seeker_id
                            WHERE ei.employer_id = '$employer_id' AND ei.interest_status = 'uninterested'
                            LIMIT 8"; // Limit to 8 rows
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr id='row-" . $row['job_seeker_id'] . "'>";
                            echo "<td><input type='checkbox'></td>";
                            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                            echo "<td>" . $row['education_level'] . "</td>";
                            echo "<td>" . $row['year_of_experience'] . "</td>";
                            echo "<td>" . $row['score'] . "</td>";
                            echo "<td class='actions'>";
                            if (isset($row['job_seeker_id'])) {
                                echo "<button class='view' onclick='viewProfile(\"" . $row['job_seeker_id'] . "\")'>⋮</button>";
                                echo "<button class='remove-button' onclick='removeInterest(\"" . $row['job_seeker_id'] . "\")'>🗑</button>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr id='no-candidates-uninterested'><td colspan='6'>No candidates found</td></tr>";
                    }

                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
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
                    <h3>Candidate</h3>
                    <ul>
                        <li><a href="search_candidate.php">Search Candidates</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Resources</h3>
                    <ul>
                        <li><a href="useful_links.html">Useful Links</a></li>
                        <li><a href="faq.html">FAQ</a></li>
                        <li><a href="sitemap.html">Sitemap</a></li>
                        <li><a href="about.html">About</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Contact</h3>
                    <ul>
                        <li><a href="contact.html">Contact Us</a></li>
                        <li><a href="feedback.php">Feedback</a></li>
                    </ul>
                </div>
                <div class="footer-column">
                    <h3>Legal</h3>
                    <ul>
                        <li><a href="terms.html">Terms of Service</a></li>
                        <li><a href="privacy.html">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 TechPathway: TechFit. All rights reserved.</p>
        </div>
    </footer>
    <script>
        function openPopup(popupId) {
            document.getElementById(popupId).style.display = 'block';
        }

        function closePopup(popupId) {
            document.getElementById(popupId).style.display = 'none';
        }

        function logoutUser() {
            window.location.href = '/Techfit'; // Redirect to the root directory
        }

        function updateInterest(jobSeekerId, interestStatus) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_interest.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Remove the row from the current tab
                    var row = document.getElementById('row-' + jobSeekerId);
                    if (row) {
                        row.parentNode.removeChild(row);
                    }

                    // Add the row to the appropriate tab
                    var newRow = document.createElement('tr');
                    newRow.id = 'row-' + jobSeekerId;
                    newRow.innerHTML = row.innerHTML;

                    if (interestStatus === 'interested') {
                        newRow.querySelector('.actions').innerHTML = "<button class='view' onclick='viewProfile(\"" + jobSeekerId + "\")'>⋮</button><button class='remove-button' onclick='removeInterest(\"" + jobSeekerId + "\")'>🗑</button>";
                        document.getElementById('interested-tab').appendChild(newRow);
                    } else if (interestStatus === 'uninterested') {
                        newRow.querySelector('.actions').innerHTML = "<button class='view' onclick='viewProfile(\"" + jobSeekerId + "\")'>⋮</button><button class='remove-button' onclick='removeInterest(\"" + jobSeekerId + "\")'>🗑</button>";
                        document.getElementById('uninterested-tab').appendChild(newRow);
                    }

                    // Remove "No candidates found" message if present
                    removeNoCandidatesMessage('interested-tab');
                    removeNoCandidatesMessage('uninterested-tab');
                }
            };
            xhr.send("job_seeker_id=" + jobSeekerId + "&interest_status=" + interestStatus);
        }

        function removeInterest(jobSeekerId) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "remove_interest.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    // Remove the row from the current tab
                    var row = document.getElementById('row-' + jobSeekerId);
                    if (row) {
                        row.parentNode.removeChild(row);
                    }

                    // Add the row back to the active tab
                    var newRow = document.createElement('tr');
                    newRow.id = 'row-' + jobSeekerId;
                    newRow.innerHTML = row.innerHTML;
                    newRow.querySelector('.actions').innerHTML = "<button class='accept' onclick='updateInterest(\"" + jobSeekerId + "\", \"interested\")'>✔</button><button class='reject' onclick='updateInterest(\"" + jobSeekerId + "\", \"uninterested\")'>✖</button><button class='view' onclick='viewProfile(\"" + jobSeekerId + "\")'>⋮</button>";
                    document.getElementById('active-tab').appendChild(newRow);

                    // Remove "No candidates found" message if present
                    removeNoCandidatesMessage('active-tab');
                }
            };
            xhr.send("job_seeker_id=" + jobSeekerId);
        }

        function removeNoCandidatesMessage(tabId) {
            var tab = document.getElementById(tabId);
            var noCandidatesRow = tab.querySelector('tr#no-candidates-' + tabId.split('-')[0]);
            if (noCandidatesRow) {
                tab.removeChild(noCandidatesRow);
            }
        }

        function viewProfile(jobSeekerId) {
            // Implement the logic to view the job seeker's profile
            alert("Viewing profile of job seeker ID: " + jobSeekerId);
        }

        function showTab(tabName) {
            document.getElementById('active-tab').style.display = 'none';
            document.getElementById('interested-tab').style.display = 'none';
            document.getElementById('uninterested-tab').style.display = 'none';
            document.getElementById(tabName + '-tab').style.display = 'table-row-group';
            document.querySelectorAll('.tabs button').forEach(button => button.classList.remove('active'));
            document.querySelector(`.tabs button[onclick="showTab('${tabName}')"]`).classList.add('active');
        }
    </script>
</body>
</html>