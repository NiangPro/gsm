<?php
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $pdo = getDB();
        if ($pdo) {
            try {
                // Rechercher le client par email
                $stmt = $pdo->prepare("SELECT * FROM customers WHERE email = ? AND is_active = TRUE");
                $stmt->execute([$email]);
                $customer = $stmt->fetch();
                
                if ($customer && password_verify($password, $customer['password_hash'])) {
                    // Connexion réussie
                    $_SESSION['user_id'] = $customer['id'];
                    $_SESSION['user_name'] = $customer['name'];
                    $_SESSION['user_email'] = $customer['email'];
                    
                    // Rediriger vers l'espace client avec JavaScript (car header() ne fonctionne pas après output)
                    echo '<script>window.location.href = "?page=espace-client";</script>';
                    echo '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-emerald-600 text-2xl"></i><p class="mt-2 text-gray-600">Connexion en cours...</p></div>';
                    exit;
                } else {
                    $error = 'Email ou mot de passe incorrect.';
                }
            } catch (PDOException $e) {
                $error = 'Une erreur est survenue. Veuillez réessayer.';
                error_log("Login error: " . $e->getMessage());
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
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">Connexion</h2>
            <p class="mt-2 text-sm text-gray-600">
                Ou <a href="?page=inscription" class="font-medium text-emerald-600 hover:text-emerald-500">créez un compte gratuitement</a>
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6" method="POST">
            <div class="rounded-md shadow-sm -space-y-px">
                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input id="email" name="email" type="email" required 
                           class="appearance-none rounded-lg relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                           placeholder="votre@email.com">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
                    <div class="relative">
                        <input id="password" name="password" type="password" required 
                               class="appearance-none rounded-lg relative block w-full px-3 py-2 pr-10 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 focus:z-10 sm:text-sm"
                               placeholder="Votre mot de passe">
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                    <label for="remember-me" class="ml-2 block text-sm text-gray-900">
                        Se souvenir de moi
                    </label>
                </div>

                <div class="text-sm">
                    <a href="#" class="font-medium text-emerald-600 hover:text-emerald-500">
                        Mot de passe oublié ?
                    </a>
                </div>
            </div>

            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-sign-in-alt text-emerald-500 group-hover:text-emerald-400"></i>
                    </span>
                    Se connecter
                </button>
            </div>
        </form>
        
        <div class="mt-4 text-center">
            <a href="?page=home" class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left mr-1"></i> Retour à l'accueil
            </a>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>
