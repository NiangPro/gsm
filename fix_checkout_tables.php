<?php
// Script pour créer les tables nécessaires pour le système de commande
require_once __DIR__ . '/includes/config.php';

try {
    $pdo = getDB();
    if (!$pdo) {
        echo "Erreur: Impossible de se connecter à la base de données<br>";
        exit;
    }
    
    echo "Création des tables pour le système de commande...<br><br>";
    
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
    echo "✅ Table 'customers' créée ou existe déjà<br>";
    
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
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Table 'orders' créée ou existe déjà<br>";
    
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
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql);
    echo "✅ Table 'order_items' créée ou existe déjà<br>";
    
    // Vérifier si les tables existent et afficher leur structure
    echo "<br><strong>Vérification des tables:</strong><br>";
    
    $tables = ['customers', 'orders', 'order_items'];
    foreach ($tables as $table) {
        $stmt = $pdo->prepare("DESCRIBE $table");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<br>📋 Structure de la table '$table':<br>";
        foreach ($columns as $column) {
            echo "  - {$column['Field']} ({$column['Type']})<br>";
        }
    }
    
    // Tester l'insertion d'un client de test
    echo "<br><strong>Test d'insertion:</strong><br>";
    try {
        $stmt = $pdo->prepare("INSERT IGNORE INTO customers (name, phone, address) VALUES (?, ?, ?)");
        $stmt->execute(['Client Test', '+221770000000', 'Adresse test']);
        echo "✅ Insertion test client réussie<br>";
        
        // Tester l'insertion d'une commande de test
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
        $stmt->execute(['+221770000000']);
        $customer = $stmt->fetch();
        
        if ($customer) {
            $stmt = $pdo->prepare("INSERT IGNORE INTO orders (order_number, customer_id, total_amount, final_amount, delivery_address, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute(['TEST-2024-0001', $customer['id'], 1000, 1000, 'Adresse test', 'Test']);
            echo "✅ Insertion test commande réussie<br>";
        }
        
        // Nettoyer les données de test
        $pdo->exec("DELETE FROM orders WHERE order_number LIKE 'TEST-%'");
        $pdo->exec("DELETE FROM customers WHERE phone = '+221770000000'");
        echo "✅ Nettoyage des données de test<br>";
        
    } catch (Exception $e) {
        echo "❌ Erreur lors du test: " . $e->getMessage() . "<br>";
    }
    
    echo "<br><strong>✅ Toutes les tables sont prêtes pour le système de commande!</strong><br>";
    
} catch (PDOException $e) {
    echo "❌ Erreur PDO: " . $e->getMessage() . "<br>";
} catch (Exception $e) {
    echo "❌ Erreur générale: " . $e->getMessage() . "<br>";
}
?>
