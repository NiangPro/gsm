<?php
// Récupération des statistiques réelles depuis la base de données
$pdo = getDB();
$pendingOrdersCount = 0;
$totalOrdersCount = 0;
$totalRevenue = 0;

if ($pdo) {
    try {
        // Compter les commandes en attente
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'En attente'");
        $stmt->execute();
        $pendingOrdersCount = $stmt->fetch()['count'];
        
        // Compter le total des commandes
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders");
        $stmt->execute();
        $totalOrdersCount = $stmt->fetch()['count'];
        
        // Calculer le revenu total
        $stmt = $pdo->prepare("SELECT SUM(total_amount) as total FROM orders WHERE status != 'Annulée'");
        $stmt->execute();
        $revenue = $stmt->fetch()['total'];
        $totalRevenue = $revenue ? number_format($revenue / 1000, 0) . 'K' : '0K';
        
    } catch (PDOException $e) {
        error_log("Error fetching dashboard stats: " . $e->getMessage());
        $pendingOrdersCount = 3;
        $totalOrdersCount = 24;
        $totalRevenue = '285K';
    }
} else {
    // Valeurs par défaut si la BDD n'est pas disponible
    $pendingOrdersCount = 3;
    $totalOrdersCount = 24;
    $totalRevenue = '285K';
}

// Récupérer le nombre de produits
$totalProductsCount = 0;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products");
        $stmt->execute();
        $totalProductsCount = $stmt->fetch()['count'];
    } catch (PDOException $e) {
        error_log("Error counting products: " . $e->getMessage());
        $totalProductsCount = 14;
    }
} else {
    $totalProductsCount = 14;
}

// Récupérer le nombre de messages non lus
$unreadMessagesCount = 0;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = FALSE");
        $stmt->execute();
        $unreadMessagesCount = $stmt->fetch()['count'];
    } catch (PDOException $e) {
        error_log("Error counting messages: " . $e->getMessage());
        $unreadMessagesCount = 6;
    }
} else {
    $unreadMessagesCount = 6;
}

$stats = [
    [
        'label' => 'Total Produits',
        'value' => $totalProductsCount,
        'change' => '+2',
        'up' => true,
        'icon' => 'fa-box',
        'bgIcon' => 'bg-emerald-100 text-emerald-600',
        'link' => '?page=admin&action=produits'
    ],
    [
        'label' => 'Commandes',
        'value' => $totalOrdersCount,
        'change' => '+' . $pendingOrdersCount,
        'up' => true,
        'icon' => 'fa-shopping-bag',
        'bgIcon' => 'bg-blue-100 text-blue-600',
        'link' => '?page=admin&action=commandes',
        'badge' => $pendingOrdersCount > 0 ? $pendingOrdersCount : null
    ],
    [
        'label' => 'Messages',
        'value' => $unreadMessagesCount,
        'change' => '+' . $unreadMessagesCount,
        'up' => true,
        'icon' => 'fa-envelope',
        'bgIcon' => 'bg-amber-100 text-amber-600',
        'link' => '?page=admin&action=messages',
        'badge' => $unreadMessagesCount > 0 ? $unreadMessagesCount : null
    ],
    [
        'label' => 'Revenus',
        'value' => $totalRevenue . ' FCFA',
        'change' => '+12%',
        'up' => true,
        'icon' => 'fa-chart-line',
        'bgIcon' => 'bg-purple-100 text-purple-600',
        'link' => '?page=admin&action=commandes'
    ],
];

$quickActions = [
    ['label' => 'Nouveau Produit', 'icon' => 'fa-plus', 'color' => 'bg-emerald-500 hover:bg-emerald-600', 'link' => '?page=admin&action=produits'],
    ['label' => 'Nouvelle Commande', 'icon' => 'fa-shopping-cart', 'color' => 'bg-blue-500 hover:bg-blue-600', 'link' => '?page=admin&action=commandes'],
    ['label' => 'Article Blog', 'icon' => 'fa-pen', 'color' => 'bg-purple-500 hover:bg-purple-600', 'link' => '?page=admin&action=blog'],
    ['label' => 'Message', 'icon' => 'fa-envelope', 'color' => 'bg-amber-500 hover:bg-amber-600', 'link' => '?page=admin&action=messages'],
];

