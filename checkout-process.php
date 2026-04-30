<?php
// Traitement PHP pur pour checkout - sans aucun HTML
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

/**
 * Créer les tables nécessaires si elles n'existent pas
 */
function createTablesIfNeeded($pdo) {
    try {
        // Table customers
        $sql = "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            phone VARCHAR(20) UNIQUE NOT NULL,
            address TEXT,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        
        // Table orders
        $sql = "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_number VARCHAR(50) UNIQUE NOT NULL,
            customer_id INT NOT NULL,
            total_amount DECIMAL(10,2) NOT NULL,
            shipping_cost DECIMAL(10,2) DEFAULT 0,
            final_amount DECIMAL(10,2) NOT NULL,
            delivery_address TEXT NOT NULL,
            delivery_notes TEXT,
            status ENUM('En attente', 'Confirmée', 'En préparation', 'En livraison', 'Livrée', 'Annulée') DEFAULT 'En attente',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        
        // Table order_items
        $sql = "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            quantity INT NOT NULL,
            unit_price DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            notes TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        
    } catch (Exception $e) {
        error_log("Error creating tables: " . $e->getMessage());
        throw $e;
    }
}

/**
 * Rediriger vers la page de détails de commande
 */
function redirectToOrderDetails($orderNumber, $finalTotal, $shippingCost, $cartData, $name, $phone, $address, $notes, $orderId) {
    // Récupérer les détails complets de la commande pour l'affichage
    $pdo = getDB();
    if ($pdo) {
        try {
            // Récupérer les détails de la commande
            $stmt = $pdo->prepare("SELECT o.*, c.name as customer_name, c.phone as customer_phone FROM orders o LEFT JOIN customers c ON o.customer_id = c.id WHERE o.id = ?");
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();
            
            // Récupérer les articles de la commande
            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt->execute([$orderId]);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Stocker les données en session pour la page de détails
            session_start();
            $_SESSION['order_success'] = [
                'order_number' => $order['order_number'],
                'status' => $order['status'],
                'customer_name' => $order['customer_name'],
                'customer_phone' => $order['customer_phone'],
                'delivery_address' => $order['delivery_address'],
                'delivery_notes' => $order['delivery_notes'],
                'total_amount' => $order['total_amount'],
                'shipping_cost' => $order['shipping_cost'],
                'final_amount' => $order['final_amount'],
                'items' => $items,
                'created_at' => $order['created_at']
            ];
            
            // Rediriger vers la page de détails
            header('Location: /gsm/index.php?page=commande-details');
            exit;
            
        } catch (PDOException $e) {
            error_log("Error fetching order details: " . $e->getMessage());
            // En cas d'erreur, rediriger vers le panier
            redirectToCartError('Error fetching order details');
        }
    } else {
        redirectToCartError('Database connection failed');
    }
}

/**
 * Rediriger vers le panier en cas d'erreur
 */
function redirectToCartError($errorDetails = '') {
    error_log("Order processing error: $errorDetails");
    
    // Stocker l'erreur en session avec plus de détails pour le débogage
    session_start();
    $_SESSION['checkout_error'] = 'Une erreur est survenue lors du traitement de votre commande. Veuillez réessayer.';
    $_SESSION['checkout_error_details'] = $errorDetails; // Pour débogage
    
    // Rediriger vers le panier
    header('Location: /gsm/index.php?page=panier');
    exit;
}

