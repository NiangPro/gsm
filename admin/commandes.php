<?php
$statusOptions = ['En attente', 'Confirmée', 'En préparation', 'En livraison', 'Livrée', 'Annulée'];

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'update_status') {
        $orderId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $newStatus = isset($_POST['status']) ? $_POST['status'] : '';
        $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
        
        if ($orderId && $newStatus && in_array($newStatus, $statusOptions)) {
            $pdo = getDB();
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("UPDATE orders SET status = ?, delivery_notes = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$newStatus, $notes, $orderId]);
                    
                    // Journaliser l'action
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['admin_id'] ?? 1, 'update_status', 'order', $orderId, json_encode(['old_status' => '', 'new_status' => $newStatus])]);
                    
                    echo "Statut mis à jour avec succès";
                } catch (PDOException $e) {
                    error_log("Error updating order status: " . $e->getMessage());
                    echo "Erreur lors de la mise à jour du statut";
                }
            }
        }
        exit;
    } elseif ($action === 'delete') {
        $orderId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        
        if ($orderId) {
            $pdo = getDB();
            if ($pdo) {
                try {
                    $pdo->beginTransaction();
                    
                    // Supprimer les items de commande d'abord
                    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
                    $stmt->execute([$orderId]);
                    
                    // Supprimer la commande
                    $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                    $stmt->execute([$orderId]);
                    
                    $pdo->commit();
                    
                    // Journaliser l'action
                    $stmt = $pdo->prepare("INSERT INTO activity_logs (admin_id, action, entity_type, entity_id, details) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$_SESSION['admin_id'] ?? 1, 'delete', 'order', $orderId, json_encode(['deleted' => true])]);
                    
                    echo "Commande supprimée avec succès";
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    error_log("Error deleting order: " . $e->getMessage());
                    echo "Erreur lors de la suppression";
                }
            }
        }
        exit;
    }
}

