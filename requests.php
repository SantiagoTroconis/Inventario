<?php
// requests.php

// 1. Set Base Path
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

require_once 'app/init.php';
$controller = new Requests();

// 2. ROBUST URL PARSING
// Try PATH_INFO first, then fallback to $_GET['action']
$pathInfo = $_SERVER['PATH_INFO'] ?? null;

$action = 'index';
$id = null;

if ($pathInfo) {
    // Logic for /requests.php/action/id
    $pathInfo = trim($pathInfo, '/');
    $parts = explode('/', $pathInfo);
    $action = $parts[0] ?? 'index';
    $id = $parts[1] ?? null;
} elseif (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? null;
}

switch ($action) {
    case 'index':
        $controller->index();
        break;

    case 'nueva':
        $controller->nueva();
        break;
    
    case 'aprobar':
        if ($id) {
            $controller->aprobar($id);
        } else {
            sendJsonError('ID missing');
        }
        break;
    
    case 'rechazar':
        if ($id) {
            $controller->rechazar($id);
        } else {
            sendJsonError('ID missing');
        }
        break;
    
    case 'modificar':
        if ($id) {
            $controller->modificar($id);
        } else {
            sendJsonError('ID missing');
        }
        break;
    
    case 'aceptar_contraoferta':
        if ($id) {
            $controller->aceptar_contraoferta($id);
        } else {
            sendJsonError('ID missing');
        }
        break;
    
    case 'rechazar_contraoferta':
        if ($id) {
            $controller->rechazar_contraoferta($id);
        } else {
            sendJsonError('ID missing');
        }
        break;
    
    case 'ver':
        if ($id) {
            $controller->ver($id);
        } else {
            $controller->index();
        }
        break;
    
    default:
        $controller->index();
        break;
}

// Helper to handle errors nicely
function sendJsonError($message) {
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}