// Traitement du formulaire POST
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . json_encode($_POST));
error_log("Request URI: " . $_SERVER['REQUEST_URI']);
error_log("HTTP Referer: " . ($_SERVER['HTTP_REFERER'] ?? 'N/A'));
error_log("Content Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'N/A'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logging détaillé pour débogage
    error_log("Checkout process started - POST data received");
    
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $notes = isset($_POST['notes']) ? trim($_POST['notes']) : '';
    $cartDataJson = isset($_POST['cart_data']) ? $_POST['cart_data'] : '';
    $cartData = json_decode($cartDataJson, true) ?: [];
    $totalAmount = isset($_POST['total_amount']) ? (float)$_POST['total_amount'] : 0;
    
    error_log("Checkout data - Name: $name, Phone: $phone, Cart items: " . count($cartData) . ", Total: $totalAmount");

    // Validation
    if (empty($name) || empty($phone) || empty($address)) {
        error_log("Validation failed: Missing required fields");
        session_start();
        $_SESSION['checkout_error'] = 'Veuillez remplir tous les champs obligatoires.';
        $_SESSION['checkout_data'] = $_POST;
        header('Location: /gsm/index.php?page=checkout');
        exit;
    } elseif (!preg_match('/^[+]?[0-9\s\-\(\)]{8,20}$/', $phone)) {
        error_log("Validation failed: Invalid phone format - $phone");
        session_start();
        $_SESSION['checkout_error'] = 'Veuillez entrer un numéro de téléphone valide.';
        $_SESSION['checkout_data'] = $_POST;
        header('Location: /gsm/index.php?page=checkout');
        exit;
    } elseif (empty($cartData) || $totalAmount <= 0) {
        error_log("Validation failed: Empty cart or invalid total - Cart: " . json_encode($cartData) . ", Total: $totalAmount");
        session_start();
        $_SESSION['checkout_error'] = 'Votre panier est vide. Veuillez ajouter des produits avant de continuer.';
        header('Location: /gsm/index.php?page=panier');
        exit;
    } else {
        error_log("Validation passed - proceeding to database operations");
        // Sauvegarder la commande en base de données
        $pdo = getDB();
        $orderNumber = 'CMD-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $shippingCost = $totalAmount > 5000 ? 0 : 1500;
        $finalTotal = $totalAmount + $shippingCost;
        
        if ($pdo) {
            error_log("Database connection successful - starting transaction");
            try {
                // Créer les tables si elles n'existent pas
                createTablesIfNeeded($pdo);
                error_log("Tables checked/created successfully");
                
                $pdo->beginTransaction();
                error_log("Database transaction started");
                
                // Vérifier si le client existe déjà
                $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
                $stmt->execute([$phone]);
                $customer = $stmt->fetch();
                error_log("Customer lookup result: " . ($customer ? "Found customer ID: " . $customer['id'] : "Customer not found"));
                
                if (!$customer) {
                    // Créer le client
                    $stmt = $pdo->prepare("INSERT INTO customers (name, phone, address, notes) VALUES (?, ?, ?, ?)");
                    $result = $stmt->execute([$name, $phone, $address, $notes]);
                    $customerId = $pdo->lastInsertId();
                    error_log("Customer created - ID: $customerId, Execute result: $result");
                } else {
                    $customerId = $customer['id'];
                    // Mettre à jour les infos du client si nécessaire
                    $stmt = $pdo->prepare("UPDATE customers SET name = ?, address = ?, notes = ? WHERE id = ?");
                    $result = $stmt->execute([$name, $address, $notes, $customerId]);
                    error_log("Customer updated - ID: $customerId, Execute result: $result");
                }
                
                // Créer la commande
                $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_id, total_amount, shipping_cost, final_amount, delivery_address, delivery_notes, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'En attente')");
                $result = $stmt->execute([$orderNumber, $customerId, $totalAmount, $shippingCost, $finalTotal, $address, $notes]);
                $orderId = $pdo->lastInsertId();
                error_log("Order created - Number: $orderNumber, ID: $orderId, Execute result: $result");
                
                // Ajouter les articles de commande
                $itemCount = 0;
                foreach ($cartData as $item) {
                    $subtotal = $item['price'] * $item['quantity'];
                    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $result = $stmt->execute([$orderId, $item['id'], $item['name'], $item['quantity'], $item['price'], $subtotal, $notes]);
                    $itemCount++;
                    error_log("Order item added - Item $itemCount: {$item['name']} x{$item['quantity']}, Execute result: $result");
                }
                
                $pdo->commit();
                error_log("Transaction committed successfully - Total items: $itemCount");
                
                // Message de succès et redirection vers la page de détails de commande
                redirectToOrderDetails($orderNumber, $finalTotal, $shippingCost, $cartData, $name, $phone, $address, $notes, $orderId);
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                error_log("Database error - Message: " . $e->getMessage() . ", Code: " . $e->getCode());
                error_log("Error trace: " . $e->getTraceAsString());
                
                // En cas d'erreur BDD, rediriger vers le panier
                redirectToCartError($e->getMessage());
            }
        } else {
            error_log("Database connection failed - PDO is null");
            // Si la BDD n'est pas disponible, rediriger vers le panier
            redirectToCartError('Database connection failed');
        }
    }
} else {
    // Si ce n'est pas du POST, afficher un message d'erreur
    echo "<h1>Erreur d'accès</h1>";
    echo "<p>Cette page ne peut être accédée que via le formulaire de commande.</p>";
    echo "<p><a href='?page=panier'>Retour au panier</a></p>";
    exit;
}
?>
