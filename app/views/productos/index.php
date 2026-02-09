<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- Success/Error Messages -->
<?php if (isset($_SESSION['success_message'])): ?>
<div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center gap-3">
    <i class="fa-solid fa-check-circle text-green-600"></i>
    <span><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></span>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
<div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center gap-3">
    <i class="fa-solid fa-exclamation-circle text-red-600"></i>
    <span><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></span>
</div>
<?php endif; ?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <?php if ($esSucursal):?>
                <h2 class="text-xl font-bold text-gray-800">Inventario de Sucursal</h2>
                <p class="text-gray-500 text-sm">Visualiza y gestiona tu propio inventario.</p>
            <?php else: ?>
                <h2 class="text-xl font-bold text-gray-800">Inventario General</h2>
                <p class="text-gray-500 text-sm">Visualiza y gestiona el inventario general.</p>
            <?php endif; ?>
        </div>
        <div class="flex gap-3">
            <?php if ($tipo_usuario === 'Administrador'): ?>
                <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-plus"></i> Nuevo Producto
                </button>
            <?php endif; ?>
            <button class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium rounded-lg transition-colors">
                <i class="fa-solid fa-file-export"></i> Exportar
            </button>
        </div>
    </div>

    <!-- Search handled by DataTables -->

    <!-- Products Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table id="productosTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Código</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Producto</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Categoría</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Estado</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Stock</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php foreach ($productos as $producto): ?>
                    <?php
                    // Determine Badge Colors
                    $badgeColorClass = match ($producto['stock_class']) {
                        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
                        default => 'bg-gray-50 text-gray-600 border-gray-200',
                    };

                    // Determine Status Dot Colors
                    $dotColorClass = match ($producto['status_class']) {
                        'active' => 'bg-emerald-500',
                        'warning' => 'bg-amber-500',
                        default => 'bg-gray-400',
                    };
                    $statusTextColor = match ($producto['status_class']) {
                        'active' => 'text-emerald-700',
                        'warning' => 'text-amber-700',
                        default => 'text-gray-500',
                    };
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-blue-600 bg-blue-50 px-2.5 py-1 rounded text-xs font-medium border border-blue-100">
                                <?php echo $producto['codigo']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500">
                                    <i class="fa-solid <?php echo $producto['icon']; ?>"></i>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900"><?php echo $producto['nombre']; ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $producto['descripcion']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4"><?php echo $producto['categoria']; ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5">
                                <div class="w-2 h-2 rounded-full <?php echo $dotColorClass; ?>"></div>
                                <span class="font-medium <?php echo $statusTextColor; ?>">
                                    <?php echo $producto['estado']; ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($tipo_usuario === 'Administrador' || ($tipo_usuario === 'Sucursal' && $esSucursal)): ?>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border <?php echo $badgeColorClass; ?>">
                                    <?php echo $producto['stock']; ?>
                                </span>
                            <?php else: ?>
                                <button class="px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-blue-50 text-blue-600 border-blue-200 hover:bg-blue-100" onclick="openRequestModal('<?php echo $producto['id']; ?>')">
                                    Solicitar
                                </button>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <?php if ($tipo_usuario === 'Administrador' || ($tipo_usuario === 'Sucursal' && $esSucursal)): ?>
                                    <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors border border-transparent hover:border-blue-100">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors border border-transparent hover:border-gray-200" onclick="openDetailModal('<?php echo $producto['id']; ?>')">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <?php if ($tipo_usuario === 'Administrador' || ($tipo_usuario === 'Sucursal' && $esSucursal)): ?>
                                    <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors border border-transparent hover:border-red-100">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination handled by DataTables -->
</div>

