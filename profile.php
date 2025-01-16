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
require_once 'classes/Logger.php';
// require_once 'classes/User.php';
// require_once 'classes/Topic.php';
// require_once 'classes/Vote.php';
// require_once 'classes/Comment.php';
require_once 'classes.php';

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
$commentObj = new Comment($pdo);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    Logger::warning("Unauthorized access attempt to profile page");
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$userId = $_SESSION['user_id'];

// Get user stats and history
$topics = $topicObj->getCreatedTopics($userId);
$topicsCreated = count($topics);

$votingHistory = $voteObj->getUserVoteHistory($userId);
$votesCast = count($votingHistory);

$userComments = $commentObj->getUserComments($userId);

Logger::info("Profile accessed by user: $username");

// Get theme
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Voting System</title>
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
        
        <h1>Profile: <?php echo htmlspecialchars($username); ?></h1>
        
        <div class="stats">
            <h2>Statistics</h2>
            <p>Topics Created: <?php echo $topicsCreated; ?></p>
            <p>Votes Cast: <?php echo $votesCast; ?></p>
            <p>Comments Made: <?php echo count($userComments); ?></p>
        </div>
        
        <div class="user-content">
            <h2>Your Topics</h2>
            <?php if (empty($topics)): ?>
                <p>You haven't created any topics yet.</p>
            <?php else: ?>
                <div class="topics-list">
                    <?php foreach ($topics as $topic): ?>
                        <div class="topic-item">
                            <h3><?php echo htmlspecialchars($topic['title']); ?></h3>
                            <p><?php echo htmlspecialchars($topic['description']); ?></p>
                            <div class="vote-stats">
                                <span>Upvotes: <?php echo $topic['upvotes']; ?></span>
                                <span>Downvotes: <?php echo $topic['downvotes']; ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h2>Voting History</h2>
            <?php if (empty($votingHistory)): ?>
                <p>You haven't voted on any topics yet.</p>
            <?php else: ?>
                <div class="voting-history">
                    <?php foreach ($votingHistory as $vote): ?>
                        <div class="vote-item">
                            <h3><?php echo htmlspecialchars($vote['topic_title']); ?></h3>
                            <p>Vote: <?php echo ucfirst($vote['vote_type']); ?></p>
                            <small>Voted: <?php echo TimeFormatter::formatTimestamp(strtotime($vote['voted_at'])); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h2>Your Comments</h2>
            <?php if (empty($userComments)): ?>
                <p>You haven't made any comments yet.</p>
            <?php else: ?>
                <div class="comments-list">
                    <?php foreach ($userComments as $comment): ?>
                        <div class="comment-item">
                            <h3>On: <?php echo htmlspecialchars($comment['topic_title']); ?></h3>
                            <p><?php echo htmlspecialchars($comment['comment']); ?></p>
                            <small>Posted: <?php echo $comment['formatted_time']; ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
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
    <h2>Source Code: index.php</h2>
    <?php show_source(__FILE__); ?>
</body>
</html>
