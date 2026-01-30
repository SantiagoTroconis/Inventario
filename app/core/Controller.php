<?php
// Clase base para los controladores
class Controller
{

    // Cargar modelo
    public function model($model)
    {
        require_once BASE_PATH . '/app/models/' . $model . '.php';
        return new $model();
    }

    // Cargar vista
    public function view($view, $data = [])
    {
        // Extraer el array $data para crear variables individuales
        extract($data);

        require_once BASE_PATH . '/app/views/' . $view . '.php';
    }
}
