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

    <!-- Products Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table id="productosTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Imagen</th>
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
                    <tr id="product-row-<?php echo $producto['id']; ?>" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center overflow-hidden border border-gray-200">
                                <?php if (!empty($producto['imagen'])): ?>
                                    <img src="<?php echo URL_BASE; ?>/public/assets/img/products/<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <i class="fa-solid <?php echo $producto['icon']; ?> text-gray-400 text-lg"></i>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="product-code font-mono text-blue-600 bg-blue-50 px-2.5 py-1 rounded text-xs font-medium border border-blue-100">
                                <?php echo $producto['codigo']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center text-gray-500">
                                    <i class="product-icon fa-solid <?php echo $producto['icon']; ?>"></i>
                                </div>
                                <div>
                                    <div class="product-name font-semibold text-gray-900"><?php echo $producto['nombre']; ?></div>
                                    <div class="product-desc text-xs text-gray-500"><?php echo $producto['descripcion']; ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="product-category px-6 py-4"><?php echo $producto['categoria']; ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1.5">
                                <div class="product-status-dot w-2 h-2 rounded-full <?php echo $dotColorClass; ?>"></div>
                                <span class="product-status-text font-medium <?php echo $statusTextColor; ?>">
                                    <?php echo $producto['estado']; ?>
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($tipo_usuario === 'Administrador' || ($tipo_usuario === 'Sucursal' && $esSucursal)): ?>
                                <span class="product-stock px-2.5 py-0.5 rounded-full text-xs font-semibold border <?php echo $badgeColorClass; ?>">
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
                                    <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors border border-transparent hover:border-blue-100" onclick="openEditModal('<?php echo $producto['id']; ?>')">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                <?php endif; ?>
                                <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors border border-transparent hover:border-gray-200" onclick="openDetailModal('<?php echo $producto['id']; ?>')">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                                <?php if ($tipo_usuario === 'Administrador' || ($tipo_usuario === 'Sucursal' && $esSucursal)): ?>
                                    <button class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors border border-transparent hover:border-red-100" onclick="openDeleteModal('<?php echo $producto['id']; ?>')">
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
            
            <form id="requestForm" class="space-y-4" >
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
                    <textarea id="requestNotes" name="notas" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow resize-none"></textarea>
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
            <button type="submit" form="requestForm" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm" id="sendRequestBtn">
                <i class="fa-solid fa-paper-plane mr-2"></i>Enviar Solicitud
            </button>
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl transform transition-all h-full md:h-auto overflow-y-auto max-h-[90vh]">
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Left Column: Image -->
                <div class="md:col-span-1">
                    <div class="w-full aspect-square rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center overflow-hidden relative">
                         <img id="detailProductImage" src="" class="hidden w-full h-full object-cover">
                         <div id="detailProductIconPlaceholder" class="flex flex-col items-center justify-center text-gray-400">
                            <div id="detailProductIcon" class="text-5xl mb-2"></div>
                            <span class="text-sm">Sin imagen</span>
                         </div>
                    </div>
                </div>

                <!-- Right Column: Details -->
                <div class="md:col-span-2 space-y-6">
                    <!-- Header Info -->
                    <div class="flex items-start justify-between border-b border-gray-100 pb-4">
                        <div>
                            <h4 id="detailProductName" class="text-2xl font-bold text-gray-900 mb-1">Producto</h4>
                            <span id="detailProductCode" class="inline-block font-mono text-sm text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded border border-blue-100">
                                SKU: ---
                            </span>
                        </div>
                        <span id="detailProductStatus" class="px-3 py-1 rounded-full text-sm font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Activo
                        </span>
                    </div>

                    <div class="prose prose-sm text-gray-600">
                        <p id="detailProductDescription">Descripción del producto</p>
                    </div>

                    <!-- Details Grid -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Categoría</span>
                            <p id="detailProductCategory" class="text-lg font-medium text-gray-900 mt-1">---</p>
                        </div>
                        <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Precio</span>
                            <p id="detailProductPrice" class="text-lg font-bold text-blue-600 mt-1">$0.00</p>
                        </div>
                        <div id="detailStockSection" class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock Actual</span>
                            <p id="detailProductStock" class="text-lg font-medium text-gray-900 mt-1">---</p>
                        </div>
                        <div id="detailMinStockSection" class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Stock Mínimo</span>
                            <p id="detailProductMinStock" class="text-lg font-medium text-gray-900 mt-1">---</p>
                        </div>
                    </div>

                    <!-- Additional Info / Actions -->
                     <div id="detailAdminInfo" class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-sm text-amber-800">
                        <i class="fa-solid fa-shield-halved mr-2"></i> Vista de Administrador
                    </div>
                    
                    <button id="detailRequestBtn" class="hidden w-full py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow-sm transition-colors">
                        <i class="fa-solid fa-paper-plane mr-2"></i>Solicitar Producto
                    </button>
                    
                     <div id="detailUserInfo" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4 mt-2">
                         <div class="flex gap-2">
                             <i class="fa-solid fa-info-circle text-blue-600 mt-0.5"></i>
                             <div>
                                 <p class="text-sm font-semibold text-blue-900 mb-1">Información</p>
                                 <p class="text-sm text-blue-700">
                                     Utiliza este botón para solicitar unidades adicionales de este producto a la administración o central.
                                 </p>
                             </div>
                         </div>
                     </div>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
            <button type="button" onclick="closeDetailModal()" class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Cerrar
            </button>
        </div>
    </div>
