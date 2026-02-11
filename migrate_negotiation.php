<?php
require_once 'app/init.php'; // Ensure DB connection is available via 'Database' class or similar

$db = new Database();

// 1. Create table `solicitud_negociaciones`
$sql1 = "CREATE TABLE IF NOT EXISTS solicitud_negociaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solicitud_id INT NOT NULL,
    usuario_id INT NOT NULL,
    notas TEXT,
    estado ENUM('Pendiente', 'Aceptada', 'Rechazada') DEFAULT 'Pendiente',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (solicitud_id) REFERENCES solicitudes_inventario(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios_inventario(usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// 2. Create table `solicitud_negociacion_detalles`
$sql2 = "CREATE TABLE IF NOT EXISTS solicitud_negociacion_detalles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    negociacion_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad_propuesta INT NOT NULL,
    observaciones TEXT,
    FOREIGN KEY (negociacion_id) REFERENCES solicitud_negociaciones(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos_inventario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $db->query($sql1);
    $db->execute();
    echo "Table 'solicitud_negociaciones' created or already exists.\n";

    $db->query($sql2);
    $db->execute();
    echo "Table 'solicitud_negociacion_detalles' created or already exists.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
