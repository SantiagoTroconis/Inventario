<?php

class RequestModel extends Database {
    
    // Obtener todas las solicitudes
    public function getAll() {
        $this->query("SELECT s.*, 
                     u1.nombre as usuario_nombre, 
                     u1.tipo_usuario as tipo_usuario,
                     u1.usuario_id as usuario_sucursal_id,
                     u2.nombre as solicitado_nombre,
                     u2.tipo_usuario as solicitado_tipo_usuario
                     FROM solicitudes_inventario s
                     LEFT JOIN usuarios_inventario u1 ON s.solcitante_id = u1.usuario_id
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
                     LEFT JOIN usuarios_inventario u1 ON s.solcitante_id = u1.usuario_id
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
                     LEFT JOIN usuarios_inventario u1 ON s.solcitante_id = u1.usuario_id
                     LEFT JOIN usuarios_inventario u2 ON s.solicitado_id = u2.usuario_id
                     WHERE s.id = :id");
        $this->bind(':id', $id);
        return $this->single();
    }
    
    // Obtener solicitudes por usuario (como solicitante)
    public function getByUsuario($usuario_id) {
        $this->query("SELECT * FROM solicitudes_inventario 
                     WHERE solcitante_id = :usuario_id 
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
                     (solcitante_id, solicitado_id, tipo, descripcion, estado, prioridad, fecha_solicitud) 
                     VALUES (:solcitante_id, :solicitado_id, :tipo, :descripcion, :estado, :prioridad, GETDATE())");
        
        $this->bind(':solcitante_id', $data['solcitante_id']);
        $this->bind(':solicitado_id', $data['solicitado_id'] ?? null);
        $this->bind(':tipo', $data['tipo']);
        $this->bind(':descripcion', $data['descripcion'] ?? '');
        $this->bind(':estado', $data['estado'] ?? 'Pendiente');
        $this->bind(':prioridad', $data['prioridad'] ?? 'Normal');
        
        if ($this->execute()) {
            // Obtener el ID de la solicitud creada
            $this->query("SELECT SCOPE_IDENTITY() as id");
            $result = $this->single();
            return $result->id;
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
        if ($usuario_aprobacion_id) {
            $this->query("UPDATE solicitudes_inventario 
                         SET estado = :estado, 
                             usuario_aprobacion_id = :usuario_aprobacion_id,
                             fecha_aprobacion = GETDATE()
                         WHERE id = :id");
            $this->bind(':usuario_aprobacion_id', $usuario_aprobacion_id);
        } else {
            $this->query("UPDATE solicitudes_inventario SET estado = :estado WHERE id = :id");
        }
        
        $this->bind(':id', $id);
        $this->bind(':estado', $estado);
        
        return $this->execute();
    }
    
    // Eliminar solicitud
    public function delete($id) {
        // Primero eliminar detalles
        $this->query("DELETE FROM solicitud_detalles_inventario WHERE solicitud_id = :id");
        $this->bind(':id', $id);
        $this->execute();
        
        // Luego eliminar solicitud
        $this->query("DELETE FROM solicitudes_inventario WHERE id = :id");
        $this->bind(':id', $id);
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
                     LEFT JOIN usuarios_inventario u1 ON s.solcitante_id = u1.usuario_id
                     LEFT JOIN usuarios_inventario u2 ON s.solicitado_id = u2.usuario_id
                     ORDER BY s.fecha_solicitud DESC");
        $this->bind(':limite', $limite);
        return $this->resultSet();
    }
}
