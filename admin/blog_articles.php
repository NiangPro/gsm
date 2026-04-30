<?php
$pdo = getDB();

// Articles de blog pour le GIE Sokhna Maï
$blogArticles = [
    [
        'title_fr' => 'Les Bienfaits du Moringa : Trésor de la Nature Sénégalaise',
        'title_en' => 'The Benefits of Moringa: Senegalese Nature\'s Treasure',
        'excerpt_fr' => 'Découvrez les vertus exceptionnelles du Moringa, cette plante miracle cultivée avec amour par les femmes du GIE Sokhna Maï.',
        'excerpt_en' => 'Discover the exceptional virtues of Moringa, this miracle plant lovingly cultivated by the women of GIE Sokhna Maï.',
        'content_fr' => '
            <h2>Le Moringa : Un Don de la Nature</h2>
            <p>Le Moringa oleifera, surnommé "l\'arbre de vie", pousse abondamment au Sénégal et représente une source inépuisable de bienfaits pour la santé. Les femmes du GIE Sokhna Maï ont fait de cette plante leur spécialité, la cultivant selon des méthodes traditionnelles respectueuses de l\'environnement.</p>
            
            <h3>Étape 1 : La Culture Biologique</h3>
            <p>Nos agricultrices sélectionnent les meilleures graines de Moringa et les plantent pendant la saison des pluies. Aucun pesticide ni engrais chimique n\'est utilisé. Le sol est enrichi naturellement avec du compost organique préparé sur place.</p>
            
            <h3>Étape 2 : La Récolte Manuelle</h3>
            <p>Les feuilles de Moringa sont récoltées à la main, tôt le matin, lorsqu\'elles sont fraîches et gorgées de nutriments. Cette méthode préserve toutes leurs propriétés thérapeutiques.</p>
            
            <h3>Étape 3 : Le Séchage Naturel</h3>
            <p>Les feuilles sont étalées sur des claies en bambou dans un endroit ombragé et aéré. Le séchage dure 3-4 jours, jusqu\'à obtenir une texture parfaitement cassante.</p>
            
            <h3>Étape 4 : Le Broyage Artisanal</h3>
            <p>Les feuilles séchées sont broyées finement à l\'aide de mortiers en bois traditionnels. Cette technique ancestrale préserve les enzymes et nutriments délicats.</p>
            
            <h3>Étape 5 : Le Conditionnement</h3>
            <p>La poudre de Moringa est conditionnée dans des sachets biodégradables, protégée de la lumière et de l\'humidité pour conserver toutes ses vertus.</p>
            
            <h2>Les Bienfaits Santé</h2>
            <ul>
                <li><strong>Antioxydant puissant</strong> : 7 fois plus de vitamine C que les oranges</li>
                <li><strong>Riche en protéines</strong> : Contient les 9 acides aminés essentiels</li>
                <li><strong>Source de fer</strong> : 3 fois plus que les épinards</li>
                <li><strong>Calcium végétal</strong> : 4 fois plus que le lait</li>
                <li><strong>Propriétés anti-inflammatoires</strong> : Soulage les douleurs articulaires</li>
            </ul>
            
            <h3>Comment Utiliser Notre Poudre de Moringa</h3>
            <p>Intégrez une cuillère à café de poudre de Moringa dans vos smoothies, yaourts, soupes ou simplement dans de l\'eau tiède. Consommez régulièrement pour bénéficier de tous ses bienfaits.</p>
        ',
        'content_en' => '
            <h2>Moringa: A Gift from Nature</h2>
            <p>Moringa oleifera, nicknamed "the tree of life," grows abundantly in Senegal and represents an inexhaustible source of health benefits. The women of GIE Sokhna Maï have made this plant their specialty, cultivating it using traditional, environmentally friendly methods.</p>
            
            <h3>Step 1: Organic Cultivation</h3>
            <p>Our female farmers select the best Moringa seeds and plant them during the rainy season. No pesticides or chemical fertilizers are used. The soil is naturally enriched with organic compost prepared on-site.</p>
            
            <h3>Step 2: Manual Harvest</h3>
            <p>Moringa leaves are harvested by hand, early in the morning, when they are fresh and full of nutrients. This method preserves all their therapeutic properties.</p>
            
            <h3>Step 3: Natural Drying</h3>
            <p>The leaves are spread on bamboo racks in a shaded, ventilated area. Drying takes 3-4 days until a perfectly brittle texture is achieved.</p>
            
            <h3>Step 4: Artisanal Grinding</h3>
            <p>Dried leaves are finely ground using traditional wooden mortars. This ancestral technique preserves delicate enzymes and nutrients.</p>
            
            <h3>Step 5: Packaging</h3>
            <p>Moringa powder is packaged in biodegradable bags, protected from light and moisture to preserve all its virtues.</p>
            
            <h2>Health Benefits</h2>
            <ul>
                <li><strong>Powerful antioxidant</strong>: 7 times more vitamin C than oranges</li>
                <li><strong>Rich in protein</strong>: Contains all 9 essential amino acids</li>
                <li><strong>Iron source</strong>: 3 times more than spinach</li>
                <li><strong>Plant calcium</strong>: 4 times more than milk</li>
                <li><strong>Anti-inflammatory properties</strong>: Relieves joint pain</li>
            </ul>
            
            <h3>How to Use Our Moringa Powder</h3>
            <p>Integrate one teaspoon of Moringa powder into your smoothies, yogurts, soups, or simply in warm water. Consume regularly to benefit from all its properties.</p>
        ',
        'category_id' => 1,
        'is_published' => true,
        'published_at' => '2024-01-15 10:00:00'
    ],
    [
        'title_fr' => 'L\'Art de la Confiture : Savoir-Faire Traditionnel des Femmes Sokhna Maï',
        'title_en' => 'The Art of Jam Making: Sokhna Maï Women\'s Traditional Know-How',
        'excerpt_fr' => 'Plongez dans l\'univers des confitures artisanales du GIE Sokhna Maï, où chaque pot raconte une histoire de tradition et de passion.',
        'excerpt_en' => 'Dive into the world of artisanal jams from GIE Sokhna Maï, where each jar tells a story of tradition and passion.',
        'content_fr' => '
            <h2>Un Héritage Culinaire Précieux</h2>
            <p>Depuis plus de trois décennies, les femmes du GIE Sokhna Maï perpétuent l\'art ancestral de la confiture. Leurs recettes, transmises de mère en fille, mettent en valeur les fruits tropicaux du Sénégal.</p>
            
            <h3>Étape 1 : La Sélection des Fruits</h3>
            <p>Nos confiturières parcourent les marchés locaux dès 4h du matin pour sélectionner les fruits les plus mûrs et parfumés. Mangues, baobab, gingembre, tamarin - chaque fruit est choisi avec le plus grand soin.</p>
            
            <h3>Étape 2 : La Préparation Méticuleuse</h3>
            <p>Les fruits sont lavés, épluchés et découpés à la main. Pour certaines recettes, comme la confiture de gingembre, les racines sont râpées finement pour en extraire toutes les saveurs.</p>
            
            <h3>Étape 3 : La Cuisson Lente</h3>
            <p>Dans de grandes marmites en cuivre, les fruits cuisent doucement pendant 2-3 heures. Le sucre de canne local est ajouté progressivement, et le mélange est remué constamment avec des spatules en bois.</p>
            
            <h3>Étape 4 : La Mise en Pot</h3>
            <p>Les confitures encore chaudes sont versées dans des pots stérilisés. Chaque pot est retourné pour créer une vide d\'air naturel, garantissant une conservation parfaite sans aucun conservateur.</p>
            
            <h3>Étape 5 : L\'Étiquetage Artisanal</h3>
            <p>Chaque pot reçoit une étiquette manuscrite indiquant la variété, la date de fabrication et le nom de l\'artisane qui l\'a préparé.</p>
            
            <h2>Nos Spécialités Uniques</h2>
            
            <h3>Confiture de Baobab</h3>
            <p>La pulpe de baobab, riche en vitamine C, est transformée en une confiture acidulée et sucrée. Parfaite sur du pain grillé ou pour accompagner le fromage blanc.</p>
            
            <h3>Confiture de Gingembre</h3>
            <p>Un mariage épicé et réchauffant, idéal pour stimuler le système immunitaire pendant l\'hivernage.</p>
            
            <h3>Confiture de Tamarin</h3>
            <p>Sweet and tangy, this jam captures the exotic flavor of tamarind, perfect for adding a tropical touch to your breakfast.</p>
            
            <h2>L\'Impact Social</h2>
            <p>Chaque pot de confiture vendu contribue directement à l\'autonomisation économique de 150 femmes du village. Les revenus permettent de scolariser leurs enfants et d\'améliorer les conditions de vie de leurs familles.</p>
        ',
        'content_en' => '
            <h2>A Precious Culinary Heritage</h2>
            <p>For over three decades, the women of GIE Sokhna Maï have perpetuated the ancestral art of jam making. Their recipes, passed from mother to daughter, showcase Senegal\'s tropical fruits.</p>
            
            <h3>Step 1: Fruit Selection</h3>
            <p>Our jam makers visit local markets from 4 AM to select the ripest, most fragrant fruits. Mangoes, baobab, ginger, tamarind - each fruit is chosen with the utmost care.</p>
            
            <h3>Step 2: Meticulous Preparation</h3>
            <p>Fruits are washed, peeled, and cut by hand. For some recipes, like ginger jam, roots are finely grated to extract all flavors.</p>
            
            <h3>Step 3: Slow Cooking</h3>
            <p>In large copper pots, fruits cook gently for 2-3 hours. Local cane sugar is added gradually, and the mixture is constantly stirred with wooden spatulas.</p>
            
            <h3>Step 4: Jarring</h3>
            <p>Hot jams are poured into sterilized jars. Each jar is inverted to create a natural vacuum, ensuring perfect preservation without any preservatives.</p>
            
            <h3>Step 5: Artisanal Labeling</h3>
            <p>Each jar receives a handwritten label indicating the variety, production date, and the name of the artisan who prepared it.</p>
            
            <h2>Our Unique Specialties</h2>
            
            <h3>Baobab Jam</h3>
            <p>Baobab pulp, rich in vitamin C, is transformed into a tangy, sweet jam. Perfect on toast or to accompany cottage cheese.</p>
            
            <h3>Ginger Jam</h3>
            <p>A spicy, warming blend, ideal for boosting the immune system during cold season.</p>
            
            <h3>Tamarind Jam</h3>
            <p>Sweet and tangy, this jam captures tamarind\'s exotic flavor, perfect for adding a tropical touch to breakfast.</p>
            
            <h2>Social Impact</h2>
            <p>Each jar of jam sold directly contributes to the economic empowerment of 150 village women. Income helps school their children and improve their families\' living conditions.</p>
        ',
        'category_id' => 2,
        'is_published' => true,
        'published_at' => '2024-01-12 14:30:00'
    ],
    [
        'title_fr' => 'Le Processus de Transformation : De la Récolte à Votre Table',
        'title_en' => 'The Transformation Process: From Harvest to Your Table',
        'excerpt_fr' => 'Suivez le voyage fascinant de nos produits, de la récolte des matières premières jusqu\'à leur arrivée dans votre cuisine.',
        'excerpt_en' => 'Follow the fascinating journey of our products, from raw material harvest to their arrival in your kitchen.',
        'content_fr' => '
            <h2>La Chaîne de Valeur du GIE Sokhna Maï</h2>
            <p>Chaque produit que nous créons représente un voyage complet, transformant des ingrédients bruts en délices culinaires tout en créant de la valeur pour notre communauté.</p>
            
            <h2>Phase 1 : Approvisionnement en Matières Premières</h2>
            
            <h3>Récolte Communautaire</h3>
            <p>Nos membres se rassemblent tôt le matin pour récolter les fruits, feuilles et racines. Cette activité collective renforce les liens sociaux et assure une qualité optimale.</p>
            
            <h3>Partenariats avec les Agriculteurs Locaux</h3>
            <p>Nous travaillons directement avec 50+ agriculteurs familiaux, garantissant des prix équitables et une traçabilité complète de nos matières premières.</p>
            
            <h2>Phase 2 : Transformation Artisanale</h2>
            
            <h3>Préparation en Atelier Collectif</h3>
            <p>Dans notre atelier central, 25 artisanes travaillent en coordination. Chaque étape est standardisée tout en préservant le caractère artisanal.</p>
            
            <h3>Contrôle Qualité Rigoureux</h3>
            <p>Nos responsables qualité vérifient chaque lot : pH, texture, goût, et aspect visuel. Seuls les produits répondant à nos critères stricts sont commercialisés.</p>
            
            <h2>Phase 3 : Conditionnement et Stockage</h2>
            
            <h3>Emballage Éco-responsable</h3>
            <p>Nous utilisons des emballages biodégradables et recyclables. Les pots en verre sont récupérés et réutilisés dans un programme de circularité.</p>
            
            <h3>Stockage Optimal</h3>
            <p>Notre entrepôt maintient des conditions idéales : température contrôlée (18-22°C) et taux d\'humidité optimal (45-55%).</p>
            
            <h2>Phase 4 : Commercialisation</h2>
            
            <h3>Circuits de Distribution</h3>
            <ul>
                <li><strong>Marchés locaux</strong> : Présence dans 5 marchés hebdomadaires</li>
                <li><strong>Boutiques spécialisées</strong> : Partenariat avec 15 épiceries fines</li>
                <li><strong>Vente en ligne</strong> : Commandes via WhatsApp et site web</li>
                <li><strong>Export</strong> : Début d\'export vers la France et les États-Unis</li>
            </ul>
            
            <h3>Service Client</h3>
            <p>Notre équipe commerciale, composée de jeunes diplômés du village, assure le suivi des commandes et la relation client.</p>
            
            <h2>Phase 5 : Impact et Développement</h2>
            
            <h3>Réinvestissement Communautaire</h3>
            <p>30% des bénéfices sont réinvestis dans des projets communautaires : école primaire, centre de santé, et programme d\'alphabétisation pour adultes.</p>
            
            <h3>Formation Continue</h3>
            <p>Nous organisons trimestriellement des formations sur les nouvelles techniques de conservation, l\'hygiène alimentaire, et la gestion d\'entreprise.</p>
            
            <h2>Notre Engagement Qualité</h2>
            <p>Chaque produit porte la garantie d\'avoir été créé avec amour, respect des traditions, et engagement pour un développement durable.</p>
        ',
        'content_en' => '
            <h2>GIE Sokhna Maï\'s Value Chain</h2>
            <p>Each product we create represents a complete journey, transforming raw ingredients into culinary delights while creating value for our community.</p>
            
            <h2>Phase 1: Raw Material Sourcing</h2>
            
            <h3>Community Harvest</h3>
            <p>Our members gather early in the morning to harvest fruits, leaves, and roots. This collective activity strengthens social bonds and ensures optimal quality.</p>
            
            <h3>Partnerships with Local Farmers</h3>
            <p>We work directly with 50+ family farmers, ensuring fair prices and complete traceability of our raw materials.</p>
            
            <h2>Phase 2: Artisanal Transformation</h2>
            
            <h3>Collective Workshop Preparation</h3>
            <p>In our central workshop, 25 artisans work in coordination. Each step is standardized while preserving the artisanal character.</p>
            
            <h3>Rigorous Quality Control</h3>
            <p>Our quality managers check each batch: pH, texture, taste, and visual appearance. Only products meeting our strict criteria are marketed.</p>
            
            <h2>Phase 3: Packaging and Storage</h2>
            
            <h3>Eco-responsible Packaging</h3>
            <p>We use biodegradable and recyclable packaging. Glass jars are collected and reused in a circularity program.</p>
            
            <h3>Optimal Storage</h3>
            <p>Our warehouse maintains ideal conditions: controlled temperature (18-22°C) and optimal humidity (45-55%).</p>
            
            <h2>Phase 4: Marketing</h2>
            
            <h3>Distribution Channels</h3>
            <ul>
                <li><strong>Local markets</strong>: Presence in 5 weekly markets</li>
                <li><strong>Specialty shops</strong>: Partnership with 15 fine grocery stores</li>
                <li><strong>Online sales</strong>: Orders via WhatsApp and website</li>
                <li><strong>Export</strong>: Beginning exports to France and the United States</li>
            </ul>
            
            <h3>Customer Service</h3>
            <p>Our sales team, composed of young graduates from the village, ensures order follow-up and customer relations.</p>
            
            <h2>Phase 5: Impact and Development</h2>
            
            <h3>Community Reinvestment</h3>
            <p>30% of profits are reinvested in community projects: primary school, health center, and adult literacy program.</p>
            
            <h3>Continuous Training</h3>
            <p>We organize quarterly trainings on new preservation techniques, food hygiene, and business management.</p>
            
            <h2>Our Quality Commitment</h2>
            <p>Each product carries the guarantee of having been created with love, respect for traditions, and commitment to sustainable development.</p>
        ',
        'category_id' => 6,
        'is_published' => true,
        'published_at' => '2024-01-10 09:00:00'
    ]
];

