<?php

class Entries extends Controller
{

    public function index()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        // Mock data for Entradas (Stock In)
        $entradas = [
            ['id' => 'ENT-001', 'producto' => 'Laptop HP EliteBook', 'cantidad' => 10, 'sucursal' => 'Almacén Central', 'fecha' => '2024-12-01', 'recibido_por' => 'Carlos M.'],
            ['id' => 'ENT-002', 'producto' => 'Silla Ergonómica', 'cantidad' => 20, 'sucursal' => 'Sucursal Sur', 'fecha' => '2024-12-02', 'recibido_por' => 'Ana R.'],
            ['id' => 'ENT-003', 'producto' => 'Impresora Laser MFP', 'cantidad' => 5, 'sucursal' => 'Sucursal Norte', 'fecha' => '2024-12-03', 'recibido_por' => 'Lucía G.']
        ];
    
        $data = [
            'pageTitle' => 'Entradas',
            'entradas' => $entradas
        ];

        $this->view('entradas/index', $data);
    }

    public function registrar()
    {
        $this->view('entradas/registrar', ['pageTitle' => 'Registrar Entrada']);
    }
}
