<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header('Location: index.php');
    exit;
}

// Get theme
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>For Prof - Voting System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .video-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            margin-bottom: 20px;
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        .sql-container {
            width: 100%;
            margin-top: 20px;
        }
        .sql-container iframe {
            width: 100%;
            height: 400px;
            border: 1px solid #ccc;
        }
        @keyframes slideIn {
            0% {
                transform: translateX(-100%);
                opacity: 0;
            }
            100% {
                transform: translateX(0);
                opacity: 1;
            }
        }
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .rickroll-message {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #ff4444;
            margin: 20px 0;
            opacity: 0;
            animation: slideIn 1s forwards 3s, bounce 2s infinite 4s;
        }
        .rickroll-message span {
            display: inline-block;
        }
        .never-gonna {
            color: #ff6b6b;
        }
        .give-you-up {
            color: #4ecdc4;
        }
        .real-content {
            display: block;
            opacity: 0;
            animation: fadeInUp 1s ease-out forwards;
            animation-delay: 5s;
        }
    </style>
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

        <h1>For Professor</h1>

        <h2>Code Explanation Video</h2>
        <h2>if the video didnt work here is the linke for it    "https://www.youtube.com/watch?v=QiblCdLeHzM" </h2>

        <div class="video-container">
            <!-- Mock YouTube video embed with autoplay -->
            <iframe 
                id="youtubeVideo"
                src="https://www.youtube.com/embed/QiblCdLeHzM"
                title="Code Explanation"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen>
            </iframe>
        </div>

        <div class="rickroll-message">
            <span class="never-gonna">🎵 Never gonna give you up! 🎵</span>
            <br>
            <span class="give-you-up">You've Been Rickrolled! 🎸</span>
        </div>

        <div class="real-content">
            <h1>Real For Professor</h1>
            <div class="video-container">
                <!-- Real code explanation video -->
                <iframe 
                    id="realVideo"
                    src="https://www.youtube.com/embed/v9B7CgvsWiQ?autoplay=0&mute=1&enablejsapi=1"
                    title="Code Explanation"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>

            <h2>Database Schema</h2>
            <div class="sql-container">
                <!-- Mock sqlbackup.txt iframe -->
                <iframe src="sqlbackup.txt" title="SQL Backup"></iframe>
            </div>
        </div>
    </div>

    <!-- YouTube IFrame API -->
    <script src="https://www.youtube.com/iframe_api"></script>
    <script>
        // Initialize YouTube player
        var player;
        function onYouTubeIframeAPIReady() {
            player = new YT.Player('youtubeVideo', {
                events: {
                    'onReady': onPlayerReady
                }
            });
        }

        // Try to unmute when player is ready
        function onPlayerReady(event) {
            // Try to unmute after a short delay
            setTimeout(() => {
                try {
                    player.unMute();
                    player.setVolume(100);
                } catch (e) {
                    console.log('Could not unmute automatically due to browser restrictions');
                }
            }, 1000);
        }
    </script>
</body>
</html>
