<?php
session_start();

// Détection de la langue
$lang = isset($_GET['lang']) ? $_GET['lang'] : (isset($_SESSION['lang']) ? $_SESSION['lang'] : 'fr');
if (!in_array($lang, ['fr', 'en'])) {
    $lang = 'fr';
}
$_SESSION['lang'] = $lang;

// Chargement des traductions
$translations = include __DIR__ . '/../lang/' . $lang . '.php';

// Fonction de traduction
function __($key, $params = []) {
    global $translations;
    $keys = explode('.', $key);
    $value = $translations;
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $key;
        }
    }
    if (is_array($value)) {
        return $key;
    }
    foreach ($params as $param => $val) {
        $value = str_replace('{' . $param . '}', $val, $value);
    }
    return $value;
}

// URL helper
function url($path = '', $params = []) {
    $base = '';
    $query = '';
    if (!empty($params)) {
        $query = '?' . http_build_query($params);
    }
    return $base . $path . $query;
}

// Asset helper
function asset($path) {
    return 'assets/' . $path;
}

// Current page helper
function isActive($page) {
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 'home';
    return $currentPage === $page ? 'text-green-600 bg-green-50' : 'text-gray-600 hover:text-green-600 hover:bg-green-50';
}

// Current page check for admin
function isAdminActive($page) {
    $currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
    return $currentPage === $page ? 'bg-green-600 text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900';
}

// Admin auth check
function checkAdminAuth() {
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: ?page=admin');
        exit;
    }
}

// Configuration de la base de données
$database = [
    'host' => 'localhost',
    'name' => 'gie_sokhna_mai',
    'user' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'
];

// Connexion à la base de données
function getDB() {
    global $database;
    try {
        $dsn = "mysql:host={$database['host']};dbname={$database['name']};charset={$database['charset']}";
        $pdo = new PDO($dsn, $database['user'], $database['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // En production, logger l'erreur plutôt que de l'afficher
        error_log("Database connection failed: " . $e->getMessage());
        return null;
    }
}

// Theme colors (matching the original)
$theme = [
    'primary' => 'green',
    'primaryHex' => '#16a34a',
    'accent' => 'amber',
    'accentHex' => '#d97706',
];
