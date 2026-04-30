<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Récupération des membres depuis la base de données
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

// Informations pour le header
$adminEmail = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'admin@sokhnamai.sn';
$adminName = explode('@', $adminEmail)[0];
$hour = date('H');
if ($hour < 12) $greeting = 'Bonjour';
elseif ($hour < 18) $greeting = 'Bon après-midi';
else $greeting = 'Bonsoir';
?>

<style>
/* Header admin style from dashboard */
.admin-header {
    position: fixed;
    top: 0;
    left: 16rem;
    right: 0;
    z-index: 50;
    background: white;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(8px);
    transition: left 0.3s ease;
}

.sidebar-collapsed .admin-header {
    left: 5rem;
}

.user-dropdown {
    position: relative;
}

.user-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    z-index: 100;
}

.user-dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
}

.user-dropdown-item:last-child {
    border-bottom: none;
}

.user-dropdown-item:hover {
    background: #f9fafb;
    color: #059669;
}

/* Adaptation mobile */
@media (max-width: 768px) {
    .admin-header {
        left: 0;
        padding: 1rem;
    }
}
</style>

<!-- Header fixe avec dropdown utilisateur -->
<div class="admin-header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-4">
        <div>
            <h1 class="text-3xl font-heading font-bold text-gray-900"><?php echo $greeting; ?>, <?php echo ucfirst($adminName); ?> 👋</h1>
            <p class="text-gray-500 mt-1">Gestion de l'équipe de votre boutique.</p>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500" id="current-date"></span>
            
            <!-- Dropdown utilisateur -->
            <div class="user-dropdown">
                <button onclick="toggleUserDropdown()" class="flex items-center gap-2 hover:bg-gray-50 rounded-lg p-2 transition-colors">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white font-bold shadow-lg">
                        <?php echo strtoupper(substr($adminName, 0, 1)); ?>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" id="dropdown-arrow"></i>
                </button>
                
                <div class="user-dropdown-menu" id="user-dropdown-menu">
                    <a href="?page=admin&action=profile" class="user-dropdown-item">
                        <i class="fas fa-user text-gray-400 w-4"></i>
                        <span>Profil</span>
                    </a>
                    <a href="?page=admin&action=settings" class="user-dropdown-item">
                        <i class="fas fa-cog text-gray-400 w-4"></i>
                        <span>Paramètres</span>
                    </a>
                    <a href="?page=admin&action=logout" class="user-dropdown-item">
                        <i class="fas fa-sign-out-alt text-gray-400 w-4"></i>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contenu principal avec padding pour compenser le header fixe -->
<div class="dashboard-content" style="padding-top: 120px;">
    <div class="space-y-6">
    <!-- Messages -->
    <?php if ($success): ?>
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-users text-primary-600 text-2xl"></i>
            <h1 class="text-2xl font-heading font-bold">Gestion de l'Équipe</h1>
        </div>
        <button onclick="openModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i> Ajouter un membre
        </button>
    </div>

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
                                <form action="equipe_actions.php" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer <?php echo addslashes($member['name']); ?> de l\\'équipe ?')">
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
            <form id="memberForm" action="equipe_actions.php" method="POST" class="space-y-4">
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
        document.getElementById('memberForm').action.value = 'add_member';
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
        document.getElementById('memberForm').action.value = 'edit_member';
        document.getElementById('memberId').value = id;
        document.getElementById('memberName').value = name;
        document.getElementById('memberRole').value = role;
        document.getElementById('memberDescription').value = description || '';
        document.getElementById('submitBtn').textContent = 'Modifier';
    }
    
    document.getElementById('memberModal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

// Gestion du dropdown utilisateur
function toggleUserDropdown() {
    const dropdown = document.getElementById('user-dropdown-menu');
    const arrow = document.getElementById('dropdown-arrow');
    
    if (dropdown) {
        dropdown.classList.toggle('show');
        if (arrow) {
            arrow.style.transform = dropdown.classList.contains('show') ? 'rotate(180deg)' : '';
        }
    }
}

// Fermer le dropdown en cliquant à l'extérieur
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('user-dropdown-menu');
    const dropdownButton = e.target.closest('.user-dropdown button');
    
    if (!dropdownButton && dropdown && dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        const arrow = document.getElementById('dropdown-arrow');
        if (arrow) arrow.style.transform = '';
    }
});

// Afficher la date actuelle
const dateElement = document.getElementById('current-date');
if (dateElement) {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    dateElement.textContent = now.toLocaleDateString('fr-FR', options);
}
</script>

</div><!-- Fermeture dashboard-content -->
</div><!-- Fermeture space-y-6 -->
