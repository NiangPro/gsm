<?php
require_once __DIR__ . '/../includes/config.php';

// Récupérer l'ID du produit
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$error = '';

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';
    $ingredients = isset($_POST['ingredients']) ? trim($_POST['ingredients']) : '';
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $weight = isset($_POST['weight']) ? trim($_POST['weight']) : '';
    $stock = isset($_POST['stock_quantity']) ? (int)$_POST['stock_quantity'] : 0;
    $isAvailable = isset($_POST['is_available']) ? (bool)$_POST['is_available'] : true;
    $isFeatured = isset($_POST['is_featured']) ? (bool)$_POST['is_featured'] : false;
    $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 0;
    
    // Gestion du type d'image
    $imageType = isset($_POST['image_type']) ? $_POST['image_type'] : 'upload';
    $externalImageUrl = isset($_POST['external_image_url']) ? trim($_POST['external_image_url']) : '';
    
    $pdo = getDB();
    if ($pdo) {
        try {
            // Récupérer l'image actuelle
            $stmt = $pdo->prepare("SELECT image_url FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $currentImage = $stmt->fetch()['image_url'];
            
            // Gestion de l'image selon le type choisi
            $imageUrl = $currentImage;
            $imageError = '';
            
            if ($imageType === 'external') {
                // Validation de l'URL externe
                if (!empty($externalImageUrl)) {
                    if (filter_var($externalImageUrl, FILTER_VALIDATE_URL)) {
                        // Vérifier si l'URL est accessible (optionnel)
                        $imageUrl = $externalImageUrl;
                    } else {
                        $imageError = 'URL d\'image invalide. Veuillez entrer une URL valide.';
                    }
                }
            } else {
                // Upload d'image interne
                $imageResult = handleImageUpload('product_image', $currentImage);
                if (isset($imageResult['error'])) {
                    $imageError = $imageResult['error'];
                } elseif ($imageResult['success']) {
                    $imageUrl = $imageResult['filename'];
                }
            }
            
            if ($imageError) {
                $error = $imageError;
            } elseif ($id && $name && $price > 0) {
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
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, slug = ?, description_fr = ?, ingredients_fr = ?, category_id = ?, price = ?, weight = ?, stock_quantity = ?, is_available = ?, is_featured = ?, image_url = ? WHERE id = ?");
                    $stmt->execute([$name, $slug, $description, $ingredients, $categoryId, $price, $weight, $stock, $isAvailable, $isFeatured, $imageUrl, $id]);
                } else {
                    $stmt = $pdo->prepare("UPDATE products SET name = ?, description_fr = ?, ingredients_fr = ?, category_id = ?, price = ?, weight = ?, stock_quantity = ?, is_available = ?, is_featured = ?, image_url = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $ingredients, $categoryId, $price, $weight, $stock, $isAvailable, $isFeatured, $imageUrl, $id]);
                }
                
                $message = 'Produit mis à jour avec succès! <a href="?page=admin&action=produits" class="text-blue-600 hover:underline">Retour à la liste des produits</a>';
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour: ' . $e->getMessage();
        }
    }
}

// Fonctions utilitaires
function generateSlug($name, $pdo = null) {
    $slug = strtolower($name);
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    if ($pdo) {
        $originalSlug = $slug;
        $counter = 1;
        
        while (true) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products WHERE slug = ?");
            $stmt->execute([$slug]);
            $count = $stmt->fetch()['count'];
            
            if ($count == 0) break;
            
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    }
    
    return $slug ?: 'produit-' . time();
}

function handleImageUpload($fileInputName, $currentImage = null) {
    if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES[$fileInputName];
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            return ['error' => 'Type de fichier non autorisé. Utilisez JPG, PNG ou GIF.'];
        }
        
        if ($file['size'] > $maxSize) {
            return ['error' => 'Fichier trop volumineux. Taille maximale: 2MB.'];
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'product_' . time() . '_' . uniqid() . '.' . $extension;
        $uploadPath = __DIR__ . '/../assets/images/' . $filename;
        
        if (!is_dir(__DIR__ . '/../assets/images')) {
            mkdir(__DIR__ . '/../assets/images', 0755, true);
        }
        
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            if ($currentImage && $currentImage !== 'default-product.jpg' && file_exists(__DIR__ . '/../assets/images/' . $currentImage)) {
                unlink(__DIR__ . '/../assets/images/' . $currentImage);
            }
            return ['success' => true, 'filename' => $filename];
        } else {
            return ['error' => 'Erreur lors du téléchargement du fichier.'];
        }
    }
    
    return ['success' => false, 'filename' => $currentImage];
}

