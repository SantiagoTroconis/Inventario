<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
        <h3 class="text-sm text-gray-500 uppercase font-semibold mb-2">Total Productos</h3>
        <div class="text-2xl font-bold text-gray-900"><?php echo number_format($stats['total_productos']); ?></div>
    </div>
    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
        <h3 class="text-sm text-gray-500 uppercase font-semibold mb-2">Entradas (Mes)</h3>
        <div class="text-2xl font-bold text-amber-500"><?php echo number_format($stats['entradas_mes']); ?></div>
    </div>
    <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
        <h3 class="text-sm text-gray-500 uppercase font-semibold mb-2">Salidas (Mes)</h3>
        <div class="text-2xl font-bold text-red-500"><?php echo number_format($stats['salidas_mes']); ?></div>
    </div>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">
    <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-bold text-gray-800">Generar Reporte</h2>
        <div class="flex gap-2">
            <select class="bg-white border border-gray-300 text-gray-700 text-sm rounded-lg block p-2.5 focus:outline-none">
                <option value="monthly">Mensual</option>
                <option value="yearly">Anual</option>
                <option value="custom">Rango Personalizado</option>
            </select>
            <button class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-file-export"></i> Generar
            </button>
        </div>
    </div>

    <p class="text-sm text-gray-500 mb-4">Vista previa rápida de datos recientes.</p>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
        <table class="w-full text-sm text-left text-gray-600">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 font-semibold">Métrica</th>
                    <th class="px-6 py-4 font-semibold">Valor</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white">
                <tr>
                    <td class="px-6 py-4">Stock Total</td>
                    <td class="px-6 py-4 font-medium text-gray-900"><?php echo number_format($stats['stock_total']); ?></td>
                </tr>
                <tr>
                    <td class="px-6 py-4">Entradas (Últimos 30 días)</td>
                    <td class="px-6 py-4 font-medium text-amber-600"><?php echo number_format($stats['entradas_mes']); ?></td>
                </tr>
                <tr>
                    <td class="px-6 py-4">Salidas (Últimos 30 días)</td>
                    <td class="px-6 py-4 font-medium text-red-600"><?php echo number_format($stats['salidas_mes']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>