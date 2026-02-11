<?php

use BcMath\Number;

class RequestModel extends Database {
    
    // Obtener todas las solicitudes (excluye contra-ofertas)
    public function getAll() {
        $this->query("SELECT s.*, 
                     u1.nombre as usuario_nombre, 
                     u1.tipo_usuario as tipo_usuario,
                     u1.usuario_id as usuario_sucursal_id,
                     u2.nombre as solicitado_nombre,
                     u2.tipo_usuario as solicitado_tipo_usuario
                     FROM solicitudes_inventario s
                     LEFT JOIN usuarios_inventario u1 ON s.solicitante_id = u1.usuario_id
                     LEFT JOIN usuarios_inventario u2 ON s.solicitado_id = u2.usuario_id
                     ORDER BY s.fecha_solicitud DESC");
        return $this->resultSet();
    }
    
    // Obtener solicitudes por estado
    public function getByEstado($estado) {
        $this->query("SELECT s.*, 
                     u1.nombre as usuario_nombre,
                     u2.nombre as solicitado_nombre
                     FROM solicitudes_inventario s
                     LEFT JOIN usuarios_inventario u1 ON s.solicitante_id = u1.usuario_id
                     LEFT JOIN usuarios_inventario u2 ON s.solicitado_id = u2.usuario_id
                     WHERE s.estado = :estado
                     ORDER BY s.fecha_solicitud DESC");
        $this->bind(':estado', $estado);
        return $this->resultSet();
    }
    
    // Obtener solicitud por ID
    public function getById($id) {
        $this->query("SELECT s.*, 
                     u1.nombre as usuario_nombre,
                     u2.nombre as solicitado_nombre
                     FROM solicitudes_inventario s
                     LEFT JOIN usuarios_inventario u1 ON s.solicitante_id = u1.usuario_id
                     LEFT JOIN usuarios_inventario u2 ON s.solicitado_id = u2.usuario_id
                     WHERE s.id = :id");
        $this->bind(':id', $id);
        return $this->single();
    }
    
    // Obtener solicitudes por usuario (como solicitante)
    public function getByUsuario($usuario_id) {
        $this->query("SELECT * FROM solicitudes_inventario 
                     WHERE solicitante_id = :usuario_id 
                     ORDER BY fecha_solicitud DESC");
        $this->bind(':usuario_id', $usuario_id);
        return $this->resultSet();
    }
    
    // Obtener detalles de una solicitud
    public function getDetalles($solicitud_id) {
        $this->query("SELECT sd.*, p.nombre as producto_nombre, p.codigo as producto_codigo
                     FROM solicitud_detalles_inventario sd
                     LEFT JOIN productos_inventario p ON sd.producto_id = p.id
                     WHERE sd.solicitud_id = :solicitud_id");
        $this->bind(':solicitud_id', $solicitud_id);
        return $this->resultSet();
    }
    
    // Crear nueva solicitud
    public function create($data) {
        $this->query("INSERT INTO solicitudes_inventario 
                     (solicitante_id, solicitado_id, tipo, descripcion, estado, prioridad, fecha_solicitud) 
                     VALUES (:solicitante_id, :solicitado_id, :tipo, :descripcion, :estado, :prioridad, NOW())");
        
        $this->bind(':solicitante_id', $data['solicitante_id']);
        $this->bind(':solicitado_id', $data['solicitado_id'] ?? null);
        $this->bind(':tipo', $data['tipo']);
        $this->bind(':descripcion', $data['descripcion'] ?? '');
        $this->bind(':estado', $data['estado'] ?? 'Pendiente');
        $this->bind(':prioridad', $data['prioridad'] ?? 'Normal');
        
        if ($this->execute()) {
            // Obtener el ID de la solicitud creada usando @@IDENTITY en lugar de SCOPE_IDENTITY()
            $this->query("SELECT @@IDENTITY as id");
            $result = $this->single();
            
            if ($result && isset($result->id) && $result->id > 0) {
                return (int)$result->id;
            }
        }
        return false;
    }
    
    // Agregar detalle a solicitud
    public function addDetalle($solicitud_id, $producto_id, $cantidad, $observaciones = '') {
        $this->query("INSERT INTO solicitud_detalles_inventario 
                     (solicitud_id, producto_id, cantidad, observaciones) 
                     VALUES (:solicitud_id, :producto_id, :cantidad, :observaciones)");
        
        $this->bind(':solicitud_id', $solicitud_id);
        $this->bind(':producto_id', $producto_id);
        $this->bind(':cantidad', $cantidad);
        $this->bind(':observaciones', $observaciones);
        
        return $this->execute();
    }
    
    // Actualizar solicitud
    public function update($id, $data) {
        $this->query("UPDATE solicitudes_inventario 
                     SET tipo = :tipo, descripcion = :descripcion, 
                         estado = :estado, prioridad = :prioridad,
                         solicitado_id = :solicitado_id
                     WHERE id = :id");
        
        $this->bind(':id', $id);
        $this->bind(':tipo', $data['tipo']);
        $this->bind(':descripcion', $data['descripcion']);
        $this->bind(':estado', $data['estado']);
        $this->bind(':prioridad', $data['prioridad']);
        $this->bind(':solicitado_id', $data['solicitado_id'] ?? null);
        
        return $this->execute();
    }
    
    // Actualizar estado de solicitud
    public function updateEstado($id, $estado, $usuario_aprobacion_id = null) {
        // Si se está aprobando, primero descontar stock
        if ($estado === 'Aprobada' || $estado === 'Aprobado') {
            // Obtener detalles de la solicitud
            $detalles = $this->getDetalles($id);
            
            // Descontar stock de cada producto
            foreach ($detalles as $detalle) {
                $this->query("UPDATE productos_inventario 
                             SET stock = stock - :cantidad 
                             WHERE id = :producto_id AND stock >= :cantidad");
                $this->bind(':cantidad', $detalle->cantidad);
                $this->bind(':producto_id', $detalle->producto_id);
                
                if (!$this->execute()) {
                    // Si falla (por ejemplo, stock insuficiente), no continuar
                    return false;
                }
            }
        }
        
        // Actualizar el estado de la solicitud
        if ($usuario_aprobacion_id) {
            $this->query("UPDATE solicitudes_inventario 
                         SET estado = :estado, 
                             usuario_aprobacion_id = :usuario_aprobacion_id,
                             fecha_aprobacion = GETDATE()
                         WHERE id = :id");
            $this->bind(':estado', $estado);
            $this->bind(':usuario_aprobacion_id', $usuario_aprobacion_id);
            $this->bind(':id', intval($id));
        } else {
            $this->query("UPDATE solicitudes_inventario SET estado = :estado WHERE id = :id");
            $this->bind(':estado', $estado);
            $this->bind(':id', intval($id));
        }
        
        return $this->execute();
    }
    
    // Eliminar solicitud
    public function delete($id) {
        // Primero eliminar detalles
        $this->query("DELETE FROM solicitud_detalles_inventario WHERE solicitud_id = :id");
        $this->bind(':id', intval($id));
        $this->execute();
        
        // Luego eliminar solicitud
        $this->query("DELETE FROM solicitudes_inventario WHERE id = :id");
        $this->bind(':id', intval($id));
        return $this->execute();
    }
    
    // Obtener estadísticas de solicitudes
    public function getStats() {
        $this->query("SELECT 
                     COUNT(*) as total,
                     SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                     SUM(CASE WHEN estado = 'Aprobada' THEN 1 ELSE 0 END) as aprobadas,
                     SUM(CASE WHEN estado = 'Rechazada' THEN 1 ELSE 0 END) as rechazadas,
                     SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) as completadas
                     FROM solicitudes_inventario");
        return $this->single();
    }
    
    // Obtener solicitudes recientes
    public function getRecientes($limite = 10) {
        $this->query("SELECT s.*, 
                     u1.nombre as usuario_nombre,
                     u2.nombre as solicitado_nombre
                     FROM solicitudes_inventario s
                     LEFT JOIN usuarios_inventario u1 ON s.solicitante_id = u1.usuario_id
                     LEFT JOIN usuarios_inventario u2 ON s.solicitado_id = u2.usuario_id
                     ORDER BY s.fecha_solicitud DESC LIMIT :limite");
        $this->bind(':limite', intval($limite));
        return $this->resultSet();
    }
    
    // Obtener solicitudes pendientes dirigidas a un usuario específico
    public function getPendingRequestsForUser($usuario_id, $limite = 10) {
        $this->query("SELECT s.*, 
                     u1.nombre as usuario_nombre,
                     u1.tipo_usuario as solicitante_tipo,
                     u2.nombre as solicitado_nombre
                     FROM solicitudes_inventario s
                     LEFT JOIN usuarios_inventario u1 ON s.solicitante_id = u1.usuario_id
                     LEFT JOIN usuarios_inventario u2 ON s.solicitado_id = u2.usuario_id
                     WHERE s.solicitado_id = :usuario_id AND s.estado = 'Pendiente'
                     ORDER BY s.fecha_solicitud DESC LIMIT :limite");
        $this->bind(':usuario_id', $usuario_id);
        $this->bind(':limite', intval($limite));
        return $this->resultSet();
    }
    
    // Crear una nueva negociación
    public function createNegotiation($solicitud_id, $usuario_id, $items, $notas = '') {
        try {
            $this->beginTransaction();

            // 1. Crear registro de negociación
            $this->query("INSERT INTO solicitud_negociaciones (solicitud_id, usuario_id, notas, estado) 
                         VALUES (:solicitud_id, :usuario_id, :notas, 'Pendiente')");
            $this->bind(':solicitud_id', $solicitud_id);
            $this->bind(':usuario_id', $usuario_id);
            $this->bind(':notas', $notas);
            $this->execute();

            $this->query("SELECT LAST_INSERT_ID() as id");
            $negociacionId = $this->single()->id;

            // 2. Insertar detalles
            foreach ($items as $item) {
                // Ensure observes is set
                $observaciones = $item['observaciones'] ?? '';
                $this->query("INSERT INTO solicitud_negociacion_detalles (negociacion_id, producto_id, cantidad_propuesta, observaciones) 
                             VALUES (:negociacion_id, :producto_id, :cantidad, :observaciones)");
                $this->bind(':negociacion_id', $negociacionId);
                $this->bind(':producto_id', $item['producto_id']);
                $this->bind(':cantidad', $item['cantidad']);
                $this->bind(':observaciones', $observaciones);
                $this->execute();
            }

            // 3. Actualizar estado de la solicitud original
            $this->query("UPDATE solicitudes_inventario SET estado = 'En Negociación' WHERE id = :id");
            $this->bind(':id', $solicitud_id);
            $this->execute();

            $this->commit();
            return $negociacionId;

        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }


    public function getNegotiationBySolicitudId($solicitud_id) {
        // Obtenemos la última negociación
        $this->query("SELECT n.*, u.nombre as usuario_nombre 
                        FROM solicitud_negociaciones n
                        LEFT JOIN usuarios_inventario u ON n.usuario_id = u.usuario_id
                        WHERE n.solicitud_id = :solicitud_id 
                        ORDER BY n.fecha_creacion DESC LIMIT 1");
        $this->bind(':solicitud_id', $solicitud_id);
        $row = $this->single();
    
        if ($row) {
            return $row;
        }
        return false;
    }

    // Obtener detalles de la negociación
    public function getNegotiationDetails($negociacion_id) {
        $this->query("SELECT d.*, p.nombre as producto_nombre, p.codigo as producto_codigo, d.cantidad_propuesta as cantidad
                     FROM solicitud_negociacion_detalles d
                     LEFT JOIN productos_inventario p ON d.producto_id = p.id
                     WHERE d.negociacion_id = :id");
        $this->bind(':id', $negociacion_id);
        return $this->resultSet();
    }

    // Aceptar negociación
    public function acceptNegotiation($negociacion_id, $solicitud_id, $usuario_id) {
        try {
            $this->beginTransaction();

            // 1. Marcar negociación como Aceptada
            $this->query("UPDATE solicitud_negociaciones SET estado = 'Aceptada' WHERE id = :id");
            $this->bind(':id', $negociacion_id);
            $this->execute();

            // 2. Actualizar los items de la solicitud original con los valores de la negociación
            $items = $this->getNegotiationDetails($negociacion_id);
            
            $this->query("DELETE FROM solicitud_detalles_inventario WHERE solicitud_id = :id");
            $this->bind(':id', $solicitud_id);
            $this->execute();

            foreach ($items as $item) {
                // $item->cantidad is aliased in getNegotiationDetails
                $this->addDetalle($solicitud_id, $item->producto_id, $item->cantidad, $item->observaciones);
            }

            // 3. Aprobar la solicitud con estado especial
            $this->query("UPDATE solicitudes_inventario 
                         SET estado = 'Aprobada con Cambios', 
                             usuario_aprobacion_id = :user_id,
                             fecha_aprobacion = NOW()
                         WHERE id = :id");
            $this->bind(':user_id', $usuario_id);
            $this->bind(':id', $solicitud_id);
            $this->execute();

            // Descontar Stock
            foreach ($items as $item) {
                 $this->query("UPDATE productos_inventario 
                             SET stock = stock - :cantidad 
                             WHERE id = :producto_id AND stock >= :cantidad");
                 $this->bind(':cantidad', $item->cantidad); // Use aliased amount
                 $this->bind(':producto_id', $item->producto_id);
                 $this->execute();
            }

            $this->commit();
            return true;

        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }

    // Rechazar negociación
    public function rejectNegotiation($negociacion_id, $solicitud_id) {
        try {
            $this->beginTransaction();

            // 1. Marcar negociación como Rechazada
            $this->query("UPDATE solicitud_negociaciones SET estado = 'Rechazada' WHERE id = :id");
            $this->bind(':id', $negociacion_id);
            $this->execute();

            // 2. Marcar solicitud original como Rechazada también
            $this->query("UPDATE solicitudes_inventario SET estado = 'Rechazada' WHERE id = :id");
            $this->bind(':id', $solicitud_id);
            $this->execute();

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }
}
