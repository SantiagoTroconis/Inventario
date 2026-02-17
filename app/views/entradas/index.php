<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Entradas</h2>
            <p class="text-gray-500 text-sm">Registros de entradas de stock por sucursal.</p>
        </div>
        <div class="flex gap-3">
            <a href="<?php echo URL_BASE; ?>/entries.php/registrar" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-plus"></i> Registrar Entrada
            </a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table id="entradasTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 font-semibold">ID</th>
                    <th class="px-6 py-4 font-semibold">Producto</th>
                    <th class="px-6 py-4 font-semibold">Código</th>
                    <th class="px-6 py-4 font-semibold">Cantidad</th>
                    <th class="px-6 py-4 font-semibold">Proveedor</th>
                    <th class="px-6 py-4 font-semibold">Referencia</th>
                    <th class="px-6 py-4 font-semibold">Fecha</th>
                    <th class="px-6 py-4 font-semibold">Registrado Por</th>
                    <th class="px-6 py-4 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php if (!empty($entradas)): ?>
                    <?php foreach ($entradas as $e): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-blue-600 bg-blue-50 px-2.5 py-1 rounded text-xs font-medium border border-blue-100">
                                    #<?php echo str_pad($e->id, 4, '0', STR_PAD_LEFT); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">
                                <?php echo htmlspecialchars($e->producto_nombre); ?>
                                <?php if (!empty($e->producto_categoria)): ?>
                                    <span class="block text-xs text-gray-500 mt-0.5"><?php echo htmlspecialchars($e->producto_categoria); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-xs text-gray-600"><?php echo htmlspecialchars($e->producto_codigo); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                    <i class="fa-solid fa-arrow-up mr-1"></i> <?php echo number_format($e->cantidad); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($e->proveedor)): ?>
                                    <span class="text-sm text-gray-700"><?php echo htmlspecialchars($e->proveedor); ?></span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400 italic">No especificado</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if (!empty($e->referencia)): ?>
                                    <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded border border-gray-200"><?php echo htmlspecialchars($e->referencia); ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">Sin ref.</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-700"><?php echo date('d/m/Y', strtotime($e->fecha_creacion)); ?></span>
                                <span class="block text-xs text-gray-500"><?php echo date('H:i', strtotime($e->fecha_creacion)); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($e->usuario_nombre); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button onclick='viewEntry(<?php echo json_encode($e); ?>)' 
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors border border-transparent hover:border-blue-100" 
                                       title="Ver detalles">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>
                                    <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'Administrador'): ?>
                                        <a href="<?php echo URL_BASE; ?>/entries/eliminar/<?php echo $e->id; ?>" 
                                           class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors border border-transparent hover:border-red-100" 
                                           title="Eliminar"
                                           onclick="return confirm('¿Está seguro de eliminar esta entrada?');">
                                            <i class="fa-solid fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <i class="fa-solid fa-inbox text-4xl mb-2 text-gray-300"></i>
                            <p class="text-sm">No hay entradas registradas</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination handled by DataTables -->
</div>

<!-- Modal para Ver Detalles de Entrada -->
<div id="entryModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-2/3 shadow-lg rounded-lg bg-white">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800">Detalle de Entrada</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- ID de Entrada -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">ID de Entrada</label>
                    <p id="modal-id" class="text-lg font-mono font-bold text-blue-600"></p>
                </div>

                <!-- Fecha de Registro -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Fecha de Registro</label>
                    <p id="modal-fecha" class="text-lg font-semibold text-gray-800"></p>
                </div>

                <!-- Producto -->
                <div class="col-span-1 md:col-span-2 bg-blue-50 rounded-lg p-5 border border-blue-200">
                    <label class="block text-xs font-semibold text-blue-700 uppercase mb-2">
                        <i class="fa-solid fa-box"></i> Producto
                    </label>
                    <p id="modal-producto" class="text-xl font-bold text-gray-900"></p>
                    <div class="flex items-center gap-4 mt-2">
                        <span class="text-sm text-gray-600">
                            <span class="font-semibold">Código:</span> 
                            <span id="modal-codigo" class="font-mono bg-white px-2 py-0.5 rounded border border-blue-200"></span>
                        </span>
                        <span id="modal-categoria-wrapper" class="text-sm text-gray-600">
                            <span class="font-semibold">Categoría:</span> <span id="modal-categoria"></span>
                        </span>
                    </div>
                </div>

                <!-- Cantidad -->
                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <label class="block text-xs font-semibold text-green-700 uppercase mb-1">
                        <i class="fa-solid fa-arrow-up"></i> Cantidad Ingresada
                    </label>
                    <p id="modal-cantidad" class="text-3xl font-bold text-green-700"></p>
                    <p class="text-xs text-green-600 mt-1">unidades</p>
                </div>

                <!-- Proveedor -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                        <i class="fa-solid fa-truck"></i> Proveedor
                    </label>
                    <p id="modal-proveedor" class="text-lg font-semibold text-gray-800"></p>
                </div>

                <!-- Referencia -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                        <i class="fa-solid fa-file-invoice"></i> Referencia / N° Factura
                    </label>
                    <p id="modal-referencia" class="text-lg font-mono font-semibold text-gray-800"></p>
                </div>

                <!-- Usuario que registró -->
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                        <i class="fa-solid fa-user"></i> Registrado Por
                    </label>
                    <p id="modal-usuario" class="text-lg font-semibold text-gray-800"></p>
                    <p id="modal-correo" class="text-sm text-gray-500 mt-1"></p>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex items-center justify-end pt-4 border-t border-gray-200 mt-4">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                Cerrar
            </button>
        </div>
    </div>
