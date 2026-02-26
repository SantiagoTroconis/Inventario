<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php
// Helper: get status badge HTML
function estadoBadge($estado) {
    $map = [
        'Pendiente'   => ['bg-amber-50 text-amber-700 border-amber-200', 'fa-clock',            'Pendiente'],
        'Confirmada'  => ['bg-green-50 text-green-700 border-green-200',  'fa-circle-check',    'Confirmada'],
        'Retrasada'   => ['bg-orange-50 text-orange-700 border-orange-200','fa-triangle-exclamation','Retrasada'],
        'Parcial'     => ['bg-blue-50 text-blue-700 border-blue-200',     'fa-circle-half-stroke','Parcial'],
        'No_Recibida' => ['bg-red-50 text-red-700 border-red-200',        'fa-circle-xmark',    'No Recibida'],
    ];
    $e = $estado ?? 'Confirmada';
    $cfg = $map[$e] ?? $map['Confirmada'];
    return "<span class=\"inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border {$cfg[0]}\">
                <i class=\"fa-solid {$cfg[1]} text-xs\"></i> {$cfg[2]}
            </span>";
}
?>

<!-- Page Header -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Entradas de Inventario</h2>
            <p class="text-gray-500 text-sm mt-0.5">Seguimiento de entregas de productos aprobados.</p>
        </div>
        <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'Administrador'): ?>
        <a href="<?php echo URL_BASE; ?>/entries.php/registrar" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Registrar Manual
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

    <!-- Pending alert banner -->
    <?php
    $pendingCount = 0;
    foreach ($entradas as $e) { if (($e->estado ?? 'Confirmada') === 'Pendiente') $pendingCount++; }
    ?>
    <?php if ($pendingCount > 0): ?>
    <div class="mb-5 flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl">
        <i class="fa-solid fa-bell text-amber-500 mt-0.5 text-lg"></i>
        <div>
            <p class="font-semibold text-amber-800 text-sm">Tienes <?php echo $pendingCount; ?> entrega<?php echo $pendingCount > 1 ? 's' : ''; ?> pendiente<?php echo $pendingCount > 1 ? 's' : ''; ?> de confirmar.</p>
            <p class="text-amber-700 text-xs mt-0.5">Revisa la tabla y confirma la llegada de los productos o reporta cualquier inconveniente.</p>
        </div>
    </div>
    <?php endif; ?>

    <div class="overflow-x-auto">
        <table id="entradasTable" class="w-full text-sm">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">ID</th>
                    <th class="px-4 py-3 text-left font-semibold">Producto</th>
                    <th class="px-4 py-3 text-left font-semibold">Cantidad</th>
                    <th class="px-4 py-3 text-left font-semibold">Estado</th>
                    <th class="px-4 py-3 text-left font-semibold">Entrega Estimada</th>
                    <th class="px-4 py-3 text-left font-semibold">Entrega Real</th>
                    <th class="px-4 py-3 text-left font-semibold">Registrado</th>
                    <th class="px-4 py-3 text-left font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php if (!empty($entradas)): ?>
                    <?php foreach ($entradas as $e): ?>
                        <?php $estado = $e->estado ?? 'Confirmada'; ?>
                        <tr class="hover:bg-gray-50 transition-colors <?php echo $estado === 'Pendiente' ? 'bg-amber-50/30' : ''; ?>">
                            <td class="px-4 py-3">
                                <span class="font-mono text-blue-600 bg-blue-50 px-2.5 py-1 rounded text-xs font-medium border border-blue-100">
                                    #<?php echo str_pad($e->id, 4, '0', STR_PAD_LEFT); ?>
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($e->producto_nombre); ?></p>
                                <?php if (!empty($e->producto_categoria)): ?>
                                    <span class="text-xs text-gray-400"><?php echo htmlspecialchars($e->producto_categoria); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i class="fa-solid fa-box"></i> <?php echo number_format($e->cantidad); ?> solicitado
                                    </span>
                                    <?php if ($estado === 'Parcial' && isset($e->cantidad_recibida)): ?>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        <i class="fa-solid fa-check"></i> <?php echo number_format($e->cantidad_recibida); ?> recibido
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <?php echo estadoBadge($estado); ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (!empty($e->fecha_entrega_estimada)): ?>
                                    <?php
                                    $hoy = new DateTime();
                                    $estimada = new DateTime($e->fecha_entrega_estimada);
                                    $atrasada = $estado === 'Pendiente' && $estimada < $hoy;
                                    ?>
                                    <span class="text-sm font-medium <?php echo $atrasada ? 'text-red-600' : 'text-gray-700'; ?>">
                                        <?php if ($atrasada): ?><i class="fa-solid fa-triangle-exclamation mr-1"></i><?php endif; ?>
                                        <?php echo $estimada->format('d/m/Y'); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">No definida</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <?php if (!empty($e->fecha_entrega_real)): ?>
                                    <span class="text-sm text-gray-700"><?php echo date('d/m/Y', strtotime($e->fecha_entrega_real)); ?></span>
                                <?php else: ?>
                                    <span class="text-xs text-gray-400 italic">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-gray-700"><?php echo date('d/m/Y', strtotime($e->fecha_creacion)); ?></span>
                                <span class="block text-xs text-gray-400"><?php echo date('H:i', strtotime($e->fecha_creacion)); ?></span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1.5">
                                    <!-- View button (always visible) -->
                                    <button onclick='viewEntry(<?php echo json_encode($e); ?>)'
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors"
                                        title="Ver detalles">
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    <?php if ($estado === 'Pendiente'): ?>
                                    <!-- Confirm arrival -->
                                    <button onclick="openActionModal(<?php echo $e->id; ?>, 'confirmar', <?php echo (int)$e->cantidad; ?>)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-white bg-emerald-500 hover:bg-emerald-600 transition-colors"
                                        title="Confirmar llegada">
                                        <i class="fa-solid fa-circle-check text-xs"></i>
                                    </button>
                                    <!-- Report delay -->
                                    <button onclick="openActionModal(<?php echo $e->id; ?>, 'retraso', <?php echo (int)$e->cantidad; ?>)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-white bg-orange-400 hover:bg-orange-500 transition-colors"
                                        title="Reportar retraso">
                                        <i class="fa-solid fa-clock text-xs"></i>
                                    </button>
                                    <!-- Partial / Not received -->
                                    <button onclick="openActionModal(<?php echo $e->id; ?>, 'opciones', <?php echo (int)$e->cantidad; ?>)"
                                        class="w-8 h-8 rounded-lg flex items-center justify-center text-white bg-red-400 hover:bg-red-500 transition-colors"
                                        title="No recibido / Parcial">
                                        <i class="fa-solid fa-circle-xmark text-xs"></i>
                                    </button>
                                    <?php endif; ?>

                                    <?php if (!empty($e->notas_entrega)): ?>
                                    <span class="ml-1 text-gray-400" title="<?php echo htmlspecialchars($e->notas_entrega); ?>">
                                        <i class="fa-solid fa-comment-dots text-xs"></i>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fa-solid fa-inbox text-4xl mb-3 text-gray-300 block"></i>
                            <p class="text-sm font-medium">No hay entradas registradas</p>
                            <p class="text-xs text-gray-400 mt-1">Las entradas aparecerán aquí cuando se aprueben tus solicitudes.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ===================== MODALS ===================== -->

