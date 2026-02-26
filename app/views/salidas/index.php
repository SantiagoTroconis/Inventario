<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<!-- Page Header -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Salidas de Inventario</h2>
            <p class="text-gray-500 text-sm mt-0.5">Registro de todas las salidas de productos del inventario.</p>
        </div>
        <div class="flex gap-3">
            <button onclick="toggleFilters()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-filter"></i> Filtros
            </button>
            <?php if (isset($_SESSION['tipo_usuario']) && ($_SESSION['tipo_usuario'] === 'Administrador' || $_SESSION['tipo_usuario'] === 'Sucursal')): ?>
            <a href="<?php echo URL_BASE; ?>/exits.php/crear" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-plus"></i> Nueva Salida
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Filters Panel -->
<div id="filtersPanel" class="hidden mb-6 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <form method="GET" action="<?php echo URL_BASE; ?>/exits.php" class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Producto</label>
            <input type="text" name="producto_nombre" value="<?php echo $filtros['producto_nombre'] ?? ''; ?>" placeholder="Buscar por nombre"
                class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
        </div>
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Tipo de Salida</label>
            <select name="tipo_salida" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                <option value="">Todas</option>
                <option value="SALIDA_SOLICITUD" <?php echo ($filtros['tipo_salida'] ?? '') === 'SALIDA_SOLICITUD' ? 'selected' : ''; ?>>Por Solicitud</option>
                <option value="SALIDA_MANUAL" <?php echo ($filtros['tipo_salida'] ?? '') === 'SALIDA_MANUAL' ? 'selected' : ''; ?>>Manual</option>
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
            <a href="<?php echo URL_BASE; ?>/exits.php" class="px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">
                <i class="fa-solid fa-times mr-1.5"></i> Limpiar
            </a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <div class="overflow-x-auto">
        <table id="salidasTable" class="w-full text-sm">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">ID</th>
                    <th class="px-4 py-3 text-left font-semibold">Fecha</th>
                    <th class="px-4 py-3 text-left font-semibold">Tipo</th>
                    <th class="px-4 py-3 text-left font-semibold">Producto</th>
                    <th class="px-4 py-3 text-left font-semibold">Cantidad</th>
                    <th class="px-4 py-3 text-left font-semibold">Stock Anterior</th>
                    <th class="px-4 py-3 text-left font-semibold">Stock Resultante</th>
                    <th class="px-4 py-3 text-left font-semibold">Usuario</th>
                    <th class="px-4 py-3 text-left font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($salidas)): ?>
                    <?php foreach ($salidas as $s): ?>
                        <?php
                        $typeColors = [
                            'SALIDA_SOLICITUD' => 'bg-red-50 text-red-700 border-red-200',
                            'SALIDA_MANUAL'    => 'bg-orange-50 text-orange-700 border-orange-200',
                        ];
                        $colorClass = $typeColors[$s->tipo_movimiento] ?? 'bg-gray-50 text-gray-700 border-gray-200';
                        $typeIcons = [
                            'SALIDA_SOLICITUD' => 'fa-file-signature',
                            'SALIDA_MANUAL'    => 'fa-hand',
                        ];
                        $iconClass = $typeIcons[$s->tipo_movimiento] ?? 'fa-arrow-up';
                        $diff = $s->stock_actual - $s->stock_anterior;
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-mono text-blue-600 bg-blue-50 px-2.5 py-1 rounded text-xs font-medium border border-blue-100">
                                    #<?php echo str_pad($s->id, 5, '0', STR_PAD_LEFT); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm font-medium text-gray-900"><?php echo date('d/m/Y', strtotime($s->fecha_movimiento)); ?></span>
                                <span class="block text-xs text-gray-400"><?php echo date('H:i', strtotime($s->fecha_movimiento)); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border <?php echo $colorClass; ?>">
                                    <i class="fa-solid <?php echo $iconClass; ?>"></i>
                                    <?php echo $s->tipo_movimiento === 'SALIDA_SOLICITUD' ? 'Solicitud' : 'Manual'; ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($s->producto_nombre); ?></p>
                                <p class="text-xs text-gray-400 font-mono"><?php echo htmlspecialchars($s->producto_codigo); ?></p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    <i class="fa-solid fa-arrow-down"></i> <?php echo number_format($s->cantidad); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600"><?php echo number_format($s->stock_anterior); ?></td>
                            <td class="px-4 py-3">
                                <span class="font-semibold text-gray-900"><?php echo number_format($s->stock_actual); ?></span>
                                <span class="ml-1 text-red-500 text-xs"><i class="fa-solid fa-arrow-down"></i> <?php echo number_format($diff); ?></span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 text-sm"><?php echo htmlspecialchars($s->usuario_nombre); ?></td>
                            <td class="px-4 py-3">
                                <button onclick='viewExit(<?php echo json_encode($s); ?>)'
                                    class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                                    title="Ver detalles">
                                    <i class="fa-solid fa-eye text-xs"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400">
                        <i class="fa-solid fa-truck-ramp-box text-3xl mb-3 block opacity-30"></i>
                        No hay salidas registradas.
                    </td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Detail Modal -->
