<?php
$pdo = getDB();
$message = '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Récupérer les catégories depuis la BDD pour le formulaire
$categories = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM categories WHERE is_available = TRUE ORDER BY display_order ASC, name_fr ASC");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
        $categories = [];
    }
}

// Récupérer les produits depuis la BDD
$products = [];
if ($pdo) {
    try {
        $query = "SELECT p.*, COALESCE(oi.order_count, 0) as order_count 
             FROM products p 
             LEFT JOIN (
                 SELECT product_id, COUNT(*) as order_count 
                 FROM order_items 
                 GROUP BY product_id
             ) oi ON p.id = oi.product_id";
        $params = [];
        
        // Filtre par catégorie
        if (isset($_GET['category']) && !empty($_GET['category'])) {
            $query .= " WHERE p.category_id = ?";
            $params[] = (int)$_GET['category'];
        }
        
        if ($search) {
            $query .= (isset($_GET['category']) && !empty($_GET['category'])) ? " AND (" : " WHERE (";
            $query .= "p.name LIKE ? OR p.description_fr LIKE ? OR p.ingredients_fr LIKE ?";
            $searchParam = '%' . $search . '%';
            $params = array_merge($params, [$searchParam, $searchParam, $searchParam]);
            $query .= ")";
        }
        
        $query .= " ORDER BY p.created_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $products = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        $products = [];
    }
} else {
    error_log("Base de données non disponible");
    $products = [];
}

