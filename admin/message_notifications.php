<?php
// Script pour vérifier les nouveaux messages et générer des notifications
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = getDB();
    
    if ($pdo) {
        // Compter les messages non lus
        $unreadStmt = $pdo->query("SELECT COUNT(*) as count FROM contact_messages WHERE is_read = FALSE");
        $unreadCount = $unreadStmt->fetch()['count'];
        
        // Récupérer les messages récents (derniers 24h)
        $recentStmt = $pdo->query("
            SELECT name, subject, created_at 
            FROM contact_messages 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ORDER BY created_at DESC 
            LIMIT 5
        ");
        $recentMessages = $recentStmt->fetchAll();
        
        // Stocker les informations en session pour l'affichage
        $_SESSION['message_notifications'] = [
            'unread_count' => $unreadCount,
            'recent_messages' => $recentMessages,
            'last_check' => date('Y-m-d H:i:s')
        ];
        
        // Retourner les données au format JSON pour les requêtes AJAX
        if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'unread_count' => $unreadCount,
                'recent_messages' => $recentMessages
            ]);
            exit;
        }
    }
    
} catch (PDOException $e) {
    error_log("Erreur notification messages: " . $e->getMessage());
    
    if (isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
        exit;
    }
}
?>
