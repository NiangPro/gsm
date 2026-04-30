<?php
// Traitement des paramètres administrateur
$pdo = getDB();
$message = '';
$success = false;

// Fonction pour créer la table des paramètres si elle n'existe pas
function createSettingsTable($pdo) {
    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admin_settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                setting_key VARCHAR(100) UNIQUE NOT NULL,
                setting_value TEXT,
                setting_type ENUM('profile', 'system', 'security') DEFAULT 'system',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Insérer les paramètres par défaut
        $defaultSettings = [
            ['admin_name', 'Administrateur', 'profile'],
            ['admin_email', 'admin@sokhnamai.sn', 'profile'],
            ['admin_phone', '+221 33 123 45 67', 'profile'],
            ['site_name', 'GIE Sokhna Maï', 'system'],
            ['site_description', 'Produits naturels et artisanaux du Sénégal', 'system'],
            ['site_email', 'contact@sokhnamai.sn', 'system'],
            ['site_phone', '+221 33 123 45 67', 'system'],
            ['site_address', 'Dakar, Sénégal', 'system'],
            ['maintenance_mode', '0', 'system'],
            ['email_notifications', '1', 'security'],
            ['backup_frequency', 'daily', 'security'],
            ['session_timeout', '3600', 'security']
        ];
        
        $stmt = $pdo->prepare("INSERT IGNORE INTO admin_settings (setting_key, setting_value, setting_type) VALUES (?, ?, ?)");
        foreach ($defaultSettings as $setting) {
            $stmt->execute($setting);
        }
        
        // Créer la table admin_users si elle n'existe pas
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admin_users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                name VARCHAR(100),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Créer un admin par défaut
        $defaultPassword = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT IGNORE INTO admin_users (username, email, password, name) VALUES (?, ?, ?, ?)");
        $stmt->execute(['admin', 'admin@sokhnamai.sn', $defaultPassword, 'Administrateur']);
        
    } catch (PDOException $e) {
        error_log("Error creating settings table: " . $e->getMessage());
    }
}

