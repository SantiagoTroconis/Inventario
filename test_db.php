<?php
require_once 'app/config/config.php';

try {
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    echo "DSN: $dsn\n";
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    echo "Connected successfully to MySQL!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
