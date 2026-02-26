<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php $baseUrl = URL_BASE; ?>

<!-- Page Header -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Sucursales</h2>
            <p class="text-gray-500 text-sm mt-0.5">Gestiona las sucursales y su estado operativo.</p>
        </div>
        <button onclick="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
            <i class="fa-solid fa-plus"></i> Nueva Sucursal
        </button>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
    <?php
    $total   = count($sucursales);
    $activas = count(array_filter((array)$sucursales, fn($s) => $s->estado === 'Activa'));
    $totalAgentes = array_sum(array_column((array)$sucursales, 'total_agentes'));
    ?>
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center"><i class="fa-solid fa-building text-blue-600"></i></div>
        <div><p class="text-xs text-gray-500">Total Sucursales</p><p class="text-xl font-bold text-gray-900"><?php echo $total; ?></p></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center"><i class="fa-solid fa-circle-check text-emerald-600"></i></div>
        <div><p class="text-xs text-gray-500">Activas</p><p class="text-xl font-bold text-gray-900"><?php echo $activas; ?></p></div>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center"><i class="fa-solid fa-users text-indigo-600"></i></div>
        <div><p class="text-xs text-gray-500">Total Agentes</p><p class="text-xl font-bold text-gray-900"><?php echo $totalAgentes; ?></p></div>
    </div>
</div>

<!-- Table -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <table id="sucursalesTable" class="w-full text-sm">
        <thead class="text-xs text-gray-500 uppercase bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left font-semibold">Sucursal</th>
                <th class="px-4 py-3 text-left font-semibold">Ciudad</th>
                <th class="px-4 py-3 text-left font-semibold">Dirección</th>
                <th class="px-4 py-3 text-left font-semibold">Contacto</th>
                <th class="px-4 py-3 text-left font-semibold">Teléfono</th>
                <th class="px-4 py-3 text-left font-semibold">Estado</th>
                <th class="px-4 py-3 text-left font-semibold">Agentes</th>
                <th class="px-4 py-3 text-left font-semibold">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            <?php foreach ($sucursales as $s): ?>
            <tr class="hover:bg-gray-50 transition-colors" id="row-<?php echo $s->id; ?>">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            <?php echo strtoupper(substr($s->nombre, 0, 1)); ?>
                        </div>
                        <span class="font-semibold text-gray-900"><?php echo htmlspecialchars($s->nombre); ?></span>
                    </div>
                </td>
                <td class="px-4 py-3 text-gray-700"><?php echo htmlspecialchars($s->ciudad ?? '—'); ?></td>
                <td class="px-4 py-3 text-gray-600 max-w-xs truncate"><?php echo htmlspecialchars($s->direccion ?? '—'); ?></td>
                <td class="px-4 py-3 text-gray-700"><?php echo htmlspecialchars($s->contacto ?? '—'); ?></td>
                <td class="px-4 py-3 text-gray-600"><?php echo htmlspecialchars($s->telefono ?? '—'); ?></td>
                <td class="px-4 py-3" id="status-<?php echo $s->id; ?>">
                    <?php if ($s->estado === 'Activa'): ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activa
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactiva
                    </span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-indigo-50 text-indigo-700 border-indigo-100">
                        <?php echo $s->total_agentes; ?> <?php echo $s->total_agentes == 1 ? 'agente' : 'agentes'; ?>
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1">
                        <button onclick='editSucursal(<?php echo json_encode($s); ?>)' title="Editar"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors">
                            <i class="fa-solid fa-pen text-xs"></i>
                        </button>
                        <button onclick="toggleSucursal(<?php echo $s->id; ?>, '<?php echo $s->estado; ?>')" title="Cambiar estado"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-amber-50 hover:text-amber-600 transition-colors">
                            <i class="fa-solid fa-power-off text-xs"></i>
                        </button>
                        <button onclick="deleteSucursal(<?php echo $s->id; ?>, '<?php echo htmlspecialchars($s->nombre); ?>')" title="Eliminar"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <i class="fa-solid fa-trash text-xs"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($sucursales)): ?>
            <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400">
                <i class="fa-solid fa-building text-3xl mb-3 block opacity-30"></i>
                No hay sucursales registradas todavía.
            </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Create / Edit Modal -->
<div id="sucursalModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white z-10">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i class="fa-solid fa-building text-blue-600"></i>
                </div>
                <h3 class="font-bold text-gray-900" id="modalTitle">Nueva Sucursal</h3>
            </div>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <div class="px-6 py-5 space-y-4">
            <input type="hidden" id="editId">

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" id="fNombre" placeholder="Ej. Sucursal Centro"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Ciudad</label>
                    <input type="text" id="fCiudad" placeholder="Ciudad"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Teléfono</label>
                    <input type="text" id="fTelefono" placeholder="+52 55 0000 0000"
                        class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Dirección</label>
                <input type="text" id="fDireccion" placeholder="Calle, colonia, número..."
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Persona de contacto</label>
                <input type="text" id="fContacto" placeholder="Nombre del responsable"
                    class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
                <p class="text-xs font-semibold text-blue-700 mb-3 flex items-center gap-2">
                    <i class="fa-solid fa-key"></i> Acceso al sistema
                </p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Correo</label>
                        <input type="email" id="fCorreo" placeholder="correo@sucursal.com"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">
                            Contraseña <span id="passRequired" class="text-red-500">*</span>
                            <span id="passHint" class="hidden text-gray-400 font-normal text-xs">(dejar vacío para no cambiar)</span>
                        </label>
                        <input type="password" id="fPassword" placeholder="Contraseña de acceso"
                            class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white">
                    </div>
                </div>
            </div>

            <div id="modalError" class="hidden p-3 bg-red-50 text-red-700 border border-red-200 rounded-lg text-sm"></div>
        </div>

        <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">Cancelar</button>
            <button onclick="saveSucursal()" id="saveBtn" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-check mr-1.5"></i>Guardar
            </button>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="hidden fixed bottom-6 right-6 z-[100] flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-xl text-sm font-medium text-white">
    <i id="toastIcon" class="fa-solid text-base"></i>
    <span id="toastMsg"></span>
