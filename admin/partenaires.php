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
        $year = isset($_POST['year']) ? trim($_POST['year']) : '';
        $icon = isset($_POST['icon']) ? trim($_POST['icon']) : 'fa-handshake';
        $website = isset($_POST['website']) ? trim($_POST['website']) : '';
        $logo = isset($_POST['logo']) ? trim($_POST['logo']) : '';
        $is_active = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true;
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        
        if ($name && $description) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO partners (name_fr, name_en, description_fr, description_en, year, icon, website, logo, is_active, display_order, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$name, $nameEn, $description, $descriptionEn, $year, $icon, $website, $logo, $is_active, $display_order]);
                $message = 'Partenaire ajouté avec succès!';
            } catch (PDOException $e) {
                error_log("Error creating partner: " . $e->getMessage());
                $message = 'Erreur lors de l\'ajout du partenaire';
            }
        }
    } elseif ($action === 'edit') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $nameEn = isset($_POST['name_en']) ? trim($_POST['name_en']) : '';
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $descriptionEn = isset($_POST['description_en']) ? trim($_POST['description_en']) : '';
        $year = isset($_POST['year']) ? trim($_POST['year']) : '';
        $icon = isset($_POST['icon']) ? trim($_POST['icon']) : 'fa-handshake';
        $website = isset($_POST['website']) ? trim($_POST['website']) : '';
        $logo = isset($_POST['logo']) ? trim($_POST['logo']) : '';
        $is_active = isset($_POST['is_active']) ? (bool)$_POST['is_active'] : true;
        $display_order = isset($_POST['display_order']) ? (int)$_POST['display_order'] : 0;
        
        if ($id && $name && $description) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE partners 
                    SET name_fr = ?, name_en = ?, description_fr = ?, description_en = ?, 
                        year = ?, icon = ?, website = ?, logo = ?, is_active = ?, display_order = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $nameEn, $description, $descriptionEn, $year, $icon, $website, $logo, $is_active, $display_order, $id]);
                $message = 'Partenaire mis à jour avec succès!';
            } catch (PDOException $e) {
                error_log("Error updating partner: " . $e->getMessage());
                $message = 'Erreur lors de la mise à jour';
            }
        }
    } elseif ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM partners WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'Partenaire supprimé avec succès!';
            } catch (PDOException $e) {
                error_log("Error deleting partner: " . $e->getMessage());
                $message = 'Erreur lors de la suppression';
            }
        }
    }
}

