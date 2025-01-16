<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include Logger
require_once 'classes/Logger.php';

// Log the logout event if there was a logged-in user
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    Logger::info("User logged out: $username");
}

// Unset all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to index page
header('Location: index.php');
exit;
