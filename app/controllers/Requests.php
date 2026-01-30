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
        if(!isset($_SESSION['usuario'])) {
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
                'id' => (string)$req->id,
                'usuario_id' => $req->usuario_id,
                'sucursal_id' => $req->sucursal_id,
                'origen' => $req->usuario_nombre ?? 'Desconocido',
                'origen_rol' => $req->tipo_usuario ?? 'Agente',
                'destino' => $req->sucursal_nombre ?? 'Almacén Central',
                'destino_rol' => ($req->sucursal_id ? 'Sucursal' : 'Administrador'),
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
                // Puede actuar en solicitudes pendientes dirigidas a Administrador (sin sucursal específica)
                $puedeActuar = ($req->estado === 'pendiente' && empty($req->sucursal_id));
                
            } elseif ($currentUserRole === 'Sucursal') {
                // Sucursal ve:
                // 1. Solicitudes dirigidas a su sucursal
                // 2. Solicitudes creadas por ella misma
                if (($req->sucursal_id == $currentUserSucursalId) || ($req->usuario_id == $currentUserId)) {
                    $incluir = true;
                    // Puede actuar solo en solicitudes pendientes dirigidas a su sucursal
                    $puedeActuar = ($req->estado === 'pendiente' && $req->sucursal_id == $currentUserSucursalId && $req->usuario_id != $currentUserId);
                }
                
            } elseif ($currentUserRole === 'Agente') {
                // Agente solo ve sus propias solicitudes
                if ($req->usuario_id == $currentUserId) {
                    $incluir = true;
                    $puedeActuar = false; // Agente nunca puede actuar, solo ver
                }
            }

            // Determinar acción y clase del botón
            if ($puedeActuar) {
                $solicitud['accion'] = 'Revisar';
                $solicitud['accion_class'] = 'blue';
                $solicitud['puede_actuar'] = true;
            } else {
                $solicitud['accion'] = ($req->estado === 'pendiente') ? 'Ver Estado' : 'Ver Detalles';
                $solicitud['accion_class'] = 'slate';
                $solicitud['puede_actuar'] = false;
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
            'en_proceso' => 'in-progress'
        ];
        return $classes[strtolower($estado)] ?? 'pending';
    }

    public function nueva()
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Crear nueva solicitud
            $solicitudData = [
                'solicitante_id' => $_SESSION['usuario_id'],
                'tipo' => trim($_POST['tipo'] ?? 'general'),
                'descripcion' => trim($_POST['descripcion'] ?? ''),
                'prioridad' => trim($_POST['prioridad'] ?? 'normal'),
                'estado' => 'pendiente'
            ];
            
            var_dump($_POST);
            var_dump($solicitudData);

            exit();


            $solicitudId = $this->requestModel->create($solicitudData);
            
            if ($solicitudId) {
                // Agregar productos a la solicitud
                if (isset($_POST['productos']) && is_array($_POST['productos'])) {
                    foreach ($_POST['productos'] as $producto) {
                        if (!empty($producto['id']) && !empty($producto['cantidad'])) {
                            $this->requestModel->addDetalle(
                                $solicitudId,
                                $producto['id'],
                                $producto['cantidad'],
                                $producto['observaciones'] ?? ''
                            );
                        }
                    }
                }
                
                header('Location: ' . URL_BASE . '/solicitudes/index');
                exit();
            } else {
                $productos = $this->productModel->getAll();
                $this->view('solicitudes/nueva', [
                    'pageTitle' => 'Nueva Solicitud',
                    'productos' => $productos,
                    'error' => 'Error al crear la solicitud.'
                ]);
            }
        } else {
            // Obtener productos para el formulario
            $productos = $this->productModel->getAll();
            $this->view('solicitudes/nueva', [
                'pageTitle' => 'Nueva Solicitud',
                'productos' => $productos
            ]);
        }
    }

    public function ver($id)
    {
        if(!isset($_SESSION['usuario'])) {
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
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        $currentUserRole = $_SESSION['tipo_usuario'];
        
        if ($currentUserRole === 'Administrador' || $currentUserRole === 'Sucursal') {
            $this->requestModel->updateEstado($id, 'aprobada', $_SESSION['usuario_id']);
        }
        
        header('Location: ' . URL_BASE . '/solicitudes/index');
        exit();
    }

    public function rechazar($id)
    {
        if(!isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/auth');
            exit();
        }

        $currentUserRole = $_SESSION['tipo_usuario'];
        
        if ($currentUserRole === 'Administrador' || $currentUserRole === 'Sucursal') {
            $this->requestModel->updateEstado($id, 'rechazada', $_SESSION['usuario_id']);
        }
        
        header('Location: ' . URL_BASE . '/solicitudes/index');
        exit();
    }
}
