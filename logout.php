<?php

/**
 * Logout functionality
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy the session
session_destroy();

// Redirect to auth page
header('Location: auth.php');
exit();
