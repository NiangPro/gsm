<?php
// Page checkout - Formulaire de commande uniquement
require_once __DIR__ . '/../includes/functions.php';

// Démarrer la session pour gérer les erreurs (si pas déjà démarrée)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer les données du panier depuis localStorage (JavaScript)
$cartData = [];
$totalAmount = 0;

// Récupérer les données du panier depuis JavaScript
if (isset($_POST['cart_data']) && !empty($_POST['cart_data'])) {
    $cartDataJson = $_POST['cart_data'];
    $cartData = json_decode($cartDataJson, true) ?: [];
    $totalAmount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
} else {
    // En GET, récupérer depuis le localStorage via JavaScript
    // Les données seront récupérées côté client
    $cartData = [];
    $totalAmount = 0;
}

// Récupérer les erreurs de session
$error = '';
if (isset($_SESSION['checkout_error'])) {
    $error = $_SESSION['checkout_error'];
    unset($_SESSION['checkout_error']);
}

// Récupérer les données précédemment soumises
$submittedData = [];
if (isset($_SESSION['checkout_data'])) {
    $submittedData = $_SESSION['checkout_data'];
    unset($_SESSION['checkout_data']);
}

// Si le client est connecté, récupérer ses informations pour pré-remplir le formulaire
if (isset($_SESSION['user_id']) && empty($submittedData)) {
    $pdo = getDB();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT name, phone, address, city FROM customers WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $customer = $stmt->fetch();
            if ($customer) {
                $submittedData = [
                    'name' => $customer['name'],
                    'phone' => $customer['phone'],
                    'address' => $customer['address'],
                    'city' => $customer['city']
                ];
            }
        } catch (PDOException $e) {
            error_log("Error fetching customer data: " . $e->getMessage());
        }
    }
}

