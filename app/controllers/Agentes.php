<?php

class Agentes extends Controller
{
    private $userModel;
    private $isSucursal = false;

    public function __construct()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth.php');
            exit();
        }
        $role = $_SESSION['tipo_usuario'] ?? '';
        if ($role !== 'Administrador' && $role !== 'Sucursal') {
            header('Location: ' . URL_BASE . '/home.php');
            exit();
        }
        $this->isSucursal = ($role === 'Sucursal');
        $this->userModel = new UserModel();
    }

    // GET /agentes.php — list agentes (filtered for Sucursal users)
    public function index()
    {
        if ($this->isSucursal) {
            $sucursalId = $_SESSION['sucursal_id'] ?? null;
            $agentes    = $sucursalId ? $this->userModel->getAgentesBySucursal($sucursalId) : [];
            $sucursales = [];
        } else {
            $agentes    = $this->userModel->getAllAgentes();
            $sucursales = $this->userModel->getAllSucursales();
        }

        $this->view('agentes/index', [
            'agentes'    => $agentes,
            'sucursales' => $sucursales,
            'isSucursal' => $this->isSucursal,
        ]);
    }

    // POST /agentes.php/crear — create agente (JSON)
    public function crear()
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        $required = ['nombre', 'password', 'sucursal_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "El campo '$field' es requerido."]);
                exit();
            }
        }

        $result = $this->userModel->createAgente($data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Agente creado exitosamente.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al crear el agente.']);
        }
        exit();
    }

    // POST /agentes.php/editar/:id — update agente (JSON)
    public function editar($id)
    {
        header('Content-Type: application/json');
        $data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

        if (empty($data['nombre']) || empty($data['sucursal_id'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Nombre y sucursal son requeridos.']);
            exit();
        }

        $result = $this->userModel->updateAgente($id, $data);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Agente actualizado.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el agente.']);
        }
        exit();
    }

    // POST /agentes.php/toggle/:id — toggle active status (JSON)
    public function toggle($id)
    {
        header('Content-Type: application/json');
        $newStatus = $this->userModel->toggleAgenteStatus($id);
        if ($newStatus !== false) {
            $label = $newStatus == 1 ? 'Activo' : 'Inactivo';
            echo json_encode(['success' => true, 'status' => (int)$newStatus, 'label' => $label]);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al cambiar estado.']);
        }
        exit();
    }

    // POST /agentes.php/eliminar/:id — hard delete (JSON)
    public function eliminar($id)
    {
        header('Content-Type: application/json');
        $result = $this->userModel->deleteAgente($id);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Agente eliminado.']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el agente.']);
        }
        exit();
    }
}
