<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>


<!-- Info Alert for Non-Admin Users -->
<?php if ($_SESSION['tipo_usuario'] !== 'Admin'): ?>
    <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-blue-500 rounded-lg p-5 shadow-sm">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-info-circle text-white text-lg"></i>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-bold text-blue-900 mb-1">Información Importante</h3>
                <p class="text-sm text-blue-800 leading-relaxed">
                    Solo puede registrar entradas para productos aprobados en sus solicitudes.
                </p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Main Form Card -->
<div class="bg-white rounded-xl border border-gray-200 shadow-lg overflow-hidden">
    <!-- Form Header -->
    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-5 border-b border-gray-200">
        <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
            <i class="fa-solid fa-clipboard-list text-green-600"></i>
            Datos de la Entrada
        </h2>
        <p class="text-sm text-gray-600 mt-1">Complete todos los campos requeridos (*)</p>
    </div>

    <form method="POST" action="<?php echo URL_BASE; ?>/entries.php/registrar" id="formEntrada" class="p-8">
        <div class="max-w-4xl mx-auto space-y-8">
            
            <!-- Section 1: Product Information -->
            <div class="space-y-6">
                <div class="flex items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-green-600 font-bold text-sm">1</span>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800">Información del Producto</h3>
                </div>

                <!-- Producto -->
                <div class="relative">
                    <label for="producto_id" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fa-solid fa-box text-green-600 mr-1"></i>
                        Producto <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="producto_id" id="producto_id" required 
                                class="w-full pl-12 pr-10 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all appearance-none bg-white hover:border-gray-300 text-gray-900 font-medium">
                            <option value="">Cargando productos...</option>
                        </select>
    
                        <div class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                            <i class="fa-solid fa-chevron-down"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 flex items-center gap-1" id="producto-help">
                        <i class="fa-solid fa-circle-info"></i>
                        <?php if ($_SESSION['tipo_usuario'] === 'Admin'): ?>
                            Seleccione el producto que está ingresando al inventario
                        <?php else: ?>
                            Solo se muestran productos de solicitudes aprobadas
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Alert for quantity info -->
                <div id="cantidad-info" class="hidden animate-fadeIn">
                    <div class="bg-gradient-to-r from-amber-50 to-yellow-50 border-l-4 border-amber-400 rounded-lg p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-amber-400 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-calculator text-white text-sm"></i>
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-amber-900">Cantidad Solicitada</p>
                                <p class="text-sm text-amber-800 mt-1">
                                    Debe registrar exactamente <span id="cantidad-requerida" class="font-bold text-amber-900 text-base"></span> unidades según la solicitud aprobada.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cantidad -->
                <div class="relative">
                    <label for="cantidad" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fa-solid fa-sort-numeric-up text-green-600 mr-1"></i>
                        Cantidad <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" name="cantidad" id="cantidad" min="1" required
                               class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all text-gray-900 font-medium hover:border-gray-300"
                               >
                        <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                            <i class="fa-solid fa-hashtag text-lg"></i>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                        <i class="fa-solid fa-circle-info"></i>
                        Cantidad de unidades que están ingresando al inventario
                    </p>
                </div>
            </div>

            <!-- Divider -->
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t-2 border-gray-200"></div>
                </div>
                <div class="relative flex justify-center">
                    <span class="bg-white px-4 text-sm text-gray-500 font-medium">Información Adicional</span>
                </div>
            </div>

            <!-- Section 2: Additional Information -->
            <div class="space-y-6">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <span class="text-blue-600 font-bold text-sm">2</span>
                    </div>
                    <h3 class="text-base font-semibold text-gray-800">Datos del Proveedor y Referencia</h3>
                    <span class="text-xs text-gray-500 italic">(Opcional)</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Proveedor -->
                    <div class="relative">
                        <label for="proveedor" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fa-solid fa-truck text-blue-600 mr-1"></i>
                            Proveedor
                        </label>
                        <div class="relative">
                            <input type="text" name="proveedor" id="proveedor" maxlength="100"
                                   class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all hover:border-gray-300"
                                   >
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fa-solid fa-building text-lg"></i>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                            <i class="fa-solid fa-circle-info"></i>
                            De dónde proviene el producto
                        </p>
                    </div>

                    <!-- Referencia -->
                    <div class="relative">
                        <label for="referencia" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fa-solid fa-file-invoice text-blue-600 mr-1"></i>
                            Referencia / N° Factura
                        </label>
                        <div class="relative">
                            <input type="text" name="referencia" id="referencia" maxlength="100"
                                   class="w-full pl-12 pr-4 py-3.5 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all hover:border-gray-300"
                                   >
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fa-solid fa-receipt text-lg"></i>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                            <i class="fa-solid fa-circle-info"></i>
                            Número de factura u orden de compra
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 pt-8 border-t-2 border-gray-200">
                <button type="submit" 
                        class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white text-sm font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fa-solid fa-check-circle text-lg"></i> 
                    Registrar Entrada
                </button>
                <a href="<?php echo URL_BASE; ?>/entries" 
                   class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-white hover:bg-gray-50 text-gray-700 text-sm font-bold rounded-xl transition-all border-2 border-gray-300 hover:border-gray-400">
                    <i class="fa-solid fa-times-circle"></i> 
                    Cancelar
                </a>
            </div>
        </div>
    </form>
