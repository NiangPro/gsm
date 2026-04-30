<?php

// Récupérer les produits depuis la base de données
$products = [];
$pdo = getDB();

if ($pdo) {
    try {
        $query = "SELECT p.id, p.name, p.description_fr, p.ingredients_fr, p.price, p.weight, p.stock_quantity, p.is_available, p.image_url, 
                     COALESCE(c.name_fr, 'Produits') as category_name
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     WHERE p.is_available = 1 
                     ORDER BY p.name ASC";
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $dbProducts = $stmt->fetchAll();
        
        foreach ($dbProducts as $p) {
            $products[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'category' => $p['category_name'],
                'description' => $p['description_fr'],
                'ingredients' => $p['ingredients_fr'],
                'price' => $p['price'],
                'weight' => $p['weight'],
                'image' => $p['image_url'] ?? getDefaultImage($p['name']),
                'is_available' => $p['is_available']
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
    }
}

// Fallback si aucun produit en BDD ou erreur
if (empty($products)) {
    $products = [
        ['id' => 1, 'name' => 'Confiture de Mangue', 'category' => 'Confitures', 'description' => 'Confiture artisanale préparée avec des mangues fraîches du Sénégal.', 'ingredients' => 'Mangue, sucre, citron', 'price' => 2500, 'weight' => '250g', 'image' => 'confiture-mangue.jpg', 'is_available' => true],
        ['id' => 2, 'name' => 'Confiture de Gingembre', 'category' => 'Confitures', 'description' => 'Une confiture épicée au gingembre frais, parfaite pour le petit-déjeuner.', 'ingredients' => 'Gingembre, sucre, citron', 'price' => 3000, 'weight' => '250g', 'image' => 'confiture-gingembre.jpg', 'is_available' => true],
        ['id' => 3, 'name' => 'Confiture de Pomplemousse', 'category' => 'Confitures', 'description' => 'Confiture acidulée au pomplemousse, un délice unique.', 'ingredients' => 'Pomplemousse, sucre', 'price' => 2800, 'weight' => '250g', 'image' => 'confiture-pomplemousse.jpg', 'is_available' => true],
        ['id' => 4, 'name' => 'Confiture de Tamarin', 'category' => 'Confitures', 'description' => 'Confiture exotique au tamarin, sucrée et acidulée.', 'ingredients' => 'Tamarin, sucre, épices', 'price' => 2500, 'weight' => '250g', 'image' => 'confiture-tamarin.jpg', 'is_available' => true],
        ['id' => 5, 'name' => 'Confiture de Citron', 'category' => 'Confitures', 'description' => 'Confiture rafraîchissante au citron, idéale pour les desserts.', 'ingredients' => 'Citron, sucre, gingembre', 'price' => 2400, 'weight' => '250g', 'image' => 'confiture-citron.jpg', 'is_available' => true],
        ['id' => 6, 'name' => 'Confiture de Papaye', 'category' => 'Confitures', 'description' => 'Confiture de papaye, fruit tropical aux saveurs uniques.', 'ingredients' => 'Papaye, sucre, vanille', 'price' => 2300, 'weight' => '250g', 'image' => 'confiture-papaye.jpg', 'is_available' => true],
        ['id' => 7, 'name' => 'Confiture de Baobab', 'category' => 'Confitures', 'description' => 'Confiture originale au fruit de baobab, riche en vitamine C.', 'ingredients' => 'Pulp de baobab, sucre', 'price' => 3200, 'weight' => '250g', 'image' => 'confiture-baobab.jpg', 'is_available' => true],
        ['id' => 8, 'name' => 'Jus de Bouye', 'category' => 'Jus', 'description' => 'Jus naturel de bouye pressé à froid.', 'ingredients' => 'Bouye, eau, sucre', 'price' => 1500, 'weight' => '500ml', 'image' => 'jus-bouye-lait-sans-sucre-djolof-15l.png', 'is_available' => true],
        ['id' => 9, 'name' => 'Jus de Bissap', 'category' => 'Jus', 'description' => 'Jus de bissap rafraîchissant et naturel.', 'ingredients' => 'Bissap, eau, sucre', 'price' => 1200, 'weight' => '500ml', 'image' => 'jus-bissap-JUS_BI02.jpg', 'is_available' => true],
        ['id' => 10, 'name' => 'Jus de Mangue', 'category' => 'Jus', 'description' => 'Jus de mangue doux et parfumé.', 'ingredients' => 'Mangue, eau, sucre', 'price' => 1500, 'weight' => '500ml', 'image' => 'default-product.jpg', 'is_available' => true],
        ['id' => 11, 'name' => 'Jus de Gingembre', 'category' => 'Jus', 'description' => 'Jus de gingembre épicé et revigorant.', 'ingredients' => 'Gingembre, eau, sucre', 'price' => 1800, 'weight' => '500ml', 'image' => 'jus-gingembre-JUS_GI02.jpg', 'is_available' => true],
        ['id' => 12, 'name' => 'Jus de Tamarin Maria', 'category' => 'Jus', 'description' => 'Jus de tamarin authentique Maria.', 'ingredients' => 'Tamarin, eau, sucre', 'price' => 1600, 'weight' => '1L', 'image' => '[JUS-TAMA-MARI-1L] Jus de Tamarin Maria.png', 'is_available' => true],
        ['id' => 13, 'name' => 'Jus de Ditakh', 'category' => 'Jus', 'description' => 'Jus de ditakh sucré Djolof.', 'ingredients' => 'Ditakh, eau, sucre', 'price' => 1400, 'weight' => '1.5L', 'image' => 'jus-ditakh-sucre-djolof-15l.png', 'is_available' => true],
        ['id' => 14, 'name' => 'Jus de Moringa', 'category' => 'Jus', 'description' => 'Jus de moringa riche en nutriments et vitamines.', 'ingredients' => 'Feuilles de moringa, eau, sucre', 'price' => 2000, 'weight' => '500ml', 'image' => 'default-product.jpg', 'is_available' => true],
        ['id' => 15, 'name' => 'Jus de Tamarin', 'category' => 'Jus', 'description' => 'Jus de tamarin naturel, sucré et acidulé.', 'ingredients' => 'Tamarin, eau, sucre', 'price' => 1300, 'weight' => '1L', 'image' => 'default-product.jpg', 'is_available' => true],
    ];
}

// Fonction pour obtenir le chemin correct de l'image
function getImageSrc($imageUrl) {
    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        return $imageUrl; // URL externe
    }
    return asset($imageUrl); // Image interne
}

// Fonction pour obtenir le nom de la catégorie
function getCategoryName($productId) {
    $pdo = getDB();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("
                SELECT c.name_fr 
                FROM categories c 
                JOIN products p ON p.category_id = c.id 
                WHERE p.id = ?
            ");
            $stmt->execute([$productId]);
            $result = $stmt->fetch();
            return $result ? $result['name_fr'] : 'Produits';
        } catch (PDOException $e) {
            return 'Produits';
        }
    }
    return 'Produits';
}

