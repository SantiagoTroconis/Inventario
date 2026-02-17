<?php

class MovementModel extends Database {
    
    /**
     * Registrar un movimiento de inventario
     * Este método maneja TODO: obtener stock actual, calcular nuevo stock, registrar movimiento, actualizar producto
     * @param array $data - [producto_id, usuario_id, tipo_movimiento, cantidad, referencia_id, comentario]
     * @return array - ['success' => bool, 'message' => string, 'movement_id' => int|null]
     */
    public function registerMovement($data) {
        try {
            // Validar datos requeridos
            if (empty($data['producto_id']) || empty($data['usuario_id']) || empty($data['tipo_movimiento']) || !isset($data['cantidad'])) {
                return ['success' => false, 'message' => 'Datos incompletos para registrar movimiento', 'movement_id' => null];
            }

            // Iniciar transacción
            $this->beginTransaction();

            // 1. Obtener stock actual del producto
            $productModel = new ProductModel();
            $producto = $productModel->getById($data['producto_id']);
            
            if (!$producto) {
                $this->rollBack();
                return ['success' => false, 'message' => 'Producto no encontrado', 'movement_id' => null];
            }

            $stock_anterior = (int)$producto->stock;
            $cantidad = (int)$data['cantidad'];

            // 2. Calcular nuevo stock según tipo de movimiento
            $stock_actual = $stock_anterior;
            
            switch ($data['tipo_movimiento']) {
                case 'ENTRADA':
                case 'DEVOLUCION':
                    // Aumenta stock
                    $stock_actual = $stock_anterior + abs($cantidad);
                    break;
                    
                case 'SALIDA_SOLICITUD':
                case 'SALIDA_MANUAL':
                    // Disminuye stock
                    $stock_actual = $stock_anterior - abs($cantidad);
                    break;
                    
                case 'AJUSTE_INVENTARIO':
                    // Puede ser + o - (cantidad ya viene con signo)
                    $stock_actual = $stock_anterior + $cantidad;
                    break;
                    
                default:
                    $this->rollBack();
                    return ['success' => false, 'message' => 'Tipo de movimiento no válido', 'movement_id' => null];
            }

            // 3. Validar que el stock no sea negativo
            if ($stock_actual < 0) {
                $this->rollBack();
                return [
                    'success' => false, 
                    'message' => "Stock insuficiente. Stock actual: {$stock_anterior}, Intentando retirar: {$cantidad}",
                    'movement_id' => null
                ];
            }

            // 4. Registrar el movimiento
            $this->query("INSERT INTO Movimientos_Inventario 
                         (producto_id, usuario_id, tipo_movimiento, cantidad, stock_anterior, stock_actual, referencia_id, comentario)
                         VALUES (:producto_id, :usuario_id, :tipo_movimiento, :cantidad, :stock_anterior, :stock_actual, :referencia_id, :comentario)");
            
            $this->bind(':producto_id', $data['producto_id']);
            $this->bind(':usuario_id', $data['usuario_id']);
            $this->bind(':tipo_movimiento', $data['tipo_movimiento']);
            $this->bind(':cantidad', abs($cantidad)); // Siempre guardamos cantidad positiva
            $this->bind(':stock_anterior', $stock_anterior);
            $this->bind(':stock_actual', $stock_actual);
            $this->bind(':referencia_id', $data['referencia_id'] ?? null);
            $this->bind(':comentario', $data['comentario'] ?? null);
            
            if (!$this->execute()) {
                $this->rollBack();
                return ['success' => false, 'message' => 'Error al registrar el movimiento', 'movement_id' => null];
            }

            $movement_id = $this->lastInsertId();

            // 5. Actualizar stock del producto
            if (!$productModel->updateStock($data['producto_id'], $stock_actual)) {
                $this->rollBack();
                return ['success' => false, 'message' => 'Error al actualizar el stock del producto', 'movement_id' => null];
            }

            // Confirmar transacción
            $this->commit();
            
            return [
                'success' => true, 
                'message' => 'Movimiento registrado exitosamente',
                'movement_id' => $movement_id,
                'stock_anterior' => $stock_anterior,
                'stock_actual' => $stock_actual
            ];

        } catch (Exception $e) {
            if ($this->dbh->inTransaction()) {
                $this->rollBack();
            }
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage(), 'movement_id' => null];
        }
    }

