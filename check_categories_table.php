<?php
// Script pour vérifier si la table categories existe
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Vérification de la table categories</h2>";

try {
    $pdo = getDB();
    
    if ($pdo) {
        // Vérifier si la table existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo "<p style='color: green;'>La table categories existe</p>";
            
            // Afficher la structure
            echo "<h3>Structure de la table:</h3>";
            $structureStmt = $pdo->query("DESCRIBE categories");
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            
            while ($row = $structureStmt->fetch()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            // Compter les enregistrements
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
            $count = $countStmt->fetch()['count'];
            echo "<p>Nombre d'enregistrements: " . $count . "</p>";
            
            // Afficher les données si elles existent
            if ($count > 0) {
                echo "<h3>Données actuelles:</h3>";
                $dataStmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC, name_fr ASC");
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr><th>ID</th><th>Nom FR</th><th>Nom EN</th><th>Description</th><th>Disponible</th><th>Ordre</th></tr>";
                
                while ($row = $dataStmt->fetch()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name_fr']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name_en'] ?: '-') . "</td>";
                    echo "<td>" . htmlspecialchars(substr($row['description_fr'], 0, 50)) . "...</td>";
                    echo "<td>" . ($row['is_available'] ?? false ? 'Oui' : 'Non') . "</td>";
                    echo "<td>" . htmlspecialchars($row['display_order']) . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            }
            
        } else {
            echo "<p style='color: red;'>La table categories n'existe pas!</p>";
            echo "<p><a href='setup_categories_table.php'>Créer la table categories</a></p>";
        }
        
    } else {
        echo "<p style='color: red;'>Erreur de connexion à la base de données</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
