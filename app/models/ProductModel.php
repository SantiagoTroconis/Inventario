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
        $this->query("SELECT * FROM productos 
                     WHERE nombre LIKE :termino 
                     OR codigo LIKE :termino 
                     OR descripcion LIKE :termino");
        $this->bind(':termino', '%' . $termino . '%');
        return $this->resultSet();
    }
    
    // Crear nuevo producto
    public function create($data) {
        $this->query("INSERT INTO productos 
                     (codigo, nombre, descripcion, precio, stock, stock_minimo, categoria, activo) 
                     VALUES (:codigo, :nombre, :descripcion, :precio, :stock, :stock_minimo, :categoria, :activo)");
        
        $this->bind(':codigo', $data['codigo']);
        $this->bind(':nombre', $data['nombre']);
        $this->bind(':descripcion', $data['descripcion'] ?? '');
        $this->bind(':precio', $data['precio']);
        $this->bind(':stock', $data['stock'] ?? 0);
        $this->bind(':stock_minimo', $data['stock_minimo'] ?? 0);
        $this->bind(':categoria', $data['categoria'] ?? '');
        $this->bind(':activo', $data['activo'] ?? 1);
        
        return $this->execute();
    }
    
    // Actualizar producto
    public function update($id, $data) {
        $this->query("UPDATE productos 
                     SET codigo = :codigo, nombre = :nombre, descripcion = :descripcion,
                         precio = :precio, stock = :stock, stock_minimo = :stock_minimo,
                         categoria = :categoria, activo = :activo
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
    
    // Actualizar stock
    public function updateStock($id, $cantidad, $operacion = 'sumar') {
        if ($operacion === 'sumar') {
            $this->query("UPDATE productos SET stock = stock + :cantidad WHERE id = :id");
        } else {
            $this->query("UPDATE productos SET stock = stock - :cantidad WHERE id = :id");
        }
        $this->bind(':id', $id);
        $this->bind(':cantidad', $cantidad);
        return $this->execute();
    }
    
    // Eliminar producto
    public function delete($id) {
        $this->query("DELETE FROM productos WHERE id = :id");
        $this->bind(':id', $id);
        return $this->execute();
    }
    
    // Verificar si existe código
    public function existsCodigo($codigo, $excludeId = null) {
        if ($excludeId) {
            $this->query("SELECT id FROM productos WHERE codigo = :codigo AND id != :id");
            $this->bind(':id', $excludeId);
        } else {
            $this->query("SELECT id FROM productos WHERE codigo = :codigo");
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
                     FROM productos 
                     WHERE activo = 1");
        return $this->single();
    }
}
