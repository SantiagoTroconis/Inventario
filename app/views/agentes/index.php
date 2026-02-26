<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php
$baseUrl = URL_BASE;
$sucursalesList = $sucursales; // for dropdowns
$readonly = $isSucursal ?? false;
?>


<!-- Page Header -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900"><?php echo $readonly ? 'Mis Agentes' : 'Agentes'; ?></h2>
            <p class="text-gray-500 text-sm mt-0.5"><?php echo $readonly ? 'Agentes asignados a tu sucursal.' : 'Gestiona las cuentas de agentes del sistema.'; ?></p>
        </div>
        <?php if (!$readonly): ?>
        <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-user-plus"></i> Nuevo Agente
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Row -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <?php
    $total    = count($agentes);
    $activos  = count(array_filter((array)$agentes, fn($a) => $a->status == 1));
    $inactivos= $total - $activos;
    $sCount   = count(array_unique(array_column((array)$agentes, 'sucursal_id')));
    ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center"><i class="fa-solid fa-users text-blue-600"></i></div>
        <div><p class="text-xs text-gray-500">Total Agentes</p><p class="text-xl font-bold text-gray-900"><?php echo $total; ?></p></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center"><i class="fa-solid fa-circle-check text-emerald-600"></i></div>
        <div><p class="text-xs text-gray-500">Activos</p><p class="text-xl font-bold text-gray-900"><?php echo $activos; ?></p></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center"><i class="fa-solid fa-circle-xmark text-gray-500"></i></div>
        <div><p class="text-xs text-gray-500">Inactivos</p><p class="text-xl font-bold text-gray-900"><?php echo $inactivos; ?></p></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center"><i class="fa-solid fa-building text-purple-600"></i></div>
        <div><p class="text-xs text-gray-500">Sucursales</p><p class="text-xl font-bold text-gray-900"><?php echo $sCount; ?></p></div>
    </div>
</div>

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <table id="agentesTable" class="w-full text-sm">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Agente</th>
                <th class="px-4 py-3 text-left font-semibold">Rol</th>
                <th class="px-4 py-3 text-left font-semibold">Sucursal</th>
                <th class="px-4 py-3 text-left font-semibold">Contacto</th>
                <th class="px-4 py-3 text-left font-semibold">Estado</th>
                <?php if (!$readonly): ?>
                <th class="px-4 py-3 text-left font-semibold">Acciones</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($agentes as $a): ?>
            <tr class="hover:bg-gray-50 transition-colors" id="row-<?php echo $a->id; ?>">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            <?php echo strtoupper(substr($a->nombre, 0, 1)); ?>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900"><?php echo htmlspecialchars($a->nombre); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($a->correo ?? '—'); ?></p>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        <?php echo htmlspecialchars($a->rol); ?>
                    </span>
                </td>
                <td class="px-4 py-3 text-gray-700 font-medium"><?php echo htmlspecialchars($a->sucursal_nombre ?? '—'); ?></td>
                <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($a->telefono ?? '—'); ?></td>
                <td class="px-4 py-3" id="status-<?php echo $a->id; ?>">
                    <?php if ($a->status == 1): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactivo
                    </span>
                    <?php endif; ?>
                </td>
                <?php if (!$readonly): ?>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1">
                        <button onclick='editAgente(<?php echo json_encode($a); ?>)' title="Editar"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <button onclick="toggleAgente(<?php echo $a->id; ?>, <?php echo $a->status; ?>)" title="Cambiar estado"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                            <i class="fa-solid fa-power-off text-xs"></i>
                        </button>
                        <button onclick="deleteAgente(<?php echo $a->id; ?>, '<?php echo htmlspecialchars($a->nombre); ?>')" title="Eliminar"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($agentes)): ?>
            <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">
                <i class="fa-solid fa-users text-3xl mb-3 block opacity-30"></i>
                No hay agentes registrados todavía.
            </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Create / Edit Modal -->
<div id="agenteModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i class="fa-solid fa-user-tie text-blue-600"></i>
                </div>
                <h3 class="font-bold text-gray-900" id="modalTitle">Nuevo Agente</h3>
            </div>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-5 space-y-4">
            <input type="hidden" id="editId">

            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                    <input type="text" id="fNombre" placeholder="Ej. Carlos López"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Correo</label>
                    <input type="email" id="fCorreo" placeholder="correo@ejemplo.com"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                    <input type="text" id="fTelefono" placeholder="+52 55 0000 0000"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Rol <span class="text-red-500">*</span></label>
                    <select id="fRol" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                        <option value="Agente">Agente</option>
                        <option value="Supervisor">Supervisor</option>
                        <option value="Coordinador">Coordinador</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Sucursal <span class="text-red-500">*</span></label>
                    <select id="fSucursal" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach ($sucursalesList as $s): ?>
                        <option value="<?php echo $s->id; ?>"><?php echo htmlspecialchars($s->nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">
                        Contraseña <span id="passRequired" class="text-red-500">*</span>
                        <span id="passHint" class="hidden text-gray-400 font-normal text-xs">(dejar vacío para no cambiar)</span>
                    </label>
                    <input type="password" id="fPassword" placeholder="Contraseña de acceso"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <div id="modalError" class="hidden p-3 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm"></div>
        </div>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">Cancelar</button>
            <button onclick="saveAgente()" id="saveBtn" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-check mr-1.5"></i>Guardar
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="hidden fixed bottom-6 right-6 z-[100] flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-xl text-sm font-medium text-white transition-all duration-300">
    <i id="toastIcon" class="fa-solid text-base"></i>
    <span id="toastMsg"></span>
</div>

<script>
const baseUrl = '<?php echo $baseUrl; ?>';
let editingId = null;

function openModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Nuevo Agente';
    document.getElementById('editId').value = '';
    document.getElementById('fNombre').value = '';
    document.getElementById('fCorreo').value = '';
    document.getElementById('fTelefono').value = '';
    document.getElementById('fRol').value = 'Agente';
    document.getElementById('fSucursal').value = '';
    document.getElementById('fPassword').value = '';
    document.getElementById('passRequired').classList.remove('hidden');
    document.getElementById('passHint').classList.add('hidden');
    document.getElementById('modalError').classList.add('hidden');
    document.getElementById('agenteModal').classList.remove('hidden');
}