// Rediriger si le panier est vide et pas d'erreur
if (empty($cartData) && $totalAmount <= 0 && empty($error)) {
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

// Calcul des frais de livraison
$shippingCost = $totalAmount > 5000 ? 0 : 1500;
$finalTotal = $totalAmount + $shippingCost;

// Vérifier s'il y a une erreur de session à afficher
$hasError = !empty($error);
$hasErrorDetails = isset($_SESSION['checkout_error_details']);
$errorDetails = '';
if ($hasErrorDetails) {
    $errorDetails = $_SESSION['checkout_error_details'];
    unset($_SESSION['checkout_error_details']);
}
?>
    <!-- En-tête -->
    <div class="text-center mb-8">
        <h1 class="text-3xl md:text-4xl font-heading font-bold text-gray-900 mb-4">
            <i class="fas fa-shopping-bag text-primary-600 mr-3"></i>
            Finaliser ma commande
        </h1>
        <p class="text-gray-600 max-w-2xl mx-auto">
            Remplissez vos informations pour finaliser votre commande de produits artisanaux
        </p>
    </div>

    <!-- Message d'erreur -->
    <?php if ($hasError): ?>
        <div class="bg-red-50 border border-red-200 text-red-700 px-6 py-4 rounded-lg mb-8 max-w-2xl mx-auto">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-3"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Formulaire de commande -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6">
                <h2 class="text-xl font-semibold mb-6 flex items-center">
                    <i class="fas fa-user text-primary-600 mr-3"></i>
                    Informations de livraison
                </h2>

                <form method="POST" action="checkout-process.php" class="space-y-6">
                    <!-- Données du panier cachées -->
                    <input type="hidden" name="cart_data" value="<?php echo htmlspecialchars(json_encode($cartData)); ?>">
                    <input type="hidden" name="total_amount" value="<?php echo $totalAmount; ?>">
                    <input type="hidden" name="clear_cart" value="true">

                    <!-- Informations personnelles -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                Nom complet <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="name" name="name" required
                                   value="<?php echo isset($submittedData['name']) ? htmlspecialchars($submittedData['name']) : ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="Votre nom complet">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                Téléphone <span class="text-red-500">*</span>
                            </label>
                            <input type="tel" id="phone" name="phone" required
                                   value="<?php echo isset($submittedData['phone']) ? htmlspecialchars($submittedData['phone']) : ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   placeholder="+221 XX XXX XX XX">
                            <p class="text-xs text-gray-500 mt-1">Format: +221 77 123 45 67</p>
                        </div>
                    </div>

                    <!-- Adresse de livraison -->
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            Adresse de livraison <span class="text-red-500">*</span>
                        </label>
                        <textarea id="address" name="address" required rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                  placeholder="Votre adresse complète pour la livraison..."><?php echo isset($submittedData['address']) ? htmlspecialchars($submittedData['address']) : ''; ?></textarea>
                    </div>

                    <!-- Notes supplémentaires -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            Instructions supplémentaires
                        </label>
                        <textarea id="notes" name="notes" rows="3"
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                  placeholder="Précisions pour la livraison, préférences, etc..."><?php echo isset($submittedData['notes']) ? htmlspecialchars($submittedData['notes']) : ''; ?></textarea>
                    </div>

                    <!-- Bouton de soumission -->
                    <div class="border-t pt-6">
                        <button type="submit" 
                                class="w-full bg-primary-600 text-white py-4 rounded-lg hover:bg-primary-700 transition-colors font-medium text-lg flex items-center justify-center">
                            <i class="fas fa-shopping-cart mr-3"></i>
                            Confirmer ma commande
                        </button>
                        <p class="text-sm text-gray-500 text-center mt-3">
                            <i class="fas fa-shield-alt mr-1"></i>
                            Paiement sécurisé à la livraison
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Résumé de la commande -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-6 sticky top-24">
                <h2 class="text-xl font-semibold mb-6 flex items-center">
                    <i class="fas fa-receipt text-primary-600 mr-3"></i>
                    Résumé de la commande
                </h2>

                <!-- Articles du panier -->
                <div class="space-y-4 mb-6">
                    <?php foreach ($cartData as $item): ?>
                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900"><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($item['category'] ?? 'Produit'); ?> • <?php echo htmlspecialchars($item['weight'] ?? ''); ?></p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium"><?php echo number_format($item['price'], 0, ',', ' '); ?> FCFA</p>
                                <p class="text-sm text-gray-600">× <?php echo $item['quantity']; ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Calcul des coûts -->
                <div class="space-y-3 border-t pt-4">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Sous-total</span>
                        <span class="font-medium"><?php echo number_format($totalAmount, 0, ',', ' '); ?> FCFA</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Livraison</span>
                        <span class="font-medium">
                            <?php if ($shippingCost === 0): ?>
                                <span class="text-green-600">Gratuite</span>
                            <?php else: ?>
                                <?php echo number_format($shippingCost, 0, ',', ' '); ?> FCFA
                            <?php endif; ?>
                        </span>
                    </div>
                    <?php if ($shippingCost === 0): ?>
                        <p class="text-sm text-green-600 bg-green-50 px-3 py-2 rounded-lg">
                            <i class="fas fa-truck mr-1"></i>
                            Livraison offerte!
                        </p>
                    <?php else: ?>
                        <p class="text-sm text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Livraison gratuite à partir de 5 000 FCFA
                        </p>
                    <?php endif; ?>
                    <div class="border-t pt-3">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span class="text-primary-600"><?php echo number_format($finalTotal, 0, ',', ' '); ?> FCFA</span>
                        </div>
                    </div>
                </div>

                <!-- Informations de livraison -->
                <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                    <h3 class="font-medium text-gray-900 mb-2">
                        <i class="fas fa-info-circle text-primary-600 mr-2"></i>
                        Informations de livraison
                    </h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Livraison dans tout le Sénégal</li>
                        <li>• Délai: 24-48h après confirmation</li>
                        <li>• Paiement à la livraison</li>
                        <li>• Suivi par WhatsApp</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .form-input:focus {
        border-color: #16a34a;
        box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .fade-in {
        animation: fadeIn 0.6s ease-out;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validation du formulaire
    const form = document.querySelector('form');
    const phoneInput = document.getElementById('phone');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            // Validation du téléphone
            const phone = phoneInput.value.trim();
            const phoneRegex = /^[+]?[0-9\s\-\(\)]{8,20}$/;
            
            if (!phoneRegex.test(phone)) {
                e.preventDefault();
                alert('Veuillez entrer un numéro de téléphone valide.');
                phoneInput.focus();
                return false;
            }
        });
    }
    
    // Animation d'entrée
    document.querySelectorAll('.fade-in').forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
    });
});
</script>
