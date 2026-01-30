<?php
// Clase principal de la aplicación
class App
{
    protected $controller;
    protected $method = 'index';
    protected $params = [];

    public function __construct()
    {
        $url = $this->parseUrl();

        // Controlador por defecto
        $this->controller = 'Auth';

        // Verificar si existe el controlador
        if (isset($url[0]) && !empty($url[0]) && file_exists('../app/controllers/' . ucfirst($url[0]) . '.php')) {
            $this->controller = ucfirst($url[0]);
            unset($url[0]);
        }

        require_once '../app/controllers/' . $this->controller . '.php';
        $this->controller = new $this->controller;

        // Verificar si existe el método
        if (isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        // Obtener parámetros
        $this->params = $url ? array_values($url) : [];

        // Llamar al método con los parámetros
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    public function parseUrl()
    {

        $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

        if (isset($uri)) {
            // Remover la ruta base del subdominio
            $basePath = '/national/Inventario';

            // Si la URI comienza con la ruta base, removerla
            if (strpos($uri, $basePath) === 0) {
                $uri = substr($uri, strlen($basePath));
            }

            $url = rtrim($uri, '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);

            // Debug: Descomentar para ver qué está recibiendo
            // echo "URL recibida: " . $_SERVER['REQUEST_URI'] . "<br>";
            // echo "URI procesada: " . $uri . "<br>";
            // echo "URL filtrada: " . $url . "<br>";

            // Si la URL está vacía después de remover la base, retornar array vacío
            if (empty($url) || $url === '/') {
                return [];
            }

            return explode('/', trim($url, '/'));
        }

        return [];
    }
}