<div id="exitModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-100 flex items-center justify-center">
                    <i class="fa-solid fa-truck-ramp-box text-red-600"></i>
                </div>
                <h3 class="font-bold text-gray-900">Detalle de Salida</h3>
            </div>
            <button onclick="closeExitModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        <div class="px-6 py-5 grid grid-cols-2 gap-4">
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">ID</p>
                <p id="modal-exit-id" class="text-base font-mono font-bold text-blue-600"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Fecha</p>
                <p id="modal-exit-fecha" class="text-base font-semibold text-gray-900"></p>
            </div>
            <div class="col-span-2 rounded-xl p-4 border" id="modal-exit-tipo-container">
                <p class="text-xs font-semibold uppercase mb-1 opacity-70">Tipo de Salida</p>
                <p id="modal-exit-tipo" class="text-lg font-bold"></p>
            </div>
            <div class="col-span-2 bg-blue-50 rounded-xl p-4 border border-blue-100">
                <p class="text-xs font-semibold text-blue-600 uppercase mb-1">Producto</p>
                <p id="modal-exit-producto" class="font-bold text-gray-900"></p>
                <p id="modal-exit-codigo" class="text-xs text-gray-500 mt-0.5 font-mono"></p>
            </div>
            <div class="bg-red-50 rounded-xl p-4 border border-red-100">
                <p class="text-xs font-semibold text-red-600 uppercase mb-1"><i class="fa-solid fa-arrow-down mr-1"></i>Cantidad</p>
                <p id="modal-exit-cantidad" class="text-2xl font-bold text-red-700"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Usuario</p>
                <p id="modal-exit-usuario" class="font-semibold text-gray-900"></p>
            </div>
            <div class="bg-emerald-50 rounded-xl p-4 border border-emerald-100">
                <p class="text-xs font-semibold text-emerald-600 uppercase mb-1">Stock Anterior</p>
                <p id="modal-exit-stock-ant" class="text-2xl font-bold text-emerald-700"></p>
            </div>
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                <p class="text-xs font-semibold text-blue-600 uppercase mb-1">Stock Resultante</p>
                <p id="modal-exit-stock-act" class="text-2xl font-bold text-blue-700"></p>
            </div>
            <div class="col-span-2 bg-gray-50 rounded-xl p-4 border border-gray-100" id="modal-exit-comentario-container">
                <p class="text-xs font-semibold text-gray-400 uppercase mb-1">Comentario / Motivo</p>
                <p id="modal-exit-comentario" class="text-sm text-gray-700"></p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end">
            <button onclick="closeExitModal()" class="px-5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-colors">Cerrar</button>
        </div>
    </div>
</div>

<style>
    #salidasTable_wrapper .dataTables_filter input {
        padding: 0.625rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin-left: 0.5rem;
    }
    
    #salidasTable_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: #3b82f6;
        ring: 2px;
        ring-color: rgba(59, 130, 246, 0.2);
    }
    
    #salidasTable_wrapper .dataTables_length select {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin: 0 0.5rem;
    }
    
    #salidasTable_wrapper .dataTables_info,
    #salidasTable_wrapper .dataTables_paginate {
        padding-top: 1.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    #salidasTable_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #4b5563;
        background: white;
    }
    
    #salidasTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #2563eb;
    }
    
    #salidasTable_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    #salidasTable_wrapper .dataTables_paginate .paginate_button.disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
</style>

<script>
    $(document).ready(function() {
        $('#salidasTable').DataTable({
            responsive: true,
            language: {
                "decimal": "",
                "emptyTable": "No hay salidas disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ salidas",
                "infoEmpty": "Mostrando 0 a 0 de 0 salidas",
                "infoFiltered": "(filtrado de _MAX_ salidas totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ salidas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron salidas coincidentes",
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

    function viewExit(exit) {
        const typeColors = {
            'SALIDA_SOLICITUD': 'bg-red-50 text-red-700 border-red-200',
            'SALIDA_MANUAL': 'bg-orange-50 text-orange-700 border-orange-200'
        };

        document.getElementById('modal-exit-id').textContent = '#' + String(exit.id).padStart(5, '0');
        document.getElementById('modal-exit-producto').textContent = exit.producto_nombre;
        document.getElementById('modal-exit-codigo').textContent = 'Código: ' + exit.producto_codigo;
        document.getElementById('modal-exit-cantidad').textContent = new Intl.NumberFormat().format(exit.cantidad);
        document.getElementById('modal-exit-usuario').textContent = exit.usuario_nombre;
        document.getElementById('modal-exit-stock-ant').textContent = new Intl.NumberFormat().format(exit.stock_anterior);
        document.getElementById('modal-exit-stock-act').textContent = new Intl.NumberFormat().format(exit.stock_actual);
        
        const tipoTexto = exit.tipo_movimiento === 'SALIDA_SOLICITUD' ? 'Salida por Solicitud' : 'Salida Manual';
        document.getElementById('modal-exit-tipo').textContent = tipoTexto;
        
        const fecha = new Date(exit.fecha_movimiento);
        document.getElementById('modal-exit-fecha').textContent = fecha.toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        const container = document.getElementById('modal-exit-tipo-container');
        container.className = 'col-span-1 md:col-span-2 rounded-lg p-4 border ' + (typeColors[exit.tipo_movimiento] || 'bg-gray-50 text-gray-700 border-gray-200');
        
        if (exit.comentario) {
            document.getElementById('modal-exit-comentario').textContent = exit.comentario;
            document.getElementById('modal-exit-comentario-container').style.display = '';
        } else {
            document.getElementById('modal-exit-comentario-container').style.display = 'none';
        }
        
        document.getElementById('exitModal').classList.remove('hidden');
    }

    function closeExitModal() {
        document.getElementById('exitModal').classList.add('hidden');
    }

    document.getElementById('exitModal')?.addEventListener('click', function(e) {
        if (e.target === this) {
            closeExitModal();
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeExitModal();
        }
    });
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>