<?php
// Script pour gérer les soumissions de formulaire de contact
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Traitement uniquement des requêtes POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

try {
    $pdo = getDB();
    
    if (!$pdo) {
        throw new Exception("Erreur de connexion à la base de données");
    }
    
    // Récupération et validation des données
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validation des champs obligatoires
    if (empty($name)) {
        throw new Exception("Le nom est obligatoire");
    }
    if (empty($email)) {
        throw new Exception("L'email est obligatoire");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("L'email n'est pas valide");
    }
    if (empty($subject)) {
        throw new Exception("Le sujet est obligatoire");
    }
    if (empty($message)) {
        throw new Exception("Le message est obligatoire");
    }
    
    // Insertion du message dans la base de données
    $stmt = $pdo->prepare("
        INSERT INTO contact_messages (name, email, phone, subject, message, is_read, created_at) 
        VALUES (?, ?, ?, ?, ?, FALSE, NOW())
    ");
    
    $result = $stmt->execute([$name, $email, $phone, $subject, $message]);
    
    if ($result) {
        // Message de succès
        $_SESSION['contact_success'] = "Votre message a été envoyé avec succès. Nous vous répondrons dans les plus brefs délais.";
        
        // Envoyer une notification email à l'admin (optionnel)
        $adminEmail = 'admin@giesokhnmai.com'; // À configurer selon vos besoins
        
        $headers = [
            'From: ' . $email,
            'Reply-To: ' . $email,
            'Content-Type: text/plain; charset=UTF-8',
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $emailSubject = "Nouveau message de contact: " . $subject;
        $emailBody = "Vous avez reçu un nouveau message de contact:\n\n";
        $emailBody .= "Nom: " . $name . "\n";
        $emailBody .= "Email: " . $email . "\n";
        $emailBody .= "Téléphone: " . ($phone ?: 'Non spécifié') . "\n";
        $emailBody .= "Sujet: " . $subject . "\n\n";
        $emailBody .= "Message:\n" . $message . "\n\n";
        $emailBody .= "Date: " . date('d/m/Y H:i') . "\n";
        
        // Tentative d'envoi d'email (silencieux en cas d'erreur)
        @mail($adminEmail, $emailSubject, $emailBody, implode("\r\n", $headers));
        
        // Redirection ou réponse JSON selon le contexte
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
            echo json_encode(['success' => true, 'message' => 'Message envoyé avec succès']);
        } else {
            $baseUrl = $_POST['redirect'] ?? 'index.php?page=contact';
            $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
            $redirectUrl = $baseUrl . $separator . "success=1";
            echo '<script>window.location.href = "' . $redirectUrl . '";</script>';
            echo '<div class="min-h-screen flex items-center justify-center bg-gray-50"><div class="text-center"><i class="fas fa-spinner fa-spin text-emerald-600 text-3xl mb-4"></i><p class="text-gray-600">Redirection...</p></div></div>';
            exit;
        }
        
    } else {
        throw new Exception("Erreur lors de l'envoi du message");
    }
    
} catch (Exception $e) {
    // Gestion des erreurs
    $_SESSION['contact_error'] = $e->getMessage();
    
    if (isset($_POST['ajax']) && $_POST['ajax'] === 'true') {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    } else {
        $baseUrl = $_POST['redirect'] ?? 'index.php?page=contact';
        $separator = strpos($baseUrl, '?') !== false ? '&' : '?';
        $redirectUrl = $baseUrl . $separator . "error=1";
        echo '<script>window.location.href = "' . $redirectUrl . '";</script>';
        echo '<div class="min-h-screen flex items-center justify-center bg-gray-50"><div class="text-center"><i class="fas fa-spinner fa-spin text-emerald-600 text-3xl mb-4"></i><p class="text-gray-600">Redirection...</p></div></div>';
        exit;
    }
}
?>
