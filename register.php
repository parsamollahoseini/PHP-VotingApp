<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include necessary files
require_once 'classes/Logger.php';
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

// Initialize User class
$userObj = new User($pdo);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
        Logger::warning("Registration attempt failed: Empty fields");
    } else {
        if ($userObj->registerUser($username, $email, $password)) {
            $success = 'Registration successful! Please login.';
            Logger::info("New user registered successfully: $username");
            header('Refresh: 2; URL=index.php');
        } else {
            $error = 'Registration failed. Please check your inputs.';
            Logger::warning("Registration failed for username: $username");
        }
    }
}

// Get theme
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
Logger::info("Using theme: $theme");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo htmlspecialchars($theme); ?>">
    <div class="container">
        <h1>Register</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required 
                       minlength="9" 
                       title="Password must be at least 9 characters long">
            </div>
            
            <button type="submit">Register</button>
        </form>
        
        <p>Already have an account? <a href="index.php">Login here</a></p>
        
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

    <h2>Source Code: register.php</h2>
    <?php show_source(__FILE__); ?>
</body>
</html>
