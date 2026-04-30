<?php
// Script pour vérifier et corriger la structure de la table team_members
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Vérification de la structure de la table team_members</h2>";

try {
    $pdo = getDB();
    
    if ($pdo) {
        // Vérifier si la table existe
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'team_members'");
        $tableExists = $tableCheck->rowCount() > 0;
        
        if (!$tableExists) {
            echo "<p style='color: orange;'>La table team_members n'existe pas. Création en cours...</p>";
            
            // Créer la table avec la bonne structure
            $createTableSQL = "
            CREATE TABLE team_members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                role VARCHAR(255) NOT NULL,
                description TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            
            $pdo->exec($createTableSQL);
            echo "<p style='color: green;'>Table team_members créée avec succès</p>";
        } else {
            echo "<p style='color: blue;'>La table team_members existe. Vérification de la structure...</p>";
            
            // Afficher la structure actuelle
            $structure = $pdo->query("DESCRIBE team_members");
            echo "<h3>Structure actuelle:</h3>";
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>Champ</th><th>Type</th><th>Null</th><th>Clé</th></tr>";
            
            $columns = [];
            while ($row = $structure->fetch()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
                echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
                echo "</tr>";
                $columns[] = $row['Field'];
            }
            echo "</table>";
            
            // Vérifier les colonnes manquantes
            $requiredColumns = ['id', 'name', 'role', 'description', 'created_at', 'updated_at'];
            $missingColumns = array_diff($requiredColumns, $columns);
            
            if (!empty($missingColumns)) {
                echo "<p style='color: orange;'>Colonnes manquantes: " . implode(', ', $missingColumns) . "</p>";
                
                // Ajouter les colonnes manquantes
                foreach ($missingColumns as $column) {
                    if ($column === 'role') {
                        $pdo->exec("ALTER TABLE team_members ADD COLUMN role VARCHAR(255) NOT NULL DEFAULT 'Non défini'");
                        echo "<p style='color: green;'>Colonne 'role' ajoutée</p>";
                    } elseif ($column === 'description') {
                        $pdo->exec("ALTER TABLE team_members ADD COLUMN description TEXT");
                        echo "<p style='color: green;'>Colonne 'description' ajoutée</p>";
                    } elseif ($column === 'updated_at') {
                        $pdo->exec("ALTER TABLE team_members ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                        echo "<p style='color: green;'>Colonne 'updated_at' ajoutée</p>";
                    }
                }
            } else {
                echo "<p style='color: green;'>Toutes les colonnes requises sont présentes</p>";
            }
        }
        
        // Vérifier et insérer les données initiales si nécessaire
        $countStmt = $pdo->query("SELECT COUNT(*) as count FROM team_members");
        $count = $countStmt->fetch()['count'];
        
        if ($count == 0) {
            echo "<p style='color: orange;'>Aucune donnée trouvée. Insertion des données initiales...</p>";
            
            $insertSQL = "
            INSERT INTO team_members (name, role, description, created_at) VALUES
            ('Amy NIOME', 'Présidente', 'Leader visionnaire du GIE', '2024-01-15 10:00:00'),
            ('Angélique TOURE', 'Vice-Présidente', 'Bras droit de la présidente', '2024-01-15 10:00:00'),
            ('Anna NDIAYE', 'Trésorière', 'Gestion rigoureuse des finances', '2024-01-15 10:00:00'),
            ('Magou SAMB', 'Gérante de Boutique', 'Service client au quotidien', '2024-01-15 10:00:00')";
            
            $pdo->exec($insertSQL);
            echo "<p style='color: green;'>4 membres initiaux insérés avec succès</p>";
        } else {
            echo "<p style='color: blue;'>La table contient déjà $count membre(s)</p>";
        }
        
        // Afficher les données actuelles
        $membersStmt = $pdo->query("SELECT * FROM team_members ORDER BY created_at DESC");
        $members = $membersStmt->fetchAll();
        
        echo "<h3>Données actuelles:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Rôle</th><th>Description</th><th>Créé le</th></tr>";
        
        foreach ($members as $member) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($member['id']) . "</td>";
            echo "<td>" . htmlspecialchars($member['name']) . "</td>";
            echo "<td>" . htmlspecialchars($member['role'] ?? 'Non défini') . "</td>";
            echo "<td>" . htmlspecialchars($member['description'] ?? '---') . "</td>";
            echo "<td>" . htmlspecialchars($member['created_at']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<p style='color: green; font-weight: bold;'>Vérification terminée avec succès!</p>";
        echo "<p><a href='?page=admin&action=equipe'>Accéder à la page de gestion de l'équipe</a></p>";
        
    } else {
        echo "<p style='color: red;'>Erreur de connexion à la base de données</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