// Récupérer les partenaires depuis la BDD
$partners = [];
if ($pdo) {
    try {
        $query = "SELECT * FROM partners ORDER BY display_order ASC, year DESC, name_fr ASC";
        $params = [];
        
        if ($search) {
            $query .= " WHERE name_fr LIKE ? OR name_en LIKE ? OR description_fr LIKE ? OR description_en LIKE ?";
            $searchParam = '%' . $search . '%';
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $partners = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching partners: " . $e->getMessage());
        // Données mock des partenaires existants
        $partners = [
            [
                'id' => 1,
                'name_fr' => 'Foire de l\'Innovation',
                'name_en' => 'Innovation Fair',
                'description_fr' => 'Participation à la Foire Internationale de Dakar (FIDAK) pour présenter nos produits artisanaux et établir des contacts commerciaux internationaux.',
                'description_en' => 'Participation in the Dakar International Fair (FIDAK) to present our artisanal products and establish international business contacts.',
                'year' => '2021',
                'icon' => 'fa-calendar',
                'website' => 'https://fidak.sn',
                'logo' => 'foire-innovation.png',
                'is_active' => true,
                'display_order' => 1,
                'created_at' => '2024-01-15 10:00:00'
            ],
            [
                'id' => 2,
                'name_fr' => 'Marché de Kaolack',
                'name_en' => 'Kaolack Market',
                'description_fr' => 'Présence régulière au plus grand marché de la région pour vendre directement nos produits et rencontrer nos clients fidèles.',
                'description_en' => 'Regular presence at the largest market in the region to sell our products directly and meet our loyal customers.',
                'year' => '2022',
                'icon' => 'fa-map-marker-alt',
                'website' => '',
                'logo' => '',
                'is_active' => true,
                'display_order' => 2,
                'created_at' => '2024-01-12 14:30:00'
            ],
            [
                'id' => 3,
                'name_fr' => 'Humasol',
                'name_en' => 'Humasol',
                'description_fr' => 'Partenariat stratégique avec Humasol pour la promotion des produits solaires et le développement durable dans nos communautés.',
                'description_en' => 'Strategic partnership with Humasol for the promotion of solar products and sustainable development in our communities.',
                'year' => '2023',
                'icon' => 'fa-sun',
                'website' => 'https://humasol.org',
                'logo' => 'humasol-logo.png',
                'is_active' => true,
                'display_order' => 3,
                'created_at' => '2024-01-10 09:00:00'
            ]
        ];
    }
} else {
    // Données mock si BDD indisponible
    $partners = [
        [
            'id' => 1,
            'name_fr' => 'Foire de l\'Innovation',
            'name_en' => 'Innovation Fair',
            'description_fr' => 'Participation à la Foire Internationale de Dakar (FIDAK) pour présenter nos produits artisanaux et établir des contacts commerciaux internationaux.',
            'description_en' => 'Participation in the Dakar International Fair (FIDAK) to present our artisanal products and establish international business contacts.',
            'year' => '2021',
            'icon' => 'fa-calendar',
            'website' => 'https://fidak.sn',
            'logo' => 'foire-innovation.png',
            'is_active' => true,
            'display_order' => 1,
            'created_at' => '2024-01-15 10:00:00'
        ],
        [
            'id' => 2,
            'name_fr' => 'Marché de Kaolack',
            'name_en' => 'Kaolack Market',
            'description_fr' => 'Présence régulière au plus grand marché de la région pour vendre directement nos produits et rencontrer nos clients fidèles.',
            'description_en' => 'Regular presence at the largest market in the region to sell our products directly and meet our loyal customers.',
            'year' => '2022',
            'icon' => 'fa-map-marker-alt',
            'website' => '',
            'logo' => '',
            'is_active' => true,
            'display_order' => 2,
            'created_at' => '2024-01-12 14:30:00'
        ],
        [
            'id' => 3,
            'name_fr' => 'Humasol',
            'name_en' => 'Humasol',
            'description_fr' => 'Partenariat stratégique avec Humasol pour la promotion des produits solaires et le développement durable dans nos communautés.',
            'description_en' => 'Strategic partnership with Humasol for the promotion of solar products and sustainable development in our communities.',
            'year' => '2023',
            'icon' => 'fa-sun',
            'website' => 'https://humasol.org',
            'logo' => 'humasol-logo.png',
            'is_active' => true,
            'display_order' => 3,
            'created_at' => '2024-01-10 09:00:00'
        ]
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
            <p class="text-gray-500 mt-1">Gestion des partenaires de votre boutique.</p>
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
            <i class="fas fa-handshake text-primary-600 text-2xl"></i>
            <h1 class="text-2xl font-heading font-bold">Gestion des Partenaires</h1>
        </div>
        <button onclick="openModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i> Ajouter un partenaire
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
                <input type="hidden" name="action" value="partenaires">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Rechercher un partenaire..."
                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </form>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-4 border-b">
            <p class="text-gray-500"><?php echo count($partners); ?> partenaire(s)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Nom</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Année</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Description</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Site web</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Statut</th>
                        <th class="text-right py-3 px-4 text-gray-600 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($partners as $partner): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($partner['name_fr']); ?></div>
                                <div class="text-xs text-gray-500 mt-1 italic"><?php echo htmlspecialchars($partner['name_en']); ?></div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                    <?php echo htmlspecialchars($partner['year']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="max-w-xs text-sm text-gray-600 truncate" title="<?php echo htmlspecialchars($partner['description_fr']); ?>">
                                    <?php echo htmlspecialchars(substr($partner['description_fr'], 0, 80)) . (strlen($partner['description_fr']) > 80 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <?php if ($partner['website']): ?>
                                    <a href="<?php echo htmlspecialchars($partner['website']); ?>" target="_blank" 
                                       class="text-blue-600 hover:text-blue-800 text-sm">
                                        <i class="fas fa-external-link-alt mr-1"></i>
                                        <?php echo parse_url($partner['website'], PHP_URL_HOST); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $partner['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'; ?>">
                                    <?php echo $partner['is_active'] ? 'Actif' : 'Inactif'; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="editPartner(<?php echo $partner['id']; ?>)" class="p-1 text-blue-600 hover:text-blue-700" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deletePartner(<?php echo $partner['id']; ?>)" class="p-1 text-red-600 hover:text-red-700" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($partners)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Aucun partenaire trouvé</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter/modifier un partenaire -->
<div id="partnerModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-heading font-bold" id="modalTitle">Ajouter un partenaire</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="partnerForm" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="partnerId">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom (FR) *</label>
                        <input type="text" name="name" id="partnerName" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom (EN)</label>
                        <input type="text" name="name_en" id="partnerNameEn" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Description (FR) *</label>
                        <textarea name="description" id="partnerDescription" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Description (EN)</label>
                        <textarea name="description_en" id="partnerDescriptionEn" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Année</label>
                        <input type="text" name="year" id="partnerYear" placeholder="2024"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Icône</label>
                        <select name="icon" id="partnerIcon" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="fa-handshake">🤝 Partenariat</option>
                            <option value="fa-calendar">📅 Événement</option>
                            <option value="fa-map-marker-alt">📍 Localisation</option>
                            <option value="fa-sun">☀️ Énergie</option>
                            <option value="fa-building">🏢 Organisation</option>
                            <option value="fa-globe">🌐 International</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Ordre d'affichage</label>
                        <input type="number" name="display_order" id="partnerOrder" min="0" value="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Site web</label>
                        <input type="url" name="website" id="partnerWebsite" placeholder="https://example.com"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Logo</label>
                        <input type="text" name="logo" id="partnerLogo" placeholder="logo.png"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_active" id="partnerActive" checked class="mr-2">
                        <span class="text-sm font-medium">Partenaire actif</span>
                    </label>
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
    document.getElementById('modalTitle').textContent = 'Ajouter un partenaire';
    document.getElementById('formAction').value = 'add';
    document.getElementById('partnerId').value = '';
    document.getElementById('partnerForm').reset();
    document.getElementById('partnerModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function editPartner(id) {
    const partners = <?php echo json_encode($partners); ?>;
    const partner = partners.find(p => p.id == id);
    
    if (partner) {
        document.getElementById('modalTitle').textContent = 'Modifier le partenaire';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('partnerId').value = id;
        document.getElementById('partnerName').value = partner.name_fr;
        document.getElementById('partnerNameEn').value = partner.name_en;
        document.getElementById('partnerDescription').value = partner.description_fr;
        document.getElementById('partnerDescriptionEn').value = partner.description_en;
        document.getElementById('partnerYear').value = partner.year;
        document.getElementById('partnerIcon').value = partner.icon;
        document.getElementById('partnerWebsite').value = partner.website;
        document.getElementById('partnerLogo').value = partner.logo;
        document.getElementById('partnerOrder').value = partner.display_order;
        document.getElementById('partnerActive').checked = partner.is_active;
        
        document.getElementById('partnerModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function deletePartner(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce partenaire ?')) {
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
    document.getElementById('partnerModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('partnerModal').addEventListener('click', function(e) {
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