<!-- Detail Modal -->
<div id="entryModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Detalle de Entrada</h3>
            <button onclick="closeModal('entryModal')" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div class="p-6 grid grid-cols-2 gap-4">
            <div class="col-span-2 bg-blue-50 rounded-xl p-4 border border-blue-100">
                <label class="text-xs font-semibold text-blue-600 uppercase tracking-wide">Producto</label>
                <p id="modal-producto" class="text-xl font-bold text-gray-900 mt-1"></p>
                <p id="modal-codigo" class="text-sm font-mono text-gray-500 mt-0.5"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Estado</label>
                <div id="modal-estado" class="mt-1"></div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Cantidad</label>
                <p id="modal-cantidad" class="text-2xl font-bold text-green-600 mt-1"></p>
                <p id="modal-cantidad-recibida" class="text-xs text-gray-500 mt-0.5 hidden"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Entrega Estimada</label>
                <p id="modal-fecha-estimada" class="text-sm font-medium text-gray-700 mt-1"></p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                <label class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Entrega Real</label>
                <p id="modal-fecha-real" class="text-sm font-medium text-gray-700 mt-1"></p>
            </div>
            <div id="modal-notas-wrapper" class="col-span-2 bg-yellow-50 rounded-xl p-4 border border-yellow-100 hidden">
                <label class="text-xs font-semibold text-yellow-700 uppercase tracking-wide"><i class="fa-solid fa-comment mr-1"></i>Notas</label>
                <p id="modal-notas" class="text-sm text-gray-700 mt-1"></p>
            </div>
        </div>
        <div class="flex justify-end p-6 pt-0">
            <button onclick="closeModal('entryModal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">Cerrar</button>
        </div>
    </div>
