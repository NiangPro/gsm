<?php
require_once 'includes/config.php';

$pdo = getDB();

if (!$pdo) {
    die("Base de données non disponible");
}

// Mise à jour des images des produits de jus avec les nouvelles photos uploadées
$juiceUpdates = [
    'Jus de Bissap' => '8J3ioeqfbmGXXJgm7L8DP7XG1u4sm2imab74deMx.jpg',
    'Sirop de Gingembre' => 'jus-gingembre-JUS_GI02.jpg'
];

try {
    foreach ($juiceUpdates as $productName => $newImage) {
        $stmt = $pdo->prepare("UPDATE products SET image_url = ? WHERE name = ?");
        $stmt->execute([$newImage, $productName]);
        echo "Image mise à jour pour: $productName -> $newImage<br>";
    }
    echo "<br><strong>Toutes les images de jus ont été mises à jour avec succès!</strong>";
} catch (PDOException $e) {
    echo "Erreur lors de la mise à jour des images: " . $e->getMessage();
}
?>