// Traitement des actions CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // Fonction pour générer un slug unique
    function generateSlug($name, $pdo = null) {
        // Convertir en slug de base
        $slug = strtolower($name);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        
        // Si la base de données est disponible, vérifier l'unicité
        if ($pdo) {
            $originalSlug = $slug;
            $counter = 1;
            
            while (true) {
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE slug = ?");
                $stmt->execute([$slug]);
                $count = $stmt->fetch()['count'];
                
                if ($count == 0) {
                    break;
                }
                
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
        }
        
        return $slug ?: 'produit-' . time();
    }
    
    // Fonction pour gérer l'upload d'image
    function handleImageUpload($fileInputName, $currentImage = null) {
        if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$fileInputName];
            
            // Validation du fichier
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($file['type'], $allowedTypes)) {
                return ['error' => 'Type de fichier non autorisé. Utilisez JPG, PNG ou GIF.'];
            }
            
            if ($file['size'] > $maxSize) {
                return ['error' => 'Fichier trop volumineux. Taille maximale: 2MB.'];
            }
            
            // Générer un nom de fichier unique
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
            $uploadPath = 'assets/images/' . $filename;
            
            // Créer le dossier s'il n'existe pas
            if (!is_dir('assets/images')) {
                mkdir('assets/images', 0755, true);
            }
            
            // Déplacer le fichier
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Supprimer l'ancienne image si elle existe
                if ($currentImage && file_exists('assets/images/' . $currentImage)) {
                    unlink('assets/images/' . $currentImage);
                }
                return ['success' => true, 'filename' => $filename];
            } else {
                return ['error' => 'Erreur lors du téléchargement du fichier.'];
            }
        }
        
        return ['success' => false, 'filename' => $currentImage];
    }
    
    if ($action === 'add') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $categoryId = isset($_POST['category']) ? (int)$_POST['category'] : 0;
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $ingredients = isset($_POST['ingredients']) ? trim($_POST['ingredients']) : '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $weight = isset($_POST['weight']) ? trim($_POST['weight']) : '';
        $stock = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 0;
        $isAvailable = isset($_POST['is_available']) ? (bool)$_POST['is_available'] : true;
        $isFeatured = isset($_POST['is_featured']) ? (bool)$_POST['is_featured'] : false;
        
        // Gestion de l'image
        $imageResult = handleImageUpload('product_image');
        $imageUrl = $imageResult['success'] ? $imageResult['filename'] : 'default-product.jpg';
        
        if (isset($imageResult['error'])) {
            $message = $imageResult['error'];
        } elseif ($name && $categoryId > 0 && $price > 0) {
            try {
                // Générer un slug unique
                $slug = generateSlug($name, $pdo);
                
                $stmt = $pdo->prepare("INSERT INTO products (name, slug, description_fr, ingredients_fr, price, weight, stock_quantity, is_available, is_featured, image_url, category_id, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $slug, $description, $ingredients, $price, $weight, $stock, $isAvailable, $isFeatured, $imageUrl, $categoryId]);
                $message = 'Produit ajouté avec succès!';
            } catch (PDOException $e) {
                $message = 'Erreur lors de l\'ajout: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'edit') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $categoryId = isset($_POST['category']) ? (int)$_POST['category'] : 0;
        $description = isset($_POST['description']) ? trim($_POST['description']) : '';
        $ingredients = isset($_POST['ingredients']) ? trim($_POST['ingredients']) : '';
        $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
        $weight = isset($_POST['weight']) ? trim($_POST['weight']) : '';
        $stock = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 0;
        $isAvailable = isset($_POST['is_available']) ? (bool)$_POST['is_available'] : true;
        $isFeatured = isset($_POST['is_featured']) ? (bool)$_POST['is_featured'] : false;
        
        // Récupérer l'image actuelle
        $currentImage = null;
        if ($id) {
            $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $currentImage = $stmt->fetch()['image_url'];
        }
        
        // Gestion de l'image
        $imageResult = handleImageUpload('product_image', $currentImage);
        $imageUrl = $imageResult['success'] ? $imageResult['filename'] : $currentImage;
        
        if (isset($imageResult['error'])) {
            $message = $imageResult['error'];
        } elseif ($id && $name && $categoryId > 0 && $price > 0) {
            try {
                // Récupérer le nom actuel pour voir si le slug doit être mis à jour
                $stmt = $pdo->prepare("SELECT name FROM products WHERE id = ?");
                $stmt->execute([$id]);
                $currentProduct = $stmt->fetch();
                
                // Générer un nouveau slug si le nom a changé
                $slug = null;
                if ($currentProduct && $currentProduct['name'] !== $name) {
                    $slug = generateSlug($name, $pdo);
                }
                
                // Mettre à jour avec ou sans le slug selon si le nom a changé
                if ($slug) {
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, slug = ?, description_fr = ?, ingredients_fr = ?, price = ?, weight = ?, stock_quantity = ?, is_available = ?, is_featured = ?, image_url = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $description, $ingredients, $price, $weight, $stock, $isAvailable, $isFeatured, $imageUrl, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, description_fr = ?, ingredients_fr = ?, price = ?, weight = ?, stock_quantity = ?, is_available = ?, is_featured = ?, image_url = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $ingredients, $price, $weight, $stock, $isAvailable, $isFeatured, $imageUrl, $id]);
                }
                
                $message = 'Produit mis à jour avec succès!';
            } catch (PDOException $e) {
                $message = 'Erreur lors de la mise à jour: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id) {
            try {
                // Vérifier si le produit est lié à des commandes
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM order_items WHERE product_id = ?");
                $stmt->execute([$id]);
                $orderCount = $stmt->fetch()['count'];
                
                if ($orderCount > 0) {
                    $message = "Impossible de supprimer ce produit : il est lié à {$orderCount} commande(s). Veuillez d'abord supprimer ou modifier les commandes associées.";
                } else {
                    // Supprimer le produit si aucune commande liée
                    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$id]);
                    $message = 'Produit supprimé avec succès!';
                }
            } catch (PDOException $e) {
                $message = 'Erreur lors de la suppression: ' . $e->getMessage();
            }
        }
    }
}

// Informations pour le header
$adminEmail = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'admin@sokhnamai.sn';
$adminName = explode('@', $adminEmail)[0];
$hour = date('H');
if ($hour < 12) $greeting = 'Bonjour';
elseif ($hour < 18) $greeting = 'Bon après-midi';
else $greeting = 'Bonsoir';

function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}
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

.products-header {
    position: sticky;
    top: 0;
    z-index: 40;
    background: white;
    padding: 1.5rem 0;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}
</style>

