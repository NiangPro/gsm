<?php
$pdo = getDB();
$message = '';

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'add') {
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $titleEn = isset($_POST['title_en']) ? trim($_POST['title_en']) : '';
        $excerpt = isset($_POST['excerpt']) ? trim($_POST['excerpt']) : '';
        $excerptEn = isset($_POST['excerpt_en']) ? trim($_POST['excerpt_en']) : '';
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        $contentEn = isset($_POST['content_en']) ? trim($_POST['content_en']) : '';
        $categoryId = isset($_POST['category_id']) ? (int)$_POST['category_id'] : 1;
        $isPublished = isset($_POST['is_published']) ? (bool)$_POST['is_published'] : false;
        
        if ($title && $content) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO blog_posts (title_fr, title_en, excerpt_fr, excerpt_en, content_fr, content_en, 
                                     category_id, is_published, published_at, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$title, $titleEn, $excerpt, $excerptEn, $content, $contentEn, 
                               $categoryId, $isPublished ? date('Y-m-d H:i:s') : null, $isPublished]);
                $message = 'Article publié avec succès!';
            } catch (PDOException $e) {
                error_log("Error creating blog post: " . $e->getMessage());
                $message = 'Erreur lors de la création de l\\'article';
            }
        }
    } elseif ($action === 'edit') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        // Similar logic for edit...
    } elseif ($action === 'delete') {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id) {
            try {
                $stmt = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
                $stmt->execute([$id]);
                $message = 'Article supprimé avec succès!';
            } catch (PDOException $e) {
                error_log("Error deleting blog post: " . $e->getMessage());
                $message = 'Erreur lors de la suppression';
            }
        }
    }
}

