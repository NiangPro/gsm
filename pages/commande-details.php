<?php
// Page de détails de commande
require_once __DIR__ . '/../includes/functions.php';

// Démarrer la session pour gérer les erreurs
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer les données de la commande depuis la session
$orderData = [];
$error = '';

if (isset($_SESSION['order_success'])) {
    $orderData = $_SESSION['order_success'];
    unset($_SESSION['order_success']);
} else {
    // Rediriger vers le panier si aucune donnée de commande
    ?>
    <script>
        window.location.href = '?page=panier';
    </script>
    <div class="min-h-screen flex items-center justify-center bg-gray-50">
        <div class="text-center">
            <i class="fas fa-spinner fa-spin text-emerald-600 text-3xl mb-4"></i>
            <p class="text-gray-600">Redirection vers le panier...</p>
        </div>
    </div>
    <?php
    exit;
}
?>

<div class="container mx-auto px-4 max-w-4xl py-8">
    <!-- En-tête de succès -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
            <i class="fas fa-check-circle text-green-600 text-4xl"></i>
        </div>
        <h1 class="text-3xl md:text-4xl font-heading font-bold text-gray-900 mb-4">
            Commande confirmée !
        </h1>
        <p class="text-gray-600 max-w-2xl mx-auto">
            Merci pour votre commande. Nous vous contacterons bientôt pour la livraison.
        </p>
    </div>

    <!-- Carte de détails de commande -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold flex items-center">
                <i class="fas fa-receipt text-primary-600 mr-3"></i>
                Détails de la commande
            </h2>
            <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm font-medium">
                <?php echo htmlspecialchars($orderData['status']); ?>
            </span>
        </div>

        <!-- Numéro de commande -->
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Numéro de commande</span>
                <span class="font-mono font-bold text-lg"><?php echo htmlspecialchars($orderData['order_number']); ?></span>
            </div>
        </div>

        <!-- Informations client -->
        <div class="mb-6">
            <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-user text-primary-600 mr-2"></i>
                Informations de livraison
            </h3>
            <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Nom</span>
                    <span class="font-medium"><?php echo htmlspecialchars($orderData['customer_name']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Téléphone</span>
                    <span class="font-medium"><?php echo htmlspecialchars($orderData['customer_phone']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Adresse</span>
                    <span class="font-medium"><?php echo htmlspecialchars($orderData['delivery_address']); ?></span>
                </div>
                <?php if (!empty($orderData['delivery_notes'])): ?>
                <div class="flex justify-between">
                    <span class="text-gray-600">Notes</span>
                    <span class="font-medium"><?php echo htmlspecialchars($orderData['delivery_notes']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Articles commandés -->
        <div class="mb-6">
            <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-shopping-bag text-primary-600 mr-2"></i>
                Articles commandés
            </h3>
            <div class="space-y-3">
                <?php foreach ($orderData['items'] as $item): ?>
                <div class="flex items-center justify-between bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gray-200 rounded-lg flex items-center justify-center">
                            <i class="fas fa-box text-gray-500"></i>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($item['product_name']); ?></h4>
                            <p class="text-sm text-gray-600">Quantité: <?php echo $item['quantity']; ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-medium"><?php echo formatPrice($item['subtotal']); ?></p>
                        <p class="text-sm text-gray-600"><?php echo formatPrice($item['unit_price']); ?> × <?php echo $item['quantity']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Récapitulatif des paiements -->
        <div class="border-t pt-6">
            <h3 class="font-semibold text-gray-900 mb-3 flex items-center">
                <i class="fas fa-calculator text-primary-600 mr-2"></i>
                Récapitulatif
            </h3>
            <div class="space-y-2">
                <div class="flex justify-between">
                    <span class="text-gray-600">Sous-total</span>
                    <span><?php echo formatPrice($orderData['total_amount']); ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Frais de livraison</span>
                    <span>
                        <?php if ($orderData['shipping_cost'] == 0): ?>
                            <span class="text-green-600">Gratuite</span>
                        <?php else: ?>
                            <?php echo formatPrice($orderData['shipping_cost']); ?>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t">
                    <span>Total</span>
                    <span><?php echo formatPrice($orderData['final_amount']); ?></span>
                </div>
            </div>
        </div>

        <!-- Informations de paiement -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-600 mr-3"></i>
                <div>
                    <h4 class="font-medium text-blue-900">Paiement à la livraison</h4>
                    <p class="text-blue-700 text-sm mt-1">
                        Le paiement sera effectué lors de la livraison. Nous vous contacterons pour confirmer les détails.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="?page=catalogue" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors font-medium">
            <i class="fas fa-shopping-bag mr-2"></i>
            Continuer mes achats
        </a>
        <a href="?page=panier" class="inline-flex items-center px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-medium">
            <i class="fas fa-arrow-left mr-2"></i>
            Retour au panier
        </a>
    </div>
</div>
