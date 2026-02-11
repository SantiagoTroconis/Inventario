<?php

class Requests extends Controller
{
    private $requestModel;
    private $productModel;

    public function __construct()
    {
        $this->requestModel = new RequestModel();
        $this->productModel = new ProductModel();
    }

    public function index()
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        $currentUserRole = $_SESSION['tipo_usuario'] ?? 'Agente';
        $currentUserId = $_SESSION['usuario_id'];
        $currentUserName = $_SESSION['usuario'];
        $currentUserSucursalId = $_SESSION['sucursal_id'] ?? null;

        // Obtener todas las solicitudes de la base de datos
        $all_solicitudes = $this->requestModel->getAll();

        // Permission Logic y procesamiento de datos
        $solicitudes = [];

        foreach ($all_solicitudes as $req) {
            // Ensure the date is a string before passing to strtotime
            if ($req->fecha_solicitud instanceof DateTime) {
                $req->fecha_solicitud = $req->fecha_solicitud->format('Y-m-d H:i:s');
            }

            $solicitud = [
                'id' => (string) $req->id,
                'solicitante_id' => $req->solicitante_id,
                'solicitado_id' => $req->solicitado_id,
                'origen' => $req->usuario_nombre ?? 'Desconocido',
                'origen_rol' => $req->tipo_usuario ?? 'Agente',
                'destino' => $req->solicitado_nombre ?? 'Almacén Central',
                'destino_rol' => $req->solicitado_tipo_usuario ?? 'Administrador',
                'fecha' => date('M d, Y', strtotime($req->fecha_solicitud)),
                'prioridad' => ucfirst(strtolower($req->prioridad)),
                'prioridad_class' => $this->getPrioridadClass($req->prioridad),
                'estado' => ucfirst(strtolower($req->estado)),
                'estado_class' => $this->getEstadoClass($req->estado),
                'tipo' => ucfirst(strtolower($req->tipo ?? 'general')),
                'descripcion' => ucfirst($req->descripcion ?? '')
            ];

            // Obtener detalles de items para este solicitud
            $detalles = $this->requestModel->getDetalles($req->id);
            $solicitud['items_summary'] = count($detalles) . ' item' . (count($detalles) != 1 ? 's' : '');
            $solicitud['items_detail'] = [];

            foreach ($detalles as $detalle) {
                $solicitud['items_detail'][] = [
                    'id' => $detalle->producto_id,
                    'nombre' => $detalle->producto_nombre,
                    'sku' => $detalle->producto_codigo,
                    'cantidad' => $detalle->cantidad,
                    'observaciones' => $detalle->observaciones ?? ''
                ];
            }

            // Aplicar lógica de permisos
            $incluir = false;
            $puedeActuar = false; // Determina si el usuario puede aprobar/rechazar/modificar

            if ($currentUserRole === 'Administrador') {
                // Administrador ve TODAS las solicitudes
                $incluir = true;
                // Puede actuar en solicitudes pendientes dirigidas a él
                $puedeActuar = ($req->estado === 'Pendiente' && $req->solicitado_id == $currentUserId);

            } elseif ($currentUserRole === 'Sucursal') {
                // Sucursal ve:
                // 1. Solicitudes dirigidas a ella
                // 2. Solicitudes creadas por ella misma
                if (($req->solicitado_id == $currentUserId) || ($req->solicitante_id == $currentUserId)) {
                    $incluir = true;
                    // Puede actuar solo en solicitudes pendientes dirigidas a ella (no las que ella creó)
                    $puedeActuar = ($req->estado === 'Pendiente' && $req->solicitado_id == $currentUserId && $req->solicitante_id != $currentUserId);
                }

            } elseif ($currentUserRole === 'Agente') {
                // Agente solo ve sus propias solicitudes
                if ($req->solicitante_id == $currentUserId) {
                    $incluir = true;
                    $puedeActuar = false; // Agente nunca puede actuar, solo ver
                }
            }

            // Check if this request has a counter-offer (is in negotiation)
            $solicitud['is_counter_offer'] = $req->is_counter_offer ?? 0;
            $solicitud['parent_request_id'] = $req->parent_request_id ?? null;
            $solicitud['counter_offer_notes'] = '';
            $solicitud['es_solicitante_original'] = ($req->solicitante_id == $currentUserId);
            $solicitud['was_counter_offered'] = false;
            $solicitud['counter_offer_items'] = null;
            $solicitud['counter_offer_id'] = null;
            
            $estadoLower = strtolower(trim($req->estado));
            
            $negotiation = $this->requestModel->getNegotiationBySolicitudId($req->id);
            
            if ($negotiation) {
                $solicitud['counter_offer_id'] = $negotiation->solicitud_id;
                $solicitud['counter_offer_notes'] = $negotiation->counter_offer_notes ?? '';
                
                // Get negotiation items
                $negotiationItems = $this->requestModel->getNegotiationDetails($negotiation->id);
                $solicitud['counter_offer_items'] = [];
                
                foreach ($negotiationItems as $detalle) {
                    $solicitud['counter_offer_items'][] = [
                        'id' => $detalle->producto_id,
                        'nombre' => $detalle->producto_nombre,
                        'sku' => $detalle->producto_codigo,
                        'cantidad' => $detalle->cantidad, // Aliased in model
                        'observaciones' => $detalle->observaciones ?? ''
                    ];
                }
                
                // If negotiation was accepted, replace the main items_detail with negotiation quantities
                // Logic: Parent is 'Aprobada con Cambios' AND negotiation is 'Aceptada'
                if (($estadoLower === 'aprobada con cambios' || strpos($estadoLower, 'aprobada') !== false) && strtolower($negotiation->estado) === 'aceptada') {
                    $solicitud['items_detail'] = $solicitud['counter_offer_items'];
                    $solicitud['was_counter_offered'] = true;
                }
            }

            // Determinar acción y clase del botón
            if ($puedeActuar) {
                $solicitud['accion'] = 'Revisar';
                $solicitud['accion_class'] = 'blue';
                $solicitud['puede_actuar'] = true;
                $solicitud['puede_negociar'] = false;
            } elseif ($estadoLower === 'en negociación' || $estadoLower === 'en negociacion') {
                // Check if user is original requester (can accept/reject counter-offer)
                if ($req->solicitante_id == $currentUserId) {
                    $solicitud['accion'] = 'Revisar Oferta';
                    $solicitud['accion_class'] = 'amber';
                    $solicitud['puede_actuar'] = false;
                    $solicitud['puede_negociar'] = true;
                } else {
                    // Other users can just view the negotiation
                    $solicitud['accion'] = 'Ver Detalles';
                    $solicitud['accion_class'] = 'slate';
                    $solicitud['puede_actuar'] = false;
                    $solicitud['puede_negociar'] = false;
                }
            } else {
                $solicitud['accion'] = ($req->estado === 'pendiente') ? 'Ver Estado' : 'Ver Detalles';
                $solicitud['accion_class'] = 'slate';
                $solicitud['puede_actuar'] = false;
                $solicitud['puede_negociar'] = false;
            }

            if ($incluir) {
                $solicitudes[] = $solicitud;
            }
        }