// Récupérer les commandes depuis la base de données
$orders = [];
$pdo = getDB();

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT 
                o.id,
                o.order_number,
                o.status,
                o.total_amount,
                o.delivery_address,
                o.delivery_notes,
                o.created_at,
                c.name as client_name,
                c.phone as client_phone,
                c.email as client_email,
                oi.product_name,
                oi.quantity,
                oi.weight,
                oi.unit_price,
                oi.subtotal
            FROM orders o
            LEFT JOIN customers c ON o.customer_id = c.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            ORDER BY o.created_at DESC
        ");
        $stmt->execute();
        $dbOrders = $stmt->fetchAll();
        
        // Grouper les commandes par numéro de commande
        $groupedOrders = [];
        foreach ($dbOrders as $order) {
            $orderNumber = $order['order_number'];
            if (!isset($groupedOrders[$orderNumber])) {
                $groupedOrders[$orderNumber] = [
                    'id' => $order['id'],
                    'order_number' => $order['order_number'],
                    'client_name' => $order['client_name'],
                    'client_phone' => $order['client_phone'],
                    'client_email' => $order['client_email'],
                    'total_price' => $order['total_amount'],
                    'status' => $order['status'],
                    'notes' => $order['delivery_notes'],
                    'created_at' => $order['created_at'],
                    'items' => []
                ];
            }
            $groupedOrders[$orderNumber]['items'][] = [
                'product_name' => $order['product_name'] ?: 'Produit',
                'quantity' => $order['quantity'],
                'weight' => $order['weight'] ?: '1 unité',
                'subtotal' => $order['subtotal']
            ];
        }
        
        // Convertir en format attendu
        foreach ($groupedOrders as $order) {
            $firstItem = $order['items'][0];
            $orders[] = [
                'id' => $order['order_number'],
                'client_name' => $order['client_name'],
                'client_phone' => $order['client_phone'],
                'client_email' => $order['client_email'],
                'product_name' => $firstItem['product_name'],
                'quantity' => $firstItem['quantity'],
                'weight' => $firstItem['weight'],
                'total_price' => $order['total_price'],
                'status' => $order['status'],
                'notes' => $order['notes'],
                'created_at' => $order['created_at']
            ];
        }
        
    } catch (PDOException $e) {
        error_log("Error fetching orders: " . $e->getMessage());
        // Conserver les données mock en cas d'erreur
        $orders = [
            ['id' => 'cmd-001', 'client_name' => 'Fatou Diallo', 'client_phone' => '+221 77 123 45 67', 'client_email' => 'fatou@email.com', 'product_name' => 'Jus de Bissap', 'quantity' => 2, 'weight' => '500g', 'total_price' => 6000, 'status' => 'Livrée', 'notes' => '', 'created_at' => '2024-01-15 10:30:00'],
            ['id' => 'cmd-002', 'client_name' => 'Amadou Sow', 'client_phone' => '+221 76 234 56 78', 'client_email' => null, 'product_name' => 'Confiture Mangue', 'quantity' => 3, 'weight' => '250g', 'total_price' => 7500, 'status' => 'En cours', 'notes' => 'Livraison express', 'created_at' => '2024-01-15 14:20:00'],
            ['id' => 'cmd-003', 'client_name' => 'Marie Ndiaye', 'client_phone' => '+221 70 345 67 89', 'client_email' => 'marie@email.com', 'product_name' => 'Sirop Gingembre', 'quantity' => 1, 'weight' => '500g', 'total_price' => 3000, 'status' => 'En attente', 'notes' => '', 'created_at' => '2024-01-16 09:15:00'],
            ['id' => 'cmd-004', 'client_name' => 'Ousmane Ba', 'client_phone' => '+221 78 456 78 90', 'client_email' => null, 'product_name' => 'Jus de Bouye', 'quantity' => 4, 'weight' => '500g', 'total_price' => 12000, 'status' => 'En préparation', 'notes' => '', 'created_at' => '2024-01-16 11:45:00'],
            ['id' => 'cmd-005', 'client_name' => 'Aïda Fall', 'client_phone' => '+221 76 567 89 01', 'client_email' => 'aida@email.com', 'product_name' => 'Poudre Moringa', 'quantity' => 2, 'weight' => '250g', 'total_price' => 5000, 'status' => 'En livraison', 'notes' => '', 'created_at' => '2024-01-16 15:30:00'],
        ];
    }
} else {
    // Fallback vers les données mock si la BDD n'est pas disponible
    $orders = [
        ['id' => 'cmd-001', 'client_name' => 'Fatou Diallo', 'client_phone' => '+221 77 123 45 67', 'client_email' => 'fatou@email.com', 'product_name' => 'Jus de Bissap', 'quantity' => 2, 'weight' => '500g', 'total_price' => 6000, 'status' => 'Livrée', 'notes' => '', 'created_at' => '2024-01-15 10:30:00'],
        ['id' => 'cmd-002', 'client_name' => 'Amadou Sow', 'client_phone' => '+221 76 234 56 78', 'client_email' => null, 'product_name' => 'Confiture Mangue', 'quantity' => 3, 'weight' => '250g', 'total_price' => 7500, 'status' => 'En cours', 'notes' => 'Livraison express', 'created_at' => '2024-01-15 14:20:00'],
        ['id' => 'cmd-003', 'client_name' => 'Marie Ndiaye', 'client_phone' => '+221 70 345 67 89', 'client_email' => 'marie@email.com', 'product_name' => 'Sirop Gingembre', 'quantity' => 1, 'weight' => '500g', 'total_price' => 3000, 'status' => 'En attente', 'notes' => '', 'created_at' => '2024-01-16 09:15:00'],
        ['id' => 'cmd-004', 'client_name' => 'Ousmane Ba', 'client_phone' => '+221 78 456 78 90', 'client_email' => null, 'product_name' => 'Jus de Bouye', 'quantity' => 4, 'weight' => '500g', 'total_price' => 12000, 'status' => 'En préparation', 'notes' => '', 'created_at' => '2024-01-16 11:45:00'],
        ['id' => 'cmd-005', 'client_name' => 'Aïda Fall', 'client_phone' => '+221 76 567 89 01', 'client_email' => 'aida@email.com', 'product_name' => 'Poudre Moringa', 'quantity' => 2, 'weight' => '250g', 'total_price' => 5000, 'status' => 'En livraison', 'notes' => '', 'created_at' => '2024-01-16 15:30:00'],
    ];
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

$filteredOrders = array_filter($orders, function($o) use ($search, $statusFilter) {
    $matchSearch = empty($search) || 
        stripos($o['client_name'], $search) !== false ||
        stripos($o['product_name'], $search) !== false ||
        stripos($o['client_phone'], $search) !== false;
    $matchStatus = $statusFilter === 'all' || $o['status'] === $statusFilter;
    return $matchSearch && $matchStatus;
});

$stats = [
    'total' => count($orders),
    'enAttente' => count(array_filter($orders, fn($o) => $o['status'] === 'En attente')),
    'enCours' => count(array_filter($orders, fn($o) => in_array($o['status'], ['Confirmée', 'En préparation', 'En livraison']))),
    'livrees' => count(array_filter($orders, fn($o) => $o['status'] === 'Livrée')),
];

function getStatusColor($status) {
    switch ($status) {
        case 'Livrée': return 'bg-green-100 text-green-700 border-green-200';
        case 'Confirmée': return 'bg-blue-100 text-blue-700 border-blue-200';
        case 'En préparation': return 'bg-yellow-100 text-yellow-700 border-yellow-200';
        case 'En livraison': return 'bg-indigo-100 text-indigo-700 border-indigo-200';
        case 'Annulée': return 'bg-red-100 text-red-700 border-red-200';
        default: return 'bg-gray-100 text-gray-700 border-gray-200';
    }
}

function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}

