<?php

/**
 * Exits Page - Enrutador para Exits Controller
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

require_once 'app/init.php';
$controller = new Exits();

// 1. Identificar la acción usando PATH_INFO (URLs limpias sin query params)
$pathInfo = $_SERVER['PATH_INFO'] ?? '/';
$segments = array_filter(explode('/', $pathInfo));

// Si está vacío, usar 'index'
$action = !empty($segments) ? array_shift($segments) : 'index';

// 2. Ejecutar el método correspondiente
switch ($action) {
    case 'crear':
        $controller->crear();
        break;
        
    case 'ver':
        $id = !empty($segments) ? array_shift($segments) : null;
        $controller->ver($id);
        break;
        
    case 'estadisticas':
        $controller->estadisticas();
        break;
        
    case 'index':
    default:
        $controller->index();
        break;
}
