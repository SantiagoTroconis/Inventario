# Modelos

En esta carpeta se colocan los modelos de la aplicación.

Los modelos son responsables de la lógica de negocio y la interacción con la base de datos.

## Ejemplo de modelo:

```php
<?php
class Usuario {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function obtenerUsuarios() {
        $this->db->query("SELECT * FROM usuarios");
        return $this->db->resultSet();
    }
}
```
