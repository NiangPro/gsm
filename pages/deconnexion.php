<?php
// Déconnexion du client
if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
}

// Redirection vers la page d'accueil avec JavaScript (car header() ne fonctionne pas après output)
?>
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="text-center">
        <i class="fas fa-spinner fa-spin text-emerald-600 text-3xl mb-4"></i>
        <p class="text-gray-600">Déconnexion en cours...</p>
    </div>
</div>
<script>
    window.location.href = '?page=home';
</script>
<?php exit; ?>
