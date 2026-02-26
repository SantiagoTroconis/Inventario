<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php
// ── Precompute sucursal performance rates ───────────────────────────────────
foreach ($sucursalPerformance as $s) {
    $s->tasa = ($s->total_solicitudes > 0) 
        ? round(($s->aprobadas / $s->total_solicitudes) * 100) 
        : 0;
}

// ── Trend data for JS chart ──────────────────────────────────────────────────
$trendLabels   = array_map(fn($r) => $r->mes_label, $trend);
$trendEntradas = array_map(fn($r) => (int)$r->total_entradas, $trend);
$trendSalidas  = array_map(fn($r) => (int)$r->total_salidas,  $trend);
?>

<!-- ── Page Header ─────────────────────────────────────────────────────────── -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-900">Reportes</h2>
            <p class="text-gray-500 text-sm mt-0.5">Análisis agregado y estadísticas de inteligencia de inventario.</p>
        </div>
        <div class="flex gap-3 items-center">
            <span class="text-sm text-gray-500 font-medium">Últimos:</span>
            <?php foreach ([7, 30, 90] as $d): ?>
                <a href="?days=<?php echo $d; ?>"
                   class="px-3 py-1.5 rounded-lg text-sm font-semibold transition-colors <?php echo $days === $d ? 'bg-blue-600 text-white shadow-sm' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'; ?>">
                    <?php echo $d; ?>d
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Summary Health Banner ──────────────────────────────────────────────── -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center text-red-600">
            <i class="fa-solid fa-circle-exclamation"></i>
        </div>
        <div>
            <p class="text-xs text-red-500 font-semibold uppercase tracking-wider">Críticos</p>
            <p class="text-2xl font-bold text-red-700"><?php echo $healthCounts['critical']; ?></p>
        </div>
    </div>
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center text-amber-600">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div>
            <p class="text-xs text-amber-500 font-semibold uppercase tracking-wider">Advertencia</p>
            <p class="text-2xl font-bold text-amber-700"><?php echo $healthCounts['warning']; ?></p>
        </div>
    </div>
    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center gap-4">
        <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center text-emerald-600">
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div>
            <p class="text-xs text-emerald-500 font-semibold uppercase tracking-wider">Saludables</p>
            <p class="text-2xl font-bold text-emerald-700"><?php echo $healthCounts['healthy']; ?></p>
        </div>
    </div>
</div>

