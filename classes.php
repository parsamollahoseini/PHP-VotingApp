<?php
// Set default timezone
date_default_timezone_set('America/Toronto');
require_once 'classes/Logger.php';
require_once 'classes/TimeFormatter.php';


// User Class
class User {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    private function isValidEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function isValidPassword(string $password): bool {
        return strlen($password) >= 9;
    }

    public function registerUser(string $username, string $email, string $password): bool {
        // Validate inputs
        if (empty($username) || empty($password) || empty($email)) {
            Logger::warning("Registration failed: Empty fields provided");
            return false;
        }

        if (!$this->isValidPassword($password)) {
            Logger::warning("Registration failed: Invalid password length for user $username");
            return false;
        }

        if (!$this->isValidEmail($email)) {
            Logger::warning("Registration failed: Invalid email format for user $username");
            return false;
        }

        try {
            // Check if username or email already exists
            $stmt = $this->pdo->prepare("SELECT id FROM Users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            
            if ($stmt->rowCount() > 0) {
                Logger::warning("Registration failed: Username or email already exists - $username, $email");
                return false; // User already exists
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $this->pdo->prepare(
                "INSERT INTO Users (username, email, password) VALUES (?, ?, ?)"
            );
            
            $result = $stmt->execute([$username, $email, $hashedPassword]);
            
            if ($result) {
                Logger::info("New user registered successfully: $username");
            } else {
                Logger::error("Failed to insert new user: $username");
            }
            
            return $result;
        } catch (PDOException $e) {
            Logger::error("Database error during user registration: " . $e->getMessage());
            return false;
        }
    }

    public function authenticateUser(string $username, string $password): bool {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT password FROM Users WHERE username = ?"
            );
            $stmt->execute([$username]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $result = password_verify($password, $row['password']);
                if ($result) {
                    Logger::info("User authenticated successfully: $username");
                } else {
                    Logger::warning("Failed authentication attempt for user: $username");
                }
                return $result;
            }
            
            Logger::warning("Authentication attempt for non-existent user: $username");
            return false;
        } catch (PDOException $e) {
            Logger::error("Database error during authentication: " . $e->getMessage());
            return false;
        }
    }

    public function getUserId(string $username): ?int {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM Users WHERE username = ?");
            $stmt->execute([$username]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return (int)$row['id'];
            }
            
            Logger::warning("Failed to get ID for non-existent user: $username");
            return null;
        } catch (PDOException $e) {
            Logger::error("Database error while getting user ID: " . $e->getMessage());
            return null;
        }
    }

    public function getUsername(int $userId): ?string {
        try {
            $stmt = $this->pdo->prepare("SELECT username FROM Users WHERE id = ?");
            $stmt->execute([$userId]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                return $row['username'];
            }
            
            Logger::warning("Failed to get username for non-existent user ID: $userId");
            return null;
        } catch (PDOException $e) {
            Logger::error("Database error while getting username: " . $e->getMessage());
            return null;
        }
    }
}

