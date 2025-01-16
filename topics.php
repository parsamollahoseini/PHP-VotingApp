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
$commentObj = new Comment($pdo);

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    Logger::warning("Unauthorized access attempt to topics page");
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'];
$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle voting
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['voteType'])) {
        $topicId = $_POST['topicId'] ?? '';
        $voteType = $_POST['voteType'] ?? '';

        Logger::info("Vote attempt - Topic ID: $topicId, Vote Type: $voteType, User: $username");

        if (empty($topicId) || !in_array($voteType, ['up', 'down'])) {
            $error = 'Invalid vote';
            Logger::warning("Invalid vote parameters from user $username");
        } else {
            if (!$voteObj->hasVoted($topicId, $userId)) {
                if ($voteObj->vote($userId, $topicId, $voteType)) { // Correct parameter order
                    $success = 'Vote recorded successfully!';
                    Logger::info("Vote recorded - Topic: $topicId, Type: $voteType, User: $username");
                } else {
                    $error = 'Failed to record vote';
                    Logger::error("Vote recording failed - Topic: $topicId, User: $username");
                }
            } else {
                $error = 'You have already voted on this topic';
                Logger::warning("Duplicate vote attempt - Topic: $topicId, User: $username");
            }
        }
    } elseif (isset($_POST['comment'])) {
        $topicId = $_POST['topicId'] ?? '';
        $comment = trim($_POST['comment']) ?? '';

        Logger::info("Comment attempt - Topic ID: $topicId, User: $username");

        if (empty($comment)) {
            $error = 'Comment cannot be empty';
            Logger::warning("Empty comment attempt from user $username");
        } else {
            if ($commentObj->addComment($userId, $topicId, $comment)) {
                $success = 'Comment added successfully!';
                Logger::info("Comment added - Topic: $topicId, User: $username");
            } else {
                $error = 'Failed to add comment';
                Logger::error("Comment addition failed - Topic: $topicId, User: $username");
            }
        }
    }
    $topics = $topicObj->getTopics();
}

// Get all topics
$topics = $topicObj->getTopics();
Logger::info("Retrieved " . count($topics) . " topics for display");

// Get theme
$theme = $_SESSION['theme'] ?? 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Topics - Voting System</title>
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
        <a href="https://f4491591.gblearn.com/comp1230/assignments/assignment3/" target="_blank">Teammate GbLearn</a>
    </nav>

    <h1>Topics</h1>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <div class="topics">
        <?php if (empty($topics)): ?>
            <p>No topics available.</p>
        <?php else: ?>
            <?php foreach ($topics as $topic): ?>
                <div class="topic">
                    <h2><?php echo htmlspecialchars($topic->title); ?></h2>
                    <p class="description"><?php echo htmlspecialchars($topic->description); ?></p>
                    <p class="creator">Created by: <?php echo htmlspecialchars($topic->creatorName); ?></p>

                    <div class="vote-results">
                        <span>Upvotes: <?php echo $topic->upvotes; ?></span>
                        <span>Downvotes: <?php echo $topic->downvotes; ?></span>
                    </div>

                    <?php if (!$voteObj->hasVoted($userId, $topic->id)): ?>
                        <form method="POST" action="" class="vote-form">
                            <input type="hidden" name="topicId" value="<?php echo htmlspecialchars($topic->id); ?>">
                            <button type="submit" name="voteType" value="up">Upvote</button>
                            <button type="submit" name="voteType" value="down">Downvote</button>
                        </form>
                    <?php else: ?>
                        <p class="voted">You have already voted on this topic</p>
                    <?php endif; ?>

                    <!-- Comments Section -->
                    <div class="comments">
                        <h3>Comments</h3>
                        <?php
                        $comments = $commentObj->getComments($topic->id);
                        foreach ($comments as $comment):
                            ?>
                            <div class="comment">
                                <strong><?php echo htmlspecialchars($comment['commenter_name']); ?>:</strong>
                                <?php echo htmlspecialchars($comment['comment']); ?>
                                <small>(<?php echo htmlspecialchars($comment['formatted_time']); ?>)</small>
                            </div>
                        <?php endforeach; ?>

                        <!-- Comment Form -->
                        <form method="POST" action="" class="comment-form">
                            <input type="hidden" name="topicId" value="<?php echo htmlspecialchars($topic->id); ?>">
                            <textarea name="comment" rows="3" placeholder="Add your comment..." required></textarea>
                            <button type="submit">Add Comment</button>
                        </form>
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
</body>
</html>
