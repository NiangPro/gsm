<?php
// Vérifier si le client est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ?page=connexion');
    exit;
}

$success = '';
$error = '';

$pdo = getDB();
$customer = null;
$orders = [];

if ($pdo) {
    try {
        // Récupérer les infos du client
        $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $customer = $stmt->fetch();
        
        // Récupérer les commandes du client
        $stmt = $pdo->prepare("
            SELECT o.*, 
                   (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as items_count
            FROM orders o 
            WHERE o.customer_id = ? 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        $orders = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching customer data: " . $e->getMessage());
    }
}

// Traitement de la mise à jour du profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    
    if (empty($name) || empty($phone)) {
        $error = 'Le nom et le téléphone sont obligatoires.';
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE customers SET name = ?, phone = ?, address = ?, city = ? WHERE id = ?");
            $stmt->execute([$name, $phone, $address, $city, $_SESSION['user_id']]);
            
            // Mettre à jour la session
            $_SESSION['user_name'] = $name;
            
            $success = 'Votre profil a été mis à jour avec succès.';
            
            // Recharger les données
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $customer = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Une erreur est survenue lors de la mise à jour.';
        }
    }
}

// Traitement du changement de mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'Le nouveau mot de passe doit contenir au moins 6 caractères.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'Les mots de passe ne correspondent pas.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT password_hash FROM customers WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($currentPassword, $user['password_hash'])) {
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE customers SET password_hash = ? WHERE id = ?");
                $stmt->execute([$newHash, $_SESSION['user_id']]);
                $success = 'Votre mot de passe a été modifié avec succès.';
            } else {
                $error = 'Le mot de passe actuel est incorrect.';
            }
        } catch (PDOException $e) {
            $error = 'Une erreur est survenue.';
        }
    }
}

// Fonction pour formater le statut
function getStatusBadge($status) {
    $badges = [
        'En attente' => 'bg-yellow-100 text-yellow-800',
        'Confirmée' => 'bg-blue-100 text-blue-800',
        'En préparation' => 'bg-purple-100 text-purple-800',
        'En livraison' => 'bg-indigo-100 text-indigo-800',
        'Livrée' => 'bg-green-100 text-green-800',
        'Annulée' => 'bg-red-100 text-red-800'
    ];
    return $badges[$status] ?? 'bg-gray-100 text-gray-800';
}
?>

