<?php
// Fichier de traitement des actions pour l'équipe - exécuté avant tout output HTML
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_member') {
        $name = trim($_POST['name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($name && $role) {
            $pdo = getDB();
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("INSERT INTO team_members (name, role, description, created_at) VALUES (?, ?, ?, NOW())");
                    $stmt->execute([$name, $role, $description]);
                    $_SESSION['success'] = "Membre ajouté avec succès";
                } catch (PDOException $e) {
                    $_SESSION['error'] = "Erreur lors de l'ajout: " . $e->getMessage();
                }
            }
        }
        header("Location: ?page=admin&action=equipe");
        exit;
    }
    
    if ($action === 'edit_member') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $role = trim($_POST['role'] ?? '');
        $description = trim($_POST['description'] ?? '');
        
        if ($id && $name && $role) {
            $pdo = getDB();
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("UPDATE team_members SET name = ?, role = ?, description = ? WHERE id = ?");
                    $stmt->execute([$name, $role, $description, $id]);
                    $_SESSION['success'] = "Membre modifié avec succès";
                } catch (PDOException $e) {
                    $_SESSION['error'] = "Erreur lors de la modification: " . $e->getMessage();
                }
            }
        }
        header("Location: ?page=admin&action=equipe");
        exit;
    }
    
    if ($action === 'delete_member') {
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id) {
            $pdo = getDB();
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
                    $stmt->execute([$id]);
                    $_SESSION['success'] = "Membre supprimé avec succès";
                } catch (PDOException $e) {
                    $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
                }
            }
        }
        header("Location: ?page=admin&action=equipe");
        exit;
    }
}

// Si on arrive ici sans action POST, rediriger vers la page équipe
header("Location: ?page=admin&action=equipe");
exit;
?>
