<?php
// Page panier - Affichage et gestion du panier d'achats
require_once __DIR__ . '/../includes/functions.php';

// Démarrer la session pour gérer les erreurs
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer les erreurs de session
$error = '';
$errorDetails = '';
if (isset($_SESSION['checkout_error'])) {
    $error = $_SESSION['checkout_error'];
    unset($_SESSION['checkout_error']);
}
if (isset($_SESSION['checkout_error_details'])) {
    $errorDetails = $_SESSION['checkout_error_details'];
    unset($_SESSION['checkout_error_details']);
}
?>

<div class="container mx-auto px-4 max-w-7xl py-8">
    <!-- Messages d'erreur -->
    <?php if (!empty($error)): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                <div>
                    <h3 class="text-red-800 font-medium">Erreur de commande</h3>
                    <p class="text-red-600 text-sm mt-1"><?php echo htmlspecialchars($error); ?></p>
                    <?php if (!empty($errorDetails)): ?>
                        <details class="mt-2">
                            <summary class="text-red-500 text-xs cursor-pointer hover:text-red-700">Détails techniques</summary>
                            <pre class="text-red-400 text-xs mt-2 bg-red-900 p-2 rounded overflow-auto"><?php echo htmlspecialchars($errorDetails); ?></pre>
                        </details>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- En-tête de la page -->
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-heading font-bold text-gray-900 mb-4">
            <i class="fas fa-shopping-cart text-primary-600 mr-3"></i>
            Mon Panier
        </h1>
        <p class="text-gray-600 max-w-2xl mx-auto">
            Gérez vos articles et finalisez votre commande de produits artisanaux du Sénégal
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Articles du panier -->
        <div class="lg:col-span-2">
            <!-- Message panier vide -->
            <div id="cartEmpty" class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gray-100 rounded-full mb-6">
                    <i class="fas fa-shopping-cart text-gray-400 text-3xl"></i>
                </div>
                <h2 class="text-2xl font-semibold text-gray-900 mb-4">Votre panier est vide</h2>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    Découvrez nos délicieux produits artisanaux et ajoutez-les à votre panier
                </p>
                <a href="?page=catalogue" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium">
                    <i class="fas fa-search mr-2"></i>
                    Parcourir le catalogue
                </a>
            </div>

            <!-- Liste des articles -->
            <div id="cartItems" class="space-y-4">
                <!-- Les articles seront ajoutés dynamiquement par JavaScript -->
            </div>
        </div>

        <!-- Résumé de la commande -->
        <div class="lg:col-span-1">
            <div id="cartSummary" class="bg-white rounded-lg border border-gray-200 p-6 sticky top-24">
                <!-- Le résumé sera ajouté dynamiquement par JavaScript -->
            </div>
        </div>
    </div>

    <!-- Section produits recommandés -->
    <div class="mt-16">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-heading font-bold text-gray-900 mb-4">
                Vous pourriez aussi aimer
            </h2>
            <p class="text-gray-600">Découvrez d'autres produits de notre catalogue</p>
        </div>
        
        <div id="recommendedProducts" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Les produits recommandés seront ajoutés dynamiquement -->
        </div>
    </div>
</div>

<style>
    .cart-item {
        transition: all 0.3s ease;
    }
    
    .cart-item:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
    
    .quantity-btn {
        transition: all 0.2s ease;
    }
    
    .quantity-btn:hover:not(:disabled) {
        background-color: #f3f4f6;
        transform: scale(1.05);
    }
    
    .quantity-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .cart-item {
        animation: slideIn 0.3s ease-out;
    }
    
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1000;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    }
    
    .notification.show {
        transform: translateX(0);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Charger les produits recommandés
    loadRecommendedProducts();
    
    // Mettre à jour l'affichage du panier
    if (window.cartManager) {
        window.cartManager.updateCartPage();
    }
});

/**
 * Charger les produits recommandés
 */
function loadRecommendedProducts() {
    const recommendedProducts = [
        {
            id: 1,
            name: 'Confiture de Mangue',
            price: 2500,
            weight: '250g',
            image: 'confiture-mangue.jpg',
            category: 'Confitures'
        },
        {
            id: 2,
            name: 'Jus de Bissap',
            price: 1200,
            weight: '500ml',
            image: 'jus-bissap-JUS_BI02.jpg',
            category: 'Jus'
        },
        {
            id: 3,
            name: 'Confiture de Gingembre',
            price: 3000,
            weight: '250g',
            image: 'confiture-gingembre.jpg',
            category: 'Confitures'
        },
        {
            id: 4,
            name: 'Jus de Bouye',
            price: 1500,
            weight: '500ml',
            image: 'jus-bouye-lait-sans-sucre-djolof-15l.png',
            category: 'Jus'
        }
    ];
    
    const container = document.getElementById('recommendedProducts');
    if (!container) return;
    
    container.innerHTML = recommendedProducts.map(product => `
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-lg transition-all overflow-hidden group">
            <div class="aspect-square overflow-hidden bg-gray-100">
                <img src="${getImageSrc(product.image)}" alt="${product.name}" 
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                     onerror="this.src='<?php echo asset('default-product.jpg'); ?>'">
            </div>
            <div class="p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-1 bg-gray-100 text-gray-700 text-xs rounded-full">${product.category}</span>
                    <span class="px-2 py-1 border border-gray-200 text-gray-600 text-xs rounded-full">${product.weight}</span>
                </div>
                <h3 class="font-heading font-semibold text-gray-900 mb-2">${product.name}</h3>
                <div class="flex items-center justify-between">
                    <span class="font-bold text-primary-600">${formatPrice(product.price)}</span>
                    <button class="add-to-cart-btn px-3 py-1.5 bg-primary-600 text-white text-sm rounded hover:bg-primary-700 transition-colors"
                            data-product='${JSON.stringify(product)}'
                            data-quantity="1">
                        <i class="fas fa-cart-plus mr-1"></i>
                        Ajouter
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

/**
 * Obtenir le chemin correct de l'image
 */
function getImageSrc(imageUrl) {
    if (!imageUrl) return 'assets/images/default-product.jpg';

    // Si c'est une URL externe (commence par http:// ou https://)
    if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) {
        return imageUrl;
    }

    // Si le chemin commence déjà par 'assets/', le laisser tel quel
    if (imageUrl.startsWith('assets/')) {
        return imageUrl;
    }

    // Sinon, ajouter 'assets/' devant
    return 'assets/' + imageUrl;
}

/**
 * Formater le prix
 */
function formatPrice(price) {
    return new Intl.NumberFormat('fr-FR').format(price) + ' FCFA';
}

/**
 * Fonction filter_var simplifiée pour le JavaScript
 */
function filter_var(url, type) {
    if (type === 'FILTER_VALIDATE_URL') {
        try {
            new URL(url);
            return true;
        } catch {
            return false;
        }
    }
    return false;
}
</script>