<!-- Request Modal -->
<div id="requestModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg transform transition-all">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fa-solid fa-shopping-cart text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Solicitar Producto</h3>
                    <p class="text-sm text-gray-500">Completa el formulario para hacer tu solicitud</p>
                </div>
            </div>
            <button type="button" onclick="closeRequestModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <!-- Error Message Area -->
            <div id="requestErrorMessage" class="hidden mb-4 p-4 rounded-lg bg-red-50 border border-red-200">
                <div class="flex gap-2">
                    <i class="fa-solid fa-exclamation-circle text-red-600 mt-0.5"></i>
                    <p id="requestErrorText" class="text-sm text-red-700 font-medium"></p>
                </div>
            </div>
            
            <div id="requestProductInfo" class="bg-gray-50 rounded-lg p-4 mb-6 border border-gray-200">
                <div class="flex items-center gap-3">
                    <div id="requestProductIcon" class="w-12 h-12 rounded-lg bg-white flex items-center justify-center text-gray-500">
                        <i class="fa-solid fa-box"></i>
                    </div>
                    <div class="flex-1">
                        <h4 id="requestProductName" class="font-semibold text-gray-900">Producto</h4>
                        <p id="requestProductCode" class="text-sm text-gray-500">Código: ---</p>
                    </div>
                </div>
            </div>
            
            <form id="requestForm" method="POST" action="<?php echo URL_BASE; ?>/requests.php/nueva" class="space-y-4">
                <input type="hidden" id="requestProductId" name="producto_id" value="">
                
                <!-- Solicitado ID field: Determined by user type and context -->
                <?php 
                    $defaultSolicitadoId = '';
                    $destinatarioNombre = '';
                    
                    if ($tipo_usuario === 'Agente') {
                        // Agente requests to the Sucursal whose inventory they're viewing
                        if (isset($sucursal_id) && !empty($sucursal_id)) {
                            $defaultSolicitadoId = $sucursal_id;
                            $destinatarioNombre = 'la Sucursal';
                        } else {
                            // Fallback: request to administrator if no sucursal context
                            $defaultSolicitadoId = $administrador->usuario_id ?? '';
                            $destinatarioNombre = $administrador->nombre ?? 'Administrador';
                        }
                    } elseif ($tipo_usuario === 'Sucursal') {
                        // Sucursal always requests to Administrator
                        $defaultSolicitadoId = $administrador->usuario_id ?? '';
                        $destinatarioNombre = $administrador->nombre ?? 'Administrador';
                    }
                ?>
                <input type="hidden" id="requestSolicitadoId" name="solicitado_id" value="<?php echo $defaultSolicitadoId; ?>">
                
                <div>
                    <label for="requestQuantity" class="block text-sm font-semibold text-gray-700 mb-2">
                        Cantidad Solicitada <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="requestQuantity" name="cantidad" min="1" value="1" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                </div>

                <!-- Information showing where request will be sent -->
                <?php if (($tipo_usuario === 'Agente' || $tipo_usuario === 'Sucursal') && !empty($destinatarioNombre)): ?>
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex gap-2">
                            <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
                            <div class="text-sm text-blue-700">
                                <p class="font-semibold mb-1">Destinatario de la solicitud</p>
                                <p>Tu solicitud será enviada a <strong><?php echo $destinatarioNombre; ?></strong> para su aprobación.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div>
                    <label for="requestNotes" class="block text-sm font-semibold text-gray-700 mb-2">
                        Notas Adicionales <span class="text-gray-400 font-normal">(Opcional)</span>
                    </label>
                    <textarea id="requestNotes" name="notas" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow resize-none"
                              placeholder="Agrega información adicional sobre tu solicitud..."></textarea>
                </div>
                
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex gap-2">
                        <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
                        <p class="text-sm text-blue-700">
                            Tu solicitud será procesada y recibirás una notificación cuando sea aprobada o rechazada.
                        </p>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
            <button type="button" onclick="closeRequestModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cancelar
            </button>
            <button type="submit" form="requestForm" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <i class="fa-solid fa-paper-plane mr-2"></i>Enviar Solicitud
            </button>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl transform transition-all">
        <!-- Modal Header -->
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                    <i class="fa-solid fa-info-circle text-purple-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Detalle del Producto</h3>
                    <p class="text-sm text-gray-500">Información completa del producto</p>
                </div>
            </div>
            <button type="button" onclick="closeDetailModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6">
            <!-- Product Header -->
            <div class="flex items-start gap-4 mb-6 pb-6 border-b border-gray-200">
                <div id="detailProductIcon" class="w-16 h-16 rounded-xl bg-gray-100 flex items-center justify-center text-gray-500 text-2xl flex-shrink-0">
                    <i class="fa-solid fa-box"></i>
                </div>
                <div class="flex-1">
                    <div class="flex items-start justify-between mb-2">
                        <h4 id="detailProductName" class="text-xl font-bold text-gray-900">Producto</h4>
                        <span id="detailProductStatus" class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Activo
                        </span>
                    </div>
                    <p id="detailProductDescription" class="text-gray-600 mb-3">Descripción del producto</p>
                    <span id="detailProductCode" class="inline-block font-mono text-sm text-blue-600 bg-blue-50 px-3 py-1 rounded-lg border border-blue-100">
                        Código: ---
                    </span>
                </div>
            </div>
            
            <!-- Product Details Grid -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-tag text-gray-400"></i>
                        <span class="text-sm text-gray-500 font-medium">Categoría</span>
                    </div>
                    <p id="detailProductCategory" class="text-lg font-semibold text-gray-900">---</p>
                </div>
                
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-dollar-sign text-gray-400"></i>
                        <span class="text-sm text-gray-500 font-medium">Precio Unitario</span>
                    </div>
                    <p id="detailProductPrice" class="text-lg font-semibold text-gray-900">$0.00</p>
                </div>
                
                <div id="detailStockSection" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-boxes-stacked text-gray-400"></i>
                        <span class="text-sm text-gray-500 font-medium">Stock Disponible</span>
                    </div>
                    <p id="detailProductStock" class="text-lg font-semibold text-gray-900">---</p>
                </div>
                
                <div id="detailMinStockSection" class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <div class="flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-triangle-exclamation text-gray-400"></i>
                        <span class="text-sm text-gray-500 font-medium">Stock Mínimo</span>
                    </div>
                    <p id="detailProductMinStock" class="text-lg font-semibold text-gray-900">---</p>
                </div>
            </div>
            
            <!-- Additional Info -->
            <div id="detailAdminInfo" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                <div class="flex gap-2">
                    <i class="fa-solid fa-shield-halved text-amber-600 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold text-amber-900 mb-1">Información de Administrador</p>
                        <p class="text-sm text-amber-700">
                            Tienes acceso completo a este producto. Puedes editar, eliminar o gestionar su inventario.
                        </p>
                    </div>
                </div>
            </div>
            
            <div id="detailUserInfo" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex gap-2">
                    <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold text-blue-900 mb-1">Información</p>
                        <p class="text-sm text-blue-700">
                            Para solicitar este producto, utiliza el botón "Solicitar" en la tabla de productos.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
            <button type="button" onclick="closeDetailModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cerrar
            </button>
            <button type="button" id="detailRequestBtn" class="hidden px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <i class="fa-solid fa-shopping-cart mr-2"></i>Solicitar Producto
            </button>
        </div>
    </div>
