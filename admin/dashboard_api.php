<?php
// API endpoint pour le rafraîchissement automatique du dashboard
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/config.php';

$pdo = getDB();
$response = [
    'newOrders' => 0,
    'newMessages' => 0,
    'stats' => [],
    'timestamp' => time()
];

if ($pdo) {
    try {
        // Vérifier les nouvelles commandes depuis la dernière vérification
        $lastCheck = isset($_GET['last_check']) ? (int)$_GET['last_check'] : 0;
        $lastCheckTime = $lastCheck > 0 ? date('Y-m-d H:i:s', $lastCheck) : date('Y-m-d H:i:s', strtotime('-30 seconds'));
        
        // Compter les nouvelles commandes
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE created_at > ? AND status = 'En attente'");
        $stmt->execute([$lastCheckTime]);
        $response['newOrders'] = (int)$stmt->fetch()['count'];
        
        // Compter les nouveaux messages non lus
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = FALSE AND created_at > ?");
        $stmt->execute([$lastCheckTime]);
        $response['newMessages'] = (int)$stmt->fetch()['count'];
        
        // Statistiques actuelles
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE status = 'En attente'");
        $stmt->execute();
        $pendingOrders = (int)$stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders");
        $stmt->execute();
        $totalOrders = (int)$stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products");
        $stmt->execute();
        $totalProducts = (int)$stmt->fetch()['count'];
        
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = FALSE");
        $stmt->execute();
        $unreadMessages = (int)$stmt->fetch()['count'];
        
        $response['stats'] = [
            'pendingOrders' => $pendingOrders,
            'totalOrders' => $totalOrders,
            'totalProducts' => $totalProducts,
            'unreadMessages' => $unreadMessages
        ];
        
        // Commandes récentes pour mise à jour
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
        $orders = $stmt->fetchAll();
        
        $response['recentOrders'] = array_map(function($order) {
            return [
                'id' => $order['order_number'],
                'client' => $order['client_name'] ?: 'Client',
                'produit' => $order['product_name'] ?: 'Produit',
                'montant' => number_format($order['total_amount'], 0, ',', ' ') . ' FCFA',
                'statut' => $order['status'],
                'date' => formatDate($order['created_at'])
            ];
        }, $orders);
        
    } catch (PDOException $e) {
        error_log("Dashboard API Error: " . $e->getMessage());
        $response['error'] = 'Database error';
    }
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

echo json_encode($response);
?>
