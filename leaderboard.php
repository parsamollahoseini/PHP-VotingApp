<?php
// Enable error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include necessary files
require_once 'classes/Logger.php';
require_once 'classes.php'; // Assuming all classes are included here
require_once 'classes/TimeFormatter.php';

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
    Logger::warning("Unauthorized access attempt to leaderboard");
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];

// Get all topics with vote counts
$topics = $topicObj->getTopics();

// Sort topics by vote score (upvotes - downvotes)
usort($topics, function($a, $b) {
    $scoreA = $a->upvotes - $a->downvotes;
    $scoreB = $b->upvotes - $b->downvotes;
    return $scoreB <=> $scoreA;
});

Logger::info("Leaderboard accessed by user: $username");

// Get theme
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Voting System</title>
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

    <h1>Top Voted Topics</h1>

    <div class="leaderboard">
        <?php if (empty($topics)): ?>
            <p>No topics available.</p>
        <?php else: ?>
            <?php foreach ($topics as $index => $topic): ?>
                <div class="leaderboard-item">
                    <div class="rank">#<?php echo $index + 1; ?></div>
                    <div class="topic-info">
                        <h2><?php echo htmlspecialchars($topic->title); ?></h2>
                        <p class="description"><?php echo htmlspecialchars($topic->description); ?></p>
                        <p class="creator">Created by: <?php echo htmlspecialchars($topic->creatorName); ?></p>
                        <div class="vote-stats">
                            <span class="upvotes">👍 <?php echo $topic->upvotes; ?></span>
                            <span class="downvotes">👎 <?php echo $topic->downvotes; ?></span>
                            <span class="score">Score: <?php echo $topic->upvotes - $topic->downvotes; ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

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
<h2>Source Code: leaderboard.php</h2>
<?php show_source(__FILE__); ?>
</body>
</html>
