<?php require_once BASE_PATH . '/app/views/layouts/header.php'; ?>

<?php
if (!function_exists('time_elapsed_string')) {
    function time_elapsed_string($datetime, $full = false) {
        if (empty($datetime)) {
            return 'Desconocido';
        }

        try {
            $now = new \DateTime;
            $ago = new \DateTime($datetime);
            $diff = $now->diff($ago);

            // Calculate weeks manually since DateInterval doesn't support it directly in older PHP versions in the same way
            $weeks = floor($diff->d / 7);
            $days = $diff->d - ($weeks * 7);

            $string = array(
                'y' => 'año',
                'm' => 'mes',
                'w' => 'semana',
                'd' => 'día',
                'h' => 'hora',
                'i' => 'minuto',
                's' => 'segundo',
            );
            
            // Map custom properties for loop
            $values = [
                'y' => $diff->y,
                'm' => $diff->m,
                'w' => $weeks,
                'd' => $days,
                'h' => $diff->h,
                'i' => $diff->i,
                's' => $diff->s,
            ];

            foreach ($string as $k => &$v) {
                if ($values[$k]) {
                    $v = $values[$k] . ' ' . $v . ($values[$k] > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }

            if (!$full) $string = array_slice($string, 0, 1);
            return $string ? implode(', ', $string) : 'justo ahora';
        } catch (\Exception $e) {
            return 'Fecha inválida';
        }
    }
}
?>

<!-- Welcome Section -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Hola, <span class="text-indigo-600"><?php echo htmlspecialchars($usuario); ?></span></h1>
    <p class="text-gray-500 mt-1">Bienvenido a tu panel de control de Inventario.</p>
</div>

<!-- ==================================================================================================== -->
<!-- ADMINISTRADOR DASHBOARD -->
<!-- ==================================================================================================== -->
<?php if($tipo_usuario === 'Administrador'): ?>
    
    <!-- KPIs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <?php foreach ($kpis as $k): ?>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 text-sm font-medium uppercase tracking-wider mb-1"><?php echo $k['label']; ?></p>
                        <h3 class="text-3xl font-bold text-gray-900"><?php echo $k['value']; ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-<?php echo $k['bg'] ?? 'gray-100'; ?> text-<?php echo $k['color']; ?> flex items-center justify-center text-xl shadow-inner">
                        <i class="fa-solid <?php echo $k['icon']; ?>"></i>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Activity Column -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Pending Actions -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-50 flex justify-between items-center bg-gradient-to-r from-gray-50 to-white">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-bell text-amber-500"></i> Solicitudes Recientes
                    </h3>
                    <a href="<?php echo URL_BASE; ?>/requests.php" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">Ver todo</a>
                </div>
                <div class="divide-y divide-gray-50">
                    <?php if (!empty($recentActivity)): $recentActivity = array_slice($recentActivity, 0, 8); ?>
                        <?php foreach ($recentActivity as $act): ?>
                            <div class="p-4 hover:bg-gray-50 transition-colors flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-sm">
                                        <?php echo substr($act->usuario_nombre, 0, 2); ?>
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">
                                            <?php echo htmlspecialchars($act->usuario_nombre); ?> 
                                            <span class="font-normal text-gray-500">solicitó</span>
                                            <span class="text-indigo-600 font-medium"><?php echo $act->tipo; ?></span>
                                        </p>
                                        <p class="text-xs text-gray-400">Hace <?php echo time_elapsed_string($act->fecha_solicitud); ?></p>
                                    </div>
                                </div>
                                <span class="px-3 py-1 rounded-full text-xs font-medium 
                                    <?php echo $act->estado === 'Pendiente' ? 'bg-amber-100 text-amber-700' : 
                                               ($act->estado === 'Aprobada' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600'); ?>">
                                    <?php echo $act->estado; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-400">No hay actividad reciente.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="space-y-8">
            <!-- Global Low Stock -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-50 bg-gradient-to-r from-red-50 to-white">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-red-500"></i> Alertas de Stock
                    </h3>
                </div>
                <div class="p-4">
                    <?php if (!empty($lowStockItems)): $lowStockItems = array_slice($lowStockItems, 0, 5); ?>
                        <div class="space-y-3">
                            <?php foreach ($lowStockItems as $item): ?>
                                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded bg-white text-red-500 flex items-center justify-center text-sm shadow-sm">
                                            <i class="fa-solid fa-box"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($item->nombre); ?></p>
                                            <p class="text-xs text-red-600 font-medium">Stock: <?php echo $item->stock; ?> (Min: <?php echo $item->stock_minimo; ?>)</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fa-solid fa-check-circle text-4xl text-emerald-200 mb-2"></i>
                            <p class="text-sm text-gray-500">Todo el inventario está saludable</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl p-6 text-white shadow-lg">
                <h3 class="font-bold text-lg mb-4">Acciones Rápidas</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="<?php echo URL_BASE; ?>/users.php?action=new" class="flex flex-col items-center justify-center p-3 bg-white/10 rounded-xl hover:bg-white/20 transition-colors backdrop-blur-sm">
                        <i class="fa-solid fa-user-plus text-xl mb-2"></i>
                        <span class="text-xs font-medium">Nuevo Usuario</span>
                    </a>
                    <a href="<?php echo URL_BASE; ?>/products.php/new" class="flex flex-col items-center justify-center p-3 bg-white/10 rounded-xl hover:bg-white/20 transition-colors backdrop-blur-sm">
                        <i class="fa-solid fa-box-open text-xl mb-2"></i>
                        <span class="text-xs font-medium">Nuevo Producto</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

<!-- ==================================================================================================== -->
<!-- SUCURSAL DASHBOARD -->
<!-- ==================================================================================================== -->
<?php elseif($tipo_usuario === 'Sucursal'): ?>

    <!-- KPIs Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <?php foreach ($kpis as $k): ?>
            <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-<?php echo $k['color']; ?>/5 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-xl bg-<?php echo $k['bg'] ?? 'gray-50'; ?> text-<?php echo $k['color']; ?> flex items-center justify-center text-xl shadow-sm mb-4">
                        <i class="fa-solid <?php echo $k['icon']; ?>"></i>
                    </div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-1"><?php echo $k['value']; ?></h3>
                    <p class="text-gray-500 text-sm font-medium"><?php echo $k['label']; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Incoming Requests (Needs Attention) -->
        <div class="lg:col-span-2">
            <h2 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fa-solid fa-inbox text-indigo-600"></i> Solicitudes Entrantes
            </h2>
            
            <?php if (!empty($pendingRequests)): ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($pendingRequests as $req): ?>
                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                                        <?php echo substr($req->usuario_nombre, 0, 1); ?>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-900 text-sm"><?php echo htmlspecialchars($req->usuario_nombre); ?></h4>
                                        <p class="text-xs text-gray-500"><?php echo time_elapsed_string($req->fecha_solicitud); ?></p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 bg-amber-50 text-amber-600 rounded text-xs font-semibold">Pendiente</span>
                            </div>
                            <p class="text-sm text-gray-600 mb-4 line-clamp-2 bg-gray-50 p-2 rounded border border-gray-100">
                                "<?php echo htmlspecialchars($req->descripcion ?: 'Sin descripción'); ?>"
                            </p>
                            <div class="flex gap-2">
                                <a href="<?php echo URL_BASE; ?>/requests.php" class="flex-1 text-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors shadow-sm shadow-indigo-200">
                                    Revisar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-xl border border-dashed border-gray-300 p-8 text-center">
                    <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-3 text-gray-400">
                        <i class="fa-solid fa-check text-2xl"></i>
                    </div>
                    <h3 class="text-gray-900 font-medium">Todo al día</h3>
                    <p class="text-gray-500 text-sm">No tienes solicitudes pendientes por revisar.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Quick Actions & Alerts -->
        <div class="space-y-6">
            <!-- CTA -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl p-6 text-white shadow-xl relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="font-bold text-xl mb-2">¿Necesitas Stock?</h3>
                    <p class="text-blue-100 text-sm mb-4">Solicita reabastecimiento al almacén central.</p>
                    <a href="<?php echo URL_BASE; ?>/products.php" class="inline-block w-full text-center px-4 py-3 bg-white text-indigo-600 font-bold rounded-xl shadow-lg hover:bg-blue-50 transition-colors">
                        <i class="fa-solid fa-plus mr-2"></i> Ver productos disponibles
                    </a>
                </div>
                <!-- Deco -->
                <i class="fa-solid fa-boxes-stacked absolute -right-4 -bottom-4 text-9xl text-white/10 rotate-12"></i>
            </div>

            <!-- Low Stock Mini -->
            <?php if(!empty($lowStockItems)): ?>
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
                    <h4 class="font-bold text-gray-800 mb-3 text-sm uppercase tracking-wide">Stock Crítico</h4>
                    <div class="space-y-3">
                        <?php foreach($lowStockItems as $item): ?>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-600 truncate flex-1"><?php echo htmlspecialchars($item->nombre); ?></span>
                                <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded font-bold text-xs"><?php echo $item->stock; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>


<!-- ==================================================================================================== -->
<!-- AGENTE DASHBOARD -->
<!-- ==================================================================================================== -->
<?php else: ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: KPIs & CTA -->
        <div class="space-y-6">
            <!-- Big CTA -->
            <div class="bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 rounded-2xl p-8 text-white shadow-xl text-center relative overflow-hidden group">
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl backdrop-blur flex items-center justify-center mx-auto mb-4 shadow-inner group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-hand-holding-box text-3xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold mb-2">Solicitar Inventario</h2>
                    <p class="text-white/80 mb-6 text-sm">Crea una solicitud ahora.</p>
                    <a href="<?php echo URL_BASE; ?>/products.php/sucursal/4" class="block w-full py-3 px-6 bg-white text-purple-600 font-bold rounded-xl shadow-lg hover:bg-gray-50 transition-transform transform active:scale-95">
                        <i class="fa-solid fa-plus-circle mr-2"></i> Nueva Solicitud
                    </a>
                </div>
            </div>

            <!-- Mini KPIs -->
            <div class="grid grid-cols-2 gap-4">
                <?php foreach ($kpis as $k): ?>
                    <div class="bg-white p-4 rounded-xl border border-gray-100 shadow-sm text-center">
                        <div class="text-2xl mb-1 text-<?php echo $k['color']; ?>">
                            <i class="fa-solid <?php echo $k['icon']; ?>"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-800"><?php echo $k['value']; ?></h3>
                        <p class="text-xs text-gray-400 uppercase tracking-wide"><?php echo $k['label']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Right Column: My Requests Feed -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm h-full">
                <div class="p-6 border-b border-gray-50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 text-lg">Mis Solicitudes Recientes</h3>
                    <a href="<?php echo URL_BASE; ?>/requests.php" class="text-sm font-medium text-gray-400 hover:text-indigo-600 transition-colors">Ver historial</a>
                </div>
                
                <div class="p-6">
                    <?php if(!empty($myRequests)): ?>
                        <div class="space-y-4">
                            <?php foreach($myRequests as $req): ?>
                                <!-- Request Card -->
                                <div class="flex flex-col md:flex-row md:items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-white hover:shadow-md transition-all group">
                                    <div class="flex items-start gap-4 mb-4 md:mb-0">
                                        <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0
                                            <?php 
                                            echo match($req->estado) {
                                                'Pendiente' => 'bg-amber-100 text-amber-600',
                                                'Aprobada' => 'bg-emerald-100 text-emerald-600',
                                                'Rechazada' => 'bg-red-100 text-red-600',
                                                'Completada' => 'bg-blue-100 text-blue-600',
                                                'En Negociación' => 'bg-purple-100 text-purple-600',
                                                'En negociación' => 'bg-purple-100 text-purple-600',
                                                default => 'bg-gray-100 text-gray-600'
                                            };
                                            ?>">
                                            <i class="fa-solid 
                                            <?php 
                                            echo match($req->estado) {
                                                'Pendiente' => 'fa-clock',
                                                'Aprobada' => 'fa-check',
                                                'Rechazada' => 'fa-xmark',
                                                'Completada' => 'fa-box-archive',
                                                'En Negociación' => 'fa-comments-dollar', 
                                                'En negociación' => 'fa-comments-dollar', 
                                                default => 'fa-circle-question'
                                            };
                                            ?> text-xl"></i>
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 mb-1">
                                                <span class="font-bold text-gray-900">#<?php echo $req->id; ?></span>
                                                <span class="text-sm text-gray-600"><?php echo htmlspecialchars($req->tipo); ?></span>
                                            </div>
                                            <p class="text-sm text-gray-500 mb-1"><?php echo htmlspecialchars($req->descripcion); ?></p>
                                            <p class="text-xs text-gray-400">
                                                <i class="fa-regular fa-calendar mr-1"></i> <?php echo date('d M, Y', strtotime($req->fecha_solicitud)); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center justify-between md:justify-end gap-4 w-full md:w-auto mt-2 md:mt-0">
                                        <div class="text-right">
                                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider
                                                <?php 
                                                echo match($req->estado) {
                                                    'Pendiente' => 'bg-amber-100 text-amber-700',
                                                    'Aprobada' => 'bg-emerald-100 text-emerald-700',
                                                    'Rechazada' => 'bg-red-100 text-red-700',
                                                    'Completada' => 'bg-blue-100 text-blue-700',
                                                    'En Negociación' => 'bg-purple-100 text-purple-700',
                                                    'En negociación' => 'bg-purple-100 text-purple-700',
                                                    default => 'bg-gray-100 text-gray-700'
                                                };
                                                ?>">
                                                <?php echo $req->estado; ?>
                                            </span>
                                        </div>
                                        
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-300">
                                <i class="fa-solid fa-folder-open text-3xl"></i>
                            </div>
                            <h3 class="text-gray-800 font-medium mb-1">Sin solicitudes</h3>
                            <p class="text-gray-500 text-sm">Aún no has realizado ninguna solicitud.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php endif; ?>



<?php require_once BASE_PATH . '/app/views/layouts/footer.php'; ?>