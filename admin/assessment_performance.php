<?php
session_start();

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
        window.location.href = "login.php";
    </script>';
    exit();
}

if (!isset($_SESSION['user_id'])) {
    displayLoginMessage();
}

if ($_SESSION['role'] !== 'Admin') {
    displayLoginMessage();
}

$sql = "
    SELECT 
        passing_score_percentage
    FROM assessment_settings
    WHERE setting_id = '1'
    LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

$passing_score_percentage = null;
if ($row = $result->fetch_assoc()) {
    $passing_score_percentage = $row['passing_score_percentage'];
}

if ($passing_score_percentage === null) {
    echo "<script>console.error('Error: Passing score percentage is null');</script>";
    exit();
}

$sql = "
    SELECT 
        score
    FROM assessment_job_seeker";

$stmt = $conn->prepare($sql);

$stmt->execute();
$result = $stmt->get_result();

$pass = 0;
$fail = 0;
$total_assessments = 0;
$total_score = 0;
$average_score=0;

while ($row = $result->fetch_assoc()) {
    $score = $row['score'] ?? null;

    echo "<script>console.log('Score: " . $score . ", Passing Score: " . $passing_score_percentage . "');</script>";

    if ($score !== null) {
        $total_assessments++;
        $total_score += $score;
        if ($score >= $passing_score_percentage) {
            $pass++;
        } else {
            $fail++;
        }
    } else {
        echo "<script>console.error('Error: Score is null');</script>";
    }
}

$average_score = $total_assessments > 0 ? $total_score / $total_assessments : 0;
$average_score = number_format($average_score, 2);

session_write_close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TechFit</title>
    <link rel="stylesheet" href="styles.css"> 
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
    <style>
        .chart-container {
            width: 50%;
            margin: auto;
            text-align: center;
        }

        h2{
            color: white;
        }

        h5{
            font-size: 20px;
            text-align: center;
            color: white;
            margin-bottom: 0px;
            font-weight: normal;
        }

        li {
            color: white;
        }
    </style>
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
        
    <h2 style="text-align: center;" ><br>Assessment Performance</h2>
         <h6></h6>                           
    <div class="chart-container">
        <canvas id="assessmentChart">
        </canvas>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const passCount = <?php echo json_encode($pass); ?>;
            const failCount = <?php echo json_encode($fail); ?>;

            console.log("Pass Count: ", passCount); 
            console.log("Fail Count: ", failCount); 

            const ctx = document.getElementById('assessmentChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Pass', 'Fail'],
                    datasets: [{
                        data: [passCount, failCount],
                        backgroundColor: ['#4CAF50', '#FF5733']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom', 
                            labels: {
                                padding: 20, 
                                align: 'center',
                                boxWidth: 40,
                            }
                        },
                        datalabels: {
                            color: 'white',
                            font: {
                                weight: 'bold',
                                size: 16
                            },
                            formatter: (value, ctx) => {
                                return value;
                            }
                        }
                    },
                    layout: {
                        padding: {
                            bottom: 10 
                        }
                    }
                }
            });
        });
    </script>

    
    <h5>Total Assessment: <?php echo $total_assessments; ?></h5>
    <h5>Average Scores across Assessment: <?php echo $average_score; ?>%</h5>
        <h6></h6>
    
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