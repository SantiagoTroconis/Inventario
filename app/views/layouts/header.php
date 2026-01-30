<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Helper for active link
function isActive($path)
{
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
    $current = explode('/', trim($uri, '/'));
    if ($current[0] == $path) return true;
    return $current === $path;
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
                <a href="home.php" class="flex items-center gap-3">
                    <img src="<?php echo URL_BASE; ?>/assets/LOGOTIPO_RGB-01.png" onerror="this.src='https://placehold.co/150x50?text=LOGO'" alt="Logo" class="h-16 w-auto object-contain content-fit rounded ">
                </a>
            </div>

            <!-- Navigation -->
            <div class="flex-1 overflow-y-auto py-4">
                <nav class="px-3 space-y-1">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-2">Principal</p>

                    <a href="home.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('home') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                        <i class="fa-solid fa-house w-5 text-center"></i> Dashboard
                    </a>

                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Operaciones</p>

                    <a href="products.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('products') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                        <i class="fa-solid fa-boxes-stacked w-5 text-center"></i> Productos
                    </a>
                    <a href="requests.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('requests') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                        <i class="fa-solid fa-file-signature w-5 text-center"></i> Solicitudes
                    </a>
                    <a href="entries.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('entries') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                        <i class="fa-solid fa-dolly w-5 text-center"></i> Entradas
                    </a>

                    <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'Sucursal'): ?>
                        <a href="exits.php" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('exits') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                            <i class="fa-solid fa-truck-ramp-box w-5 text-center"></i> Salidas
                        </a>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['tipo_usuario']) && $_SESSION['tipo_usuario'] === 'Administrador'): ?>
                        <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2 mt-6">Administración</p>

                        <a href="?page=sucursales" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('sucursales') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                            <i class="fa-solid fa-building w-5 text-center"></i> Sucursales
                        </a>
                        <a href="?page=agentes" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('agentes') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
                            <i class="fa-solid fa-user-tie w-5 text-center"></i> Agentes
                        </a>
                        <a href="?page=reportes" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg <?php echo isActive('reportes') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'; ?> transition-colors">
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
                    <a href="logout.php" class="text-gray-400 hover:text-red-500 transition-colors"><i class="fa-solid fa-right-from-bracket"></i></a>
                </div>
            </div>
        </aside>

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