function editAgente(a) {
    editingId = a.id;
    document.getElementById('modalTitle').textContent = 'Editar Agente';
    document.getElementById('editId').value = a.id;
    document.getElementById('fNombre').value = a.nombre ?? '';
    document.getElementById('fCorreo').value = a.correo ?? '';
    document.getElementById('fTelefono').value = a.telefono ?? '';
    document.getElementById('fRol').value = a.rol ?? 'Agente';
    document.getElementById('fSucursal').value = a.sucursal_id ?? '';
    document.getElementById('fPassword').value = '';
    document.getElementById('passRequired').classList.add('hidden');
    document.getElementById('passHint').classList.remove('hidden');
    document.getElementById('modalError').classList.add('hidden');
    document.getElementById('agenteModal').classList.remove('hidden');
}

function closeModal() { document.getElementById('agenteModal').classList.add('hidden'); }

function saveAgente() {
    const nombre     = document.getElementById('fNombre').value.trim();
    const correo     = document.getElementById('fCorreo').value.trim();
    const telefono   = document.getElementById('fTelefono').value.trim();
    const rol        = document.getElementById('fRol').value;
    const sucursalId = document.getElementById('fSucursal').value;
    const password   = document.getElementById('fPassword').value;
    const errDiv     = document.getElementById('modalError');

    if (!nombre || !sucursalId || (!editingId && !password)) {
        errDiv.textContent = 'Por favor completa los campos requeridos (nombre, sucursal' + (!editingId ? ', contraseña' : '') + ').';
        errDiv.classList.remove('hidden');
        return;
    }
    errDiv.classList.add('hidden');

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Guardando...';

    const payload = { nombre, correo, telefono, rol, sucursal_id: sucursalId };
    if (password) payload.password = password;

    const url = editingId
        ? `${baseUrl}/agentes.php/editar/${editingId}`
        : `${baseUrl}/agentes.php/crear`;

    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeModal();
            showToast(data.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            errDiv.textContent = data.message || 'Error al guardar.';
            errDiv.classList.remove('hidden');
        }
    })
    .catch(() => { errDiv.textContent = 'Error de conexión.'; errDiv.classList.remove('hidden'); })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-check mr-1.5"></i>Guardar';
    });
}

function toggleAgente(id, currentStatus) {
    if (!confirm(`¿${currentStatus == 1 ? 'Desactivar' : 'Activar'} este agente?`)) return;
    fetch(`${baseUrl}/agentes.php/toggle/${id}`, { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const cell = document.getElementById(`status-${id}`);
            if (data.status == 1) {
                cell.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activo</span>`;
            } else {
                cell.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactivo</span>`;
            }
            showToast(`Agente ${data.label}`, 'success');
        } else {
            showToast('Error al cambiar estado', 'error');
        }
    });
}

function deleteAgente(id, nombre) {
    if (!confirm(`¿Eliminar permanentemente al agente "${nombre}"? Esta acción no se puede deshacer.`)) return;
    fetch(`${baseUrl}/agentes.php/eliminar/${id}`, { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`row-${id}`)?.remove();
            showToast('Agente eliminado.', 'success');
        } else {
            showToast(data.message || 'Error al eliminar.', 'error');
        }
    });
}

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    const msgEl = document.getElementById('toastMsg');
    t.className = t.className.replace(/bg-\S+/g, '');
    if (type === 'success') { t.classList.add('bg-emerald-600'); icon.className = 'fa-solid fa-check-circle text-base'; }
    else { t.classList.add('bg-red-600'); icon.className = 'fa-solid fa-circle-xmark text-base'; }
    msgEl.textContent = msg;
    t.classList.remove('hidden');
    setTimeout(() => t.classList.add('hidden'), 3000);
}

// Init DataTable
document.addEventListener('DOMContentLoaded', () => {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#agentesTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            columnDefs: [{ orderable: false, targets: 5 }],
            pageLength: 15,
        });
    }
});
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>