<!-- ── Row 1: Sucursal Performance + Top Products ──────────────────────────── -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

    <!-- Sucursal Performance -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 text-sm">
                <i class="fa-solid fa-building"></i>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 text-sm">Rendimiento por Sucursal</h3>
                <p class="text-gray-400 text-xs">Solicitudes, aprobaciones y stock crítico por rama</p>
            </div>
        </div>
        <div class="p-5">
            <?php if (empty($sucursalPerformance)): ?>
                <div class="text-center py-8 text-gray-400">
                    <i class="fa-solid fa-building text-3xl mb-2 opacity-30"></i>
                    <p class="text-sm">No hay sucursales registradas.</p>
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($sucursalPerformance as $s):
                        $tasa = $s->total_solicitudes > 0
                            ? round(($s->aprobadas / $s->total_solicitudes) * 100) : 0;
                        $bajos = (int)($s->productos_bajo_stock ?? 0);
                        [$rBg, $rText, $rBorder] = match(true) {
                            $bajos >= 5 || $tasa < 40 => ['bg-red-50',    'text-red-600',    'border-red-200'],
                            $bajos >= 2 || $tasa < 70 => ['bg-amber-50',  'text-amber-600',  'border-amber-200'],
                            default                   => ['bg-emerald-50','text-emerald-600','border-emerald-200'],
                        };
                    ?>
                    <div class="p-3 rounded-lg border <?php echo $rBorder; ?> <?php echo $rBg; ?>">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-semibold text-gray-900 text-sm"><?php echo htmlspecialchars($s->sucursal_nombre); ?></p>
                                <p class="text-xs text-gray-400"><?php echo $s->total_solicitudes; ?> solicitudes en total</p>
                            </div>
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full <?php echo $rText; ?> bg-white border <?php echo $rBorder; ?>">
                                <?php echo $tasa; ?>% aprobadas
                            </span>
                        </div>
                        <!-- Fulfillment bar -->
                        <div class="w-full bg-white rounded-full h-1.5 border <?php echo $rBorder; ?>">
                            <div class="h-1.5 rounded-full <?php echo str_replace(['text-','50','bg-'], ['bg-','600',''], $rText); ?> transition-all"
                                 style="width:<?php echo min(100, $tasa); ?>%"></div>
                        </div>
                        <!-- Stats row -->
                        <div class="flex gap-3 mt-2 text-xs text-gray-500">
                            <span><i class="fa-solid fa-check text-emerald-500 mr-0.5"></i><?php echo (int)$s->aprobadas; ?> aprob.</span>
                            <span><i class="fa-solid fa-clock text-amber-500 mr-0.5"></i><?php echo (int)$s->pendientes; ?> pend.</span>
                            <span><i class="fa-solid fa-xmark text-red-500 mr-0.5"></i><?php echo (int)$s->rechazadas; ?> rech.</span>
                            <?php if ($bajos > 0): ?>
                                <span class="ml-auto font-semibold <?php echo $rText; ?>">
                                    <i class="fa-solid fa-box-open mr-0.5"></i><?php echo $bajos; ?> bajo stock
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top 10 Requested Products -->
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center text-blue-600 text-sm">
                    <i class="fa-solid fa-fire"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 text-sm">Top Productos Solicitados</h3>
                    <p class="text-gray-400 text-xs">Últimos <?php echo $days; ?> días · por cantidad total</p>
                </div>
            </div>
        </div>
        <div class="p-5">
            <?php if (empty($topProducts)): ?>
                <div class="text-center py-8 text-gray-400">
                    <i class="fa-solid fa-box-open text-3xl mb-2 opacity-30"></i>
                    <p class="text-sm">Sin datos de solicitudes en este período.</p>
                </div>
            <?php else: ?>
                <div class="space-y-2.5">
                    <?php foreach ($topProducts as $i => $p):
                        $bar = $maxDemand > 0 ? round(($p->cantidad_total / $maxDemand) * 100) : 0;
                        $stockOk = $p->stock_minimo == 0 || $p->stock > $p->stock_minimo;
                    ?>
                    <div class="group">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="text-xs font-bold text-gray-400 w-4"><?php echo $i + 1; ?></span>
                                <span class="text-sm font-semibold text-gray-800 truncate"><?php echo htmlspecialchars($p->nombre); ?></span>
                                <?php if (!$stockOk): ?>
                                    <span class="flex-shrink-0 text-xs bg-red-100 text-red-600 border border-red-200 px-1.5 py-0.5 rounded font-medium">
                                        <i class="fa-solid fa-triangle-exclamation mr-0.5"></i>Bajo stock
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="text-sm font-bold text-blue-600 flex-shrink-0 ml-2"><?php echo number_format($p->cantidad_total); ?> uds</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-blue-500 transition-all" style="width:<?php echo $bar; ?>%"></div>
                        </div>
                        <p class="text-xs text-gray-400 mt-0.5"><?php echo $p->veces_solicitado; ?> solicitudes · Stock actual: <?php echo $p->stock; ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Row 2: Trend Chart ─────────────────────────────────────────────────── -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6">
    <div class="p-5 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600 text-sm">
            <i class="fa-solid fa-chart-column"></i>
        </div>
        <div>
            <h3 class="font-bold text-gray-900 text-sm">Tendencia de Movimientos</h3>
            <p class="text-gray-400 text-xs">Unidades entradas vs salidas — últimos 6 meses</p>
        </div>
        <div class="ml-auto flex items-center gap-4 text-xs text-gray-500">
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-emerald-500 inline-block"></span> Entradas</span>
            <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-red-400 inline-block"></span> Salidas</span>
        </div>
    </div>
    <div class="p-5">
        <?php if (empty($trend)): ?>
            <div class="text-center py-12 text-gray-400">
                <i class="fa-solid fa-chart-line text-4xl mb-2 opacity-30"></i>
                <p class="text-sm">No hay movimientos registrados en los últimos 6 meses.</p>
            </div>
        <?php else: ?>
            <div class="relative h-56">
                <canvas id="trendChart"></canvas>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Row 3: Stock Health Table ─────────────────────────────────────────── -->
