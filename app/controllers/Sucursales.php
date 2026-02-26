<?php

class Sucursales extends Controller
{
    private $userModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth.php');
            exit();
        }
        if ($_SESSION['tipo_usuario'] !== 'Administrador') {
            header('Location: ' . URL_BASE . '/home.php');
            exit();
        }
        $this->userModel = new UserModel();
    }

    // GET /sucursales.php — list all sucursales
    public function index()
    {
        $sucursales = $this->userModel->getAllSucursales();
        $this->view('sucursales/index', ['sucursales' => $sucursales]);
    }

    // POST /sucursales.php/crear — create sucursal (JSON)
    public function crear()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        if (empty($data['nombre']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nombre y contraseña son requeridos.']);
            exit();
        }

        $id = $this->userModel->createSucursal($data);
        if ($id) {
            echo json_encode(['success' => true, 'message' => 'Sucursal creada exitosamente.', 'id' => $id]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al crear la sucursal.']);
        }
        exit();
    }

    // POST /sucursales.php/editar/:id — update sucursal (JSON)
    public function editar($id)
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        if (empty($data['nombre'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El nombre es requerido.']);
            exit();
        }

        $result = $this->userModel->updateSucursal($id, $data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Sucursal actualizada.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar la sucursal.']);
        }
        exit();
    }

    // POST /sucursales.php/toggle/:id — toggle active/inactive (JSON)
    public function toggle($id)
    {
        header('Content-Type: application/json');
        $newEstado = $this->userModel->toggleSucursalStatus($id);
        if ($newEstado !== false) {
            echo json_encode(['success' => true, 'estado' => $newEstado]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al cambiar estado.']);
        }
        exit();
    }

    // POST /sucursales.php/eliminar/:id — delete sucursal (JSON)
    public function eliminar($id)
    {
        header('Content-Type: application/json');
        $result = $this->userModel->deleteSucursal($id);
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Sucursal eliminada.']);
        } else {
            http_response_code(409);
            echo json_encode($result);
        }
        exit();
    }
}
