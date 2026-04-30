<?php
// Page de gestion des messages pour GIE Sokhna Maï
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pdo = getDB();
$message = '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Traitement des actions POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'mark_read') {
        $messageId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($messageId) {
            try {
                $stmt = $pdo->prepare("UPDATE contact_messages SET is_read = TRUE, replied_at = NOW() WHERE id = ?");
                $stmt->execute([$messageId]);
                $message = 'Message marqué comme lu';
            } catch (PDOException $e) {
                error_log("Error marking message as read: " . $e->getMessage());
                $message = 'Erreur lors du marquage du message';
            }
        }
        exit;
    } elseif ($action === 'reply') {
        $messageId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $replyText = isset($_POST['reply']) ? trim($_POST['reply']) : '';
        
        if ($messageId && $replyText) {
            try {
                $stmt = $pdo->prepare("UPDATE contact_messages SET reply_message = ?, replied_by = ?, replied_at = NOW() WHERE id = ?");
                $stmt->execute([$replyText, $_SESSION['admin_id'] ?? 1, $messageId]);
                $message = 'Réponse envoyée avec succès';
            } catch (PDOException $e) {
                error_log("Error replying to message: " . $e->getMessage());
                $message = 'Erreur lors de l\'envoi de la réponse';
            }
        }
        exit;
    } elseif ($action === 'delete') {
        $messageId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($messageId) {
            try {
                $stmt = $pdo->prepare("DELETE FROM contact_messages WHERE id = ?");
                $stmt->execute([$messageId]);
                $message = 'Message supprimé avec succès';
            } catch (PDOException $e) {
                error_log("Error deleting message: " . $e->getMessage());
                $message = 'Erreur lors de la suppression';
            }
        }
        exit;
    }
}

