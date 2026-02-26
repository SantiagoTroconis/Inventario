<?php

class UserModel extends Database
{
    // ─── AUTH ──────────────────────────────────────────────────────────────

    public function login($username, $password)
    {
        $this->query("SELECT * FROM Usuarios_Inventario WHERE nombre = :username AND password = :password");
        $this->bind(':username', $username);
        $this->bind(':password', $password);
        return $this->single();
    }

    // ─── HELPERS USED BY OTHER MODELS ──────────────────────────────────────

    public function getAdministrador()
    {
        $this->query("SELECT usuario_id, nombre FROM Usuarios_Inventario WHERE tipo_usuario = 'Administrador' LIMIT 1");
        return $this->single();
    }

    public function getSucursalesSimple()
    {
        $this->query("SELECT usuario_id, nombre FROM Usuarios_Inventario WHERE tipo_usuario = 'Sucursal' ORDER BY nombre");
        return $this->resultSet();
    }

    // ─── SUCURSALES ────────────────────────────────────────────────────────

    /**
     * Returns all sucursales rows joined with their linked user account
     * and a count of agentes assigned.
     */
    public function getAllSucursales()
    {
        $this->query("SELECT s.*,
                             u.usuario_id,
                             u.nombre   AS usuario_nombre,
                             u.correo,
                             u.status,
                             (SELECT COUNT(*) FROM agentes a WHERE a.sucursal_id = s.id) AS total_agentes
                      FROM sucursales s
                      LEFT JOIN Usuarios_Inventario u ON u.sucursal_id = s.id AND u.tipo_usuario = 'Sucursal'
                      ORDER BY s.nombre");
        return $this->resultSet();
    }

    public function getSucursalById($id)
    {
        $this->query("SELECT s.*,
                             u.usuario_id, u.nombre AS usuario_nombre, u.correo, u.status
                      FROM sucursales s
                      LEFT JOIN Usuarios_Inventario u ON u.sucursal_id = s.id AND u.tipo_usuario = 'Sucursal'
                      WHERE s.id = :id");
        $this->bind(':id', $id);
        return $this->single();
    }

    /**
     * Creates a sucursal profile row AND a matching Usuarios_Inventario login account.
     * $data keys: nombre, direccion, ciudad, contacto, telefono, correo, password
     */
    public function createSucursal($data)
    {
        try {
            $this->beginTransaction();

            // 1. Insert sucursal profile
            $this->query("INSERT INTO sucursales (nombre, direccion, ciudad, contacto, telefono)
                          VALUES (:nombre, :direccion, :ciudad, :contacto, :telefono)");
            $this->bind(':nombre',    $data['nombre']);
            $this->bind(':direccion', $data['direccion'] ?? null);
            $this->bind(':ciudad',    $data['ciudad']    ?? null);
            $this->bind(':contacto',  $data['contacto']  ?? null);
            $this->bind(':telefono',  $data['telefono']  ?? null);
            $this->execute();

            $this->query("SELECT LAST_INSERT_ID() as id");
            $sucursalId = $this->single()->id;

            // 2. Insert login user
            $this->query("INSERT INTO Usuarios_Inventario
                            (nombre, correo, password, tipo_usuario, status, sucursal_id)
                          VALUES
                            (:nombre, :correo, :password, 'Sucursal', 1, :sucursal_id)");
            $this->bind(':nombre',      $data['nombre']);
            $this->bind(':correo',      $data['correo']    ?? null);
            $this->bind(':password',    $data['password']);
            $this->bind(':sucursal_id', $sucursalId);
            $this->execute();

            $this->commit();
            return $sucursalId;
        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }

    /**
     * Updates sucursal profile and syncs the user account name/correo.
     */
    public function updateSucursal($id, $data)
    {
        try {
            $this->beginTransaction();

            $this->query("UPDATE sucursales
                          SET nombre = :nombre, direccion = :direccion,
                              ciudad = :ciudad, contacto = :contacto, telefono = :telefono
                          WHERE id = :id");
            $this->bind(':nombre',    $data['nombre']);
            $this->bind(':direccion', $data['direccion'] ?? null);
            $this->bind(':ciudad',    $data['ciudad']    ?? null);
            $this->bind(':contacto',  $data['contacto']  ?? null);
            $this->bind(':telefono',  $data['telefono']  ?? null);
            $this->bind(':id',        $id);
            $this->execute();

            // Sync user account
            $sets  = "nombre = :nombre, correo = :correo";
            $binds = [':nombre' => $data['nombre'], ':correo' => $data['correo'] ?? null, ':suc_id' => $id];
            if (!empty($data['password'])) {
                $sets .= ", password = :password";
                $binds[':password'] = $data['password'];
            }
            $this->query("UPDATE Usuarios_Inventario SET $sets WHERE sucursal_id = :suc_id AND tipo_usuario = 'Sucursal'");
            foreach ($binds as $k => $v) $this->bind($k, $v);
            $this->execute();

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }

    public function toggleSucursalStatus($id)
    {
        try {
            $this->beginTransaction();

            $this->query("UPDATE sucursales SET estado = IF(estado='Activa','Inactiva','Activa') WHERE id = :id");
            $this->bind(':id', $id);
            $this->execute();

            $this->query("UPDATE Usuarios_Inventario SET status = IF(status=1,0,1) WHERE sucursal_id = :id AND tipo_usuario='Sucursal'");
            $this->bind(':id', $id);
            $this->execute();

            // Return new state
            $this->query("SELECT estado FROM sucursales WHERE id = :id");
            $this->bind(':id', $id);
            $row = $this->single();

            $this->commit();
            return $row->estado ?? null;
        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }

    public function deleteSucursal($id)
    {
        // Prevent delete if agentes are assigned
        $this->query("SELECT COUNT(*) as cnt FROM agentes WHERE sucursal_id = :id");
        $this->bind(':id', $id);
        if ($this->single()->cnt > 0) return ['success' => false, 'message' => 'No se puede eliminar: la sucursal tiene agentes asignados.'];

        try {
            $this->beginTransaction();
            $this->query("DELETE FROM Usuarios_Inventario WHERE sucursal_id = :id AND tipo_usuario='Sucursal'");
            $this->bind(':id', $id); $this->execute();
            $this->query("DELETE FROM sucursales WHERE id = :id");
            $this->bind(':id', $id); $this->execute();
            $this->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->rollBack();
            return ['success' => false, 'message' => 'Error al eliminar la sucursal.'];
        }
    }

    // ─── AGENTES ───────────────────────────────────────────────────────────

    public function getAllAgentes()
    {
        $this->query("SELECT a.id, a.rol, a.telefono, a.created_at,
                             u.usuario_id, u.nombre, u.correo, u.status,
                             s.id AS sucursal_id, s.nombre AS sucursal_nombre
                      FROM agentes a
                      JOIN Usuarios_Inventario u ON u.usuario_id = a.usuario_id
                      JOIN sucursales s ON s.id = a.sucursal_id
                      ORDER BY u.nombre");
        return $this->resultSet();
    }

    public function getAgentesBySucursal($sucursal_id)
    {
        $this->query("SELECT a.id, a.rol, a.telefono, a.created_at,
                             u.usuario_id, u.nombre, u.correo, u.status,
                             s.id AS sucursal_id, s.nombre AS sucursal_nombre
                      FROM agentes a
                      JOIN Usuarios_Inventario u ON u.usuario_id = a.usuario_id
                      JOIN sucursales s ON s.id = a.sucursal_id
                      WHERE a.sucursal_id = :sucursal_id
                      ORDER BY u.nombre");
        $this->bind(':sucursal_id', $sucursal_id);
        return $this->resultSet();
    }

    public function getAgenteById($id)
    {
        $this->query("SELECT a.id, a.rol, a.telefono, a.sucursal_id,
                             u.usuario_id, u.nombre, u.correo, u.status
                      FROM agentes a
                      JOIN Usuarios_Inventario u ON u.usuario_id = a.usuario_id
                      WHERE a.id = :id");
        $this->bind(':id', $id);
        return $this->single();
    }

    /**
     * Creates an Agente login user + agentes profile row.
     * $data keys: nombre, correo, password, rol, sucursal_id, telefono
     */
    public function createAgente($data)
    {
        try {
            $this->beginTransaction();

            $this->query("INSERT INTO Usuarios_Inventario
                            (nombre, correo, password, tipo_usuario, status, sucursal_id)
                          VALUES
                            (:nombre, :correo, :password, 'Agente', 1, :sucursal_id)");
            $this->bind(':nombre',      $data['nombre']);
            $this->bind(':correo',      $data['correo']    ?? null);
            $this->bind(':password',    $data['password']);
            $this->bind(':sucursal_id', $data['sucursal_id']);
            $this->execute();

            $this->query("SELECT LAST_INSERT_ID() as id");
            $usuarioId = $this->single()->id;

            $this->query("INSERT INTO agentes (usuario_id, sucursal_id, rol, telefono)
                          VALUES (:usuario_id, :sucursal_id, :rol, :telefono)");
            $this->bind(':usuario_id',  $usuarioId);
            $this->bind(':sucursal_id', $data['sucursal_id']);
            $this->bind(':rol',         $data['rol']      ?? 'Agente');
            $this->bind(':telefono',    $data['telefono'] ?? null);
            $this->execute();

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }

    public function updateAgente($id, $data)
    {
        try {
            $agente = $this->getAgenteById($id);
            if (!$agente) return false;

            $this->beginTransaction();

            // Update agentes profile
            $this->query("UPDATE agentes SET sucursal_id = :sucursal_id, rol = :rol, telefono = :telefono WHERE id = :id");
            $this->bind(':sucursal_id', $data['sucursal_id']);
            $this->bind(':rol',         $data['rol'] ?? 'Agente');
            $this->bind(':telefono',    $data['telefono'] ?? null);
            $this->bind(':id',          $id);
            $this->execute();

            // Update user account (sync sucursal_id too)
            $sets  = "nombre = :nombre, correo = :correo, sucursal_id = :sucursal_id";
            $binds = [':nombre' => $data['nombre'], ':correo' => $data['correo'] ?? null, ':sucursal_id' => $data['sucursal_id'], ':uid' => $agente->usuario_id];
            if (!empty($data['password'])) {
                $sets .= ", password = :password";
                $binds[':password'] = $data['password'];
            }
            $this->query("UPDATE Usuarios_Inventario SET $sets WHERE usuario_id = :uid");
            foreach ($binds as $k => $v) $this->bind($k, $v);
            $this->execute();

            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }

    public function toggleAgenteStatus($id)
    {
        try {
            $agente = $this->getAgenteById($id);
            if (!$agente) return false;

            $this->beginTransaction();
            $this->query("UPDATE Usuarios_Inventario SET status = IF(status=1,0,1) WHERE usuario_id = :uid");
            $this->bind(':uid', $agente->usuario_id);
            $this->execute();

            $this->query("SELECT status FROM Usuarios_Inventario WHERE usuario_id = :uid");
            $this->bind(':uid', $agente->usuario_id);
            $newStatus = $this->single()->status;

            $this->commit();
            return $newStatus;
        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }

    public function deleteAgente($id)
    {
        try {
            $agente = $this->getAgenteById($id);
            if (!$agente) return false;

            $this->beginTransaction();
            $this->query("DELETE FROM agentes WHERE id = :id");
            $this->bind(':id', $id); $this->execute();
            $this->query("DELETE FROM Usuarios_Inventario WHERE usuario_id = :uid");
            $this->bind(':uid', $agente->usuario_id); $this->execute();
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollBack();
            return false;
        }
    }

    // ─── LEGACY (kept for backwards compat) ───────────────────────────────

    public function getAll()
    {
        $this->query("SELECT * FROM Usuarios_Inventario");
        return $this->resultSet();
    }

    public function getById($id)
    {
        $this->query("SELECT * FROM Usuarios_Inventario WHERE usuario_id = :id");
        $this->bind(':id', $id);
        return $this->single();
    }

    public function getSucursales()
    {
        return $this->getSucursalesSimple();
    }
}
