<?php
// Inclure la configuration
require_once __DIR__ . '/../includes/config.php';

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$currentPage = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '?page=admin&action=dashboard';

    if ($email === 'admin@sokhnamai.sn' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        // Redirection avec JavaScript (car header() ne fonctionne pas après output)
        echo '<script>window.location.href = "' . $redirect . '";</script>';
        echo '<div class="text-center py-8"><i class="fas fa-spinner fa-spin text-primary-600 text-2xl"></i><p class="mt-2 text-gray-600">Connexion en cours...</p></div>';
        exit;
    } else {
        $error = __('admin.login.errorDesc');
    }
}

// Récupérer le paramètre de redirection depuis l'URL
$redirectUrl = isset($_GET['redirect']) ? $_GET['redirect'] : '?page=admin&action=dashboard';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo __('admin.login.title'); ?> - GIE Sokhna Maï</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        heading: ['Playfair Display', 'serif'],
                        body: ['Lato', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            DEFAULT: '#16a34a',
                            foreground: '#ffffff',
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#16a34a',
                            600: '#15803d',
                            700: '#166534',
                            800: '#14532d',
                            900: '#052e16',
                        },
                    }
                }
            }
        }
    </script>
</head>
<body class="font-body min-h-screen flex items-center justify-center bg-gradient-to-br from-primary-50 to-gray-100 p-4">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-primary-200/50 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-gray-200/50 rounded-full blur-3xl"></div>
    </div>

    <div class="absolute top-4 right-4 z-20">
        <a href="?<?php echo $currentPage ? 'page=' . $currentPage . '&' : ''; ?>lang=<?php echo $lang === 'fr' ? 'en' : 'fr'; ?>" 
           class="flex items-center gap-1 px-3 py-2 text-sm border border-gray-300 rounded bg-white hover:bg-gray-50 transition-colors">
            <i class="fas fa-globe"></i>
            <?php echo $lang === 'fr' ? 'EN' : 'FR'; ?>
        </a>
    </div>

    <div class="w-full max-w-md relative z-10 bg-white rounded-xl shadow-2xl border border-primary-100">
        <div class="p-8">
            <div class="text-center space-y-4 pb-6">
                <div class="mx-auto w-20 h-20 rounded-full overflow-hidden shadow-lg ring-4 ring-primary-200">
                    <img src="<?php echo asset('logo.jpg'); ?>" alt="Logo" class="w-full h-full object-cover">
                </div>
                <div>
                    <h1 class="text-2xl font-heading text-primary-600 font-bold"><?php echo __('admin.login.title'); ?></h1>
                    <p class="text-gray-500 text-sm"><?php echo __('admin.login.subtitle'); ?></p>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4">
                    <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-5">
                <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirectUrl); ?>">
                <div class="space-y-2">
                    <label class="block text-sm font-medium"><?php echo __('admin.login.email'); ?></label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="email" name="email" required 
                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="admin@example.com">
                    </div>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium"><?php echo __('admin.login.password'); ?></label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="password" name="password" required id="password"
                               class="w-full pl-10 pr-10 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePassword()" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition-colors">
                    <i class="fas fa-shield-alt mr-2"></i> <?php echo __('admin.login.submit'); ?>
                </button>
            </form>

            <!-- Lien retour accueil -->
            <div class="mt-4 text-center">
                <a href="?page=home" class="text-sm text-emerald-600 hover:text-emerald-800 hover:underline flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left"></i>
                    Retour à l'accueil
                </a>
            </div>

            <div class="mt-4 p-3 rounded-lg bg-gray-50 border border-gray-200">
                <p class="text-xs text-gray-500 text-center"><?php echo __('admin.login.restricted'); ?></p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (password.type === 'password') {
                password.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
