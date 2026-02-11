<?php
require_once 'app/init.php';
$db = new Database();
$db->query("SHOW COLUMNS FROM solicitudes_inventario");
$columns = $db->resultSet();
foreach ($columns as $col) {
    echo $col->Field . "\n";
}
