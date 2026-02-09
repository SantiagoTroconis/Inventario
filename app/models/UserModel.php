<?php


class UserModel extends Database
{

    // Obtener todos los usuarios
    public function getAll()
    {
        $this->query("SELECT * FROM usuarios");
        return $this->resultSet();
    }

    // Obtener usuario por ID
    public function getById($id)
    {
        $this->query("SELECT * FROM usuarios WHERE id = :id");
        $this->bind(':id', $id);
        return $this->single();
    }

    // Obtener usuario por email
    public function getByEmail($email)
    {
        $this->query("SELECT * FROM usuarios WHERE email = :email");
        $this->bind(':email', $email);
        return $this->single();
    }

    // Obtener usuario por username
    public function login($username, $password)
    {
        $this->query("SELECT * FROM Usuarios_Inventario WHERE nombre = :username and password = :password");
        $this->bind(':username', $username);
        $this->bind(':password', $password);
        return $this->single();
    }

    // Obtener el usuario Administrador
    public function getAdministrador()
    {
        $this->query("SELECT TOP 1 usuario_id, nombre FROM Usuarios_Inventario WHERE tipo_usuario = 'Administrador'");
        return $this->single();
    }

    // Obtener todos los usuarios de tipo Sucursal activos
    public function getSucursales()
    {
        $this->query("SELECT usuario_id, nombre FROM Usuarios_Inventario WHERE tipo_usuario = 'Sucursal' ORDER BY nombre");
        return $this->resultSet();
    }

    // Crear nuevo usuario
    public function create($data)
    {
        $this->query("INSERT INTO usuarios (username, email, password, nombre, rol) 
                     VALUES (:username, :email, :password, :nombre, :rol)");

        $this->bind(':username', $data['username']);
        $this->bind(':email', $data['email']);
        $this->bind(':password', $data['password']);
        $this->bind(':nombre', $data['nombre']);
        $this->bind(':rol', $data['rol']);
        $this->bind(':activo', $data['activo'] ?? 1);

        return $this->execute();
    }

    // Actualizar usuario
    public function update($id, $data)
    {
        $this->query("UPDATE usuarios 
                     SET username = :username, email = :email, nombre = :nombre, 
                         rol = :rol, activo = :activo 
                     WHERE id = :id");

        $this->bind(':id', $id);
        $this->bind(':username', $data['username']);
        $this->bind(':email', $data['email']);
        $this->bind(':nombre', $data['nombre']);
        $this->bind(':rol', $data['rol']);
        $this->bind(':activo', $data['activo']);

        return $this->execute();
    }

    // Actualizar contraseña
    public function updatePassword($id, $password)
    {
        $this->query("UPDATE usuarios SET password = :password WHERE id = :id");
        $this->bind(':id', $id);
        $this->bind(':password', $password);
        return $this->execute();
    }

    // Eliminar usuario
    public function delete($id)
    {
        $this->query("DELETE FROM usuarios WHERE id = :id");
        $this->bind(':id', $id);
        return $this->execute();
    }

    // Verificar si existe usuario
    public function exists($username, $email)
    {
        $this->query("SELECT id FROM usuarios WHERE username = :username OR email = :email");
        $this->bind(':username', $username);
        $this->bind(':email', $email);
        return $this->single() ? true : false;
    }
}
