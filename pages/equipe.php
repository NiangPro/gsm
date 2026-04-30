<?php
$members = [
    ['name' => 'Amy NIOME', 'role' => 'Présidente', 'roleEn' => 'President', 'description' => 'Leader visionnaire du GIE, Amy coordonne les activités et porte la voix du groupement auprès des partenaires.', 'descEn' => 'Visionary leader of the GIE, Amy coordinates activities and represents the group to partners.'],
    ['name' => 'Angélique TOURE', 'role' => 'Vice-Présidente', 'roleEn' => 'Vice President', 'description' => 'Bras droit de la présidente, Angélique veille à la bonne marche des projets et à la cohésion de l\'équipe.', 'descEn' => 'Right-hand of the president, Angélique ensures the smooth running of projects and team cohesion.'],
    ['name' => 'Anna NDIAYE', 'role' => 'Trésorière', 'roleEn' => 'Treasurer', 'description' => 'Anna assure une gestion rigoureuse et transparente des finances du groupement.', 'descEn' => 'Anna ensures rigorous and transparent management of the group\'s finances.'],
    ['name' => 'Magou SAMB', 'role' => 'Gérante de Boutique', 'roleEn' => 'Shop Manager', 'description' => 'Magou gère la boutique sur place et veille à la qualité du service client au quotidien.', 'descEn' => 'Magou manages the on-site shop and ensures quality customer service daily.'],
];

$isEn = $lang === 'en';
?>

<div>
    <section class="bg-gradient-to-br from-primary-50 to-gray-100 py-20">
        <div class="container mx-auto px-4 max-w-7xl text-center max-w-3xl">
            <h1 class="text-4xl md:text-5xl font-heading font-bold mb-6"><?php echo __('team.title'); ?></h1>
            <p class="text-lg text-gray-600"><?php echo __('team.subtitle'); ?></p>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4 max-w-7xl max-w-5xl">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <?php foreach ($members as $member): ?>
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-lg transition-shadow text-center p-6">
                        <?php if ($member['name'] === 'Magou SAMB'): ?>
                            <div class="w-20 h-20 rounded-full overflow-hidden mx-auto mb-4">
                                <img src="assets/images/magou-samb-profile.jpg" alt="Magou SAMB" class="w-full h-full object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=&quot;w-full h-full bg-primary-100 text-primary-600 flex items-center justify-center&quot;><i class=&quot;fas fa-user text-3xl&quot;></i></div>';">
                            </div>
                        <?php elseif ($member['name'] === 'Amy NIOME'): ?>
                            <div class="w-20 h-20 rounded-full overflow-hidden mx-auto mb-4">
                                <img src="assets/images/amy-niome-profile.jpg" alt="Amy NIOME" class="w-full h-full object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=&quot;w-full h-full bg-primary-100 text-primary-600 flex items-center justify-center&quot;><i class=&quot;fas fa-user text-3xl&quot;></i></div>';">
                            </div>
                        <?php elseif ($member['name'] === 'Anna NDIAYE'): ?>
                            <div class="w-20 h-20 rounded-full overflow-hidden mx-auto mb-4">
                                <img src="assets/images/anna-ndiaye-profile.jpg" alt="Anna NDIAYE" class="w-full h-full object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=&quot;w-full h-full bg-primary-100 text-primary-600 flex items-center justify-center&quot;><i class=&quot;fas fa-user text-3xl&quot;></i></div>';">
                            </div>
                        <?php elseif ($member['name'] === 'Angélique TOURE'): ?>
                            <div class="w-20 h-20 rounded-full overflow-hidden mx-auto mb-4">
                                <img src="assets/images/angelique-toure-profile.jpg" alt="Angélique TOURE" class="w-full h-full object-cover" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=&quot;w-full h-full bg-primary-100 text-primary-600 flex items-center justify-center&quot;><i class=&quot;fas fa-user text-3xl&quot;></i></div>';">
                            </div>
                        <?php else: ?>
                            <div class="w-20 h-20 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-user text-3xl"></i>
                            </div>
                        <?php endif; ?>
                        <h3 class="font-heading text-lg font-bold mb-2"><?php echo $member['name']; ?></h3>
                        <p class="text-sm font-medium text-primary-600 mb-3"><?php echo $isEn ? $member['roleEn'] : $member['role']; ?></p>
                        <p class="text-sm text-gray-600"><?php echo $isEn ? $member['descEn'] : $member['description']; ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
