<?php
// Script pour corriger la table orders
require_once __DIR__ . '/includes/config.php';

echo "<h1>Correction de la table orders</h1>";

try {
    $pdo = getDB();
    
    if ($pdo) {
        echo "<p>✅ Connexion à la base de données réussie</p>";
        
        // Vérifier la structure actuelle de la table orders
        echo "<h2>Structure actuelle de la table orders:</h2>";
        $stmt = $pdo->query("DESCRIBE orders");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        
        $hasShippingCost = false;
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "</tr>";
            
            if ($column['Field'] === 'shipping_cost') {
                $hasShippingCost = true;
            }
        }
        echo "</table>";
        
        if (!$hasShippingCost) {
            echo "<h2>Ajout de la colonne shipping_cost:</h2>";
            
            // Ajouter la colonne shipping_cost
            $sql = "ALTER TABLE orders ADD COLUMN shipping_cost DECIMAL(10,2) DEFAULT 0 AFTER total_amount";
            $pdo->exec($sql);
            echo "<p>✅ Colonne shipping_cost ajoutée avec succès</p>";
        } else {
            echo "<p>✅ La colonne shipping_cost existe déjà</p>";
        }
        
        // Vérifier la colonne final_amount
        $stmt = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'orders' AND COLUMN_NAME = 'final_amount'");
        $hasFinalAmount = $stmt->fetch();
        
        if (!$hasFinalAmount) {
            echo "<h2>Ajout de la colonne final_amount:</h2>";
            
            // Ajouter la colonne final_amount
            $sql = "ALTER TABLE orders ADD COLUMN final_amount DECIMAL(12,2) NOT NULL AFTER shipping_cost";
            $pdo->exec($sql);
            echo "<p>✅ Colonne final_amount ajoutée avec succès</p>";
        } else {
            echo "<p>✅ La colonne final_amount existe déjà</p>";
        }
        
        // Vérifier aussi delivery_notes
        $stmt = $pdo->query("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = 'orders' AND COLUMN_NAME = 'delivery_notes'");
        $hasDeliveryNotes = $stmt->fetch();
        
        if (!$hasDeliveryNotes) {
            echo "<h2>Ajout de la colonne delivery_notes:</h2>";
            $sql = "ALTER TABLE orders ADD COLUMN delivery_notes TEXT AFTER delivery_address";
            $pdo->exec($sql);
            echo "<p>✅ Colonne delivery_notes ajoutée avec succès</p>";
        } else {
            echo "<p>✅ La colonne delivery_notes existe déjà</p>";
        }
        
        // Afficher la structure finale
        echo "<h2>Structure finale de la table orders:</h2>";
        $stmt = $pdo->query("DESCRIBE orders");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
        echo "<tr><th>Colonne</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<p style='color: green; font-weight: bold;'>Correction terminée!</p>";
        echo "<p><a href='?page=panier'>Tester le processus de commande</a></p>";
        
    } else {
        echo "<p style='color: red;'>Erreur de connexion à la base de données</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
