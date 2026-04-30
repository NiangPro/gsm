<?php
$productKeys = ['bouye', 'bissap', 'mangue', 'gingembre', 'ditakh', 'moringa'];
$emojis = ['', '', '', '', '', ''];
$valueKeys = ['natural', 'love', 'global', 'empower'];
$valueIcons = ['fa-leaf', 'fa-heart', 'fa-globe', 'fa-users'];

// Styles CSS pour les cards avec images en arrière-plan
?>
<style>
.featured-product-card {
    min-height: 320px;
    aspect-ratio: 1;
}

.featured-product-card .absolute.inset-0.bg-cover {
    transition: transform 0.7s ease;
}

.featured-product-card:hover .absolute.inset-0.bg-cover {
    transform: scale(1.1);
}

.featured-product-card .absolute.inset-0.bg-gradient-to-t {
    background: linear-gradient(to top, 
        rgba(0, 0, 0, 0.8) 0%, 
        rgba(0, 0, 0, 0.4) 50%, 
        rgba(0, 0, 0, 0.1) 100%);
}

.featured-product-card .relative.z-10 {
    transition: transform 0.3s ease;
}

.featured-product-card:hover .relative.z-10 {
    transform: translateY(-2px);
}

.featured-product-card .bg-white\/20 {
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.featured-product-card .bg-white\/20:hover {
    background: rgba(255, 255, 255, 0.3);
}

.featured-product-card .drop-shadow-lg {
    filter: drop-shadow(2px 4px 6px rgba(0, 0, 0, 0.3));
}

.featured-product-card .text-white\/90 {
    color: rgba(255, 255, 255, 0.9);
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .featured-product-card {
        min-height: 280px;
        aspect-ratio: 4/3;
    }
    
    .featured-product-card .relative.z-10 {
        padding: 1rem;
    }
    
    .featured-product-card h3 {
        font-size: 1.125rem;
    }
    
    .featured-product-card p {
        font-size: 0.875rem;
    }
}

@media (max-width: 480px) {
    .featured-product-card {
        min-height: 240px;
        aspect-ratio: 5/4;
    }
    
    .featured-product-card .relative.z-10 {
        padding: 0.75rem;
    }
    
    .featured-product-card h3 {
        font-size: 1rem;
    }
    
    .featured-product-card p {
        font-size: 0.8125rem;
        line-height: 1.4;
    }
    
    .featured-product-card .bg-white\/20 {
        padding: 0.5rem 1rem;
        font-size: 0.8125rem;
    }
}

/* Animation d'entrée */
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

.featured-product-card {
    animation: fadeInUp 0.6s ease-out;
    animation-fill-mode: both;
}

.featured-product-card:nth-child(1) { animation-delay: 0.1s; }
.featured-product-card:nth-child(2) { animation-delay: 0.2s; }
.featured-product-card:nth-child(3) { animation-delay: 0.3s; }
.featured-product-card:nth-child(4) { animation-delay: 0.4s; }
.featured-product-card:nth-child(5) { animation-delay: 0.5s; }
.featured-product-card:nth-child(6) { animation-delay: 0.6s; }
</style>

<?php

// Récupérer les données dynamiques depuis la base de données
$latestProducts = [];
$featuredCategories = [];
$testimonials = [];
$blogPosts = [];
$pdo = getDB();

if ($pdo) {
    try {
        // Récupérer les 8 derniers produits avec catégories
        $stmt = $pdo->prepare("SELECT p.id, p.name, p.description_fr, p.price, p.weight, p.image_url, 
                                     COALESCE(c.name_fr, 'Produits') as category_name
                                     FROM products p 
                                     LEFT JOIN categories c ON p.category_id = c.id 
                                     WHERE p.is_available = 1 
                                     ORDER BY p.created_at DESC LIMIT 8");
        $stmt->execute();
        $latestProducts = $stmt->fetchAll();
        
        // Récupérer les catégories mises en avant
        $stmt = $pdo->prepare("SELECT id, name_fr, description_fr, image_url FROM categories WHERE is_featured = 1 ORDER BY sort_order ASC LIMIT 6");
        $stmt->execute();
        $featuredCategories = $stmt->fetchAll();
        
        // Récupérer les témoignages approuvés
        $stmt = $pdo->prepare("SELECT name, content, rating, created_at FROM testimonials WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 3");
        $stmt->execute();
        $testimonials = $stmt->fetchAll();
        
        // Récupérer les derniers articles de blog
        $stmt = $pdo->prepare("SELECT id, title_fr, excerpt_fr, image_url, created_at FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 3");
        $stmt->execute();
        $blogPosts = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error fetching dynamic data: " . $e->getMessage());
    }
}

// Fallback si BDD indisponible
if (empty($latestProducts)) {
    $latestProducts = [
        ['id' => 1, 'name' => 'Confiture de Mangue', 'description_fr' => 'Confiture artisanale préparée avec des mangues fraîches du Sénégal.', 'price' => 2500, 'weight' => '250g', 'image_url' => 'confiture-mangue.jpg'],
        ['id' => 2, 'name' => 'Confiture de Gingembre', 'description_fr' => 'Une confiture épicée au gingembre frais.', 'price' => 3000, 'weight' => '250g', 'image_url' => 'confiture-gingembre.jpg'],
        ['id' => 3, 'name' => 'Jus de Bissap', 'description_fr' => 'Jus rafraîchissant préparé à partir des fleurs d\'hibiscus.', 'price' => 1500, 'weight' => '1L', 'image_url' => 'jus-bissap.jpg'],
        ['id' => 4, 'name' => 'Jus de Bouye', 'description_fr' => 'Jus de baobab riche en vitamine C.', 'price' => 1800, 'weight' => '1L', 'image_url' => 'jus-bouye.jpg'],
        ['id' => 5, 'name' => 'Poudre de Moringa', 'description_fr' => 'Poudre de feuilles de moringa séchées, riche en nutriments.', 'price' => 4000, 'weight' => '200g', 'image_url' => 'poudre-moringa.jpg'],
        ['id' => 6, 'name' => 'Sirop de Tamarin', 'description_fr' => 'Sirop sucré et acidulé à base de tamarin.', 'price' => 3500, 'weight' => '500ml', 'image_url' => 'sirop-tamarin.jpg'],
        ['id' => 7, 'name' => 'Miel du Sahel', 'description_fr' => 'Miel naturel récolté dans les régions du Sahel.', 'price' => 6000, 'weight' => '500g', 'image_url' => 'miel-sahel.jpg'],
        ['id' => 8, 'name' => 'Confiture Mixte Tropicale', 'description_fr' => 'Mélange exotique de mangue, ananas et fruit de la passion.', 'price' => 2800, 'weight' => '250g', 'image_url' => 'confiture-mix-tropicale.jpg'],
    ];
}

function formatPrice($price) {
    return number_format($price, 0, ',', ' ') . ' FCFA';
}
?>

<div>
    <section class="relative overflow-hidden">
        <div class="absolute inset-0">
            <img src="assets/images/home-hero-bg.jpg" alt="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/60"></div>
        </div>
        <div class="container mx-auto px-4 max-w-7xl py-20 md:py-32 relative z-10">
            <div class="max-w-3xl mx-auto text-center">
                <img src="<?php echo asset('logo.jpg'); ?>" alt="Logo GIE Sokhna Maï" 
                     class="w-32 h-32 md:w-40 md:h-40 rounded-full object-cover mx-auto mb-6 shadow-xl animate-fade-in border-4 border-white/30">
                <span class="inline-block px-4 py-1.5 rounded-full bg-white/10 text-white text-sm font-medium mb-6 animate-fade-in">
                    <?php echo __('hero.badge'); ?>
                </span>
                <h1 class="text-4xl md:text-6xl font-heading font-bold text-white leading-tight mb-6 animate-fade-in delay-100">
                    <?php echo __('hero.title'); ?>
                </h1>
                <p class="text-lg md:text-xl text-white/80 mb-8 animate-fade-in delay-200">
                    <?php echo __('hero.subtitle'); ?>
                </p>
                <div class="flex flex-wrap justify-center gap-4 animate-fade-in delay-300">
                    <a href="?page=a-propos" class="inline-flex items-center px-6 py-3 bg-accent-500 text-white rounded-lg font-medium hover:bg-accent-600 transition-colors">
                        <?php echo __('hero.ourStory'); ?>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-heading font-bold mb-4"><?php echo __('home.featuredTitle'); ?></h2>
                <p class="text-gray-600 max-w-2xl mx-auto"><?php echo __('home.featuredSubtitle'); ?></p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (!empty($featuredCategories)): ?>
                    <?php foreach ($featuredCategories as $index => $category): ?>
                        <div class="featured-product-card relative overflow-hidden rounded-lg shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 bg-white border border-gray-200">
                            <!-- Contenu -->
                            <div class="p-6 h-full flex flex-col justify-center text-gray-900">
                                <div class="mb-4">
                                    <?php if ($category['image_url']): ?>
                                        <div class="w-16 h-16 mx-auto bg-primary-100 rounded-full flex items-center justify-center">
                                            <img src="<?php echo filter_var($category['image_url'], FILTER_VALIDATE_URL) ? $category['image_url'] : asset($category['image_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($category['name_fr']); ?>" 
                                                 class="w-10 h-10 rounded-full object-cover">
                                        </div>
                                    <?php else: ?>
                                        <div class="w-16 h-16 mx-auto bg-primary-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-box text-primary-600 text-xl"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <h3 class="text-xl font-semibold mb-2 text-gray-900"><?php echo htmlspecialchars($category['name_fr']); ?></h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2"><?php echo htmlspecialchars($category['description_fr'] ?? ''); ?></p>
                                <a href="?page=catalogue&category=<?php echo $category['id']; ?>" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors text-sm font-medium">
                                    <?php echo __('home.viewCategory'); ?>
                                    <i class="fas fa-arrow-right ml-2"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback avec les catégories par défaut -->
                    <?php foreach ($productKeys as $i => $key): ?>
                        <?php 
                        $backgroundImages = [
                            'https://images.unsplash.com/photo-1606986531732-632d6c128272?w=800&h=600&fit=crop', // Bouye
                            'https://images.unsplash.com/photo-1577234286642-fc512a5f8f11?w=800&h=600&fit=crop', // Bissap
                            'https://images.unsplash.com/photo-1553279768-865429fa0078?w=800&h=600&fit=crop', // Mangue
                            'https://images.unsplash.com/photo-1589226097032-a99c1b4620b8?w=800&h=600&fit=crop', // Gingembre
                            'https://images.unsplash.com/photo-1576675466969-38eeae4b41f6?w=800&h=600&fit=crop', // Ditakh
                            'https://images.unsplash.com/photo-1543076499-a6133cb561c2?w=800&h=600&fit=crop'  // Moringa
                        ];
                        $bgImage = $backgroundImages[$i] ?? $backgroundImages[0];
                        ?>
                        <div class="featured-product-card relative overflow-hidden rounded-lg shadow-sm hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 bg-white border border-gray-200">
                            <!-- Contenu -->
                            <div class="p-6 h-full flex flex-col justify-center text-gray-900">
                                <div class="mb-4">
                                    <div class="w-16 h-16 mx-auto bg-primary-100 rounded-full flex items-center justify-center">
                                        <span class="text-3xl"><?php echo $emojis[$i]; ?></span>
                                    </div>
                                </div>
                                <h3 class="font-heading text-xl font-bold mb-2 text-gray-900"><?php echo __('home.products.' . $key . '.name'); ?></h3>
                                <p class="text-sm text-gray-600"><?php echo __('home.products.' . $key . '.desc'); ?></p>
                                
                                <!-- Bouton d'action -->
                                <div class="mt-4">
                                    <a href="?page=catalogue" 
                                       class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors text-sm font-medium">
                                        Découvrir <i class="fas fa-arrow-right ml-2 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
                    </div>
    </section>

    <!-- Section des 8 derniers produits -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-heading font-bold mb-4">Nos Derniers Produits</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Découvrez nos dernières créations artisanales, préparées avec amour par les femmes du GIE Sokhna Maï</p>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
                <?php foreach ($latestProducts as $product): ?>
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-lg transition-all overflow-hidden group">
                        <div class="aspect-square overflow-hidden bg-gray-100">
                            <?php 
$imageUrl = $product['image_url'] ?? 'default-product.jpg';
$imageSrc = filter_var($imageUrl, FILTER_VALIDATE_URL) ? $imageUrl : asset($imageUrl);
?>
<img src="<?php echo $imageSrc; ?>" 
     alt="<?php echo htmlspecialchars($product['name']); ?>" 
     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" 
     onerror="this.src='<?php echo asset('default-product.jpg'); ?>'">
                        </div>
                        <div class="p-5">
                            <h3 class="font-heading text-lg font-semibold mb-2"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2"><?php echo htmlspecialchars(substr($product['description_fr'], 0, 80)) . (strlen($product['description_fr']) > 80 ? '...' : ''); ?></p>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs text-gray-500"><?php echo htmlspecialchars($product['weight']); ?></span>
                                <span class="font-bold text-primary-600"><?php echo formatPrice($product['price']); ?></span>
                            </div>
                            <div class="flex gap-2">
                            <a href="?page=produit&id=<?php echo $product['id']; ?>" class="flex-1 block text-center px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50 transition-colors">
                                Détails
                            </a>
                            <button class="add-to-cart-btn flex-1 px-3 py-2 bg-primary-600 text-white text-sm rounded hover:bg-primary-700 transition-colors"
                                    data-product='<?php echo json_encode([
                                        'id' => $product['id'],
                                        'name' => $product['name'],
                                        'price' => $product['price'],
                                        'weight' => $product['weight'],
                                        'image' => $product['image_url'] ?? 'default-product.jpg',
                                        'category' => 'Produits'
                                    ]); ?>'
                                    data-quantity="1">
                                <i class="fas fa-cart-plus text-xs"></i>
                            </button>
                        </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center">
                <a href="?page=catalogue" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors">
                    Voir tous les produits <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Section À propos - Notre histoire -->
    <section class="py-20 bg-gradient-to-br from-primary-50 to-gray-100">
        <div class="container mx-auto px-4 max-w-7xl text-center max-w-3xl">
            <h2 class="text-3xl md:text-4xl font-heading font-bold mb-6">Notre Histoire</h2>
            <p class="text-lg text-gray-600 mb-8">Découvrez l'histoire du GIE Sokhna Maï et notre engagement pour les produits artisanaux du Sénégal</p>
        </div>
    </section>

    <section class="py-20">
        <div class="container mx-auto px-4 max-w-7xl max-w-4xl">
            <div class="prose prose-lg mx-auto text-center">
                <h3 class="font-heading text-3xl font-bold mb-6">Notre Engagement</h3>
                <p class="text-gray-600 leading-relaxed mb-4">Le GIE Sokhna Maï est né de la volonté d'un groupe de femmes de transformer leur savoir-faire traditionnel en une activité économique durable.</p>
                <p class="text-gray-600 leading-relaxed">Aujourd'hui, nous sommes fières de proposer des produits authentiques qui reflètent la richesse de notre terroir et soutiennent l'émancipation économique des femmes sénégalaises.</p>
            </div>
        </div>
    </section>

    <!-- Section jalons -->
    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-heading font-bold mb-4">Nos Valeurs</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Les principes qui guident notre action quotidienne</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-users text-xl"></i>
                    </div>
                    <h3 class="font-heading text-lg font-semibold mb-2">Identité</h3>
                    <p class="text-sm text-gray-600">Valorisation des savoir-faire traditionnels et de l'identité culturelle sénégalaise</p>
                </div>
                <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-bullseye text-xl"></i>
                    </div>
                    <h3 class="font-heading text-lg font-semibold mb-2">Mission</h3>
                    <p class="text-sm text-gray-600">Promouvoir l'autonomie économique des femmes à travers des produits artisanaux de qualité</p>
                </div>
                <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-heart text-xl"></i>
                    </div>
                    <h3 class="font-heading text-lg font-semibold mb-2">Valeurs</h3>
                    <p class="text-sm text-gray-600">Authenticité, qualité et engagement social au cœur de notre démarche</p>
                </div>
                <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200 text-center">
                    <div class="inline-flex items-center justify-center w-14 h-14 rounded-full bg-primary-100 text-primary-600 mb-4">
                        <i class="fas fa-award text-xl"></i>
                    </div>
                    <h3 class="font-heading text-lg font-semibold mb-2">Ambition</h3>
                    <p class="text-sm text-gray-600">Devenir une référence de l'artisanat sénégalais et créer des opportunités durables</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Section témoignages -->
    <?php if (!empty($testimonials)): ?>
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-heading font-bold mb-4">Ce que disent nos clients</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Découvrez les témoignages de nos clients satisfaits</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <?php foreach ($testimonials as $testimonial): ?>
                    <div class="bg-gray-50 rounded-lg p-6 text-center">
                        <div class="flex justify-center mb-4">
                            <?php for ($i = 0; $i < 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i < $testimonial['rating'] ? 'text-yellow-400' : 'text-gray-300'; ?> text-sm"></i>
                            <?php endfor; ?>
                        </div>
                        <blockquote class="text-gray-700 italic mb-4">"<?php echo htmlspecialchars($testimonial['content']); ?>"</blockquote>
                        <cite class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($testimonial['name']); ?></cite>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Section blog/actualités -->
    <?php if (!empty($blogPosts)): ?>
    <section class="py-20 bg-white">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-heading font-bold mb-4">Actualités & Blog</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">Découvrez nos dernières actualités et conseils sur les produits du terroir sénégalais</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                <?php foreach ($blogPosts as $post): ?>
                    <article class="bg-gray-50 rounded-lg overflow-hidden hover:shadow-lg transition-shadow">
                        <?php if ($post['image_url']): ?>
                            <div class="aspect-video overflow-hidden">
                                <img src="<?php echo filter_var($post['image_url'], FILTER_VALIDATE_URL) ? $post['image_url'] : asset($post['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($post['title_fr']); ?>" 
                                     class="w-full h-full object-cover">
                            </div>
                        <?php endif; ?>
                        <div class="p-6">
                            <time class="text-sm text-gray-500 mb-2 block"><?php echo date('d/m/Y', strtotime($post['created_at'])); ?></time>
                            <h3 class="font-heading text-lg font-semibold mb-2"><?php echo htmlspecialchars($post['title_fr']); ?></h3>
                            <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($post['excerpt_fr']); ?></p>
                            <a href="?page=blog&id=<?php echo $post['id']; ?>" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                                Lire la suite <i class="fas fa-arrow-right ml-1 text-xs"></i>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="?page=blog" class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Voir tous les articles <i class="fas fa-arrow-right ml-2 text-sm"></i>
                </a>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="py-16 bg-primary-600 text-white">
        <div class="container mx-auto px-4 max-w-7xl text-center">
            <h2 class="text-3xl md:text-4xl font-heading font-bold mb-4"><?php echo __('home.ctaTitle'); ?></h2>
            <p class="text-lg opacity-90 mb-8 max-w-xl mx-auto"><?php echo __('home.ctaSubtitle'); ?></p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="?page=catalogue" class="inline-flex items-center px-6 py-3 bg-accent-500 text-white rounded-lg font-medium hover:bg-accent-600 transition-colors">
                    <?php echo __('home.orderNow'); ?>
                </a>
                <a href="?page=contact" class="inline-flex items-center px-6 py-3 bg-accent-500 text-white rounded-lg font-medium hover:bg-accent-600 transition-colors">
                    <?php echo __('home.contactUs'); ?>
                </a>
            </div>
        </div>
    </section>
</div>
