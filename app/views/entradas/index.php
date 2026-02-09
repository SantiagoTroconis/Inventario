<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Entradas</h2>
            <p class="text-gray-500 text-sm">Registros de entradas de stock por sucursal.</p>
        </div>
        <div class="flex gap-3">
            <a href="registrar" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-plus"></i> Registrar Entrada
            </a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table id="entradasTable" class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 font-semibold">ID</th>
                    <th class="px-6 py-4 font-semibold">Producto</th>
                    <th class="px-6 py-4 font-semibold">Cantidad</th>
                    <th class="px-6 py-4 font-semibold">Sucursal</th>
                    <th class="px-6 py-4 font-semibold">Fecha</th>
                    <th class="px-6 py-4 font-semibold">Recibido Por</th>
                    <th class="px-6 py-4 font-semibold">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <?php foreach ($entradas as $e): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4"><span class="font-mono text-blue-600 bg-blue-50 px-2.5 py-1 rounded text-xs font-medium border border-blue-100"><?php echo $e['id']; ?></span></td>
                        <td class="px-6 py-4 font-medium text-gray-900"><?php echo $e['producto']; ?></td>
                        <td class="px-6 py-4"><?php echo $e['cantidad']; ?></td>
                        <td class="px-6 py-4"><?php echo $e['sucursal']; ?></td>
                        <td class="px-6 py-4"><?php echo $e['fecha']; ?></td>
                        <td class="px-6 py-4"><?php echo $e['recibido_por']; ?></td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <a href="#" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-blue-50 hover:text-blue-600 transition-colors border border-transparent hover:border-blue-100"><i class="fa-solid fa-eye"></i></a>
                                <a href="#" class="w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:bg-red-50 hover:text-red-500 transition-colors border border-transparent hover:border-red-100"><i class="fa-solid fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination handled by DataTables -->
</div>

<style>
    /* DataTables Custom Styling */
    #entradasTable_wrapper .dataTables_filter input {
        padding: 0.625rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin-left: 0.5rem;
    }
    
    #entradasTable_wrapper .dataTables_filter input:focus {
        outline: none;
        border-color: #3b82f6;
        ring: 2px;
        ring-color: rgba(59, 130, 246, 0.2);
    }
    
    #entradasTable_wrapper .dataTables_length select {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        margin: 0 0.5rem;
    }
    
    #entradasTable_wrapper .dataTables_info,
    #entradasTable_wrapper .dataTables_paginate {
        padding-top: 1.5rem;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    #entradasTable_wrapper .dataTables_paginate .paginate_button {
        padding: 0.5rem 0.75rem;
        margin: 0 0.125rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #4b5563;
        background: white;
    }
    
    #entradasTable_wrapper .dataTables_paginate .paginate_button:hover {
        background: #eff6ff;
        border-color: #3b82f6;
        color: #2563eb;
    }
    
    #entradasTable_wrapper .dataTables_paginate .paginate_button.current {
        background: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }
    
    #entradasTable_wrapper .dataTables_paginate .paginate_button.disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }
</style>

<script>
    $(document).ready(function() {
        $('#entradasTable').DataTable({
            responsive: true,
            language: {
                "decimal": "",
                "emptyTable": "No hay entradas disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ entradas",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron entradas coincidentes",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": activar para ordenar la columna ascendente",
                    "sortDescending": ": activar para ordenar la columna descendente"
                }
            },
            pageLength: 10,
            order: [[4, 'desc']], // Sort by date descending by default
            columnDefs: [
                { orderable: false, targets: [6] } // Disable sorting on Actions column
            ]
        });
    });
</script>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>