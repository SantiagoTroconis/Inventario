<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <?php foreach ($kpis as $k): ?>
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider"><?php echo $k['label']; ?></h3>
                <div class="w-10 h-10 rounded-lg bg-<?php echo str_replace('-', '', explode('-', $k['color'])[0]); ?>-50 text-<?php echo $k['color']; ?> flex items-center justify-center text-lg">
                    <i class="fa-solid <?php echo $k['icon']; ?>"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?php echo $k['value']; ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Main Content Area - Overview -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Quick Actions -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Acciones Rápidas</h2>
            <div class="flex flex-wrap gap-3">
                <?php if ($tipo_usuario === 'Administrador'): ?>
                    <a href="solicitudes/nueva" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <i class="fa-solid fa-plus"></i> Nueva Solicitud
                    </a>
                    <a href="entradas/registrar" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-arrow-down text-emerald-500"></i> Registrar Entrada
                    </a>
                    <a href="salidas/registrar" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-arrow-up text-amber-500"></i> Registrar Salida
                    </a>
                    <a href="productos/nuevo" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-tag text-purple-500"></i> Nuevo Producto
                    </a>
                <?php elseif ($tipo_usuario === 'Sucursal'): ?>
                    <a href="solicitudes/nueva" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <i class="fa-solid fa-plus"></i> Nueva Solicitud
                    </a>
                    <a href="entradas/registrar" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-arrow-down text-emerald-500"></i> Registrar Entrada
                    </a>
                    <a href="salidas/registrar" class="flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-arrow-up text-amber-500"></i> Registrar Salida
                    </a>
                <?php else: ?>
                    <a href="solicitudes/nueva" class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                        <i class="fa-solid fa-plus"></i> Nueva Solicitud
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Activity (role-aware) -->
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Actividad Reciente</h2>

                <a href="reportes/log" class="mb-4 flex items-center gap-2 px-4 py-2 text-black text-sm font-medium rounded-lg transition-colors shadow-sm border border-gray-200">
                    <i class="fa-solid fa-list"></i>
                </a>
            </div>
            <div class="space-y-4">
                <!-- Static content for now, ideally dynamic -->
                <div class="flex items-start gap-4 pb-4 border-b border-gray-50 last:border-0 last:pb-0">
                    <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                        <i class="fa-solid fa-info text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-800 font-medium">Sistema actualizado a MVC v1.0</p>
                        <p class="text-xs text-gray-500">Hace un momento</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>