<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Movimientos de Inventario</h2>
            <p class="text-gray-500 text-sm">Registro completo de todos los movimientos de stock.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="toggleFilters()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                <i class="fa-solid fa-filter"></i> Filtros
            </button>
        </div>
    </div>

    <!-- Filters Panel -->
    <div id="filtersPanel" class="hidden mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
        <form method="GET" action="<?php echo URL_BASE; ?>/movements" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Producto</label>
                <input type="text" name="producto_nombre" 
                       value="<?php echo $filtros['producto_nombre'] ?? ''; ?>"
                       placeholder="Buscar por nombre"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Movimiento</label>
                <select name="tipo_movimiento" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option value="ENTRADA" <?php echo ($filtros['tipo_movimiento'] ?? '') === 'ENTRADA' ? 'selected' : ''; ?>>Entrada</option>
                    <option value="SALIDA_SOLICITUD" <?php echo ($filtros['tipo_movimiento'] ?? '') === 'SALIDA_SOLICITUD' ? 'selected' : ''; ?>>Salida (Solicitud)</option>
                    <option value="SALIDA_MANUAL" <?php echo ($filtros['tipo_movimiento'] ?? '') === 'SALIDA_MANUAL' ? 'selected' : ''; ?>>Salida Manual</option>
                    <option value="AJUSTE_INVENTARIO" <?php echo ($filtros['tipo_movimiento'] ?? '') === 'AJUSTE_INVENTARIO' ? 'selected' : ''; ?>>Ajuste</option>
                    <option value="DEVOLUCION" <?php echo ($filtros['tipo_movimiento'] ?? '') === 'DEVOLUCION' ? 'selected' : ''; ?>>Devolución</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" 
                       value="<?php echo $filtros['fecha_inicio'] ?? ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha Fin</label>
                <input type="date" name="fecha_fin" 
                       value="<?php echo $filtros['fecha_fin'] ?? ''; ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-search"></i> Buscar
                </button>
                <a href="<?php echo URL_BASE; ?>/movements" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-times"></i> Limpiar
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table id="movimientosTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 font-semibold">ID</th>
                    <th class="px-6 py-4 font-semibold">Fecha</th>
                    <th class="px-6 py-4 font-semibold">Tipo</th>
                    <th class="px-6 py-4 font-semibold">Producto</th>
                    <th class="px-6 py-4 font-semibold">Cantidad</th>
                    <th class="px-6 py-4 font-semibold">Stock Anterior</th>
                    <th class="px-6 py-4 font-semibold">Stock Actual</th>
                    <th class="px-6 py-4 font-semibold">Usuario</th>
                    <th class="px-6 py-4 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php if (!empty($movimientos)): ?>
                    <?php foreach ($movimientos as $m): ?>
                        <?php
                        // Determine colors based on movement type
                        $typeColors = [
                            'ENTRADA' => 'bg-green-50 text-green-700 border-green-200',
                            'DEVOLUCION' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'SALIDA_SOLICITUD' => 'bg-red-50 text-red-700 border-red-200',
                            'SALIDA_MANUAL' => 'bg-orange-50 text-orange-700 border-orange-200',
                            'AJUSTE_INVENTARIO' => 'bg-purple-50 text-purple-700 border-purple-200'
                        ];
                        $colorClass = $typeColors[$m->tipo_movimiento] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                        
                        $typeIcons = [
                            'ENTRADA' => 'fa-arrow-down',
                            'DEVOLUCION' => 'fa-rotate-left',
                            'SALIDA_SOLICITUD' => 'fa-arrow-up',
                            'SALIDA_MANUAL' => 'fa-arrow-up',
                            'AJUSTE_INVENTARIO' => 'fa-wrench'
                        ];
                        $iconClass = $typeIcons[$m->tipo_movimiento] ?? 'fa-exchange';
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-gray-600 text-xs">
                                    #<?php echo str_pad($m->id, 5, '0', STR_PAD_LEFT); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-700"><?php echo date('d/m/Y', strtotime($m->fecha_movimiento)); ?></span>
                                <span class="block text-xs text-gray-500"><?php echo date('H:i', strtotime($m->fecha_movimiento)); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border <?php echo $colorClass; ?>">
                                    <i class="fa-solid <?php echo $iconClass; ?> mr-1"></i>
                                    <?php echo str_replace('_', ' ', $m->tipo_movimiento); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-gray-900"><?php echo htmlspecialchars($m->producto_nombre); ?></span>
                                <span class="block text-xs text-gray-500 font-mono"><?php echo htmlspecialchars($m->producto_codigo); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-bold text-gray-900"><?php echo number_format($m->cantidad); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-600"><?php echo number_format($m->stock_anterior); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-gray-900 font-medium"><?php echo number_format($m->stock_actual); ?></span>
                                <?php
                                $diff = $m->stock_actual - $m->stock_anterior;
                                if ($diff > 0) {
                                    echo '<span class="ml-1 text-green-600 text-xs"><i class="fa-solid fa-arrow-up"></i> +' . number_format($diff) . '</span>';
                                } elseif ($diff < 0) {
                                    echo '<span class="ml-1 text-red-600 text-xs"><i class="fa-solid fa-arrow-down"></i> ' . number_format($diff) . '</span>';
                                }
                                ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($m->usuario_nombre); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <button onclick='viewMovement(<?php echo json_encode($m); ?>)' 
                                   class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors border border-transparent hover:border-blue-100" 
                                   title="Ver detalles">
                                    <i class="fa-solid fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                            <i class="fa-solid fa-inbox text-4xl mb-2 text-gray-300"></i>
                            <p class="text-sm">No hay movimientos registrados</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Ver Detalles de Movimiento -->
