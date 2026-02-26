<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- Page Header -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Movimientos de Inventario</h2>
            <p class="text-gray-500 text-sm mt-0.5">Registro completo de todos los movimientos de stock.</p>
        </div>
        <button onclick="toggleFilters()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
            <i class="fa-solid fa-filter"></i> Filtros
        </button>
    </div>
</div>

<!-- Filters Panel -->
<div id="filtersPanel" class="hidden mb-6 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <form method="GET" action="<?php echo URL_BASE; ?>/movements" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Producto</label>
            <input type="text" name="producto_nombre" value="<?php echo $filtros['producto_nombre'] ?? ''; ?>" placeholder="Buscar por nombre"
                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Movimiento</label>
            <select name="tipo_movimiento" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                <option value="">Todos</option>
                <option value="ENTRADA" <?php echo ($filtros['tipo_movimiento'] ?? '') === 'ENTRADA' ? 'selected' : ''; ?>>Entrada</option>
                <option value="SALIDA_SOLICITUD" <?php echo ($filtros['tipo_movimiento'] ?? '') === 'SALIDA_SOLICITUD' ? 'selected' : ''; ?>>Salida (Solicitud)</option>
                <option value="SALIDA_MANUAL" <?php echo ($filtros['tipo_movimiento'] ?? '') === 'SALIDA_MANUAL' ? 'selected' : ''; ?>>Salida Manual</option>
                <option value="AJUSTE_INVENTARIO" <?php echo ($filtros['tipo_movimiento'] ?? '') === 'AJUSTE_INVENTARIO' ? 'selected' : ''; ?>>Ajuste</option>
                <option value="DEVOLUCION" <?php echo ($filtros['tipo_movimiento'] ?? '') === 'DEVOLUCION' ? 'selected' : ''; ?>>Devolución</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha Inicio</label>
            <input type="date" name="fecha_inicio" value="<?php echo $filtros['fecha_inicio'] ?? ''; ?>"
                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha Fin</label>
            <input type="date" name="fecha_fin" value="<?php echo $filtros['fecha_fin'] ?? ''; ?>"
                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-search mr-1.5"></i> Buscar
            </button>
            <a href="<?php echo URL_BASE; ?>/movements" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-times mr-1.5"></i> Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <div class="overflow-x-auto">
        <table id="movimientosTable" class="w-full text-sm">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">ID</th>
                    <th class="px-4 py-3 text-left font-semibold">Fecha</th>
                    <th class="px-4 py-3 text-left font-semibold">Tipo</th>
                    <th class="px-4 py-3 text-left font-semibold">Producto</th>
                    <th class="px-4 py-3 text-left font-semibold">Cantidad</th>
                    <th class="px-4 py-3 text-left font-semibold">Stock Anterior</th>
                    <th class="px-4 py-3 text-left font-semibold">Stock Actual</th>
                    <th class="px-4 py-3 text-left font-semibold">Usuario</th>
                    <th class="px-4 py-3 text-left font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($movimientos)): ?>
                    <?php foreach ($movimientos as $m): ?>
                        <?php
                        $typeColors = [
                            'ENTRADA'            => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'DEVOLUCION'         => 'bg-blue-50 text-blue-700 border-blue-200',
                            'SALIDA_SOLICITUD'   => 'bg-red-50 text-red-700 border-red-200',
                            'SALIDA_MANUAL'      => 'bg-orange-50 text-orange-700 border-orange-200',
                            'AJUSTE_INVENTARIO'  => 'bg-purple-50 text-purple-700 border-purple-200',
                        ];
                        $colorClass = $typeColors[$m->tipo_movimiento] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                        $typeIcons = [
                            'ENTRADA'           => 'fa-arrow-down',
                            'DEVOLUCION'        => 'fa-rotate-left',
                            'SALIDA_SOLICITUD'  => 'fa-arrow-up',
                            'SALIDA_MANUAL'     => 'fa-arrow-up',
                            'AJUSTE_INVENTARIO' => 'fa-wrench',
                        ];
                        $iconClass = $typeIcons[$m->tipo_movimiento] ?? 'fa-exchange';
                        $diff = $m->stock_actual - $m->stock_anterior;
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-mono text-blue-600 bg-blue-50 px-2.5 py-1 rounded text-xs font-medium border border-blue-100">
                                    #<?php echo str_pad($m->id, 5, '0', STR_PAD_LEFT); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-900"><?php echo date('d/m/Y', strtotime($m->fecha_movimiento)); ?></span>
                                <span class="block text-xs text-gray-400"><?php echo date('H:i', strtotime($m->fecha_movimiento)); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border <?php echo $colorClass; ?>">
                                    <i class="fa-solid <?php echo $iconClass; ?>"></i>
                                    <?php echo str_replace('_', ' ', $m->tipo_movimiento); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($m->producto_nombre); ?></p>
                                <p class="text-xs text-gray-400 font-mono"><?php echo htmlspecialchars($m->producto_codigo); ?></p>
                            </td>
                            <td class="px-4 py-3 font-bold text-gray-900"><?php echo number_format($m->cantidad); ?></td>
                            <td class="px-4 py-3 text-gray-600"><?php echo number_format($m->stock_anterior); ?></td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-gray-900"><?php echo number_format($m->stock_actual); ?></span>
                                <?php if ($diff > 0): ?>
                                    <span class="ml-1 text-emerald-600 text-xs"><i class="fa-solid fa-arrow-up"></i> +<?php echo number_format($diff); ?></span>
                                <?php elseif ($diff < 0): ?>
                                    <span class="ml-1 text-red-500 text-xs"><i class="fa-solid fa-arrow-down"></i> <?php echo number_format($diff); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-gray-700 text-sm"><?php echo htmlspecialchars($m->usuario_nombre); ?></td>
                            <td class="px-4 py-3">
                                <button onclick='viewMovement(<?php echo json_encode($m); ?>)'
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                                    title="Ver detalles">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-right-left text-3xl mb-3 block opacity-30"></i>
                        No hay movimientos registrados.
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Detail Modal -->
<div id="movementModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i class="fa-solid fa-right-left text-blue-600"></i>
                </div>
                <h3 class="font-bold text-gray-900">Detalle de Movimiento</h3>
            </div>
            <button onclick="closeMovementModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="px-6 py-5 grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">ID</p>
                <p id="modal-mov-id" class="text-base font-mono font-bold text-blue-600"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Fecha</p>
                <p id="modal-mov-fecha" class="text-base font-semibold text-gray-900"></p>
            </div>
            <div class="col-span-2 rounded-xl p-4 border" id="modal-mov-tipo-container">
                <p class="text-xs font-semibold uppercase mb-1 opacity-70">Tipo de Movimiento</p>
                <p id="modal-mov-tipo" class="text-lg font-bold"></p>
            </div>
            <div class="col-span-2 bg-blue-50 rounded-xl p-4 border border-blue-100">
                <p class="text-xs font-semibold text-blue-600 uppercase mb-1">Producto</p>
                <p id="modal-mov-producto" class="font-bold text-gray-900"></p>
                <p id="modal-mov-codigo" class="text-xs text-gray-500 mt-0.5 font-mono"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Cantidad</p>
                <p id="modal-mov-cantidad" class="text-2xl font-bold text-gray-900"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Usuario</p>
                <p id="modal-mov-usuario" class="font-semibold text-gray-900"></p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                <p class="text-xs font-semibold text-emerald-600 uppercase mb-1">Stock Anterior</p>
                <p id="modal-mov-stock-ant" class="text-2xl font-bold text-emerald-700"></p>
            </div>
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                <p class="text-xs font-semibold text-blue-600 uppercase mb-1">Stock Actual</p>
                <p id="modal-mov-stock-act" class="text-2xl font-bold text-blue-700"></p>
            </div>
            <div class="col-span-2 bg-gray-50 rounded-xl p-4 border border-gray-100" id="modal-mov-comentario-container">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Comentario</p>
                <p id="modal-mov-comentario" class="text-sm text-gray-700"></p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button onclick="closeMovementModal()" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">Cerrar</button>
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
