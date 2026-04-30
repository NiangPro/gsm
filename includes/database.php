<?php
/**
 * GIE Sokhna Maï - Configuration et connexion à la base de données
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'gie_sokhna_mai');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Établit une connexion PDO à la base de données
 * 
 * @return PDO
 * @throws PDOException
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
        } catch (PDOException $e) {
            error_log("Erreur de connexion à la base de données: " . $e->getMessage());
            throw new Exception("Impossible de se connecter à la base de données.");
        }
    }
    
    return $pdo;
}

/**
 * Exécute une requête SQL avec paramètres optionnels
 * 
 * @param string $sql Requête SQL
 * @param array $params Paramètres pour la requête préparée
 * @return PDOStatement
 */
function dbQuery($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

/**
 * Récupère une seule ligne
 * 
 * @param string $sql Requête SQL
 * @param array $params Paramètres
 * @return array|false
 */
function dbFetchOne($sql, $params = []) {
    return dbQuery($sql, $params)->fetch();
}

/**
 * Récupère toutes les lignes
 * 
 * @param string $sql Requête SQL
 * @param array $params Paramètres
 * @return array
 */
function dbFetchAll($sql, $params = []) {
    return dbQuery($sql, $params)->fetchAll();
}

/**
 * Insert un enregistrement et retourne l'ID généré
 * 
 * @param string $sql Requête SQL INSERT
 * @param array $params Paramètres
 * @return int ID de l'enregistrement créé
 */
function dbInsert($sql, $params = []) {
    $pdo = getDBConnection();
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $pdo->lastInsertId();
}

/**
 * Met à jour des enregistrements
 * 
 * @param string $sql Requête SQL UPDATE
 * @param array $params Paramètres
 * @return int Nombre de lignes affectées
 */
function dbUpdate($sql, $params = []) {
    return dbQuery($sql, $params)->rowCount();
}

/**
 * Supprime des enregistrements
 * 
 * @param string $sql Requête SQL DELETE
 * @param array $params Paramètres
 * @return int Nombre de lignes supprimées
 */
function dbDelete($sql, $params = []) {
    return dbQuery($sql, $params)->rowCount();
}

/**
 * Démarre une transaction
 */
function dbBeginTransaction() {
    getDBConnection()->beginTransaction();
}

/**
 * Valide une transaction
 */
function dbCommit() {
    getDBConnection()->commit();
}

/**
 * Annule une transaction
 */
function dbRollback() {
    getDBConnection()->rollBack();
}

/**
 * Échappe une chaîne pour les requêtes SQL (alternative aux requêtes préparées)
 * 
 * @param string $string
 * @return string
 */
function dbEscape($string) {
    return htmlspecialchars(strip_tags(trim($string)), ENT_QUOTES, 'UTF-8');
}
