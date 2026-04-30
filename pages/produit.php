<?php
// Inclure les fonctions utilitaires
require_once __DIR__ . '/../includes/functions.php';

// Page de détails d'un produit
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$product = null;
$relatedProducts = [];
$category = null;

// Récupérer les détails du produit
if ($productId > 0) {
    $pdo = getDB();
    if ($pdo) {
        try {
            // Récupérer le produit avec sa catégorie
            $stmt = $pdo->prepare("SELECT p.*, c.name_fr as category_name, c.name_en as category_name_en 
                                   FROM products p 
                                   LEFT JOIN categories c ON p.category_id = c.id 
                                   WHERE p.id = ? AND p.is_available = 1");
            $stmt->execute([$productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                // Récupérer les produits similaires (même catégorie)
                $stmt = $pdo->prepare("SELECT id, name, price, weight, image_url, description_fr 
                                       FROM products 
                                       WHERE category_id = ? AND id != ? AND is_available = 1 
                                       ORDER BY RAND() LIMIT 4");
                $stmt->execute([$product['category_id'], $productId]);
                $relatedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Récupérer les détails de la catégorie
                if ($product['category_id']) {
                    $stmt = $pdo->prepare("SELECT id, name_fr, description_fr, image_url 
                                           FROM categories 
                                           WHERE id = ?");
                    $stmt->execute([$product['category_id']]);
                    $category = $stmt->fetch(PDO::FETCH_ASSOC);
                }
            }
        } catch (PDOException $e) {
            error_log("Error fetching product details: " . $e->getMessage());
        }
    }
}

// Si le produit n'existe pas, afficher un message d'erreur dans le contexte de la page
if (!$product) {
    ?>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12">
        <div class="text-center max-w-md">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-6">
                <i class="fas fa-exclamation-triangle text-red-600 text-3xl"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900 mb-4">Produit non trouvé</h1>
            <p class="text-gray-600 mb-6">Le produit que vous recherchez n'existe pas ou n'est plus disponible.</p>
            <a href="?page=home" class="inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-home mr-2"></i>
                Retour à l'accueil
            </a>
        </div>
    </div>
    <?php
    exit;
}

// Styles CSS pour la page produit
?>
<style>
.product-image-container {
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 1rem;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-image:hover {
    transform: scale(1.05);
}

.product-gallery {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.5rem;
    margin-top: 1rem;
}

.gallery-thumb {
    aspect-ratio: 1;
    overflow: hidden;
    border-radius: 0.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.gallery-thumb:hover,
.gallery-thumb.active {
    border-color: #16a34a;
    transform: scale(1.05);
}

.gallery-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.product-info {
    animation: fadeInUp 0.6s ease-out;
}

.related-product-card {
    transition: all 0.3s ease;
}

.related-product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

.breadcrumb {
    animation: fadeIn 0.4s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@media (max-width: 768px) {
    .product-gallery {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .product-image-container {
        aspect-ratio: 4/3;
    }
}
</style>


<!-- Fil d'Ariane -->
<nav class="breadcrumb py-4 px-4 max-w-7xl mx-auto">
    <div class="flex items-center text-sm text-gray-600 space-x-2">
        <a href="?page=home" class="hover:text-primary-600 transition-colors">
            <i class="fas fa-home mr-1"></i> Accueil
        </a>
        <i class="fas fa-chevron-right text-xs"></i>
        <a href="?page=catalogue" class="hover:text-primary-600 transition-colors">Catalogue</a>
        <?php if ($category): ?>
            <i class="fas fa-chevron-right text-xs"></i>
            <a href="?page=catalogue&category=<?php echo $category['id']; ?>" class="hover:text-primary-600 transition-colors">
                <?php echo htmlspecialchars($category['name_fr']); ?>
            </a>
        <?php endif; ?>
        <i class="fas fa-chevron-right text-xs"></i>
        <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($product['name']); ?></span>
    </div>
</nav>

<!-- Contenu principal -->
<div class="container mx-auto px-4 max-w-7xl py-8">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
        <!-- Section Images -->
        <div class="product-info">
            <div class="product-image-container bg-gray-100">
                <?php 
                $mainImage = $product['image_url'] ?? 'default-product.jpg';
                $imageSrc = filter_var($mainImage, FILTER_VALIDATE_URL) ? $mainImage : asset($mainImage);
                ?>
                <img src="<?php echo $imageSrc; ?>" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>" 
                     class="product-image"
                     id="mainProductImage"
                     onerror="this.src='<?php echo asset('default-product.jpg'); ?>'">
            </div>
            
            <!-- Galerie d'images (si disponible) -->
            <?php if (!empty($product['gallery_images'])): ?>
                <div class="product-gallery">
                    <?php 
                    $galleryImages = json_decode($product['gallery_images'], true) ?? [];
                    foreach ($galleryImages as $index => $galleryImage): 
                        $gallerySrc = filter_var($galleryImage, FILTER_VALIDATE_URL) ? $galleryImage : asset($galleryImage);
                    ?>
                        <div class="gallery-thumb <?php echo $index === 0 ? 'active' : ''; ?>" 
                             onclick="changeMainImage('<?php echo $gallerySrc; ?>', this)">
                            <img src="<?php echo $gallerySrc; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?> - Image <?php echo $index + 1; ?>"
                                 onerror="this.src='<?php echo asset('default-product.jpg'); ?>'">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section Informations -->
        <div class="product-info">
            <!-- Catégorie -->
            <?php if ($category): ?>
                <div class="mb-4">
                    <a href="?page=catalogue&category=<?php echo $category['id']; ?>" 
                       class="inline-flex items-center px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm font-medium hover:bg-primary-200 transition-colors">
                        <i class="fas fa-tag mr-2 text-xs"></i>
                        <?php echo htmlspecialchars($category['name_fr']); ?>
                    </a>
                </div>
            <?php endif; ?>

            <!-- Titre et prix -->
            <h1 class="text-3xl md:text-4xl font-heading font-bold text-gray-900 mb-4">
                <?php echo htmlspecialchars($product['name']); ?>
            </h1>
            
            <div class="flex items-center justify-between mb-6">
                <div>
                    <span class="text-3xl font-bold text-primary-600"><?php echo formatPrice($product['price']); ?></span>
                    <span class="text-gray-500 ml-2"><?php echo htmlspecialchars($product['weight']); ?></span>
                </div>
                
                <!-- Disponibilité -->
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-sm text-gray-600">En stock</span>
                </div>
            </div>

            <!-- Description -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-3">Description</h2>
                <div class="prose prose-gray max-w-none">
                    <p class="text-gray-600 leading-relaxed">
                        <?php echo nl2br(htmlspecialchars($product['description_fr'])); ?>
                    </p>
                </div>
            </div>

            <!-- Caractéristiques -->
            <?php if (!empty($product['ingredients']) || !empty($product['nutrition_info'])): ?>
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-3">Caractéristiques</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php if (!empty($product['ingredients'])): ?>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="font-medium text-gray-900 mb-2">
                                    <i class="fas fa-list-ul mr-2 text-primary-600"></i>
                                    Ingrédients
                                </h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['ingredients']); ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($product['nutrition_info'])): ?>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h3 class="font-medium text-gray-900 mb-2">
                                    <i class="fas fa-chart-pie mr-2 text-primary-600"></i>
                                    Informations nutritionnelles
                                </h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($product['nutrition_info']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="border-t border-gray-200 pt-6">
                <div class="flex flex-col sm:flex-row gap-4">
                    <button class="add-to-cart-btn flex-1 inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors"
                            data-product='<?php echo json_encode([
                                'id' => $product['id'],
                                'name' => $product['name'],
                                'price' => $product['price'],
                                'weight' => $product['weight'],
                                'image' => filter_var($product['image_url'] ?? '', FILTER_VALIDATE_URL) ? ($product['image_url'] ?? '') : asset($product['image_url'] ?? 'default-product.jpg'),
                                'category' => $category['name_fr'] ?? 'Produits'
                            ]); ?>'
                            data-quantity="1">
                        <i class="fas fa-cart-plus mr-2"></i>
                        Ajouter au panier
                    </button>
                    
                    <a href="https://wa.me/221778084577?text=Bonjour, je suis intéressé(e) par le produit : <?php echo urlencode($product['name']); ?>" 
                       target="_blank"
                       class="flex-1 inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors">
                        <i class="fab fa-whatsapp mr-2"></i>
                        Commander via WhatsApp
                    </a>
                </div>
                
                <div class="mt-4">
                    <a href="tel:+221778084577" 
                       class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-phone mr-2"></i>
                        Appeler directement
                    </a>
                </div>
                
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-shield-alt mr-1"></i>
                        Paiement sécurisé à la livraison
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Produits similaires -->
    <?php if (!empty($relatedProducts)): ?>
        <div class="mt-16">
            <div class="text-center mb-8">
                <h2 class="text-2xl md:text-3xl font-heading font-bold text-gray-900 mb-2">Produits similaires</h2>
                <p class="text-gray-600">Découvrez d'autres produits de la même catégorie</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="related-product-card bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="aspect-square overflow-hidden bg-gray-100">
                            <?php 
                            $relatedImage = $relatedProduct['image_url'] ?? 'default-product.jpg';
                            $relatedImageSrc = filter_var($relatedImage, FILTER_VALIDATE_URL) ? $relatedImage : asset($relatedImage);
                            ?>
                            <img src="<?php echo $relatedImageSrc; ?>" 
                                 alt="<?php echo htmlspecialchars($relatedProduct['name']); ?>" 
                                 class="w-full h-full object-cover hover:scale-105 transition-transform duration-300"
                                 onerror="this.src='<?php echo asset('default-product.jpg'); ?>'">
                        </div>
                        <div class="p-4">
                            <h3 class="font-heading font-semibold text-gray-900 mb-2"><?php echo htmlspecialchars($relatedProduct['name']); ?></h3>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm text-gray-500"><?php echo htmlspecialchars($relatedProduct['weight']); ?></span>
                                <span class="font-bold text-primary-600"><?php echo formatPrice($relatedProduct['price']); ?></span>
                            </div>
                            <a href="?page=produit&id=<?php echo $relatedProduct['id']; ?>" 
                               class="block w-full text-center px-3 py-2 bg-primary-600 text-white text-sm rounded hover:bg-primary-700 transition-colors">
                                Voir les détails
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
// Changer l'image principale lors du clic sur une miniature
function changeMainImage(imageSrc, thumbnail) {
    const mainImage = document.getElementById('mainProductImage');
    mainImage.src = imageSrc;
    
    // Mettre à jour la classe active
    document.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.classList.remove('active');
    });
    thumbnail.classList.add('active');
}

// Animation au scroll
document.addEventListener('DOMContentLoaded', function() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, {
        threshold: 0.1
    });

    document.querySelectorAll('.related-product-card').forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.6s ease-out';
        observer.observe(card);
    });
});
</script>