// Récupérer les paramètres actuels
$adminSettings = [];
if ($pdo) {
    try {
        // Créer la table si elle n'existe pas
        createSettingsTable($pdo);
        
        // Paramètres du profil
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM admin_settings WHERE setting_type = 'profile'");
        $stmt->execute();
        $profileSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Paramètres système
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM admin_settings WHERE setting_type = 'system'");
        $stmt->execute();
        $systemSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        // Paramètres de sécurité
        $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM admin_settings WHERE setting_type = 'security'");
        $stmt->execute();
        $securitySettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $adminSettings = array_merge($profileSettings, $systemSettings, $securitySettings);
        
    } catch (PDOException $e) {
        error_log("Error fetching admin settings: " . $e->getMessage());
        // Valeurs par défaut
        $adminSettings = [
            'admin_name' => 'Administrateur',
            'admin_email' => 'admin@sokhnamai.sn',
            'admin_phone' => '+221 33 123 45 67',
            'site_name' => 'GIE Sokhna Maï',
            'site_description' => 'Produits naturels et artisanaux du Sénégal',
            'site_email' => 'contact@sokhnamai.sn',
            'site_phone' => '+221 33 123 45 67',
            'site_address' => 'Dakar, Sénégal',
            'maintenance_mode' => '0',
            'email_notifications' => '1',
            'backup_frequency' => 'daily',
            'session_timeout' => '3600'
        ];
    }
} else {
    // Valeurs par défaut si BDD indisponible
    $adminSettings = [
        'admin_name' => 'Administrateur',
        'admin_email' => 'admin@sokhnamai.sn',
        'admin_phone' => '+221 33 123 45 67',
        'site_name' => 'GIE Sokhna Maï',
        'site_description' => 'Produits naturels et artisanaux du Sénégal',
        'site_email' => 'contact@sokhnamai.sn',
        'site_phone' => '+221 33 123 45 67',
        'site_address' => 'Dakar, Sénégal',
        'maintenance_mode' => '0',
        'email_notifications' => '1',
        'backup_frequency' => 'daily',
        'session_timeout' => '3600'
    ];
}

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'update_profile') {
        $adminName = isset($_POST['admin_name']) ? trim($_POST['admin_name']) : '';
        $adminEmail = isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
        $adminPhone = isset($_POST['admin_phone']) ? trim($_POST['admin_phone']) : '';
        $currentPassword = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
        $newPassword = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
        $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
        
        // Validation
        if (empty($adminName) || empty($adminEmail)) {
            $message = 'Le nom et l\'email sont obligatoires';
        } elseif (!filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
            $message = 'L\'adresse email n\'est pas valide';
        } elseif (!empty($newPassword) && strlen($newPassword) < 8) {
            $message = 'Le nouveau mot de passe doit contenir au moins 8 caractères';
        } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
            $message = 'Les mots de passe ne correspondent pas';
        } else {
            try {
                if ($pdo) {
                    // Vérifier le mot de passe actuel si changement de mot de passe
                    if (!empty($newPassword)) {
                        if (empty($currentPassword)) {
                            $message = 'Le mot de passe actuel est requis pour le changer';
                        } else {
                            // Vérifier le mot de passe actuel (ici vous devriez avoir un hash stocké)
                            $stmt = $pdo->prepare("SELECT password FROM admin_users WHERE id = 1");
                            $stmt->execute();
                            $storedPassword = $stmt->fetch()['password'];
                            
                            if (!password_verify($currentPassword, $storedPassword)) {
                                $message = 'Le mot de passe actuel est incorrect';
                            } else {
                                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                                $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE id = 1");
                                $stmt->execute([$hashedPassword]);
                            }
                        }
                    }
                    
                    // Mettre à jour les informations du profil
                    $stmt = $pdo->prepare("UPDATE admin_settings SET setting_value = ? WHERE setting_key = ?");
                    $stmt->execute([$adminName, 'admin_name']);
                    $stmt->execute([$adminEmail, 'admin_email']);
                    $stmt->execute([$adminPhone, 'admin_phone']);
                    
                    $message = 'Profil mis à jour avec succès!';
                    $success = true;
                    
                    // Mettre à jour les variables de session
                    $_SESSION['admin_name'] = $adminName;
                    $_SESSION['admin_email'] = $adminEmail;
                    
                    // Rafraîchir les paramètres après mise à jour
                    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM admin_settings WHERE setting_type = 'profile'");
                    $stmt->execute();
                    $profileSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    $adminSettings = array_merge($adminSettings, $profileSettings);
                }
            } catch (PDOException $e) {
                $message = 'Erreur lors de la mise à jour: ' . $e->getMessage();
            }
        }
        
    } elseif ($action === 'update_system') {
        $siteName = isset($_POST['site_name']) ? trim($_POST['site_name']) : '';
        $siteDescription = isset($_POST['site_description']) ? trim($_POST['site_description']) : '';
        $siteEmail = isset($_POST['site_email']) ? trim($_POST['site_email']) : '';
        $sitePhone = isset($_POST['site_phone']) ? trim($_POST['site_phone']) : '';
        $siteAddress = isset($_POST['site_address']) ? trim($_POST['site_address']) : '';
        $maintenanceMode = isset($_POST['maintenance_mode']) ? (int)$_POST['maintenance_mode'] : 0;
        
        // Validation
        if (empty($siteName) || empty($siteEmail)) {
            $message = 'Le nom du site et l\'email sont obligatoires';
        } elseif (!filter_var($siteEmail, FILTER_VALIDATE_EMAIL)) {
            $message = 'L\'adresse email du site n\'est pas valide';
        } else {
            try {
                if ($pdo) {
                    $stmt = $pdo->prepare("UPDATE admin_settings SET setting_value = ? WHERE setting_key = ?");
                    $stmt->execute([$siteName, 'site_name']);
                    $stmt->execute([$siteDescription, 'site_description']);
                    $stmt->execute([$siteEmail, 'site_email']);
                    $stmt->execute([$sitePhone, 'site_phone']);
                    $stmt->execute([$siteAddress, 'site_address']);
                    $stmt->execute([$maintenanceMode, 'maintenance_mode']);
                    
                    $message = 'Paramètres système mis à jour avec succès!';
                    $success = true;
                    
                    // Rafraîchir les paramètres après mise à jour
                    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM admin_settings WHERE setting_type = 'system'");
                    $stmt->execute();
                    $systemSettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    $adminSettings = array_merge($adminSettings, $systemSettings);
                }
            } catch (PDOException $e) {
                $message = 'Erreur lors de la mise à jour: ' . $e->getMessage();
            }
        }
        
    } elseif ($action === 'update_security') {
        $emailNotifications = isset($_POST['email_notifications']) ? (int)$_POST['email_notifications'] : 0;
        $backupFrequency = isset($_POST['backup_frequency']) ? trim($_POST['backup_frequency']) : 'daily';
        $sessionTimeout = isset($_POST['session_timeout']) ? (int)$_POST['session_timeout'] : 3600;
        
        // Validation
        if ($sessionTimeout < 300) {
            $message = 'Le délai d\'expiration de session doit être d\'au moins 5 minutes';
        } elseif (!in_array($backupFrequency, ['daily', 'weekly', 'monthly'])) {
            $message = 'La fréquence de sauvegarde n\'est pas valide';
        } else {
            try {
                if ($pdo) {
                    $stmt = $pdo->prepare("UPDATE admin_settings SET setting_value = ? WHERE setting_key = ?");
                    $stmt->execute([$emailNotifications, 'email_notifications']);
                    $stmt->execute([$backupFrequency, 'backup_frequency']);
                    $stmt->execute([$sessionTimeout, 'session_timeout']);
                    
                    $message = 'Paramètres de sécurité mis à jour avec succès!';
                    $success = true;
                    
                    // Rafraîchir les paramètres après mise à jour
                    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM admin_settings WHERE setting_type = 'security'");
                    $stmt->execute();
                    $securitySettings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                    $adminSettings = array_merge($adminSettings, $securitySettings);
                }
            } catch (PDOException $e) {
                $message = 'Erreur lors de la mise à jour: ' . $e->getMessage();
            }
        }
    }
}

