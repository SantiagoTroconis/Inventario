<?php

/**
 * Home Page - Dashboard
 */

// Initialize the application
require_once 'app/init.php';

// Create and run the Home controller
$controller = new Home();
$controller->index();
