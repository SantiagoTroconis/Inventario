<?php
// Controlador de inicio
class Home extends Controller
{
    private $requestModel;
    
    public function __construct()
    {
        $this->requestModel = new RequestModel();
    }

    public function index()
    {

        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth.php');
            exit();
        }

        $currentUserRole = $_SESSION['tipo_usuario'] ?? 'Agente';
        $currentUserName = $_SESSION['usuario'] ?? 'Invitado';
        $currentSucursal = $_SESSION['sucursal'] ?? 'N/A';

        // KPI Logic
        $kpis = [];
        if ($currentUserRole === 'Administrador') {
            $kpis = [
                ['label' => 'Total Productos', 'value' => '1,247', 'icon' => 'fa-box-open', 'color' => 'blue-600'],
                ['label' => 'Solicitudes', 'value' => '145', 'icon' => 'fa-clipboard-list', 'color' => 'amber-500'],
                ['label' => 'Sucursales', 'value' => '24', 'icon' => 'fa-store', 'color' => 'purple-600'],
                ['label' => 'Agentes', 'value' => '86', 'icon' => 'fa-users', 'color' => 'emerald-600'],
            ];
        } elseif ($currentUserRole === 'Sucursal') {
            $kpis = [
                ['label' => 'Productos (Sucursal)', 'value' => '230', 'icon' => 'fa-boxes-stacked', 'color' => 'blue-600'],
                ['label' => 'Solicitudes Pendientes', 'value' => '8', 'icon' => 'fa-file-signature', 'color' => 'amber-500'],
                ['label' => 'Entradas (Mes)', 'value' => '12', 'icon' => 'fa-dolly', 'color' => 'emerald-600'],
                ['label' => 'Salidas (Mes)', 'value' => '9', 'icon' => 'fa-truck-ramp-box', 'color' => 'red-500'],
            ];
        } else { // Agente
            $kpis = [
                ['label' => 'Mis Solicitudes', 'value' => '3', 'icon' => 'fa-file-signature', 'color' => 'amber-500'],
                ['label' => 'Items Asignados', 'value' => '12', 'icon' => 'fa-box-open', 'color' => 'blue-600'],
                ['label' => 'Entregas Pendientes', 'value' => '1', 'icon' => 'fa-truck', 'color' => 'red-500'],
                ['label' => 'Sucursal', 'value' => $currentSucursal, 'icon' => 'fa-store', 'color' => 'purple-600'],
            ];
        }

        // Obtener solicitudes pendientes para Admin y Sucursal
        $pendingRequests = [];
        if ($currentUserRole === 'Administrador' || $currentUserRole === 'Sucursal') {
            $pendingRequests = $this->requestModel->getPendingRequestsForUser($_SESSION['usuario_id'], 5);
        }

        $data = [
            'pageTitle' => 'Dashboard',
            'usuario'   => $currentUserName,
            'tipo_usuario' => $currentUserRole,
            'kpis'      => $kpis,
            'pendingRequests' => $pendingRequests
        ];

        $this->view('home/index', $data);
    }
}
