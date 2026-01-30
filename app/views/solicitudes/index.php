<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Transferencias</h2>
            <p class="text-gray-500 text-sm">Gestiona y aprueba transferencias de stock entre sucursales.</p>
        </div>
        <div>
            <?php if ($currentUserRole !== 'Administrador'): ?>
                <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-plus"></i> Nueva Solicitud
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <!-- Filters -->
    <div class="flex flex-col lg:flex-row justify-between gap-4 mb-6 bg-gray-50 p-4 rounded-lg border border-gray-100">
        <div class="relative flex-grow max-w-md">
            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
            <input type="text" placeholder="Buscar solicitud ID, sucursal..." class="w-full bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 block pl-9 p-2.5 placeholder-gray-400 focus:outline-none transition-shadow">
        </div>
        <div class="flex flex-wrap gap-3">
            <select class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 block p-2.5 focus:outline-none min-w-[160px]">
                <option value="">Estado: Todos</option>
                <option value="pending">Pendiente</option>
                <option value="approved">Aprobado</option>
                <option value="rejected">Rechazado</option>
                <option value="in_transit">En Tránsito</option>
            </select>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">ID Solicitud</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Sucursal Origen</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Sucursal Destino</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Fecha</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Items</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Prioridad</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Estado</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php foreach ($solicitudes as $req): ?>
                    <?php
                    $priorityClass = match ($req['prioridad_class']) {
                        'red' => 'bg-red-50 text-red-600 border-red-200',
                        default => 'bg-gray-50 text-gray-600 border-gray-200',
                    };
                    $statusClass = match ($req['estado_class']) {
                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'transit' => 'bg-blue-50 text-blue-700 border-blue-200',
                        default => 'bg-gray-50 text-gray-600 border-gray-200',
                    };
                    $statusDot = match ($req['estado_class']) {
                        'pending' => 'bg-amber-500',
                        'approved' => 'bg-emerald-500',
                        'transit' => 'bg-blue-500',
                        default => 'bg-gray-400',
                    };
                    $btnClass = match ($req['accion_class']) {
                        'blue' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm',
                        default => 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50',
                    };
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-blue-600 bg-blue-50 px-2.5 py-1 rounded text-xs font-medium border border-blue-100"><?php echo $req['id']; ?></span>
                        </td>
                        <td class="px-6 py-4 text-gray-900"><?php echo $req['origen']; ?></td>
                        <td class="px-6 py-4"><?php echo $req['destino']; ?></td>
                        <td class="px-6 py-4"><?php echo $req['fecha']; ?></td>
                        <td class="px-6 py-4"><?php echo $req['items_summary']; ?></td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border <?php echo $priorityClass; ?>">
                                <?php echo $req['prioridad']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium border flex items-center gap-1.5 w-fit <?php echo $statusClass; ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo $statusDot; ?>"></span> <?php echo $req['estado']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <button onclick="openModal('<?php echo $req['id']; ?>')" class="px-3 py-1.5 rounded-lg text-xs font-medium transition-all <?php echo $btnClass; ?>"><?php echo $req['accion']; ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="actionModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"></div>
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-3xl opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" id="modalPanel">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Solicitud #REQ-000</h3>
                    <p class="text-sm text-gray-500 mt-1">Detalles de la transferencia</p>
                </div>
                <button type="button" onclick="closeModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none bg-white rounded-lg p-1 hover:bg-gray-100 transition-colors">
                    <span class="sr-only">Cerrar</span>
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>
            <div class="px-6 py-6">
                <!-- Info Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 text-sm">
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="block text-gray-500 text-xs uppercase font-semibold mb-1">Solicitante</span>
                        <span class="font-medium text-gray-900" id="modalOrigin">Usuario</span>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="block text-gray-500 text-xs uppercase font-semibold mb-1">Destino</span>
                        <span class="font-medium text-gray-900" id="modalDest">Sucursal</span>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="block text-gray-500 text-xs uppercase font-semibold mb-1">Prioridad</span>
                        <span class="font-medium" id="modalPriority">Normal</span>
                    </div>
                    <div class="p-3 bg-gray-50 rounded-lg border border-gray-100">
                        <span class="block text-gray-500 text-xs uppercase font-semibold mb-1">Items</span>
                        <span class="font-medium text-gray-900" id="modalItemsCount">0</span>
                    </div>
                </div>

                <!-- Items Table -->
                <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-box-open text-blue-500"></i> Productos Solicitados
                </h4>
                <div class="overflow-hidden border border-gray-200 rounded-lg mb-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-32">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="modalItemsTable"></tbody>
                    </table>
                </div>

                <!-- Permission Info Banner -->
                <div id="permissionInfo" class="hidden mb-4"></div>

                <div id="actionMessage" class="hidden mb-4 p-3 rounded-lg text-sm border"></div>
            </div>

            <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row sm:justify-between items-center gap-3 border-t border-gray-100">
                <div class="text-xs text-gray-500 hidden sm:block">* Modificar cantidades requiere aprobación adicional.</div>
                <div class="flex gap-3 w-full sm:w-auto" id="modalActions">
                    <button type="button" onclick="closeModal()" class="flex-1 sm:flex-none justify-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const requestsData = <?php echo json_encode($solicitudes); ?>;
    const currentUserRole = "<?php echo $currentUserRole; ?>";

    const modal = document.getElementById('actionModal');
    const backdrop = document.getElementById('modalBackdrop');
    const panel = document.getElementById('modalPanel');

    function openModal(requestId) {
        const req = requestsData.find(r => r.id === requestId);
        if (!req) return;

        document.getElementById('modalTitle').textContent = `Solicitud #${req.id}`;
        document.getElementById('modalOrigin').textContent = req.origen;
        document.getElementById('modalDest').textContent = req.destino;
        document.getElementById('modalPriority').textContent = req.prioridad;
        document.getElementById('modalItemsCount').textContent = req.items_summary;

        // Populate items table
        const tbody = document.getElementById('modalItemsTable');
        tbody.innerHTML = '';
        req.items_detail.forEach(item => {
            // Solo permitir editar cantidades si el usuario puede actuar en esta solicitud
            const isEditable = req.puede_actuar && req.estado === 'Pendiente';
            const qtyInput = isEditable ?
                `<input type="number" value="${item.cantidad}" min="1" class="w-20 text-right text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 px-2 py-1" data-item-id="${item.id}">` :
                `<span class="font-medium text-gray-900">${item.cantidad}</span>`;

            tbody.innerHTML += `
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-500">${item.sku}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">${item.nombre}</td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right">${qtyInput}</td>
                </tr>`;
        });

        // Determine which buttons to show based on permissions
        const actionsDiv = document.getElementById('modalActions');
        const permissionInfo = document.getElementById('permissionInfo');
        let buttons = '';
        
        if (req.puede_actuar && req.estado === 'Pendiente') {
            // Usuario puede actuar en esta solicitud pendiente
            permissionInfo.innerHTML = `
                <div class="flex gap-2 p-3 rounded-lg bg-blue-50 text-blue-700 border border-blue-200">
                    <i class="fa-solid fa-shield-halved mt-0.5"></i>
                    <div>
                        <p class="text-sm font-semibold mb-1">Acciones Disponibles</p>
                        <p class="text-sm">Tienes permisos para aprobar, modificar o rechazar esta solicitud.</p>
                    </div>
                </div>`;
            permissionInfo.classList.remove('hidden');
            
            buttons = `
                <button onclick="performAction('reject', '${req.id}')" type="button" 
                        class="flex-1 sm:flex-none justify-center px-4 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg border border-red-200 hover:bg-red-100 transition-colors">
                    <i class="fa-solid fa-times mr-2"></i>Rechazar
                </button>
                <button onclick="performAction('modify', '${req.id}')" type="button" 
                        class="flex-1 sm:flex-none justify-center px-4 py-2 bg-amber-50 text-amber-700 text-sm font-medium rounded-lg border border-amber-200 hover:bg-amber-100 transition-colors">
                    <i class="fa-solid fa-pen mr-2"></i>Modificar
                </button>
                <button onclick="performAction('approve', '${req.id}')" type="button" 
                        class="flex-1 sm:flex-none justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 shadow-sm transition-colors">
                    <i class="fa-solid fa-check mr-2"></i>Aprobar Solicitud
                </button>`;
        } else {
            // Solo ver detalles, sin acciones
            if (req.estado !== 'Pendiente') {
                permissionInfo.innerHTML = `
                    <div class="flex gap-2 p-3 rounded-lg bg-gray-50 text-gray-700 border border-gray-200">
                        <i class="fa-solid fa-info-circle mt-0.5"></i>
                        <div>
                            <p class="text-sm font-semibold mb-1">Solicitud ${req.estado}</p>
                            <p class="text-sm">Esta solicitud ya ha sido procesada y no puede ser modificada.</p>
                        </div>
                    </div>`;
            } else {
                permissionInfo.innerHTML = `
                    <div class="flex gap-2 p-3 rounded-lg bg-amber-50 text-amber-700 border border-amber-200">
                        <i class="fa-solid fa-eye mt-0.5"></i>
                        <div>
                            <p class="text-sm font-semibold mb-1">Solo Lectura</p>
                            <p class="text-sm">Puedes ver los detalles de esta solicitud pero no tienes permisos para modificarla.</p>
                        </div>
                    </div>`;
            }
            permissionInfo.classList.remove('hidden');
            
            buttons = `
                <button type="button" onclick="closeModal()" 
                        class="flex-1 sm:flex-none justify-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                    Cerrar
                </button>`;
        }
        actionsDiv.innerHTML = buttons;

        // Show modal with animation
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        setTimeout(() => {
            backdrop.classList.remove('opacity-0');
            panel.classList.remove('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
            panel.classList.add('opacity-100', 'translate-y-0', 'sm:scale-100');
        }, 10);
    }

    function closeModal() {
        backdrop.classList.add('opacity-0');
        panel.classList.remove('opacity-100', 'translate-y-0', 'sm:scale-100');
        panel.classList.add('opacity-0', 'translate-y-4', 'sm:translate-y-0', 'sm:scale-95');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
            document.getElementById('actionMessage').classList.add('hidden');
        }, 300);
    }

    function performAction(action, requestId) {
        const msgDiv = document.getElementById('actionMessage');
        msgDiv.classList.remove('hidden', 'bg-emerald-50', 'text-emerald-700', 'border-emerald-200', 
                                 'bg-red-50', 'text-red-700', 'border-red-200', 
                                 'bg-amber-50', 'text-amber-700', 'border-amber-200');

        let text = "";
        let iconHtml = "";
        
        if (action === 'approve') {
            msgDiv.classList.add('bg-emerald-50', 'text-emerald-700', 'border-emerald-200');
            iconHtml = '<i class="fa-solid fa-circle-check"></i>';
            text = "Solicitud aprobada correctamente.";
            
            // En producción, hacer llamada AJAX al servidor
            // fetch(`/solicitudes/aprobar/${requestId}`, { method: 'POST' })...
            
        } else if (action === 'reject') {
            msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
            iconHtml = '<i class="fa-solid fa-circle-xmark"></i>';
            text = "Solicitud rechazada.";
            
            // En producción, hacer llamada AJAX al servidor
            // fetch(`/solicitudes/rechazar/${requestId}`, { method: 'POST' })...
            
        } else if (action === 'modify') {
            // Obtener las cantidades modificadas
            const inputs = document.querySelectorAll('#modalItemsTable input[type="number"]');
            const modifications = [];
            inputs.forEach(input => {
                modifications.push({
                    itemId: input.dataset.itemId,
                    newQuantity: input.value
                });
            });
            
            msgDiv.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200');
            iconHtml = '<i class="fa-solid fa-pen-to-square"></i>';
            text = "Cambios guardados correctamente.";
            
            // En producción, hacer llamada AJAX al servidor con modifications
            console.log('Modificaciones:', modifications);
        }

        msgDiv.innerHTML = `<div class="flex gap-2">${iconHtml}<span>${text}</span></div>`;
        
        // Cerrar modal después de mostrar mensaje
        setTimeout(() => {
            closeModal();
            // Recargar página para ver cambios
            // window.location.reload();
        }, 1500);
    }

    // Close modal when clicking backdrop
    backdrop.addEventListener('click', closeModal);
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>