// Fonction pour obtenir une image par défaut selon le type de produit
function getDefaultImage($productName) {
    $productImages = [
        'Confiture' => 'confiture-default.jpg',
        'Jus' => 'jus-default.jpg',
        'Sirop' => 'sirop-default.jpg',
        'Poudre' => 'poudre-default.jpg',
        'Miel' => 'miel-default.jpg',
        'Thé' => 'the-default.jpg',
        'Pâte' => 'pate-default.jpg'
    ];
    
    foreach ($productImages as $type => $image) {
        if (stripos($productName, $type) !== false) {
            return $image;
        }
    }
    
    return 'product-default.jpg';
}

$activeCategory = isset($_GET['category']) ? $_GET['category'] : 'all';
$categories = array_unique(array_column($products, 'category'));

// Toujours charger tous les produits pour permettre le filtrage dynamique côté client
$filteredProducts = $products;

function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}
?>

<div>
    <section class="bg-gradient-to-br from-primary-50 to-gray-100 py-20">
        <div class="container mx-auto px-4 max-w-7xl text-center max-w-3xl">
            <?php if ($activeCategory !== 'all'): ?>
                <div class="mb-4">
                    <a href="?page=catalogue" class="inline-flex items-center text-primary-600 hover:text-primary-700 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Retour au catalogue
                    </a>
                </div>
                <h1 class="text-4xl md:text-5xl font-heading font-bold mb-4">
                    <?php echo htmlspecialchars(ucfirst($activeCategory)); ?>
                </h1>
                <p class="text-lg text-gray-600">
                    Découvrez notre sélection de <?php echo htmlspecialchars(strtolower($activeCategory)); ?> artisanales
                </p>
                <div class="mt-6">
                    <span class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-sm">
                        <i class="fas fa-tag text-primary-600 mr-2"></i>
                        <span class="font-medium"><?php echo count($filteredProducts); ?></span>
                        <span class="text-gray-600 ml-1">produit(s) trouvé(s)</span>
                    </span>
                </div>
            <?php else: ?>
                <h1 class="text-4xl md:text-5xl font-heading font-bold mb-6"><?php echo __('catalogue.title'); ?></h1>
                <p class="text-lg text-gray-600"><?php echo __('catalogue.subtitle'); ?></p>
            <?php endif; ?>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4 max-w-7xl">
            <!-- Étapes de navigation -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm text-center p-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-search text-lg"></i>
                    </div>
                    <h3 class="font-heading font-semibold mb-2">1. Découvrez nos produits</h3>
                    <p class="text-sm text-gray-600">Parcourez notre catalogue de produits artisanaux du Sénégal</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm text-center p-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-eye text-lg"></i>
                    </div>
                    <h3 class="font-heading font-semibold mb-2">2. Consultez les détails</h3>
                    <p class="text-sm text-gray-600">Cliquez sur un produit pour voir ses caractéristiques complètes</p>
                </div>
                <div class="bg-white rounded-lg border border-gray-200 shadow-sm text-center p-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-phone text-lg"></i>
                    </div>
                    <h3 class="font-heading font-semibold mb-2">3. Contactez-nous</h3>
                    <p class="text-sm text-gray-600">Commandez directement via WhatsApp ou téléphone</p>
                </div>
            </div>

            <!-- Section Filtres Avancés -->
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-10">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Recherche -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-search mr-1"></i> Recherche
                        </label>
                        <input type="text" id="searchInput" placeholder="Rechercher un produit..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    
                    <!-- Filtre par catégorie -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-1"></i> Catégorie
                        </label>
                        <select id="categoryFilter" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="all">Toutes les catégories</option>
                            <option value="Jus">Jus</option>
                            <?php foreach ($categories as $cat): ?>
                                <?php if ($cat !== 'Jus'): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                                    
                </div>
                
                <!-- Tags de filtres actifs -->
                <div id="activeFilters" class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-200">
                    <!-- Les tags seront ajoutés dynamiquement par JavaScript -->
                </div>
                
                <!-- Compteur de résultats -->
                <div class="flex justify-end mt-4">
                    <div class="text-sm text-gray-600 flex items-center">
                        <span id="resultCount">0</span> produit(s) trouvé(s)
                    </div>
                </div>
            </div>

            <!-- Grille de produits -->
            <div id="productsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($filteredProducts as $p): ?>
                    <div class="product-card bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-lg transition-all overflow-hidden group" 
                         data-category="<?php echo htmlspecialchars($p['category']); ?>" 
                         data-price="<?php echo $p['price']; ?>" 
                         data-name="<?php echo htmlspecialchars(strtolower($p['name'])); ?>"
                         data-id="<?php echo $p['id']; ?>">
                        <div class="relative">
                            <div class="aspect-square overflow-hidden">
                                <img src="<?php echo getImageSrc($p['image']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" 
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" onerror="this.src='<?php echo asset('default-product.jpg'); ?>'">
                            </div>
                            <?php if (isset($p['stock']) && $p['stock'] > 0): ?>
                                <div class="absolute top-3 right-3 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-medium">
                                    En stock
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="px-2 py-1 bg-primary-100 text-primary-700 text-xs rounded-full font-medium"><?php echo htmlspecialchars($p['category']); ?></span>
                                <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full"><?php echo $p['weight']; ?></span>
                            </div>
                            <h3 class="font-heading text-lg font-semibold mb-2 line-clamp-2"><?php echo htmlspecialchars($p['name']); ?></h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?php echo htmlspecialchars($p['description']); ?></p>
                            <?php if ($p['ingredients']): ?>
                                <p class="text-xs text-gray-500 italic mb-3"><?php echo __('catalogue.ingredients'); ?> : <?php echo htmlspecialchars($p['ingredients']); ?></p>
                            <?php endif; ?>
                            <?php if (isset($p['origin']) && $p['origin']): ?>
                                <div class="flex items-center text-xs text-gray-500 mb-3">
                                    <i class="fas fa-map-marker-alt mr-1"></i>
                                    <?php echo htmlspecialchars($p['origin']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center justify-between border-t border-gray-100 px-5 py-4">
                            <div>
                                <span class="font-bold text-primary-600 text-lg"><?php echo formatPrice($p['price']); ?></span>
                                <?php if (isset($p['old_price']) && $p['old_price'] && $p['old_price'] > $p['price']): ?>
                                    <span class="text-xs text-gray-500 line-through ml-2"><?php echo formatPrice($p['old_price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="flex gap-2">
                            <a href="?page=produit&id=<?php echo $p['id']; ?>" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50 transition-colors">
                                <i class="fas fa-eye mr-1 text-xs"></i> Détails
                            </a>
                            <button class="add-to-cart-btn inline-flex items-center px-3 py-1.5 bg-primary-600 text-white text-sm rounded hover:bg-primary-700 transition-colors"
                                    data-product='<?php echo json_encode([
                                        'id' => $p['id'],
                                        'name' => $p['name'],
                                        'price' => $p['price'],
                                        'weight' => $p['weight'],
                                        'image' => getImageSrc($p['image']),
                                        'category' => $p['category']
                                    ]); ?>'
                                    data-quantity="1">
                                <i class="fas fa-cart-plus mr-1 text-xs"></i> Ajouter
                            </button>
                        </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Message si aucun résultat -->
            <div id="noResults" class="hidden text-center py-12">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                    <i class="fas fa-search text-gray-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Aucun produit trouvé</h3>
                <p class="text-gray-600 mb-4">Essayez de modifier vos filtres ou votre recherche</p>
                <button onclick="resetFilters()" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                    Réinitialiser les filtres
                </button>
            </div>
        </div>
    </section>

    </div>

    <style>
        .product-card {
            transition: all 0.3s ease;
        }
        
        .product-card.hidden {
            display: none;
        }
        
        .filter-tag {
            display: inline-flex items-center px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-sm;
        }
        
        .filter-tag .remove-tag {
            margin-left: 6px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }
        
        .filter-tag .remove-tag:hover {
            opacity: 1;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .product-card:not(.hidden) {
            animation: fadeIn 0.3s ease-out;
        }
    </style>

    <script>
        // Données des produits
        const products = <?php echo json_encode($products); ?>;
        const categories = <?php echo json_encode($categories); ?>;
        
        // État des filtres
        let filters = {
            search: '',
            category: 'all',
            maxPrice: '',
            sort: 'name-asc'
        };
        
        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Appliquer les filtres de l'URL
            applyUrlFilters();
            
            // Configurer les écouteurs d'événements
            setupEventListeners();
            
            // Appliquer les filtres initiaux
            applyFilters();
        });
        
        function applyUrlFilters() {
            const urlParams = new URLSearchParams(window.location.search);
            const category = urlParams.get('category');
            const search = urlParams.get('search');
            
            if (category) {
                filters.category = category;
                document.getElementById('categoryFilter').value = category;
            }
            
            if (search) {
                filters.search = search;
                document.getElementById('searchInput').value = search;
            }
        }
        
        function setupEventListeners() {
            // Recherche
            document.getElementById('searchInput').addEventListener('input', function(e) {
                filters.search = e.target.value.toLowerCase();
                updateUrl();
                applyFilters();
            });
            
            // Catégorie
            document.getElementById('categoryFilter').addEventListener('change', function(e) {
                filters.category = e.target.value;
                updateUrl();
                applyFilters();
            });
            
            // Prix
            document.getElementById('priceFilter').addEventListener('change', function(e) {
                filters.maxPrice = e.target.value;
                updateUrl();
                applyFilters();
            });
            
            // Tri
            document.getElementById('sortFilter').addEventListener('change', function(e) {
                filters.sort = e.target.value;
                applyFilters();
            });
        }
        
        function updateUrl() {
            const url = new URL(window.location);
            url.searchParams.delete('category');
            url.searchParams.delete('search');
            
            if (filters.category && filters.category !== 'all') {
                url.searchParams.set('category', filters.category);
            }
            
            if (filters.search) {
                url.searchParams.set('search', filters.search);
            }
            
            window.history.replaceState({}, '', url);
        }
        
        function applyFilters() {
            const productCards = document.querySelectorAll('.product-card');
            let visibleCount = 0;
            
            productCards.forEach(card => {
                let shouldShow = true;
                
                // Filtre de recherche
                if (filters.search) {
                    const name = card.dataset.name;
                    const category = card.dataset.category.toLowerCase();
                    if (!name.includes(filters.search) && !category.includes(filters.search)) {
                        shouldShow = false;
                    }
                }
                
                // Filtre de catégorie
                if (filters.category !== 'all') {
                    const category = card.dataset.category.toLowerCase();
                    const filterCategory = filters.category.toLowerCase();
                    if (!category.includes(filterCategory) && category !== filterCategory) {
                        shouldShow = false;
                    }
                }
                
                // Filtre de prix
                if (filters.maxPrice) {
                    const price = parseInt(card.dataset.price);
                    if (price > parseInt(filters.maxPrice)) {
                        shouldShow = false;
                    }
                }
                
                // Afficher ou masquer
                if (shouldShow) {
                    card.classList.remove('hidden');
                    visibleCount++;
                } else {
                    card.classList.add('hidden');
                }
            });
            
            // Trier les produits visibles
            sortProducts();
            
            // Mettre à jour le compteur
            document.getElementById('resultCount').textContent = visibleCount;
            
            // Afficher/masquer le message "aucun résultat"
            const noResults = document.getElementById('noResults');
            if (visibleCount === 0) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
            
            // Mettre à jour les tags de filtres actifs
            updateActiveFilters();
        }
        
        function sortProducts() {
            const grid = document.getElementById('productsGrid');
            const cards = Array.from(grid.querySelectorAll('.product-card:not(.hidden)'));
            
            cards.sort((a, b) => {
                const [field, order] = filters.sort.split('-');
                
                switch (field) {
                    case 'name':
                        const nameA = a.dataset.name;
                        const nameB = b.dataset.name;
                        return order === 'asc' ? nameA.localeCompare(nameB) : nameB.localeCompare(nameA);
                    
                    case 'price':
                        const priceA = parseInt(a.dataset.price);
                        const priceB = parseInt(b.dataset.price);
                        return order === 'asc' ? priceA - priceB : priceB - priceA;
                    
                    case 'category':
                        const catA = a.dataset.category;
                        const catB = b.dataset.category;
                        return catA.localeCompare(catB);
                    
                    default:
                        return 0;
                }
            });
            
            // Réorganiser la grille
            cards.forEach(card => grid.appendChild(card));
        }
        
        function updateActiveFilters() {
            const container = document.getElementById('activeFilters');
            container.innerHTML = '';
            
            // Tag de recherche
            if (filters.search) {
                addFilterTag('search', 'Recherche: ' + filters.search);
            }
            
            // Tag de catégorie
            if (filters.category !== 'all') {
                addFilterTag('category', 'Catégorie: ' + filters.category);
            }
            
            // Tag de prix
            if (filters.maxPrice) {
                addFilterTag('price', 'Max: ' + parseInt(filters.maxPrice).toLocaleString() + ' FCFA');
            }
        }
        
        function addFilterTag(type, label) {
            const container = document.getElementById('activeFilters');
            const tag = document.createElement('div');
            tag.className = 'filter-tag';
            tag.innerHTML = `
                ${label}
                <span class="remove-tag" onclick="removeFilter('${type}')">×</span>
            `;
            container.appendChild(tag);
        }
        
        function removeFilter(type) {
            switch (type) {
                case 'search':
                    filters.search = '';
                    document.getElementById('searchInput').value = '';
                    break;
                case 'category':
                    filters.category = 'all';
                    document.getElementById('categoryFilter').value = 'all';
                    break;
                case 'price':
                    filters.maxPrice = '';
                    document.getElementById('priceFilter').value = '';
                    break;
            }
            
            updateUrl();
            applyFilters();
        }
        
        function resetFilters() {
            filters = {
                search: '',
                category: 'all',
                maxPrice: '',
                sort: 'name-asc'
            };
            
            // Réinitialiser les champs
            document.getElementById('searchInput').value = '';
            document.getElementById('categoryFilter').value = 'all';
            document.getElementById('priceFilter').value = '';
            document.getElementById('sortFilter').value = 'name-asc';
            
            // Nettoyer l'URL
            const url = new URL(window.location);
            url.searchParams.delete('category');
            url.searchParams.delete('search');
            window.history.replaceState({}, '', url);
            
            applyFilters();
        }
    </script>
        </div>
    </section>

    </div>

