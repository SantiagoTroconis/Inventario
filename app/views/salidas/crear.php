<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Registrar Salida Manual</h2>
            <p class="text-gray-500 text-sm">Registre salidas por merma, uso interno, robo u otros motivos.</p>
        </div>
        <a href="<?php echo URL_BASE; ?>/exits.php" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
            <i class="fa-solid fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <i class="fa-solid fa-triangle-exclamation text-yellow-600 text-lg mt-0.5"></i>
            <div>
                <h3 class="font-semibold text-yellow-800">Importante</h3>
                <p class="text-sm text-yellow-700 mt-1">
                    Las salidas manuales reducen el stock del producto de forma permanente. 
                    Solo use esta función para registrar mermas, robos, uso interno u otros motivos especiales.
                </p>
            </div>
        </div>
    </div>

    <form method="POST" action="<?php echo URL_BASE; ?>/exits.php/crear" class="space-y-6">
        <!-- Producto -->
        <div>
            <label for="producto_id" class="block text-sm font-semibold text-gray-700 mb-2">
                Producto <span class="text-red-500">*</span>
            </label>
            <select name="producto_id" id="producto_id" required 
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                    onchange="updateStockInfo()">
                <option value="">Seleccione un producto</option>
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?php echo $p->id; ?>" 
                                data-stock="<?php echo $p->stock; ?>"
                                data-nombre="<?php echo htmlspecialchars($p->nombre); ?>"
                                data-codigo="<?php echo htmlspecialchars($p->codigo); ?>">
                            <?php echo htmlspecialchars($p->codigo); ?> - <?php echo htmlspecialchars($p->nombre); ?> (Stock: <?php echo number_format($p->stock); ?>)
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <p class="mt-1 text-xs text-gray-500">Seleccione el producto del cual se registrará la salida</p>
        </div>

        <!-- Stock Disponible Info -->
        <div id="stockInfo" class="hidden bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-900">Stock Disponible</p>
                    <p id="stockProducto" class="text-xs text-blue-700 mt-0.5"></p>
                </div>
                <p id="stockCantidad" class="text-3xl font-bold text-blue-700"></p>
            </div>
        </div>

        <!-- Cantidad -->
        <div>
            <label for="cantidad" class="block text-sm font-semibold text-gray-700 mb-2">
                Cantidad <span class="text-red-500">*</span>
            </label>
            <input type="number" name="cantidad" id="cantidad" required min="1" step="1"
                   placeholder="Ingrese la cantidad"
                   onchange="validateStock()"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm">
            <p class="mt-1 text-xs text-gray-500">Cantidad de unidades que salen del inventario</p>
            <p id="stockWarning" class="hidden mt-1 text-xs text-red-600">
                <i class="fa-solid fa-exclamation-circle"></i> La cantidad excede el stock disponible
            </p>
        </div>

        <!-- Comentario / Motivo -->
        <div>
            <label for="comentario" class="block text-sm font-semibold text-gray-700 mb-2">
                Motivo / Comentario <span class="text-red-500">*</span>
            </label>
            <textarea name="comentario" id="comentario" rows="4" required
                      placeholder="Ej: Producto dañado en transporte, Uso interno para demostración, Robo, etc."
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm resize-none"></textarea>
            <p class="mt-1 text-xs text-gray-500">Describa detalladamente el motivo de la salida manual</p>
        </div>

        <!-- Summary Panel -->
        <div id="summaryPanel" class="hidden bg-gray-50 border border-gray-200 rounded-lg p-5">
            <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <i class="fa-solid fa-clipboard-check"></i> Resumen de la Salida
            </h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Producto:</span>
                    <span id="summaryProducto" class="font-semibold text-gray-900"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Cantidad a retirar:</span>
                    <span id="summaryCantidad" class="font-semibold text-red-600"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Stock actual:</span>
                    <span id="summaryStockActual" class="font-semibold text-gray-900"></span>
                </div>
                <div class="flex justify-between pt-2 border-t border-gray-300">
                    <span class="text-gray-600">Stock resultante:</span>
                    <span id="summaryStockResultante" class="font-bold text-lg text-blue-600"></span>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="<?php echo URL_BASE; ?>/exits.php" 
               class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors">
                Cancelar
            </a>
            <button type="submit" id="submitBtn" disabled
                    class="px-5 py-2.5 bg-red-600 hover:bg-red-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-check mr-2"></i> Registrar Salida
            </button>
        </div>
    </form>
</div>

<script>
    function updateStockInfo() {
        const select = document.getElementById('producto_id');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value) {
            const stock = selectedOption.getAttribute('data-stock');
            const nombre = selectedOption.getAttribute('data-nombre');
            const codigo = selectedOption.getAttribute('data-codigo');
            
            document.getElementById('stockInfo').classList.remove('hidden');
            document.getElementById('stockProducto').textContent = codigo + ' - ' + nombre;
            document.getElementById('stockCantidad').textContent = new Intl.NumberFormat().format(stock);
            
            validateStock();
        } else {
            document.getElementById('stockInfo').classList.add('hidden');
            document.getElementById('summaryPanel').classList.add('hidden');
        }
    }

    function validateStock() {
        const select = document.getElementById('producto_id');
        const selectedOption = select.options[select.selectedIndex];
        const cantidad = parseInt(document.getElementById('cantidad').value) || 0;
        const comentario = document.getElementById('comentario').value.trim();
        const submitBtn = document.getElementById('submitBtn');
        const stockWarning = document.getElementById('stockWarning');
        
        if (selectedOption.value && cantidad > 0) {
            const stock = parseInt(selectedOption.getAttribute('data-stock'));
            const nombre = selectedOption.getAttribute('data-nombre');
            const codigo = selectedOption.getAttribute('data-codigo');
            
            // Show summary
            document.getElementById('summaryPanel').classList.remove('hidden');
            document.getElementById('summaryProducto').textContent = codigo + ' - ' + nombre;
            document.getElementById('summaryCantidad').textContent = new Intl.NumberFormat().format(cantidad);
            document.getElementById('summaryStockActual').textContent = new Intl.NumberFormat().format(stock);
            document.getElementById('summaryStockResultante').textContent = new Intl.NumberFormat().format(stock - cantidad);
            
            // Validate
            if (cantidad > stock) {
                stockWarning.classList.remove('hidden');
                submitBtn.disabled = true;
            } else {
                stockWarning.classList.add('hidden');
                // Enable submit only if all required fields are filled
                submitBtn.disabled = !(selectedOption.value && cantidad > 0 && comentario.length > 0);
            }
        } else {
            document.getElementById('summaryPanel').classList.add('hidden');
            stockWarning.classList.add('hidden');
            submitBtn.disabled = true;
        }
    }

    // Add event listener to comentario
    document.getElementById('comentario').addEventListener('input', validateStock);
    document.getElementById('cantidad').addEventListener('input', validateStock);
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
