<?php
$pdo = getDB();
$articles = [];

// Récupérer les articles depuis la base de données
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT bp.*, bc.name_fr as category_name, bc.name_en as category_name_en
            FROM blog_posts bp 
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
            WHERE bp.is_published = TRUE
            ORDER BY bp.published_at DESC
        ");
        $stmt->execute();
        $dbArticles = $stmt->fetchAll();
        
        foreach ($dbArticles as $article) {
            $articles[] = [
                'id' => $article['id'],
                'title' => $article['title_fr'],
                'titleEn' => $article['title_en'],
                'excerpt' => $article['excerpt_fr'],
                'excerptEn' => $article['excerpt_en'],
                'category' => $article['category_name'] ?? 'Non catégorisé',
                'categoryEn' => $article['category_name_en'] ?? 'Uncategorized',
                'date' => date('d F Y', strtotime($article['published_at'])),
                'dateEn' => date('F d, Y', strtotime($article['published_at'])),
                'emoji' => getArticleEmoji($article['category_id']),
                'content' => $article['content_fr'],
                'contentEn' => $article['content_en']
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching blog posts: " . $e->getMessage());
    }
}

// Articles par défaut si la BDD n'est pas disponible
if (empty($articles)) {
    $articles = [
        [
            'id' => 1, 
            'title' => 'Les Bienfaits du Moringa : Trésor de la Nature Sénégalaise', 
            'titleEn' => 'The Benefits of Moringa: Senegalese Nature\'s Treasure', 
            'excerpt' => 'Découvrez les vertus exceptionnelles du Moringa, cette plante miracle cultivée avec amour par les femmes du GIE Sokhna Maï.', 
            'excerptEn' => 'Discover the exceptional virtues of Moringa, this miracle plant lovingly cultivated by the women of GIE Sokhna Maï.', 
            'category' => 'Santé', 
            'categoryEn' => 'Health', 
            'date' => '15 Janvier 2024', 
            'dateEn' => 'January 15, 2024', 
            'emoji' => ''
        ],
        [
            'id' => 2, 
            'title' => 'L\'Art de la Confiture : Savoir-Faire Traditionnel des Femmes Sokhna Maï', 
            'titleEn' => 'The Art of Jam Making: Sokhna Maï Women\'s Traditional Know-How', 
            'excerpt' => 'Plongez dans l\'univers des confitures artisanales du GIE Sokhna Maï, où chaque pot raconte une histoire de tradition et de passion.', 
            'excerptEn' => 'Dive into the world of artisanal jams from GIE Sokhna Maï, where each jar tells a story of tradition and passion.', 
            'category' => 'Recettes', 
            'categoryEn' => 'Recipes', 
            'date' => '12 Janvier 2024', 
            'dateEn' => 'January 12, 2024', 
            'emoji' => ''
        ],
        [
            'id' => 3, 
            'title' => 'Le Processus de Transformation : De la Récolte à Votre Table', 
            'titleEn' => 'The Transformation Process: From Harvest to Your Table', 
            'excerpt' => 'Suivez le voyage fascinant de nos produits, de la récolte des matières premières jusqu\'à leur arrivée dans votre cuisine.', 
            'excerptEn' => 'Follow the fascinating journey of our products, from raw material harvest to their arrival in your kitchen.', 
            'category' => 'Savoir-faire', 
            'categoryEn' => 'Know-how', 
            'date' => '10 Janvier 2024', 
            'dateEn' => 'January 10, 2024', 
            'emoji' => ''
        ]
    ];
}

function getArticleEmoji($categoryId) {
    $emojis = [
        1 => '', // Santé
        2 => '', // Recettes  
        3 => '', // Événements
        4 => '', // Savoir-faire
        5 => '', // Conseils
        6 => ''  // Portraits
    ];
    return $emojis[$categoryId] ?? '';
}

$isEn = $lang === 'en';
?>

<div>
    <section class="bg-gradient-to-br from-primary-50 to-gray-100 py-20">
        <div class="container mx-auto px-4 max-w-7xl text-center max-w-3xl">
            <h1 class="text-4xl md:text-5xl font-heading font-bold mb-6"><?php echo __('blog.title'); ?></h1>
            <p class="text-lg text-gray-600"><?php echo __('blog.subtitle'); ?></p>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4 max-w-7xl max-w-5xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <?php foreach ($articles as $a): ?>
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-lg transition-shadow cursor-pointer group">
                        <div class="p-6">
                            <div class="flex items-start gap-4">
                                <span class="text-4xl shrink-0"><?php echo $a['emoji']; ?></span>
                                <div>
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded"><?php echo $isEn ? $a['categoryEn'] : $a['category']; ?></span>
                                        <span class="text-xs text-gray-500 flex items-center gap-1">
                                            <i class="far fa-calendar text-xs"></i> <?php echo $isEn ? $a['dateEn'] : $a['date']; ?>
                                        </span>
                                    </div>
                                    <h3 class="font-heading text-lg font-semibold mb-2 group-hover:text-primary-600 transition-colors"><?php echo $isEn ? $a['titleEn'] : $a['title']; ?></h3>
                                    <p class="text-sm text-gray-600"><?php echo $isEn ? $a['excerptEn'] : $a['excerpt']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