// Insérer les articles dans la base de données
if ($pdo) {
    try {
        $pdo->beginTransaction();
        
        foreach ($blogArticles as $article) {
            // Vérifier si l\'article existe déjà
            $stmt = $pdo->prepare("SELECT id FROM blog_posts WHERE title_fr = ?");
            $stmt->execute([$article['title_fr']]);
            $existing = $stmt->fetch();
            
            if (!$existing) {
                $stmt = $pdo->prepare("
                    INSERT INTO blog_posts (title_fr, title_en, excerpt_fr, excerpt_en, content_fr, content_en, 
                                           category_id, is_published, published_at, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmt->execute([
                    $article['title_fr'],
                    $article['title_en'],
                    $article['excerpt_fr'],
                    $article['excerpt_en'],
                    $article['content_fr'],
                    $article['content_en'],
                    $article['category_id'],
                    $article['is_published'],
                    $article['published_at']
                ]);
                echo "Article inséré: " . $article['title_fr'] . "<br>";
            } else {
                echo "Article déjà existant: " . $article['title_fr'] . "<br>";
            }
        }
        
        $pdo->commit();
        echo "<br><strong>Tous les articles de blog ont été traités avec succès!</strong>";
        
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo "Erreur lors de l\'insertion des articles: " . $e->getMessage();
    }
} else {
    echo "Base de données non disponible";
}
?>
