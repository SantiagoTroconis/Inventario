<?php
// Controlador de inicio
class Home extends Controller
{
    private $requestModel;
    private $userModel;
    private $productModel;
    
    public function __construct()
    {
        $this->requestModel = new RequestModel();
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
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
        
        $data = [
            'pageTitle' => 'Dashboard',
            'usuario'   => $currentUserName,
            'tipo_usuario' => $currentUserRole,
            'kpis'      => [],
            'pendingRequests' => [],
            'recentActivity' => [],
            'lowStockItems' => [],
            'myRequests' => []
        ];

        // 1. ADMIN DASHBOARD DATA
        if ($currentUserRole === 'Administrador') {
            $stats = $this->requestModel->getStats();
            $prodStats = $this->productModel->getStats();
            
            // KPIs
            $data['kpis'] = [
                ['label' => 'Total Solicitudes', 'value' => $stats ? $stats->total : 0, 'icon' => 'fa-clipboard-list', 'color' => 'blue-600', 'bg' => 'blue-50'],
                ['label' => 'Pendientes', 'value' => $stats ? $stats->pendientes : 0, 'icon' => 'fa-clock', 'color' => 'amber-500', 'bg' => 'amber-50'],
                ['label' => 'Total Productos', 'value' => $prodStats ? $prodStats->total_productos : 0, 'icon' => 'fa-boxes-stacked', 'color' => 'emerald-600', 'bg' => 'emerald-50'],
            ];

            // Recent System Activity (All latest requests)
            $data['recentActivity'] = $this->requestModel->getRecientes(10);
            
            // Global Low Stock
            $data['lowStockItems'] = $this->productModel->getLowStock(5);
            
            // Pending requests needing Admin attention (if any specific logic exists, typically admins oversee all)
            $data['pendingRequests'] = $this->requestModel->getPendingRequestsForUser($_SESSION['usuario_id'], 5);

        } 
        // 2. SUCURSAL DASHBOARD DATA
        elseif ($currentUserRole === 'Sucursal') {
            $sucursalId = $_SESSION['usuario_id']; // Assuming sucursal user has products assigned to them
            
            $sucursalStats = $this->productModel->getStatsBySucursal($sucursalId);
            
            // KPIs
            $data['kpis'] = [
                ['label' => 'Mis Productos', 'value' => $sucursalStats ? $sucursalStats->total_productos : 0, 'icon' => 'fa-box', 'color' => 'blue-600', 'bg' => 'blue-50'],
                ['label' => 'Bajo Stock', 'value' => $sucursalStats ? $sucursalStats->productos_bajo_stock : 0, 'icon' => 'fa-triangle-exclamation', 'color' => 'red-500', 'bg' => 'red-50'],
            ];

            // Incoming Requests (Requests directed TO this sucursal)
            $data['pendingRequests'] = $this->requestModel->getPendingRequestsForUser($sucursalId, 10);
            
            // My Low Stock Items
            $data['lowStockItems'] = $this->productModel->getLowStockBySucursal($sucursalId, 5);

        } 
        // 3. AGENTE DASHBOARD DATA
        else { 
            // KPIs for Agent
            // Get my requests stats
            $myRequests = $this->requestModel->getByUsuario($_SESSION['usuario_id']);
            $totalMyRequests = count($myRequests);
            $pendingMyRequests = 0;
            $approvedMyRequests = 0;
            
            foreach($myRequests as $req) {
                if($req->estado == 'Pendiente') $pendingMyRequests++;
                if($req->estado == 'Aprobada' || $req->estado == 'Completada') $approvedMyRequests++;
            }

            $data['kpis'] = [
                ['label' => 'Mis Solicitudes', 'value' => $totalMyRequests, 'icon' => 'fa-file-pen', 'color' => 'blue-600', 'bg' => 'blue-50'],
                ['label' => 'En Espera', 'value' => $pendingMyRequests, 'icon' => 'fa-hourglass-half', 'color' => 'amber-500', 'bg' => 'amber-50'],
                ['label' => 'Aprobadas', 'value' => $approvedMyRequests, 'icon' => 'fa-check-double', 'color' => 'emerald-600', 'bg' => 'emerald-50'],
            ];

            // My Recent Requests
            $data['myRequests'] = array_slice($myRequests, 0, 10); // getRecentByRequester would be better but this works with existing methods
        }

        $this->view('home/index', $data);
    }
}
