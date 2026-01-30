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
            <h2 class="text-xl font-bold text-gray-800">Inventario General</h2>
            <p class="text-gray-500 text-sm">Administra el inventario completo, precios y existencias.</p>
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

    <!-- Filters & Search -->
    <div class="flex flex-col lg:flex-row justify-between gap-4 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
        <div class="relative flex-grow max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" placeholder="Buscar por código, nombre o categoría..." class="w-full bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 block pl-9 p-2.5 placeholder-gray-400 focus:outline-none transition-shadow">
        </div>
        <div class="flex flex-wrap gap-3">
            <select class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 block p-2.5 focus:outline-none min-w-[160px]">
                <option value="">Todas las Categorías</option>
                <option value="electronics">Electrónica</option>
                <option value="furniture">Mobiliario</option>
                <option value="consumables">Consumibles</option>
            </select>
            <select class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 block p-2.5 focus:outline-none min-w-[150px]">
                <option value="">Estado: Todos</option>
                <option value="active">Activo</option>
                <option value="low_stock">Stock Bajo</option>
                <option value="inactive">Inactivo</option>
            </select>
        </div>
    </div>

    <!-- Products Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Código</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Producto</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Categoría</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Stock</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Precio Unit.</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Estado</th>
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
                            <?php if ($tipo_usuario === 'Administrador'): ?>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border <?php echo $badgeColorClass; ?>">
                                    <?php echo $producto['stock']; ?>
                                </span>
                            <?php else: ?>
                                <button class="px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-blue-50 text-blue-600 border-blue-200 hover:bg-blue-100" onclick="openRequestModal('<?php echo $producto['id']; ?>')">
                                    Solicitar
                                </button>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">
                            <?php echo $producto['precio']; ?>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5">
                                <div class="w-2 h-2 rounded-full <?php echo $dotColorClass; ?>"></div>
                                <span class="font-medium <?php echo $statusTextColor; ?>">
                                    <?php echo $producto['estado']; ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <?php if ($tipo_usuario === 'Administrador'): ?>
                                    <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors border border-transparent hover:border-blue-100">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors border border-transparent hover:border-gray-200" onclick="openDetailModal('<?php echo $producto['id']; ?>')">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <?php if ($tipo_usuario === 'Administrador'): ?>
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

    <!-- Pagination -->
    <div class="flex flex-col md:flex-row justify-between items-center mt-6 pt-6 border-t border-gray-100 gap-4">
        <span class="text-sm text-gray-500">Mostrando <span class="text-gray-900 font-medium">1-3</span> de <span class="text-gray-900 font-medium">1247</span> resultados</span>
        <div class="flex items-center gap-2">
            <button class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed bg-gray-50"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-600 border border-blue-600 text-white font-medium shadow-sm">1</button>
            <button class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition-colors">2</button>
            <button class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition-colors">3</button>
            <span class="text-gray-400 px-1">...</span>
            <button class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </div>
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
                
                <!-- Solicitado ID field (hidden, will be set by JavaScript based on user type) -->
                <input type="hidden" id="requestSolicitadoId" name="solicitado_id" value="<?php echo ($tipo_usuario === 'Sucursal' ? ($administrador->usuario_id ?? '') : ''); ?>">
                
                <div>
                    <label for="requestQuantity" class="block text-sm font-semibold text-gray-700 mb-2">
                        Cantidad Solicitada <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="requestQuantity" name="cantidad" min="1" value="1" required
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                </div>

                <!-- Destinatario field - shown only for Agente users -->
                <?php if ($tipo_usuario === 'Agente'): ?>
                <div>
                    <label for="requestDestinatario" class="block text-sm font-semibold text-gray-700 mb-2">
                        Solicitar a <span class="text-red-500">*</span>
                    </label>
                    <select id="requestDestinatario" name="destinatario" required onchange="updateSolicitadoId()"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                        <option value="">Selecciona una sucursal...</option>
                        <?php foreach ($sucursales as $sucursal): ?>
                            <option value="<?php echo $sucursal->usuario_id; ?>"><?php echo $sucursal->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
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
                            Tu solicitud será enviada <?php echo ($tipo_usuario === 'Agente' ? 'a la sucursal seleccionada' : 'al administrador'); ?> para su aprobación. Recibirás una notificación cuando sea procesada.
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
    const administradorId = <?php echo json_encode($administrador->usuario_id ?? null); ?>;
    const baseUrl = '<?php echo URL_BASE; ?>';
    
    // Request Modal Functions
    function openRequestModal(productId) {
        const product = productsData.find(p => p.id == productId);
        if (!product) {
            console.error('Product not found:', productId);
            return;
        }
        
        // Populate request modal
        document.getElementById('requestProductIcon').innerHTML = `<i class="fa-solid ${product.icon}"></i>`;
        document.getElementById('requestProductName').textContent = product.nombre;
        document.getElementById('requestProductCode').textContent = `Código: ${product.codigo}`;
        document.getElementById('requestQuantity').value = 1;
        document.getElementById('requestNotes').value = '';
        
        // Set product ID in hidden field
        document.getElementById('requestProductId').value = productId;
        
        // Reset destinatario if exists (for Agente users)
        const destinatarioSelect = document.getElementById('requestDestinatario');
        if (destinatarioSelect) {
            destinatarioSelect.value = '';
            // Reset hidden solicitado_id field for Agente
            document.getElementById('requestSolicitadoId').value = '';
        }
        
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
    
    // Update solicitado_id when Agente selects a destinatario
    function updateSolicitadoId() {
        const destinatarioSelect = document.getElementById('requestDestinatario');
        const solicitadoIdField = document.getElementById('requestSolicitadoId');
        if (destinatarioSelect && solicitadoIdField) {
            solicitadoIdField.value = destinatarioSelect.value;
        }
    }

    function closeRequestModal() {
        const modal = document.getElementById('requestModal');
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
        
        if (userType === 'Administrador') {
            stockSection.classList.remove('hidden');
            minStockSection.classList.remove('hidden');
            document.getElementById('detailProductStock').textContent = product.stock;
            document.getElementById('detailProductMinStock').textContent = product.stock_minimo;
            adminInfo.classList.remove('hidden');
            userInfo.classList.add('hidden');
            requestBtn.classList.add('hidden');
        } else {
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
</style>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>