<?php
// Page complète de gestion de l'équipe GIE Sokhna Maï
// Gère l'affichage des membres et les opérations CRUD
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        $pdo = getDB();
        
        if (!$pdo) {
            throw new Exception("Erreur de connexion à la base de données");
        }
        
        switch ($action) {
            case 'add_member':
                $name = trim($_POST['name'] ?? '');
                $role = trim($_POST['role'] ?? '');
                $description = trim($_POST['description'] ?? '');
                
                if (empty($name)) throw new Exception("Le nom du membre est obligatoire");
                if (empty($role)) throw new Exception("Le rôle du membre est obligatoire");
                
                $stmt = $pdo->prepare("INSERT INTO team_members (name, role, description, created_at) VALUES (?, ?, ?, NOW())");
                $result = $stmt->execute([$name, $role, $description]);
                
                if ($result) {
                    $_SESSION['success'] = "Membre <strong>" . htmlspecialchars($name) . "</strong> ajouté avec succès";
                } else {
                    throw new Exception("Erreur lors de l'ajout du membre");
                }
                break;
                
            case 'edit_member':
                $id = (int)($_POST['id'] ?? 0);
                $name = trim($_POST['name'] ?? '');
                $role = trim($_POST['role'] ?? '');
                $description = trim($_POST['description'] ?? '');
                
                if ($id <= 0) throw new Exception("ID du membre invalide");
                if (empty($name)) throw new Exception("Le nom du membre est obligatoire");
                if (empty($role)) throw new Exception("Le rôle du membre est obligatoire");
                
                $checkStmt = $pdo->prepare("SELECT id FROM team_members WHERE id = ?");
                $checkStmt->execute([$id]);
                if (!$checkStmt->fetch()) throw new Exception("Membre non trouvé");
                
                $stmt = $pdo->prepare("UPDATE team_members SET name = ?, role = ?, description = ?, updated_at = NOW() WHERE id = ?");
                $result = $stmt->execute([$name, $role, $description, $id]);
                
                if ($result) {
                    $_SESSION['success'] = "Membre <strong>" . htmlspecialchars($name) . "</strong> modifié avec succès";
                } else {
                    throw new Exception("Erreur lors de la modification du membre");
                }
                break;
                
            case 'delete_member':
                $id = (int)($_POST['id'] ?? 0);
                
                if ($id <= 0) throw new Exception("ID du membre invalide");
                
                $nameStmt = $pdo->prepare("SELECT name FROM team_members WHERE id = ?");
                $nameStmt->execute([$id]);
                $member = $nameStmt->fetch();
                
                if (!$member) throw new Exception("Membre non trouvé");
                
                $stmt = $pdo->prepare("DELETE FROM team_members WHERE id = ?");
                $result = $stmt->execute([$id]);
                
                if ($result) {
                    $_SESSION['success'] = "Membre <strong>" . htmlspecialchars($member['name']) . "</strong> supprimé avec succès";
                } else {
                    throw new Exception("Erreur lors de la suppression du membre");
                }
                break;
                
            default:
                throw new Exception("Action non reconnue");
        }
        
        // Redirection après traitement POST
        header("Location: equipe_actions.php?page=admin&action=equipe");
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Erreur: " . $e->getMessage();
        error_log("Team member action error: " . $e->getMessage());
        header("Location: equipe_actions.php?page=admin&action=equipe");
        exit;
    }
}

// Récupération des membres pour l'affichage
$members = [];
$pdo = getDB();

if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM team_members ORDER BY created_at DESC");
        $stmt->execute();
        $members = $stmt->fetchAll();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de la récupération des membres: " . $e->getMessage();
    }
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filteredMembers = $members;

if ($search) {
    $filteredMembers = array_filter($members, function($m) use ($search) {
        return stripos($m['name'], $search) !== false || stripos($m['role'], $search) !== false || stripos($m['description'], $search) !== false;
    });
}