// Récupérer les catégories pour le formulaire
$categories = [];
$pdo = getDB();
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM categories WHERE is_available = TRUE ORDER BY display_order ASC, name_fr ASC");
        $categories = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching categories: " . $e->getMessage());
    }
}

// Récupérer les données du produit
$product = null;
if ($pdo && $productId > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if (!$product) {
            $error = 'Produit non trouvé.';
        }
    } catch (PDOException $e) {
        $error = 'Erreur lors de la récupération du produit: ' . $e->getMessage();
    }
} else {
    $error = 'ID de produit non valide.';
}
?>

<!-- Contenu principal pour la page admin -->
<div class="space-y-6">
    <!-- En-tête -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="?page=admin&action=produits" class="text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-heading font-bold">Modifier le produit</h1>
        </div>
        <a href="?page=admin&action=produits" class="text-gray-600 hover:text-gray-900">
            <i class="fas fa-times"></i> Annuler
        </a>
    </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire -->
            <?php if ($product): ?>
                <form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nom du produit -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Nom du produit *</label>
                            <input type="text" name="name" id="productName" required 
                                   value="<?php echo htmlspecialchars($product['name']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>

                        <!-- Prix -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Prix (FCFA) *</label>
                            <input type="number" name="price" id="productPrice" required min="0" step="100"
                                   value="<?php echo $product['price']; ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>

                        <!-- Poids -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Poids</label>
                            <input type="text" name="weight" id="productWeight" 
                                   value="<?php echo htmlspecialchars($product['weight']); ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>

                        <!-- Stock -->
                        <div>
                            <label class="block text-sm font-medium mb-1">Stock</label>
                            <input type="number" name="stock_quantity" id="productStock" min="0"
                                   value="<?php echo $product['stock_quantity']; ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium mb-1">Description</label>
                        <textarea name="description" id="productDescription" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"><?php echo htmlspecialchars($product['description_fr']); ?></textarea>
                    </div>

                    <!-- Ingrédients -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium mb-1">Ingrédients</label>
                        <input type="text" name="ingredients" id="productIngredients" 
                               value="<?php echo htmlspecialchars($product['ingredients_fr']); ?>"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <!-- Catégorie -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium mb-1">Catégorie</label>
                        <select name="category_id" id="productCategory" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="">-- Sélectionner une catégorie --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo ($product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name_fr']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Image -->
                    <div class="mt-6">
                        <label class="block text-sm font-medium mb-1">Photo du produit</label>
                        
                        <!-- Type d'image -->
                        <div class="mb-4">
                            <div class="flex gap-4">
                                <label class="flex items-center">
                                    <input type="radio" name="image_type" value="upload" 
                                           <?php echo (!isset($_POST['image_type']) || $_POST['image_type'] === 'upload') && !filter_var($product['image_url'] ?? '', FILTER_VALIDATE_URL) ? 'checked' : ''; ?> 
                                           class="mr-2" onchange="toggleImageType('upload')">
                                    <span class="text-sm">Uploader une image</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="image_type" value="external" 
                                           <?php echo (isset($_POST['image_type']) && $_POST['image_type'] === 'external') || filter_var($product['image_url'] ?? '', FILTER_VALIDATE_URL) ? 'checked' : ''; ?> 
                                           class="mr-2" onchange="toggleImageType('external')">
                                    <span class="text-sm">URL externe</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Upload d'image -->
                        <div id="uploadSection" class="space-y-2" style="<?php echo (isset($_POST['image_type']) && $_POST['image_type'] === 'external') || filter_var($product['image_url'] ?? '', FILTER_VALIDATE_URL) ? 'display: none;' : ''; ?>">
                            <input type="file" name="product_image" id="productImage" accept="image/*" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        
                        <!-- URL externe -->
                        <div id="externalSection" class="space-y-2" style="<?php echo (!isset($_POST['image_type']) || $_POST['image_type'] === 'upload') && !filter_var($product['image_url'] ?? '', FILTER_VALIDATE_URL) ? 'display: none;' : ''; ?>">
                            <input type="url" name="external_image_url" id="externalImageUrl" 
                                   value="<?php echo filter_var($product['image_url'] ?? '', FILTER_VALIDATE_URL) ? htmlspecialchars($product['image_url']) : (isset($_POST['external_image_url']) ? htmlspecialchars($_POST['external_image_url']) : ''); ?>"
                                   placeholder="https://example.com/image.jpg" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <p class="text-xs text-gray-500">Entrez l'URL complète de l'image (http:// ou https://)</p>
                        </div>
                        
                        <!-- Aperçu de l'image actuelle -->
                        <?php if ($product['image_url'] && $product['image_url'] !== 'default-product.jpg'): ?>
                            <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                                <img src="<?php echo filter_var($product['image_url'], FILTER_VALIDATE_URL) ? htmlspecialchars($product['image_url']) : asset($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                     class="w-16 h-16 rounded object-cover">
                                <div>
                                    <p class="text-sm text-gray-600">Image actuelle</p>
                                    <p class="text-xs text-gray-500"><?php echo htmlspecialchars($product['image_url']); ?></p>
                                    <p class="text-xs text-gray-400">
                                        <?php echo filter_var($product['image_url'], FILTER_VALIDATE_URL) ? 'URL externe' : 'Image interne'; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Options -->
                    <div class="mt-6 space-y-3">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_available" id="productAvailable" 
                                   <?php echo $product['is_available'] ? 'checked' : ''; ?> class="mr-2">
                            <span class="text-sm font-medium">Disponible</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="is_featured" id="productFeatured" 
                                   <?php echo $product['is_featured'] ? 'checked' : ''; ?> class="mr-2">
                            <span class="text-sm font-medium">Produit mis en avant</span>
                        </label>
                    </div>

                    <!-- Boutons -->
                    <div class="flex gap-3 justify-end mt-8 pt-6 border-t">
                        <a href="?page=admin&action=produits" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                            Annuler
                        </a>
                        <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                            <i class="fas fa-save mr-2"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            <?php endif; ?>
</div>

<script>
function toggleImageType(type) {
    const uploadSection = document.getElementById('uploadSection');
    const externalSection = document.getElementById('externalSection');
    
    if (type === 'upload') {
        uploadSection.style.display = 'block';
        externalSection.style.display = 'none';
    } else {
        uploadSection.style.display = 'none';
        externalSection.style.display = 'block';
    }
}

// Validation de l'URL externe en temps réel
document.addEventListener('DOMContentLoaded', function() {
    const externalUrlInput = document.getElementById('externalImageUrl');
    if (externalUrlInput) {
        externalUrlInput.addEventListener('blur', function() {
            const url = this.value.trim();
            if (url && !isValidUrl(url)) {
                this.classList.add('border-red-500');
            } else {
                this.classList.remove('border-red-500');
            }
        });
    }
});

function isValidUrl(string) {
    try {
        new URL(string);
        return true;
    } catch (_) {
        return false;
    }
}
</script>
