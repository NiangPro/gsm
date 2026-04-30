<?php
// Récupérer les produits depuis la base de données
$products = [];
$pdo = getDB();
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, name, price, weight, is_available FROM products WHERE is_available = 1 ORDER BY name");
        $stmt->execute();
        $dbProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($dbProducts as $p) {
            $products[] = [
                'id' => $p['id'],
                'name' => $p['name'],
                'price' => $p['price'],
                'weight' => $p['weight']
            ];
        }
    } catch (PDOException $e) {
        error_log("Error fetching products: " . $e->getMessage());
        // Fallback en cas d'erreur
        $products = [];
    }
}

$steps = [
    ['icon' => 'fa-shopping-cart', 'key' => 'step1'],
    ['icon' => 'fa-comment-dots', 'key' => 'step2'],
    ['icon' => 'fa-truck', 'key' => 'step3'],
]; 

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $productId = isset($_POST['product']) ? (int)$_POST['product'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';

    if (empty($name) || empty($phone) || empty($productId) || empty($quantity)) {
        $error = __('contact.errorRequired');
    } else {
        // Sauvegarder la commande en base de données
        $pdo = getDB();
        if ($pdo) {
            try {
                $pdo->beginTransaction();
                
                // Récupérer les informations du produit
                $stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE id = ? AND is_available = 1");
                $stmt->execute([$productId]);
                $product = $stmt->fetch();
                
                if (!$product) {
                    throw new Exception("Produit non disponible");
                }
                
                // Vérifier si le client existe déjà
                $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
                $stmt->execute([$phone]);
                $customer = $stmt->fetch();
                
                if (!$customer) {
                    // Créer le client
                    $stmt = $pdo->prepare("INSERT INTO customers (name, phone, address, notes) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$name, $phone, $address, $notes]);
                    $customerId = $pdo->lastInsertId();
                } else {
                    $customerId = $customer['id'];
                    // Mettre à jour les infos du client si nécessaire
                    $stmt = $pdo->prepare("UPDATE customers SET name = ?, address = ?, notes = ? WHERE id = ?");
                    $stmt->execute([$name, $address, $notes, $customerId]);
                }
                
                // Générer un numéro de commande unique
                $orderNumber = 'CMD-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
                
                $productPrice = $product['price'];
                $totalAmount = $productPrice * $quantity;
                
                // Créer la commande
                $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, total_amount, delivery_address, delivery_notes, status) VALUES (?, ?, ?, ?, ?, 'En attente')");
                $stmt->execute([$orderNumber, $customerId, $totalAmount, $address, $notes]);
                $orderId = $pdo->lastInsertId();
                
                // Ajouter l'article de commande
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal, notes) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$orderId, $productId, $quantity, $productPrice, $totalAmount, $notes]);
                
                $pdo->commit();
                
                // Message de succès pour le client
                $successMessage = urlencode("Merci pour votre commande! Votre numéro de commande est: $orderNumber\n\nProduit: {$product['name']}\nQuantité: $quantity\nTotal: $totalAmount FCFA\n\nNous avons bien reçu votre demande et vous contacterons rapidement pour la confirmation.\n\nGIE Sokhna Maï");
                $whatsappUrl = "https://wa.me/221774460474?text=$successMessage";
                echo '<script>window.location.href = "' . $whatsappUrl . '";</script>';
                echo '<div class="min-h-screen flex items-center justify-center bg-gray-50"><div class="text-center"><i class="fas fa-spinner fa-spin text-emerald-600 text-3xl mb-4"></i><p class="text-gray-600">Redirection vers WhatsApp...</p></div></div>';
                exit;
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Error saving order: " . $e->getMessage());
                $error = "Une erreur est survenue lors de l'enregistrement de votre commande. Veuillez réessayer.";
            } catch (Exception $e) {
                error_log("Error: " . $e->getMessage());
                $error = $e->getMessage();
            }
        } else {
            // Fallback vers l'ancien système si la BDD n'est pas disponible
            $message = urlencode("Bonjour GIE Sokhna Maï!\n\nJe souhaite commander:\nNom: $name\nTéléphone: $phone\nAdresse: $address\nNotes: $notes");
            $whatsappUrl = "https://wa.me/221774460474?text=$message";
            echo '<script>window.location.href = "' . $whatsappUrl . '";</script>';
            echo '<div class="min-h-screen flex items-center justify-center bg-gray-50"><div class="text-center"><i class="fas fa-spinner fa-spin text-emerald-600 text-3xl mb-4"></i><p class="text-gray-600">Redirection vers WhatsApp...</p></div></div>';
            exit;
        }
    }
}
?>

<div>
    <section class="bg-gradient-to-br from-primary-50 to-gray-100 py-20">
        <div class="container mx-auto px-4 max-w-7xl text-center max-w-3xl">
            <h1 class="text-4xl md:text-5xl font-heading font-bold mb-6"><?php echo __('shop.title'); ?></h1>
            <p class="text-lg text-gray-600"><?php echo __('shop.subtitle'); ?></p>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4 max-w-7xl max-w-5xl">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16">
                <?php foreach ($steps as $step): ?>
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm text-center p-6">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 text-primary-600 mb-4">
                            <i class="fas <?php echo $step['icon']; ?> text-lg"></i>
                        </div>
                        <h3 class="font-heading font-semibold mb-2"><?php echo __('shop.' . $step['key'] . '.title'); ?></h3>
                        <p class="text-sm text-gray-600"><?php echo __('shop.' . $step['key'] . '.desc'); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-lg border border-gray-200 shadow-sm max-w-2xl mx-auto">
                <div class="p-8">
                    <h2 class="font-heading text-2xl font-bold mb-6 text-center"><?php echo __('shop.formTitle'); ?></h2>
                    
                    <?php if ($error): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4"><?php echo $error; ?></div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium"><?php echo __('shop.fullName'); ?> *</label>
                                <input type="text" name="name" required maxlength="100" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                       placeholder="<?php echo __('shop.fullName'); ?>">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium"><?php echo __('shop.phone'); ?> *</label>
                                <input type="tel" name="phone" required maxlength="20" 
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                       placeholder="+221 XX XXX XX XX">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium"><?php echo __('shop.product'); ?> *</label>
                                <select name="product" required 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value=""><?php echo __('shop.chooseProd'); ?></option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['name']); ?> — <?php echo number_format($p['price'], 0, ',', ' '); ?> FCFA</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium"><?php echo __('shop.quantity'); ?> *</label>
                                <input type="number" name="quantity" required min="1" max="100" value="1"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium"><?php echo __('shop.address'); ?></label>
                            <input type="text" name="address" maxlength="200"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                   placeholder="<?php echo __('shop.address'); ?>">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium"><?php echo __('shop.notes'); ?></label>
                            <textarea name="notes" rows="3" maxlength="500"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                      placeholder="<?php echo __('shop.notes'); ?>"></textarea>
                        </div>
                        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                            <i class="fab fa-whatsapp mr-2"></i> <?php echo __('shop.sendWhatsApp'); ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="text-center mt-10">
                <p class="text-gray-600 mb-4"><?php echo __('shop.orContact'); ?></p>
                <a href="https://wa.me/221774460474" target="_blank" rel="noopener noreferrer" 
                   class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    <i class="fas fa-phone mr-2"></i> <?php echo __('shop.callWhatsApp'); ?>
                </a>
            </div>
        </div>
    </section>
</div>
