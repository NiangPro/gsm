<?php
checkAdminAuth();
$adminPage = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
$menuItems = [
    ['key' => 'dashboard', 'icon' => 'fa-house', 'label' => 'Tableau de bord', 'badge' => null],
    ['key' => 'produits', 'icon' => 'fa-box-open', 'label' => 'Produits', 'badge' => '14'],
    ['key' => 'categories', 'icon' => 'fa-tags', 'label' => 'Catégories', 'badge' => null],
    ['key' => 'commandes', 'icon' => 'fa-shopping-bag', 'label' => 'Commandes', 'badge' => '3'],
    ['key' => 'equipe', 'icon' => 'fa-users', 'label' => 'Équipe', 'badge' => null],
    ['key' => 'partenaires', 'icon' => 'fa-handshake', 'label' => 'Partenaires', 'badge' => null],
    ['key' => 'messages', 'icon' => 'fa-envelope', 'label' => 'Messages', 'badge' => '6'],
    ['key' => 'parametres', 'icon' => 'fa-gear', 'label' => 'Paramètres', 'badge' => null],
];

// Lien vers le site public
$siteHomeUrl = '?';

$sidebarCollapsed = isset($_COOKIE['sidebarCollapsed']) && $_COOKIE['sidebarCollapsed'] === 'true';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - GIE Sokhna Maï</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['Playfair Display', 'serif'],
                        body: ['Lato', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#16a34a',
                            foreground: '#ffffff',
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#16a34a',
                            600: '#15803d',
                            700: '#166534',
                            800: '#14532d',
                            900: '#052e16',
                        },
                    }
                }
            }
        }
    </script>
</head>
<body class="font-body bg-gray-50">
    <div class="flex min-h-screen">
        <!-- Sidebar moderne -->
        <aside class="h-screen sticky top-0 flex flex-col bg-white border-r border-gray-200 transition-all duration-300 z-30" 
               id="sidebar" 
               style="width: <?php echo $sidebarCollapsed ? '5rem' : '16rem'; ?>">
            
            <!-- Logo Section -->
            <div class="flex items-center gap-3 p-4 border-b border-gray-100 h-16">
                <img src="<?php echo asset('logo.jpg'); ?>" alt="Logo" class="h-10 w-10 rounded-xl object-cover shrink-0 shadow-sm">
                <div class="sidebar-text overflow-hidden transition-all duration-300 <?php echo $sidebarCollapsed ? 'w-0 opacity-0' : 'w-auto opacity-100'; ?>">
                    <span class="font-heading text-sm font-bold text-gray-900 block leading-tight">GIE Sokhna Maï</span>
                    <span class="text-[10px] text-gray-500">Administration</span>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <?php foreach ($menuItems as $item): 
                    $active = $adminPage === $item['key'];
                    $activeClass = $active 
                        ? 'bg-emerald-600 text-white shadow-md shadow-emerald-200' 
                        : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900';
                    $activeIconClass = $active ? 'text-white' : '';
                ?>
                    <a href="?page=admin&action=<?php echo $item['key']; ?>" 
                       class="group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-200 <?php echo $activeClass; ?>"
                       title="<?php echo $item['label']; ?>">
                        <div class="relative">
                            <i class="fas <?php echo $item['icon']; ?> w-5 text-center text-base <?php echo $activeIconClass; ?>"></i>
                            <?php if ($item['badge'] && !$sidebarCollapsed): ?>
                                <span class="absolute -top-1.5 -right-1.5 h-4 min-w-[16px] px-1 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white">
                                    <?php echo $item['badge']; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="sidebar-text whitespace-nowrap overflow-hidden transition-all duration-300 <?php echo $sidebarCollapsed ? 'w-0 opacity-0' : 'w-auto opacity-100'; ?>">
                            <?php echo $item['label']; ?>
                        </span>
                        <?php if ($item['badge'] && $sidebarCollapsed): ?>
                            <span class="absolute right-2 h-2 w-2 bg-red-500 rounded-full"></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <!-- Toggle Button -->
            <button onclick="toggleSidebar()" 
                    class="absolute -right-3 top-24 bg-white border border-gray-200 rounded-full p-1.5 shadow-md hover:shadow-lg hover:bg-gray-50 transition-all z-40"
                    title="Réduire/Agrandir le menu">
                <i class="fas fa-chevron-left text-xs text-gray-600 transition-transform duration-300 <?php echo $sidebarCollapsed ? 'rotate-180' : ''; ?>" id="sidebarToggle"></i>
            </button>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-auto min-h-screen">
            <!-- Top Bar mobile -->
            <div class="lg:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-20">
                <div class="flex items-center gap-3">
                    <img src="<?php echo asset('logo.jpg'); ?>" alt="Logo" class="h-8 w-8 rounded-lg object-cover">
                    <span class="font-heading font-bold text-gray-900">GIE Sokhna Maï</span>
                </div>
                <button onclick="document.getElementById('sidebar').classList.toggle('-translate-x-full'); document.getElementById('sidebar').classList.toggle('absolute'); document.getElementById('sidebar').classList.toggle('w-64');" 
                        class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <div class="p-6 lg:p-8">
