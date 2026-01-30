<?php

if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

require_once 'app/init.php';
$controller = new Requests();

// 1. Identificar la acción usando PATH_INFO (URLs limpias sin query params)
$pathInfo = $_SERVER['PATH_INFO'] ?? '/';
$action = trim($pathInfo, '/');

// Si está vacío, usar 'index'
if (empty($action)) {
    $action = 'index';
}

// 2. Ejecutar el método correspondiente
switch ($action) {
    case 'nueva':
        $controller->nueva();
        break;
    default:
        $controller->index();
        break;
}
