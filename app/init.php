<?php
// Archivo de inicialización

// Definir la ruta base del proyecto (si no está ya definida)
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Iniciar sesión
session_start();

// Cargar archivos del core
require_once BASE_PATH . '/app/core/App.php';
require_once BASE_PATH . '/app/core/Controller.php';
require_once BASE_PATH . '/app/core/Database.php';
require_once BASE_PATH . '/app/config/config.php';

// Cargar controladores
require_once BASE_PATH . '/app/controllers/Auth.php';
require_once BASE_PATH . '/app/controllers/Home.php';
require_once BASE_PATH . '/app/controllers/Products.php';
require_once BASE_PATH . '/app/controllers/Requests.php';
require_once BASE_PATH . '/app/controllers/Entries.php';
require_once BASE_PATH . '/app/controllers/Movements.php';
require_once BASE_PATH . '/app/controllers/Exits.php';

// Cargar modelos
require_once BASE_PATH . '/app/models/UserModel.php';
require_once BASE_PATH . '/app/models/ProductModel.php';
require_once BASE_PATH . '/app/models/RequestModel.php';
require_once BASE_PATH . '/app/models/EntriesModel.php';
require_once BASE_PATH . '/app/models/MovementModel.php';  
