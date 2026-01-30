<?php

class Products extends Controller {

    private $productModel;
    private $userModel;
    
    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->userModel = new UserModel();
    }
        
    public function index() {
            
            if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        // Obtener productos de la base de datos
        $productosDb = $this->productModel->getAll();
        
        // Formatear productos para la vista
        $productos = [];
        foreach ($productosDb as $producto) {
            $stockClass = 'success';
            $statusClass = 'active';
            $estado = 'Activo';
            
            if ($producto->stock <= $producto->stock_minimo) {
                $stockClass = 'warning';
                $statusClass = 'warning';
                $estado = 'Stock Bajo';
            }
            
            if ($producto->activo == 0) {
                $statusClass = 'inactive';
                $estado = 'Inactivo';
            }
            
            $productos[] = [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'categoria' => $producto->categoria,
                'stock' => $producto->stock,
                'stock_minimo' => $producto->stock_minimo,
                'precio' => '$' . number_format($producto->precio, 2),
                'estado' => $estado,
                'status_class' => $statusClass,
                'stock_class' => $stockClass,
                'icon' => $this->getCategoryIcon($producto->categoria)
            ];
        }

        // Obtener datos necesarios para las solicitudes
        $administrador = $this->userModel->getAdministrador();
        $sucursales = $this->userModel->getSucursales();

        $data = [
            'pageTitle' => 'Gestión de Productos',
            'productos' => $productos,
            'tipo_usuario' => $_SESSION['tipo_usuario'],
            'administrador' => $administrador,
            'sucursales' => $sucursales
        ];

        $this->view('productos/index', $data);
    }

    public function add() {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Procesar el formulario de adición de producto
            $newProduct = [
                'codigo' => trim($_POST['codigo']),
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'stock' => (int)($_POST['stock'] ?? 0),
                'stock_minimo' => (int)($_POST['stock_minimo'] ?? 0),
                'precio' => (float)$_POST['precio'],
                'activo' => 1
            ];

            // Verificar si el código ya existe
            if ($this->productModel->existsCodigo($newProduct['codigo'])) {
                $this->view('productos/add', ['error' => 'El código del producto ya existe.']);
                return;
            }

            if ($this->productModel->create($newProduct)) {
                header('Location: ' . URL_BASE . '/productos/index');
                exit();
            } else {
                $this->view('productos/add', ['error' => 'Error al agregar el producto.']);
            }
        } else {
            $this->view('productos/add');
        }
    }

    public function edit($id) {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        $producto = $this->productModel->getById($id);
        
        if (!$producto) {
            header('Location: ' . URL_BASE . '/productos/index');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $updateData = [
                'codigo' => trim($_POST['codigo']),
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                'stock' => (int)($_POST['stock'] ?? 0),
                'stock_minimo' => (int)($_POST['stock_minimo'] ?? 0),
                'precio' => (float)$_POST['precio'],
                'activo' => (int)($_POST['activo'] ?? 1)
            ];

            // Verificar si el código ya existe (excluyendo el actual)
            if ($this->productModel->existsCodigo($updateData['codigo'], $id)) {
                $this->view('productos/edit', ['error' => 'El código del producto ya existe.', 'producto' => $producto]);
                return;
            }

            if ($this->productModel->update($id, $updateData)) {
                header('Location: ' . URL_BASE . '/productos/index');
                exit();
            } else {
                $this->view('productos/edit', ['error' => 'Error al actualizar el producto.', 'producto' => $producto]);
            }
        } else {
            $this->view('productos/edit', ['producto' => $producto]);
        }
    }

    public function delete($id) {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        if ($this->productModel->delete($id)) {
            header('Location: ' . URL_BASE . '/productos/index');
        } else {
            die('Error al eliminar el producto.');
        }
        exit();
    }



    // Metodo perteneciente a la clase de Requests.php
    public function solicitar()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Obtener datos del POST
            $productId = $_POST['producto_id'] ?? null;
            $cantidad = $_POST['cantidad'] ?? null;
            $notas = $_POST['notas'] ?? '';
            $solicitadoId = $_POST['solicitado_id'] ?? null;

            // Validar datos requeridos
            if (!$productId || !$cantidad || !$solicitadoId) {
                $_SESSION['error_message'] = 'Datos incompletos. Por favor completa todos los campos requeridos.';
                header('Location: ' . URL_BASE . '/productos');
                exit();
            }

            // Incluir RequestModel
            if (!isset($this->requestModel)) {
                $this->requestModel = new RequestModel();
            }

            // Crear la solicitud
            $solicitudData = [
                'solcitante_id' => $_SESSION['usuario_id'],
                'solicitado_id' => $solicitadoId,
                'tipo' => 'Producto',
                'descripcion' => $notas,
                'estado' => 'Pendiente',
                'prioridad' => 'Normal'
            ];

            $solicitudId = $this->requestModel->create($solicitudData);

            if ($solicitudId) {
                // Agregar el detalle del producto
                $this->requestModel->addDetalle($solicitudId, $productId, $cantidad, $notas);
                
                $_SESSION['success_message'] = 'Solicitud creada exitosamente. ID: ' . $solicitudId;
                header('Location: ' . URL_BASE . '/productos');
            } else {
                $_SESSION['error_message'] = 'Error al crear la solicitud. Por favor intenta nuevamente.';
                header('Location: ' . URL_BASE . '/productos');
            }
        } else {
            header('Location: ' . URL_BASE . '/productos');
        }
        exit();
    }

    private function getCategoryIcon($categoria) {
        $icons = [
            'Electrónica' => 'fa-laptop',
            'Oficina' => 'fa-print',
            'Mobiliario' => 'fa-chair',
            'Tecnología' => 'fa-microchip',
            'Papelería' => 'fa-file-alt'
        ];
        
        return $icons[$categoria] ?? 'fa-box';
    }
}