</div>

<style>
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fadeIn {
        animation: fadeIn 0.3s ease-out;
    }
    
    /* Custom select arrow hide for Firefox */
    select {
        -moz-appearance: none;
        -webkit-appearance: none;
    }
    
    /* Input focus effects */
    input:focus, select:focus {
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
    }
    
    /* Hover effects for inputs */
    input:not(:focus):hover, select:not(:focus):hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
</style>

<script>
    let productosData = [];
    const tipoUsuario = '<?php echo $_SESSION['tipo_usuario'] ?? 'Agente'; ?>';

    // Load products via Fetch API
    document.addEventListener('DOMContentLoaded', function() {
        const selectProducto = document.getElementById('producto_id');
        const inputCantidad = document.getElementById('cantidad');
        const cantidadInfo = document.getElementById('cantidad-info');
        const cantidadRequerida = document.getElementById('cantidad-requerida');
        
        fetch('<?php echo URL_BASE; ?>/entries.php/getProductosDisponibles')
            .then(response => response.json())
            .then(data => {
                selectProducto.innerHTML = '<option value="">Seleccione un producto</option>';
                
                if (!data.success) {
                    selectProducto.innerHTML = '<option value="">Error al cargar productos</option>';
                    alert(data.message || 'Error al cargar productos');
                    return;
                }

                if (!data.data || data.data.length === 0) {
                    selectProducto.innerHTML = '<option value="">No hay productos disponibles</option>';
                    if (data.message) {
                        const noProductsMsg = document.createElement('div');
                        noProductsMsg.className = 'mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800';
                        noProductsMsg.innerHTML = '<i class="fa-solid fa-info-circle"></i> ' + data.message;
                        selectProducto.parentElement.appendChild(noProductsMsg);
                    }
                    return;
                }

                productosData = data.data;
                
                data.data.forEach(function(producto) {
                    const option = document.createElement('option');
                    option.value = producto.id;
                    option.dataset.cantidad = producto.cantidad_solicitada || '';
                    option.dataset.tipo = producto.tipo;
                    
                    let optionText = producto.codigo + ' - ' + producto.nombre;
                    if (producto.cantidad_solicitada) {
                        optionText += ' (Solicitado: ' + producto.cantidad_solicitada + ' unidades)';
                    }
                    
                    option.textContent = optionText;
                    selectProducto.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error loading products:', error);
                selectProducto.innerHTML = '<option value="">Error al cargar productos</option>';
                alert('Error al cargar los productos disponibles');
            });

        // Handle product selection
        selectProducto.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const cantidadSolicitada = selectedOption.dataset.cantidad;
            const tipo = selectedOption.dataset.tipo;

            if (tipo === 'solicitud' && cantidadSolicitada) {
                // Show required quantity alert
                cantidadInfo.classList.remove('hidden');
                cantidadRequerida.textContent = cantidadSolicitada;
                
                // Pre-fill and lock quantity field
                inputCantidad.value = cantidadSolicitada;
                inputCantidad.readOnly = true;
                inputCantidad.classList.add('bg-gray-100');
            } else {
                // Hide alert and unlock quantity
                cantidadInfo.classList.add('hidden');
                inputCantidad.value = '';
                inputCantidad.readOnly = false;
                inputCantidad.classList.remove('bg-gray-100');
            }
        });

        // Form validation
        const form = document.getElementById('formEntrada');
        form.addEventListener('submit', function(e) {
            const cantidad = parseInt(inputCantidad.value);
            const selectedOption = selectProducto.options[selectProducto.selectedIndex];
            const cantidadSolicitada = selectedOption.dataset.cantidad;
            const tipo = selectedOption.dataset.tipo;

            if (cantidad <= 0) {
                e.preventDefault();
                alert('La cantidad debe ser mayor a 0');
                return false;
            }

            // Validate quantity matches requested for non-admin users
            if (tipo === 'solicitud' && cantidadSolicitada) {
                if (cantidad !== parseInt(cantidadSolicitada)) {
                    e.preventDefault();
                    alert('La cantidad debe ser exactamente ' + cantidadSolicitada + ' unidades según la solicitud aprobada');
                    return false;
                }
            }
        });
    });
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>