// Topic Class
class Topic {
    private $pdo;
    public $id;
    public $userId;
    public $title;
    public $description;
    public $createdAt;
    public $creatorName; // Add property for creator name
    public $upvotes = 0; // Add property for upvotes with a default value
    public $downvotes = 0; // Add property for downvotes with a default value

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }


    public function createTopic(int $userId, string $title, string $description): bool {
        if (empty($title) || empty($description)) {
            Logger::warning("Topic creation failed: Empty title or description by user $userId");
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO Topics (user_id, title, description) VALUES (?, ?, ?)"
            );

            $result = $stmt->execute([$userId, $title, $description]);

            if ($result) {
                Logger::info("New topic created: '$title' by user $userId");
            } else {
                Logger::error("Failed to create topic: '$title' by user $userId");
            }

            return $result;
        } catch (PDOException $e) {
            Logger::error("Database error during topic creation: " . $e->getMessage());
            return false;
        }
    }

    public function getTopics(): array {
        try {
            $query = "
                SELECT 
                    t.*,
                    u.username as creator_name,
                    COALESCE(SUM(CASE WHEN v.vote_type = 'up' THEN 1 ELSE 0 END), 0) as upvotes,
                    COALESCE(SUM(CASE WHEN v.vote_type = 'down' THEN 1 ELSE 0 END), 0) as downvotes
                FROM Topics t
                JOIN Users u ON t.user_id = u.id
                LEFT JOIN Votes v ON t.id = v.topic_id
                GROUP BY t.id
                ORDER BY t.created_at DESC
            ";

            $stmt = $this->pdo->query($query);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $topics = [];
            foreach ($rows as $row) {
                $topic = new self($this->pdo); // Create a new Topic instance
                $topic->id = $row['id'];
                $topic->userId = $row['user_id'];
                $topic->title = $row['title'];
                $topic->description = $row['description'];
                $topic->createdAt = $row['created_at'];
                $topic->creatorName = $row['creator_name'];
                $topic->upvotes = (int) $row['upvotes'];
                $topic->downvotes = (int) $row['downvotes'];

                $topics[] = $topic;
            }
            Logger::info("Retrieved " . count($topics) . " topics as Topic instances");
            return $topics;
        } catch (PDOException $e) {
            Logger::error("Error fetching Topics: " . $e->getMessage());
            return [];
        }
    }



    public function getCreatedTopics(int $userId): array {
        try {
            $query = "
                SELECT 
                    t.*,
                    COALESCE(SUM(CASE WHEN v.vote_type = 'up' THEN 1 ELSE 0 END), 0) as upvotes,
                    COALESCE(SUM(CASE WHEN v.vote_type = 'down' THEN 1 ELSE 0 END), 0) as downvotes
                FROM Topics t
                LEFT JOIN Votes v ON t.id = v.topic_id
                WHERE t.user_id = ?
                GROUP BY t.id
                ORDER BY t.created_at DESC
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$userId]);
            $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Logger::info("Retrieved " . count($topics) . " Topics for user $userId");
            return $topics;
        } catch (PDOException $e) {
            Logger::error("Database error while retrieving user topics: " . $e->getMessage());
            return [];
        }
    }

    public function getTopic(int $topicId): ?array {
        try {
            $query = "
                SELECT 
                    t.*,
                    u.username as creator_name,
                    COALESCE(SUM(CASE WHEN v.vote_type = 'up' THEN 1 ELSE 0 END), 0) as upvotes,
                    COALESCE(SUM(CASE WHEN v.vote_type = 'down' THEN 1 ELSE 0 END), 0) as downvotes
                FROM Topics t
                JOIN Users u ON t.user_id = u.id
                LEFT JOIN Votes v ON t.id = v.topic_id
                WHERE t.id = ?
                GROUP BY t.id
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$topicId]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                Logger::info("Retrieved topic $topicId");
                return $row;
            }
            
            Logger::warning("Attempted to retrieve non-existent topic: $topicId");
            return null;
        } catch (PDOException $e) {
            Logger::error("Database error while retrieving topic: " . $e->getMessage());
            return null;
        }
    }

    public function topicExists(int $topicId): bool {
        try {
            $stmt = $this->pdo->prepare("SELECT id FROM Topics WHERE id = ?");
            $stmt->execute([$topicId]);
            $exists = $stmt->rowCount() > 0;
            
            if (!$exists) {
                Logger::warning("Checked for non-existent topic: $topicId");
            }
            
            return $exists;
        } catch (PDOException $e) {
            Logger::error("Database error while checking topic existence: " . $e->getMessage());
            return false;
        }
    }
}

// Vote Class
class Vote {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;

    }

    public function vote(int $userId, int $topicId, string $voteType): bool {
        try {
            // Validate vote type
            if (!in_array($voteType, ['up', 'down'])) {
                Logger::warning("Invalid vote type: $voteType by user $userId on topic $topicId");
                return false;
            }

            // Check if the user has already voted on this topic
            $stmt = $this->pdo->prepare("SELECT * FROM Votes WHERE user_id = ? AND topic_id = ?");
            $stmt->execute([$userId, $topicId]);
            if ($stmt->rowCount() > 0) {
                Logger::warning("User $userId already voted on topic $topicId");
                return false;
            }

            // Insert the vote into the database
            $stmt = $this->pdo->prepare("INSERT INTO Votes (user_id, topic_id, vote_type) VALUES (?, ?, ?)");
            if ($stmt->execute([$userId, $topicId, $voteType])) {
                Logger::info("Vote recorded successfully: User $userId, Topic $topicId, Type $voteType");
                return true;
            } else {
                Logger::error("Failed to record vote: User $userId, Topic $topicId, Type $voteType");
                return false;
            }
        } catch (PDOException $e) {
            Logger::error("Database error during voting: " . $e->getMessage());
            return false;
        }
    }




    public function hasVoted(int $topicId, int $userId): bool {
        try {
            $stmt = $this->pdo->prepare("SELECT 1 FROM Votes WHERE user_id = ? AND topic_id = ?");
            $stmt->execute([$userId, $topicId]);

            $hasVoted = $stmt->fetch() !== false; // Check if any row exists
            Logger::info("HasVoted: " . ($hasVoted ? "True" : "False") . " for User $userId, Topic $topicId");

            return $hasVoted;
        } catch (PDOException $e) {
            Logger::error("Database error during vote check: " . $e->getMessage());
            return false;
        }
    }

    public function getUserVoteType(int $topicId, int $userId): ?string {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT vote_type FROM Votes WHERE topic_id = ? AND user_id = ?"
            );
            $stmt->execute([$topicId, $userId]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                Logger::info("Retrieved vote type for user $userId on topic $topicId: {$row['vote_type']}");
                return $row['vote_type'];
            }
            
            Logger::info("No vote found for user $userId on topic $topicId");
            return null;
        } catch (PDOException $e) {
            Logger::error("Database error while getting vote type: " . $e->getMessage());
            return null;
        }
    }

    public function getUserVoteHistory(int $userId): array {
        try {
            $query = "
                SELECT 
                    v.*,
                    t.title as topic_title,
                    t.description as topic_description,
                    u.username as topic_creator
                FROM Votes v
                JOIN Topics t ON v.topic_id = t.id
                JOIN Users u ON t.user_id = u.id
                WHERE v.user_id = ?
                ORDER BY v.voted_at DESC
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$userId]);
            $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
            Logger::info("Retrieved voting history for user $userId: " . count($history) . " votes");
            return $history;
        } catch (PDOException $e) {
            Logger::error("Database error while retrieving vote history: " . $e->getMessage());
            return [];
        }
    }

    public function getVoteCounts(int $topicId): array {
        try {
            $query = "
                SELECT 
                    COALESCE(SUM(CASE WHEN vote_type = 'up' THEN 1 ELSE 0 END), 0) as upvotes,
                    COALESCE(SUM(CASE WHEN vote_type = 'down' THEN 1 ELSE 0 END), 0) as downvotes
                FROM Votes
                WHERE topic_id = ?
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$topicId]);
            $counts = $stmt->fetch(PDO::FETCH_ASSOC);
            Logger::info("Retrieved vote counts for topic $topicId: {$counts['upvotes']} up, {$counts['downvotes']} down");
            return $counts;
        } catch (PDOException $e) {
            Logger::error("Database error while getting vote counts: " . $e->getMessage());
            return ['upvotes' => 0, 'downvotes' => 0];
        }
    }
}

