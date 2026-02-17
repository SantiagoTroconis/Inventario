<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Detalle de Entrada</h2>
            <p class="text-gray-500 text-sm">Información completa de la entrada de inventario.</p>
        </div>
        <a href="<?php echo URL_BASE; ?>/entries" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>

    <?php if (isset($entrada) && $entrada): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- ID de Entrada -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">ID de Entrada</label>
                <p class="text-lg font-mono font-bold text-blue-600">#<?php echo str_pad($entrada->id, 4, '0', STR_PAD_LEFT); ?></p>
            </div>

            <!-- Fecha de Registro -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Fecha de Registro</label>
                <p class="text-lg font-semibold text-gray-800">
                    <?php echo date('d/m/Y H:i', strtotime($entrada->fecha_creacion)); ?>
                </p>
            </div>

            <!-- Producto -->
            <div class="col-span-1 md:col-span-2 bg-blue-50 rounded-lg p-5 border border-blue-200">
                <label class="block text-xs font-semibold text-blue-700 uppercase mb-2">
                    <i class="fa-solid fa-box"></i> Producto
                </label>
                <p class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($entrada->producto_nombre); ?></p>
                <div class="flex items-center gap-4 mt-2">
                    <span class="text-sm text-gray-600">
                        <span class="font-semibold">Código:</span> 
                        <span class="font-mono bg-white px-2 py-0.5 rounded border border-blue-200"><?php echo htmlspecialchars($entrada->producto_codigo); ?></span>
                    </span>
                    <?php if (!empty($entrada->producto_categoria)): ?>
                        <span class="text-sm text-gray-600">
                            <span class="font-semibold">Categoría:</span> <?php echo htmlspecialchars($entrada->producto_categoria); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <?php if (!empty($entrada->producto_descripcion)): ?>
                    <p class="text-sm text-gray-600 mt-2"><?php echo htmlspecialchars($entrada->producto_descripcion); ?></p>
                <?php endif; ?>
            </div>

            <!-- Cantidad -->
            <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                <label class="block text-xs font-semibold text-green-700 uppercase mb-1">
                    <i class="fa-solid fa-arrow-up"></i> Cantidad Ingresada
                </label>
                <p class="text-3xl font-bold text-green-700"><?php echo number_format($entrada->cantidad); ?></p>
                <p class="text-xs text-green-600 mt-1">unidades</p>
            </div>

            <!-- Proveedor -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                    <i class="fa-solid fa-truck"></i> Proveedor
                </label>
                <?php if (!empty($entrada->proveedor)): ?>
                    <p class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($entrada->proveedor); ?></p>
                <?php else: ?>
                    <p class="text-lg text-gray-400 italic">No especificado</p>
                <?php endif; ?>
            </div>

            <!-- Referencia -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                    <i class="fa-solid fa-file-invoice"></i> Referencia / N° Factura
                </label>
                <?php if (!empty($entrada->referencia)): ?>
                    <p class="text-lg font-mono font-semibold text-gray-800"><?php echo htmlspecialchars($entrada->referencia); ?></p>
                <?php else: ?>
                    <p class="text-lg text-gray-400 italic">Sin referencia</p>
                <?php endif; ?>
            </div>

            <!-- Usuario que registró -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">
                    <i class="fa-solid fa-user"></i> Registrado Por
                </label>
                <p class="text-lg font-semibold text-gray-800">
                    <?php echo htmlspecialchars($entrada->usuario_nombre); ?>
                </p>
                <?php if (!empty($entrada->usuario_correo)): ?>
                    <p class="text-sm text-gray-500 mt-1">
                        <i class="fa-solid fa-envelope text-xs"></i> <?php echo htmlspecialchars($entrada->usuario_correo); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'Admin'): ?>
            <div class="mt-6 pt-6 border-t border-gray-200 flex items-center gap-3">
                <a href="<?php echo URL_BASE; ?>/entries/eliminar/<?php echo $entrada->id; ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                   onclick="return confirm('¿Está seguro de eliminar esta entrada? Esta acción no se puede deshacer.');">
                    <i class="fa-solid fa-trash"></i> Eliminar Entrada
                </a>
                <span class="text-xs text-gray-500">Esta acción es permanente y no se puede revertir</span>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="text-center py-12">
            <i class="fa-solid fa-exclamation-circle text-5xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">No se encontró la entrada solicitada</p>
            <a href="<?php echo URL_BASE; ?>/entries" class="inline-block mt-4 text-blue-600 hover:text-blue-700 font-medium">
                Volver a entradas
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