<!-- Header fixe avec dropdown utilisateur -->
<div class="admin-header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-4">
        <div>
            <h1 class="text-3xl font-heading font-bold text-gray-900"><?php echo $greeting; ?>, <?php echo ucfirst($adminName); ?> 👋</h1>
            <p class="text-gray-500 mt-1">Gestion des produits de votre boutique.</p>
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
        <div class="products-header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <i class="fas fa-box text-primary-600 text-2xl"></i>
                <h1 class="text-2xl font-heading font-bold">Gestion des Produits</h1>
            </div>
            <button onclick="openModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-plus mr-2"></i> Ajouter un produit
            </button>
        </div>

        <div class="flex items-center gap-3 max-w-2xl mt-4">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <form method="GET" class="w-full">
                    <input type="hidden" name="page" value="admin">
                    <input type="hidden" name="action" value="produits">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Rechercher un produit..."
                           class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </form>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm font-medium text-gray-700">Catégorie:</label>
                <form method="GET" class="flex items-center gap-2">
                    <input type="hidden" name="page" value="admin">
                    <input type="hidden" name="action" value="produits">
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                    <select name="category" onchange="this.form.submit()" 
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="">Toutes les catégories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['id']); ?>" 
                                    <?php echo isset($_GET['category']) && $_GET['category'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name_fr']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <div class="mt-4">
            <p class="text-gray-500"><?php echo count($products); ?> produit(s)</p>
            <?php if ($message): ?>
                <div class="bg-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-50 border border-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-200 text-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-700 px-4 py-3 rounded mb-4 mt-2">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-4 border-b">
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Image</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Produit</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Prix</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Stock</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Disponibilité</th>
                        <th class="text-right py-3 px-4 text-gray-600 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-8 text-gray-500">Aucun produit trouvé</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($products as $p): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                                <td class="py-3 px-4">
                                    <?php 
$imageUrl = $p['image_url'] ?? 'default-product.jpg';
$imageSrc = filter_var($imageUrl, FILTER_VALIDATE_URL) ? $imageUrl : asset($imageUrl);
?>
<img src="<?php echo $imageSrc; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" class="w-12 h-12 rounded object-cover" onerror="this.src='<?php echo asset('default-product.jpg'); ?>'">
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($p['name']); ?></div>
                                    <?php if ($p['ingredients_fr']): ?>
                                        <div class="text-xs text-gray-400 italic mt-1">Ingrédients: <?php echo htmlspecialchars($p['ingredients_fr']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 font-medium text-gray-900"><?php echo formatPrice($p['price']); ?></td>
                                <td class="py-3 px-4">
                                    <span class="text-sm text-gray-600"><?php echo $p['weight']; ?></span>
                                    <div class="text-xs text-gray-500">Stock: <?php echo $p['stock_quantity']; ?></div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 text-xs rounded-full <?php echo $p['is_available'] ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700'; ?>">
                                        <?php echo $p['is_available'] ? 'Disponible' : 'Indisponible'; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <?php if ($p['order_count'] > 0): ?>
                                            <span class="px-2 py-1 bg-orange-100 text-orange-700 text-xs rounded-full" title="<?php echo $p['order_count']; ?> commande(s) liée(s)">
                                                <i class="fas fa-shopping-cart mr-1"></i><?php echo $p['order_count']; ?>
                                            </span>
                                        <?php endif; ?>
                                        <a href="?page=admin&action=edit_product&id=<?php echo $p['id']; ?>" class="p-1 text-blue-600 hover:text-blue-700 inline-block" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php if ($p['order_count'] == 0): ?>
                                            <button onclick="deleteProduct(<?php echo $p['id']; ?>)" class="p-1 text-red-600 hover:text-red-700" title="Supprimer">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php else: ?>
                                            <button onclick="alert('Ce produit est lié à <?php echo $p['order_count']; ?> commande(s) et ne peut être supprimé.')" class="p-1 text-gray-400 cursor-not-allowed" title="Non supprimable">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter/modifier un produit -->
<div id="productModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-heading font-bold" id="modalTitle">Ajouter un produit</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" id="productForm" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="productId">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nom du produit *</label>
                        <input type="text" name="name" id="productName" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Catégorie *</label>
                        <select name="category" id="productCategory" required 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">Choisir une catégorie</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat['id']); ?>">
                                    <?php echo htmlspecialchars($cat['name_fr']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <textarea name="description" id="productDescription" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ingrédients</label>
                    <input type="text" name="ingredients" id="productIngredients" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Photo du produit</label>
                    <div class="space-y-2">
                        <input type="file" name="product_image" id="productImage" accept="image/*" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <input type="hidden" name="image_url" id="imageUrl">
                        <div id="imagePreview" class="hidden">
                            <img id="previewImg" src="" alt="Aperçu" class="w-32 h-32 object-cover rounded-lg border border-gray-300">
                            <button type="button" onclick="removeImage()" class="mt-2 text-sm text-red-600 hover:text-red-700">
                                <i class="fas fa-trash mr-1"></i> Supprimer l'image
                            </button>
                        </div>
                        <p class="text-xs text-gray-500">Formats acceptés: JPG, PNG, GIF. Taille max: 2MB</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Prix (FCFA) *</label>
                        <input type="number" name="price" id="productPrice" required min="0" step="100"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Poids</label>
                        <input type="text" name="weight" id="productWeight" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Stock</label>
                        <input type="number" name="stock" id="productStock" min="0" value="50"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_available" id="productAvailable" checked class="mr-2">
                        <span class="text-sm font-medium">Disponible</span>
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
    const modalTitle = document.getElementById('modalTitle');
    const formAction = document.getElementById('formAction');
    const productId = document.getElementById('productId');
    const productForm = document.getElementById('productForm');
    const productModal = document.getElementById('productModal');
    
    if (modalTitle) modalTitle.textContent = 'Ajouter un produit';
    if (formAction) formAction.value = 'add';
    if (productId) productId.value = '';
    if (productForm) productForm.reset();
    if (productModal) {
        productModal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
}

function editProduct(id) {
    console.log('editProduct appelé avec id:', id);
    
    // Récupérer les données du produit
    const products = <?php echo json_encode($products); ?>;
    console.log('Produits disponibles:', products);
    
    const product = products.find(p => p.id == id);
    console.log('Produit trouvé:', product);
    
    if (product) {
        console.log('Mise à jour du modal avec les données du produit');
        
        // Récupérer les éléments avec vérification
        const modalTitle = document.getElementById('modalTitle');
        const formAction = document.getElementById('formAction');
        const productId = document.getElementById('productId');
        const productName = document.getElementById('productName');
        const productDescription = document.getElementById('productDescription');
        const productIngredients = document.getElementById('productIngredients');
        const productPrice = document.getElementById('productPrice');
        const productWeight = document.getElementById('productWeight');
        const productStock = document.getElementById('productStock');
        const productAvailable = document.getElementById('productAvailable');
        const productModal = document.getElementById('productModal');
        
        console.log('Éléments du modal:', {
            modalTitle: !!modalTitle,
            formAction: !!formAction,
            productId: !!productId,
            productName: !!productName,
            productModal: !!productModal
        });
        
        // Mettre à jour les valeurs si les éléments existent
        if (modalTitle) {
            modalTitle.textContent = 'Modifier le produit';
            console.log('Titre du modal mis à jour');
        }
        if (formAction) {
            formAction.value = 'edit';
            console.log('Action du formulaire mise à jour');
        }
        if (productId) {
            productId.value = id;
            console.log('ID du produit mis à jour:', id);
        }
        if (productName) {
            productName.value = product.name || '';
            console.log('Nom du produit mis à jour:', product.name);
        }
        if (productDescription) {
            productDescription.value = product.description_fr || '';
            console.log('Description du produit mise à jour');
        }
        if (productIngredients) {
            productIngredients.value = product.ingredients_fr || '';
            console.log('Ingrédients du produit mis à jour');
        }
        if (productPrice) {
            productPrice.value = product.price || '';
            console.log('Prix du produit mis à jour:', product.price);
        }
        if (productWeight) {
            productWeight.value = product.weight || '';
            console.log('Poids du produit mis à jour');
        }
        if (productStock) {
            productStock.value = product.stock_quantity || '';
            console.log('Stock du produit mis à jour');
        }
        if (productAvailable) {
            productAvailable.checked = product.is_available || false;
            console.log('Disponibilité du produit mise à jour');
        }
        
        // Afficher le modal
        if (productModal) {
            productModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            console.log('Modal affiché');
        } else {
            console.error('Modal non trouvé!');
        }
    } else {
        console.error('Produit non trouvé pour l\'ID:', id);
    }
}

function deleteProduct(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce produit ?')) {
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
    const productModal = document.getElementById('productModal');
    if (productModal) {
        productModal.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }
}

// Fermer le modal en cliquant à l'extérieur
document.addEventListener('DOMContentLoaded', function() {
    const productModal = document.getElementById('productModal');
    if (productModal) {
        productModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    }
    
    // Fermer avec la touche Echape
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    
    // Test pour vérifier si la fonction editProduct est accessible
    console.log('Fonction editProduct disponible:', typeof editProduct);
    
    // Ajouter un écouteur de clic global pour débogage
    document.addEventListener('click', function(e) {
        if (e.target.closest('button[onclick*="editProduct"]')) {
            console.log('Clic détecté sur un bouton editProduct');
            const button = e.target.closest('button');
            console.log('Attribut onclick:', button.getAttribute('onclick'));
        }
    });
});

// Gestion de l'upload d'image
document.addEventListener('DOMContentLoaded', function() {
    const productImage = document.getElementById('productImage');
    if (productImage) {
        productImage.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validation du type de fichier
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Type de fichier non autorisé. Utilisez JPG, PNG ou GIF.');
                    e.target.value = '';
                    return;
                }
                
                // Validation de la taille
                const maxSize = 2 * 1024 * 1024; // 2MB
                if (file.size > maxSize) {
                    alert('Fichier trop volumineux. Taille maximale: 2MB.');
                    e.target.value = '';
                    return;
                }
                
                // Prévisualisation
                const reader = new FileReader();
                reader.onload = function(e) {
                    const previewImg = document.getElementById('previewImg');
                    const imagePreview = document.getElementById('imagePreview');
                    if (previewImg) previewImg.src = e.target.result;
                    if (imagePreview) imagePreview.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

// Fonction pour supprimer l'image
function removeImage() {
    const productImage = document.getElementById('productImage');
    const imageUrl = document.getElementById('imageUrl');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    if (productImage) productImage.value = '';
    if (imageUrl) imageUrl.value = '';
    if (imagePreview) imagePreview.classList.add('hidden');
    if (previewImg) previewImg.src = '';
}

// Mettre à jour la fonction editProduct pour charger l'image existante
function editProduct(id) {
    // Récupérer les données du produit depuis le tableau
    const products = <?php echo json_encode($products); ?>;
    const product = products.find(p => p.id == id);
    
    if (product) {
        document.getElementById('modalTitle').textContent = 'Modifier un produit';
        document.getElementById('formAction').value = 'edit';
        document.getElementById('productId').value = product.id;
        document.getElementById('productName').value = product.name;
        document.getElementById('productDescription').value = product.description_fr || '';
        document.getElementById('productIngredients').value = product.ingredients_fr || '';
        document.getElementById('productPrice').value = product.price;
        document.getElementById('productWeight').value = product.weight || '';
        document.getElementById('productStock').value = product.stock_quantity || 0;
        document.getElementById('productAvailable').checked = product.is_available;
        document.getElementById('productFeatured').checked = product.is_featured;
        
        // Afficher l'image existante
        if (product.image_url && product.image_url !== 'default-product.jpg') {
            document.getElementById('previewImg').src = '<?php echo asset(""); ?>' + product.image_url;
            document.getElementById('imagePreview').classList.remove('hidden');
            document.getElementById('imageUrl').value = product.image_url;
        } else {
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('imageUrl').value = '';
        }
        
        openModal();
    }
}

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

// Fermer avec la touche Echape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const dropdown = document.getElementById('user-dropdown-menu');
        const arrow = document.getElementById('dropdown-arrow');
        
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
            if (arrow) arrow.style.transform = '';
        }
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
