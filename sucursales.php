<?php
if (!defined('BASE_PATH')) define('BASE_PATH', __DIR__);
require_once 'app/init.php';

$controller = new Sucursales();

$pathInfo = $_SERVER['PATH_INFO'] ?? null;
$action = 'index';
$id = null;

if ($pathInfo) {
    $pathInfo = trim($pathInfo, '/');
    $parts = explode('/', $pathInfo);
    $action = $parts[0] ?? 'index';
    $id = $parts[1] ?? null;
} elseif (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'] ?? null;
}

switch ($action) {
    case 'crear':
        $controller->crear();
        break;
    case 'editar':
        $controller->editar($id);
        break;
    case 'toggle':
        $controller->toggle($id);
        break;
    case 'eliminar':
        $controller->eliminar($id);
        break;
    default:
        $controller->index();
        break;
}
