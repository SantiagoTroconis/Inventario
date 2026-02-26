<?php

class ProductModel extends Database {
    
    // Obtener todos los productos
    public function getAll() {
        $this->query("SELECT * FROM Productos_Inventario ORDER BY nombre");
        return $this->resultSet();
    }
    
    // Obtener productos con stock bajo
    public function getLowStock($limite = 10) {
        $this->query("SELECT * FROM Productos_Inventario WHERE stock <= :limite ORDER BY stock ASC");
        $this->bind(':limite', $limite);
        return $this->resultSet();
    }
    
    // Obtener producto por ID
    public function getById($id) {
        $this->query("SELECT * FROM Productos_Inventario WHERE id = :id");
        $this->bind(':id', $id);
        return $this->single();
    }

    public function getBySucursal($sucursal_id) {
        $this->query("SELECT * FROM Productos_Inventario 
                        WHERE usuario_id = :sucursal_id
                      ");
        $this->bind(':sucursal_id', $sucursal_id);
        return $this->resultSet();
    }
    
    // Obtener producto por código
    public function getByCodigo($codigo) {
        $this->query("SELECT * FROM Productos_Inventario WHERE codigo = :codigo");
        $this->bind(':codigo', $codigo);
        return $this->single();
    }
    
    // Buscar productos
    public function search($termino) {
        $this->query("SELECT * FROM productos_inventario 
                     WHERE nombre LIKE :termino 
                     OR codigo LIKE :termino 
                     OR descripcion LIKE :termino");
        $this->bind(':termino', '%' . $termino . '%');
        return $this->resultSet();
    }
    
    // Crear nuevo producto
    public function create($input) {
        $this->query("INSERT INTO productos_inventario 
                     (codigo, nombre, descripcion, categoria, stock, stock_minimo, precio, activo, imagen) 
                     VALUES 
                     (:codigo, :nombre, :descripcion, :categoria, :stock, :stock_minimo, :precio, :activo, :imagen)");
        
        $this->bind(':codigo', $input['codigo']);
        $this->bind(':nombre', $input['nombre']);
        $this->bind(':descripcion', $input['descripcion'] ?? '');
        $this->bind(':categoria', $input['categoria'] ?? '');
        $this->bind(':stock', $input['stock'] ?? 0);
        $this->bind(':stock_minimo', $input['stock_minimo'] ?? 0);
        $this->bind(':precio', $input['precio']);
        $this->bind(':activo', $input['activo'] ?? 1);
        $this->bind(':imagen', $input['imagen'] ?? null);
        
        return $this->execute();
    }
    
    // Actualizar producto
    public function update($id, $data) {
        $this->query("UPDATE productos_inventario 
                     SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion,
                         precio = :precio, stock = :stock, stock_minimo = :stock_minimo,
                         categoria = :categoria, activo = :activo, imagen = :imagen
                     WHERE id = :id");
        
        $this->bind(':id', $id);
        $this->bind(':codigo', $data['codigo']);
        $this->bind(':nombre', $data['nombre']);
        $this->bind(':descripcion', $data['descripcion']);
        $this->bind(':precio', $data['precio']);
        $this->bind(':stock', $data['stock']);
        $this->bind(':stock_minimo', $data['stock_minimo']);
        $this->bind(':categoria', $data['categoria']);
        $this->bind(':activo', $data['activo']);
        
        return $this->execute();
    }
    
    /**
     * Actualizar stock de un producto (método directo - usado por MovementModel)
     * @param int $id - ID del producto
     * @param int $new_stock - Nuevo valor de stock
     * @return bool - True si se actualizó correctamente
     */
    public function updateStock($id, $new_stock) {
        $this->query("UPDATE Productos_Inventario SET stock = :stock WHERE id = :id");
        $this->bind(':id', $id);
        $this->bind(':stock', $new_stock);
        return $this->execute();
    }
    
    /**
     * Obtener stock actual de un producto
     * @param int $id - ID del producto
     * @return int - Stock actual o 0 si no existe
     */
    public function getCurrentStock($id) {
        $this->query("SELECT stock FROM Productos_Inventario WHERE id = :id");
        $this->bind(':id', $id);
        $result = $this->single();
        return $result ? (int)$result->stock : 0;
    }
    
    // Eliminar producto
    public function delete($id) {
        $this->query("DELETE FROM productos_inventario WHERE id = :id");
        $this->bind(':id', $id);
        return $this->execute();
    }
    
    // Verificar si existe código
    public function existsCodigo($codigo, $excludeId = null) {
        if ($excludeId) {
            $this->query("SELECT id FROM productos_inventario WHERE codigo = :codigo AND id != :id");
            $this->bind(':id', $excludeId);
        } else {
            $this->query("SELECT id FROM productos_inventario WHERE codigo = :codigo");
        }
        $this->bind(':codigo', $codigo);
        return $this->single() ? true : false;
    }
    
    // Obtener estadísticas
    public function getStats() {
        $this->query("SELECT 
                     COUNT(*) as total_productos,
                     SUM(stock) as total_stock,
                     SUM(stock * precio) as valor_inventario,
                     COUNT(CASE WHEN stock <= stock_minimo THEN 1 END) as productos_bajo_stock
                     FROM productos_inventario 
                     WHERE activo = 1");
        return $this->single();
    }

    // Estadísticas por Sucursal
    public function getStatsBySucursal($sucursal_id) {
        $this->query("SELECT 
                     COUNT(*) as total_productos,
                     SUM(stock) as total_stock,
                     SUM(stock * precio) as valor_inventario,
                     COUNT(CASE WHEN stock <= stock_minimo THEN 1 END) as productos_bajo_stock
                     FROM productos_inventario 
                     WHERE usuario_id = :sucursal_id AND activo = 1");
        $this->bind(':sucursal_id', $sucursal_id);
        return $this->single();
    }

    // Productos con bajo stock por Sucursal
    public function getLowStockBySucursal($sucursal_id, $limite = 5) {
        $this->query("SELECT * FROM productos_inventario 
                     WHERE usuario_id = :sucursal_id AND stock <= stock_minimo 
                     ORDER BY stock ASC LIMIT :limite");
        $this->bind(':sucursal_id', $sucursal_id);
        $this->bind(':limite', $limite);
        return $this->resultSet();
    }

    /**
     * Salud de stock de todos los productos (para el reporte de salud)
     * Retorna todos los productos con el porcentaje de stock vs mínimo
     */
    public function getStockHealthAll() {
        $this->query("SELECT
                        id, nombre, codigo, categoria, stock, stock_minimo, activo,
                        CASE
                            WHEN stock_minimo = 0 THEN 100
                            ELSE ROUND((stock / stock_minimo) * 100, 1)
                        END AS stock_pct,
                        CASE
                            WHEN stock_minimo = 0 THEN 'healthy'
                            WHEN stock <= (stock_minimo * 0.5) THEN 'critical'
                            WHEN stock <= stock_minimo THEN 'warning'
                            ELSE 'healthy'
                        END AS health_status
                      FROM Productos_Inventario
                      WHERE activo = 1
                      ORDER BY stock_pct ASC");
        return $this->resultSet();
    }
}
