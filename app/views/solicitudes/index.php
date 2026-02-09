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

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Solicitudes</h2>
            <p class="text-gray-500 text-sm">Gestiona tus solicitudes.</p>
        </div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <!-- Search handled by DataTables -->

    <!-- Requests Table -->
    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table id="solicitudesTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">ID Solicitud</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Origen</th>
                    <th scope="col" class="px-6 py-4 font-semibold tracking-wider">Destino</th>
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
                        'approved-modified' => 'bg-teal-50 text-teal-700 border-teal-200',
                        'transit' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'negotiation' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'rejected' => 'bg-red-50 text-red-700 border-red-200',
                        default => 'bg-gray-50 text-gray-600 border-gray-200',
                    };
                    $statusDot = match ($req['estado_class']) {
                        'pending' => 'bg-amber-500',
                        'approved' => 'bg-emerald-500',
                        'approved-modified' => 'bg-teal-500',
                        'transit' => 'bg-blue-500',
                        'negotiation' => 'bg-purple-500',
                        'rejected' => 'bg-red-500',
                        default => 'bg-gray-400',
                    };
                    $btnClass = match ($req['accion_class']) {
                        'blue' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-sm',
                        'amber' => 'bg-amber-600 hover:bg-amber-700 text-white shadow-sm',
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
<div id="actionModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
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
                
                <!-- Counter Offer Accepted Banner -->
                <div id="counterAcceptedBanner" class="hidden mb-4">
                    <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-3">
                        <div class="flex gap-2">
                            <i class="fa-solid fa-check-circle text-emerald-600 mt-0.5"></i>
                            <div class="flex-1">
                                <p class="text-sm font-semibold text-emerald-900">Contra-Oferta Aceptada</p>
                                <p class="text-xs text-emerald-700 mt-1" id="acceptedCounterNotes"></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="overflow-hidden border border-gray-200 rounded-lg mb-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">SKU</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Producto</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-32" id="cantidadHeader">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="modalItemsTable"></tbody>
                    </table>
                </div>
                
                <!-- Counter Offer Comparison (for negotiation) -->
                <div id="counterOfferComparison" class="hidden mb-6">
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-4">
                        <div class="flex gap-2 mb-3">
                            <i class="fa-solid fa-handshake text-purple-600 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-semibold text-purple-900">Contra-Oferta Recibida</p>
                                <p class="text-sm text-purple-700 mt-1" id="counterOfferNotes"></p>
                            </div>
                        </div>
                    </div>
                    <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center gap-2">
                        <i class="fa-solid fa-code-compare text-purple-500"></i> Comparación de Cantidades
                    </h4>
                    <div id="comparisonItems" class="space-y-3"></div>
                </div>

                <!-- Counter Offer Form (hidden by default) -->
                <div id="counterOfferForm" class="hidden mb-6">
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-4">
                        <div class="flex gap-2 mb-3">
                            <i class="fa-solid fa-pen-to-square text-amber-600 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-semibold text-amber-900">Modificar Cantidades</p>
                                <p class="text-sm text-amber-700 mt-1">Ingresa las nuevas cantidades para la contra-oferta. Los cambios serán enviados al solicitante para su revisión.</p>
                            </div>
                        </div>
                    </div>
                    <div id="counterOfferItems" class="space-y-3"></div>
                    <div class="mt-4">
                        <label for="counterOfferNotes" class="block text-sm font-semibold text-gray-700 mb-2">
                            Notas de la Contra-Oferta <span class="text-gray-400 font-normal">(Opcional)</span>
                        </label>
                        <textarea id="counterOfferNotes" rows="3"
                                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 outline-none transition-shadow resize-none"
                                  placeholder="Explica los motivos de la modificación..."></textarea>
                    </div>
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

<style>
    /* DataTables Custom Styling */
    #solicitudesTable_wrapper .dataTables_filter input {
        padding: 0.625rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin-left: 0.5rem;
    }
    
    #solicitudesTable_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: #3b82f6;
        ring: 2px;
        ring-color: rgba(59, 130, 246, 0.2);
    }
    
    #solicitudesTable_wrapper .dataTables_length select {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin: 0 0.5rem;
    }
    
    #solicitudesTable_wrapper .dataTables_info,
    #solicitudesTable_wrapper .dataTables_paginate {
        padding-top: 1.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    #solicitudesTable_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #4b5563;
        background: white;
    }
    
    #solicitudesTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #2563eb;
    }
    
    #solicitudesTable_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    #solicitudesTable_wrapper .dataTables_paginate .paginate_button.disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