<div id="movementModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-lg bg-white">
        <!-- Modal Header -->
        <div class="flex items-center justify-between pb-4 border-b border-gray-200">
            <h3 class="text-xl font-bold text-gray-800">Detalle de Movimiento</h3>
            <button onclick="closeMovementModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fa-solid fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Modal Body -->
        <div class="mt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">ID Movimiento</label>
                    <p id="modal-mov-id" class="text-lg font-mono font-bold text-blue-600"></p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Fecha</label>
                    <p id="modal-mov-fecha" class="text-lg font-semibold text-gray-800"></p>
                </div>

                <div class="col-span-1 md:col-span-2 rounded-lg p-4 border" id="modal-mov-tipo-container">
                    <label class="block text-xs font-semibold uppercase mb-1">Tipo de Movimiento</label>
                    <p id="modal-mov-tipo" class="text-xl font-bold"></p>
                </div>

                <div class="col-span-1 md:col-span-2 bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <label class="block text-xs font-semibold text-blue-700 uppercase mb-1">Producto</label>
                    <p id="modal-mov-producto" class="text-lg font-bold text-gray-900"></p>
                    <p id="modal-mov-codigo" class="text-sm text-gray-600 mt-1"></p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Cantidad</label>
                    <p id="modal-mov-cantidad" class="text-2xl font-bold text-gray-900"></p>
                </div>

                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Usuario</label>
                    <p id="modal-mov-usuario" class="text-lg font-semibold text-gray-800"></p>
                </div>

                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                    <label class="block text-xs font-semibold text-green-700 uppercase mb-1">Stock Anterior</label>
                    <p id="modal-mov-stock-ant" class="text-2xl font-bold text-green-700"></p>
                </div>

                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <label class="block text-xs font-semibold text-blue-700 uppercase mb-1">Stock Actual</label>
                    <p id="modal-mov-stock-act" class="text-2xl font-bold text-blue-700"></p>
                </div>

                <div class="col-span-1 md:col-span-2 bg-gray-50 rounded-lg p-4 border border-gray-200" id="modal-mov-comentario-container">
                    <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Comentario</label>
                    <p id="modal-mov-comentario" class="text-sm text-gray-700"></p>
                </div>
            </div>
        </div>
        
        <!-- Modal Footer -->
        <div class="flex items-center justify-end pt-4 border-t border-gray-200 mt-4">
            <button onclick="closeMovementModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                Cerrar
            </button>
        </div>
    </div>
