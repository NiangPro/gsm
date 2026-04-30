<?php
// Script pour corriger la table order_items
require_once __DIR__ . '/includes/config.php';

try {
    $pdo = getDB();
    
    echo "<h2>Correction de la table order_items</h2>";
    
    // Vérifier si la table existe
    $stmt = $pdo->query("SHOW TABLES LIKE 'order_items'");
    $tableExists = $stmt->rowCount() > 0;
    
    if ($tableExists) {
        echo "<p>✅ Table order_items existe</p>";
        
        // Vérifier les colonnes actuelles
        $stmt = $pdo->query("DESCRIBE order_items");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<p>Colonnes actuelles: " . implode(', ', $columns) . "</p>";
        
        // Vérifier si la colonne product_name existe
        if (!in_array('product_name', $columns)) {
            echo "<p>❌ Colonne product_name manquante</p>";
            
            // Ajouter la colonne product_name
            $sql = "ALTER TABLE order_items ADD COLUMN product_name VARCHAR(255) NOT NULL AFTER product_id";
            $pdo->exec($sql);
            echo "<p>✅ Colonne product_name ajoutée</p>";
        } else {
            echo "<p>✅ Colonne product_name existe déjà</p>";
        }
        
        // Vérifier les autres colonnes nécessaires
        $requiredColumns = ['id', 'order_id', 'product_id', 'product_name', 'quantity', 'unit_price', 'subtotal', 'notes'];
        
        foreach ($requiredColumns as $col) {
            if (!in_array($col, $columns)) {
                echo "<p>⚠️ Colonne $col manquante - à ajouter manuellement si nécessaire</p>";
            }
        }
        
    } else {
        echo "<p>❌ Table order_items n'existe pas</p>";
        
        // Créer la table complète
        $sql = "CREATE TABLE order_items (
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
        echo "<p>✅ Table order_items créée avec toutes les colonnes</p>";
    }
    
    echo "<h3>✅ Correction terminée avec succès!</h3>";
    echo "<p><a href='?page=panier'>Retour au panier</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Erreur</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