// Récupérer les articles
$posts = [];
if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT bp.*, bc.name_fr as category_name 
            FROM blog_posts bp 
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
            ORDER BY bp.created_at DESC
        ");
        $stmt->execute();
        $posts = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching blog posts: " . $e->getMessage());
        // Articles par défaut si la BDD n'est pas encore configurée
        $posts = [
            [
                'id' => 1,
                'title_fr' => 'Les Bienfaits du Moringa : Trésor de la Nature Sénégalaise',
                'title_en' => 'The Benefits of Moringa: Senegalese Nature\'s Treasure',
                'excerpt_fr' => 'Découvrez les vertus exceptionnelles du Moringa, cette plante miracle cultivée avec amour par les femmes du GIE Sokhna Maï.',
                'excerpt_en' => 'Discover the exceptional virtues of Moringa, this miracle plant lovingly cultivated by the women of GIE Sokhna Maï.',
                'category_name' => 'Santé',
                'is_published' => true,
                'created_at' => '2024-01-15 10:00:00'
            ],
            [
                'id' => 2,
                'title_fr' => 'L\'Art de la Confiture : Savoir-Faire Traditionnel des Femmes Sokhna Maï',
                'title_en' => 'The Art of Jam Making: Sokhna Maï Women\'s Traditional Know-How',
                'excerpt_fr' => 'Plongez dans l\'univers des confitures artisanales du GIE Sokhna Maï, où chaque pot raconte une histoire de tradition et de passion.',
                'excerpt_en' => 'Dive into the world of artisanal jams from GIE Sokhna Maï, where each jar tells a story of tradition and passion.',
                'category_name' => 'Recettes',
                'is_published' => true,
                'created_at' => '2024-01-12 14:30:00'
            ],
            [
                'id' => 3,
                'title_fr' => 'Le Processus de Transformation : De la Récolte à Votre Table',
                'title_en' => 'The Transformation Process: From Harvest to Your Table',
                'excerpt_fr' => 'Suivez le voyage fascinant de nos produits, de la récolte des matières premières jusqu\'à leur arrivée dans votre cuisine.',
                'excerpt_en' => 'Follow the fascinating journey of our products, from raw material harvest to their arrival in your kitchen.',
                'category_name' => 'Savoir-faire',
                'is_published' => true,
                'created_at' => '2024-01-10 09:00:00'
            ]
        ];
    }
}
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <i class="fas fa-newspaper text-primary-600 text-2xl"></i>
            <h1 class="text-2xl font-heading font-bold">Gestion du Blog</h1>
        </div>
        <button onclick="openModal()" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-plus mr-2"></i> Nouvel article
        </button>
    </div>

    <?php if ($message): ?>
        <div class="bg-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-50 border border-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-200 text-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-700 px-4 py-3 rounded mb-4">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Titre</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Catégorie</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Extrait</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Statut</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Date</th>
                        <th class="text-right py-3 px-4 text-gray-600 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $post): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($post['title_fr']); ?></div>
                                <div class="text-xs text-gray-500 mt-1 italic"><?php echo htmlspecialchars($post['title_en']); ?></div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">
                                    <?php echo htmlspecialchars($post['category_name'] ?? 'Non catégorisé'); ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="max-w-xs text-sm text-gray-600 truncate" title="<?php echo htmlspecialchars($post['excerpt_fr']); ?>">
                                    <?php echo htmlspecialchars(substr($post['excerpt_fr'], 0, 80)) . (strlen($post['excerpt_fr']) > 80 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full <?php echo $post['is_published'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700'; ?>">
                                    <?php echo $post['is_published'] ? 'Publié' : 'Brouillon'; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 text-xs text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($post['created_at'])); ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="viewPost(<?php echo $post['id']; ?>)" class="p-1 text-gray-400 hover:text-gray-600" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button onclick="editPost(<?php echo $post['id']; ?>)" class="p-1 text-blue-600 hover:text-blue-700" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="deletePost(<?php echo $post['id']; ?>)" class="p-1 text-red-600 hover:text-red-700" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-8 text-gray-500">Aucun article trouvé</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour ajouter/modifier un article -->
<div id="blogModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-heading font-bold">Nouvel article</h2>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Titre (FR) *</label>
                        <input type="text" name="title" required 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Titre (EN)</label>
                        <input type="text" name="title_en" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Extrait (FR)</label>
                        <textarea name="excerpt" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Extrait (EN)</label>
                        <textarea name="excerpt_en" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Catégorie</label>
                    <select name="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <option value="1">Santé</option>
                        <option value="2">Recettes</option>
                        <option value="3">Événements</option>
                        <option value="4">Savoir-faire</option>
                        <option value="5">Conseils</option>
                        <option value="6">Portraits</option>
                    </select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Contenu (FR) *</label>
                        <textarea name="content" required rows="8" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Contenu (EN)</label>
                        <textarea name="content_en" rows="8" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_published" class="mr-2">
                        <span class="text-sm font-medium">Publié immédiatement</span>
                    </label>
                </div>
                <div class="flex gap-2 justify-end pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">Publier</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('blogModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeModal() {
    document.getElementById('blogModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fermer le modal en cliquant à l'extérieur
document.getElementById('blogModal').addEventListener('click', function(e) {
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

function viewPost(id) {
    const posts = <?php echo json_encode($posts); ?>;
    const post = posts.find(p => p.id == id);
    
    if (post) {
        // Créer un modal pour afficher l'article complet
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
        modal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-heading font-bold">${post.title_fr}</h2>
                        <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="mb-4">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">${post.category_name || 'Non catégorisé'}</span>
                        <span class="px-2 py-1 text-xs rounded-full ml-2 ${post.is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-700'}">
                            ${post.is_published ? 'Publié' : 'Brouillon'}
                        </span>
                    </div>
                    <div class="prose max-w-none">
                        <p class="text-gray-600 italic mb-4">${post.excerpt_fr}</p>
                        <div class="text-gray-700">
                            ${post.content_fr ? post.content_fr.substring(0, 500) + '...' : 'Contenu non disponible'}
                        </div>
                    </div>
                    <div class="mt-6 pt-4 border-t text-sm text-gray-500">
                        Créé le: ${new Date(post.created_at).toLocaleDateString('fr-FR')}
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }
}

function editPost(id) {
    // Pour l'instant, rediriger vers le formulaire d'édition
    alert('Fonction d\'édition à implémenter pour l\'article ID: ' + id);
}

function deletePost(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cet article ?')) {
        // Implémenter la suppression
        alert('Fonction de suppression à implémenter pour l\'article ID: ' + id);
    }
}
</script>
</div>