</div>

<!-- Action Modal (Confirm / Delay / Options) -->
<div id="actionModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-60 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 id="actionModal-title" class="text-lg font-bold text-gray-800">Acción</h3>
            <button onclick="closeModal('actionModal')" class="text-gray-400 hover:text-gray-600 w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <div id="actionModal-body" class="p-6 space-y-4"></div>
        <div class="flex justify-end gap-3 p-6 pt-0">
            <button onclick="closeModal('actionModal')" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">Cancelar</button>
            <button id="actionModal-confirm" class="px-5 py-2 text-white text-sm font-semibold rounded-lg transition-colors">Confirmar</button>
        </div>
    </div>
</div>

<!-- ===================== STYLES ===================== -->
<style>
    #entradasTable_wrapper .dataTables_filter input {
        padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; margin-left: 0.5rem;
    }
    #entradasTable_wrapper .dataTables_filter input:focus { outline: none; border-color: #3b82f6; }
    #entradasTable_wrapper .dataTables_length select { padding: 0.5rem 2rem 0.5rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; font-size: 0.875rem; margin: 0 0.5rem; }
    #entradasTable_wrapper .dataTables_info, #entradasTable_wrapper .dataTables_paginate { padding-top: 1.5rem; font-size: 0.875rem; color: #6b7280; }
    #entradasTable_wrapper .dataTables_paginate .paginate_button { padding: 0.5rem 0.75rem; margin: 0 0.125rem; border: 1px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem; color: #4b5563; background: white; }
    #entradasTable_wrapper .dataTables_paginate .paginate_button:hover { background: #eff6ff; border-color: #3b82f6; color: #2563eb; }
    #entradasTable_wrapper .dataTables_paginate .paginate_button.current { background: #3b82f6; border-color: #3b82f6; color: white; }
</style>

