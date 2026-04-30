<?php
$error = '';
$success = '';

// Fonction pour créer la table customers si elle n'existe pas et ajouter les colonnes manquantes
function createCustomersTableIfNeeded($pdo) {
    try {
        // Créer la table si elle n'existe pas
        $sql = "CREATE TABLE IF NOT EXISTS customers (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(150) NOT NULL,
            phone VARCHAR(20) NOT NULL UNIQUE,
            email VARCHAR(255) UNIQUE,
            password_hash VARCHAR(255),
            address TEXT,
            city VARCHAR(100),
            notes TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_phone (phone),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        
        // Ajouter les colonnes manquantes si la table existe déjà
        $columns = $pdo->query("SHOW COLUMNS FROM customers")->fetchAll(PDO::FETCH_COLUMN);
        
        if (!in_array('password_hash', $columns)) {
            $pdo->exec("ALTER TABLE customers ADD COLUMN password_hash VARCHAR(255)");
        }
        if (!in_array('email', $columns)) {
            $pdo->exec("ALTER TABLE customers ADD COLUMN email VARCHAR(255) UNIQUE");
        }
        if (!in_array('is_active', $columns)) {
            $pdo->exec("ALTER TABLE customers ADD COLUMN is_active BOOLEAN DEFAULT TRUE");
        }
        if (!in_array('city', $columns)) {
            $pdo->exec("ALTER TABLE customers ADD COLUMN city VARCHAR(100)");
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error creating customers table: " . $e->getMessage());
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    
    // Validation
    if (empty($name) || empty($phone) || empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'L\'adresse email n\'est pas valide.';
    } elseif (strlen($password) < 6) {
        $error = 'Le mot de passe doit contenir au moins 6 caractères.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Les mots de passe ne correspondent pas.';
    } elseif (!preg_match('/^[+]?[0-9\s\-\(\)]{8,20}$/', $phone)) {
        $error = 'Veuillez entrer un numéro de téléphone valide.';
    } else {
        $pdo = getDB();
        if ($pdo) {
            try {
                // Créer la table si elle n'existe pas
                createCustomersTableIfNeeded($pdo);
                
                // Vérifier si l'email existe déjà
                $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetch()) {
                    $error = 'Cette adresse email est déjà utilisée.';
                } else {
                    // Vérifier si le téléphone existe déjà
                    $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
                    $stmt->execute([$phone]);
                    if ($stmt->fetch()) {
                        $error = 'Ce numéro de téléphone est déjà utilisée.';
                    } else {
                        // Créer le compte
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("INSERT INTO customers (name, phone, email, password_hash, address, city) VALUES (?, ?, ?, ?, ?, ?)");
                        $stmt->execute([$name, $phone, $email, $passwordHash, $address, $city]);
                        
                        $success = 'Votre compte a été créé avec succès ! Vous pouvez maintenant vous connecter.';
                    }
                }
            } catch (PDOException $e) {
                $error = 'Erreur: ' . $e->getMessage();
                error_log("Registration error: " . $e->getMessage());
            }
        } else {
            $error = 'Erreur de connexion à la base de données.';
        }
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white p-8 rounded-xl shadow-lg">
        <div class="text-center">
            <div class="mx-auto w-20 h-20 rounded-full overflow-hidden shadow-lg">
                <img src="<?php echo asset('logo.jpg'); ?>" alt="Logo" class="w-full h-full object-cover">
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Créer un compte</h2>
            <p class="mt-2 text-sm text-gray-600">
                Ou <a href="?page=connexion" class="font-medium text-emerald-600 hover:text-emerald-500">connectez-vous à votre compte</a>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i> <?php echo $success; ?>
            </div>
            <div class="text-center">
                <a href="?page=connexion" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700">
                    Se connecter
                </a>
            </div>
        <?php else: ?>
            <form class="mt-8 space-y-6" method="POST">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet *</label>
                        <input id="name" name="name" type="text" required 
                               class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                               placeholder="Votre nom complet">
                    </div>
                    
                    <div class="mb-4">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone *</label>
                        <input id="phone" name="phone" type="tel" required 
                               class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                               placeholder="+221 77 123 45 67">
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                        <input id="email" name="email" type="email" required 
                               class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                               placeholder="votre@email.com">
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe *</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required 
                                   class="appearance-none rounded-lg relative block w-full px-3 py-2 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                                   placeholder="Minimum 6 caractères">
                            <button type="button" onclick="togglePassword('password', 'eyeIcon1')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="eyeIcon1"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe *</label>
                        <div class="relative">
                            <input id="confirm_password" name="confirm_password" type="password" required 
                                   class="appearance-none rounded-lg relative block w-full px-3 py-2 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                                   placeholder="Confirmez votre mot de passe">
                            <button type="button" onclick="togglePassword('confirm_password', 'eyeIcon2')" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                                <i class="fas fa-eye" id="eyeIcon2"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                        <input id="address" name="address" type="text" 
                               class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                               placeholder="Votre adresse">
                    </div>
                    
                    <div class="mb-4">
                        <label for="city" class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                        <input id="city" name="city" type="text" 
                               class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                               placeholder="Dakar">
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <i class="fas fa-user-plus text-emerald-500 group-hover:text-emerald-400"></i>
                        </span>
                        Créer mon compte
                    </button>
                </div>
            </form>
        <?php endif; ?>
        
        <div class="mt-4 text-center">
            <a href="?page=home" class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-1"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<script>
function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