</div>

<script>
    // Store products data for modal access
    const productsData = <?php echo json_encode($productos); ?>;
    const userType = '<?php echo $tipo_usuario; ?>';
    const esSucursal = <?php echo $esSucursal ? 'true' : 'false'; ?>;
    const administradorId = <?php echo json_encode($administrador->usuario_id ?? null); ?>;
    const baseUrl = '<?php echo URL_BASE; ?>';
    
    // Request Modal Functions
    function openRequestModal(productId) {
        const product = productsData.find(p => p.id == productId);
        if (!product) {
            console.error('Product not found:', productId);
            return;
        }

        // Hide error message from previous attempts
        document.getElementById('requestErrorMessage').classList.add('hidden');

        // Populate request modal
        document.getElementById('requestProductIcon').innerHTML = `<i class="fa-solid ${product.icon}"></i>`;
        document.getElementById('requestProductName').textContent = product.nombre;
        document.getElementById('requestProductCode').textContent = `Código: ${product.codigo}`;
        document.getElementById('requestQuantity').value = 1;
        document.getElementById('requestNotes').value = '';
        
        // Set product ID in hidden field
        document.getElementById('requestProductId').value = productId;
        
        // For Agente users, solicitado_id is already set (no need to reset)
        // Just keep the pre-filled value
        
        // Show modal
        const modal = document.getElementById('requestModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Trigger animation
        setTimeout(() => {
            modal.style.opacity = '1';
            const innerDiv = modal.querySelector('div');
            if (innerDiv) {
                innerDiv.style.transform = 'scale(1)';
                innerDiv.style.opacity = '1';
            }
        }, 10);
    }

    function closeRequestModal() {
        const modal = document.getElementById('requestModal');
        const innerDiv = modal.querySelector('div');
        
        // Hide error message
        document.getElementById('requestErrorMessage').classList.add('hidden');
        
        // Trigger close animation
        if (innerDiv) {
            innerDiv.style.transform = 'scale(0.95)';
            innerDiv.style.opacity = '0';
        }
        modal.style.opacity = '0';
        
        // Hide modal after animation
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 200);
    }
    
    // Handle request form submission via AJAX
    document.getElementById('requestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Hide any previous error messages
        const errorDiv = document.getElementById('requestErrorMessage');
        errorDiv.classList.add('hidden');
        
        // Get submit button
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        // Disable form and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Enviando...';
        
        // Prepare form data
        const formData = new FormData(this);
        
        // Submit via AJAX
        fetch(`${baseUrl}/requests.php/nueva`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Success - redirect to requests page
                window.location.href = data.redirect || `${baseUrl}/requests.php`;
            } else {
                // Show error in modal
                document.getElementById('requestErrorText').textContent = data.message || 'Error al crear la solicitud';
                errorDiv.classList.remove('hidden');
                
                // Re-enable form
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('requestErrorText').textContent = 'Error de conexión. Por favor, intenta de nuevo.';
            errorDiv.classList.remove('hidden');
            
            // Re-enable form
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
    
    // Detail Modal Functions
    function openDetailModal(productId) {
        const product = productsData.find(p => p.id == productId);
        if (!product) {
            console.error('Product not found:', productId);
            return;
        }
        
        // Populate detail modal
        document.getElementById('detailProductIcon').innerHTML = `<i class="fa-solid ${product.icon}"></i>`;
        document.getElementById('detailProductName').textContent = product.nombre;
        document.getElementById('detailProductDescription').textContent = product.descripcion;
        document.getElementById('detailProductCode').textContent = `Código: ${product.codigo}`;
        document.getElementById('detailProductCategory').textContent = product.categoria;
        document.getElementById('detailProductPrice').textContent = product.precio;
        
        // Status badge
        const statusBadge = document.getElementById('detailProductStatus');
        statusBadge.textContent = product.estado;
        statusBadge.className = 'px-3 py-1 rounded-full text-xs font-semibold';
        if (product.status_class === 'active') {
            statusBadge.className += ' bg-emerald-50 text-emerald-700 border border-emerald-200';
        } else if (product.status_class === 'warning') {
            statusBadge.className += ' bg-amber-50 text-amber-700 border border-amber-200';
        } else {
            statusBadge.className += ' bg-gray-50 text-gray-500 border border-gray-200';
        }
        
        // Show/hide stock info based on user type
        const stockSection = document.getElementById('detailStockSection');
        const minStockSection = document.getElementById('detailMinStockSection');
        const adminInfo = document.getElementById('detailAdminInfo');
        const userInfo = document.getElementById('detailUserInfo');
        const requestBtn = document.getElementById('detailRequestBtn');
        
        if (userType === 'Administrador' || (userType === 'Sucursal' && esSucursal)) {
            // Admin view OR Sucursal viewing their own inventory
            stockSection.classList.remove('hidden');
            minStockSection.classList.remove('hidden');
            document.getElementById('detailProductStock').textContent = product.stock;
            document.getElementById('detailProductMinStock').textContent = product.stock_minimo;
            adminInfo.classList.remove('hidden');
            userInfo.classList.add('hidden');
            requestBtn.classList.add('hidden');
        } else {
            // Agente OR Sucursal viewing admin inventory
            stockSection.classList.add('hidden');
            minStockSection.classList.add('hidden');
            adminInfo.classList.add('hidden');
            userInfo.classList.remove('hidden');
            requestBtn.classList.remove('hidden');
            requestBtn.onclick = () => {
                closeDetailModal();
                setTimeout(() => openRequestModal(productId), 300);
            };
        }
        
        // Show modal
        const modal = document.getElementById('detailModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        // Trigger animation
        setTimeout(() => {
            modal.style.opacity = '1';
            const innerDiv = modal.querySelector('div');
            if (innerDiv) {
                innerDiv.style.transform = 'scale(1)';
                innerDiv.style.opacity = '1';
            }
        }, 10);
    }

    function closeDetailModal() {
        const modal = document.getElementById('detailModal');
        const innerDiv = modal.querySelector('div');
        
        // Trigger close animation
        if (innerDiv) {
            innerDiv.style.transform = 'scale(0.95)';
            innerDiv.style.opacity = '0';
        }
        modal.style.opacity = '0';
        
        // Hide modal after animation
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 200);
    }
    
    // Close modals with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const requestModal = document.getElementById('requestModal');
            const detailModal = document.getElementById('detailModal');
            
            if (!requestModal.classList.contains('hidden')) {
                closeRequestModal();
            }
            if (!detailModal.classList.contains('hidden')) {
                closeDetailModal();
            }
        }
    });
    
    // Close modals when clicking backdrop
    document.getElementById('requestModal').addEventListener('click', function(e) {
        if (e.target.id === 'requestModal') {
            closeRequestModal();
        }
    });
    
    document.getElementById('detailModal').addEventListener('click', function(e) {
        if (e.target.id === 'detailModal') {
            closeDetailModal();
        }
    });
