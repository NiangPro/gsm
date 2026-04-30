<?php
// Script pour ajouter la catégorie "Fruits Séchées"
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Ajout de la catégorie 'Fruits Séchées'</h2>";

try {
    $pdo = getDB();
    
    if ($pdo) {
        // Vérifier si la table categories existe
        $stmt = $pdo->query("SHOW TABLES LIKE 'categories'");
        $tableExists = $stmt->rowCount() > 0;
        
        if ($tableExists) {
            echo "<p style='color: green;'>La table categories existe</p>";
            
            // Vérifier si la catégorie "Fruits Séchées" existe déjà
            $checkStmt = $pdo->prepare("SELECT COUNT(*) as count FROM categories WHERE name_fr = ?");
            $checkStmt->execute(['Fruits Séchées']);
            $exists = $checkStmt->fetch()['count'];
            
            if ($exists > 0) {
                echo "<p style='color: blue;'>La catégorie 'Fruits Séchées' existe déjà</p>";
                
                // Afficher les détails de la catégorie existante
                $catStmt = $pdo->prepare("SELECT * FROM categories WHERE name_fr = ?");
                $catStmt->execute(['Fruits Séchées']);
                $category = $catStmt->fetch();
                
                echo "<h3>Détails de la catégorie existante:</h3>";
                echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                echo "<tr><th>ID</th><th>Nom FR</th><th>Nom EN</th><th>Description</th><th>Disponible</th><th>Ordre</th></tr>";
                echo "<tr>";
                echo "<td>" . htmlspecialchars($category['id']) . "</td>";
                echo "<td>" . htmlspecialchars($category['name_fr']) . "</td>";
                echo "<td>" . htmlspecialchars($category['name_en'] ?: '-') . "</td>";
                echo "<td>" . htmlspecialchars(substr($category['description_fr'], 0, 50)) . "...</td>";
                echo "<td>" . ($category['is_available'] ? 'Oui' : 'Non') . "</td>";
                echo "<td>" . htmlspecialchars($category['display_order']) . "</td>";
                echo "</tr>";
                echo "</table>";
                
            } else {
                echo "<p style='color: orange;'>La catégorie 'Fruits Séchées' n'existe pas, ajout en cours...</p>";
                
                // Ajouter la catégorie
                $insertStmt = $pdo->prepare("
                    INSERT INTO categories (name_fr, name_en, description_fr, description_en, parent_id, is_available, display_order, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $result = $insertStmt->execute([
                    'Fruits Séchées',
                    'Dried Fruits',
                    'Différents types de fruits séchés de haute qualité',
                    'Various types of high quality dried fruits',
                    null, // Pas de catégorie parente
                    true,  // Disponible
                    7      // Ordre d'affichage
                ]);
                
                if ($result) {
                    $newId = $pdo->lastInsertId();
                    echo "<p style='color: green;'>Catégorie 'Fruits Séchées' ajoutée avec succès! (ID: $newId)</p>";
                    
                    // Afficher les détails de la nouvelle catégorie
                    $catStmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
                    $catStmt->execute([$newId]);
                    $category = $catStmt->fetch();
                    
                    echo "<h3>Nouvelle catégorie ajoutée:</h3>";
                    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
                    echo "<tr><th>ID</th><th>Nom FR</th><th>Nom EN</th><th>Description</th><th>Disponible</th><th>Ordre</th></tr>";
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($category['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($category['name_fr']) . "</td>";
                    echo "<td>" . htmlspecialchars($category['name_en']) . "</td>";
                    echo "<td>" . htmlspecialchars($category['description_fr']) . "</td>";
                    echo "<td>" . ($category['is_available'] ? 'Oui' : 'Non') . "</td>";
                    echo "<td>" . htmlspecialchars($category['display_order']) . "</td>";
                    echo "</tr>";
                    echo "</table>";
                    
                } else {
                    echo "<p style='color: red;'>Erreur lors de l'ajout de la catégorie</p>";
                }
            }
            
            // Afficher toutes les catégories disponibles
            echo "<h3>Toutes les catégories disponibles:</h3>";
            $allStmt = $pdo->query("SELECT * FROM categories ORDER BY display_order ASC, name_fr ASC");
            $categories = $allStmt->fetchAll();
            
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
            echo "<tr><th>ID</th><th>Nom FR</th><th>Nom EN</th><th>Description</th><th>Disponible</th><th>Ordre</th></tr>";
            
            foreach ($categories as $cat) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($cat['id']) . "</td>";
                echo "<td>" . htmlspecialchars($cat['name_fr']) . "</td>";
                echo "<td>" . htmlspecialchars($cat['name_en'] ?: '-') . "</td>";
                echo "<td>" . htmlspecialchars(substr($cat['description_fr'], 0, 30)) . "...</td>";
                echo "<td>" . ($cat['is_available'] ? 'Oui' : 'Non') . "</td>";
                echo "<td>" . htmlspecialchars($cat['display_order']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
            
            echo "<p style='color: green; font-weight: bold;'>Opération terminée!</p>";
            echo "<p><a href='?page=admin&action=produits'>Tester l'ajout de produit avec la nouvelle catégorie</a></p>";
            
        } else {
            echo "<p style='color: red;'>La table categories n'existe pas!</p>";
            echo "<p><a href='setup_categories_table.php'>Créer la table categories d'abord</a></p>";
        }
        
    } else {
        echo "<p style='color: red;'>Erreur de connexion à la base de données</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
