<?php
// Set default timezone
date_default_timezone_set('America/Toronto');

// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include necessary files
require_once 'classes/TimeFormatter.php';
// require_once 'classes/User.php';
// require_once 'classes/Topic.php';
// require_once 'classes/Vote.php';
require_once 'classes.php';


// Load database configuration
$config = require 'db.config.php';

try {
    // Create PDO instance with the working configuration
    $dsn = "mysql:host={$config['app']['host']};dbname={$config['app']['dbname']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_TIMEOUT => 5,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_EMULATE_PREPARES => true
    ];
    
    $pdo = new PDO($dsn, $config['app']['username'], $config['app']['password'], $options);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize classes
$userObj = new User($pdo);
$topicObj = new Topic($pdo);
$voteObj = new Vote($pdo);
$commentObj = new Comment($pdo);

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        if ($userObj->authenticateUser($username, $password)) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $userObj->getUserId($username);
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password';
        }
    }
}

// Get theme
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo $theme; ?>">
    <div class="container">
        <h1>Login</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit">Login</button>
        </form>
        
        <p>Don't have an account? <a href="register.php">Register here</a></p>
        
        <div class="theme-selector">
            <form method="POST" action="set_theme.php">
                <label>Theme:</label>
                <select name="theme" onchange="this.form.submit()">
                    <option value="light" <?php echo $theme === 'light' ? 'selected' : ''; ?>>Light</option>
                    <option value="dark" <?php echo $theme === 'dark' ? 'selected' : ''; ?>>Dark</option>
                </select>
            </form>
        </div>
    </div>

    <h2>Source Code: index.php</h2>
    <?php show_source(__FILE__); ?>
</body>
</html>
