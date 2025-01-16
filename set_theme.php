<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include Logger
require_once 'classes/Logger.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $theme = $_POST['theme'] ?? 'light';
    
    // Validate theme
    if (!in_array($theme, ['light', 'dark'])) {
        Logger::warning("Invalid theme attempted: $theme");
        $theme = 'light';
    }
    
    // Set theme in session
    $_SESSION['theme'] = $theme;
    
    // Log theme change
    $username = $_SESSION['username'] ?? 'Guest';
    Logger::info("Theme changed to $theme by user: $username");
}

// Redirect back to previous page or index if referer not set
$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $redirect);
exit;