<div class="bg-white rounded-xl border border-gray-200 shadow-sm">
    <div class="p-5 border-b border-gray-100 flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600 text-sm">
            <i class="fa-solid fa-heart-pulse"></i>
        </div>
        <div>
            <h3 class="font-bold text-gray-900 text-sm">Salud de Stock — Todos los Productos</h3>
            <p class="text-gray-400 text-xs">Ordenado por criticidad · stock % vs mínimo configurado</p>
        </div>
        <!-- Legend -->
        <div class="ml-auto flex items-center gap-3 text-xs text-gray-500">
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span> Crítico ≤50%</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span> Advertencia ≤100%</span>
            <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span> Saludable</span>
        </div>
    </div>
    <div class="overflow-x-auto">
        <?php if (empty($stockHealth)): ?>
            <div class="text-center py-12 text-gray-400">
                <i class="fa-solid fa-boxes-stacked text-4xl mb-2 opacity-30"></i>
                <p class="text-sm">No hay productos activos registrados.</p>
            </div>
        <?php else: ?>
        <table class="w-full text-sm">
            <thead class="text-xs text-gray-500 uppercase bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold">Producto</th>
                    <th class="px-4 py-3 text-left font-semibold">Categoría</th>
                    <th class="px-4 py-3 text-left font-semibold">Stock actual</th>
                    <th class="px-4 py-3 text-left font-semibold">Mínimo</th>
                    <th class="px-4 py-3 text-left font-semibold w-48">Salud</th>
                    <th class="px-4 py-3 text-left font-semibold">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($stockHealth as $p):
                    $pct = min((float)$p->stock_pct, 200); // cap visual at 200%
                    [$dotCls, $barCls, $badgeCls, $badgeText] = match($p->health_status) {
                        'critical' => [
                            'bg-red-500',
                            'bg-red-500',
                            'bg-red-50 text-red-700 border-red-200',
                            'Crítico'
                        ],
                        'warning' => [
                            'bg-amber-400',
                            'bg-amber-400',
                            'bg-amber-50 text-amber-700 border-amber-200',
                            'Advertencia'
                        ],
                        default => [
                            'bg-emerald-500',
                            'bg-emerald-500',
                            'bg-emerald-50 text-emerald-700 border-emerald-200',
                            'Saludable'
                        ],
                    };
                ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full flex-shrink-0 <?php echo $dotCls; ?>"></span>
                            <span class="font-medium text-gray-900"><?php echo htmlspecialchars($p->nombre); ?></span>
                            <span class="font-mono text-xs text-blue-500 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-100"><?php echo $p->codigo; ?></span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs"><?php echo htmlspecialchars($p->categoria ?? '—'); ?></td>
                    <td class="px-4 py-3 font-bold text-gray-900"><?php echo number_format($p->stock); ?></td>
                    <td class="px-4 py-3 text-gray-500"><?php echo number_format($p->stock_minimo); ?></td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-100 rounded-full h-2">
                                <div class="h-2 rounded-full <?php echo $barCls; ?> transition-all"
                                     style="width:<?php echo min(100, $pct * 0.5); ?>%"></div>
                            </div>
                            <span class="text-xs text-gray-500 w-10 text-right font-mono"><?php echo $p->stock_pct; ?>%</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border <?php echo $badgeCls; ?>">
                            <?php echo $badgeText; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($trend)): ?>
<!-- ── Trend Chart Script ──────────────────────────────────────────────────── -->
<script>
(function () {
    const labels   = <?php echo json_encode($trendLabels); ?>;
    const entradas = <?php echo json_encode($trendEntradas); ?>;
    const salidas  = <?php echo json_encode($trendSalidas); ?>;

    const canvas  = document.getElementById('trendChart');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');

    // ── Responsive sizing ────────────────────────────────────────────────────
    function resize() {
        const parent = canvas.parentElement;
        canvas.width  = parent.clientWidth;
        canvas.height = parent.clientHeight;
        draw();
    }

    function draw() {
        const W = canvas.width;
        const H = canvas.height;
        ctx.clearRect(0, 0, W, H);

        if (!labels.length) return;

        const padL = 48, padR = 16, padT = 16, padB = 36;
        const chartW = W - padL - padR;
        const chartH = H - padT - padB;

        const maxVal = Math.max(...entradas, ...salidas, 1);
        const n = labels.length;
        const groupW = chartW / n;
        const barW   = Math.min(groupW * 0.35, 28);
        const gap    = 4;

        // ── Grid lines ───────────────────────────────────────────────────────
        ctx.strokeStyle = '#f3f4f6';
        ctx.lineWidth   = 1;
        for (let i = 0; i <= 4; i++) {
            const y = padT + (chartH / 4) * i;
            ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(padL + chartW, y); ctx.stroke();
            // Y labels
            const val = Math.round(maxVal * (1 - i / 4));
            ctx.fillStyle = '#9ca3af';
            ctx.font      = '10px Inter, sans-serif';
            ctx.textAlign = 'right';
            ctx.fillText(val >= 1000 ? (val / 1000).toFixed(1) + 'k' : val, padL - 6, y + 3);
        }

        // ── Bars ─────────────────────────────────────────────────────────────
        labels.forEach((label, i) => {
            const cx = padL + groupW * i + groupW / 2;

            // Entrada (green)
            const hE = (entradas[i] / maxVal) * chartH;
            ctx.fillStyle = '#10b981';
            ctx.beginPath();
            ctx.roundRect(cx - barW - gap / 2, padT + chartH - hE, barW, hE, [4, 4, 0, 0]);
            ctx.fill();

            // Salida (red)
            const hS = (salidas[i] / maxVal) * chartH;
            ctx.fillStyle = '#f87171';
            ctx.beginPath();
            ctx.roundRect(cx + gap / 2, padT + chartH - hS, barW, hS, [4, 4, 0, 0]);
            ctx.fill();

            // X labels
            ctx.fillStyle = '#6b7280';
            ctx.font      = '10px Inter, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(label, cx, padT + chartH + 18);
        });

        // ── Baseline ─────────────────────────────────────────────────────────
        ctx.strokeStyle = '#e5e7eb';
        ctx.lineWidth   = 1;
        ctx.beginPath();
        ctx.moveTo(padL, padT + chartH);
        ctx.lineTo(padL + chartW, padT + chartH);
        ctx.stroke();
    }

    window.addEventListener('resize', resize);
    resize();
})();
</script>
<?php endif; ?>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>