    /**
     * Obtener todos los movimientos con información relacionada
     * @return array - Movimientos con datos de producto y usuario
     */
    public function getAll() {
        $this->query("SELECT 
                        m.*,
                        p.nombre AS producto_nombre,
                        p.codigo AS producto_codigo,
                        p.categoria AS producto_categoria,
                        u.nombre AS usuario_nombre,
                        u.correo AS usuario_correo
                      FROM Movimientos_Inventario m
                      INNER JOIN Productos_Inventario p ON m.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON m.usuario_id = u.usuario_id
                      ORDER BY m.fecha_movimiento DESC");
        
        return $this->resultSet();
    }

    /**
     * Obtener movimientos por producto
     * @param int $producto_id - ID del producto
     * @return array - Historial de movimientos del producto
     */
    public function getByProduct($producto_id) {
        $this->query("SELECT 
                        m.*,
                        p.nombre AS producto_nombre,
                        p.codigo AS producto_codigo,
                        u.nombre AS usuario_nombre,
                        u.correo AS usuario_correo
                      FROM Movimientos_Inventario m
                      INNER JOIN Productos_Inventario p ON m.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON m.usuario_id = u.usuario_id
                      WHERE m.producto_id = :producto_id
                      ORDER BY m.fecha_movimiento DESC");
        
        $this->bind(':producto_id', $producto_id);
        return $this->resultSet();
    }

    /**
     * Obtener movimientos por usuario
     * @param int $usuario_id - ID del usuario
     * @return array - Movimientos realizados por el usuario
     */
    public function getByUser($usuario_id) {
        $this->query("SELECT 
                        m.*,
                        p.nombre AS producto_nombre,
                        p.codigo AS producto_codigo,
                        u.nombre AS usuario_nombre
                      FROM Movimientos_Inventario m
                      INNER JOIN Productos_Inventario p ON m.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON m.usuario_id = u.usuario_id
                      WHERE m.usuario_id = :usuario_id
                      ORDER BY m.fecha_movimiento DESC");
        
        $this->bind(':usuario_id', $usuario_id);
        return $this->resultSet();
    }

    /**
     * Obtener movimientos por tipo
     * @param string $tipo_movimiento - Tipo de movimiento
     * @return array - Movimientos del tipo especificado
     */
    public function getByType($tipo_movimiento) {
        $this->query("SELECT 
                        m.*,
                        p.nombre AS producto_nombre,
                        p.codigo AS producto_codigo,
                        u.nombre AS usuario_nombre
                      FROM Movimientos_Inventario m
                      INNER JOIN Productos_Inventario p ON m.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON m.usuario_id = u.usuario_id
                      WHERE m.tipo_movimiento = :tipo_movimiento
                      and u.usuario_id = :usuario_id
                      ORDER BY m.fecha_movimiento DESC");
        
        $this->bind(':tipo_movimiento', $tipo_movimiento);
        $this->bind(':usuario_id', $_SESSION['usuario_id']);
        return $this->resultSet();
    }

    /**
     * Obtener movimientos por rango de fechas
     * @param string $fecha_inicio - Fecha de inicio (Y-m-d)
     * @param string $fecha_fin - Fecha de fin (Y-m-d)
     * @return array - Movimientos en el rango
     */
    public function getByDateRange($fecha_inicio, $fecha_fin) {
        $this->query("SELECT 
                        m.*,
                        p.nombre AS producto_nombre,
                        p.codigo AS producto_codigo,
                        u.nombre AS usuario_nombre
                      FROM Movimientos_Inventario m
                      INNER JOIN Productos_Inventario p ON m.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON m.usuario_id = u.usuario_id
                      WHERE DATE(m.fecha_movimiento) BETWEEN :fecha_inicio AND :fecha_fin
                      ORDER BY m.fecha_movimiento DESC");
        
        $this->bind(':fecha_inicio', $fecha_inicio);
        $this->bind(':fecha_fin', $fecha_fin);
        return $this->resultSet();
    }

    /**
     * Obtener movimientos por referencia (trazabilidad)
     * @param string $tipo - Tipo de referencia ('entrada', 'solicitud', etc.)
     * @param int $referencia_id - ID de la referencia
     * @return array - Movimientos relacionados
     */
    public function getByReference($referencia_id) {
        $this->query("SELECT 
                        m.*,
                        p.nombre AS producto_nombre,
                        p.codigo AS producto_codigo,
                        u.nombre AS usuario_nombre
                      FROM Movimientos_Inventario m
                      INNER JOIN Productos_Inventario p ON m.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON m.usuario_id = u.usuario_id
                      WHERE m.referencia_id = :referencia_id
                      ORDER BY m.fecha_movimiento DESC");
        
        $this->bind(':referencia_id', $referencia_id);
        return $this->resultSet();
    }

    /**
     * Obtener movimientos recientes
     * @param int $limite - Número de movimientos a retornar
     * @return array - Últimos movimientos
     */
    public function getRecent($limite = 50) {
        $this->query("SELECT 
                        m.*,
                        p.nombre AS producto_nombre,
                        p.codigo AS producto_codigo,
                        u.nombre AS usuario_nombre
                      FROM Movimientos_Inventario m
                      INNER JOIN Productos_Inventario p ON m.producto_id = p.id
                      INNER JOIN Usuarios_Inventario u ON m.usuario_id = u.usuario_id
                      ORDER BY m.fecha_movimiento DESC
                      LIMIT :limite");
        
        $this->bind(':limite', $limite, PDO::PARAM_INT);
        return $this->resultSet();
    }

    /**
     * Obtener evolución del stock de un producto en el tiempo
     * @param int $producto_id - ID del producto
     * @return array - Timeline de cambios de stock
     */
    public function getStockTimeline($producto_id) {
        $this->query("SELECT 
                        m.fecha_movimiento,
                        m.tipo_movimiento,
                        m.cantidad,
                        m.stock_anterior,
                        m.stock_actual,
                        m.comentario,
                        u.nombre AS usuario_nombre
                      FROM Movimientos_Inventario m
                      INNER JOIN Usuarios_Inventario u ON m.usuario_id = u.usuario_id
                      WHERE m.producto_id = :producto_id
                      ORDER BY m.fecha_movimiento ASC");
        
        $this->bind(':producto_id', $producto_id);
        return $this->resultSet();
    }

    /**
     * Obtener estadísticas de movimientos por periodo
     * @param string $fecha_inicio - Fecha de inicio
     * @param string $fecha_fin - Fecha de fin
     * @return object - Estadísticas del periodo
     */
    public function getStatsByPeriod($fecha_inicio, $fecha_fin) {
        $this->query("SELECT 
                        COUNT(*) AS total_movimientos,
                        SUM(CASE WHEN tipo_movimiento IN ('ENTRADA', 'DEVOLUCION') THEN cantidad ELSE 0 END) AS total_entradas,
                        SUM(CASE WHEN tipo_movimiento IN ('SALIDA_SOLICITUD', 'SALIDA_MANUAL') THEN cantidad ELSE 0 END) AS total_salidas,
                        COUNT(DISTINCT producto_id) AS productos_afectados,
                        COUNT(DISTINCT usuario_id) AS usuarios_activos
                      FROM Movimientos_Inventario
                      WHERE DATE(fecha_movimiento) BETWEEN :fecha_inicio AND :fecha_fin");
        
        $this->bind(':fecha_inicio', $fecha_inicio);
        $this->bind(':fecha_fin', $fecha_fin);
        return $this->single();
    }

    /**
     * Obtener productos más movidos en un periodo
     * @param string $fecha_inicio - Fecha de inicio
     * @param string $fecha_fin - Fecha de fin
     * @param int $limite - Número de productos a retornar
     * @return array - Top productos con más movimientos
     */
    public function getTopMovedProducts($fecha_inicio, $fecha_fin, $limite = 10) {
        $this->query("SELECT 
                        p.id,
                        p.nombre,
                        p.codigo,
                        COUNT(m.id) AS numero_movimientos,
                        SUM(m.cantidad) AS total_cantidad_movida
                      FROM Movimientos_Inventario m
                      INNER JOIN Productos_Inventario p ON m.producto_id = p.id
                      WHERE DATE(m.fecha_movimiento) BETWEEN :fecha_inicio AND :fecha_fin
                      GROUP BY p.id, p.nombre, p.codigo
                      ORDER BY numero_movimientos DESC
                      LIMIT :limite");
        
        $this->bind(':fecha_inicio', $fecha_inicio);
        $this->bind(':fecha_fin', $fecha_fin);
        $this->bind(':limite', $limite, PDO::PARAM_INT);
        return $this->resultSet();
    }

    /**
     * Buscar movimientos con múltiples filtros
     * @param array $filtros - Criterios de búsqueda
     * @return array - Movimientos que coinciden
     */
    public function search($filtros) {
        $where = [];
        $params = [];
        
        if (!empty($filtros['producto_id'])) {
            $where[] = "m.producto_id = :producto_id";
            $params[':producto_id'] = $filtros['producto_id'];
        }
        
        if (!empty($filtros['usuario_id'])) {
            $where[] = "m.usuario_id = :usuario_id";
            $params[':usuario_id'] = $filtros['usuario_id'];
        }
        
        if (!empty($filtros['tipo_movimiento'])) {
            $where[] = "m.tipo_movimiento = :tipo_movimiento";
            $params[':tipo_movimiento'] = $filtros['tipo_movimiento'];
        }
        
        if (!empty($filtros['fecha_inicio'])) {
            $where[] = "DATE(m.fecha_movimiento) >= :fecha_inicio";
            $params[':fecha_inicio'] = $filtros['fecha_inicio'];
        }
        
        if (!empty($filtros['fecha_fin'])) {
            $where[] = "DATE(m.fecha_movimiento) <= :fecha_fin";
            $params[':fecha_fin'] = $filtros['fecha_fin'];
        }
        
        if (!empty($filtros['producto_nombre'])) {
            $where[] = "p.nombre LIKE :producto_nombre";
            $params[':producto_nombre'] = '%' . $filtros['producto_nombre'] . '%';
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT 
                    m.*,
                    p.nombre AS producto_nombre,
                    p.codigo AS producto_codigo,
                    p.categoria AS producto_categoria,
                    u.nombre AS usuario_nombre,
                    u.correo AS usuario_correo
                FROM Movimientos_Inventario m
                INNER JOIN Productos_Inventario p ON m.producto_id = p.id
                INNER JOIN Usuarios_Inventario u ON m.usuario_id = u.usuario_id
                {$whereClause}
                ORDER BY m.fecha_movimiento DESC";
        
        $this->query($sql);
        
        foreach ($params as $param => $value) {
            $this->bind($param, $value);
        }
        
        return $this->resultSet();
    }

    /**
     * Contar total de movimientos
     * @return int - Número total de movimientos
     */
    public function countAll() {
        $this->query("SELECT COUNT(*) AS total FROM Movimientos_Inventario");
        $result = $this->single();
        return $result ? $result->total : 0;
    }

    /**
     * Verificar consistencia de stock
     * Compara el stock actual del producto con el último movimiento registrado
     * @param int $producto_id - ID del producto
     * @return array - ['consistent' => bool, 'product_stock' => int, 'last_movement_stock' => int]
     */
    public function verifyStockConsistency($producto_id) {
        // Obtener stock actual del producto
        $productModel = new ProductModel();
        $producto = $productModel->getById($producto_id);
        
        if (!$producto) {
            return ['consistent' => false, 'message' => 'Producto no encontrado'];
        }
        
        // Obtener último movimiento
        $this->query("SELECT stock_actual 
                     FROM Movimientos_Inventario 
                     WHERE producto_id = :producto_id 
                     ORDER BY fecha_movimiento DESC 
                     LIMIT 1");
        $this->bind(':producto_id', $producto_id);
        $lastMovement = $this->single();
        
        if (!$lastMovement) {
            return [
                'consistent' => true, 
                'message' => 'Sin movimientos registrados',
                'product_stock' => $producto->stock,
                'last_movement_stock' => null
            ];
        }
        
        $consistent = ($producto->stock == $lastMovement->stock_actual);
        
        return [
            'consistent' => $consistent,
            'message' => $consistent ? 'Stock consistente' : 'Inconsistencia detectada',
            'product_stock' => $producto->stock,
            'last_movement_stock' => $lastMovement->stock_actual
        ];
    }
}