<div class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Mon Espace Client</h1>
            <p class="mt-2 text-gray-600">Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?> !</p>
        </div>

        <?php if ($success): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center">
                            <i class="fas fa-user text-emerald-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($customer['name'] ?? $_SESSION['user_name']); ?></h3>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($customer['email'] ?? $_SESSION['user_email']); ?></p>
                        </div>
                    </div>
                    <nav class="space-y-2">
                        <a href="#profile" onclick="showSection('profile')" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-emerald-50 text-emerald-700" id="nav-profile">
                            <i class="fas fa-user-circle w-5 mr-2"></i> Mon Profil
                        </a>
                        <a href="#orders" onclick="showSection('orders')" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50" id="nav-orders">
                            <i class="fas fa-shopping-bag w-5 mr-2"></i> Mes Commandes
                        </a>
                        <a href="#security" onclick="showSection('security')" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50" id="nav-security">
                            <i class="fas fa-lock w-5 mr-2"></i> Sécurité
                        </a>
                        <a href="?page=deconnexion" class="flex items-center px-4 py-2 text-sm font-medium rounded-lg text-red-600 hover:bg-red-50">
                            <i class="fas fa-sign-out-alt w-5 mr-2"></i> Déconnexion
                        </a>
                    </nav>
                </div>

                <!-- Résumé -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Résumé</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Commandes</span>
                            <span class="font-semibold text-gray-900"><?php echo count($orders); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">En cours</span>
                            <span class="font-semibold text-blue-600"><?php echo count(array_filter($orders, fn($o) => !in_array($o['status'], ['Livrée', 'Annulée']))); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Livrées</span>
                            <span class="font-semibold text-green-600"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'Livrée')); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Section Profil -->
                <div id="section-profile" class="bg-white rounded-xl shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Informations personnelles</h2>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_profile">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($customer['name'] ?? ''); ?>" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($customer['phone'] ?? ''); ?>" required
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" value="<?php echo htmlspecialchars($customer['email'] ?? ''); ?>" disabled
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-100 text-gray-500">
                                <p class="text-xs text-gray-500 mt-1">L'email ne peut pas être modifié.</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                                <input type="text" name="city" value="<?php echo htmlspecialchars($customer['city'] ?? ''); ?>"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                            <textarea name="address" rows="3"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500"><?php echo htmlspecialchars($customer['address'] ?? ''); ?></textarea>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                <i class="fas fa-save mr-2"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Section Commandes -->
                <div id="section-orders" class="bg-white rounded-xl shadow-sm p-6 hidden">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Mes commandes</h2>
                    <?php if (empty($orders)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-shopping-bag text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">Vous n'avez pas encore passé de commande.</p>
                            <a href="?page=catalogue" class="mt-4 inline-block px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700">
                                Découvrir nos produits
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="space-y-4">
                            <?php foreach ($orders as $order): ?>
                                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex flex-wrap justify-between items-start mb-2">
                                        <div>
                                            <h3 class="font-semibold text-gray-900">Commande #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                                            <p class="text-sm text-gray-500"><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                                        </div>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium <?php echo getStatusBadge($order['status']); ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-gray-600"><?php echo $order['items_count']; ?> article(s)</span>
                                        <span class="font-bold text-lg"><?php echo number_format($order['final_amount'], 0, ',', ' '); ?> FCFA</span>
                                    </div>
                                    <?php if ($order['status'] === 'En attente'): ?>
                                        <div class="mt-3 pt-3 border-t border-gray-100">
                                            <p class="text-xs text-gray-500">Votre commande est en attente de confirmation.</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Section Sécurité -->
                <div id="section-security" class="bg-white rounded-xl shadow-sm p-6 hidden">
                    <h2 class="text-xl font-semibold text-gray-900 mb-6">Changer le mot de passe</h2>
                    <form method="POST" class="space-y-4 max-w-md">
                        <input type="hidden" name="action" value="change_password">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe actuel</label>
                            <input type="password" name="current_password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                            <input type="password" name="new_password" required minlength="6"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <p class="text-xs text-gray-500 mt-1">Minimum 6 caractères</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le nouveau mot de passe</label>
                            <input type="password" name="confirm_password" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        </div>
                        <div class="flex justify-end">
                            <button type="submit" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 transition-colors">
                                <i class="fas fa-key mr-2"></i> Changer le mot de passe
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showSection(section) {
    // Masquer toutes les sections
    document.getElementById('section-profile').classList.add('hidden');
    document.getElementById('section-orders').classList.add('hidden');
    document.getElementById('section-security').classList.add('hidden');
    
    // Réinitialiser tous les liens
    document.getElementById('nav-profile').className = 'flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50';
    document.getElementById('nav-orders').className = 'flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50';
    document.getElementById('nav-security').className = 'flex items-center px-4 py-2 text-sm font-medium rounded-lg text-gray-700 hover:bg-gray-50';
    
    // Afficher la section sélectionnée
    document.getElementById('section-' + section).classList.remove('hidden');
    
    // Activer le lien correspondant
    document.getElementById('nav-' + section).className = 'flex items-center px-4 py-2 text-sm font-medium rounded-lg bg-emerald-50 text-emerald-700';
    
    // Sauvegarder la section dans l'URL
    if (history.pushState) {
        history.pushState(null, null, '#'+section);
    }
}

// Gérer le hash dans l'URL au chargement
window.addEventListener('load', function() {
    const hash = window.location.hash.substring(1);
    if (hash && ['profile', 'orders', 'security'].includes(hash)) {
        showSection(hash);
    }
});
</script>
