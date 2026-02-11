<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';

try {
    $db = new Database();
    
    // Check if column exists first to avoid error
    $checkSql = "SELECT COUNT(*) as count 
                 FROM information_schema.COLUMNS 
                 WHERE TABLE_SCHEMA = '" . DB_NAME . "' 
                 AND TABLE_NAME = 'productos_inventario' 
                 AND COLUMN_NAME = 'imagen'";
                 
    $db->query($checkSql);
    $result = $db->single();
    
    if ($result->count == 0) {
        $sql = "ALTER TABLE productos_inventario ADD COLUMN imagen VARCHAR(255) DEFAULT NULL";
        $db->query($sql);
        $db->execute();
        echo "Column 'imagen' added successfully.";
    } else {
        echo "Column 'imagen' already exists.";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