// Informations pour le header
$adminEmail = isset($_SESSION['admin_email']) ? $_SESSION['admin_email'] : 'admin@sokhnamai.sn';
$adminName = explode('@', $adminEmail)[0];
$hour = date('H');
if ($hour < 12) $greeting = 'Bonjour';
elseif ($hour < 18) $greeting = 'Bon après-midi';
else $greeting = 'Bonsoir';
?>

<style>
/* Header admin style from dashboard */
.admin-header {
    position: fixed;
    top: 0;
    left: 16rem;
    right: 0;
    z-index: 50;
    background: white;
    border-bottom: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(8px);
    transition: left 0.3s ease;
}

.sidebar-collapsed .admin-header {
    left: 5rem;
}

.user-dropdown {
    position: relative;
}

.user-dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 0.5rem;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.75rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.2s ease;
    z-index: 100;
}

.user-dropdown-menu.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.user-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    color: #374151;
    text-decoration: none;
    transition: all 0.2s ease;
    border-bottom: 1px solid #f3f4f6;
}

.user-dropdown-item:last-child {
    border-bottom: none;
}

.user-dropdown-item:hover {
    background: #f9fafb;
    color: #059669;
}

/* Adaptation mobile */
@media (max-width: 768px) {
    .admin-header {
        left: 0;
        padding: 1rem;
    }
}

.settings-container {
    max-width: 1200px;
    margin: 0 auto;
}

