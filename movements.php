<?php

/**
 * Movements Page - Enrutador para Movements Controller
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

require_once 'app/init.php';
$controller = new Movements();

// 1. Identificar la acción usando PATH_INFO (URLs limpias sin query params)
$pathInfo = $_SERVER['PATH_INFO'] ?? '/';
$segments = array_filter(explode('/', $pathInfo));

// Si está vacío, usar 'index'
$action = !empty($segments) ? array_shift($segments) : 'index';

// 2. Ejecutar el método correspondiente
switch ($action) {
    case 'producto':
        // Ver timeline de un producto específico
        $producto_id = !empty($segments) ? array_shift($segments) : null;
        $controller->producto($producto_id);
        break;
        
    case 'tipo':
        // Ver movimientos por tipo
        $tipo = !empty($segments) ? array_shift($segments) : null;
        $controller->tipo($tipo);
        break;
        
    case 'estadisticas':
        $controller->estadisticas();
        break;
        
    case 'exportar':
        $controller->exportar();
        break;
        
    case 'index':
    default:
        $controller->index();
        break;
}