// Informations pour le header
$adminEmail = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'admin@sokhnamai.sn';
$adminName = explode('@', $adminEmail)[0];
$hour = date('H');
if ($hour < 12) $greeting = 'Bonjour';
elseif ($hour < 18) $greeting = 'Bon après-midi';
else $greeting = 'Bonsoir';
?>

<style>
/* Header admin style from dashboard */
.admin-header {
    position: fixed;
    top: 0;
    left: 16rem;
    right: 0;
    z-index: 50;
    background: white;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(8px);
    transition: left 0.3s ease;
}

.sidebar-collapsed .admin-header {
    left: 5rem;
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
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    z-index: 100;
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

/* Adaptation mobile */
@media (max-width: 768px) {
    .admin-header {
        left: 0;
        padding: 1rem;
    }
}
</style>

<!-- Header fixe avec dropdown utilisateur -->
<div class="admin-header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-4">
        <div>
            <h1 class="text-3xl font-heading font-bold text-gray-900"><?php echo $greeting; ?>, <?php echo ucfirst($adminName); ?> 👋</h1>
            <p class="text-gray-500 mt-1">Gestion des commandes de votre boutique.</p>
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
<div class="dashboard-content" style="padding-top: 120px;">
    <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-heading font-bold text-gray-900">Gestion des Commandes</h1>
            <p class="text-gray-500 mt-1">Gérez vos commandes et contactez vos clients</p>
        </div>
        <div class="flex gap-2">
            <a href="https://wa.me/221774460474" target="_blank" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                <i class="fas fa-phone mr-2"></i> WhatsApp Admin
            </a>
            <button onclick="openModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-plus mr-2"></i> Nouvelle commande
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php 
        $statItems = [
            ['label' => 'Total', 'value' => $stats['total'], 'color' => 'text-gray-600'],
            ['label' => 'En attente', 'value' => $stats['enAttente'], 'color' => 'text-yellow-600'],
            ['label' => 'En cours', 'value' => $stats['enCours'], 'color' => 'text-blue-600'],
            ['label' => 'Livrées', 'value' => $stats['livrees'], 'color' => 'text-green-600'],
        ];
        foreach ($statItems as $s): 
        ?>
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="p-4 text-center">
                    <p class="text-sm text-gray-500"><?php echo $s['label']; ?></p>
                    <p class="text-2xl font-bold mt-1 <?php echo $s['color']; ?>"><?php echo $s['value']; ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <form method="GET" class="w-full">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="action" value="commandes">
                <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Rechercher par client, produit ou téléphone..."
                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </form>
        </div>
        <form method="GET" class="w-full sm:w-48">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="action" value="commandes">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <select name="status" onchange="this.form.submit()" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="all">Tous les statuts</option>
                <?php foreach ($statusOptions as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $statusFilter === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-4 border-b">
            <h3 class="text-lg font-heading flex items-center gap-2">
                <i class="fas fa-shopping-cart text-primary-600"></i>
                Commandes (<?php echo count($filteredOrders); ?>)
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Client</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium hidden md:table-cell">Produit</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Montant</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Statut</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium hidden lg:table-cell">Date</th>
                        <th class="text-right py-3 px-4 text-gray-600 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filteredOrders as $order): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <div>
                                    <p class="font-medium text-gray-900"><?php echo $order['client_name']; ?></p>
                                    <p class="text-xs text-gray-500"><?php echo $order['client_phone']; ?></p>
                                </div>
                            </td>
                            <td class="py-3 px-4 hidden md:table-cell">
                                <p class="text-sm"><?php echo $order['product_name']; ?></p>
                                <p class="text-xs text-gray-500"><?php echo $order['quantity']; ?>x <?php echo $order['weight']; ?></p>
                            </td>
                            <td class="py-3 px-4 font-medium"><?php echo formatPrice($order['total_price']); ?></td>
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border <?php echo getStatusColor($order['status']); ?>">
                                    <?php echo $order['status']; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 hidden lg:table-cell text-xs text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="viewOrderDetails(<?php echo $order['id']; ?>)" class="p-1 text-gray-400 hover:text-gray-600" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="p-1 text-primary-600 hover:text-primary-700" title="Générer reçu">
                                        <i class="fas fa-file-alt"></i>
                                    </button>
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $order['client_phone']); ?>" 
                                       target="_blank" class="p-1 text-green-600 hover:text-green-700" title="WhatsApp">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                    <button onclick="editOrderStatus(<?php echo $order['id']; ?>, '<?php echo $order['status']; ?>')" class="p-1 text-blue-600 hover:text-blue-700" title="Modifier le statut">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button onclick="deleteOrder(<?php echo $order['id']; ?>)" class="p-1 text-red-600 hover:text-red-700" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($filteredOrders)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Aucune commande trouvée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter une commande -->
<div id="orderModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-heading font-bold">Nouvelle commande</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom du client *</label>
                        <input type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Téléphone *</label>
                        <input type="tel" required placeholder="+221 7X XXX XX XX" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Produit *</label>
                        <input type="text" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Quantité</label>
                        <input type="number" min="1" value="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Poids</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option>250g</option>
                            <option>500g</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Prix total</label>
                        <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Statut</label>
                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <?php foreach ($statusOptions as $s): ?>
                                <option value="<?php echo $s; ?>"><?php echo $s; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Notes</label>
                    <textarea rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
                <div class="flex gap-2 justify-end pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('orderModal').classList.remove('hidden');
    }
    function closeModal() {
        document.getElementById('orderModal').classList.add('hidden');
    }
    document.getElementById('orderModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

// Gestion du dropdown utilisateur
function toggleUserDropdown() {
    const dropdown = document.getElementById('user-dropdown-menu');
    const arrow = document.getElementById('dropdown-arrow');
    
    if (dropdown) {
        dropdown.classList.toggle('show');
        if (arrow) {
            arrow.style.transform = dropdown.classList.contains('show') ? 'rotate(180deg)' : '';
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
        if (arrow) arrow.style.transform = '';
    }
});

// Afficher la date actuelle
const dateElement = document.getElementById('current-date');
if (dateElement) {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    dateElement.textContent = now.toLocaleDateString('fr-FR', options);
}
</script>

</div><!-- Fermeture dashboard-content -->
</div><!-- Fermeture space-y-6 -->
