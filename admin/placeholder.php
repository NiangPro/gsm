<?php
$pageTitles = [
    'blog' => 'Gestion du Blog',
    'partenaires' => 'Gestion des Partenaires',
    'messages' => 'Messages',
    'parametres' => 'Paramètres',
];
$currentTitle = isset($pageTitles[$adminPage]) ? $pageTitles[$adminPage] : 'Administration';
?>

<div class="space-y-6">
    <div>
        <h1 class="text-3xl font-heading font-bold text-gray-900"><?php echo $currentTitle; ?></h1>
        <p class="text-gray-500 mt-1">Cette section est en cours de développement.</p>
    </div>

    <div class="bg-white rounded-lg border border-gray-200 shadow-sm p-12 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-primary-100 text-primary-600 mb-4">
            <i class="fas fa-tools text-2xl"></i>
        </div>
        <h2 class="text-xl font-heading font-bold mb-2">Fonctionnalité en cours</h2>
        <p class="text-gray-600 max-w-md mx-auto">Cette fonctionnalité sera disponible prochainement. Revenez plus tard !</p>
        <a href="?page=admin&action=dashboard" class="inline-flex items-center px-4 py-2 mt-6 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Retour au tableau de bord
        </a>
    </div>
</div>
