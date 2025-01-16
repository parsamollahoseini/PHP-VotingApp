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
// require_once 'classes/User.php';
// require_once 'classes/Topic.php';
// require_once 'classes/Vote.php';
require_once 'classes.php';

// Load database configuration
$config = require 'db.config.php';

try {
    // Create PDO instance
    $dsn = "mysql:host={$config['app']['host']};dbname={$config['app']['dbname']}";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_TIMEOUT => 5,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
        PDO::ATTR_EMULATE_PREPARES => true
    ];
    
    $pdo = new PDO($dsn, $config['app']['username'], $config['app']['password'], $options);
    Logger::info("Database connection established successfully");
} catch (PDOException $e) {
    Logger::error("Database connection failed: " . $e->getMessage());
    die("Database connection failed: " . $e->getMessage());
}

// Initialize classes
$userObj = new User($pdo);
$topicObj = new Topic($pdo);
$voteObj = new Vote($pdo);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    Logger::warning("Unauthorized access attempt to dashboard");
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle topic creation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    
    if (empty($title) || empty($description)) {
        $error = 'Please fill in all fields';
        Logger::warning("Topic creation failed: Empty fields by user $username");
    } else {
        if ($topicObj->createTopic((int)$userId, $title, $description)) {
            $success = 'Topic created successfully!';
            Logger::info("New topic created by user $username: $title");
            header('Location: topics.php');
            exit;
        } else {
            $error = 'Failed to create topic';
            Logger::error("Topic creation failed for user $username");
        }
    }
}

// Get user stats
$topics = $topicObj->getCreatedTopics($userId);
$topicsCreated = count($topics);
$voteHistory = $voteObj->getUserVoteHistory($userId);
$votesCast = count($voteHistory);

// Get theme
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
Logger::info("Dashboard accessed by user: $username");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Voting System</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="<?php echo htmlspecialchars($theme); ?>">
    <div class="container">
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="topics.php">View Topics</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="profile.php">My Profile</a>
            <a href="professor.php">For Prof</a>
            <a href="logout.php">Logout</a>
            <a href="https://f4537771.gblearn.com/comp1230/assignments/project/" target="_blank">Teammate GbLearn</a>
        </nav>
        
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        
        <div class="stats">
            <p>Topics Created: <?php echo $topicsCreated; ?></p>
            <p>Votes Cast: <?php echo $votesCast; ?></p>
        </div>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <h2>Create New Topic</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required></textarea>
            </div>
            
            <button type="submit">Create Topic</button>
        </form>
        
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
