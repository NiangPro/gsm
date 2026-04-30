<?php
// Page de confirmation de commande
require_once __DIR__ . '/../includes/functions.php';

// Récupérer les informations de la commande depuis les paramètres
$orderNumber = isset($_GET['order']) ? $_GET['order'] : '';
$total = isset($_GET['total']) ? (float)$_GET['total'] : 0;

// Si pas de numéro de commande, rediriger vers l'accueil
if (empty($orderNumber)) {
    header('Location: ?');
    exit;
}
?>

<div class="container mx-auto px-4 max-w-4xl py-8">
    <!-- Message de succès -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
            <i class="fas fa-check-circle text-green-600 text-4xl"></i>
        </div>
        <h1 class="text-3xl md:text-4xl font-heading font-bold text-gray-900 mb-4">
            Commande confirmée !
        </h1>
        <p class="text-lg text-gray-600 max-w-2xl mx-auto">
            Merci pour votre commande. Nous avons bien reçu votre demande et nous vous contacterons rapidement pour la confirmation et la livraison.
        </p>
    </div>

    <!-- Carte de confirmation -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-8 mb-8">
        <div class="text-center mb-6">
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">Détails de votre commande</h2>
            <div class="inline-flex items-center px-4 py-2 bg-primary-100 text-primary-700 rounded-full">
                <i class="fas fa-receipt mr-2"></i>
                <span class="font-medium">Numéro: <?php echo htmlspecialchars($orderNumber); ?></span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-truck text-primary-600 mr-2"></i>
                    Livraison
                </h3>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li class="flex items-center">
                        <i class="fas fa-check text-green-500 mr-2"></i>
                        Livraison dans tout le Sénégal
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-clock text-blue-500 mr-2"></i>
                        Délai: 24-48h après confirmation
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-money-bill-wave text-green-500 mr-2"></i>
                        Paiement à la livraison
                    </li>
                </ul>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-phone text-primary-600 mr-2"></i>
                    Contact
                </h3>
                <ul class="text-sm text-gray-600 space-y-2">
                    <li class="flex items-center">
                        <i class="fab fa-whatsapp text-green-500 mr-2"></i>
                        Suivi par WhatsApp
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-mobile-alt text-blue-500 mr-2"></i>
                        +221 77 808 45 77
                    </li>
                    <li class="flex items-center">
                        <i class="fas fa-envelope text-purple-500 mr-2"></i>
                        Confirmation par téléphone
                    </li>
                </ul>
            </div>
        </div>

        <?php if ($total > 0): ?>
        <div class="border-t pt-6">
            <div class="flex justify-between items-center">
                <span class="text-lg font-medium text-gray-700">Total de votre commande</span>
                <span class="text-2xl font-bold text-primary-600"><?php echo number_format($total, 0, ',', ' '); ?> FCFA</span>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Actions suivantes -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-shopping-bag text-primary-600 mr-2"></i>
                Continuer vos achats
            </h3>
            <p class="text-gray-600 mb-4">
                Découvrez d'autres délicieux produits artisanaux du Sénégal
            </p>
            <a href="?page=catalogue" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                <i class="fas fa-search mr-2"></i>
                Voir le catalogue
            </a>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
            <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-headset text-primary-600 mr-2"></i>
                Besoin d'aide ?
            </h3>
            <p class="text-gray-600 mb-4">
                Notre équipe est à votre disposition pour toute question
            </p>
            <div class="flex gap-3">
                <a href="tel:+221778084577" class="inline-flex items-center px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                    <i class="fas fa-phone mr-1"></i>
                    Appeler
                </a>
                <a href="https://wa.me/221778084577" target="_blank" class="inline-flex items-center px-3 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                    <i class="fab fa-whatsapp mr-1"></i>
                    WhatsApp
                </a>
            </div>
        </div>
    </div>

    <!-- Informations supplémentaires -->
    <div class="mt-8 text-center">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 max-w-2xl mx-auto">
            <h3 class="font-semibold text-blue-900 mb-2">
                <i class="fas fa-info-circle mr-2"></i>
                Prochaines étapes
            </h3>
            <ol class="text-sm text-blue-800 space-y-2 text-left">
                <li class="flex items-start">
                    <span class="font-semibold mr-2">1.</span>
                    <span>Nous vous contacterons par téléphone dans les plus brefs délais pour confirmer votre commande</span>
                </li>
                <li class="flex items-start">
                    <span class="font-semibold mr-2">2.</span>
                    <span>Nous validerons la disponibilité des produits et les détails de livraison</span>
                </li>
                <li class="flex items-start">
                    <span class="font-semibold mr-2">3.</span>
                    <span>Votre commande sera préparée et expédiée dans les 24-48h</span>
                </li>
                <li class="flex items-start">
                    <span class="font-semibold mr-2">4.</span>
                    <span>Paiement sécurisé à la réception de votre colis</span>
                </li>
            </ol>
        </div>
    </div>
</div>

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
        animation: fadeIn 0.6s ease-out;
    }
    
    .success-icon {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animation d'entrée
    document.querySelectorAll('.fade-in').forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
    });
    
    // Effacer le panier après confirmation
    if (window.cartManager) {
        window.cartManager.clearCartAfterOrder();
    }
    
    // Afficher une notification de succès
    setTimeout(() => {
        if (window.cartManager) {
            window.cartManager.showNotification('Commande enregistrée avec succès!', 'success');
        }
    }, 1000);
});
</script>
