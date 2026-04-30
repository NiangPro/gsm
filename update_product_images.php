<?php
// Script pour mettre à jour les images des produits avec les nouvelles photos uploadées
require_once 'includes/config.php';

$pdo = getDB();

if ($pdo) {
    try {
        // Association des nouvelles images avec les produits correspondants
        $productImages = [
            // Confitures
            'Confiture de Baobab' => 'confiture-baobab.jpg',
            'Confiture de Citron' => 'confiture-citron.jpg', 
            'Confiture de Gingembre' => 'confiture-gingembre.jpg',
            'Confiture de Mangue' => 'confiture-mangue.jpg',
            'Confiture de Papaye' => 'confiture-papaye.jpg',
            'Confiture de Pamplemousse' => 'confiture-pomplemousse.jpg',
            'Confiture de Tamarin' => 'confiture-tamarin.jpg',
            
            // Jus
            'Jus de Bissap' => 'jus-bissap-JUS_BI02.jpg',
            'Jus de Gingembre' => 'jus-gingembre-JUS_GI02.jpg',
            'Jus de Tamarin Maria' => '[JUS-TAMA-MARI-1L] Jus de Tamarin Maria.png',
            'Jus de Bouye Lait Sans Sucre' => 'jus-bouye-lait-sans-sucre-djolof-15l.png',
            'Jus de Ditakh Sucre' => 'jus-ditakh-sucre-djolof-15l.png',
        ];

        foreach ($productImages as $productName => $imageName) {
            // Mettre à jour l'image du produit
            $stmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE name LIKE ?");
            $result = $stmt->execute([$imageName, '%' . $productName . '%']);
            
            if ($result) {
                $rowCount = $stmt->rowCount();
                echo "Produit '$productName' mis à jour avec l'image '$imageName' ($rowCount ligne(s) affectée(s))<br>";
            } else {
                echo "Erreur lors de la mise à jour du produit '$productName'<br>";
            }
        }

        echo "<br><strong>Mise à jour terminée !</strong><br>";

        // Vérification des produits mis à jour
        echo "<br><strong>Vérification des produits mis à jour :</strong><br>";
        $stmt = $pdo->prepare("SELECT name, image_url FROM products WHERE image_url IS NOT NULL AND image_url != 'default-product.jpg' ORDER BY name");
        $stmt->execute();
        $products = $stmt->fetchAll();

        foreach ($products as $product) {
            echo "- {$product['name']} : {$product['image_url']}<br>";
        }

    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
} else {
    echo "Erreur de connexion à la base de données";
}
?>
