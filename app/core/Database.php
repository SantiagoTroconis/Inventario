<?php
// Clase para la conexión a la base de datos usando MySQL (PDO)
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $dbh;
    private $stmt;
    private $error;

    public function __construct() {
        // Configurar conexión DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        // Crear una nueva instancia de PDO
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            // $this->dbh->exec("set names utf8mb4"); // Ya incluido en DSN
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            die('Error de conexión a la base de datos: ' . $this->error);
        }
    }

    // Preparar consulta
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Vincular variables
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Ejecutar consulta y devolver true/false
    public function execute() {
        return $this->stmt->execute();
    }

    // Obtener el ID del último registro insertado
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    // Obtener un conjunto de registros como array de objetos
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Obtener un solo registro como objeto
    public function single() {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_OBJ);
    }

    // Obtener cantidad de filas afectadas
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Iniciar transacción
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    // Confirmar transacción
    public function commit() {
        return $this->dbh->commit();
    }

    // Revertir transacción
    public function rollBack() {
        return $this->dbh->rollBack();
    }
    
    // Cerrar conexión (opcional en PDO, sucede al destruir objeto)
    public function __destruct() {
        $this->dbh = null;
    }
}

/*
// CODIGO ANTERIOR (SQL SERVER - sqlsrv)
// Clase para la conexión a la base de datos usando sqlsrv
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;

    private $conn;
    private $stmt;
    private $sql;
    private $params = [];
    private $error;

    public function __construct() {
        // Verificar si el driver SQL Server está instalado
        if (!function_exists('sqlsrv_connect')) {
            die('Error: El driver SQL Server (sqlsrv) no está instalado. Por favor, instálelo desde: https://docs.microsoft.com/en-us/sql/connect/php/download-drivers-php-sql-server');
        }
        
        // Configurar opciones de conexión
        $connectionOptions = [
            "Database" => $this->dbname,
            "CharacterSet" => "UTF-8",
            "TrustServerCertificate" => true
        ];
        
        // Si user y pass están vacíos, usar autenticación de Windows
        if (!empty($this->user) && !empty($this->pass)) {
            $connectionOptions["Uid"] = $this->user;
            $connectionOptions["PWD"] = $this->pass;
        }

        // Crear conexión
        $this->conn = sqlsrv_connect($this->host, $connectionOptions);

        if ($this->conn === false) {
            $errors = sqlsrv_errors();
            $this->error = print_r($errors, true);
            die('Error de conexión a la base de datos: ' . $this->error);
        }
    }

    // Preparar consulta
    public function query($sql) {
        $this->sql = $sql;
        $this->params = [];
    }

    // Vincular valores
    public function bind($param, $value, $type = null) {
        // En sqlsrv, usamos ? en lugar de :param, así que almacenamos los valores en orden
        $this->params[] = $value;
    }

    // Ejecutar consulta
    public function execute() {
        // Reemplazar :param con ? para sqlsrv
        $sql = $this->sql;
        $paramNames = [];
        
        // Encontrar todos los parámetros nombrados :param
        preg_match_all('/:(\w+)/', $sql, $matches);
        if (!empty($matches[0])) {
            $paramNames = $matches[0];
            // Reemplazar parámetros nombrados con ?
            $sql = preg_replace('/:(\w+)/', '?', $sql);
        }
        
        if (!empty($this->params)) {
            $this->stmt = sqlsrv_query($this->conn, $sql, $this->params);
        } else {
            $this->stmt = sqlsrv_query($this->conn, $sql);
        }
        
        if ($this->stmt === false) {
            $errors = sqlsrv_errors();
            $this->error = print_r($errors, true);
            error_log('Error en consulta SQL: ' . $this->error);
            return false;
        }
        
        return true;
    }

    // Obtener registros
    public function resultSet() {
        $this->execute();
        $results = [];
        
        if ($this->stmt) {
            while ($row = sqlsrv_fetch_object($this->stmt)) {
                $results[] = $row;
            }
        }
        
        return $results;
    }

    // Obtener un solo registro
    public function single() {
        $this->execute();
        
        if ($this->stmt) {
            return sqlsrv_fetch_object($this->stmt);
        }
        
        return false;
    }

    // Contar filas
    public function rowCount() {
        if ($this->stmt) {
            return sqlsrv_num_rows($this->stmt);
        }
        return 0;
    }
    
    // Cerrar conexión
    public function __destruct() {
        if ($this->conn) {
            sqlsrv_close($this->conn);
        }
    }
}
*/