</style>

<script>
    const requestsData = <?php echo json_encode($solicitudes); ?>;
    const currentUserRole = "<?php echo $currentUserRole; ?>";
    const baseUrl = "<?php echo URL_BASE; ?>";

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

        // Hide counter offer form and comparison initially
        document.getElementById('counterOfferForm').classList.add('hidden');
        document.getElementById('counterOfferComparison').classList.add('hidden');
        document.getElementById('counterAcceptedBanner').classList.add('hidden');
        
        // Check if this request has counter-offer data
        const isNegotiation = req.estado.toLowerCase().includes('negociaci') && req.counter_offer_items && req.counter_offer_items.length > 0;
        const wasCounterOffered = req.was_counter_offered;
        
        if (isNegotiation && req.counter_offer_items) {
            // Show comparison view for negotiation
            document.getElementById('counterOfferComparison').classList.remove('hidden');
            
            // Update header and show counter-offer notes
            document.getElementById('cantidadHeader').textContent = 'Solicitado';
            const notesEl = document.getElementById('counterOfferNotes');
            notesEl.textContent = req.counter_offer_notes || 'El destinatario ha propuesto modificaciones a las cantidades solicitadas.';
            
            // Build comparison items
            const comparisonDiv = document.getElementById('comparisonItems');
            comparisonDiv.innerHTML = '';
            
            req.items_detail.forEach((item, index) => {
                const counterItem = req.counter_offer_items[index];
                const diferencia = counterItem.cantidad - item.cantidad;
                const diferenciaClass = diferencia < 0 ? 'text-red-600' : 'text-emerald-600';
                const diferenciaIcon = diferencia < 0 ? 'fa-arrow-down' : 'fa-arrow-up';
                
                comparisonDiv.innerHTML += `
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 text-sm">${item.nombre}</p>
                                <p class="text-xs text-gray-500 mt-0.5">SKU: ${item.sku}</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 mb-1">Solicitaste</p>
                                    <p class="text-lg font-bold text-gray-900">${item.cantidad}</p>
                                </div>
                                <div class="flex items-center gap-1 ${diferenciaClass}">
                                    <i class="fa-solid ${diferenciaIcon} text-sm"></i>
                                    <span class="font-semibold">${Math.abs(diferencia)}</span>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 mb-1">Ofrecen</p>
                                    <p class="text-lg font-bold text-purple-600">${counterItem.cantidad}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            // Populate original items table (read-only)
            const tbody = document.getElementById('modalItemsTable');
            tbody.innerHTML = '';
            req.items_detail.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-500">${item.sku}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${item.nombre}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                            <span class="font-medium text-gray-900">${item.cantidad}</span>
                        </td>
                    </tr>`;
            });
        } else {
            // Normal view (not negotiation)
            document.getElementById('cantidadHeader').textContent = 'Cantidad';
            
            // Check if this request was accepted with a counter-offer
            if (wasCounterOffered) {
                const banner = document.getElementById('counterAcceptedBanner');
                banner.classList.remove('hidden');
                const notesEl = document.getElementById('acceptedCounterNotes');
                notesEl.textContent = req.counter_offer_notes || 'Las cantidades mostradas reflejan la contra-oferta aceptada.';
            }
            
            // Populate items table (always read-only initially)
            const tbody = document.getElementById('modalItemsTable');
            tbody.innerHTML = '';
            req.items_detail.forEach(item => {
                tbody.innerHTML += `
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap text-sm font-mono text-gray-500">${item.sku}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">${item.nombre}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-right">
                            <span class="font-medium text-gray-900">${item.cantidad}</span>
                        </td>
                    </tr>`;
            });
        }

        // Determine which buttons to show based on permissions
        const actionsDiv = document.getElementById('modalActions');
        const permissionInfo = document.getElementById('permissionInfo');
        let buttons = '';
        
        if (isNegotiation) {
            // Check if user can negotiate (original requester)
            if (req.puede_negociar) {
                // User is original requester viewing counter-offer - can accept/reject
                permissionInfo.innerHTML = `
                    <div class="flex gap-2 p-3 rounded-lg bg-purple-50 text-purple-700 border border-purple-200">
                        <i class="fa-solid fa-handshake mt-0.5"></i>
                        <div>
                            <p class="text-sm font-semibold mb-1">Contra-Oferta Recibida</p>
                            <p class="text-sm">El destinatario ha propuesto cantidades diferentes. Puedes aceptar la contra-oferta o rechazarla.</p>
                        </div>
                    </div>`;
                permissionInfo.classList.remove('hidden');
                
                buttons = `
                    <button onclick="performAction('reject_counter', '${req.counter_offer_id}')" type="button" 
                            class="flex-1 sm:flex-none justify-center px-4 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg border border-red-200 hover:bg-red-100 transition-colors">
                        <i class="fa-solid fa-times mr-2"></i>Rechazar Contra-Oferta
                    </button>
                    <button onclick="performAction('accept_counter', '${req.counter_offer_id}')" type="button" 
                            class="flex-1 sm:flex-none justify-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 shadow-sm transition-colors">
                        <i class="fa-solid fa-check mr-2"></i>Aceptar Contra-Oferta
                    </button>`;
            } else {
                // Other users viewing the negotiation - read-only
                permissionInfo.innerHTML = `
                    <div class="flex gap-2 p-3 rounded-lg bg-purple-50 text-purple-700 border border-purple-200">
                        <i class="fa-solid fa-handshake mt-0.5"></i>
                        <div>
                            <p class="text-sm font-semibold mb-1">En Negociación</p>
                            <p class="text-sm">Esta solicitud tiene una contra-oferta pendiente de respuesta.</p>
                        </div>
                    </div>`;
                permissionInfo.classList.remove('hidden');
                
                buttons = `
                    <button type="button" onclick="closeModal()" 
                            class="flex-1 sm:flex-none justify-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                        Cerrar
                    </button>`;
            }
        } else if (req.puede_actuar && req.estado === 'Pendiente') {
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
                <button onclick="showCounterOfferForm('${req.id}')" type="button" 
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
            document.getElementById('counterOfferForm').classList.add('hidden');
        }, 300);
    }
    
    function showCounterOfferForm(requestId) {
        const req = requestsData.find(r => r.id === requestId);
        if (!req) return;
        
        // Hide action buttons and show counter offer form
        const counterOfferForm = document.getElementById('counterOfferForm');
        const counterOfferItems = document.getElementById('counterOfferItems');
        const actionsDiv = document.getElementById('modalActions');
        
        // Build counter offer input fields
        counterOfferItems.innerHTML = '';
        req.items_detail.forEach((item, index) => {
            counterOfferItems.innerHTML += `
                <div class="bg-white border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900 text-sm">${item.nombre}</p>
                            <p class="text-xs text-gray-500 mt-0.5">SKU: ${item.sku} • Cantidad solicitada: <span class="font-semibold">${item.cantidad}</span></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <label class="text-sm font-medium text-gray-700 whitespace-nowrap">Nueva cantidad:</label>
                            <input type="number" 
                                   value="${item.cantidad}" 
                                   min="1" 
                                   max="${item.cantidad}"
                                   class="w-24 px-3 py-2 text-right text-sm font-semibold border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500" 
                                   data-item-id="${item.id}"
                                   data-item-index="${index}"
                                   data-original-qty="${item.cantidad}">
                        </div>
                    </div>
                </div>
            `;
        });
        
        // Show the form
        counterOfferForm.classList.remove('hidden');
        
        // Update buttons
        actionsDiv.innerHTML = `
            <button type="button" onclick="cancelCounterOffer('${requestId}')" 
                    class="flex-1 sm:flex-none justify-center px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
                Cancelar
            </button>
            <button type="button" onclick="submitCounterOffer('${requestId}')" 
                    class="flex-1 sm:flex-none justify-center px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 shadow-sm transition-colors">
                <i class="fa-solid fa-paper-plane mr-2"></i>Enviar Contra-Oferta
            </button>
        `;
    }
    
    function cancelCounterOffer(requestId) {
        // Hide counter offer form and restore original buttons
        document.getElementById('counterOfferForm').classList.add('hidden');
        
        // Re-open modal to restore buttons
        const req = requestsData.find(r => r.id === requestId);
        if (!req) return;
        
        const actionsDiv = document.getElementById('modalActions');
        actionsDiv.innerHTML = `
            <button onclick="performAction('reject', '${req.id}')" type="button" 
                    class="flex-1 sm:flex-none justify-center px-4 py-2 bg-red-50 text-red-700 text-sm font-medium rounded-lg border border-red-200 hover:bg-red-100 transition-colors">
                <i class="fa-solid fa-times mr-2"></i>Rechazar
            </button>
            <button onclick="showCounterOfferForm('${req.id}')" type="button" 
                    class="flex-1 sm:flex-none justify-center px-4 py-2 bg-amber-50 text-amber-700 text-sm font-medium rounded-lg border border-amber-200 hover:bg-amber-100 transition-colors">
                <i class="fa-solid fa-pen mr-2"></i>Modificar
            </button>
            <button onclick="performAction('approve', '${req.id}')" type="button" 
                    class="flex-1 sm:flex-none justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 shadow-sm transition-colors">
                <i class="fa-solid fa-check mr-2"></i>Aprobar Solicitud
            </button>
        `;
    }
    
    function submitCounterOffer(requestId) {
        const req = requestsData.find(r => r.id === requestId);
        if (!req) return;
        
        const msgDiv = document.getElementById('actionMessage');
        const actionsDiv = document.getElementById('modalActions');
        
        // Hide any previous messages
        msgDiv.classList.add('hidden');
        
        // Get counter offer quantities
        const inputs = document.querySelectorAll('#counterOfferItems input[type="number"]');
        const formData = new FormData();
        
        let hasChanges = false;
        inputs.forEach(input => {
            const originalQty = parseInt(input.dataset.originalQty);
            const newQty = parseInt(input.value);
            const itemIndex = parseInt(input.dataset.itemIndex);
            
            if (newQty !== originalQty) {
                hasChanges = true;
            }
            
            formData.append('producto_id[]', req.items_detail[itemIndex].id);
            formData.append('cantidad[]', newQty);
            formData.append('observaciones[]', req.items_detail[itemIndex].observaciones || '');
        });
        
        if (!hasChanges) {
            msgDiv.classList.add('bg-amber-50', 'text-amber-700', 'border-amber-200');
            msgDiv.innerHTML = '<div class="flex gap-2"><i class="fa-solid fa-info-circle"></i><span>No se detectaron cambios en las cantidades.</span></div>';
            msgDiv.classList.remove('hidden');
            return;
        }
        
        // Get notes
        const notes = document.getElementById('counterOfferNotes').value.trim();
        formData.append('counter_offer_notes', notes || 'Modificación de cantidades solicitadas');
        
        // Show loading state
        actionsDiv.innerHTML = `
            <div class="flex items-center justify-center gap-3 w-full py-2">
                <i class="fa-solid fa-spinner fa-spin text-amber-600 text-lg"></i>
                <span class="text-gray-700 font-medium">Enviando contra-oferta...</span>
            </div>
        `;
        
        // Submit to server
        fetch(`${baseUrl}/requests.php/modificar/${requestId}`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = `${baseUrl}/requests.php`;
            } else {
                msgDiv.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
                msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                msgDiv.innerHTML = `<div class="flex gap-2"><i class="fa-solid fa-circle-xmark"></i><span>${data.message || 'Error al crear la contra-oferta.'}</span></div>`;
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            msgDiv.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
            msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
            msgDiv.innerHTML = '<div class="flex gap-2"><i class="fa-solid fa-circle-xmark"></i><span>Error al crear la contra-oferta.</span></div>';
            location.reload();
        });
    }

    function performAction(action, requestId) {
        const msgDiv = document.getElementById('actionMessage');
        const actionsDiv = document.getElementById('modalActions');
        
        // Hide any previous messages
        msgDiv.classList.add('hidden');

        // Show loading state in button area
        actionsDiv.innerHTML = `
            <div class="flex items-center justify-center gap-3 w-full py-2">
                <i class="fa-solid fa-spinner fa-spin text-blue-600 text-lg"></i>
                <span class="text-gray-700 font-medium">Cargando...</span>
            </div>
        `;

        if (action === 'approve') {
            fetch(`${baseUrl}/requests.php/aprobar/${requestId}`, { 
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {

                console.log('Response:', data);

                if (data.success) {
                    // Success - reload to show message
                    window.location.href = `${baseUrl}/requests.php`;
                } else {
                    // Show error in modal
                    msgDiv.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
                    msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                    msgDiv.innerHTML = `<div class="flex gap-2"><i class="fa-solid fa-circle-xmark"></i><span>${data.message || 'Error al aprobar la solicitud.'}</span></div>`;
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                msgDiv.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
                msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                msgDiv.innerHTML = '<div class="flex gap-2"><i class="fa-solid fa-circle-xmark"></i><span>Error al aprobar la solicitud.</span></div>';
                location.reload();
            });
            
        } else if (action === 'reject') {
            // Llamada real al servidor
            fetch(`${baseUrl}/requests.php/rechazar/${requestId}`, { 
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Success - reload to show message
                    window.location.href = `${baseUrl}/requests.php`;
                } else {
                    // Show error in modal
                    msgDiv.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
                    msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                    msgDiv.innerHTML = `<div class="flex gap-2"><i class="fa-solid fa-circle-xmark"></i><span>${data.message || 'Error al rechazar la solicitud.'}</span></div>`;
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                msgDiv.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
                msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                msgDiv.innerHTML = '<div class="flex gap-2"><i class="fa-solid fa-circle-xmark"></i><span>Error al rechazar la solicitud.</span></div>';
                location.reload();
            });
            
        } else if (action === 'accept_counter') {
            // Accept counter-offer
            fetch(`${baseUrl}/requests.php/aceptar_contraoferta/${requestId}`, { 
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `${baseUrl}/requests.php`;
                } else {
                    msgDiv.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
                    msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                    msgDiv.innerHTML = `<div class="flex gap-2"><i class="fa-solid fa-circle-xmark"></i><span>${data.message || 'Error al aceptar la contra-oferta.'}</span></div>`;
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                msgDiv.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
                msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                msgDiv.innerHTML = '<div class="flex gap-2"><i class="fa-solid fa-circle-xmark"></i><span>Error al aceptar la contra-oferta.</span></div>';
                location.reload();
            });
            
        } else if (action === 'reject_counter') {
            // Reject counter-offer
            fetch(`${baseUrl}/requests.php/rechazar_contraoferta/${requestId}`, { 
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `${baseUrl}/requests.php`;
                } else {
                    msgDiv.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
                    msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                    msgDiv.innerHTML = `<div class="flex gap-2"><i class="fa-solid fa-circle-xmark"></i><span>${data.message || 'Error al rechazar la contra-oferta.'}</span></div>`;
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                msgDiv.classList.remove('hidden', 'bg-blue-50', 'text-blue-700', 'border-blue-200');
                msgDiv.classList.add('bg-red-50', 'text-red-700', 'border-red-200');
                msgDiv.innerHTML = '<div class="flex gap-2"><i class="fa-solid fa-circle-xmark"></i><span>Error al rechazar la contra-oferta.</span></div>';
                location.reload();
            });
            
        }
    }

    // Close modal when clicking backdrop
    backdrop.addEventListener('click', closeModal);
    
    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
    
    // Initialize DataTables
    $(document).ready(function() {
        $('#solicitudesTable').DataTable({
            responsive: true,
            language: {
                "decimal": "",
                "emptyTable": "No hay solicitudes disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ solicitudes",
                "infoEmpty": "Mostrando 0 a 0 de 0 solicitudes",
                "infoFiltered": "(filtrado de _MAX_ solicitudes totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ solicitudes",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron solicitudes coincidentes",
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
            order: [[3, 'desc']], // Sort by date descending by default
            columnDefs: [
                { orderable: false, targets: [7] } // Disable sorting on Actions column
            ]
        });
    });
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>