<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
    <?php foreach ($kpis as $k): ?>
        <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider"><?php echo $k['label']; ?></h3>
                <div
                    class="w-10 h-10 rounded-lg bg-<?php echo str_replace('-', '', explode('-', $k['color'])[0]); ?>-50 text-<?php echo $k['color']; ?> flex items-center justify-center text-lg">
                    <i class="fa-solid <?php echo $k['icon']; ?>"></i>
                </div>
            </div>
            <div class="text-2xl font-bold text-gray-900"><?php echo $k['value']; ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Main Content Area - Overview -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Pending Requests Panel (Admin & Sucursal only) -->
    <?php if (isset($tipo_usuario) && ($tipo_usuario === 'Administrador' || $tipo_usuario === 'Sucursal')): ?>
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                            <i class="fa-solid fa-bell text-amber-600"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Solicitudes Pendientes</h3>
                            <p class="text-sm text-gray-500">Requieren tu atención</p>
                        </div>
                    </div>
                    <?php if (!empty($pendingRequests)): ?>
                        <span class="px-3 py-1 bg-amber-100 text-amber-700 text-sm font-semibold rounded-full">
                            <?php echo count($pendingRequests); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Requests List -->
                <div class="divide-y divide-gray-100">
                    <?php if (!empty($pendingRequests)): ?>
                        <?php foreach ($pendingRequests as $request): ?>
                            <?php
                            // Format date (accept either a DateTime object or a string)
                            if ($request->fecha_solicitud instanceof \DateTime) {
                                $fechaSolicitud = $request->fecha_solicitud;
                            } else {
                                try {
                                    $fechaSolicitud = new \DateTime($request->fecha_solicitud ?? 'now');
                                } catch (\Exception $e) {
                                    $fechaSolicitud = new \DateTime();
                                }
                            }
                            $ahora = new \DateTime();
                            $diff = $ahora->diff($fechaSolicitud);

                            if ($diff->days == 0) {
                                if ($diff->h == 0) {
                                    $tiempoTranscurrido = $diff->i . ' minutos';
                                } else {
                                    $tiempoTranscurrido = $diff->h . ' horas';
                                }
                            } elseif ($diff->days == 1) {
                                $tiempoTranscurrido = 'Ayer';
                            } else {
                                $tiempoTranscurrido = $diff->days . ' días';
                            }

                            // Determine icon and color based on type
                            $iconClass = 'fa-box';
                            $bgColor = 'bg-blue-50';
                            $textColor = 'text-blue-600';
                            if ($request->tipo === 'Producto') {
                                $iconClass = 'fa-box';
                                $bgColor = 'bg-blue-50';
                                $textColor = 'text-blue-600';
                            }

                            // Priority badge
                            $priorityClass = match ($request->prioridad) {
                                'Alta' => 'bg-red-100 text-red-700 border-red-200',
                                'Normal' => 'bg-gray-100 text-gray-700 border-gray-200',
                                'Baja' => 'bg-green-100 text-green-700 border-green-200',
                                default => 'bg-gray-100 text-gray-700 border-gray-200'
                            };
                            ?>
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start gap-4">
                                    <div
                                        class="w-10 h-10 rounded-lg <?php echo $bgColor; ?> <?php echo $textColor; ?> flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid <?php echo $iconClass; ?>"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2 mb-1">
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-900">
                                                    Solicitud de <?php echo htmlspecialchars($request->usuario_nombre); ?>
                                                </h4>
                                                <p class="text-xs text-gray-500">
                                                    <i
                                                        class="fa-solid fa-user-tag mr-1"></i><?php echo htmlspecialchars($request->solicitante_tipo); ?>
                                                </p>
                                            </div>
                                            <span
                                                class="px-2 py-0.5 text-xs font-medium rounded border <?php echo $priorityClass; ?>">
                                                <?php echo $request->prioridad; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($request->descripcion)): ?>
                                            <p class="text-sm text-gray-600 mb-2 line-clamp-2">
                                                <?php echo htmlspecialchars($request->descripcion); ?></p>
                                        <?php endif; ?>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-xs text-gray-400">
                                                <i class="fa-solid fa-clock mr-1"></i>Hace <?php echo $tiempoTranscurrido; ?>
                                            </span>
                                            <a href="<?php echo URL_BASE; ?>/requests.php"
                                                class="text-xs font-medium text-blue-600 hover:text-blue-700">
                                                Ver detalles <i class="fa-solid fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- View All Link -->
                        <div class="p-4 bg-gray-50">
                            <a href="<?php echo URL_BASE; ?>/requests.php"
                                class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center justify-center gap-2">
                                <span>Ver todas las solicitudes</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="p-12 text-center">
                            <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                <i class="fa-solid fa-check-circle text-2xl text-gray-400"></i>
                            </div>
                            <h4 class="text-base font-semibold text-gray-900 mb-2">Todo al día</h4>
                            <p class="text-sm text-gray-500">No tienes solicitudes pendientes en este momento</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>