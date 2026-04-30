<?php
// Script pour créer la table categories et insérer des données de test
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Configuration de la table categories</h2>";

try {
    $pdo = getDB();
    
    if ($pdo) {
        // Création de la table
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name_fr VARCHAR(255) NOT NULL,
            name_en VARCHAR(255) DEFAULT NULL,
            description_fr TEXT DEFAULT NULL,
            description_en TEXT DEFAULT NULL,
            parent_id INT DEFAULT NULL,
            is_available BOOLEAN DEFAULT TRUE,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_parent_id (parent_id),
            INDEX idx_is_available (is_available),
            INDEX idx_display_order (display_order),
            FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTableSQL);
        echo "<p style='color: green;'>Table categories créée avec succès</p>";
        
        // Vérifier si des données existent déjà
        $countStmt = $pdo->query("SELECT COUNT(*) as count FROM categories");
        $count = $countStmt->fetch()['count'];
        
        if ($count == 0) {
            // Insertion des données de test
            $insertSQL = "
            INSERT INTO categories (name_fr, name_en, description_fr, description_en, parent_id, is_available, display_order, created_at) VALUES
            ('Confitures', 'Jams', 'Différentes confitures artisanales faites maison', 'Various homemade artisanal jams', NULL, TRUE, 1, NOW()),
            ('Jus', 'Juices', 'Jus naturels et rafraîchissants sans conservateurs', 'Natural and refreshing juices without preservatives', NULL, TRUE, 2, NOW()),
            ('Sirops', 'Syrups', 'Sirops concentrés pour boissons et desserts', 'Concentrated syrups for drinks and desserts', NULL, TRUE, 3, NOW()),
            ('Céréales', 'Cereals', 'Produits céréaliers naturels et bio', 'Natural and organic cereal products', NULL, TRUE, 4, NOW()),
            ('Fruits Secs', 'Dried Fruits', 'Fruits séchés de haute qualité', 'High quality dried fruits', NULL, TRUE, 5, NOW()),
            ('Miel', 'Honey', 'Miel naturel et bio du Sénégal', 'Natural and organic honey from Senegal', NULL, TRUE, 6, NOW())";
            
            $pdo->exec($insertSQL);
            echo "<p style='color: green;'>6 catégories de test insérées avec succès</p>";
        } else {
            echo "<p style='color: blue;'>La table contient déjà $count catégorie(s)</p>";
        }
        
        // Afficher les données actuelles
        $categoriesStmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC, name_fr ASC");
        $categories = $categoriesStmt->fetchAll();
        
        echo "<h3>Catégories actuelles dans la base de données:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nom (FR)</th><th>Nom (EN)</th><th>Description</th><th>Disponible</th><th>Ordre</th></tr>";
        
        foreach ($categories as $category) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($category['id']) . "</td>";
            echo "<td>" . htmlspecialchars($category['name_fr']) . "</td>";
            echo "<td>" . htmlspecialchars($category['name_en'] ?: '-') . "</td>";
            echo "<td>" . htmlspecialchars(substr($category['description_fr'], 0, 50)) . "...</td>";
            echo "<td>" . ($category['is_available'] ? 'Oui' : 'Non') . "</td>";
            echo "<td>" . htmlspecialchars($category['display_order']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Afficher les statistiques
        $availableStmt = $pdo->query("SELECT COUNT(*) as available FROM categories WHERE is_available = TRUE");
        $availableCount = $availableStmt->fetch()['available'];
        
        echo "<h3>Statistiques:</h3>";
        echo "<ul>";
        echo "<li>Total des catégories: " . count($categories) . "</li>";
        echo "<li>Catégories disponibles: " . $availableCount . "</li>";
        echo "<li>Catégories indisponibles: " . (count($categories) - $availableCount) . "</li>";
        echo "</ul>";
        
        echo "<p style='color: green; font-weight: bold;'>Configuration terminée avec succès!</p>";
        echo "<p><a href='?page=admin&action=categories'>Accéder à la page de gestion des catégories</a></p>";
        
    } else {
        echo "<p style='color: red;'>Erreur de connexion à la base de données</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