</div>             
            </button>
            <button type="button" id="detailRequestBtn" class="hidden px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
                <i class="fa-solid fa-shopping-cart mr-2"></i>Solicitar Producto
            </button>
        </div>
    </div>
</div>


<!-- Edit Product Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl transform transition-all h-full md:h-auto overflow-y-auto max-h-[90vh]">
        <form id="editForm" onsubmit="handleEditSubmit(event)" enctype="multipart/form-data">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <i class="fa-solid fa-pen text-blue-600"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Editar Producto</h3>
                        <p class="text-sm text-gray-500">Actualiza la información del producto</p>
                    </div>
                </div>
                <button type="button" onclick="closeEditModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <i class="fa-solid fa-times"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Left Column: Image Upload -->
                    <div class="md:col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Imagen del Producto</label>
                        <div class="relative group cursor-pointer" onclick="document.getElementById('editImagenInput').click()">
                            <div id="editImagePreview" class="w-full aspect-square rounded-xl bg-gray-50 border-2 border-dashed border-gray-300 flex flex-col items-center justify-center overflow-hidden hover:bg-gray-100 hover:border-blue-400 transition-colors">
                                <!-- Default Icon (Hidden if image exists) -->
                                <div id="editImagePlaceholder" class="flex flex-col items-center justify-center text-gray-400">
                                    <i class="fa-solid fa-cloud-arrow-up text-4xl mb-2"></i>
                                    <span class="text-sm font-medium">Subir imagen</span>
                                    <span class="text-xs text-gray-400 mt-1">Click para seleccionar</span>
                                </div>
                                <!-- Image Preview (Shown if image exists) -->
                                <img id="editImageElement" src="" class="hidden w-full h-full object-cover">
                            </div>
                            
                            <!-- Overlay on Hover -->
                            <div class="absolute inset-0 bg-black bg-opacity-40 rounded-xl flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="text-white font-medium text-sm"><i class="fa-solid fa-camera mr-2"></i>Cambiar</span>
                            </div>
                        </div>
                        <input type="file" id="editImagenInput" name="imagen" accept="image/*" class="hidden" onchange="previewEditImage(this)">
                        <p class="text-xs text-gray-500 mt-2 text-center">Formatos: JPG, PNG, WEBP (Max 5MB)</p>
                    </div>

                    <!-- Right Column: Form Fields -->
                    <div class="md:col-span-2 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="editNombre" class="block text-sm font-semibold text-gray-700 mb-1">Nombre</label>
                                <input type="text" id="editNombre" name="nombre" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                            </div>
                            <div>
                                <label for="editCodigo" class="block text-sm font-semibold text-gray-700 mb-1">Código (SKU)</label>
                                <input type="text" id="editCodigo" name="codigo" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="editCategoria" class="block text-sm font-semibold text-gray-700 mb-1">Categoría</label>
                                <select id="editCategoria" name="categoria" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow bg-white">
                                    <option value="">Seleccionar categoría</option>
                                    <option value="Electrónica">Electrónica</option>
                                    <option value="Oficina">Oficina</option>
                                    <option value="Mobiliario">Mobiliario</option>
                                    <option value="Papelería">Papelería</option>
                                    <option value="Tecnología">Tecnología</option>
                                    <option value="Otros">Otros</option>
                                </select>
                            </div>
                            <div>
                                <label for="editPrecio" class="block text-sm font-semibold text-gray-700 mb-1">Precio</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                                    <input type="number" id="editPrecio" name="precio" step="0.01" min="0" required
                                        class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="editStock" class="block text-sm font-semibold text-gray-700 mb-1">Stock Actual</label>
                                <input type="number" id="editStock" name="stock" min="0" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                            </div>
                            <div>
                                <label for="editStockMinimo" class="block text-sm font-semibold text-gray-700 mb-1">Stock Mínimo</label>
                                <input type="number" id="editStockMinimo" name="stock_minimo" min="0" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow">
                            </div>
                        </div>

                        <div>
                            <label for="editDescripcion" class="block text-sm font-semibold text-gray-700 mb-1">Descripción</label>
                            <textarea id="editDescripcion" name="descripcion" rows="3"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none transition-shadow resize-none"></textarea>
                        </div>

                        <div>
                            <label for="editActivo" class="flex items-center gap-2 cursor-pointer p-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <input type="checkbox" id="editActivo" name="activo" value="1" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm font-medium text-gray-700">Producto Activo</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm" id="submitEditBtn">
                    Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-sm transform transition-all">
        <div class="p-6 text-center">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                <i class="fa-solid fa-trash-can text-red-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">¿Eliminar Producto?</h3>
            <p class="text-sm text-gray-500 mb-6">¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.</p>
            
            <div class="flex items-center justify-center gap-3">
                <button type="button" onclick="closeDeleteModal()" class="px-4 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button id="confirmDeleteBtn" type="button" class="px-4 py-2.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors shadow-sm">
                    Sí, Eliminar
                </button>
            </div>
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
    
    // Image Preview for Edit Modal
    function previewEditImage(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                const img = document.getElementById('editImageElement');
                const placeholder = document.getElementById('editImagePlaceholder');
                
                img.src = e.target.result;
                img.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Function to set up image in modals (Edit/Detail)
    function setupModalImage(containerId, imgId, placeholderId, product) {
        const img = document.getElementById(imgId);
        const placeholder = document.getElementById(placeholderId);
        
        if (product.imagen) {
            img.src = `${baseUrl}/public/assets/img/products/${product.imagen}`;
            img.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            img.src = '';
            img.classList.add('hidden');
            placeholder.classList.remove('hidden');
            
            // Set icon in placeholder if available
            const iconContainer = placeholder.querySelector('div, i'); 
            if (iconContainer) {
                 // Try to find the inner icon element or just replace content
                 // For detail modal, we have a specific structure
                 const detailIcon = document.getElementById('detailProductIcon');
                 if (detailIcon && placeholderId === 'detailProductIconPlaceholder') {
                     detailIcon.innerHTML = `<i class="fa-solid ${product.icon}"></i>`;
                 }
            }
        }
    }

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
    
    document.getElementById('requestForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const errorDiv = document.getElementById('requestErrorMessage');
        errorDiv.classList.add('hidden');
        
        const submitBtn = document.getElementById('sendRequestBtn');
        const originalBtnText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i>Enviando...';
        
        const formData = new FormData(this);
        
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
                window.location.href = data.redirect || `${baseUrl}/requests.php`;
            } else {
                document.getElementById('requestErrorText').textContent = data.message || 'Error al crear la solicitud';
                errorDiv.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('requestErrorText').textContent = 'Error de conexión. Por favor, intenta de nuevo.';
            errorDiv.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        });
    });
    
    // Detail Modal Functions
    function openDetailModal(productId) {
        const product = productsData.find(p => p.id == productId);
        if (!product) return;
        
        // Populate detail modal
        document.getElementById('detailProductName').textContent = product.nombre;
        document.getElementById('detailProductDescription').innerHTML = product.descripcion.replace(/\n/g, '<br>');
        document.getElementById('detailProductCode').textContent = `SKU: ${product.codigo}`;
        document.getElementById('detailProductCategory').textContent = product.categoria;
        document.getElementById('detailProductPrice').textContent = product.precio;
        
        // Image setup
        setupModalImage('detailModal', 'detailProductImage', 'detailProductIconPlaceholder', product);

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
        
        const modal = document.getElementById('detailModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
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
        
        if (innerDiv) {
            innerDiv.style.transform = 'scale(0.95)';
            innerDiv.style.opacity = '0';
        }
        modal.style.opacity = '0';
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 200);
    }
    
    // Edit Modal Functions
    function openEditModal(productId) {
        const product = productsData.find(p => p.id == productId);
        if (!product) return;

        // Populate fields
        document.getElementById('editNombre').value = product.nombre;
        document.getElementById('editCodigo').value = product.codigo;
        document.getElementById('editCategoria').value = product.categoria;
        
        const priceValue = product.precio.replace('$', '').replace(/,/g, '');
        document.getElementById('editPrecio').value = priceValue;
        
        document.getElementById('editStock').value = product.stock;
        document.getElementById('editStockMinimo').value = product.stock_minimo;
        document.getElementById('editDescripcion').value = product.descripcion;
        
        const isActive = product.status_class !== 'inactive';
        document.getElementById('editActivo').checked = isActive;

        // Reset & Setup Image
        document.getElementById('editImagenInput').value = '';
        setupModalImage('editModal', 'editImageElement', 'editImagePlaceholder', product);

        // Update Form Action
        const form = document.getElementById('editForm');
        form.action = `${baseUrl}/products.php/edit/${productId}`;

        const modal = document.getElementById('editModal');
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            modal.style.opacity = '1';
            const innerDiv = modal.querySelector('div');
            if (innerDiv) {
                innerDiv.style.transform = 'scale(1)';
                innerDiv.style.opacity = '1';
            }
        }, 10);
    }

    function closeEditModal() {
        const modal = document.getElementById('editModal');
        const innerDiv = modal.querySelector('div');
        
        if (innerDiv) {
            innerDiv.style.transform = 'scale(0.95)';
            innerDiv.style.opacity = '0';
        }
        modal.style.opacity = '0';
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 200);
    }

    // Delete Modal Functions
    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        const innerDiv = modal.querySelector('div');
        
        if (innerDiv) {
            innerDiv.style.transform = 'scale(0.95)';
            innerDiv.style.opacity = '0';
        }
        modal.style.opacity = '0';
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }, 200);
    }
    
    async function handleEditSubmit(e) {
        e.preventDefault();
        const submitBtn = document.getElementById('submitEditBtn');
        const form = document.getElementById('editForm');
        const formData = new FormData(form);
        const originalText = submitBtn.innerText;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Guardando...';
        
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                const product = data.product;
                
                // Update local data
                const index = productsData.findIndex(p => p.id == product.id);
                if (index !== -1) {
                    productsData[index] = product;
                }
                
                const row = document.getElementById(`product-row-${product.id}`);
                if (row) {
                     // Reload page to show new image properly or update via JS?
                     // Updating image via JS in the table:
                     const imgContainer = row.querySelector('td:first-child div');
                     if (product.imagen) {
                         imgContainer.innerHTML = `<img src="${baseUrl}/public/assets/img/products/${product.imagen}" alt="${product.nombre}" class="w-full h-full object-cover">`;
                     } else {
                         imgContainer.innerHTML = `<i class="fa-solid ${product.icon} text-gray-400 text-lg"></i>`;
                     }

                    row.querySelector('.product-code').textContent = product.codigo;
                    row.querySelector('.product-name').textContent = product.nombre;
                    // ... other fields updates ...
                    row.querySelector('.product-category').textContent = product.categoria;
                    row.querySelector('.product-stock').textContent = product.stock;
                    
                    // Status
                    const statusTextElem = row.querySelector('.product-status-text');
                    statusTextElem.textContent = product.estado;
                    statusTextElem.className = 'product-status-text font-medium';
                    if (product.status_class === 'active') statusTextElem.classList.add('text-emerald-700');
                    else if (product.status_class === 'warning') statusTextElem.classList.add('text-amber-700');
                    else statusTextElem.classList.add('text-gray-500');
                    
                    const statusDot = row.querySelector('.product-status-dot');
                    statusDot.className = 'product-status-dot w-2 h-2 rounded-full';
                    if (product.status_class === 'active') statusDot.classList.add('bg-emerald-500');
                    else if (product.status_class === 'warning') statusDot.classList.add('bg-amber-500');
                    else statusDot.classList.add('bg-gray-400');
                }
                
                submitBtn.innerHTML = '<i class="fa fa-check"></i> ¡Guardado!';
                submitBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');
                submitBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                
                setTimeout(() => {
                    closeEditModal();
                    setTimeout(() => {
                         submitBtn.disabled = false;
                         submitBtn.innerHTML = originalText;
                         submitBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                         submitBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                    }, 500);
                }, 1000);
                
                return; 
            } else {            
                alert(data.message || 'Error al actualizar el producto');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión');
        } finally {
            setTimeout(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }, 1000);
        }
    }

    async function deleteProduct(productId) {
        if (!productId) return;
        
        const btn = document.getElementById('confirmDeleteBtn');
        const originalText = btn.innerText;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Eliminando...';
        
        try {
            const response = await fetch(`${baseUrl}/products.php/delete/${productId}`, {
                method: 'POST'
            });
            
            const data = await response.json();
            
            if (data.success) {
                const row = document.getElementById(`product-row-${productId}`);
                if (row) {
                    row.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(20px)';
                    setTimeout(() => row.remove(), 500);
                }
                
                const index = productsData.findIndex(p => p.id == productId);
                if (index !== -1) {
                    productsData.splice(index, 1);
                }
                
                closeDeleteModal();
            } else {
                alert(data.message || 'Error al eliminar el producto');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error de conexión');
        } finally {
            setTimeout(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }, 1000);
            closeDeleteModal();
        }
    }

    function openDeleteModal(productId) {
        const modal = document.getElementById('deleteModal');
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        confirmBtn.onclick = () => deleteProduct(productId);
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        setTimeout(() => {
            modal.style.opacity = '1';
            const innerDiv = modal.querySelector('div');
            if (innerDiv) {
                innerDiv.style.transform = 'scale(1)';
                innerDiv.style.opacity = '1';
            }
        }, 10);
    }
    
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const requestModal = document.getElementById('requestModal');
            const detailModal = document.getElementById('detailModal');
            const editModal = document.getElementById('editModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (!requestModal.classList.contains('hidden')) closeRequestModal();
            if (!detailModal.classList.contains('hidden')) closeDetailModal();
            if (!editModal.classList.contains('hidden')) closeEditModal();
            if (!deleteModal.classList.contains('hidden')) closeDeleteModal();
        }
    });    
</script>

<style>
    /* Modal animations */
    #requestModal,
    #detailModal,
    #editModal,
    #deleteModal {
        opacity: 0;
        transition: opacity 0.2s ease-out;
    }
    
    #requestModal > div,
    #detailModal > div,
    #editModal > div,
    #deleteModal > div {
        transform: scale(0.95);
        opacity: 0;
        transition: all 0.2s ease-out;
    }
    
    /* Smooth scrolling for modals */
    #requestModal,
    #detailModal,
    #editModal,
    #deleteModal {
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