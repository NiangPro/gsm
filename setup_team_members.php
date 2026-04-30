<?php
// Script pour créer la table team_members et insérer les données initiales
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Configuration de la table team_members</h2>";

try {
    $pdo = getDB();
    
    if ($pdo) {
        // Création de la table
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS team_members (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            role VARCHAR(255) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTableSQL);
        echo "<p style='color: green;'>Table team_members créée avec succès</p>";
        
        // Vérifier si des données existent déjà
        $countStmt = $pdo->query("SELECT COUNT(*) as count FROM team_members");
        $count = $countStmt->fetch()['count'];
        
        if ($count == 0) {
            // Insertion des données initiales
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
        
        echo "<h3>Membres actuels dans la base de données:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Rôle</th><th>Description</th><th>Créé le</th></tr>";
        
        foreach ($members as $member) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($member['id']) . "</td>";
            echo "<td>" . htmlspecialchars($member['name']) . "</td>";
            echo "<td>" . htmlspecialchars($member['role']) . "</td>";
            echo "<td>" . htmlspecialchars($member['description'] ?? '---') . "</td>";
            echo "<td>" . htmlspecialchars($member['created_at']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<p style='color: green; font-weight: bold;'>Configuration terminée avec succès!</p>";
        echo "<p><a href='?page=admin&action=equipe'>Accéder à la page de gestion de l'équipe</a></p>";
        
    } else {
        echo "<p style='color: red;'>Erreur de connexion à la base de données</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