// Récupérer les commandes réelles
$recentOrders = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                o.order_number,
                c.name as client_name,
                oi.product_name,
                o.total_amount,
                o.status,
                o.created_at
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            ORDER BY o.created_at DESC
            LIMIT 5
        ");
        $stmt->execute();
        $dbOrders = $stmt->fetchAll();
        
        foreach ($dbOrders as $order) {
            $recentOrders[] = [
                'id' => $order['order_number'],
                'client' => $order['client_name'],
                'avatar' => strtoupper(substr($order['client_name'], 0, 1)),
                'produit' => $order['product_name'] ?: 'Produit',
                'montant' => number_format($order['total_amount'], 0, ',', ' ') . ' FCFA',
                'statut' => $order['status'],
                'date' => formatDate($order['created_at'])
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching recent orders: " . $e->getMessage());
        // Garder les données mock en cas d'erreur
        $recentOrders = [
            ['id' => 'CMD-001', 'client' => 'Fatou Diallo', 'avatar' => 'F', 'produit' => 'Jus de Bissap 500ml', 'montant' => '3 000 FCFA', 'statut' => 'Livrée', 'date' => 'Aujourd\'hui'],
            ['id' => 'CMD-002', 'client' => 'Amadou Sow', 'avatar' => 'A', 'produit' => 'Confiture Mangue 250g', 'montant' => '2 500 FCFA', 'statut' => 'En préparation', 'date' => 'Hier'],
            ['id' => 'CMD-003', 'client' => 'Marie Ndiaye', 'avatar' => 'M', 'produit' => 'Sirop Gingembre 500ml', 'montant' => '3 000 FCFA', 'statut' => 'En attente', 'date' => 'Hier'],
            ['id' => 'CMD-004', 'client' => 'Ousmane Ba', 'avatar' => 'O', 'produit' => 'Jus de Bouye 500ml', 'montant' => '4 000 FCFA', 'statut' => 'Livrée', 'date' => 'Il y a 2j'],
            ['id' => 'CMD-005', 'client' => 'Aïda Fall', 'avatar' => 'A', 'produit' => 'Poudre Moringa 250g', 'montant' => '2 500 FCFA', 'statut' => 'En livraison', 'date' => 'Il y a 3j'],
        ];
    }
} else {
    // Données mock si BDD indisponible
    $recentOrders = [
        ['id' => 'CMD-001', 'client' => 'Fatou Diallo', 'avatar' => 'F', 'produit' => 'Jus de Bissap 500ml', 'montant' => '3 000 FCFA', 'statut' => 'Livrée', 'date' => 'Aujourd\'hui'],
        ['id' => 'CMD-002', 'client' => 'Amadou Sow', 'avatar' => 'A', 'produit' => 'Confiture Mangue 250g', 'montant' => '2 500 FCFA', 'statut' => 'En préparation', 'date' => 'Hier'],
        ['id' => 'CMD-003', 'client' => 'Marie Ndiaye', 'avatar' => 'M', 'produit' => 'Sirop Gingembre 500ml', 'montant' => '3 000 FCFA', 'statut' => 'En attente', 'date' => 'Hier'],
        ['id' => 'CMD-004', 'client' => 'Ousmane Ba', 'avatar' => 'O', 'produit' => 'Jus de Bouye 500ml', 'montant' => '4 000 FCFA', 'statut' => 'Livrée', 'date' => 'Il y a 2j'],
        ['id' => 'CMD-005', 'client' => 'Aïda Fall', 'avatar' => 'A', 'produit' => 'Poudre Moringa 250g', 'montant' => '2 500 FCFA', 'statut' => 'En livraison', 'date' => 'Il y a 3j'],
    ];
}

function formatDate($date) {
    $timestamp = strtotime($date);
    $now = time();
    $diff = $now - $timestamp;
    
    if ($diff < 3600) {
        return 'Il y a ' . floor($diff / 60) . ' min';
    } elseif ($diff < 86400) {
        return 'Il y a ' . floor($diff / 3600) . 'h';
    } elseif ($diff < 172800) {
        return 'Hier';
    } else {
        return 'Il y a ' . floor($diff / 86400) . 'j';
    }
}

