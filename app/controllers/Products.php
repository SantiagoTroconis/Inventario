<?php

class Products extends Controller {

    private $productModel;
    private $userModel;
    private $requestModel;
    
    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->userModel = new UserModel();
        $this->requestModel = new RequestModel();
    }
        
    public function index() 
    {
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
                'icon' => $this->getCategoryIcon($producto->categoria),
                'imagen' => $producto->imagen ?? null
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
            'sucursales' => $sucursales,
            'esSucursal' => false,
            'sucursal_id' => null  // No specific sucursal for general inventory
        ];

        $this->view('productos/index', $data);
    }

    public function sucursal($sucursalId = null): void 
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth.php');
            exit();
        }

        // Determine which sucursal's inventory to show
        if ($sucursalId === null) {
            // No ID provided - use logged user's ID (for Sucursales viewing their own)
            $sucursalId = $_SESSION['usuario_id'];
        } else {
            // ID provided - verify permissions
            // Sucursales can ONLY view their own inventory
            if ($_SESSION['tipo_usuario'] === 'Sucursal' && $sucursalId != $_SESSION['usuario_id']) {
                $_SESSION['error_message'] = 'No tienes permisos para ver este inventario.';
                header('Location: ' . URL_BASE . '/products.php/sucursal');
                exit();
            }
            // Agentes can view any sucursal's inventory (no restriction)
        }

        $productosDb = $this->productModel->getBySucursal($sucursalId);
        
        
        if (!isset($productosDb) || empty($productosDb)) {
            $_SESSION['error_message'] = 'No se encontraron productos para esta sucursal.';
            $data = [
                'pageTitle' => 'Gestión de Productos',
                'productos' => [],
                'tipo_usuario' => $_SESSION['tipo_usuario'],
                'administrador' => null,
                'sucursales' => [],
                'esSucursal' => true,
                'sucursal_id' => $sucursalId  // Pass sucursal ID even when empty
            ];
            $this->view('productos/index', $data);
            return;
        }

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
                'icon' => $this->getCategoryIcon($producto->categoria),
                'imagen' => $producto->imagen ?? null
            ];
        }

        $administrador = $this->userModel->getAdministrador();
        $sucursales = $this->userModel->getSucursales();

        $data = [
            'pageTitle' => 'Gestión de Productos',
            'productos' => $productos,
            'tipo_usuario' => $_SESSION['tipo_usuario'],
            'administrador' => $administrador,
            'sucursales' => $sucursales,
            'esSucursal' => true,
            'sucursal_id' => $sucursalId  // Pass the sucursal ID being viewed
        ];

        $this->view('productos/index', $data);
    }

    public function add() 
    {
        if(!isset($_SESSION['usuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            
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
                echo json_encode(['success' => false, 'message' => 'El código del producto ya existe.']);
                exit();
            }

            // Manejo de imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadImage($_FILES['imagen']);
                if ($uploadResult['success']) {
                    $newProduct['imagen'] = $uploadResult['filename'];
                } else {
                    echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                    exit();
                }
            }

            if ($this->productModel->create($newProduct)) {
                echo json_encode(['success' => true, 'message' => 'Producto agregado correctamente']);
                exit();
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al agregar el producto.']);
                exit();
            }
        } else {
            $this->view('productos/add');
        }
    }

    public function edit($id) 
    {
        if(!isset($_SESSION['usuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit();
        }

        $producto = $this->productModel->getById($id);
        
        if (!$producto) {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                exit();
            }
            header('Location: ' . URL_BASE . '/products.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            
            $updateData = [
                'codigo' => trim($_POST['codigo']),
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'categoria' => trim($_POST['categoria'] ?? ''),
                // Stock is not editable here - only through Entradas/Salidas pages
                'stock_minimo' => (int)($_POST['stock_minimo'] ?? 0),
                'precio' => (float)$_POST['precio'],
                'activo' => isset($_POST['activo']) ? 1 : 0
            ];

            // Verificar si el código ya existe (excluyendo el actual)
            if ($this->productModel->existsCodigo($updateData['codigo'], $id)) {
                echo json_encode(['success' => false, 'message' => 'El código del producto ya existe.']);
                exit();
            }

            // Manejo de imagen
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = $this->uploadImage($_FILES['imagen']);
                if ($uploadResult['success']) {
                    // Obtener producto actual para eliminar imagen anterior
                    $currentProduct = $this->productModel->getById($id);
                    if ($currentProduct && !empty($currentProduct->imagen)) {
                        $oldImagePath = 'public/assets/img/products/' . $currentProduct->imagen;
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    $updateData['imagen'] = $uploadResult['filename'];
                } else {
                    echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
                    exit();
                }
            }

            if ($this->productModel->update($id, $updateData)) {
                // Fetch updated product to return to frontend
                $updatedProduct = $this->productModel->getById($id);
                
                // Format for frontend
                $stockClass = 'success';
                $statusClass = 'active';
                $estado = 'Activo';
                
                if ($updatedProduct->stock <= $updatedProduct->stock_minimo) {
                    $stockClass = 'warning';
                    $statusClass = 'warning';
                    $estado = 'Stock Bajo';
                }
                
                if ($updatedProduct->activo == 0) {
                    $statusClass = 'inactive';
                    $estado = 'Inactivo';
                }
                
                $formattedProduct = [
                    'id' => $updatedProduct->id,
                    'codigo' => $updatedProduct->codigo,
                    'nombre' => $updatedProduct->nombre,
                    'descripcion' => $updatedProduct->descripcion,
                    'categoria' => $updatedProduct->categoria,
                    'stock' => $updatedProduct->stock,
                    'stock_minimo' => $updatedProduct->stock_minimo,
                    'precio' => '$' . number_format($updatedProduct->precio, 2),
                    'estado' => $estado,
                    'status_class' => $statusClass,
                    'stock_class' => $stockClass,
                    'icon' => $this->getCategoryIcon($updatedProduct->categoria),
                    'imagen' => $updatedProduct->imagen // Return image filename
                ];

                echo json_encode([
                    'success' => true, 
                    'message' => 'Producto actualizado correctamente',
                    'product' => $formattedProduct
                ]);
                exit();
            } else {
                $_SESSION['error_message'] = 'Error al actualizar el producto.';
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto.']);
                exit();
            }
        } else {
            $this->view('productos/edit', ['producto' => $producto]);
        }
    }

    public function delete($id) 
    {
        if(!isset($_SESSION['usuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit();
        }

        header('Content-Type: application/json');
        
        if ($this->productModel->delete($id)) {
            echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar el producto']);
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
                header('Location: ' . URL_BASE . '/products.php');
                exit();
            }

            // Crear la solicitud (RequestModel ya está inicializado en el constructor)
            $solicitudData = [
                'solicitante_id' => $_SESSION['usuario_id'],  // FIXED: era solcitante_id
                'solicitado_id' => $solicitadoId,
                'tipo' => 'Producto',
                'descripcion' => $notas,
                'estado' => 'Pendiente',
                'prioridad' => 'Normal'
            ];

            $solicitudId = $this->requestModel->create($solicitudData);

            if ($solicitudId) {
                // Agregar el detalle del producto
                $detalleCreado = $this->requestModel->addDetalle($solicitudId, $productId, $cantidad, $notas);
                
                if ($detalleCreado) {
                    $_SESSION['success_message'] = 'Solicitud creada exitosamente. ID: ' . $solicitudId;
                    header('Location: ' . URL_BASE . '/requests');
                } else {
                    $_SESSION['error_message'] = 'Solicitud creada pero error al agregar el detalle del producto.';
                header('Location: ' . URL_BASE . '/products.php');
                }
            } else {
                $_SESSION['error_message'] = 'Error al crear la solicitud. Por favor intenta nuevamente.';
                header('Location: ' . URL_BASE . '/products.php');
            }
        } else {
                header('Location: ' . URL_BASE . '/products.php');
        }
        exit();
    }

    private function getCategoryIcon($categoria) 
    {
        $icons = [
            'Electrónica' => 'fa-laptop',
            'Oficina' => 'fa-print',
            'Mobiliario' => 'fa-chair',
            'Tecnología' => 'fa-microchip',
            'Papelería' => 'fa-file-alt'
        ];
        
        return $icons[$categoria] ?? 'fa-box';
    }

    private function uploadImage($file) 
    {
        $targetDir = "public/assets/img/products/";
        
        // Crear directorio si no existe
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = basename($file["name"]);
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Generar nombre ├║nico
        $newFileName = uniqid() . '.' . $fileType;
        $targetFilePath = $targetDir . $newFileName;

        // Validar tipo de archivo
        $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp');
        if (in_array($fileType, $allowTypes)) {
            // Validar tama├▒o (ej. max 5MB)
            if ($file["size"] > 5000000) {
                 return ['success' => false, 'message' => 'El archivo es demasiado grande (Max 5MB).'];
            }

            if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
                return ['success' => true, 'filename' => $newFileName];
            } else {
                return ['success' => false, 'message' => 'Error al subir la imagen.'];
            }
        } else {
            return ['success' => false, 'message' => 'Solo se permiten archivos JPG, JPEG, PNG, GIF y WEBP.'];
        }
    }


    public function getAll()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit();
        }

        $productos = $this->productModel->getAll();

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $productos]);
        exit();
    }
}