// Comment Class
class Comment {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function addComment(int $userId, int $topicId, string $comment): bool {
        if (empty($comment)) {
            Logger::warning("Empty comment attempted by user $userId on topic $topicId");
            return false;
        }

        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO Comments (user_id, topic_id, comment) VALUES (?, ?, ?)"
            );
            $result = $stmt->execute([$userId, $topicId, $comment]);
            
            if ($result) {
                Logger::info("User $userId added comment to topic $topicId");
            } else {
                Logger::error("Failed to add comment from user $userId to topic $topicId");
            }

            return $result;
        } catch (PDOException $e) {
            Logger::error("Database error while adding comment: " . $e->getMessage());
            return false;
        }
    }

    public function getComments(int $topicId): array {
        try {
            $query = "
                SELECT 
                    c.*,
                    u.username as commenter_name
                FROM Comments c
                JOIN Users u ON c.user_id = u.id
                WHERE c.topic_id = ?
                ORDER BY c.commented_at DESC
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$topicId]);
            
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format timestamps using TimeFormatter
            foreach ($comments as &$comment) {
                $comment['formatted_time'] = TimeFormatter::formatTimestamp(
                    strtotime($comment['commented_at'])
                );
            }
            
            Logger::info("Retrieved " . count($comments) . " comments for topic $topicId");
            return $comments;
        } catch (PDOException $e) {
            Logger::error("Database error while retrieving comments: " . $e->getMessage());
            return [];
        }
    }

    public function getCommentCount(int $topicId): int {
        try {
            $stmt = $this->pdo->prepare(
                "SELECT COUNT(*) as count FROM Comments WHERE topic_id = ?"
            );
            $stmt->execute([$topicId]);
            
            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $count = (int)$row['count'];
                Logger::info("Topic $topicId has $count comments");
                return $count;
            }
            
            return 0;
        } catch (PDOException $e) {
            Logger::error("Database error while getting comment count: " . $e->getMessage());
            return 0;
        }
    }

    public function getUserComments(int $userId): array {
        try {
            $query = "
                SELECT 
                    c.*,
                    t.title as topic_title,
                    u.username as commenter_name
                FROM Comments c
                JOIN Topics t ON c.topic_id = t.id
                JOIN Users u ON c.user_id = u.id
                WHERE c.user_id = ?
                ORDER BY c.commented_at DESC
            ";
            
            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$userId]);
            
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format timestamps using TimeFormatter
            foreach ($comments as &$comment) {
                $comment['formatted_time'] = TimeFormatter::formatTimestamp(
                    strtotime($comment['commented_at'])
                );
            }
            
            Logger::info("Retrieved " . count($comments) . " comments for user $userId");
            return $comments;
        } catch (PDOException $e) {
            Logger::error("Database error while retrieving user comments: " . $e->getMessage());
            return [];
        }
    }

    public function deleteComment(int $commentId, int $userId): bool {
        try {
            $stmt = $this->pdo->prepare(
                "DELETE FROM Comments WHERE id = ? AND user_id = ?"
            );
            $result = $stmt->execute([$commentId, $userId]);
            
            if ($result && $stmt->rowCount() > 0) {
                Logger::info("User $userId deleted comment $commentId");
                return true;
            }
            
            Logger::warning("Failed to delete comment $commentId by user $userId - Comment not found or unauthorized");
            return false;
        } catch (PDOException $e) {
            Logger::error("Database error while deleting comment: " . $e->getMessage());
            return false;
        }
    }
}