// Récupérer les messages depuis la BDD
$messages = [];
if ($pdo) {
    try {
        $query = "SELECT id, name, email, phone, subject, message, is_read, created_at FROM contact_messages";
        $params = [];
        
        if ($search) {
            $query .= " WHERE name LIKE ? OR email LIKE ? OR subject LIKE ? OR message LIKE ?";
            $searchParam = '%' . $search . '%';
            $params = [$searchParam, $searchParam, $searchParam, $searchParam];
        }
        
        if ($statusFilter === 'unread') {
            $query .= ($params ? ' AND' : ' WHERE') . " is_read = FALSE";
        } elseif ($statusFilter === 'read') {
            $query .= ($params ? ' AND' : ' WHERE') . " is_read = TRUE";
        }
        
        $query .= " ORDER BY created_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $messages = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error fetching messages: " . $e->getMessage());
        // Données mock en cas d'erreur
        $messages = [
            ['id' => 1, 'name' => 'Fatou Diallo', 'email' => 'fatou@email.com', 'phone' => '+221 77 123 45 67', 'subject' => 'Demande de partenariat', 'message' => 'Bonjour, je suis intéressée par vos produits...', 'is_read' => false, 'created_at' => '2024-01-15 10:30:00'],
            ['id' => 2, 'name' => 'Amadou Sow', 'email' => 'amadou@email.com', 'phone' => '+221 76 234 56 78', 'subject' => 'Information produit', 'message' => 'Pouvez-vous me donner plus d\'infos...', 'is_read' => true, 'created_at' => '2024-01-14 14:20:00'],
            ['id' => 3, 'name' => 'Marie Ndiaye', 'email' => 'marie@email.com', 'phone' => '+221 70 345 67 89', 'subject' => 'Commande', 'message' => 'Je souhaite commander...', 'is_read' => false, 'created_at' => '2024-01-13 09:15:00'],
        ];
    }
} else {
    // Données mock si BDD indisponible
    $messages = [
        ['id' => 1, 'name' => 'Fatou Diallo', 'email' => 'fatou@email.com', 'phone' => '+221 77 123 45 67', 'subject' => 'Demande de partenariat', 'message' => 'Bonjour, je suis intéressée par vos produits...', 'is_read' => false, 'created_at' => '2024-01-15 10:30:00'],
        ['id' => 2, 'name' => 'Amadou Sow', 'email' => 'amadou@email.com', 'phone' => '+221 76 234 56 78', 'subject' => 'Information produit', 'message' => 'Pouvez-vous me donner plus d\'infos...', 'is_read' => true, 'created_at' => '2024-01-14 14:20:00'],
        ['id' => 3, 'name' => 'Marie Ndiaye', 'email' => 'marie@email.com', 'phone' => '+221 70 345 67 89', 'subject' => 'Commande', 'message' => 'Je souhaite commander...', 'is_read' => false, 'created_at' => '2024-01-13 09:15:00'],
    ];
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
</style>

<!-- Header fixe avec dropdown utilisateur -->
<div class="admin-header">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 px-6 py-4">
        <div>
            <h1 class="text-3xl font-heading font-bold text-gray-900"><?php echo $greeting; ?>, <?php echo ucfirst($adminName); ?> 👋</h1>
            <p class="text-gray-500 mt-1">Gestion des messages de votre boutique.</p>
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
    <div class="space-y-6">
    <div class="flex items-center gap-3">
        <i class="fas fa-envelope text-primary-600 text-2xl"></i>
        <h1 class="text-2xl font-heading font-bold">Gestion des Messages</h1>
    </div>

    <?php if ($message): ?>
        <div class="bg-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-50 border border-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-200 text-<?php echo strpos($message, 'succès') !== false ? 'emerald' : 'red'; ?>-700 px-4 py-3 rounded mb-4">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="flex flex-col sm:flex-row gap-3">
        <div class="relative flex-1">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <form method="GET" class="w-full">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="action" value="messages">
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                       placeholder="Rechercher un message..."
                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </form>
        </div>
        <form method="GET" class="w-full sm:w-48">
            <input type="hidden" name="page" value="admin">
            <input type="hidden" name="action" value="messages">
            <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
            <select name="status" onchange="this.form.submit()" 
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="all">Tous les messages</option>
                <option value="unread" <?php echo $statusFilter === 'unread' ? 'selected' : ''; ?>>Non lus</option>
                <option value="read" <?php echo $statusFilter === 'read' ? 'selected' : ''; ?>>Lus</option>
            </select>
        </form>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm">
        <div class="p-4 border-b">
            <p class="text-gray-500"><?php echo count($messages); ?> message(s)</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Statut</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Nom</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Téléphone</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Email</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Sujet</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Message</th>
                        <th class="text-left py-3 px-4 text-gray-600 font-medium">Date</th>
                        <th class="text-right py-3 px-4 text-gray-600 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($messages as $msg): ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="py-3 px-4">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium <?php echo $msg['is_read'] ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-700'; ?>">
                                    <?php echo $msg['is_read'] ? 'Lu' : 'Non lu'; ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 font-medium text-gray-900">
                                <?php echo htmlspecialchars($msg['name']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($msg['phone'] ?? 'Non spécifié'); ?>
                            </td>
                            <td class="py-3 px-4 text-sm text-gray-600">
                                <?php echo htmlspecialchars($msg['email']); ?>
                            </td>
                            <td class="py-3 px-4 text-sm font-medium text-gray-700">
                                <?php echo htmlspecialchars($msg['subject']); ?>
                            </td>
                            <td class="py-3 px-4">
                                <div class="max-w-xs truncate text-sm text-gray-600" title="<?php echo htmlspecialchars($msg['message']); ?>">
                                    <?php echo substr(htmlspecialchars($msg['message']), 0, 50) . (strlen($msg['message']) > 50 ? '...' : ''); ?>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-xs text-gray-500"><?php echo formatDate($msg['created_at']); ?></td>
                            <td class="py-3 px-4 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button onclick="viewMessage(<?php echo $msg['id']; ?>)" class="p-1 text-gray-400 hover:text-gray-600" title="Voir détails">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <?php if (!$msg['is_read']): ?>
                                        <button onclick="markAsRead(<?php echo $msg['id']; ?>)" class="p-1 text-blue-600 hover:text-blue-700" title="Marquer comme lu">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button onclick="replyToMessage(<?php echo $msg['id']; ?>)" class="p-1 text-green-600 hover:text-green-700" title="Répondre">
                                        <i class="fas fa-reply"></i>
                                    </button>
                                    <button onclick="deleteMessage(<?php echo $msg['id']; ?>)" class="p-1 text-red-600 hover:text-red-700" title="Supprimer">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-8 text-gray-500">Aucun message trouvé</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal pour répondre à un message -->
<div id="replyModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-heading font-bold">Répondre au message</h3>
                <button onclick="closeReplyModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="replyForm" class="space-y-4">
                <input type="hidden" name="action" value="reply">
                <input type="hidden" name="id" id="replyMessageId">
                
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Votre réponse</label>
                    <textarea name="reply" id="replyText" rows="5" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                              placeholder="Tapez votre réponse ici..."></textarea>
                </div>
                
                <div class="flex gap-2 justify-end pt-4">
                    <button type="button" onclick="closeReplyModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Annuler</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">Envoyer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openModal() {
    // Rediriger vers la page de contact pour créer un nouveau message
    window.location.href = '?page=contact';
}

function viewMessage(id) {
    // Afficher les détails complets du message dans une modal
    const messages = <?php echo json_encode($messages); ?>;
    const message = messages.find(m => m.id == id);
    
    if (message) {
        // Créer une modal temporaire pour afficher les détails
        const modalHtml = `
            <div id="viewModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-heading font-bold">Détails du message</h3>
                            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Nom</label>
                                    <p class="font-medium">${message.name}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Email</label>
                                    <p class="font-medium">${message.email}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Téléphone</label>
                                    <p class="font-medium">${message.phone || 'Non spécifié'}</p>
                                </div>
                                <div>
                                    <label class="text-sm font-medium text-gray-500">Statut</label>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${message.is_read ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-700'}">
                                        ${message.is_read ? 'Lu' : 'Non lu'}
                                    </span>
                                </div>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Sujet</label>
                                <p class="font-medium">${message.subject}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Message</label>
                                <p class="text-gray-700 whitespace-pre-wrap">${message.message}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-500">Date d'envoi</label>
                                <p class="text-gray-600">${message.created_at}</p>
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end mt-6">
                            <button onclick="closeViewModal()" class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Fermer</button>
                            <button onclick="replyToMessage(${message.id}); closeViewModal();" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                                <i class="fas fa-reply mr-2"></i>Répondre
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Ajouter la modal au DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);
        document.body.style.overflow = 'hidden';
    }
}

function closeViewModal() {
    const modal = document.getElementById('viewModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = 'auto';
    }
}

function markAsRead(id) {
    // Afficher un indicateur de chargement
    const button = event.target.closest('button');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    fetch('?page=admin&action=messages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=mark_read&id=${id}`
    })
    .then(response => response.text())
    .then(html => {
        // Afficher un message de succès
        showNotification('Message marqué comme lu avec succès', 'success');
        setTimeout(() => window.location.reload(), 1000);
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors du marquage du message', 'error');
        // Restaurer le bouton
        button.innerHTML = originalContent;
        button.disabled = false;
    });
}

function replyToMessage(id) {
    document.getElementById('replyMessageId').value = id;
    document.getElementById('replyModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function deleteMessage(id) {
    if (confirm('Êtes-vous sûr de vouloir supprimer ce message ? Cette action est irréversible.')) {
        // Afficher un indicateur de chargement
        const button = event.target.closest('button');
        const originalContent = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        button.disabled = true;
        
        fetch('?page=admin&action=messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&id=${id}`
        })
        .then(response => response.text())
        .then(html => {
            showNotification('Message supprimé avec succès', 'success');
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(error => {
            console.error('Erreur:', error);
            showNotification('Erreur lors de la suppression du message', 'error');
            // Restaurer le bouton
            button.innerHTML = originalContent;
            button.disabled = false;
        });
    }
}

function closeReplyModal() {
    document.getElementById('replyModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Fonction pour afficher des notifications
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transform transition-all duration-300 translate-x-full ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-exclamation-circle' : 
                'fa-info-circle'
            } mr-2"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animation d'entrée
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
        notification.classList.add('translate-x-0');
    }, 100);
    
    // Auto-suppression après 3 secondes
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Soumettre le formulaire de réponse
document.getElementById('replyForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const id = document.getElementById('replyMessageId').value;
    const reply = document.getElementById('replyText').value;
    const submitButton = e.target.querySelector('button[type="submit"]');
    
    if (!reply.trim()) {
        showNotification('Veuillez saisir une réponse', 'error');
        return;
    }
    
    // Afficher un indicateur de chargement
    const originalContent = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Envoi en cours...';
    submitButton.disabled = true;
    
    fetch('?page=admin&action=messages', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=reply&id=${id}&reply=${encodeURIComponent(reply)}`
    })
    .then(response => response.text())
    .then(html => {
        showNotification('Réponse envoyée avec succès', 'success');
        closeReplyModal();
        setTimeout(() => window.location.reload(), 1000);
    })
    .catch(error => {
        console.error('Erreur:', error);
        showNotification('Erreur lors de l\'envoi de la réponse', 'error');
        // Restaurer le bouton
        submitButton.innerHTML = originalContent;
        submitButton.disabled = false;
    });
});

// Fermer les modals en cliquant à l'extérieur
document.getElementById('replyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeReplyModal();
    }
});

// Fermer avec la touche Echape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeReplyModal();
    }
});

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
</div><!-- Fermeture space-y-6 -->
