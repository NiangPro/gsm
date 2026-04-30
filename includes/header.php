<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GIE Sokhna Maï</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="<?php echo asset('js/cart.js'); ?>"></script>
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
                        accent: {
                            DEFAULT: '#d97706',
                            50: '#fffbeb',
                            100: '#fef3c7',
                            200: '#fde68a',
                            300: '#fcd34d',
                            400: '#fbbf24',
                            500: '#d97706',
                            600: '#b45309',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeInUp 0.6s ease-out forwards;
        }
        .delay-100 { animation-delay: 0.1s; }
        .delay-200 { animation-delay: 0.2s; }
        .delay-300 { animation-delay: 0.3s; }
    </style>
</head>
<body class="font-body bg-white text-gray-900 min-h-screen flex flex-col">
    <?php
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
// Si la page demandée est "a-propos", la traiter comme "home" car nous avons fusionné les pages
if ($currentPage === 'a-propos') {
    $currentPage = 'home';
}
    $navItems = [
        ['key' => 'home', 'url' => '?', 'label' => 'nav.home'],
        // ['key' => 'a-propos', 'url' => '?', 'label' => 'nav.about'],
        ['key' => 'catalogue', 'url' => '?page=catalogue', 'label' => 'nav.catalogue'],
        ['key' => 'blog', 'url' => '?page=blog', 'label' => 'nav.news'],
        ['key' => 'equipe', 'url' => '?page=equipe', 'label' => 'nav.team'],
        ['key' => 'contact', 'url' => '?page=contact', 'label' => 'nav.contact'],
        ['key' => 'partenaires', 'url' => '?page=partenaires', 'label' => 'nav.partners'],
    ];
    ?>
    <header class="sticky top-0 z-50 bg-white/95 backdrop-blur border-b border-gray-200">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="flex items-center justify-between h-16">
                <a href="?" class="flex items-center gap-2">
                    <img src="<?php echo asset('logo.jpg'); ?>" alt="Logo GIE Sokhna Maï" class="h-12 w-12 rounded-full object-cover">
                    <span class="font-heading text-xl font-bold text-primary-600">GIE Sokhna Maï</span>
                </a>

                <nav class="hidden md:flex items-center gap-1">
                    <?php foreach ($navItems as $item): 
                        $isActive = ($currentPage === $item['key'] || ($currentPage === 'home' && $item['key'] === 'home'));
                        $activeClass = $isActive ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:text-primary-600 hover:bg-primary-50';
                    ?>
                        <a href="<?php echo $item['url']; ?>" class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php echo $activeClass; ?>">
                            <?php echo __($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                    
                    <div class="flex items-center gap-2 ml-2">
                        <!-- Panier -->
                        <a href="?page=panier" class="relative group">
                            <div class="flex items-center gap-1.5 px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i id="cartIcon" class="fas fa-shopping-cart text-primary-600"></i>
                                <span class="hidden sm:inline">Panier</span>
                                <span id="cartCounter" class="absolute -top-2 -right-2 bg-primary-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center" style="display: none;">0</span>
                            </div>
                        </a>
                        
                        <a href="?<?php echo $currentPage ? 'page=' . $currentPage . '&' : ''; ?>lang=<?php echo $lang === 'fr' ? 'en' : 'fr'; ?>" 
                           class="flex items-center gap-1 px-2 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-100 transition-colors">
                            <i class="fas fa-globe text-xs"></i>
                            <?php echo $lang === 'fr' ? 'EN' : 'FR'; ?>
                        </a>
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <!-- Utilisateur connecté -->
                            <a href="?page=espace-client" 
                               class="flex items-center gap-1.5 px-3 py-1.5 text-xs bg-emerald-600 text-white rounded hover:bg-emerald-700 transition-colors font-medium">
                                <i class="fas fa-user text-xs"></i>
                                <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                            </a>
                        <?php else: ?>
                            <!-- Non connecté -->
                            <a href="?page=connexion" 
                               class="flex items-center gap-1.5 px-3 py-1.5 text-xs bg-primary-600 text-white rounded hover:bg-primary-700 transition-colors font-medium">
                                <i class="fas fa-user text-xs"></i>
                                <?php echo $lang === 'fr' ? 'Connexion' : 'Login'; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </nav>

                <div class="flex items-center gap-2 md:hidden">
                    <!-- Panier mobile -->
                    <a href="?page=panier" class="relative">
                        <div class="flex items-center gap-1 px-2 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            <i class="fas fa-shopping-cart text-primary-600"></i>
                            <span id="cartCounterMobile" class="absolute -top-1 -right-1 bg-primary-600 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center text-[10px]" style="display: none;">0</span>
                        </div>
                    </a>
                    
                    <a href="?page=connexion"
                       class="flex items-center gap-1.5 px-2 py-1.5 text-xs bg-primary-600 text-white rounded hover:bg-primary-700 font-medium">
                        <i class="fas fa-lock text-xs"></i>
                    </a>
                    <a href="?<?php echo $currentPage ? 'page=' . $currentPage . '&' : ''; ?>lang=<?php echo $lang === 'fr' ? 'en' : 'fr'; ?>" 
                       class="flex items-center gap-1 px-2 py-1.5 text-xs border border-gray-300 rounded hover:bg-gray-100">
                        <i class="fas fa-globe text-xs"></i>
                        <?php echo $lang === 'fr' ? 'EN' : 'FR'; ?>
                    </a>
                    <button id="mobileMenuBtn" class="p-2 text-gray-600 hover:text-primary-600">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>

            <nav id="mobileMenu" class="hidden md:hidden border-t border-gray-200 bg-white pb-4">
                <?php foreach ($navItems as $item): 
                    $isActive = ($currentPage === $item['key'] || ($currentPage === 'home' && $item['key'] === 'home'));
                    $activeClass = $isActive ? 'text-primary-600 bg-primary-50' : 'text-gray-600 hover:bg-primary-50';
                ?>
                    <a href="<?php echo $item['url']; ?>" class="block px-6 py-3 text-sm font-medium transition-colors <?php echo $activeClass; ?>">
                        <?php echo __($item['label']); ?>
                    </a>
                <?php endforeach; ?>
                <div class="border-t border-gray-200 mt-2 pt-2 px-4 space-y-1">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="?page=espace-client" class="flex items-center gap-2 px-2 py-3 text-sm font-medium text-emerald-600">
                            <i class="fas fa-user"></i>
                            Mon compte (<?php echo htmlspecialchars($_SESSION['user_name']); ?>)
                        </a>
                    <?php else: ?>
                        <a href="?page=connexion" class="flex items-center gap-2 px-2 py-3 text-sm font-medium text-primary-600">
                            <i class="fas fa-user"></i>
                            Connexion / Inscription
                        </a>
                    <?php endif; ?>
                    <a href="?page=connexion" class="flex items-center gap-2 px-2 py-3 text-sm font-medium text-gray-600">
                        <i class="fas fa-lock"></i>
                        Administration
                    </a>
                </div>
            </nav>
        </div>
    </header>

    <main class="flex-1">