        $data = [
            'pageTitle' => 'Solicitudes',
            'solicitudes' => $solicitudes,
            'currentUserRole' => $currentUserRole
        ];

        $this->view('solicitudes/index', $data);
    }

    // Helper para obtener clase CSS de prioridad
    private function getPrioridadClass($prioridad)
    {
        $classes = [
            'alta' => 'red',
            'media' => 'amber',
            'normal' => 'slate',
            'baja' => 'gray'
        ];
        return $classes[strtolower($prioridad)] ?? 'slate';
    }

    // Helper para obtener clase CSS de estado
    private function getEstadoClass($estado)
    {
        $classes = [
            'pendiente' => 'pending',
            'aprobada' => 'approved',
            'rechazada' => 'rejected',
            'completada' => 'completed',
            'en_proceso' => 'in-progress',
            'en negociación' => 'negotiation',
            'en_negociación' => 'negotiation',
            'aprobada con cambios' => 'approved-modified'
        ];
        return $classes[strtolower($estado)] ?? 'pending';
    }

    public function nueva()
    {
        if (!isset($_SESSION['usuario'])) {
            // Check if this is an AJAX request
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'No autorizado']);
                exit();
            }
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Determine if this is an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            // Validar solicitado_id
            $solicitadoId = trim($_POST['solicitado_id'] ?? '');
            if (empty($solicitadoId)) {
                $errorMsg = 'Debe especificar a quién va dirigida la solicitud.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => $errorMsg]);
                    exit();
                }
                $_SESSION['error_message'] = $errorMsg;
                header('Location: ' . URL_BASE . '/productos');
                exit();
            }

            // Validar que haya un producto
            $productoId = $_POST['producto_id'] ?? null;
            $cantidad = $_POST['cantidad'] ?? null;
            
            if (empty($productoId) || empty($cantidad)) {
                $errorMsg = 'Debe especificar un producto y cantidad.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => $errorMsg]);
                    exit();
                }
                $_SESSION['error_message'] = $errorMsg;
                header('Location: ' . URL_BASE . '/productos');
                exit();
            }

            // Validar stock disponible antes de crear la solicitud
            $producto = $this->productModel->getById($productoId);
            
            if (!$producto) {
                $errorMsg = 'El producto seleccionado no existe.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => $errorMsg]);
                    exit();
                }
                $_SESSION['error_message'] = $errorMsg;
                header('Location: ' . URL_BASE . '/productos');
                exit();
            }

            // Verificar si hay suficiente stock
            if ($producto->stock < $cantidad) {
                $errorMsg = 'Stock insuficiente.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => $errorMsg]);
                    exit();
                }
                $_SESSION['error_message'] = $errorMsg;
                header('Location: ' . URL_BASE . '/productos');
                exit();
            }

            // Crear nueva solicitud
            $solicitudData = [
                'solicitante_id' => $_SESSION['usuario_id'],
                'solicitado_id' => $solicitadoId,
                'tipo' => 'Producto',
                'descripcion' => trim($_POST['notas'] ?? ''),
                'prioridad' => 'Normal',
                'estado' => 'Pendiente'
            ];

            $solicitudId = $this->requestModel->create($solicitudData);

            if ($solicitudId) {
                // Agregar el producto a la solicitud
                $detalleCreado = $this->requestModel->addDetalle(
                    $solicitudId,
                    $productoId,
                    $cantidad,
                    trim($_POST['notas'] ?? '')
                );

                if ($detalleCreado) {
                    $successMsg = 'Solicitud creada exitosamente.';
                    if ($isAjax) {
                        $_SESSION['success_message'] = $successMsg;
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true, 
                            'message' => $successMsg,
                            'solicitud_id' => $solicitudId,
                            'redirect' => URL_BASE . '/requests.php'
                        ]);
                        exit();
                    }
                    $_SESSION['success_message'] = $successMsg;
                    header('Location: ' . URL_BASE . '/productos.php');
                } else {
                    $errorMsg = 'Solicitud creada pero error al agregar el producto.';
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        http_response_code(500);
                        echo json_encode(['success' => false, 'message' => $errorMsg]);
                        exit();
                    }
                    $_SESSION['error_message'] = $errorMsg;
                    header('Location: ' . URL_BASE . '/productos.php');
                }
            } else {
                $errorMsg = 'Error al crear la solicitud.';
                if ($isAjax) {
                    header('Content-Type: application/json');
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => $errorMsg]);
                    exit();
                }
                $_SESSION['error_message'] = $errorMsg;
                header('Location: ' . URL_BASE . '/productos.php');
            }
            exit();
        } else {
            // GET request - Mostrar formulario de nueva solicitud
            $productos = $this->productModel->getAll();
            $this->view('solicitudes/nueva', [
                'pageTitle' => 'Nueva Solicitud',
                'productos' => $productos
            ]);
        }
    }

    public function ver($id)
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        $solicitud = $this->requestModel->getById($id);

        if (!$solicitud) {
            header('Location: ' . URL_BASE . '/solicitudes/index');
            exit();
        }

        $detalles = $this->requestModel->getDetalles($id);

        $this->view('solicitudes/ver', [
            'pageTitle' => 'Detalle de Solicitud',
            'solicitud' => $solicitud,
            'detalles' => $detalles
        ]);
    }

    public function aprobar($id)
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit();
        }

        $currentUserRole = $_SESSION['tipo_usuario'];

        if ($currentUserRole === 'Administrador' || $currentUserRole === 'Sucursal') {
            $result = $this->requestModel->updateEstado($id, 'Aprobada', $_SESSION['usuario_id']);
            

            if ($result) {
                $_SESSION['success_message'] = 'Solicitud aprobada exitosamente. El stock ha sido actualizado.';
                echo json_encode(['success' => true, 'message' => 'Solicitud aprobada exitosamente. El stock ha sido actualizado.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al aprobar la solicitud. Verifica que haya stock suficiente.']);
            }
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Sin permisos para esta acción.']);
        }
        
        exit();
    }

    public function rechazar($id)
    {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit();
        }

        $currentUserRole = $_SESSION['tipo_usuario'];

        if ($currentUserRole === 'Administrador' || $currentUserRole === 'Sucursal') {
            $result = $this->requestModel->updateEstado($id, 'Rechazada', $_SESSION['usuario_id']);
            
            if ($result) {
                $_SESSION['success_message'] = 'Solicitud rechazada exitosamente.';
                echo json_encode(['success' => true, 'message' => 'Solicitud rechazada exitosamente.']);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al rechazar la solicitud.']);
            }
        } else {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Sin permisos para esta acción.']);
        }
        
        exit();
    }

    public function modificar($id)
    {
        if (!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        $currentUserRole = $_SESSION['tipo_usuario'];

        // Solo Admin y Sucursal pueden modificar
        if ($currentUserRole !== 'Administrador' && $currentUserRole !== 'Sucursal') {
            header('Location: ' . URL_BASE . '/requests.php');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            header('Content-Type: application/json');
            
            // Obtener solicitud original
            $originalRequest = $this->requestModel->getById($id);
            
            if (!$originalRequest) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Solicitud no encontrada.']);
                exit();
            }

            // Obtener detalles modificados del formulario
            $modified_details = [];
            
            // Esperamos que vengan arrays de producto_id y cantidad
            $producto_ids = $_POST['producto_id'] ?? [];
            $cantidades = $_POST['cantidad'] ?? [];
            $observaciones = $_POST['observaciones'] ?? [];

            if (empty($producto_ids) || empty($cantidades)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Debe especificar productos y cantidades.']);
                exit();
            }

            // Construir array de detalles modificados
            foreach ($producto_ids as $index => $producto_id) {
                if (isset($cantidades[$index]) && $cantidades[$index] > 0) {
                    $modified_details[] = [
                        'producto_id' => $producto_id,
                        'cantidad' => $cantidades[$index],
                        'observaciones' => $observaciones[$index] ?? ''
                    ];
                }
            }

            if (empty($modified_details)) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Debe especificar al menos un producto con cantidad válida.']);
                exit();
            }

            // Obtener notas de modificación
            $counter_offer_notes = $_POST['counter_offer_notes'] ?? 'Modificación de cantidades solicitadas';

            // Crear NEGOCIACIÓN (Refactor)
            $negotiationId = $this->requestModel->createNegotiation(
                $id,
                $_SESSION['usuario_id'],
                $modified_details,
                $counter_offer_notes
            );

            if ($negotiationId) {
                $_SESSION['success_message'] = 'Contra-oferta enviada exitosamente.';
                echo json_encode(['success' => true, 'message' => 'Contra-oferta enviada exitosamente.', 'counter_offer_id' => $negotiationId]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'message' => 'Error al crear la contra-oferta.']);
            }
            
            exit();
        } else {
            // GET request - Mostrar formulario de modificación
            $solicitud = $this->requestModel->getById($id);
            
            if (!$solicitud) {
                header('Location: ' . URL_BASE . '/requests.php');
                exit();
            }

            $detalles = $this->requestModel->getDetalles($id);

            $this->view('solicitudes/modificar', [
                'pageTitle' => 'Modificar Solicitud',
                'solicitud' => $solicitud,
                'detalles' => $detalles
            ]);
        }
    }

    public function aceptar_contraoferta($id)
    {
        header('Content-Type: application/json');
        
        // En el frontend enviamos el ID de la solicitud principal al endpoint
        // Pero el modelo nuevo necesita el ID de la negociación?
        // Revisemos `performAction` en JS: call `aceptar_contraoferta/${requestId}`.
        // Así que recibimos `$requestId`. 
        // Necesitamos buscar la negociación pendiente asociada a esa request.
        
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit();
        }

        // Buscar negociación pendiente
        $negotiation = $this->requestModel->getNegotiationBySolicitudId($id);


        if (!$negotiation) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'No se encontró negociación activa.']);
            exit();
        }

        if ($this->requestModel->acceptNegotiation($negotiation->id, $id, $_SESSION['usuario_id'])) {
             echo json_encode(['success' => true, 'message' => 'Contra-oferta aceptada correctamente']);
        } else {
             http_response_code(500);
             echo json_encode(['success' => false, 'message' => 'Error al aceptar contra-oferta']);
        }
        exit();
    }



    public function rechazar_contraoferta($id)
    {
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit();
        }
        
        // Buscar negociación pendiente
        $negotiation = $this->requestModel->getNegotiationBySolicitudId($id);
        
        if (!$negotiation) {
             http_response_code(404);
             echo json_encode(['success' => false, 'message' => 'No se encontró negociación activa.']);
             exit();
        }

        if ($this->requestModel->rejectNegotiation($negotiation->id, $id)) {
             echo json_encode(['success' => true, 'message' => 'Contra-oferta rechazada correctamente']);
        } else {
             http_response_code(500);
             echo json_encode(['success' => false, 'message' => 'Error al rechazar contra-oferta']);
        }
        exit();
    }
}
