<?php
require_once __DIR__ . '/includes/config.php';

// Router
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Alias pour espace-admin -> admin
if ($page === 'espace-admin') {
    $page = 'admin';
}

// Admin routes
if ($page === 'admin') {
    $action = isset($_GET['action']) ? $_GET['action'] : 'login';

    if ($action === 'logout') {
        session_destroy();
        echo '<script>window.location.href = "?page=connexion";</script>';
        exit;
    }

    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Rediriger vers la page de connexion unique
        echo '<script>window.location.href = "?page=connexion";</script>';
        exit;
    }
    
    $adminPages = ['dashboard', 'produits', 'categories', 'commandes', 'equipe', 'partenaires', 'messages', 'parametres', 'edit_product'];
    if (!in_array($action, $adminPages)) {
        $action = 'dashboard';
    }
    
    include __DIR__ . '/admin/header.php';
    
    if (in_array($action, ['parametres'])) {
        include __DIR__ . '/admin/parametres.php';
    } else {
        include __DIR__ . '/admin/' . $action . '.php';
    }
    
    include __DIR__ . '/admin/footer.php';
    exit;
}

// Public routes
$validPages = ['home', 'a-propos', 'catalogue', 'blog', 'equipe', 'contact', 'partenaires', 'produit', 'panier', 'checkout', 'checkout-process', 'commande-confirmee', 'commande-details', 'inscription', 'connexion', 'espace-client', 'deconnexion'];

// Cas spécial pour la page produit
if ($page === 'produit') {
    $productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($productId <= 0) {
        header('Location: ?page=home');
        exit;
    }
}

if (!in_array($page, $validPages)) {
    $page = 'notfound';
}

include __DIR__ . '/includes/header.php';
include __DIR__ . '/pages/' . ($page === 'home' ? 'home' : $page) . '.php';
include __DIR__ . '/includes/footer.php';
