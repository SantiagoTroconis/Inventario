<?php
/**
 * Reportes Controller — Administrador only
 */
class Reportes extends Controller
{
    private $movementModel;
    private $requestModel;
    private $productModel;

    public function __construct()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth.php');
            exit();
        }
        if (($_SESSION['tipo_usuario'] ?? '') !== 'Administrador') {
            header('Location: ' . URL_BASE . '/home.php');
            exit();
        }

        $this->movementModel = new MovementModel();
        $this->requestModel  = new RequestModel();
        $this->productModel  = new ProductModel();
    }

    public function index()
    {
        // ── Period filter (days back, default 30) ──────────────────────────
        $days = max(7, min(365, (int)($_GET['days'] ?? 30)));

        // ── 1. Sucursal performance ────────────────────────────────────────
        try {
            $sucursalPerformance = $this->requestModel->getSucursalPerformance() ?? [];
        } catch (Exception $e) {
            $sucursalPerformance = [];
        }

        // ── 2. Top requested products ──────────────────────────────────────
        try {
            $topProducts = $this->requestModel->getTopRequestedProducts(10, $days) ?? [];
        } catch (Exception $e) {
            $topProducts = [];
        }
        $maxDemand = !empty($topProducts) ? (int)$topProducts[0]->cantidad_total : 1;

        // ── 3. Movement trend (last 6 months) ─────────────────────────────
        try {
            $trend = $this->movementModel->getTrendByMonth(6) ?? [];
        } catch (Exception $e) {
            $trend = [];
        }

        // ── 4. Stock health (all products) ────────────────────────────────
        try {
            $stockHealth = $this->productModel->getStockHealthAll() ?? [];
        } catch (Exception $e) {
            $stockHealth = [];
        }

        $healthCounts = ['critical' => 0, 'warning' => 0, 'healthy' => 0];
        foreach ($stockHealth as $p) {
            $healthCounts[$p->health_status] = ($healthCounts[$p->health_status] ?? 0) + 1;
        }

        $this->view('reportes/index', [
            'pageTitle'          => 'Reportes',
            'days'               => $days,
            'sucursalPerformance'=> $sucursalPerformance,
            'topProducts'        => $topProducts,
            'maxDemand'          => $maxDemand,
            'trend'              => $trend,
            'stockHealth'        => $stockHealth,
            'healthCounts'       => $healthCounts,
        ]);
    }
}
