<?php

class Exits extends Controller
{
    private $movementModel;
    private $productModel;

    public function __construct()
    {
        $this->movementModel = new MovementModel();
        $this->productModel = new ProductModel();
    }

    /**
     * Página principal de salidas (filtro de movimientos)
     */
    public function index()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        // Get filters
        $filtros = [];
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_GET)) {
            $filtros = [
                'producto_id' => $_POST['producto_id'] ?? $_GET['producto_id'] ?? null,
                'fecha_inicio' => $_POST['fecha_inicio'] ?? $_GET['fecha_inicio'] ?? null,
                'fecha_fin' => $_POST['fecha_fin'] ?? $_GET['fecha_fin'] ?? null,
                'producto_nombre' => $_POST['producto_nombre'] ?? $_GET['producto_nombre'] ?? null,
                'tipo_salida' => $_POST['tipo_salida'] ?? $_GET['tipo_salida'] ?? null
            ];
            
            // Remove empty filters
            $filtros = array_filter($filtros, function($value) {
                return !empty($value);
            });
        }
        
        // Always filter by exit types
        if (!empty($filtros)) {
            // If user selected specific exit type
            if (!empty($filtros['tipo_salida'])) {
                $filtros['tipo_movimiento'] = $filtros['tipo_salida'];
                unset($filtros['tipo_salida']);
            } else {
                // Show all exit types - we'll filter manually
                $allMovements = $this->movementModel->search($filtros);
                $salidas = array_filter($allMovements, function($m) {
                    return in_array($m->tipo_movimiento, ['SALIDA_SOLICITUD', 'SALIDA_MANUAL']);
                });
            }
        }
        
        // If no custom filters or tipo_movimiento set, get all exits
        if (!isset($salidas)) {
            if (isset($filtros['tipo_movimiento'])) {
                $salidas = $this->movementModel->search($filtros);
            } else {
                // Get both types of exits
                $salidas_solicitud = $this->movementModel->getByType('SALIDA_SOLICITUD');
                $salidas_manual = $this->movementModel->getByType('SALIDA_MANUAL');
                $salidas = array_merge($salidas_solicitud, $salidas_manual);
                
                // Sort by date descending
                usort($salidas, function($a, $b) {
                    return strtotime($b->fecha_movimiento) - strtotime($a->fecha_movimiento);
                });
            }
        }

        // Get products for filter
        $productos = $this->productModel->getAll();
    
        $data = [
            'pageTitle' => 'Salidas de Inventario',
            'salidas' => $salidas,
            'productos' => $productos,
            'filtros' => $filtros
        ];

        $this->view('salidas/index', $data);
    }

    /**
     * Formulario para registrar salida manual
     */
    public function crear()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        // Only admins can create manual exits
        if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'Administrador') {
            $_SESSION['error_msg'] = 'No tiene permisos para registrar salidas manuales';
            header('Location: ' . URL_BASE . '/exits.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form submission
            $usuario_id = $_SESSION['usuario_id'] ?? null;
            
            $data = [
                'producto_id' => $_POST['producto_id'] ?? null,
                'cantidad' => $_POST['cantidad'] ?? 0,
                'motivo' => $_POST['motivo'] ?? 'SALIDA_MANUAL',
                'comentario' => $_POST['comentario'] ?? null
            ];

            // Validate required fields
            if (empty($data['producto_id']) || empty($data['cantidad']) || empty($usuario_id)) {
                $_SESSION['error_msg'] = 'Por favor complete todos los campos requeridos';
                header('Location: ' . URL_BASE . '/exits.php/crear');
                exit();
            }

            // Validate quantity
            if ($data['cantidad'] <= 0) {
                $_SESSION['error_msg'] = 'La cantidad debe ser mayor a 0';
                header('Location: ' . URL_BASE . '/exits.php/crear');
                exit();
            }

            // Validate stock availability
            $producto = $this->productModel->getById($data['producto_id']);
            if (!$producto) {
                $_SESSION['error_msg'] = 'Producto no encontrado';
                header('Location: ' . URL_BASE . '/exits.php/crear');
                exit();
            }

            if ($producto->stock < $data['cantidad']) {
                $_SESSION['error_msg'] = "Stock insuficiente. Disponible: {$producto->stock} unidades";
                header('Location: ' . URL_BASE . '/exits.php/crear');
                exit();
            }

            // Register manual exit movement
            $result = $this->movementModel->registerMovement([
                'producto_id' => $data['producto_id'],
                'usuario_id' => $usuario_id,
                'tipo_movimiento' => 'SALIDA_MANUAL',
                'cantidad' => $data['cantidad'],
                'referencia_id' => null,
                'comentario' => $data['comentario'] ?? 'Salida manual'
            ]);

            if ($result['success']) {
                $_SESSION['success_msg'] = 'Salida registrada exitosamente';
                header('Location: ' . URL_BASE . '/exits.php');
                exit();
            } else {
                $_SESSION['error_msg'] = $result['message'];
                header('Location: ' . URL_BASE . '/exits.php/crear');
                exit();
            }
        }

        // GET request - show form
        $productos = $this->productModel->getBySucursal($_SESSION['usuario_id'] ?? null);

        $data = [
            'pageTitle' => 'Registrar Salida',
            'productos' => $productos
        ];

        $this->view('salidas/crear', $data);
    }

    /**
     * Ver detalle de una salida específica
     */
    public function ver($id = null)
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        if (!$id) {
            header('Location: ' . URL_BASE . '/exits.php');
            exit();
        }

        // Get movement by reference (if it's an exit)
        $movimientos = $this->movementModel->getByReference($id);
        
        if (empty($movimientos)) {
            $_SESSION['error_msg'] = 'Salida no encontrada';
            header('Location: ' . URL_BASE . '/exits.php');
            exit();
        }

        $salida = $movimientos[0]; // Get first movement

        // Verify it's an exit type
        if (!in_array($salida->tipo_movimiento, ['SALIDA_SOLICITUD', 'SALIDA_MANUAL'])) {
            $_SESSION['error_msg'] = 'El movimiento solicitado no es una salida';
            header('Location: ' . URL_BASE . '/exits.php');
            exit();
        }

        $data = [
            'pageTitle' => 'Detalle de Salida',
            'salida' => $salida
        ];

        $this->view('salidas/ver', $data);
    }

    /**
     * Estadísticas de salidas
     */
    public function estadisticas()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        // Default to last 30 days
        $fecha_inicio = $_GET['fecha_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
        $fecha_fin = $_GET['fecha_fin'] ?? date('Y-m-d');

        // Get all exits in period
        $salidas_solicitud = $this->movementModel->search([
            'tipo_movimiento' => 'SALIDA_SOLICITUD',
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ]);

        $salidas_manual = $this->movementModel->search([
            'tipo_movimiento' => 'SALIDA_MANUAL',
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ]);

        // Calculate statistics
        $total_salidas_solicitud = count($salidas_solicitud);
        $total_salidas_manual = count($salidas_manual);
        $cantidad_solicitud = array_sum(array_column($salidas_solicitud, 'cantidad'));
        $cantidad_manual = array_sum(array_column($salidas_manual, 'cantidad'));

        $data = [
            'pageTitle' => 'Estadísticas de Salidas',
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'total_salidas_solicitud' => $total_salidas_solicitud,
            'total_salidas_manual' => $total_salidas_manual,
            'cantidad_solicitud' => $cantidad_solicitud,
            'cantidad_manual' => $cantidad_manual,
            'salidas_solicitud' => $salidas_solicitud,
            'salidas_manual' => $salidas_manual
        ];

        $this->view('salidas/estadisticas', $data);
    }
}