.settings-section {
    background: white;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.settings-header {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: white;
    padding: 1.25rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.settings-header i {
    font-size: 1.25rem;
}

.settings-header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
}

.settings-content {
    padding: 2rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
    font-size: 0.875rem;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #16a34a;
    box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 100px;
}

.checkbox-group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.checkbox-group input[type="checkbox"] {
    width: 1.25rem;
    height: 1.25rem;
    border-radius: 4px;
    cursor: pointer;
}

.checkbox-group label {
    cursor: pointer;
    user-select: none;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid #e5e7eb;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-primary {
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(22, 163, 74, 0.3);
}

.btn-secondary {
    background: #f3f4f6;
    color: #6b7280;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-success {
    background: #f0fdf4;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.alert-error {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

.password-input-container {
    position: relative;
    display: flex;
    align-items: center;
}

.password-field {
    width: 100%;
    padding-right: 3rem; /* Espace pour le bouton */
}

.password-toggle {
    position: absolute;
    right: 0.75rem;
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 0.5rem;
    border-radius: 4px;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
}

.password-toggle:hover {
    background: #f3f4f6;
    color: #374151;
}

.password-toggle:focus {
    outline: none;
    background: #e5e7eb;
    color: #111827;
}

.password-toggle.visible {
    color: #16a34a;
}

.password-toggle.visible:hover {
    background: #f0fdf4;
    color: #15803d;
}

@media (max-width: 768px) {
    .settings-container {
        padding: 1rem;
    }
    
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<!-- Header fixe avec dropdown utilisateur -->
<div class="admin-header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-4">
        <div>
            <h1 class="text-3xl font-heading font-bold text-gray-900"><?php echo $greeting; ?>, <?php echo ucfirst($adminName); ?> 👋</h1>
            <p class="text-gray-500 mt-1">Paramètres de votre boutique.</p>
        </div>
        <div class="flex items-center gap-4">
            <span class="text-sm text-gray-500" id="current-date"></span>
            
            <!-- Dropdown utilisateur -->
            <div class="user-dropdown">
                <button onclick="toggleUserDropdown()" class="flex items-center gap-2 hover:bg-gray-50 rounded-lg p-2 transition-colors">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center text-white font-bold shadow-lg">
                        <?php echo strtoupper(substr($adminName, 0, 1)); ?>
                    </div>
                    <i class="fas fa-chevron-down text-gray-400 text-xs transition-transform" id="dropdown-arrow"></i>
                </button>
                
                <div class="user-dropdown-menu" id="user-dropdown-menu">
                    <a href="?page=admin&action=profile" class="user-dropdown-item">
                        <i class="fas fa-user text-gray-400 w-4"></i>
                        <span>Profil</span>
                    </a>
                    <a href="?page=admin&action=settings" class="user-dropdown-item">
                        <i class="fas fa-cog text-gray-400 w-4"></i>
                        <span>Paramètres</span>
                    </a>
                    <a href="?page=admin&action=logout" class="user-dropdown-item">
                        <i class="fas fa-sign-out-alt text-gray-400 w-4"></i>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contenu principal avec padding pour compenser le header fixe -->
<div class="dashboard-content" style="padding-top: 120px;">
    <div class="settings-container">
    <div class="mb-6">
        <h1 class="text-3xl font-heading font-bold text-gray-900 mb-2">Paramètres</h1>
        <p class="text-gray-600">Gérez les paramètres de votre administration et de votre site</p>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $success ? 'success' : 'error'; ?>">
            <i class="fas fa-<?php echo $success ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Paramètres du profil -->
    <div class="settings-section">
        <div class="settings-header">
            <i class="fas fa-user"></i>
            <h2>Paramètres du profil</h2>
        </div>
        <div class="settings-content">
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_profile">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="admin_name">Nom complet *</label>
                        <input type="text" id="admin_name" name="admin_name" 
                               value="<?php echo htmlspecialchars($adminSettings['admin_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_email">Email professionnel *</label>
                        <input type="email" id="admin_email" name="admin_email" 
                               value="<?php echo htmlspecialchars($adminSettings['admin_email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="admin_phone">Téléphone</label>
                        <input type="tel" id="admin_phone" name="admin_phone" 
                               value="<?php echo htmlspecialchars($adminSettings['admin_phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="current_password">Mot de passe actuel</label>
                        <div class="password-input-container">
                            <input type="password" id="current_password" name="current_password" 
                                   placeholder="Laissez vide pour ne pas changer" class="password-field">
                            <button type="button" class="password-toggle" data-target="current_password" 
                                    title="Afficher le mot de passe">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Nouveau mot de passe</label>
                        <div class="password-input-container">
                            <input type="password" id="new_password" name="new_password" 
                                   placeholder="Minimum 8 caractères" class="password-field">
                            <button type="button" class="password-toggle" data-target="new_password" 
                                    title="Afficher le mot de passe">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirmer le mot de passe</label>
                        <div class="password-input-container">
                            <input type="password" id="confirm_password" name="confirm_password" 
                                   placeholder="Confirmez le nouveau mot de passe" class="password-field">
                            <button type="button" class="password-toggle" data-target="confirm_password" 
                                    title="Afficher le mot de passe">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="this.form.reset()">
                        <i class="fas fa-times"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Mettre à jour le profil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Paramètres système -->
    <div class="settings-section">
        <div class="settings-header">
            <i class="fas fa-cogs"></i>
            <h2>Paramètres système</h2>
        </div>
        <div class="settings-content">
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_system">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="site_name">Nom du site *</label>
                        <input type="text" id="site_name" name="site_name" 
                               value="<?php echo htmlspecialchars($adminSettings['site_name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_email">Email du site *</label>
                        <input type="email" id="site_email" name="site_email" 
                               value="<?php echo htmlspecialchars($adminSettings['site_email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="site_phone">Téléphone du site</label>
                        <input type="tel" id="site_phone" name="site_phone" 
                               value="<?php echo htmlspecialchars($adminSettings['site_phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="site_address">Adresse du site</label>
                        <input type="text" id="site_address" name="site_address" 
                               value="<?php echo htmlspecialchars($adminSettings['site_address'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label for="site_description">Description du site</label>
                        <textarea id="site_description" name="site_description" 
                                  rows="3"><?php echo htmlspecialchars($adminSettings['site_description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="maintenance_mode" name="maintenance_mode" 
                                   value="1" <?php echo ($adminSettings['maintenance_mode'] ?? '0') == '1' ? 'checked' : ''; ?>>
                            <label for="maintenance_mode">
                                Activer le mode maintenance
                            </label>
                        </div>
                        <small class="text-gray-500 mt-1">
                            Le site affichera une page de maintenance aux visiteurs
                        </small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="this.form.reset()">
                        <i class="fas fa-times"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Mettre à jour les paramètres
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Paramètres de sécurité -->
    <div class="settings-section">
        <div class="settings-header">
            <i class="fas fa-shield-alt"></i>
            <h2>Paramètres de sécurité</h2>
        </div>
        <div class="settings-content">
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_security">
                
                <div class="form-grid">
                    <div class="form-group">
                        <div class="checkbox-group">
                            <input type="checkbox" id="email_notifications" name="email_notifications" 
                                   value="1" <?php echo ($adminSettings['email_notifications'] ?? '1') == '1' ? 'checked' : ''; ?>>
                            <label for="email_notifications">
                                Activer les notifications par email
                            </label>
                        </div>
                        <small class="text-gray-500 mt-1">
                            Recevez des alertes pour les nouvelles commandes et messages
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="backup_frequency">Fréquence de sauvegarde</label>
                        <select id="backup_frequency" name="backup_frequency">
                            <option value="daily" <?php echo ($adminSettings['backup_frequency'] ?? 'daily') == 'daily' ? 'selected' : ''; ?>>
                                Quotidienne
                            </option>
                            <option value="weekly" <?php echo ($adminSettings['backup_frequency'] ?? 'daily') == 'weekly' ? 'selected' : ''; ?>>
                                Hebdomadaire
                            </option>
                            <option value="monthly" <?php echo ($adminSettings['backup_frequency'] ?? 'daily') == 'monthly' ? 'selected' : ''; ?>>
                                Mensuelle
                            </option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="session_timeout">Délai d'expiration de session (minutes)</label>
                        <input type="number" id="session_timeout" name="session_timeout" 
                               value="<?php echo htmlspecialchars($adminSettings['session_timeout'] ?? '3600'); ?>" 
                               min="5" max="1440">
                        <small class="text-gray-500 mt-1">
                            Durée avant la déconnexion automatique (5-1440 minutes)
                        </small>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" onclick="this.form.reset()">
                        <i class="fas fa-times"></i>
                        Annuler
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Mettre à jour la sécurité
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Validation côté client
document.addEventListener('DOMContentLoaded', function() {
    // Gestion de l'affichage/masquage des mots de passe
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const targetInput = document.getElementById(targetId);
            
            if (targetInput) {
                if (targetInput.type === 'password') {
                    targetInput.type = 'text';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i>';
                    this.classList.add('visible');
                    this.setAttribute('title', 'Masquer le mot de passe');
                } else {
                    targetInput.type = 'password';
                    this.innerHTML = '<i class="fas fa-eye"></i>';
                    this.classList.remove('visible');
                    this.setAttribute('title', 'Afficher le mot de passe');
                }
            }
        });
    });
    
    // Validation du mot de passe
    const newPassword = document.getElementById('new_password');
    const confirmPassword = document.getElementById('confirm_password');
    const currentPassword = document.getElementById('current_password');
    
    if (newPassword && confirmPassword) {
        function validatePasswords() {
            if (newPassword.value && confirmPassword.value && newPassword.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas');
            } else {
                confirmPassword.setCustomValidity('');
            }
            
            // Si nouveau mot de passe, mot de passe actuel requis
            if (newPassword.value && !currentPassword.value) {
                currentPassword.setCustomValidity('Mot de passe actuel requis');
            } else {
                currentPassword.setCustomValidity('');
            }
        }
        
        newPassword.addEventListener('input', validatePasswords);
        confirmPassword.addEventListener('input', validatePasswords);
        currentPassword.addEventListener('input', validatePasswords);
    }
    
    // Animation des sections
    const sections = document.querySelectorAll('.settings-section');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'slideInUp 0.5s ease-out';
            }
        });
    });
    
    sections.forEach(section => observer.observe(section));
    
    // Réinitialiser les champs de mot de passe lors du reset du formulaire
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('reset', function() {
            const passwordInputs = this.querySelectorAll('.password-field');
            const toggles = this.querySelectorAll('.password-toggle');
            
            passwordInputs.forEach(input => {
                input.type = 'password';
            });
            
            toggles.forEach(toggle => {
                toggle.innerHTML = '<i class="fas fa-eye"></i>';
                toggle.classList.remove('visible');
                toggle.setAttribute('title', 'Afficher le mot de passe');
            });
        });
    });
});

// Animation CSS
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// Gestion du dropdown utilisateur
function toggleUserDropdown() {
    const dropdown = document.getElementById('user-dropdown-menu');
    const arrow = document.getElementById('dropdown-arrow');
    
    if (dropdown) {
        dropdown.classList.toggle('show');
        if (arrow) {
            arrow.style.transform = dropdown.classList.contains('show') ? 'rotate(180deg)' : '';
        }
    }
}

// Fermer le dropdown en cliquant à l'extérieur
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('user-dropdown-menu');
    const dropdownButton = e.target.closest('.user-dropdown button');
    
    if (!dropdownButton && dropdown && dropdown.classList.contains('show')) {
        dropdown.classList.remove('show');
        const arrow = document.getElementById('dropdown-arrow');
        if (arrow) arrow.style.transform = '';
    }
});

// Afficher la date actuelle
const dateElement = document.getElementById('current-date');
if (dateElement) {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    dateElement.textContent = now.toLocaleDateString('fr-FR', options);
}
</script>

</div><!-- Fermeture dashboard-content -->
