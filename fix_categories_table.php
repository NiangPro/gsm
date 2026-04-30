<?php
// Script pour corriger la structure de la table categories
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Correction de la structure de la table categories</h2>";

try {
    $pdo = getDB();
    
    if ($pdo) {
        // Vérifier si la table existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo "<p style='color: green;'>La table categories existe</p>";
            
            // Afficher la structure actuelle
            echo "<h3>Structure actuelle:</h3>";
            $structureStmt = $pdo->query("DESCRIBE categories");
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            
            $existingColumns = [];
            while ($row = $structureStmt->fetch()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
                echo "</tr>";
                $existingColumns[] = $row['Field'];
            }
            echo "</table>";
            
            // Colonnes requises
            $requiredColumns = [
                'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
                'name_fr' => 'VARCHAR(255) NOT NULL',
                'name_en' => 'VARCHAR(255) DEFAULT NULL',
                'description_fr' => 'TEXT DEFAULT NULL',
                'description_en' => 'TEXT DEFAULT NULL',
                'parent_id' => 'INT DEFAULT NULL',
                'is_available' => 'BOOLEAN DEFAULT TRUE',
                'display_order' => 'INT DEFAULT 0',
                'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
                'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
            ];
            
            // D'abord supprimer les clés étrangères problématiques
            try {
                $pdo->exec("ALTER TABLE categories DROP FOREIGN KEY IF EXISTS fk_parent_category");
                echo "<p style='color: blue;'>Clé étrangère fk_parent_category supprimée si elle existait</p>";
            } catch (PDOException $e) {
                echo "<p style='color: orange;'>Aucune clé étrangère à supprimer: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
            
            // Ajouter les colonnes manquantes
            $missingColumns = array_diff(array_keys($requiredColumns), $existingColumns);
            
            if (!empty($missingColumns)) {
                echo "<h3>Ajout des colonnes manquantes:</h3>";
                
                foreach ($missingColumns as $column) {
                    $columnDef = $requiredColumns[$column];
                    
                    if ($column === 'parent_id') {
                        // Ajouter la colonne parent_id SANS clé étrangère pour l'instant
                        try {
                            $pdo->exec("ALTER TABLE categories ADD COLUMN parent_id INT DEFAULT NULL");
                            echo "<p style='color: green;'>Colonne 'parent_id' ajoutée</p>";
                            
                            // Ajouter l'index
                            $pdo->exec("ALTER TABLE categories ADD INDEX idx_parent_id (parent_id)");
                            echo "<p style='color: green;'>Index 'idx_parent_id' ajouté</p>";
                            
                            // La clé étrangère sera ajoutée plus tard après insertion de données
                            
                        } catch (PDOException $e) {
                            echo "<p style='color: orange;'>Erreur lors de l'ajout de parent_id: " . htmlspecialchars($e->getMessage()) . "</p>";
                        }
                    } elseif ($column === 'is_available') {
                        try {
                            $pdo->exec("ALTER TABLE categories ADD COLUMN is_available BOOLEAN DEFAULT TRUE");
                            echo "<p style='color: green;'>Colonne 'is_available' ajoutée</p>";
                            
                            $pdo->exec("ALTER TABLE categories ADD INDEX idx_is_available (is_available)");
                            echo "<p style='color: green;'>Index 'idx_is_available' ajouté</p>";
                            
                        } catch (PDOException $e) {
                            echo "<p style='color: orange;'>Erreur lors de l'ajout de is_available: " . htmlspecialchars($e->getMessage()) . "</p>";
                        }
                    } elseif ($column === 'display_order') {
                        try {
                            $pdo->exec("ALTER TABLE categories ADD COLUMN display_order INT DEFAULT 0");
                            echo "<p style='color: green;'>Colonne 'display_order' ajoutée</p>";
                            
                            $pdo->exec("ALTER TABLE categories ADD INDEX idx_display_order (display_order)");
                            echo "<p style='color: green;'>Index 'idx_display_order' ajouté</p>";
                            
                        } catch (PDOException $e) {
                            echo "<p style='color: orange;'>Erreur lors de l'ajout de display_order: " . htmlspecialchars($e->getMessage()) . "</p>";
                        }
                    } elseif ($column === 'updated_at') {
                        try {
                            $pdo->exec("ALTER TABLE categories ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                            echo "<p style='color: green;'>Colonne 'updated_at' ajoutée</p>";
                            
                        } catch (PDOException $e) {
                            echo "<p style='color: orange;'>Erreur lors de l'ajout de updated_at: " . htmlspecialchars($e->getMessage()) . "</p>";
                        }
                    } else {
                        echo "<p style='color: orange;'>Colonne '$column' non gérée automatiquement</p>";
                    }
                }
            } else {
                echo "<p style='color: blue;'>Toutes les colonnes requises existent déjà</p>";
            }
            
            // Afficher la structure finale
            echo "<h3>Structure finale:</h3>";
            $finalStructureStmt = $pdo->query("DESCRIBE categories");
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
            
            while ($row = $finalStructureStmt->fetch()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Default']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<p style='color: green; font-weight: bold;'>Correction terminée!</p>";
            echo "<p><a href='?page=admin&action=categories'>Tester l'ajout de catégorie</a></p>";
            
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