</div>

<style>
    #movimientosTable_wrapper .dataTables_filter input {
        padding: 0.625rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin-left: 0.5rem;
    }
    
    #movimientosTable_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: #3b82f6;
        ring: 2px;
        ring-color: rgba(59, 130, 246, 0.2);
    }
    
    #movimientosTable_wrapper .dataTables_length select {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin: 0 0.5rem;
    }
    
    #movimientosTable_wrapper .dataTables_info,
    #movimientosTable_wrapper .dataTables_paginate {
        padding-top: 1.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    #movimientosTable_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #4b5563;
        background: white;
    }
    
    #movimientosTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #2563eb;
    }
    
    #movimientosTable_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    #movimientosTable_wrapper .dataTables_paginate .paginate_button.disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
</style>

<script>
    $(document).ready(function() {
        $('#movimientosTable').DataTable({
            responsive: true,
            language: {
                "decimal": "",
                "emptyTable": "No hay movimientos disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ movimientos",
                "infoEmpty": "Mostrando 0 a 0 de 0 movimientos",
                "infoFiltered": "(filtrado de _MAX_ movimientos totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ movimientos",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron movimientos coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            pageLength: 20,
            order: [[1, 'desc']], // Sort by date descending
            columnDefs: [
                { orderable: false, targets: [8] }
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

    function toggleFilters() {
        const panel = document.getElementById('filtersPanel');
        panel.classList.toggle('hidden');
    }

    function viewMovement(movement) {
        const typeColors = {
            'ENTRADA': 'bg-green-50 text-green-700 border-green-200',
            'DEVOLUCION': 'bg-blue-50 text-blue-700 border-blue-200',
            'SALIDA_SOLICITUD': 'bg-red-50 text-red-700 border-red-200',
            'SALIDA_MANUAL': 'bg-orange-50 text-orange-700 border-orange-200',
            'AJUSTE_INVENTARIO': 'bg-purple-50 text-purple-700 border-purple-200'
        };

        document.getElementById('modal-mov-id').textContent = '#' + String(movement.id).padStart(5, '0');
        document.getElementById('modal-mov-producto').textContent = movement.producto_nombre;
        document.getElementById('modal-mov-codigo').textContent = 'Código: ' + movement.producto_codigo;
        document.getElementById('modal-mov-cantidad').textContent = new Intl.NumberFormat().format(movement.cantidad);
        document.getElementById('modal-mov-usuario').textContent = movement.usuario_nombre;
        document.getElementById('modal-mov-stock-ant').textContent = new Intl.NumberFormat().format(movement.stock_anterior);
        document.getElementById('modal-mov-stock-act').textContent = new Intl.NumberFormat().format(movement.stock_actual);
        document.getElementById('modal-mov-tipo').textContent = movement.tipo_movimiento.replace(/_/g, ' ');
        
        const fecha = new Date(movement.fecha_movimiento);
        document.getElementById('modal-mov-fecha').textContent = fecha.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const container = document.getElementById('modal-mov-tipo-container');
        container.className = 'col-span-1 md:col-span-2 rounded-lg p-4 border ' + (typeColors[movement.tipo_movimiento] || 'bg-gray-50 text-gray-700 border-gray-200');
        
        if (movement.comentario) {
            document.getElementById('modal-mov-comentario').textContent = movement.comentario;
            document.getElementById('modal-mov-comentario-container').style.display = '';
        } else {
            document.getElementById('modal-mov-comentario-container').style.display = 'none';
        }
        
        document.getElementById('movementModal').classList.remove('hidden');
    }

    function closeMovementModal() {
        document.getElementById('movementModal').classList.add('hidden');
    }

    document.getElementById('movementModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeMovementModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeMovementModal();
        }
    });
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