// Affichage des messages
$success = $_SESSION['success'] ?? '';
$error = $_SESSION['error'] ?? '';
unset($_SESSION['success'], $_SESSION['error']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de l'Équipe - GIE Sokhna Maï</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <a href="index.php" class="text-xl font-bold text-primary-600">GIE Sokhna Maï</a>
                    <span class="text-gray-400">|</span>
                    <h1 class="text-lg font-semibold">Gestion de l'Équipe</h1>
                </div>
                <a href="index.php?page=admin" class="text-gray-600 hover:text-gray-900">
                    <i class="fas fa-arrow-left mr-2"></i>Retour admin
                </a>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Messages -->
        <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 animate-fade-in">
                <?php echo $success; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 animate-fade-in">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="space-y-6">
            <!-- En-tête avec bouton d'ajout -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <i class="fas fa-users text-primary-600 text-2xl"></i>
                    <h1 class="text-2xl font-heading font-bold">Gestion de l'Équipe</h1>
                </div>
                <button onclick="openModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i> Ajouter un membre
                </button>
            </div>

            <!-- Barre de recherche -->
            <div class="flex items-center gap-3 max-w-md">
                <div class="relative flex-1">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <form method="GET" class="w-full">
                        <input type="hidden" name="page" value="admin">
                        <input type="hidden" name="action" value="equipe">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Rechercher un membre..."
                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </form>
                </div>
            </div>

            <!-- Tableau des membres -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
                <div class="p-4 border-b">
                    <p class="text-gray-500"><?php echo count($filteredMembers); ?> membre(s) dans l'équipe</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 bg-gray-50">
                                <th class="text-left py-3 px-4 text-gray-600 font-medium">Nom</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-medium">Poste</th>
                                <th class="text-left py-3 px-4 text-gray-600 font-medium">Description</th>
                                <th class="text-right py-3 px-4 text-gray-600 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredMembers as $member): ?>
                                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                    <td class="py-3 px-4 font-medium">
                                        <div class="flex items-center gap-2">
                                            <div class="h-8 w-8 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-sm">
                                                <?php echo substr($member['name'], 0, 1); ?>
                                            </div>
                                            <?php echo htmlspecialchars($member['name']); ?>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($member['role'] ?? 'Non défini'); ?></td>
                                    <td class="py-3 px-4 text-gray-500"><?php echo $member['description'] ?? '---'; ?></td>
                                    <td class="py-3 px-4 text-right space-x-2">
                                        <button onclick="editMember(<?php echo $member['id']; ?>, '<?php echo addslashes($member['name']); ?>', '<?php echo addslashes($member['role'] ?? ''); ?>', '<?php echo addslashes($member['description'] ?? ''); ?>')" 
                                                class="inline-flex items-center px-2 py-1 border border-gray-300 rounded hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-pencil-alt"></i>
                                        </button>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Supprimer <?php echo addslashes($member['name']); ?> de l\\'équipe ?')">
                                            <input type="hidden" name="action" value="delete_member">
                                            <input type="hidden" name="id" value="<?php echo $member['id']; ?>">
                                            <button type="submit" class="inline-flex items-center px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($filteredMembers)): ?>
                                <tr>
                                    <td colspan="4" class="text-center py-8 text-gray-500">Aucun membre trouvé</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal pour ajouter/modifier un membre -->
    <div id="memberModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 id="modalTitle" class="text-xl font-heading font-bold">Ajouter un membre</h2>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="add_member">
                    <input type="hidden" name="id" id="memberId" value="">
                    
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom complet *</label>
                        <input type="text" name="name" id="memberName" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" 
                               placeholder="Nom du membre">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Poste / Rôle *</label>
                        <input type="text" name="role" id="memberRole" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" 
                               placeholder="Ex: Présidente, Trésorière, Membre...">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description (optionnel)</label>
                        <textarea name="description" id="memberDescription" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" 
                                  placeholder="Description du membre..."></textarea>
                    </div>
                    <button type="submit" id="submitBtn" class="w-full inline-flex items-center justify-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                        Ajouter
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('memberModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Ajouter un membre';
            document.querySelector('form[name="action"]').value = 'add_member';
            document.getElementById('memberId').value = '';
            document.getElementById('memberName').value = '';
            document.getElementById('memberRole').value = '';
            document.getElementById('memberDescription').value = '';
            document.getElementById('submitBtn').textContent = 'Ajouter';
        }
        
        function closeModal() {
            document.getElementById('memberModal').classList.add('hidden');
        }
        
        function editMember(id, name, role, description) {
            document.getElementById('memberModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = 'Modifier un membre';
            document.querySelector('form[name="action"]').value = 'edit_member';
            document.getElementById('memberId').value = id;
            document.getElementById('memberName').value = name;
            document.getElementById('memberRole').value = role;
            document.getElementById('memberDescription').value = description || '';
            document.getElementById('submitBtn').textContent = 'Modifier';
        }
        
        document.getElementById('memberModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
