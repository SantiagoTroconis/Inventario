<?php
/**
 * Reportes Page — Enrutador
 */
if (!defined('BASE_PATH')) {
    define('BASE_PATH', __DIR__);
}

require_once 'app/init.php';
$controller = new Reportes();

$pathInfo = $_SERVER['PATH_INFO'] ?? '/';
$segments = array_filter(explode('/', $pathInfo));
$action   = !empty($segments) ? array_shift($segments) : 'index';

switch ($action) {
    case 'index':
    default:
        $controller->index();
        break;
}
