<?php

class Entries extends Controller
{
    private $entriesModel;
    private $requestModel;

    public function __construct()
    {
        $this->entriesModel = new EntriesModel();
        $this->requestModel = new RequestModel();
    }

    public function index()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        // Get all entries from database
        $entradas = $this->entriesModel->getByAgente($_SESSION['usuario_id']);
    
        $data = [
            'pageTitle' => 'Entradas',
            'entradas' => $entradas
        ];

        $this->view('entradas/index', $data);
    }

    public function registrar()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form submission
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            $tipo_usuario = $_SESSION['tipo_usuario'] ?? 'Agente';
            
            $data = [
                'producto_id' => $_POST['producto_id'] ?? null,
                'cantidad' => $_POST['cantidad'] ?? 0,
                'usuario_id' => $usuario_id,
                'proveedor' => $_POST['proveedor'] ?? null,
                'referencia' => $_POST['referencia'] ?? null
            ];

            // Validate required fields
            if (empty($data['producto_id']) || empty($data['cantidad']) || empty($data['usuario_id'])) {
                $_SESSION['error_msg'] = 'Por favor complete todos los campos requeridos';
                header('Location: ' . URL_BASE . '/entries.php/registrar');
                exit();
            }

            // Validate quantity
            if ($data['cantidad'] <= 0) {
                $_SESSION['error_msg'] = 'La cantidad debe ser mayor a 0';
                header('Location: ' . URL_BASE . '/entries.php/registrar');
                exit();
            }

            // Validate user permission for this product (except Admins)
            $validation = $this->entriesModel->canUserRegisterEntry(
                $usuario_id, 
                $data['producto_id'], 
                $tipo_usuario
            );

            if (!$validation['valid']) {
                $_SESSION['error_msg'] = $validation['message'];
                header('Location: ' . URL_BASE . '/entries.php/registrar');
                exit();
            }

            // For non-admin users, validate quantity matches requested amount
            if ($tipo_usuario !== 'Administrador' && $validation['data']) {
                $cantidad_solicitada = $validation['data']->cantidad_solicitada;
                
                if ($data['cantidad'] != $cantidad_solicitada) {
                    $_SESSION['error_msg'] = "La cantidad debe ser exactamente $cantidad_solicitada unidades según la solicitud aprobada";
                    header('Location: ' . URL_BASE . '/entries.php/registrar');
                    exit();
                }
            }

            // Create entry
            $result = $this->entriesModel->create($data);
            
            if ($result['success']) {
                $_SESSION['success_msg'] = 'Entrada registrada exitosamente';
                header('Location: ' . URL_BASE . '/entries');
                exit();
            } else {
                $_SESSION['error_msg'] = $result['message'];
                header('Location: ' . URL_BASE . '/entries/registrar');
                exit();
            }
        }

        $this->view('entradas/registrar', ['pageTitle' => 'Registrar Entrada']);
    }

    public function ver($id)
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        // Get entry by ID
        $entrada = $this->entriesModel->getById($id);

        if (!$entrada) {
            $_SESSION['error_msg'] = 'Entrada no encontrada';
            header('Location: ' . URL_BASE . '/entries.php');
            exit();
        }

        $data = [
            'pageTitle' => 'Detalle de Entrada',
            'entrada' => $entrada
        ];

        $this->view('entradas/ver', $data);
    }

    public function eliminar($id)
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        // Check if user has permission to delete
        if ($_SESSION['tipo_usuario'] !== 'Admin') {
            $_SESSION['error_msg'] = 'No tiene permisos para eliminar entradas';
            header('Location: ' . URL_BASE . '/entries');
            exit();
        }

        if ($this->entriesModel->delete($id)) {
            $_SESSION['success_msg'] = 'Entrada eliminada exitosamente';
        } else {
            $_SESSION['error_msg'] = 'Error al eliminar la entrada';
        }

        header('Location: ' . URL_BASE . '/entries');
        exit();
    }

    public function buscar()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        $criterios = [
            'producto' => $_GET['producto'] ?? '',
            'proveedor' => $_GET['proveedor'] ?? '',
            'referencia' => $_GET['referencia'] ?? '',
            'fecha_inicio' => $_GET['fecha_inicio'] ?? '',
            'fecha_fin' => $_GET['fecha_fin'] ?? ''
        ];

        $entradas = $this->entriesModel->search($criterios);

        $data = [
            'pageTitle' => 'Resultados de Búsqueda',
            'entradas' => $entradas,
            'criterios' => $criterios
        ];

        $this->view('entradas/index', $data);
    }

    public function recientes()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        $limite = $_GET['limite'] ?? 10;
        $entradas = $this->entriesModel->getRecent($limite);

        $data = [
            'pageTitle' => 'Entradas Recientes',
            'entradas' => $entradas
        ];

        $this->view('entradas/index', $data);
    }
    
    /**
     * API endpoint to get available products for entry registration
     * Returns JSON response with products based on user role
     */
    public function getProductosDisponibles()
    {
        header('Content-Type: application/json');
        
        if(!isset($_SESSION['usuario'])) {
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit();
        }

        $usuario_id = $_SESSION['usuario_id'] ?? null;
        $tipo_usuario = $_SESSION['tipo_usuario'] ?? 'Agente';

        try {
            if ($tipo_usuario === 'Administrador') {
                // Admins can see all products
                $productModel = new ProductModel();
                $productos = $productModel->getAll();
                
                // Format for response
                $formatted = array_map(function($p) {
                    return [
                        'id' => $p->id,
                        'codigo' => $p->codigo,
                        'nombre' => $p->nombre,
                        'categoria' => $p->categoria ?? '',
                        'descripcion' => $p->descripcion ?? '',
                        'cantidad_solicitada' => null, // No aplica para admin
                        'tipo' => 'admin'
                    ];
                }, $productos);

                echo json_encode(['success' => true, 'data' => $formatted]);
            } else {
                // Non-admin users only see approved requested products
                $productos = $this->requestModel->getApprovedProductsByUser($usuario_id);
                
                if (empty($productos)) {
                    echo json_encode([
                        'success' => true, 
                        'data' => [],
                        'message' => 'No tiene solicitudes aprobadas pendientes de recibir'
                    ]);
                    exit();
                }

                // Format for response
                $formatted = array_map(function($p) {
                    return [
                        'id' => $p->producto_id,
                        'codigo' => $p->producto_codigo,
                        'nombre' => $p->producto_nombre,
                        'categoria' => $p->producto_categoria ?? '',
                        'descripcion' => $p->producto_descripcion ?? '',
                        'cantidad_solicitada' => $p->cantidad_solicitada,
                        'solicitud_id' => $p->solicitud_id,
                        'detalle_id' => $p->detalle_id,
                        'observaciones' => $p->observaciones ?? '',
                        'tipo' => 'solicitud'
                    ];
                }, $productos);

                echo json_encode(['success' => true, 'data' => $formatted]);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al cargar productos: ' . $e->getMessage()]);
        }
        exit();
    }
}