</div>

<style>
    /* DataTables Custom Styling */
    #entradasTable_wrapper .dataTables_filter input {
        padding: 0.625rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin-left: 0.5rem;
    }
    
    #entradasTable_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: #3b82f6;
        ring: 2px;
        ring-color: rgba(59, 130, 246, 0.2);
    }
    
    #entradasTable_wrapper .dataTables_length select {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin: 0 0.5rem;
    }
    
    #entradasTable_wrapper .dataTables_info,
    #entradasTable_wrapper .dataTables_paginate {
        padding-top: 1.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    #entradasTable_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #4b5563;
        background: white;
    }
    
    #entradasTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #2563eb;
    }
    
    #entradasTable_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    #entradasTable_wrapper .dataTables_paginate .paginate_button.disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
</style>

<script>
    $(document).ready(function() {
        $('#entradasTable').DataTable({
            responsive: true,
            language: {
                "decimal": "",
                "emptyTable": "No hay entradas disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron entradas coincidentes",
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
            order: [[6, 'desc']], // Sort by date descending by default
            columnDefs: [
                { orderable: false, targets: [8] } // Disable sorting on Actions column
            ]
        });

        // Show success/error messages
        <?php if (isset($_SESSION['success_msg'])): ?>
            alert('<?php echo $_SESSION['success_msg']; ?>');
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_msg'])): ?>
            alert('<?php echo $_SESSION['error_msg']; ?>');
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>
    });

    // Functions for modal
    function viewEntry(entry) {
        // Populate modal with entry data
        document.getElementById('modal-id').textContent = '#' + String(entry.id).padStart(4, '0');
        document.getElementById('modal-producto').textContent = entry.producto_nombre;
        document.getElementById('modal-codigo').textContent = entry.producto_codigo;
        document.getElementById('modal-cantidad').textContent = new Intl.NumberFormat().format(entry.cantidad);
        document.getElementById('modal-usuario').textContent = entry.usuario_nombre;
        
        // Format date
        const fecha = new Date(entry.fecha_creacion);
        document.getElementById('modal-fecha').textContent = fecha.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Optional fields
        if (entry.producto_categoria) {
            document.getElementById('modal-categoria').textContent = entry.producto_categoria;
            document.getElementById('modal-categoria-wrapper').style.display = '';
        } else {
            document.getElementById('modal-categoria-wrapper').style.display = 'none';
        }
        
        if (entry.proveedor) {
            document.getElementById('modal-proveedor').textContent = entry.proveedor;
        } else {
            document.getElementById('modal-proveedor').innerHTML = '<span class="text-gray-400 italic">No especificado</span>';
        }
        
        if (entry.referencia) {
            document.getElementById('modal-referencia').textContent = entry.referencia;
        } else {
            document.getElementById('modal-referencia').innerHTML = '<span class="text-gray-400 italic">Sin referencia</span>';
        }
        
        if (entry.usuario_correo) {
            document.getElementById('modal-correo').innerHTML = '<i class="fa-solid fa-envelope text-xs"></i> ' + entry.usuario_correo;
        } else {
            document.getElementById('modal-correo').textContent = '';
        }
        
        // Show modal
        document.getElementById('entryModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('entryModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('entryModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>