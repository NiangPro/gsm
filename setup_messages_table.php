<?php
// Script pour créer la table contact_messages et insérer des données de test
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

echo "<h2>Configuration de la table contact_messages</h2>";

try {
    $pdo = getDB();
    
    if ($pdo) {
        // Création de la table
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS contact_messages (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50),
            subject VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            is_read BOOLEAN DEFAULT FALSE,
            reply_message TEXT,
            replied_by VARCHAR(255),
            replied_at TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_is_read (is_read),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTableSQL);
        echo "<p style='color: green;'>Table contact_messages créée avec succès</p>";
        
        // Vérifier si des données existent déjà
        $countStmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages");
        $count = $countStmt->fetch()['count'];
        
        if ($count == 0) {
            // Insertion des données de test
            $insertSQL = "
            INSERT INTO contact_messages (name, email, phone, subject, message, is_read, created_at) VALUES
            ('Fatou Diallo', 'fatou.diallo@email.com', '+221 77 123 45 67', 'Demande de partenariat', 'Bonjour, je suis intéressée par vos produits et souhaiterais établir un partenariat avec votre entreprise GIE Sokhna Maï. Pouvez-vous me contacter pour en discuter ?', FALSE, '2024-01-15 10:30:00'),
            ('Amadou Sow', 'amadou.sow@email.com', '+221 76 234 56 78', 'Information produit', 'Bonjour, je souhaite avoir plus d\'informations sur vos confitures artisanales. Quels sont les différents parfums disponibles et les tarifs ? Merci.', TRUE, '2024-01-14 14:20:00'),
            ('Marie Ndiaye', 'marie.ndiaye@email.com', '+221 70 345 67 89', 'Commande en gros', 'Bonjour, je gère une petite épicerie et je souhaiterais commander vos produits en gros. Quelles sont vos conditions pour les revendeurs ?', FALSE, '2024-01-13 09:15:00'),
            ('Ibrahim Ba', 'ibrahim.ba@email.com', '+221 78 456 78 90', 'Question sur les jus', 'Vos jus de fruits sont-ils bio ? Quels sont les ingrédients utilisés ? Je recherche des produits sans sucre ajouté.', TRUE, '2024-01-12 16:45:00'),
            ('Aïssa Touré', 'aissa.toure@email.com', '+221 77 567 89 01', 'Rendez-vous boutique', 'Bonjour, je souhaite passer à votre boutique cette semaine. Quels sont vos horaires d\'ouverture ? Et où êtes-vous exactement situés ?', FALSE, '2024-01-11 11:30:00'),
            ('Mamadou Cissé', 'mamadou.cisse@email.com', '+221 76 678 90 12', 'Feedback positif', 'Je voulais simplement vous dire que vos produits sont excellents ! J\'ai acheté votre confiture de mangose et c\'est délicieux. Continuez comme ça !', TRUE, '2024-01-10 08:20:00'),
            ('Khadija Fall', 'khadija.fall@email.com', '+221 70 789 01 23', 'Demande de catalogue', 'Bonjour, pourriez-vous m\'envoyer votre catalogue complet avec tous les produits et prix ? Je prépare une commande pour mon association.', FALSE, '2024-01-09 15:10:00'),
            ('Oumar Kane', 'oumar.kane@email.com', '+221 77 890 12 34', 'Problème livraison', 'Bonjour, j\'ai passé une commande il y a 3 jours et je ne l\'ai toujours pas reçue. Pouvez-vous vérifier le statut de ma commande numéro CMD-2024-001 ?', FALSE, '2024-01-08 13:25:00')";
            
            $pdo->exec($insertSQL);
            echo "<p style='color: green;'>8 messages de test insérés avec succès</p>";
        } else {
            echo "<p style='color: blue;'>La table contient déjà $count message(s)</p>";
        }
        
        // Afficher les données actuelles
        $messagesStmt = $pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
        $messages = $messagesStmt->fetchAll();
        
        echo "<h3>Messages actuels dans la base de données:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>";
        echo "<tr><th>ID</th><th>Nom</th><th>Email</th><th>Sujet</th><th>Lu?</th><th>Créé le</th></tr>";
        
        foreach ($messages as $message) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($message['id']) . "</td>";
            echo "<td>" . htmlspecialchars($message['name']) . "</td>";
            echo "<td>" . htmlspecialchars($message['email']) . "</td>";
            echo "<td>" . htmlspecialchars(substr($message['subject'], 0, 30)) . "...</td>";
            echo "<td>" . ($message['is_read'] ? 'Oui' : 'Non') . "</td>";
            echo "<td>" . htmlspecialchars($message['created_at']) . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Afficher les statistiques
        $unreadStmt = $pdo->query("SELECT COUNT(*) as unread FROM contact_messages WHERE is_read = FALSE");
        $unreadCount = $unreadStmt->fetch()['unread'];
        
        echo "<h3>Statistiques:</h3>";
        echo "<ul>";
        echo "<li>Total des messages: " . count($messages) . "</li>";
        echo "<li>Messages non lus: " . $unreadCount . "</li>";
        echo "<li>Messages lus: " . (count($messages) - $unreadCount) . "</li>";
        echo "</ul>";
        
        echo "<p style='color: green; font-weight: bold;'>Configuration terminée avec succès!</p>";
        echo "<p><a href='?page=admin&action=messages'>Accéder à la page de gestion des messages</a></p>";
        
    } else {
        echo "<p style='color: red;'>Erreur de connexion à la base de données</p>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Erreur: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
