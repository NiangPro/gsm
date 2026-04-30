<?php
// Script de test pour le processus de commande
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h1>Test du processus de commande</h1>";

// 1. Test de connexion BDD
echo "<h2>1. Test de connexion BDD</h2>";
$pdo = getDB();
if ($pdo) {
    echo "✅ Connexion BDD réussie<br>";
} else {
    echo "❌ Connexion BDD échouée<br>";
    exit;
}

// 2. Test de création des tables
echo "<h2>2. Test de création des tables</h2>";
try {
    // Table customers
    $sql = "CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        phone VARCHAR(20) UNIQUE NOT NULL,
        address TEXT,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✅ Table customers créée/vérifiée<br>";
    
    // Table orders
    $sql = "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_number VARCHAR(50) UNIQUE NOT NULL,
        customer_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        shipping_cost DECIMAL(10,2) DEFAULT 0,
        final_amount DECIMAL(10,2) NOT NULL,
        delivery_address TEXT NOT NULL,
        delivery_notes TEXT,
        status ENUM('En attente', 'Confirmée', 'En préparation', 'En livraison', 'Livrée', 'Annulée') DEFAULT 'En attente',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✅ Table orders créée/vérifiée<br>";
    
    // Table order_items
    $sql = "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL,
        unit_price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        notes TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $pdo->exec($sql);
    echo "✅ Table order_items créée/vérifiée<br>";
    
} catch (Exception $e) {
    echo "❌ Erreur création tables: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Test d'insertion de données
echo "<h2>3. Test d'insertion de données</h2>";
try {
    $pdo->beginTransaction();
    
    // Test client
    $stmt = $pdo->prepare("INSERT INTO customers (name, phone, address, notes) VALUES (?, ?, ?, ?)");
    $result = $stmt->execute(['Test Client', '771234567', 'Adresse test', 'Notes test']);
    $customerId = $pdo->lastInsertId();
    echo "✅ Client test créé - ID: $customerId<br>";
    
    // Test commande
    $orderNumber = 'CMD-TEST-' . date('Y-m-d-H-i-s');
    $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, total_amount, shipping_cost, final_amount, delivery_address, delivery_notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'En attente')");
    $result = $stmt->execute([$orderNumber, $customerId, 10000, 1500, 11500, 'Adresse livraison test', 'Notes livraison test']);
    $orderId = $pdo->lastInsertId();
    echo "✅ Commande test créée - Numéro: $orderNumber, ID: $orderId<br>";
    
    // Test article
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([$orderId, 1, 'Produit test', 2, 5000, 10000, 'Notes article test']);
    echo "✅ Article test créé<br>";
    
    $pdo->commit();
    echo "✅ Transaction validée<br>";
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "❌ Erreur insertion: " . $e->getMessage() . "<br>";
    echo "Code erreur: " . $e->getCode() . "<br>";
}

// 4. Test de lecture des données
echo "<h2>4. Test de lecture des données</h2>";
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
    $result = $stmt->fetch();
    echo "✅ Nombre total de commandes: " . $result['total'] . "<br>";
    
    $stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 1");
    $order = $stmt->fetch();
    echo "✅ Dernière commande: " . $order['order_number'] . " - " . $order['final_amount'] . " FCFA<br>";
    
} catch (PDOException $e) {
    echo "❌ Erreur lecture: " . $e->getMessage() . "<br>";
}

echo "<h2>5. Test terminé</h2>";
echo "<a href='?page=panier'>Retour au panier</a>";
?>