</script>

<style>
    /* Modal animations */
    #requestModal,
    #detailModal {
        opacity: 0;
        transition: opacity 0.2s ease-out;
    }
    
    #requestModal > div,
    #detailModal > div {
        transform: scale(0.95);
        opacity: 0;
        transition: all 0.2s ease-out;
    }
    
    /* Smooth scrolling for modals */
    #requestModal,
    #detailModal {
        overflow-y: auto;
    }
    
    /* DataTables Custom Styling - Professional Look */
    /* Wrapper adjustments */
    #productosTable_wrapper {
        font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
        color: #000000; /* gray-700 */
    }

    /* Search input with subtle icon and focus ring */
    #productosTable_wrapper .dataTables_filter input {
        padding: 0.5rem 0.75rem 0.5rem 2.25rem;
        border: 1px solid #e5e7eb; /* gray-200 */
        background: #ffffff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23909aa0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='6'/%3E%3Cpath d='M21 21l-4.35-4.35'/%3E%3C/svg%3E") no-repeat 8px center;
        background-size: 14px;
        border-radius: 0.5rem;
        font-size: 0.92rem;
        margin-left: 0.5rem;
        box-shadow: 0 1px 2px rgba(16,24,40,0.03);
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }

    #productosTable_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: #3b82f6; /* blue-500 */
        box-shadow: 0 6px 18px rgba(59,130,246,0.12);
        background-color: #ffffff;
    }

    /* Length select (rows per page) */
    #productosTable_wrapper .dataTables_length select {
        padding: 0.45rem 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.92rem;
        margin: 0 0.5rem;
        background: white;
        box-shadow: 0 1px 2px rgba(16,24,40,0.03);
    }

    /* Info text and pagination container */
    #productosTable_wrapper .dataTables_info,
    #productosTable_wrapper .dataTables_paginate {
        padding-top: 1rem;
        font-size: 0.9rem;
        color: #000000; /* gray-500 */
    }

    /* Pagination buttons */
    #productosTable_wrapper .dataTables_paginate .paginate_button {
        padding: 0.45rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid transparent;
        border-radius: 0.5rem;
        font-size: 0.9rem;
        color: #000000; /* gray-700 */
        background: #ffffff;
        box-shadow: 0 1px 2px rgba(16,24,40,0.04);
        transition: background-color 0.12s ease, transform 0.08s ease, box-shadow 0.12s ease;
    }

    #productosTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #1d4ed8; /* subtle */
        border-color: #e6eefc;
        
    }

    #productosTable_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(180deg,#2563eb,#1d4ed8);
        border-color: #1d4ed8;
        color: white;
        box-shadow: 0 6px 18px rgba(29,78,216,0.18);
    }

    #productosTable_wrapper .dataTables_paginate .paginate_button.disabled {
        cursor: not-allowed;
        opacity: 0.45;
        transform: none;
    }

    /* Table visual polish */
    #productosTable {
        border-collapse: separate;
        border-spacing: 0;
        border: none;
    }

    #productosTable thead th {
        background: #f9fafb; /* gray-50 */
        color: #374151;
        font-weight: 600;
        border-bottom: 1px solid #e6e9ee;
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
    }

    #productosTable tbody td {
        padding: 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f3f4f6;
    }

    /* Zebra rows and hover */
    #productosTable tbody tr:nth-child(even) {
        background: #ffffff;
    }

    #productosTable tbody tr:hover {
        background: #fbfdff; /* very light blue */
        box-shadow: inset 0 0 0 1px rgba(59,130,246,0.03);
    }

    /* Small responsive tweaks */
    @media (max-width: 640px) {
        #productosTable_wrapper .dataTables_filter input {
            margin-left: 0;
            width: 100%;
            margin-bottom: 0.5rem;
        }

        #productosTable thead th,
        #productosTable tbody td {
            padding: 0.6rem 0.5rem;
        }
    }
</style>

<script>
    // Initialize DataTables when document is ready
    $(document).ready(function() {
        $('#productosTable').DataTable({
            responsive: true,
            language: {
                "decimal": "",
                "emptyTable": "No hay datos disponibles en la tabla",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ productos",
                "infoEmpty": "Mostrando 0 a 0 de 0 productos",
                "infoFiltered": "(filtrado de _MAX_ productos totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ productos",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron productos coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar la columna ascendente",
                    "sortDescending": ": activar para ordenar la columna descendente"
                }
            },
            pageLength: 10,
            order: [[1, 'asc']], // Sort by product name by default
            columnDefs: [
                { orderable: false, targets: [4, 5] } // Disable sorting on Stock and Actions columns
            ]
        });
    });
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>