<!-- ===================== SCRIPTS ===================== -->
<script>
    const baseUrl = '<?php echo URL_BASE; ?>';

    $(document).ready(function() {
        $('#entradasTable').DataTable({
            responsive: true,
            language: {
                "emptyTable": "No hay entradas disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron coincidencias",
                "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" }
            },
            pageLength: 10,
            order: [[6, 'desc']],
            columnDefs: [{ orderable: false, targets: [7] }]
        });

        <?php if (isset($_SESSION['success_msg'])): ?>
            showToast('<?php echo addslashes($_SESSION['success_msg']); ?>', 'success');
            <?php unset($_SESSION['success_msg']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_msg'])): ?>
            showToast('<?php echo addslashes($_SESSION['error_msg']); ?>', 'error');
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>
    });

    // ---- Toast Notification ----
    function showToast(message, type = 'success') {
        const colors = {
            success: 'bg-green-600',
            error: 'bg-red-600',
            info: 'bg-blue-600',
            warning: 'bg-amber-500'
        };
        const toast = document.createElement('div');
        toast.className = `fixed bottom-6 right-6 z-[999] flex items-center gap-3 px-5 py-3.5 rounded-xl text-white shadow-xl text-sm font-medium transition-all duration-300 ${colors[type] || colors.success}`;
        toast.innerHTML = `<i class="fa-solid ${type === 'success' ? 'fa-circle-check' : type === 'error' ? 'fa-circle-xmark' : 'fa-circle-info'}"></i> ${message}`;
        document.body.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3500);
    }

    // ---- Modal Helpers ----
    function closeModal(id) { document.getElementById(id).classList.add('hidden'); }
    document.querySelectorAll('#entryModal, #actionModal').forEach(m => {
        m.addEventListener('click', e => { if (e.target === m) m.classList.add('hidden'); });
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') { closeModal('entryModal'); closeModal('actionModal'); } });

    // ---- View Entry Detail ----
    const estadoHtml = {
        'Pendiente':   '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border bg-amber-50 text-amber-700 border-amber-200"><i class="fa-solid fa-clock"></i> Pendiente</span>',
        'Confirmada':  '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border bg-green-50 text-green-700 border-green-200"><i class="fa-solid fa-circle-check"></i> Confirmada</span>',
        'Retrasada':   '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border bg-orange-50 text-orange-700 border-orange-200"><i class="fa-solid fa-triangle-exclamation"></i> Retrasada</span>',
        'Parcial':     '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border bg-blue-50 text-blue-700 border-blue-200"><i class="fa-solid fa-circle-half-stroke"></i> Parcial</span>',
        'No_Recibida': '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border bg-red-50 text-red-700 border-red-200"><i class="fa-solid fa-circle-xmark"></i> No Recibida</span>',
    };

    function viewEntry(entry) {
        const fmt = d => d ? new Date(d).toLocaleDateString('es-ES', {day:'2-digit',month:'2-digit',year:'numeric'}) : '—';
        document.getElementById('modal-producto').textContent = entry.producto_nombre;
        document.getElementById('modal-codigo').textContent = entry.producto_codigo;
        document.getElementById('modal-estado').innerHTML = estadoHtml[entry.estado] || estadoHtml['Confirmada'];
        document.getElementById('modal-cantidad').textContent = new Intl.NumberFormat().format(entry.cantidad) + ' uds.';
        const cantRec = document.getElementById('modal-cantidad-recibida');
        if (entry.cantidad_recibida) { cantRec.textContent = 'Recibido: ' + entry.cantidad_recibida + ' uds.'; cantRec.classList.remove('hidden'); }
        else { cantRec.classList.add('hidden'); }
        document.getElementById('modal-fecha-estimada').textContent = entry.fecha_entrega_estimada ? fmt(entry.fecha_entrega_estimada) : 'No definida';
        document.getElementById('modal-fecha-real').textContent = entry.fecha_entrega_real ? fmt(entry.fecha_entrega_real) : '—';
        const notasW = document.getElementById('modal-notas-wrapper');
        if (entry.notas_entrega) { document.getElementById('modal-notas').textContent = entry.notas_entrega; notasW.classList.remove('hidden'); }
        else { notasW.classList.add('hidden'); }
        document.getElementById('entryModal').classList.remove('hidden');
    }

    // ---- Action Modal ----
    let currentActionEntryId = null;
    let currentActionType = null;
    let currentActionCantidad = 0;

    function openActionModal(entryId, type, cantidad) {
        currentActionEntryId = entryId;
        currentActionType = type;
        currentActionCantidad = cantidad;

        const title = document.getElementById('actionModal-title');
        const body = document.getElementById('actionModal-body');
        const btn = document.getElementById('actionModal-confirm');

        if (type === 'confirmar') {
            title.textContent = '✅ Confirmar Llegada';
            body.innerHTML = `
                <p class="text-sm text-gray-600">Confirmar que los <strong>${cantidad} productos</strong> llegaron correctamente.</p>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Fecha de llegada</label>
                    <input type="date" id="ac-fecha" value="${new Date().toISOString().split('T')[0]}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Notas (opcional)</label>
                    <textarea id="ac-notas" rows="2" placeholder="Condición del paquete, observaciones..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 resize-none"></textarea>
                </div>`;
            btn.className = 'px-5 py-2 text-white text-sm font-semibold rounded-lg bg-green-500 hover:bg-green-600 transition-colors';
            btn.textContent = 'Confirmar llegada';

        } else if (type === 'retraso') {
            title.textContent = '⏳ Reportar Retraso';
            body.innerHTML = `
                <p class="text-sm text-gray-600">La entrega está retrasada. Puedes actualizar la nueva fecha estimada.</p>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Nueva fecha estimada</label>
                    <input type="date" id="ac-nueva-fecha" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Notas</label>
                    <textarea id="ac-notas" rows="2" placeholder="Motivo del retraso..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 resize-none"></textarea>
                </div>`;
            btn.className = 'px-5 py-2 text-white text-sm font-semibold rounded-lg bg-orange-500 hover:bg-orange-600 transition-colors';
            btn.textContent = 'Reportar retraso';

        } else if (type === 'opciones') {
            title.textContent = '⚠️ Problema con la entrega';
            body.innerHTML = `
                <p class="text-sm text-gray-600">Selecciona qué ocurrió con la entrega:</p>
                <div class="space-y-2">
                    <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="ac-tipo" value="parcial" class="text-blue-500"> 
                        <div><p class="font-medium text-sm text-gray-800">Llegada Parcial</p><p class="text-xs text-gray-500">Algunos productos llegaron, otros no</p></div>
                    </label>
                    <label class="flex items-center gap-3 p-3 border rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                        <input type="radio" name="ac-tipo" value="no_recibida" class="text-red-500">
                        <div><p class="font-medium text-sm text-gray-800">No Recibido</p><p class="text-xs text-gray-500">Los productos no llegaron. Se restaurará el stock.</p></div>
                    </label>
                </div>
                <div id="ac-parcial-fields" class="hidden space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Cantidad recibida (de ${cantidad})</label>
                        <input type="number" id="ac-cantidad-recibida" min="1" max="${cantidad - 1}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400" placeholder="Ej: ${Math.floor(cantidad/2)}">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1">Notas (opcional)</label>
                    <textarea id="ac-notas" rows="2" placeholder="Descripción del problema..." class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none resize-none"></textarea>
                </div>`;

            // Show/hide partial fields
            setTimeout(() => {
                document.querySelectorAll('input[name="ac-tipo"]').forEach(r => {
                    r.addEventListener('change', () => {
                        document.getElementById('ac-parcial-fields').classList.toggle('hidden', r.value !== 'parcial');
                    });
                });
            }, 50);

            btn.className = 'px-5 py-2 text-white text-sm font-semibold rounded-lg bg-red-500 hover:bg-red-600 transition-colors';
            btn.textContent = 'Confirmar';
        }

        document.getElementById('actionModal').classList.remove('hidden');
    }

    document.getElementById('actionModal-confirm').addEventListener('click', async () => {
        const btn = document.getElementById('actionModal-confirm');
        btn.disabled = true;
        btn.textContent = 'Procesando...';

        let endpoint = '';
        let payload = {};

        if (currentActionType === 'confirmar') {
            endpoint = `${baseUrl}/entries.php/confirmar/${currentActionEntryId}`;
            payload = {
                fecha_entrega_real: document.getElementById('ac-fecha')?.value,
                notas: document.getElementById('ac-notas')?.value
            };
        } else if (currentActionType === 'retraso') {
            endpoint = `${baseUrl}/entries.php/reportarRetraso/${currentActionEntryId}`;
            payload = {
                nueva_fecha: document.getElementById('ac-nueva-fecha')?.value,
                notas: document.getElementById('ac-notas')?.value
            };
        } else if (currentActionType === 'opciones') {
            const tipo = document.querySelector('input[name="ac-tipo"]:checked')?.value;
            if (!tipo) { showToast('Selecciona una opción.', 'warning'); btn.disabled = false; btn.textContent = 'Confirmar'; return; }

            if (tipo === 'parcial') {
                endpoint = `${baseUrl}/entries.php/reportarParcial/${currentActionEntryId}`;
                payload = { cantidad_recibida: parseInt(document.getElementById('ac-cantidad-recibida')?.value), notas: document.getElementById('ac-notas')?.value };
                if (!payload.cantidad_recibida) { showToast('Ingresa la cantidad recibida.', 'warning'); btn.disabled = false; btn.textContent = 'Confirmar'; return; }
            } else {
                endpoint = `${baseUrl}/entries.php/reportarNoRecibido/${currentActionEntryId}`;
                payload = { notas: document.getElementById('ac-notas')?.value };
            }
        }

        try {
            const res = await fetch(endpoint, { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify(payload) });
            const data = await res.json();
            closeModal('actionModal');
            showToast(data.message, data.success ? 'success' : 'error');
            if (data.success) setTimeout(() => location.reload(), 1800);
        } catch(e) {
            showToast('Error de conexión.', 'error');
            btn.disabled = false;
            btn.textContent = 'Confirmar';
        }
    });
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>