// Récupérer les activités réelles depuis la base de données
$activities = [];
if ($pdo) {
    try {
        // Récupérer les commandes récentes
        $stmt = $pdo->prepare("
            SELECT 
                'Nouvelle commande reçue' as text,
                c.name as detail,
                o.total_amount as amount,
                o.created_at
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            ORDER BY o.created_at DESC
            LIMIT 3
        ");
        $stmt->execute();
        $recentActivities = $stmt->fetchAll();
        
        foreach ($recentActivities as $activity) {
            $activities[] = [
                'icon' => 'fa-shopping-bag',
                'color' => 'bg-emerald-500',
                'text' => $activity['text'],
                'detail' => $activity['detail'] . ' • ' . number_format($activity['amount'], 0, ',', ' ') . ' FCFA',
                'time' => formatDate($activity['created_at'])
            ];
        }
        
        // Récupérer les messages récents
        $stmt = $pdo->prepare("
            SELECT 
                'Nouveau message' as text,
                name as detail,
                subject,
                created_at
            FROM contact_messages
            WHERE is_read = FALSE
            ORDER BY created_at DESC
            LIMIT 2
        ");
        $stmt->execute();
        $recentMessages = $stmt->fetchAll();
        
        foreach ($recentMessages as $message) {
            $activities[] = [
                'icon' => 'fa-envelope',
                'color' => 'bg-amber-500',
                'text' => $message['text'],
                'detail' => $message['detail'] . ($message['subject'] ? ' • ' . $message['subject'] : ''),
                'time' => formatDate($message['created_at'])
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error fetching activities: " . $e->getMessage());
        // Garder les données mock en cas d'erreur
        $activities = [
            ['icon' => 'fa-shopping-bag', 'color' => 'bg-emerald-500', 'text' => 'Nouvelle commande reçue', 'detail' => 'Fatou Diallo • 3 000 FCFA', 'time' => 'Il y a 10 min'],
            ['icon' => 'fa-box', 'color' => 'bg-blue-500', 'text' => 'Stock mis à jour', 'detail' => 'Confiture Mangue : +20 unités', 'time' => 'Il y a 35 min'],
            ['icon' => 'fa-envelope', 'color' => 'bg-amber-500', 'text' => 'Nouveau message', 'detail' => 'Jean Dupont • Demande de partenariat', 'time' => 'Il y a 1h'],
            ['icon' => 'fa-newspaper', 'color' => 'bg-purple-500', 'text' => 'Article publié', 'detail' => 'Les bienfaits du Moringa', 'time' => 'Il y a 2h'],
            ['icon' => 'fa-user', 'color' => 'bg-pink-500', 'text' => 'Nouveau membre', 'detail' => 'Awa Diallo a rejoint l\'équipe', 'time' => 'Il y a 4h'],
        ];
    }
} else {
    // Données mock si BDD indisponible
    $activities = [
        ['icon' => 'fa-shopping-bag', 'color' => 'bg-emerald-500', 'text' => 'Nouvelle commande reçue', 'detail' => 'Fatou Diallo • 3 000 FCFA', 'time' => 'Il y a 10 min'],
        ['icon' => 'fa-box', 'color' => 'bg-blue-500', 'text' => 'Stock mis à jour', 'detail' => 'Confiture Mangue : +20 unités', 'time' => 'Il y a 35 min'],
        ['icon' => 'fa-envelope', 'color' => 'bg-amber-500', 'text' => 'Nouveau message', 'detail' => 'Jean Dupont • Demande de partenariat', 'time' => 'Il y a 1h'],
        ['icon' => 'fa-newspaper', 'color' => 'bg-purple-500', 'text' => 'Article publié', 'detail' => 'Les bienfaits du Moringa', 'time' => 'Il y a 2h'],
        ['icon' => 'fa-user', 'color' => 'bg-pink-500', 'text' => 'Nouveau membre', 'detail' => 'Awa Diallo a rejoint l\'équipe', 'time' => 'Il y a 4h'],
    ];
}

// Récupérer les produits avec stock bas
$productsLowStock = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                name,
                stock_quantity as stock,
                50 as max
            FROM products 
            WHERE stock_quantity < 20 AND is_available = TRUE
            ORDER BY stock_quantity ASC
            LIMIT 5
        ");
        $stmt->execute();
        $lowStockProducts = $stmt->fetchAll();
        
        foreach ($lowStockProducts as $product) {
            $productsLowStock[] = [
                'name' => $product['name'],
                'stock' => $product['stock'],
                'max' => $product['max']
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching low stock products: " . $e->getMessage());
        // Garder les données mock en cas d'erreur
        $productsLowStock = [
            ['name' => 'Confiture Baobab', 'stock' => 5, 'max' => 50],
            ['name' => 'Sirop Gingembre', 'stock' => 8, 'max' => 45],
            ['name' => 'Jus de Bissap', 'stock' => 12, 'max' => 100],
        ];
    }
} else {
    // Données mock si BDD indisponible
    $productsLowStock = [
        ['name' => 'Confiture Baobab', 'stock' => 5, 'max' => 50],
        ['name' => 'Sirop Gingembre', 'stock' => 8, 'max' => 45],
        ['name' => 'Jus de Bissap', 'stock' => 12, 'max' => 100],
    ];
}

function getStatusStyle($status) {
    $styles = [
        'Livrée' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'Confirmée' => 'bg-blue-100 text-blue-700 border-blue-200',
        'En préparation' => 'bg-amber-100 text-amber-700 border-amber-200',
        'En livraison' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
        'En attente' => 'bg-gray-100 text-gray-700 border-gray-200',
        'Annulée' => 'bg-red-100 text-red-700 border-red-200',
    ];
    return $styles[$status] ?? $styles['En attente'];
}

$adminEmail = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'admin@sokhnamai.sn';
$adminName = explode('@', $adminEmail)[0];
$hour = date('H');
if ($hour < 12) $greeting = 'Bonjour';
elseif ($hour < 18) $greeting = 'Bon après-midi';
else $greeting = 'Bonsoir';
?>

<style>
.admin-header {
    position: fixed;
    top: 0;
    left: 16rem; /* Décalé pour ne pas couvrir le sidebar (largeur du sidebar) */
    right: 0;
    z-index: 50;
    background: white;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(8px);
    transition: left 0.3s ease; /* Animation pour la transition du sidebar */
}

/* Quand le sidebar est réduit */
.sidebar-collapsed .admin-header {
    left: 5rem; /* Largeur du sidebar réduit */
}

/* Ajouter un padding au contenu principal pour compenser le header fixe */
.dashboard-content {
    padding-top: 120px; /* Hauteur du header + marge */
    transition: padding-left 0.3s ease; /* Animation pour la transition du sidebar */
    padding-right: 1.5rem;
    width: 100%; /* Prend toute la largeur */
}

/* Adaptation mobile */
@media (max-width: 768px) {
    .admin-header {
        left: 0; /* Header prend toute la largeur sur mobile */
        padding: 1rem;
    }
    
    .admin-header .flex {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }
    
    .admin-header h1 {
        font-size: 1.5rem;
    }
    
    .admin-header p {
        font-size: 0.875rem;
    }
    
    .dashboard-content {
        padding-top: 140px; /* Plus de padding pour header mobile */
        padding-left: 1rem; /* Padding sur mobile car sidebar masquée */
        padding-right: 1rem;
    }
    
    /* Cacher la date sur mobile */
    #current-date {
        display: none;
    }
    
    /* Adapter les quick actions */
    .grid.grid-cols-2.sm\:grid-cols-4 {
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    
    /* Adapter les stats cards */
    .grid.grid-cols-1.sm\:grid-cols-2.lg\:grid-cols-4 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    /* Adapter le layout principal */
    .grid.grid-cols-1.lg\:grid-cols-3 {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    /* Responsive pour les tableaux */
    .overflow-x-auto {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Adapter les graphiques */
    .h-48 {
        height: 200px;
    }
}

@media (max-width: 480px) {
    .admin-header h1 {
        font-size: 1.25rem;
    }
    
    .dashboard-content {
        padding-top: 160px; /* Encore plus de padding pour petits écrans */
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    .grid.grid-cols-2.sm\:grid-cols-4 {
        grid-template-columns: 1fr;
    }
    
    /* Adapter les textes */
    .text-3xl {
        font-size: 1.875rem;
    }
    
    .text-lg {
        font-size: 1rem;
    }
}

.user-dropdown {
    position: relative;
}

.user-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
}

.user-dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
}

.user-dropdown-item:last-child {
    border-bottom: none;
}

.user-dropdown-item:hover {
    background: #f9fafb;
    color: #059669;
}

.user-dropdown-item:first-child:hover {
    background: #f0fdf4;
    border-radius: 0.75rem 0.75rem 0 0;
}

.user-dropdown-item:last-child:hover {
    background: #fef2f2;
    color: #dc2626;
    border-radius: 0 0 0.75rem 0.75rem;
}
</style>

<!-- Header fixe avec dropdown utilisateur -->
<div class="admin-header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-4">
        <div>
            <h1 class="text-3xl font-heading font-bold text-gray-900"><?php echo $greeting; ?>, <?php echo ucfirst($adminName); ?> 👋</h1>
            <p class="text-gray-500 mt-1">Voici ce qui se passe avec votre boutique aujourd'hui.</p>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500" id="current-date"></span>
            
            <!-- Dropdown utilisateur -->
            <div class="user-dropdown">
                <button onclick="toggleUserDropdown()" class="flex items-center gap-2 hover:bg-gray-50 rounded-lg p-2 transition-colors">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white font-bold shadow-lg">
                        <?php echo strtoupper(substr($adminName, 0, 1)); ?>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" id="dropdown-arrow"></i>
                </button>
                
                <div class="user-dropdown-menu" id="user-dropdown-menu">
                    <a href="?page=admin&action=profile" class="user-dropdown-item">
                        <i class="fas fa-user text-gray-400 w-4"></i>
                        <span>Profil</span>
                    </a>
                    <a href="?page=admin&action=settings" class="user-dropdown-item">
                        <i class="fas fa-cog text-gray-400 w-4"></i>
                        <span>Paramètres</span>
                    </a>
                    <a href="?page=admin&action=logout" class="user-dropdown-item">
                        <i class="fas fa-sign-out-alt text-gray-400 w-4"></i>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contenu principal avec padding pour compenser le header fixe -->
<div class="dashboard-content">
    <div class="space-y-8">
        <!-- Quick Actions -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 w-full">
        <?php foreach ($quickActions as $action): ?>
            <a href="<?php echo $action['link']; ?>" 
               class="flex items-center gap-3 px-4 py-3 <?php echo $action['color']; ?> text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-200 transform hover:-translate-y-0.5">
                <i class="fas <?php echo $action['icon']; ?>"></i>
                <span class="text-sm font-medium"><?php echo $action['label']; ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Stats Cards modernes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <?php foreach ($stats as $stat): ?>
            <a href="<?php echo $stat['link']; ?>" class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="p-3 rounded-xl <?php echo $stat['bgIcon']; ?> transition-transform group-hover:scale-110 relative">
                            <i class="fas <?php echo $stat['icon']; ?> text-lg"></i>
                            <?php if (isset($stat['badge']) && $stat['badge']): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-bold animate-pulse">
                                    <?php echo $stat['badge']; ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center gap-1 text-xs font-medium <?php echo $stat['up'] ? 'text-emerald-600' : 'text-red-600'; ?> bg-gray-50 px-2 py-1 rounded-full">
                            <i class="fas fa-<?php echo $stat['up'] ? 'arrow-trend-up' : 'arrow-trend-down'; ?>"></i>
                            <?php echo $stat['change']; ?>
                        </div>
                    </div>
                    <div class="mt-4">
                        <p class="text-3xl font-bold text-gray-900"><?php echo $stat['value']; ?></p>
                        <p class="text-sm text-gray-500 mt-1"><?php echo $stat['label']; ?></p>
                    </div>
                </div>
                <div class="h-1 bg-gradient-to-r from-emerald-500 to-emerald-600 transform scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
            </a>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-1 gap-6 w-full">
        <!-- Commandes récentes -->
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden w-full">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-emerald-100 rounded-lg">
                        <i class="fas fa-shopping-bag text-emerald-600"></i>
                    </div>
                    <h3 class="text-lg font-heading font-semibold text-gray-900">Commandes récentes</h3>
                </div>
                <a href="?page=admin&action=commandes" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium flex items-center gap-1">
                    Voir tout <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-3 px-4 text-gray-500 font-medium text-xs uppercase tracking-wider">Commande</th>
                            <th class="text-left py-3 px-4 text-gray-500 font-medium text-xs uppercase tracking-wider">Client</th>
                            <th class="text-left py-3 px-4 text-gray-500 font-medium text-xs uppercase tracking-wider hidden sm:table-cell">Produit</th>
                            <th class="text-left py-3 px-4 text-gray-500 font-medium text-xs uppercase tracking-wider">Montant</th>
                            <th class="text-left py-3 px-4 text-gray-500 font-medium text-xs uppercase tracking-wider">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($recentOrders as $order): ?>
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="py-3 px-4">
                                    <span class="font-mono text-xs font-medium text-gray-900 bg-gray-100 px-2 py-1 rounded"><?php echo $order['id']; ?></span>
                                    <p class="text-xs text-gray-400 mt-1"><?php echo $order['date']; ?></p>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white text-xs font-bold">
                                            <?php echo $order['avatar']; ?>
                                        </div>
                                        <span class="font-medium text-gray-900"><?php echo $order['client']; ?></span>
                                    </div>
                                </td>
                                <td class="py-3 px-4 hidden sm:table-cell text-gray-500"><?php echo $order['produit']; ?></td>
                                <td class="py-3 px-4 font-semibold text-gray-900"><?php echo $order['montant']; ?></td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border <?php echo getStatusStyle($order['statut']); ?>">
                                        <span class="w-1.5 h-1.5 rounded-full mr-1.5 <?php echo $order['statut'] === 'Livrée' ? 'bg-emerald-500' : ($order['statut'] === 'En attente' ? 'bg-gray-400' : 'bg-amber-500'); ?>"></span>
                                        <?php echo $order['statut']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Colonne latérale -->
        <div class="space-y-6">
            <!-- Activité récente -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i class="fas fa-bolt text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-heading font-semibold text-gray-900">Activité récente</h3>
                    </div>
                </div>
                <div class="p-5">
                    <div class="space-y-4">
                        <?php foreach ($activities as $act): ?>
                            <div class="flex gap-3 group cursor-pointer">
                                <div class="relative">
                                    <div class="h-8 w-8 rounded-full <?php echo $act['color']; ?> flex items-center justify-center text-white text-xs shrink-0 shadow-md group-hover:scale-110 transition-transform">
                                        <i class="fas <?php echo $act['icon']; ?>"></i>
                                    </div>
                                    <div class="absolute top-8 left-1/2 -translate-x-1/2 w-px h-full bg-gray-200 <?php echo $act === end($activities) ? 'hidden' : ''; ?>"></div>
                                </div>
                                <div class="flex-1 pb-4">
                                    <p class="text-sm font-medium text-gray-900 group-hover:text-emerald-600 transition-colors"><?php echo $act['text']; ?></p>
                                    <p class="text-xs text-gray-500 mt-0.5"><?php echo $act['detail']; ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><?php echo $act['time']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Stock alertes -->
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-heading font-semibold text-gray-900">Stock faible</h3>
                    </div>
                    <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded-full font-medium"><?php echo count($productsLowStock); ?> alertes</span>
                </div>
                <div class="p-5 space-y-4">
                    <?php foreach ($productsLowStock as $prod): 
                        $percent = ($prod['stock'] / $prod['max']) * 100;
                        $color = $percent < 20 ? 'bg-red-500' : 'bg-amber-500';
                    ?>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700"><?php echo $prod['name']; ?></span>
                                <span class="text-xs text-gray-500"><?php echo $prod['stock']; ?>/<?php echo $prod['max']; ?></span>
                            </div>
                            <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full <?php echo $color; ?> rounded-full transition-all duration-500" style="width: <?php echo $percent; ?>"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="p-4 bg-gray-50 border-t border-gray-100">
                    <a href="?page=admin&action=produits" class="text-sm text-emerald-600 hover:text-emerald-700 font-medium flex items-center justify-center gap-1">
                        Gérer le stock <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mini graphique statistiques -->
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 w-full">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-chart-bar text-purple-600"></i>
                </div>
                <div>
                    <h3 class="text-lg font-heading font-semibold text-gray-900">Aperçu des ventes</h3>
                    <p class="text-sm text-gray-500">Évolution sur les 7 derniers jours</p>
                </div>
            </div>
            <select class="text-sm border-gray-200 rounded-lg bg-gray-50 px-3 py-1.5 focus:ring-emerald-500 focus:border-emerald-500">
                <option>Cette semaine</option>
                <option>Ce mois</option>
                <option>Cette année</option>
            </select>
        </div>
        <div class="h-48 flex items-end justify-between gap-2">
            <?php 
            $chartData = [45, 60, 35, 80, 55, 90, 70];
            $days = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
            foreach ($chartData as $i => $value): 
                $height = ($value / max($chartData)) * 100;
                $isMax = $value == max($chartData);
            ?>
                <div class="flex-1 flex flex-col items-center gap-2 group cursor-pointer">
                    <div class="relative w-full flex items-end justify-center">
                        <div class="w-full max-w-[40px] bg-gray-100 rounded-t-lg relative overflow-hidden">
                            <div class="absolute bottom-0 w-full <?php echo $isMax ? 'bg-gradient-to-t from-emerald-500 to-emerald-400' : 'bg-gradient-to-t from-gray-400 to-gray-300'; ?> rounded-t-lg transition-all duration-500 group-hover:from-emerald-500 group-hover:to-emerald-400" 
                                 style="height: 0%" 
                                 data-height="<?php echo $height; ?>"></div>
                        </div>
                        <div class="absolute -top-8 opacity-0 group-hover:opacity-100 transition-opacity bg-gray-900 text-white text-xs px-2 py-1 rounded whitespace-nowrap">
                            <?php echo $value; ?>k FCFA
                        </div>
                    </div>
                    <span class="text-xs text-gray-500 font-medium"><?php echo $days[$i]; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>
</div>

<script>
    // Gestion du dropdown utilisateur
    function toggleUserDropdown() {
        const dropdown = document.getElementById('user-dropdown-menu');
        const arrow = document.getElementById('dropdown-arrow');
        
        if (dropdown) {
            dropdown.classList.toggle('show');
            if (arrow) {
                arrow.style.transform = dropdown.classList.contains('show') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }
    }
    
    // Fermer le dropdown en cliquant à l'extérieur
    document.addEventListener('click', function(e) {
        const dropdown = document.getElementById('user-dropdown-menu');
        const dropdownButton = e.target.closest('.user-dropdown button');
        
        if (!dropdownButton && dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            const arrow = document.getElementById('dropdown-arrow');
            if (arrow) {
                arrow.style.transform = 'rotate(0deg)';
            }
        }
    });
    
    // Fermer avec la touche Echape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const dropdown = document.getElementById('user-dropdown-menu');
            const arrow = document.getElementById('dropdown-arrow');
            
            if (dropdown && dropdown.classList.contains('show')) {
                dropdown.classList.remove('show');
                if (arrow) {
                    arrow.style.transform = 'rotate(0deg)';
                }
            }
        }
    });

    // Date actuelle et heure dynamique
    function updateDateTime() {
        const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        const now = new Date();
        const dateStr = now.toLocaleDateString('fr-FR', dateOptions);
        const timeStr = now.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
        
        const dateElement = document.getElementById('current-date');
        if (dateElement) {
            dateElement.textContent = dateStr + ' - ' + timeStr;
        }
    }
    
    updateDateTime();
    setInterval(updateDateTime, 60000); // Mise à jour chaque minute
    
    // Animation des barres de graphique
    setTimeout(() => {
        document.querySelectorAll('[data-height]').forEach(bar => {
            bar.style.height = bar.dataset.height + '%';
        });
    }, 300);
    
    // Animation des nombres dans les cartes statistiques
    document.addEventListener('DOMContentLoaded', function() {
        const statValues = document.querySelectorAll('.text-3xl.font-bold');
        statValues.forEach(stat => {
            const finalValue = stat.textContent;
            const isNumeric = /^\d/.test(finalValue);
            
            if (isNumeric) {
                const numValue = parseInt(finalValue.replace(/[^\d]/g, ''));
                let currentValue = 0;
                const increment = numValue / 30;
                const suffix = finalValue.replace(/[\d\s]/g, '');
                
                const counter = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= numValue) {
                        currentValue = numValue;
                        clearInterval(counter);
                    }
                    stat.textContent = Math.floor(currentValue).toLocaleString('fr-FR') + suffix;
                }, 50);
            }
        });
        
        // Effet de ripple sur les boutons d'action rapide
        const quickActions = document.querySelectorAll('.grid.grid-cols-2.sm\\:grid-cols-4 a');
        quickActions.forEach(action => {
            action.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.cssText = `
                    position: absolute;
                    width: ${size}px;
                    height: ${size}px;
                    left: ${x}px;
                    top: ${y}px;
                    background: rgba(255, 255, 255, 0.3);
                    border-radius: 50%;
                    transform: scale(0);
                    animation: ripple 0.6s ease-out;
                    pointer-events: none;
                `;
                
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                setTimeout(() => ripple.remove(), 600);
            });
        });
        
        // Gestion des clics sur les cartes statistiques
        const statCards = document.querySelectorAll('.group.bg-white.rounded-2xl');
        statCards.forEach(card => {
            card.addEventListener('click', function(e) {
                // Ajouter un effet de pulse
                this.style.animation = 'pulse 0.3s ease-out';
                setTimeout(() => {
                    this.style.animation = '';
                }, 300);
            });
        });
        
        // Rafraîchissement automatique des données
        setInterval(() => {
            console.log('Vérification des nouvelles données...');
            // Ici vous pouvez ajouter un appel AJAX pour rafraîchir
            checkForNewData();
        }, 30000); // Toutes les 30 secondes
    });
    
    // Vérification des nouvelles données
    function checkForNewData() {
        const lastCheck = localStorage.getItem('lastDashboardCheck') || (Date.now() - 30000);
        
        fetch('dashboard_api.php?last_check=' + Math.floor(lastCheck / 1000), {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            localStorage.setItem('lastDashboardCheck', Date.now());
            
            if (data.newOrders > 0) {
                showNotification(data.newOrders + ' nouvelle(s) commande(s) reçue(s)!', 'success');
                updateOrderCount(data.newOrders);
            }
            if (data.newMessages > 0) {
                showNotification(data.newMessages + ' nouveau(x) message(s) reçu(s)!', 'info');
                updateMessageCount(data.newMessages);
            }
            
            // Mettre à jour les statistiques si nécessaire
            if (data.stats) {
                updateDashboardStats(data.stats);
            }
        })
        .catch(error => {
            console.log('Pas de nouvelles données ou erreur de connexion:', error);
        });
    }
    
    // Mettre à jour les statistiques du dashboard
    function updateDashboardStats(stats) {
        const statElements = document.querySelectorAll('.text-3xl.font-bold');
        statElements.forEach(element => {
            const parentCard = element.closest('a');
            if (parentCard) {
                const link = parentCard.getAttribute('href');
                
                if (link && link.includes('produits') && stats.totalProducts !== undefined) {
                    animateValue(element, parseInt(element.textContent.replace(/[^\d]/g, '')), stats.totalProducts, 500);
                } else if (link && link.includes('commandes') && stats.totalOrders !== undefined) {
                    animateValue(element, parseInt(element.textContent.replace(/[^\d]/g, '')), stats.totalOrders, 500);
                }
            }
        });
    }
    
    // Animation des valeurs
    function animateValue(element, start, end, duration) {
        const range = end - start;
        const increment = range / (duration / 16);
        let current = start;
        
        const timer = setInterval(() => {
            current += increment;
            if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
                current = end;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current).toLocaleString('fr-FR');
        }, 16);
    }
    
    // Système de notifications
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        const colors = {
            success: 'bg-emerald-500',
            error: 'bg-red-500',
            info: 'bg-blue-500',
            warning: 'bg-amber-500'
        };
        
        notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50 transform translate-x-full transition-transform duration-300`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Sortie automatique
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => notification.remove(), 300);
        }, 4000);
    }
    
    // Mise à jour des compteurs
    function updateOrderCount(count) {
        const badges = document.querySelectorAll('.bg-red-500.text-white.text-xs.rounded-full');
        badges.forEach(badge => {
            if (badge.textContent.includes('Commandes')) {
                const currentCount = parseInt(badge.textContent) || 0;
                badge.textContent = currentCount + count;
                badge.classList.add('animate-pulse');
                setTimeout(() => badge.classList.remove('animate-pulse'), 2000);
            }
        });
    }
    
    function updateMessageCount(count) {
        const badges = document.querySelectorAll('.bg-red-500.text-white.text-xs.rounded-full');
        badges.forEach(badge => {
            if (badge.textContent.includes('Messages')) {
                const currentCount = parseInt(badge.textContent) || 0;
                badge.textContent = currentCount + count;
                badge.classList.add('animate-pulse');
                setTimeout(() => badge.classList.remove('animate-pulse'), 2000);
            }
        });
    }
    
    // Gestion du clavier pour les raccourcis
    document.addEventListener('keydown', function(e) {
        // Ctrl + N : Nouveau produit
        if (e.ctrlKey && e.key === 'n') {
            e.preventDefault();
            window.location.href = '?page=admin&action=produits#new';
        }
        // Ctrl + O : Commandes
        if (e.ctrlKey && e.key === 'o') {
            e.preventDefault();
            window.location.href = '?page=admin&action=commandes';
        }
        // Ctrl + M : Messages
        if (e.ctrlKey && e.key === 'm') {
            e.preventDefault();
            window.location.href = '?page=admin&action=messages';
        }
    });
    
    // Animation CSS pour l'effet ripple
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        .group:hover .group-hover\\:scale-110 {
            transform: scale(1.1);
        }
        
        .transform.hover\\:-translate-y-1:hover {
            transform: translateY(-4px);
        }
    `;
    document.head.appendChild(style);
</script>