</div>

<script>
const baseUrl = '<?php echo $baseUrl; ?>';
let editingId = null;

function openModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Nueva Sucursal';
    ['fNombre','fCiudad','fTelefono','fDireccion','fContacto','fCorreo','fPassword'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('editId').value = '';
    document.getElementById('passRequired').classList.remove('hidden');
    document.getElementById('passHint').classList.add('hidden');
    document.getElementById('modalError').classList.add('hidden');
    document.getElementById('sucursalModal').classList.remove('hidden');
}

function editSucursal(s) {
    editingId = s.id;
    document.getElementById('modalTitle').textContent = 'Editar Sucursal';
    document.getElementById('editId').value = s.id;
    document.getElementById('fNombre').value   = s.nombre   ?? '';
    document.getElementById('fCiudad').value   = s.ciudad   ?? '';
    document.getElementById('fTelefono').value = s.telefono ?? '';
    document.getElementById('fDireccion').value= s.direccion?? '';
    document.getElementById('fContacto').value = s.contacto ?? '';
    document.getElementById('fCorreo').value   = s.correo   ?? '';
    document.getElementById('fPassword').value = '';
    document.getElementById('passRequired').classList.add('hidden');
    document.getElementById('passHint').classList.remove('hidden');
    document.getElementById('modalError').classList.add('hidden');
    document.getElementById('sucursalModal').classList.remove('hidden');
}

function closeModal() { document.getElementById('sucursalModal').classList.add('hidden'); }

function saveSucursal() {
    const nombre    = document.getElementById('fNombre').value.trim();
    const password  = document.getElementById('fPassword').value;
    const errDiv    = document.getElementById('modalError');

    if (!nombre || (!editingId && !password)) {
        errDiv.textContent = 'Por favor completa los campos requeridos (nombre' + (!editingId ? ', contraseña' : '') + ').';
        errDiv.classList.remove('hidden');
        return;
    }
    errDiv.classList.add('hidden');

    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i>Guardando...';

    const payload = {
        nombre:    nombre,
        ciudad:    document.getElementById('fCiudad').value.trim(),
        telefono:  document.getElementById('fTelefono').value.trim(),
        direccion: document.getElementById('fDireccion').value.trim(),
        contacto:  document.getElementById('fContacto').value.trim(),
        correo:    document.getElementById('fCorreo').value.trim(),
    };
    if (password) payload.password = password;

    const url = editingId
        ? `${baseUrl}/sucursales.php/editar/${editingId}`
        : `${baseUrl}/sucursales.php/crear`;

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

function toggleSucursal(id, currentEstado) {
    if (!confirm(`¿${currentEstado === 'Activa' ? 'Desactivar' : 'Activar'} esta sucursal?`)) return;
    fetch(`${baseUrl}/sucursales.php/toggle/${id}`, { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const cell = document.getElementById(`status-${id}`);
            if (data.estado === 'Activa') {
                cell.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Activa</span>`;
            } else {
                cell.innerHTML = `<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500 border border-gray-200"><span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>Inactiva</span>`;
            }
            showToast(`Sucursal ${data.estado}`, 'success');
        } else {
            showToast('Error al cambiar estado', 'error');
        }
    });
}

function deleteSucursal(id, nombre) {
    if (!confirm(`¿Eliminar la sucursal "${nombre}"?\n\nSolo es posible si no tiene agentes asignados.`)) return;
    fetch(`${baseUrl}/sucursales.php/eliminar/${id}`, { method: 'POST' })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById(`row-${id}`)?.remove();
            showToast('Sucursal eliminada.', 'success');
        } else {
            showToast(data.message || 'No se puede eliminar.', 'error');
        }
    });
}

function showToast(msg, type = 'success') {
    const t = document.getElementById('toast');
    const icon = document.getElementById('toastIcon');
    t.className = 'fixed bottom-6 right-6 z-[100] flex items-center gap-3 px-5 py-3.5 rounded-xl shadow-xl text-sm font-medium text-white';
    if (type === 'success') { t.classList.add('bg-emerald-600'); icon.className = 'fa-solid fa-check-circle text-base'; }
    else { t.classList.add('bg-red-600'); icon.className = 'fa-solid fa-circle-xmark text-base'; }
    document.getElementById('toastMsg').textContent = msg;
    setTimeout(() => t.classList.add('hidden'), 3000);
}

document.addEventListener('DOMContentLoaded', () => {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('#sucursalesTable').DataTable({
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            columnDefs: [{ orderable: false, targets: 7 }],
            pageLength: 15,
        });
    }
});
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>