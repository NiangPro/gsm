<?php
require_once 'includes/config.php';

$pdo = getDB();
if ($pdo) {
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM products');
    $result = $stmt->fetch();
    echo 'Nombre de produits dans la BDD: ' . $result['count'] . PHP_EOL;
    
    if ($result['count'] > 0) {
        $stmt = $pdo->query('SELECT id, name FROM products LIMIT 5');
        $products = $stmt->fetchAll();
        echo 'Produits trouvés:' . PHP_EOL;
        foreach ($products as $p) {
            echo '- ID: ' . $p['id'] . ', Nom: ' . $p['name'] . PHP_EOL;
        }
    }
} else {
    echo 'Base de données non disponible' . PHP_EOL;
}
?>
