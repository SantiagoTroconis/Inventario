<?php

class EntriesModel extends Database {
    
    /**
     * Crear una nueva entrada de inventario
     * @param array $data - Datos de la entrada (producto_id, cantidad, usuario_id, proveedor, referencia)
     * @return array - ['success' => bool, 'entry_id' => int|null, 'message' => string]
     */
    public function create($data) {
        try {
            // Iniciar transacción
            $this->beginTransaction();
            
            // 1. Crear la entrada
            $this->query("INSERT INTO Entradas_Inventario 
                         (producto_id, cantidad, usuario_id, proveedor, referencia) 
                         VALUES (:producto_id, :cantidad, :usuario_id, :proveedor, :referencia)");
            
            $this->bind(':producto_id', $data['producto_id']);
            $this->bind(':cantidad', $data['cantidad']);
            $this->bind(':usuario_id', $data['usuario_id']);
            $this->bind(':proveedor', $data['proveedor'] ?? null);
            $this->bind(':referencia', $data['referencia'] ?? null);
            
            if(!$this->execute()) {
                $this->rollBack();
                return ['success' => false, 'entry_id' => null, 'message' => 'Error al crear la entrada'];
            }
            
            $entry_id = $this->lastInsertId();
            
            // 2. Registrar el movimiento de inventario
            $movementModel = new MovementModel();
            $movementResult = $movementModel->registerMovement([
                'producto_id' => $data['producto_id'],
                'usuario_id' => $data['usuario_id'],
                'tipo_movimiento' => 'ENTRADA',
                'cantidad' => $data['cantidad'],
                'referencia_id' => $entry_id,
                'comentario' => 'Entrada de inventario' . (!empty($data['proveedor']) ? ' - Proveedor: ' . $data['proveedor'] : '') . (!empty($data['referencia']) ? ' - Ref: ' . $data['referencia'] : '')
            ]);
            
            if (!$movementResult['success']) {
                $this->rollBack();
                return ['success' => false, 'entry_id' => null, 'message' => 'Error al registrar movimiento: ' . $movementResult['message']];
            }
            
            // Confirmar transacción
            $this->commit();
            
            return [
                'success' => true, 
                'entry_id' => $entry_id, 
                'message' => 'Entrada registrada exitosamente',
                'movement_id' => $movementResult['movement_id']
            ];
            
        } catch (Exception $e) {
            if ($this->dbh->inTransaction()) {
                $this->rollBack();
            }
            return ['success' => false, 'entry_id' => null, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    
    /**
     * Obtener todas las entradas con información relacionada
     * @return array - Array de entradas con datos de producto y usuario
     */
    public function getAll() {
        $this->query("SELECT 
                        e.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        p.categoria as producto_categoria,
                        u.nombre as usuario_nombre,
                        u.correo as usuario_correo
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                      ORDER BY e.fecha_creacion DESC");
        
        return $this->resultSet();
    }
    
    /**
     * Obtener entrada por ID
     * @param int $id - ID de la entrada
     * @return object|bool - Objeto con los datos de la entrada o false
     */
    public function getById($id) {
        $this->query("SELECT 
                        e.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        p.categoria as producto_categoria,
                        p.descripcion as producto_descripcion,
                        u.nombre as usuario_nombre,
                        u.correo as usuario_correo
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                      WHERE e.id = :id");
        
        $this->bind(':id', $id);
        return $this->single();
    }
    
    /**
     * Obtener entradas por tipo de usuario
     * @param string $tipo_usuario - Tipo de usuario (Admin, Agente, etc.)
     * @return array - Array de entradas registradas por ese tipo de usuario
     */
    public function getByTipoUsuario($tipo_usuario) {
        $this->query("SELECT 
                        e.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        p.categoria as producto_categoria,
                        u.nombre as usuario_nombre,
                        u.correo as usuario_correo,
                        u.tipo_usuario as usuario_tipo
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                      WHERE u.tipo_usuario = :tipo_usuario
                      ORDER BY e.fecha_creacion DESC");
        
        $this->bind(':tipo_usuario', $tipo_usuario);
        return $this->resultSet();
    }
    
    /**
     * Obtener entradas por agente/usuario
     * @param int $usuario_id - ID del usuario que registró la entrada
     * @return array - Array de entradas registradas por ese usuario
     */
    public function getByAgente($usuario_id) {
        $this->query("SELECT 
                        e.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        p.categoria as producto_categoria,
                        u.nombre as usuario_nombre,
                        u.correo as usuario_correo
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                      WHERE e.usuario_id = :usuario_id
                      ORDER BY e.fecha_creacion DESC");
        
        $this->bind(':usuario_id', $usuario_id);
        return $this->resultSet();
    }
    
    /**
     * Obtener entradas por producto
     * @param int $producto_id - ID del producto
     * @return array - Array de entradas de ese producto
     */
    public function getByProducto($producto_id) {
        $this->query("SELECT 
                        e.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        u.nombre as usuario_nombre,
                        u.correo as usuario_correo
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                      WHERE e.producto_id = :producto_id
                      ORDER BY e.fecha_creacion DESC");
        
        $this->bind(':producto_id', $producto_id);
        return $this->resultSet();
    }
    
    /**
     * Obtener entradas por proveedor
     * @param string $proveedor - Nombre del proveedor
     * @return array - Array de entradas de ese proveedor
     */
    public function getByProveedor($proveedor) {
        $this->query("SELECT 
                        e.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        u.nombre as usuario_nombre,
                        u.correo as usuario_correo
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                      WHERE e.proveedor LIKE :proveedor
                      ORDER BY e.fecha_creacion DESC");
        
        $this->bind(':proveedor', '%' . $proveedor . '%');
        return $this->resultSet();
    }
    
    /**
     * Obtener entradas por rango de fechas
     * @param string $fecha_inicio - Fecha de inicio (Y-m-d)
     * @param string $fecha_fin - Fecha de fin (Y-m-d)
     * @return array - Array de entradas en ese rango
     */
    public function getByDateRange($fecha_inicio, $fecha_fin) {
        $this->query("SELECT 
                        e.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        p.categoria as producto_categoria,
                        u.nombre as usuario_nombre,
                        u.correo as usuario_correo
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                      WHERE DATE(e.fecha_creacion) BETWEEN :fecha_inicio AND :fecha_fin
                      ORDER BY e.fecha_creacion DESC");
        
        $this->bind(':fecha_inicio', $fecha_inicio);
        $this->bind(':fecha_fin', $fecha_fin);
        return $this->resultSet();
    }
    
    /**
     * Obtener entradas recientes
     * @param int $limite - Número de registros a obtener
     * @return array - Array de las últimas entradas
     */
    public function getRecent($limite = 10) {
        $this->query("SELECT 
                        e.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        p.categoria as producto_categoria,
                        u.nombre as usuario_nombre,
                        u.correo as usuario_correo
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                      ORDER BY e.fecha_creacion DESC
                      LIMIT :limite");
        
        $this->bind(':limite', $limite, PDO::PARAM_INT);
        return $this->resultSet();
    }
    
    /**
     * Actualizar una entrada
     * @param int $id - ID de la entrada
     * @param array $data - Datos a actualizar
     * @return bool - True si se actualizó correctamente
     */
    public function update($id, $data) {
        $this->query("UPDATE Entradas_Inventario 
                     SET producto_id = :producto_id,
                         cantidad = :cantidad,
                         proveedor = :proveedor,
                         referencia = :referencia
                     WHERE id = :id");
        
        $this->bind(':id', $id);
        $this->bind(':producto_id', $data['producto_id']);
        $this->bind(':cantidad', $data['cantidad']);
        $this->bind(':proveedor', $data['proveedor'] ?? null);
        $this->bind(':referencia', $data['referencia'] ?? null);
        
        return $this->execute();
    }
    
    /**
     * Eliminar una entrada
     * @param int $id - ID de la entrada
     * @return bool - True si se eliminó correctamente
     */
    public function delete($id) {
        $this->query("DELETE FROM Entradas_Inventario WHERE id = :id");
        $this->bind(':id', $id);
        return $this->execute();
    }
    
    /**
     * Obtener el total de cantidad ingresada por producto
     * @param int $producto_id - ID del producto
     * @return int - Cantidad total ingresada
     */
    public function getTotalByProduct($producto_id) {
        $this->query("SELECT COALESCE(SUM(cantidad), 0) as total 
                     FROM Entradas_Inventario 
                     WHERE producto_id = :producto_id");
        
        $this->bind(':producto_id', $producto_id);
        $result = $this->single();
        return $result ? $result->total : 0;
    }
    
    /**
     * Obtener estadísticas de entradas por periodo
     * @param string $fecha_inicio - Fecha de inicio
     * @param string $fecha_fin - Fecha de fin
     * @return array - Estadísticas del periodo
     */
    public function getStatsByPeriod($fecha_inicio, $fecha_fin) {
        $this->query("SELECT 
                        COUNT(*) as total_entradas,
                        SUM(e.cantidad) as total_unidades,
                        COUNT(DISTINCT e.producto_id) as productos_distintos,
                        COUNT(DISTINCT e.proveedor) as proveedores_distintos
                      FROM Entradas_Inventario e
                      WHERE DATE(e.fecha_creacion) BETWEEN :fecha_inicio AND :fecha_fin");
        
        $this->bind(':fecha_inicio', $fecha_inicio);
        $this->bind(':fecha_fin', $fecha_fin);
        return $this->single();
    }
    
    /**
     * Obtener productos más ingresados en un periodo
     * @param string $fecha_inicio - Fecha de inicio
     * @param string $fecha_fin - Fecha de fin
     * @param int $limite - Número de productos a retornar
     * @return array - Top productos ingresados
     */
    public function getTopProductsByPeriod($fecha_inicio, $fecha_fin, $limite = 10) {
        $this->query("SELECT 
                        p.id,
                        p.nombre,
                        p.codigo,
                        SUM(e.cantidad) as total_cantidad,
                        COUNT(e.id) as numero_entradas
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      WHERE DATE(e.fecha_creacion) BETWEEN :fecha_inicio AND :fecha_fin
                      GROUP BY p.id, p.nombre, p.codigo
                      ORDER BY total_cantidad DESC
                      LIMIT :limite");
        
        $this->bind(':fecha_inicio', $fecha_inicio);
        $this->bind(':fecha_fin', $fecha_fin);
        $this->bind(':limite', $limite, PDO::PARAM_INT);
        return $this->resultSet();
    }
    
    /**
     * Obtener entradas por referencia
     * @param string $referencia - Número de factura o nota
     * @return array - Array de entradas con esa referencia
     */
    public function getByReferencia($referencia) {
        $this->query("SELECT 
                        e.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        u.nombre as usuario_nombre,
                        u.correo as usuario_correo
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                      WHERE e.referencia = :referencia
                      ORDER BY e.fecha_creacion DESC");
        
        $this->bind(':referencia', $referencia);
        return $this->resultSet();
    }
    
    /**
     * Buscar entradas por múltiples criterios
     * @param array $criterios - Array con criterios de búsqueda
     * @return array - Array de entradas que coinciden
     */
    public function search($criterios) {
        $where = [];
        $params = [];
        
        if (!empty($criterios['producto'])) {
            $where[] = "(p.nombre LIKE :producto OR p.codigo LIKE :producto)";
            $params[':producto'] = '%' . $criterios['producto'] . '%';
        }
        
        if (!empty($criterios['proveedor'])) {
            $where[] = "e.proveedor LIKE :proveedor";
            $params[':proveedor'] = '%' . $criterios['proveedor'] . '%';
        }
        
        if (!empty($criterios['referencia'])) {
            $where[] = "e.referencia LIKE :referencia";
            $params[':referencia'] = '%' . $criterios['referencia'] . '%';
        }
        
        if (!empty($criterios['fecha_inicio'])) {
            $where[] = "DATE(e.fecha_creacion) >= :fecha_inicio";
            $params[':fecha_inicio'] = $criterios['fecha_inicio'];
        }
        
        if (!empty($criterios['fecha_fin'])) {
            $where[] = "DATE(e.fecha_creacion) <= :fecha_fin";
            $params[':fecha_fin'] = $criterios['fecha_fin'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT 
                    e.*,
                    p.nombre as producto_nombre,
                    p.codigo as producto_codigo,
                    p.categoria as producto_categoria,
                    u.nombre as usuario_nombre,
                    u.correo as usuario_correo
                FROM Entradas_Inventario e
                INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                $whereClause
                ORDER BY e.fecha_creacion DESC";
        
        $this->query($sql);
        
        foreach ($params as $param => $value) {
            $this->bind($param, $value);
        }
        
        return $this->resultSet();
    }
    
    /**
     * Contar total de entradas
     * @return int - Número total de entradas
     */
    public function countAll() {
        $this->query("SELECT COUNT(*) as total FROM Entradas_Inventario");
        $result = $this->single();
        return $result ? $result->total : 0;
    }
    
    /**
     * Obtener entradas con paginación
     * @param int $pagina - Número de página
     * @param int $por_pagina - Registros por página
     * @return array - Array de entradas
     */
    public function getPaginated($pagina = 1, $por_pagina = 20) {
        $offset = ($pagina - 1) * $por_pagina;
        
        $this->query("SELECT 
                        e.*,
                        p.nombre as producto_nombre,
                        p.codigo as producto_codigo,
                        p.categoria as producto_categoria,
                        u.nombre as usuario_nombre,
                        u.correo as usuario_correo
                      FROM Entradas_Inventario e
                      INNER JOIN Productos_Inventario p ON e.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON e.usuario_id = u.usuario_id
                      ORDER BY e.fecha_creacion DESC
                      LIMIT :por_pagina OFFSET :offset");
        
        $this->bind(':por_pagina', $por_pagina, PDO::PARAM_INT);
        $this->bind(':offset', $offset, PDO::PARAM_INT);
        
        return $this->resultSet();
    }

    /**
     * Validar si un usuario puede registrar una entrada para un producto
     * @param int $usuario_id - ID del usuario
     * @param int $producto_id - ID del producto
     * @param string $tipo_usuario - Tipo de usuario (Admin, Agente, etc.)
     * @return array - ['valid' => bool, 'message' => string, 'data' => array]
     */
    public function canUserRegisterEntry($usuario_id, $producto_id, $tipo_usuario) {
        // Los administradores pueden registrar entradas para cualquier producto
        if ($tipo_usuario === 'Admin') {
            return [
                'valid' => true,
                'message' => 'Administrador puede registrar entradas para cualquier producto',
                'data' => null
            ];
        }

        // Para otros usuarios, verificar que tengan solicitudes aprobadas con este producto
        $requestModel = new RequestModel();
        $approved = $requestModel->userHasApprovedProduct($usuario_id, $producto_id);

        if (!$approved) {
            return [
                'valid' => false,
                'message' => 'No tiene solicitudes aprobadas para este producto',
                'data' => null
            ];
        }

        return [
            'valid' => true,
            'message' => 'Usuario tiene solicitud aprobada para este producto',
            'data' => $approved
        ];
    }

    /**
     * Obtener cantidad ya recibida de una solicitud específica
     * @param int $solicitud_id - ID de la solicitud
     * @param int $producto_id - ID del producto
     * @return int - Cantidad ya recibida
     */
    public function getReceivedQuantityByRequest($solicitud_id, $producto_id) {
        $this->query("SELECT COALESCE(SUM(e.cantidad), 0) as total_recibido
                     FROM Entradas_Inventario e
                     WHERE e.producto_id = :producto_id
                     AND e.usuario_id IN (
                         SELECT solicitante_id 
                         FROM Solicitudes_Inventario 
                         WHERE id = :solicitud_id
                     )");
        
        $this->bind(':producto_id', $producto_id);
        $this->bind(':solicitud_id', $solicitud_id);
        
        $result = $this->single();
        return $result ? (int)$result->total_recibido : 0;
    }
}
