<?php
// Script de débogage pour le processus de commande
require_once __DIR__ . '/includes/config.php';

echo "<h1>Débogage du processus de commande</h1>";

echo "<h2>1. Informations de la requête</h2>";
echo "Méthode: " . $_SERVER['REQUEST_METHOD'] . "<br>";
echo "URL: " . $_SERVER['REQUEST_URI'] . "<br>";
echo "Referer: " . ($_SERVER['HTTP_REFERER'] ?? 'N/A') . "<br>";

echo "<h2>2. Données POST reçues</h2>";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<pre>" . htmlspecialchars(json_encode($_POST, JSON_PRETTY_PRINT)) . "</pre>";
} else {
    echo "Aucune donnée POST reçue<br>";
}

echo "<h2>3. Test de connexion BDD</h2>";
$pdo = getDB();
if ($pdo) {
    echo "✅ Connexion BDD réussie<br>";
    
    echo "<h2>4. Structure de la table orders</h2>";
    try {
        $stmt = $pdo->query("DESCRIBE orders");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table border='1' style='border-collapse: collapse;'>";
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
        
    } catch (PDOException $e) {
        echo "❌ Erreur: " . $e->getMessage() . "<br>";
    }
    
} else {
    echo "❌ Connexion BDD échouée<br>";
}

echo "<h2>5. Test d'insertion simple</h2>";
if ($pdo) {
    try {
        $orderNumber = 'TEST-' . date('Y-m-d-H-i-s');
        $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, total_amount, final_amount, delivery_address, status) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$orderNumber, 1, 5000, 6500, 'Test address', 'En attente']);
        
        if ($result) {
            echo "✅ Insertion test réussie - Numéro: $orderNumber<br>";
            
            // Supprimer le test
            $stmt = $pdo->prepare("DELETE FROM orders WHERE order_number = ?");
            $stmt->execute([$orderNumber]);
            echo "✅ Test nettoyé<br>";
        } else {
            echo "❌ Insertion test échouée<br>";
        }
        
    } catch (PDOException $e) {
        echo "❌ Erreur insertion: " . $e->getMessage() . "<br>";
    }
}

echo "<h2>6. Session actuelle</h2>";
session_start();
echo "Session ID: " . session_id() . "<br>";
echo "Données session: <pre>" . htmlspecialchars(json_encode($_SESSION, JSON_PRETTY_PRINT)) . "</pre>";

echo "<h2>7. Actions recommandées</h2>";
echo "<ul>";
echo "<li><a href='fix_orders_table.php'>Corriger la table orders</a></li>";
echo "<li><a href='test-checkout.php'>Tester le processus complet</a></li>";
echo "<li><a href='?page=panier'>Aller au panier</a></li>";
echo "</ul>";
?>
