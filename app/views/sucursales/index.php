<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Sucursales</h2>
            <p class="text-gray-500 text-sm">Gestiona las sucursales, contactos y estado operativo.</p>
        </div>
        <div class="flex gap-3">
            <a href="nueva" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-plus"></i> Nueva Sucursal
            </a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 font-semibold">ID</th>
                    <th class="px-6 py-4 font-semibold">Nombre</th>
                    <th class="px-6 py-4 font-semibold">Dirección</th>
                    <th class="px-6 py-4 font-semibold">Ciudad</th>
                    <th class="px-6 py-4 font-semibold">Contacto</th>
                    <th class="px-6 py-4 font-semibold">Teléfono</th>
                    <th class="px-6 py-4 font-semibold">Estado</th>
                    <th class="px-6 py-4 font-semibold">Empleados</th>
                    <th class="px-6 py-4 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php foreach ($sucursales as $s): ?>
                    <?php
                    $statusDot = match ($s['estado_class']) {
                        'active' => 'bg-emerald-500',
                        'inactive' => 'bg-gray-400',
                        default => 'bg-gray-400',
                    };
                    $statusText = match ($s['estado_class']) {
                        'active' => 'text-emerald-700',
                        'inactive' => 'text-gray-500',
                        default => 'text-gray-500',
                    };
                    ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-mono text-blue-600 bg-blue-50 px-2.5 py-1 rounded text-xs font-medium border border-blue-100"><?php echo $s['id']; ?></span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900"><?php echo $s['nombre']; ?></td>
                        <td class="px-6 py-4 text-gray-700"><?php echo $s['direccion']; ?></td>
                        <td class="px-6 py-4"><?php echo $s['ciudad']; ?></td>
                        <td class="px-6 py-4"><?php echo $s['contacto']; ?></td>
                        <td class="px-6 py-4"><?php echo $s['telefono']; ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full <?php echo $statusDot; ?>"></span>
                                <span class="font-medium <?php echo $statusText; ?>"><?php echo $s['estado']; ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border bg-gray-50 text-gray-600 border-gray-200"><?php echo $s['empleados']; ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="#" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors border border-transparent hover:border-blue-100"><i class="fa-solid fa-pen"></i></a>
                                <a href="#" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors border border-transparent hover:border-gray-200"><i class="fa-solid fa-eye"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="flex justify-between items-center mt-6 pt-6 border-t border-gray-100">
        <span class="text-sm text-gray-500">Mostrando <span class="text-gray-900 font-medium">1-3</span> de <span class="text-gray-900 font-medium">3</span> sucursales</span>
        <div class="flex items-center gap-2">
            <button class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-400 cursor-not-allowed bg-gray-50"><i class="fa-solid fa-chevron-left"></i></button>
            <button class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-600 border border-blue-600 text-white font-medium shadow-sm">1</button>
            <button class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition-colors">2</button>
            <button class="w-9 h-9 flex items-center justify-center rounded-lg border border-gray-200 text-gray-600 hover:border-blue-500 hover:text-blue-600 hover:bg-blue-50 transition-colors"><i class="fa-solid fa-chevron-right"></i></button>
        </div>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>