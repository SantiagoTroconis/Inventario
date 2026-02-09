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
                     WHERE (s.is_counter_offer = 0 OR s.is_counter_offer IS NULL)
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
                     (solicitante_id, solicitado_id, tipo, descripcion, estado, prioridad, fecha_solicitud, parent_request_id, is_counter_offer, counter_offer_notes) 
                     VALUES (:solicitante_id, :solicitado_id, :tipo, :descripcion, :estado, :prioridad, GETDATE(), :parent_request_id, :is_counter_offer, :counter_offer_notes)");
        
        $this->bind(':solicitante_id', $data['solicitante_id']);
        $this->bind(':solicitado_id', $data['solicitado_id'] ?? null);
        $this->bind(':tipo', $data['tipo']);
        $this->bind(':descripcion', $data['descripcion'] ?? '');
        $this->bind(':estado', $data['estado'] ?? 'Pendiente');
        $this->bind(':prioridad', $data['prioridad'] ?? 'Normal');
        $this->bind(':parent_request_id', $data['parent_request_id'] ?? null);
        $this->bind(':is_counter_offer', $data['is_counter_offer'] ?? 0);
        $this->bind(':counter_offer_notes', $data['counter_offer_notes'] ?? null);
        
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
        $this->query("SELECT TOP :limite s.*, 
                     u1.nombre as usuario_nombre,
                     u2.nombre as solicitado_nombre
                     FROM solicitudes_inventario s
                     LEFT JOIN usuarios_inventario u1 ON s.solicitante_id = u1.usuario_id
                     LEFT JOIN usuarios_inventario u2 ON s.solicitado_id = u2.usuario_id
                     ORDER BY s.fecha_solicitud DESC");
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
                     ORDER BY s.fecha_solicitud DESC");
        $this->bind(':usuario_id', $usuario_id);
        return $this->resultSet();
    }
    
    // Crear contra-oferta (modificación de solicitud)
    public function createCounterOffer($parent_request_id, $admin_id, $modified_details, $notes = '') {
        // 1. Obtener solicitud original
        $originalRequest = $this->getById($parent_request_id);
        if (!$originalRequest) {
            return false;
        }
        
        // 2. Crear nueva solicitud como contra-oferta (roles invertidos)
        $counterOfferData = [
            'solicitante_id' => $admin_id, // Admin ahora es el solicitante
            'solicitado_id' => $originalRequest->solicitante_id, // Original solicitante ahora recibe
            'tipo' => $originalRequest->tipo,
            'descripcion' => 'Contra-oferta: ' . ($originalRequest->descripcion ?? ''),
            'estado' => 'Pendiente',
            'prioridad' => $originalRequest->prioridad,
            'parent_request_id' => $parent_request_id,
            'is_counter_offer' => 1,
            'counter_offer_notes' => $notes
        ];
        
        $counterOfferId = $this->create($counterOfferData);
        
        if ($counterOfferId) {
            // 3. Agregar detalles modificados a la contra-oferta
            foreach ($modified_details as $detail) {
                $this->addDetalle(
                    $counterOfferId,
                    $detail['producto_id'],
                    $detail['cantidad'],
                    $detail['observaciones'] ?? ''
                );
            }
            
            // 4. Actualizar estado de solicitud original a "En Negociación"
            $this->updateEstado($parent_request_id, 'En Negociación');
            
            return $counterOfferId;
        }
        
        return false;
    }
    
    // Obtener contra-ofertas de una solicitud
    public function getCounterOffers($parent_request_id) {
        $this->query("SELECT s.*, 
                     u1.nombre as usuario_nombre,
                     u2.nombre as solicitado_nombre
                     FROM solicitudes_inventario s
                     LEFT JOIN usuarios_inventario u1 ON s.solicitante_id = u1.usuario_id
                     LEFT JOIN usuarios_inventario u2 ON s.solicitado_id = u2.usuario_id
                     WHERE s.parent_request_id = :parent_request_id
                     AND s.is_counter_offer = 1
                     ORDER BY s.fecha_solicitud DESC");
        $this->bind(':parent_request_id', $parent_request_id);
        return $this->resultSet();
    }
    
    // Aceptar contra-oferta
    public function acceptCounterOffer($counter_offer_id, $parent_request_id, $user_id) {
        // 1. Aprobar la contra-oferta
        $this->updateEstado($counter_offer_id, 'Aprobada', $user_id);
        
        // 2. Actualizar solicitud original como "Aprobada con Cambios"
        $this->updateEstado($parent_request_id, 'Aprobada con Cambios', $user_id);
        
        return true;
    }
    
    // Rechazar contra-oferta
    public function rejectCounterOffer($counter_offer_id, $parent_request_id, $user_id) {
        // 1. Rechazar la contra-oferta
        $this->updateEstado($counter_offer_id, 'Rechazada', $user_id);
        
        // 2. Rechazar también la solicitud original (fin del ciclo de negociación)
        $this->updateEstado($parent_request_id, 'Rechazada');
        
        return true;
    }
    
    // Obtener contra-oferta por parent_request_id
    public function getCounterOfferByParentId($parent_id) {
        $this->query("SELECT s.*, 
                     u1.nombre as usuario_nombre,
                     u2.nombre as solicitado_nombre
                     FROM solicitudes_inventario s
                     LEFT JOIN usuarios_inventario u1 ON s.solicitante_id = u1.usuario_id
                     LEFT JOIN usuarios_inventario u2 ON s.solicitado_id = u2.usuario_id
                     WHERE s.parent_request_id = :parent_id 
                     AND s.is_counter_offer = 1
                     ORDER BY s.fecha_solicitud DESC");
        $this->bind(':parent_id', $parent_id);
        return $this->single();
    }
}
