<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

$db = new Database();

echo "<pre>";
echo "=== Migration: Automated Entry System ===\n\n";

$dbName = DB_NAME;

$columns = [
    ['table' => 'Entradas_Inventario',     'column' => 'solicitud_id',            'def' => 'INT NULL DEFAULT NULL'],
    ['table' => 'Entradas_Inventario',     'column' => 'estado',                  'def' => "VARCHAR(20) NOT NULL DEFAULT 'Confirmada'"],
    ['table' => 'Entradas_Inventario',     'column' => 'fecha_entrega_estimada',  'def' => 'DATE NULL DEFAULT NULL'],
    ['table' => 'Entradas_Inventario',     'column' => 'fecha_entrega_real',      'def' => 'DATE NULL DEFAULT NULL'],
    ['table' => 'Entradas_Inventario',     'column' => 'cantidad_recibida',       'def' => 'INT NULL DEFAULT NULL'],
    ['table' => 'Entradas_Inventario',     'column' => 'notas_entrega',           'def' => 'TEXT NULL DEFAULT NULL'],
    ['table' => 'Solicitudes_Inventario',  'column' => 'fecha_entrega_estimada',  'def' => 'DATE NULL DEFAULT NULL'],
];

foreach ($columns as $col) {
    $table  = $col['table'];
    $column = $col['column'];
    $def    = $col['def'];

    // Check if column already exists
    $db->query("SELECT COUNT(*) as cnt
                FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = '$dbName'
                AND TABLE_NAME = '$table'
                AND COLUMN_NAME = '$column'");
    $result = $db->single();

    if ($result->cnt == 0) {
        $db->query("ALTER TABLE $table ADD COLUMN $column $def");
        $db->execute();
        echo "✅ Added: $column to $table\n";
    } else {
        echo "⏭️  Skipped (already exists): $column on $table\n";
    }
}

echo "\n=== Migration Complete ===";
echo "</pre>";
