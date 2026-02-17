<?php

class Movements extends Controller
{
    private $movementModel;
    private $productModel;

    public function __construct()
    {
        $this->movementModel = new MovementModel();
        $this->productModel = new ProductModel();
    }

    /**
     * Página principal de movimientos
     */
    public function index()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        // Check if filters are applied
        if ($_SERVER['REQUEST_METHOD'] == 'POST' || !empty($_GET)) {
            $filtros = [
                'producto_id' => $_POST['producto_id'] ?? $_GET['producto_id'] ?? null,
                'usuario_id' => $_POST['usuario_id'] ?? $_GET['usuario_id'] ?? null,
                'tipo_movimiento' => $_POST['tipo_movimiento'] ?? $_GET['tipo_movimiento'] ?? null,
                'fecha_inicio' => $_POST['fecha_inicio'] ?? $_GET['fecha_inicio'] ?? null,
                'fecha_fin' => $_POST['fecha_fin'] ?? $_GET['fecha_fin'] ?? null,
                'producto_nombre' => $_POST['producto_nombre'] ?? $_GET['producto_nombre'] ?? null
            ];
            
            // Remove empty filters
            $filtros = array_filter($filtros, function($value) {
                return !empty($value);
            });
            
            if (!empty($filtros)) {
                $movimientos = $this->movementModel->search($filtros);
            } else {
                $movimientos = $this->movementModel->getAll();
            }
        } else {
            // Get all movements
            $movimientos = $this->movementModel->getAll();
        }

        // Get products for filter
        $productos = $this->productModel->getAll();
    
        $data = [
            'pageTitle' => 'Movimientos de Inventario',
            'movimientos' => $movimientos,
            'productos' => $productos,
            'filtros' => $filtros ?? []
        ];

        $this->view('movimientos/index', $data);
    }

    /**
     * Ver timeline de un producto específico
     */
    public function producto($producto_id = null)
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        if (!$producto_id) {
            header('Location: ' . URL_BASE . '/movements');
            exit();
        }

        // Get product info
        $producto = $this->productModel->getById($producto_id);
        
        if (!$producto) {
            $_SESSION['error_msg'] = 'Producto no encontrado';
            header('Location: ' . URL_BASE . '/movements');
            exit();
        }

        // Get movement timeline
        $timeline = $this->movementModel->getStockTimeline($producto_id);
        
        // Verify stock consistency
        $consistency = $this->movementModel->verifyStockConsistency($producto_id);

        $data = [
            'pageTitle' => 'Timeline de ' . $producto->nombre,
            'producto' => $producto,
            'timeline' => $timeline,
            'consistency' => $consistency
        ];

        $this->view('movimientos/producto', $data);
    }

    /**
     * Estadísticas de movimientos
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

        // Get statistics
        $stats = $this->movementModel->getStatsByPeriod($fecha_inicio, $fecha_fin);
        $topProducts = $this->movementModel->getTopMovedProducts($fecha_inicio, $fecha_fin, 10);

        $data = [
            'pageTitle' => 'Estadísticas de Movimientos',
            'stats' => $stats,
            'topProducts' => $topProducts,
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin
        ];

        $this->view('movimientos/estadisticas', $data);
    }

    /**
     * Ver movimientos por tipo
     */
    public function tipo($tipo_movimiento = null)
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        $tipos_validos = ['ENTRADA', 'SALIDA_SOLICITUD', 'SALIDA_MANUAL', 'AJUSTE_INVENTARIO', 'DEVOLUCION'];
        
        if (!$tipo_movimiento || !in_array($tipo_movimiento, $tipos_validos)) {
            header('Location: ' . URL_BASE . '/movements');
            exit();
        }

        $movimientos = $this->movementModel->getByType($tipo_movimiento);

        $data = [
            'pageTitle' => 'Movimientos: ' . str_replace('_', ' ', $tipo_movimiento),
            'movimientos' => $movimientos,
            'tipo' => $tipo_movimiento
        ];

        $this->view('movimientos/tipo', $data);
    }

    /**
     * Exportar movimientos (futuro)
     */
    public function exportar()
    {
        // TODO: Implement CSV/Excel export
        header('Location: ' . URL_BASE . '/movements');
        exit();
    }
}
