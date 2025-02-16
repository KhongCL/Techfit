<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "techfit";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$admin_key = "techfit";
if (!isset($_GET['key']) || $_GET['key'] !== $admin_key) {
    die("Access denied.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $_SESSION['entered_username'] = $username;

    $stmt = $conn->prepare("SELECT * FROM User WHERE BINARY username=? AND role='Admin'");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            
            header("Location: admin/index.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Invalid password.";
            header("Location: admin_login.php?key=techfit");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "No admin user found with that username.";
        header("Location: admin_login.php?key=techfit");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - TechFit</title>
    <script src="scripts.js?v=1.0"></script>
    <style>
    body {
        background-color: #121212;
        color: #ffffff;
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .logo {
        position: absolute;
        top: 10px;
        left: 20px;
    }

    .logo img {
        height: 50px;
    }

    h2 {
        margin-top: 0;
    }

    .container {
        background-color: #1e1e1e;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        width: 400px;
        text-align: center;
    }
    .container img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin-bottom: 20px;
    }
    input[type="text"], input[type="password"] {
        width: calc(100% - 20px);
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc; /* Original border style kept */
        border-radius: 5px;
        background-color: #333;
        color: #fff;
    }
    input[type="submit"] {
        width: calc(100% - 20px);
        padding: 10px 20px;
        margin: 10px 0;
        border: none;
        border-radius: 5px;
        background-color: #007bff;
        color: #fff;
        cursor: pointer;
    }

    .error-message {
        color: red;
        font-weight: bold;
    }

    a {
        color: #007bff;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }

    label {
    display: block;
    text-align: left;
    margin-bottom: 5px;
    }
    <style>
    body {
        background-color: #121212;
        color: #ffffff;
        font-family: Arial, sans-serif;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        margin: 0;
    }

    .logo {
        position: absolute;
        top: 10px;
        left: 20px;
    }
    .logo img {
        height: 50px;
    }

    h2 {
        margin-top: 0;
    }

    .container {
        background-color: #1e1e1e;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        width: 400px;
        text-align: center;
    }
    .container img {
        width: 100px;
        height: 100px;
        border-radius: 50%;
        margin-bottom: 20px;
    }
    input[type="text"], input[type="password"] {
        width: calc(100% - 20px);
        padding: 10px;
        margin: 10px 0;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #333;
        color: #fff;
    }
    input[type="submit"] {
        width: calc(100% - 20px);
        padding: 10px 20px;
        margin: 10px 0;
        border: none;
        border-radius: 5px;
        background-color: #007bff;
        color: #fff;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #0056b3;
    }

    .error-message {
        color: red;
        font-weight: bold;
    }

    a {
        color: #007bff;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }

    label {
    display: block;
    text-align: left;
    margin-bottom: 5px;
    }
</style>

</head>
<body>
    <div class="logo">
        <a href="index.php"><img src="images/logo.jpg" alt="TechFit Logo"></a>
    </div>
    <div class="container">
        <h2>Admin Login</h2>
        <?php
        if (isset($_SESSION['error_message'])) {
            echo '<p class="error-message">' . $_SESSION['error_message'] . '</p>';
            unset($_SESSION['error_message']);
        }
        ?>
        <img src="images/usericon.png" alt="User Icon">
        <form action="admin_login.php?key=techfit" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username"
                value="<?php echo isset($_SESSION['entered_username']) ? htmlspecialchars($_SESSION['entered_username']) : ''; ?>" required><br>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>
            <input type="submit" value="Login">
        </form>
        <p>Don't have an account? <a href="admin_register.php?key=techfit">Sign up here</a></p>
    </div>
</body>
</html>