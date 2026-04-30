<?php
$pdo = getDB();
$message = '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Traitement des actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $nameEn = isset($_POST['name_en']) ? trim($_POST['name_en']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $descriptionEn = isset($_POST['description_en']) ? trim($_POST['description_en']) : '';
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
        $isAvailable = isset($_POST['is_available']) ? (bool)$_POST['is_available'] : true;
        $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        
        if ($name) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO categories (name_fr, name_en, description_fr, description_en, parent_id, is_available, display_order, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $nameEn, $description, $descriptionEn, $parentId, $isAvailable, $displayOrder]);
                $message = 'Catégorie ajoutée avec succès!';
            } catch (PDOException $e) {
                error_log("Error creating category: " . $e->getMessage());
                $message = 'Erreur lors de l\'ajout de la catégorie';
            }
        }
    } elseif ($action === 'edit') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $nameEn = isset($_POST['name_en']) ? trim($_POST['name_en']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $descriptionEn = isset($_POST['description_en']) ? trim($_POST['description_en']) : '';
        $parentId = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int)$_POST['parent_id'] : null;
        $isAvailable = isset($_POST['is_available']) ? (bool)$_POST['is_available'] : true;
        $displayOrder = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        
        if ($id && $name) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE categories 
                    SET name_fr = ?, name_en = ?, description_fr = ?, description_en = ?, 
                        parent_id = ?, is_available = ?, display_order = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $nameEn, $description, $descriptionEn, $parentId, $isAvailable, $displayOrder, $id]);
                $message = 'Catégorie mise à jour avec succès!';
            } catch (PDOException $e) {
                error_log("Error updating category: " . $e->getMessage());
                $message = 'Erreur lors de la mise à jour';
            }
        }
    } elseif ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id) {
            try {
                // Vérifier si des produits utilisent cette catégorie
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
                $stmt->execute([$id]);
                $productCount = $stmt->fetch()['count'];
                
                if ($productCount > 0) {
                    $message = 'Impossible de supprimer cette catégorie: elle contient des produits';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Catégorie supprimée avec succès!';
                }
            } catch (PDOException $e) {
                error_log("Error deleting category: " . $e->getMessage());
                $message = 'Erreur lors de la suppression';
            }
        }
    }
}

