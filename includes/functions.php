<?php
// Fonctions utilitaires pour le projet GIE Sokhna Maï
// Note: la fonction getDB() est déjà définie dans config.php

// Fonction pour formater les prix
function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}

// Fonction pour obtenir l'URL d'une image
function getImageSrc($image) {
    if (empty($image)) {
        return asset('default-product.jpg');
    }
    
    // Si c'est une URL externe
    if (filter_var($image, FILTER_VALIDATE_URL)) {
        return $image;
    }
    
    // Si c'est une image locale
    return asset($image);
}

// Fonction pour obtenir une image par défaut selon le type de produit
function getDefaultImage($productName) {
    $productImages = [
        'Confiture' => 'confiture-default.jpg',
        'Jus' => 'jus-default.jpg',
        'Sirop' => 'sirop-default.jpg',
        'Poudre' => 'poudre-default.jpg',
        'Miel' => 'miel-default.jpg',
        'Thé' => 'the-default.jpg',
        'Pâte' => 'pate-default.jpg'
    ];
    
    foreach ($productImages as $type => $image) {
        if (stripos($productName, $type) !== false) {
            return $image;
        }
    }
    
    return 'product-default.jpg';
}

// Fonction pour nettoyer et sécuriser les entrées
function cleanInput($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Fonction pour vérifier si un utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Fonction pour rediriger
function redirect($url) {
    header("Location: $url");
    exit;
}

// Fonction pour afficher un message flash
function flashMessage($type, $message) {
    $_SESSION['flash'][$type] = $message;
}

// Fonction pour récupérer les messages flash
function getFlashMessages() {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

// Fonction pour générer un token CSRF
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Fonction pour vérifier un token CSRF
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Fonction pour logger les erreurs
function logError($message, $context = []) {
    $logEntry = date('Y-m-d H:i:s') . ' - ' . $message;
    if (!empty($context)) {
        $logEntry .= ' - Context: ' . json_encode($context);
    }
    error_log($logEntry);
}

// Fonction pour valider un email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fonction pour générer une chaîne aléatoire
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / 32))), 0, $length);
}

// Fonction pour limiter le texte
function limitText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

// Fonction pour formater une date
function formatDate($date, $format = 'd/m/Y') {
    return date($format, strtotime($date));
}

// Fonction pour calculer le temps écoulé
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'à l\'instant';
    } elseif ($diff < 3600) {
        return 'il y a ' . floor($diff / 60) . ' minutes';
    } elseif ($diff < 86400) {
        return 'il y a ' . floor($diff / 3600) . ' heures';
    } elseif ($diff < 2592000) {
        return 'il y a ' . floor($diff / 86400) . ' jours';
    } else {
        return formatDate($datetime);
    }
}
