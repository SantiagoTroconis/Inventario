<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fetch sucursales for dropdown (if Agente user type)
if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'Agente') {
    require_once BASE_PATH . '/app/models/UserModel.php';
    $userModel = new UserModel();
    $sucursalesDropdown = $userModel->getSucursales();
}

// Helper for active link
function isActive($path)
{
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $current = explode('/', trim($uri, '/'));
    if ($current[0] == $path) return true;
    return $current === $path;
}

// Helper to check if viewing a specific sucursal
function isViewingSucursal($sucursalId)
{
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    return strpos($uri, '/products.php/sucursal/' . $sucursalId) !== false;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
    <!-- jQuery (required for DataTables) - Load early so it's available in page scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>

<body class="bg-gray-50 text-slate-800 font-sans antialiased">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col z-20 pt-2">
            <!-- Logo -->
            <div class="h-16 flex items-center px-2 mx-auto py-4">
                <a href="<?php echo URL_BASE; ?>/home.php" class="flex items-center gap-3">
                    <img src="<?php echo URL_BASE; ?>/assets/LOGOTIPO_RGB-01.png" onerror="this.src='https://placehold.co/150x50?text=LOGO'" alt="Logo" class="h-16 w-auto object-contain content-fit rounded ">
                </a>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto py-4">
                <nav class="px-3 space-y-1">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-2">Principal</p>

                    <a href="<?php echo URL_BASE; ?>/home.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg mb-2 <?php echo isActive('home') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors ">
                        <i class="fa-solid fa-house w-5 text-center"></i> Inicio
                    </a>

                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 pt-4">Operaciones</p>

                    <?php if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] !== 'Agente'): ?>
                    <a href="<?php echo URL_BASE; ?>/products.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('products') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                        <i class="fa-solid fa-boxes-stacked w-5 text-center"></i> Inventario General
                    </a>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'Agente'): ?>
                    <!-- Collapsible Sucursales Dropdown for Agentes -->
                    <div class="mb-1">
                        <button onclick="toggleSucursalesDropdown()" class="w-full flex items-center justify-between gap-3 px-3 py-2 text-sm font-medium rounded-lg text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-boxes-stacked w-5 text-center"></i>
                                <span>Sucursales</span>
                            </div>
                            <i id="sucursalesChevron" class="fa-solid fa-chevron-down text-xs transition-transform"></i>
                        </button>
                        <div id="sucursalesDropdown" class="hidden mt-1 ml-8 space-y-1">
                            <?php if (isset($sucursalesDropdown) && !empty($sucursalesDropdown)): ?>
                                <?php foreach ($sucursalesDropdown as $sucursal): ?>
                                    <a href="<?php echo URL_BASE; ?>/products.php/sucursal/<?php echo $sucursal->usuario_id; ?>" 
                                       class="flex items-center gap-2 px-3 py-2 text-sm rounded-lg <?php echo isViewingSucursal($sucursal->usuario_id) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                                        <i class="fa-solid fa-building text-xs w-4 text-center"></i>
                                        <span><?php echo htmlspecialchars($sucursal->nombre); ?></span>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="px-3 py-2 text-xs text-gray-400">No hay sucursales disponibles</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <a href="<?php echo URL_BASE; ?>/requests.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('requests') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                        <i class="fa-solid fa-file-signature w-5 text-center"></i> Solicitudes
                    </a>
                    <a href="<?php echo URL_BASE; ?>/entries.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('entries') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                        <i class="fa-solid fa-dolly w-5 text-center"></i> Entradas
                    </a>

                    <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'Sucursal'): ?>
                        <a href="<?php echo URL_BASE; ?>/exits.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('exits') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                            <i class="fa-solid fa-truck-ramp-box w-5 text-center"></i> Salidas
                        </a>
                        <a href="<?php echo URL_BASE; ?>/products.php/sucursal" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('products/sucursal') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                            <i class="fa-solid fa-boxes-stacked  w-5 text-center"></i> Inventario
                        </a>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'Administrador'): ?>
                        <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Administración</p>

                        <a href="<?php echo URL_BASE; ?>/sucursales" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('sucursales') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                            <i class="fa-solid fa-building w-5 text-center"></i> Sucursales
                        </a>
                        <a href="<?php echo URL_BASE; ?>/agentes" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('agentes') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                            <i class="fa-solid fa-user-tie w-5 text-center"></i> Agentes
                        </a>
                        <a href="<?php echo URL_BASE; ?>/reportes" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('reportes') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                            <i class="fa-solid fa-chart-pie w-5 text-center"></i> Reportes
                        </a>
                    <?php endif; ?>
                </nav>
            </div>

            <div class="border-t border-gray-100 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-100 to-blue-50 flex items-center justify-center text-blue-700 font-bold border border-blue-200">
                        <?php echo substr($_SESSION['usuario'] ?? 'A', 0, 1); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate"><?php echo $_SESSION['tipo_usuario'] ?? 'Admin'; ?></p>
                        <p class="text-xs text-gray-500 truncate"><?php echo $_SESSION['sucursal'] ?? 'Principal'; ?></p>
                    </div>
                    <a href="<?php echo URL_BASE; ?>/auth.php/logout" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fa-solid fa-right-from-bracket"></i></a>
                </div>
            </div>
        </aside>

        <script>
            // Toggle Sucursales Dropdown for Agentes
            function toggleSucursalesDropdown() {
                const dropdown = document.getElementById('sucursalesDropdown');
                const chevron = document.getElementById('sucursalesChevron');
                
                if (dropdown && chevron) {
                    dropdown.classList.toggle('hidden');
                    chevron.classList.toggle('rotate-180');
                    
                    // Save state to localStorage
                    localStorage.setItem('sucursalesDropdownOpen', !dropdown.classList.contains('hidden'));
                }
            }
            
            // Restore dropdown state on page load
            document.addEventListener('DOMContentLoaded', function() {
                const dropdown = document.getElementById('sucursalesDropdown');
                const chevron = document.getElementById('sucursalesChevron');
                const isOpen = localStorage.getItem('sucursalesDropdownOpen') === 'true';
                
                if (dropdown && chevron && isOpen) {
                    dropdown.classList.remove('hidden');
                    chevron.classList.add('rotate-180');
                }
            });
        </script>

        <!-- Main Content Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="bg-white border-b border-gray-100 h-16 flex items-center justify-between px-6 z-10 shadow-sm">
                <button class="md:hidden text-gray-500 hover:text-gray-700 focus:outline-none"><i class="fa-solid fa-bars text-xl"></i></button>
                <div class="hidden md:flex items-center gap-2">
                    <h1 class="text-lg font-semibold text-gray-800"><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></h1>
                </div>
                <div class="flex items-center gap-4">
                    <button class="relative p-2 text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="fa-solid fa-bell"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full border border-white"></span>
                    </button>
                    <div class="md:hidden w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold"><?php echo substr($_SESSION['usuario'] ?? 'A', 0, 1); ?></div>
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6 scroll-smooth bg-gray-50">
                <div class="max-w-7xl mx-auto">