// Récupérer les catégories depuis la BDD
$categories = [];
if ($pdo) {
    try {
        $query = "SELECT c.*, p.name_fr as parent_name FROM categories c LEFT JOIN categories p ON c.parent_id = p.id";
        $params = [];
        
        if ($search) {
            $query .= " WHERE c.name_fr LIKE ? OR c.name_en LIKE ? OR c.description_fr LIKE ? OR c.description_en LIKE ?";
            $searchParam = '%' . $search . '%';
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        $query .= " ORDER BY c.display_order ASC, c.name_fr ASC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        // Données mock en cas d'erreur
        $categories = [
            ['id' => 1, 'name_fr' => 'Confitures', 'name_en' => 'Jams', 'description_fr' => 'Différentes confitures artisanales', 'description_en' => 'Various artisanal jams', 'parent_id' => null, 'is_available' => true, 'display_order' => 1, 'parent_name' => null],
            ['id' => 2, 'name_fr' => 'Jus', 'name_en' => 'Juices', 'description_fr' => 'Jus naturels et rafraîchissants', 'description_en' => 'Natural and refreshing juices', 'parent_id' => null, 'is_available' => true, 'display_order' => 2, 'parent_name' => null],
            ['id' => 3, 'name_fr' => 'Sirops', 'name_en' => 'Syrups', 'description_fr' => 'Sirops concentrés pour boissons', 'description_en' => 'Concentrated syrups for drinks', 'parent_id' => null, 'is_available' => true, 'display_order' => 3, 'parent_name' => null],
            ['id' => 4, 'name_fr' => 'Céréales', 'name_en' => 'Cereals', 'description_fr' => 'Produits céréaliers naturels', 'description_en' => 'Natural cereal products', 'parent_id' => null, 'is_available' => true, 'display_order' => 4, 'parent_name' => null],
        ];
    }
} else {
    // Données mock si BDD indisponible
    $categories = [
        ['id' => 1, 'name_fr' => 'Confitures', 'name_en' => 'Jams', 'description_fr' => 'Différentes confitures artisanales', 'description_en' => 'Various artisanal jams', 'parent_id' => null, 'is_available' => true, 'display_order' => 1, 'parent_name' => null],
        ['id' => 2, 'name_fr' => 'Jus', 'name_en' => 'Juices', 'description_fr' => 'Jus naturels et rafraîchissants', 'description_en' => 'Natural and refreshing juices', 'parent_id' => null, 'is_available' => true, 'display_order' => 2, 'parent_name' => null],
        ['id' => 3, 'name_fr' => 'Sirops', 'name_en' => 'Syrups', 'description_fr' => 'Sirops concentrés pour boissons', 'description_en' => 'Concentrated syrups for drinks', 'parent_id' => null, 'is_available' => true, 'display_order' => 3, 'parent_name' => null],
        ['id' => 4, 'name_fr' => 'Céréales', 'name_en' => 'Cereals', 'description_fr' => 'Produits céréaliers naturels', 'description_en' => 'Natural cereal products', 'parent_id' => null, 'is_available' => true, 'display_order' => 4, 'parent_name' => null],
    ];
}

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
            <p class="text-gray-500 mt-1">Gestion des catégories de votre boutique.</p>
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
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-tags text-primary-600 text-2xl"></i>
            <h1 class="text-2xl font-heading font-bold">Gestion des Catégories</h1>
        </div>
        <button onclick="openModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i> Ajouter une catégorie
        </button>
    </div>

    <?php if ($message): ?>
        <div class="bg-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-50 border border-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-200 text-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-700 px-4 py-3 rounded mb-4">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <form method="GET" class="w-full">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="action" value="categories">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Rechercher une catégorie..."
                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-4 border-b">
            <p class="text-gray-500"><?php echo count($categories); ?> catégorie(s)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Nom (FR)</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Nom (EN)</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Description</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Catégorie parente</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Ordre</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Disponibilité</th>
                        <th class="text-right py-3 px-4 text-gray-600 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categories as $cat): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($cat['name_fr']); ?></div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-sm text-gray-600"><?php echo htmlspecialchars($cat['name_en'] ?: '-'); ?></div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="text-sm text-gray-600 max-w-xs truncate" title="<?php echo htmlspecialchars($cat['description_fr']); ?>">
                                    <?php echo substr(htmlspecialchars($cat['description_fr']), 0, 50) . (strlen($cat['description_fr']) > 50 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm text-gray-600"><?php echo $cat['parent_name'] ?: '-'; ?></span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-sm text-gray-600"><?php echo $cat['display_order']; ?></span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $cat['is_available'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'; ?>">
                                    <?php echo $cat['is_available'] ? 'Disponible' : 'Indisponible'; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="editCategory(<?php echo $cat['id']; ?>)" class="p-1 text-blue-600 hover:text-blue-700" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deleteCategory(<?php echo $cat['id']; ?>)" class="p-1 text-red-600 hover:text-red-700" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categories)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-8 text-gray-500">Aucune catégorie trouvée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter/modifier une catégorie -->
<div id="categoryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-heading font-bold" id="modalTitle">Ajouter une catégorie</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="categoryForm" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="categoryId">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom (FR) *</label>
                        <input type="text" name="name" id="categoryName" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom (EN)</label>
                        <input type="text" name="name_en" id="categoryNameEn" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Description (FR)</label>
                        <textarea name="description" id="categoryDescription" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description (EN)</label>
                        <textarea name="description_en" id="categoryDescriptionEn" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Catégorie parente</label>
                        <select name="parent_id" id="categoryParent" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Aucune (catégorie racine)</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name_fr']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ordre d'affichage</label>
                        <input type="number" name="display_order" id="categoryOrder" min="0" value="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="flex items-center gap-2 pt-6">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_available" id="categoryAvailable" checked class="mr-2">
                            <span class="text-sm font-medium">Disponible</span>
                        </label>
                    </div>
                </div>
                <div class="flex gap-2 justify-end pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('modalTitle').textContent = 'Ajouter une catégorie';
    document.getElementById('formAction').value = 'add';
    document.getElementById('categoryId').value = '';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function editCategory(id) {
    // Récupérer les données de la catégorie
    const categories = <?php echo json_encode($categories); ?>;
    const category = categories.find(c => c.id == id);
    
    if (category) {
        document.getElementById('modalTitle').textContent = 'Modifier la catégorie';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('categoryId').value = id;
        document.getElementById('categoryName').value = category.name_fr;
        document.getElementById('categoryNameEn').value = category.name_en;
        document.getElementById('categoryDescription').value = category.description_fr;
        document.getElementById('categoryDescriptionEn').value = category.description_en;
        document.getElementById('categoryParent').value = category.parent_id;
        document.getElementById('categoryOrder').value = category.display_order;
        document.getElementById('categoryAvailable').checked = category.is_available;
        
        document.getElementById('categoryModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function deleteCategory(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" value="${id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
}

function closeModal() {
    document.getElementById('categoryModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('categoryModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Fermer avec la touche Echape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
    }
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
