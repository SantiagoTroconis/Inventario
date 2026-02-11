<?php
/**
 * Products Page
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

// Initialize the application
require_once 'app/init.php';

// Create and run the Products controller
$controller = new Products();

$pathInfo = $_SERVER['PATH_INFO'] ?? '/';
$action = trim($pathInfo, '/');

// Si está vacío, usar 'index'
if (empty($action)) {
    $action = 'index';
}
if (strpos($action, '/') !== false) {
    $parts = explode('/', $action);
    $action = $parts[0];
    $id = $parts[1] ?? null;
}


switch ($action) {
    case 'new':
        $controller->add();
        break;
    case 'edit':
        if (isset($id)) {
            $controller->edit($id);
        } else {
            $controller->index();
        }
        break;
    case 'delete':
        if (isset($id)) {
            $controller->delete($id);
        } else {
            $controller->index();
        }
        break;
    case 'sucursal':
        $controller->sucursal($id ?? null);
        break;
    default:
        $controller->index();
        break;
}

