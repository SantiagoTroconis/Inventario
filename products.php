<?php

/**
 * Products Page
 */

// Initialize the application
require_once 'app/init.php';

// Create and run the Products controller
$controller = new Products();

// Check if there's an action parameter
if (isset($_GET['action']) && method_exists($controller, $_GET['action'])) {
    $action = $_GET['action'];
    $params = isset($_GET['id']) ? [$_GET['id']] : [];
    call_user_func_array([$controller, $action], $params);
} else {
    $controller->index();
}
