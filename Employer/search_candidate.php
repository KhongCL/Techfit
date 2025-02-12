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
                            <li><a href="#" >Logout</a></li>
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
    
    <div style="text-align: center; padding-top: 50px; color: white;">
        <h1>Search Candidates</h1>
    </div>
    <div class="container">
        <div class="table-container">
            <div class="sort-controls-wrapper">
                <div class="sort-controls">
                    <span>Sort by:</span>
                    <select id="sortDropdown">
                        <option value="none">None</option>
                        <option value="name_asc">Name ASC</option>
                        <option value="name_desc">Name DESC</option>
                        <option value="education_level_asc">Education Level ASC</option>
                        <option value="education_level_desc">Education Level DESC</option>
                        <option value="years_of_experience_asc">Years of Experience ASC</option>
                        <option value="years_of_experience_desc">Years of Experience DESC</option>
                        <option value="assessment_scores_asc">Assessment Scores ASC</option>
                        <option value="assessment_scores_desc">Assessment Scores DESC</option>
                    </select>
                    <div class="search-container">
                        <input type="text" id="searchInput" placeholder="Search...">
                        <span id="clearSearch">&#x2715;</span>
                        <div id="noMatchesPopup">No matches found.</div>
                    </div>
                </div>
            </div>
            <div class="tabs">
                <button class="active" onclick="showTab('active')">Active</button>
                <button onclick="showTab('interested')">Interested</button>
                <button onclick="showTab('uninterested')">Uninterested</button>
                <button onclick="showTab('view-deleted-candidates')">View Deleted Candidates</button>
            </div>
            <table>
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th data-column="name">Name</th>
                        <th data-column="education_level">Education Level</th>
                        <th data-column="years_of_experience">Years of Experience</th>
                        <th data-column="assessment_scores">Assessment Scores</th>
                        <th>Interested?</th>
                    </tr>
                </thead>
                <tbody id="active-tab">
                    <?php
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

                    $employer_id = $_SESSION['employer_id'];

                    $sql = "SELECT js.user_id, u.first_name, u.last_name, js.education_level, js.year_of_experience, js.job_seeker_id,
                                GROUP_CONCAT(ajs.score ORDER BY ajs.assessment_id SEPARATOR ', ') AS scores,
                                AVG(ajs.score) AS avg_score
                            FROM Job_Seeker js
                            JOIN User u ON js.user_id = u.user_id
                            LEFT JOIN Assessment_Job_Seeker ajs ON js.job_seeker_id = ajs.job_seeker_id
                            LEFT JOIN Employer_Interest ei ON js.job_seeker_id = ei.job_seeker_id AND ei.employer_id = '$employer_id'
                            WHERE ei.employer_id IS NULL AND ajs.assessment_id IS NOT NULL
                            GROUP BY js.job_seeker_id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr id='row-" . $row['job_seeker_id'] . "'>";
                            echo "<td><input type='checkbox'></td>";
                            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                            
                                $education_level = !empty($row['education_level']) ? htmlspecialchars($row['education_level']) : 'null';
                            echo "<td>" . $education_level . "</td>";
                            
                                $experience = (!empty($row['year_of_experience']) || $row['year_of_experience'] === '0') ? htmlspecialchars($row['year_of_experience']) : 'null';
                            echo "<td>" . $experience . "</td>";
                            
                                $scores_display = !empty($row['scores']) ? htmlspecialchars($row['scores']) : 'null';
                            echo "<td>" . $scores_display . "</td>";
                            echo "<td class='actions'>";
                            if (isset($row['job_seeker_id'])) {
                                echo "<button class='accept' onclick='updateInterest(\"" . $row['job_seeker_id'] . "\", \"interested\")'>✔</button>";
                                echo "<button class='reject' onclick='updateInterest(\"" . $row['job_seeker_id'] . "\", \"uninterested\")'>✖</button>";
                                echo "<a href='candidate_answer.php?job_seeker_id=" . $row['job_seeker_id'] . "' class='view'>View</a>";
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

                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $sql = "SELECT js.user_id, u.first_name, u.last_name, js.education_level, js.year_of_experience, js.job_seeker_id,
                                GROUP_CONCAT(ajs.score ORDER BY ajs.assessment_id SEPARATOR ', ') AS scores,
                                AVG(ajs.score) AS avg_score
                            FROM Job_Seeker js
                            JOIN User u ON js.user_id = u.user_id
                            LEFT JOIN Assessment_Job_Seeker ajs ON js.job_seeker_id = ajs.job_seeker_id
                            JOIN Employer_Interest ei ON js.job_seeker_id = ei.job_seeker_id
                            WHERE ei.employer_id = '$employer_id' AND ei.interest_status = 'interested' AND ei.is_active = 1 AND ajs.assessment_id IS NOT NULL
                            GROUP BY js.job_seeker_id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr id='row-" . $row['job_seeker_id'] . "'>";
                            echo "<td><input type='checkbox'></td>";
                            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                            
                                $education_level = !empty($row['education_level']) ? htmlspecialchars($row['education_level']) : 'null';
                            echo "<td>" . $education_level . "</td>";
                            
                                $experience = (!empty($row['year_of_experience']) || $row['year_of_experience'] === '0') ? htmlspecialchars($row['year_of_experience']) : 'null';
                            echo "<td>" . $experience . "</td>";
                            
                                $scores_display = !empty($row['scores']) ? htmlspecialchars($row['scores']) : 'null';
                            echo "<td>" . $scores_display . "</td>";
                            echo "<td class='actions'>";
                            if (isset($row['job_seeker_id'])) {
                                echo "<a href='candidate_answer.php?job_seeker_id=" . $row['job_seeker_id'] . "' class='view'>View</a>";
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

                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $sql = "SELECT js.user_id, u.first_name, u.last_name, js.education_level, js.year_of_experience, js.job_seeker_id,
                                GROUP_CONCAT(ajs.score ORDER BY ajs.assessment_id SEPARATOR ', ') AS scores,
                                AVG(ajs.score) AS avg_score
                            FROM Job_Seeker js
                            JOIN User u ON js.user_id = u.user_id
                            LEFT JOIN Assessment_Job_Seeker ajs ON js.job_seeker_id = ajs.job_seeker_id
                            JOIN Employer_Interest ei ON js.job_seeker_id = ei.job_seeker_id
                            WHERE ei.employer_id = '$employer_id' AND ei.interest_status = 'uninterested' AND ei.is_active = 1 AND ajs.assessment_id IS NOT NULL
                            GROUP BY js.job_seeker_id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr id='row-" . $row['job_seeker_id'] . "'>";
                            echo "<td><input type='checkbox'></td>";
                            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                            
                                $education_level = !empty($row['education_level']) ? htmlspecialchars($row['education_level']) : 'null';
                            echo "<td>" . $education_level . "</td>";
                            
                                $experience = (!empty($row['year_of_experience']) || $row['year_of_experience'] === '0') ? htmlspecialchars($row['year_of_experience']) : 'null';
                            echo "<td>" . $experience . "</td>";
                            
                                $scores_display = !empty($row['scores']) ? htmlspecialchars($row['scores']) : 'null';
                            echo "<td>" . $scores_display . "</td>";
                            echo "<td class='actions'>";
                            if (isset($row['job_seeker_id'])) {
                                echo "<a href='candidate_answer.php?job_seeker_id=" . $row['job_seeker_id'] . "' class='view'>View</a>";
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

                <tbody id="view-deleted-candidates-tab" style="display:none;">
                    <?php
                    $conn = new mysqli($servername, $username, $password, $dbname);

                    if ($conn->connect_error) {
                        die("Connection failed: " . $conn->connect_error);
                    }

                    $sql = "SELECT js.user_id, u.first_name, u.last_name, js.education_level, js.year_of_experience, js.job_seeker_id,
                                GROUP_CONCAT(ajs.score ORDER BY ajs.assessment_id SEPARATOR ', ') AS scores,
                                AVG(ajs.score) AS avg_score
                            FROM Job_Seeker js
                            JOIN User u ON js.user_id = u.user_id
                            LEFT JOIN Assessment_Job_Seeker ajs ON js.job_seeker_id = ajs.job_seeker_id
                            JOIN Employer_Interest ei ON js.job_seeker_id = ei.job_seeker_id
                            WHERE ei.employer_id = '$employer_id' AND ei.is_active = 0 AND ajs.assessment_id IS NOT NULL
                            GROUP BY js.job_seeker_id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr id='row-" . $row['job_seeker_id'] . "'>";
                            echo "<td><input type='checkbox'></td>";
                            echo "<td>" . $row['first_name'] . " " . $row['last_name'] . "</td>";
                            
                            
                            $education_level = !empty($row['education_level']) ? htmlspecialchars($row['education_level']) : 'null';
                            echo "<td>" . $education_level . "</td>";
                            
                            
                            $experience = (!empty($row['year_of_experience']) || $row['year_of_experience'] === '0') ? htmlspecialchars($row['year_of_experience']) : 'null';
                            echo "<td>" . $experience . "</td>";
                            
                            
                            $scores_display = !empty($row['scores']) ? htmlspecialchars($row['scores']) : 'null';
                            echo "<td>" . $scores_display . "</td>";
                            
                            echo "<td class='actions'>";
                            if (isset($row['job_seeker_id'])) {
                                echo "<button class='accept' onclick='updateInterest(\"" . $row['job_seeker_id'] . "\", \"interested\")'>✔</button>";
                                echo "<button class='reject' onclick='updateInterest(\"" . $row['job_seeker_id'] . "\", \"uninterested\")'>✖</button>";
                                echo "<a href='candidate_answer.php?job_seeker_id=" . $row['job_seeker_id'] . "' class='view'>View</a>";
                            }
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr id='no-candidates-deleted'><td colspan='6'>No candidates found</td></tr>";
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
    <script src="scripts.js"></script>  
    <script>
    function updateInterest(jobSeekerId, interestStatus) {
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "update_interest.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                
                var row = document.getElementById('row-' + jobSeekerId);
                if (row) {
                    row.parentNode.removeChild(row);
                }

                
                var newRow = document.createElement('tr');
                newRow.id = 'row-' + jobSeekerId;
                newRow.innerHTML = row.innerHTML;

                if (interestStatus === 'interested') {
                    newRow.querySelector('.actions').innerHTML = "<a href='candidate_answer.php?job_seeker_id=" + jobSeekerId + "' class='view'>View</a><button class='remove-button' onclick='removeInterest(\"" + jobSeekerId + "\")'>🗑</button>";
                    document.getElementById('interested-tab').appendChild(newRow);
                } else if (interestStatus === 'uninterested') {
                    newRow.querySelector('.actions').innerHTML = "<a href='candidate_answer.php?job_seeker_id=" + jobSeekerId + "' class='view'>View</a><button class='remove-button' onclick='removeInterest(\"" + jobSeekerId + "\")'>🗑</button>";
                    document.getElementById('uninterested-tab').appendChild(newRow);
                }

                
                removeNoCandidatesMessage('interested-tab');
                removeNoCandidatesMessage('uninterested-tab');

                
                fetchDeletedCandidates();
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
                
                var row = document.getElementById('row-' + jobSeekerId);
                if (row) {
                    row.parentNode.removeChild(row);
                }

                
                var newRow = document.createElement('tr');
                newRow.id = 'row-' + jobSeekerId;
                newRow.innerHTML = row.innerHTML;
                newRow.querySelector('.actions').innerHTML = "<button class='accept' onclick='updateInterest(\"" + jobSeekerId + "\", \"interested\")'>✔</button><button class='reject' onclick='updateInterest(\"" + jobSeekerId + "\", \"uninterested\")'>✖</button><a href='candidate_answer.php?job_seeker_id=" + jobSeekerId + "' class='view'>View</a>";

                
                removeNoCandidatesMessage('active-tab');

                
                fetchDeletedCandidates();
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

    function showTab(tabName) {
        document.getElementById('active-tab').style.display = 'none';
        document.getElementById('interested-tab').style.display = 'none';
        document.getElementById('uninterested-tab').style.display = 'none';
        document.getElementById('view-deleted-candidates-tab').style.display = 'none';
        document.getElementById(tabName + '-tab').style.display = 'table-row-group';
        document.querySelectorAll('.tabs button').forEach(button => button.classList.remove('active'));
        document.querySelector(`.tabs button[onclick="showTab('${tabName}')"]`).classList.add('active');

        if (tabName === 'view-deleted-candidates') {
            fetchDeletedCandidates();
        }
    }

    function fetchDeletedCandidates() {
        const xhr = new XMLHttpRequest();
        xhr.open('GET', 'fetch_deleted_candidates.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === 4 && xhr.status === 200) {
                const deletedCandidates = JSON.parse(xhr.responseText);
                const tbody = document.getElementById('view-deleted-candidates-tab');
                tbody.innerHTML = '';

                deletedCandidates.forEach(candidate => {
                    const row = document.createElement('tr');
                    row.id = 'row-' + candidate.job_seeker_id;
                    row.innerHTML = `
                        <td><input type='checkbox'></td>
                        <td>${candidate.name}</td>
                        <td>${candidate.education_level}</td>
                        <td>${candidate.years_of_experience}</td>
                        <td>${candidate.assessment_scores}</td>
                        <td class="actions">
                            <button class="accept" onclick="updateInterest('${candidate.job_seeker_id}', 'interested')">✔</button>
                            <button class="reject" onclick="updateInterest('${candidate.job_seeker_id}', 'uninterested')">✖</button>
                            <a href="candidate_answer.php?job_seeker_id=${candidate.job_seeker_id}" class="view">View</a>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }
        };
        xhr.send();
    }

    document.addEventListener('DOMContentLoaded', function() {
        
        document.getElementById('sortDropdown').addEventListener('change', function() {
            const value = this.value;
            const activeTab = document.getElementById('active-tab');
            const interestedTab = document.getElementById('interested-tab');
            const uninterestedTab = document.getElementById('uninterested-tab');
            const deletedTab = document.getElementById('view-deleted-candidates-tab');
            const tabs = [activeTab, interestedTab, uninterestedTab, deletedTab];

            let columnIndex, order;

            switch (value) {
                case 'name_asc':
                    columnIndex = 2;
                    order = 1;
                    break;
                case 'name_desc':
                    columnIndex = 2;
                    order = -1;
                    break;
                case 'education_level_asc':
                    columnIndex = 3;
                    order = 1;
                    break;
                case 'education_level_desc':
                    columnIndex = 3;
                    order = -1;
                    break;
                case 'years_of_experience_asc':
                    columnIndex = 4;
                    order = 1;
                    break;
                case 'years_of_experience_desc':
                    columnIndex = 4;
                    order = -1;
                    break;
                case 'assessment_scores_asc':
                    columnIndex = 5;
                    order = 1;
                    break;
                case 'assessment_scores_desc':
                    columnIndex = 5;
                    order = -1;
                    break;
                default:
                    return;
            }

            tabs.forEach(tab => {
                const rows = Array.from(tab.querySelectorAll('tr'));
                rows.sort((a, b) => {
                    const aText = a.querySelector(`td:nth-child(${columnIndex})`).textContent.trim();
                    const bText = b.querySelector(`td:nth-child(${columnIndex})`).textContent.trim();
                    if (columnIndex === 4 || columnIndex === 5) { 
                        const aValue = columnIndex === 5 ? calculateAverage(aText) : parseFloat(aText);
                        const bValue = columnIndex === 5 ? calculateAverage(bText) : parseFloat(bText);
                        return (aValue - bValue) * order;
                    }
                    return aText.localeCompare(bText, undefined, {numeric: true}) * order;
                });
                rows.forEach(row => tab.appendChild(row));
            });
        });

        function calculateAverage(scoresText) {
            const scores = scoresText.split(', ').map(Number);
            return scores.reduce((sum, score) => sum + score, 0) / scores.length;
        }

        
        document.querySelectorAll('th[data-column]').forEach(th => {
            th.addEventListener('click', function() {
                const column = this.getAttribute('data-column');
                const currentOrder = this.dataset.order || -1;
                const order = this.dataset.order = currentOrder * -1; 
                console.log(`Sorting table column: ${column}, Order: ${order}`); 
                const rows = Array.from(document.querySelectorAll('#active-tab tr, #interested-tab tr, #uninterested-tab tr, #view-deleted-candidates-tab tr'));
                rows.sort((a, b) => {
                    const aText = a.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                    const bText = b.querySelector(`td:nth-child(${this.cellIndex + 1})`).textContent.trim();
                    if (column === 'years_of_experience' || column === 'assessment_scores') { 
                        const aValue = column === 'assessment_scores' ? calculateAverage(aText) : parseFloat(aText);
                        const bValue = column === 'assessment_scores' ? calculateAverage(bText) : parseFloat(bText);
                        return (aValue - bValue) * order;
                    }
                    return aText.localeCompare(bText, undefined, {numeric: true}) * order;
                });
                rows.forEach(row => row.parentNode.appendChild(row));

                
                document.querySelectorAll('th[data-column]').forEach(th => th.classList.remove('asc', 'desc'));
                this.classList.add(order === 1 ? 'asc' : 'desc');
            });
        });

        
        document.getElementById('searchInput').addEventListener('input', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#active-tab tr, #interested-tab tr, #uninterested-tab tr, #view-deleted-candidates-tab tr');
            let matchFound = false;
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const match = Array.from(cells).some(cell => cell.textContent.toLowerCase().includes(filter));
                row.style.display = match ? '' : 'none';
                if (match) matchFound = true;
            });
            const noMatchesPopup = document.getElementById('noMatchesPopup');
            if (!matchFound) {
                noMatchesPopup.style.display = 'block';
                noMatchesPopup.style.opacity = '1';
            } else {
                noMatchesPopup.style.display = 'none';
            }
            document.getElementById('clearSearch').style.display = filter ? 'block' : 'none';
        });

        document.getElementById('clearSearch').addEventListener('click', function() {
            document.getElementById('searchInput').value = '';
            const rows = document.querySelectorAll('#active-tab tr, #interested-tab tr, #uninterested-tab tr, #view-deleted-candidates-tab tr');
            rows.forEach(row => {
                row.style.display = '';
            });
            this.style.display = 'none';
            document.getElementById('noMatchesPopup').style.display = 'none';
        });

        document.getElementById('searchInput').addEventListener('focus', function() {
            const noMatchesPopup = document.getElementById('noMatchesPopup');
            if (this.value && !Array.from(document.querySelectorAll('#active-tab tr, #interested-tab tr, #uninterested-tab tr, #view-deleted-candidates-tab tr')).some(row => row.style.display !== 'none')) {
                noMatchesPopup.style.display = 'block';
                noMatchesPopup.style.opacity = '1';
            }
        });

        document.addEventListener('click', function(event) {
            const noMatchesPopup = document.getElementById('noMatchesPopup');
            if (!document.getElementById('searchInput').contains(event.target) && !noMatchesPopup.contains(event.target)) {
                noMatchesPopup.style.display = 'none';
            }
        });
    });
</script>
</body>
</html>