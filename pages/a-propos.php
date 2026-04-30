<?php
$milestones = [
    ['icon' => 'fa-users', 'key' => 'identity'],
    ['icon' => 'fa-bullseye', 'key' => 'mission'],
    ['icon' => 'fa-heart', 'key' => 'values'],
    ['icon' => 'fa-award', 'key' => 'ambition'],
];
?>

<div>
    <section class="bg-gradient-to-br from-primary-50 to-gray-100 py-20">
        <div class="container mx-auto px-4 max-w-7xl text-center max-w-3xl">
            <h1 class="text-4xl md:text-5xl font-heading font-bold mb-6"><?php echo __('about.title'); ?></h1>
            <p class="text-lg text-gray-600"><?php echo __('about.subtitle'); ?></p>
        </div>
    </section>

    <section class="py-20">
        <div class="container mx-auto px-4 max-w-7xl max-w-4xl">
            <div class="prose prose-lg mx-auto text-center">
                <h2 class="font-heading text-3xl font-bold mb-6"><?php echo __('about.storyTitle'); ?></h2>
                <p class="text-gray-600 leading-relaxed mb-4"><?php echo __('about.storyP1'); ?></p>
                <p class="text-gray-600 leading-relaxed"><?php echo __('about.storyP2'); ?></p>
            </div>
        </div>
    </section>

    <section class="py-20 bg-gray-50">
        <div class="container mx-auto px-4 max-w-7xl">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                <?php foreach ($milestones as $m): ?>
                    <div class="bg-white rounded-lg p-8 shadow-sm border border-gray-200">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 text-primary-600 mb-4">
                            <i class="fas <?php echo $m['icon']; ?> text-lg"></i>
                        </div>
                        <h3 class="font-heading text-xl font-semibold mb-3"><?php echo __('about.' . $m['key'] . '.title'); ?></h3>
                        <p class="text-gray-600 leading-relaxed"><?php echo __('about.' . $m['key'] . '.text'); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <section class="py-20">
        <div class="container mx-auto px-4 max-w-7xl max-w-3xl text-center">
            <h2 class="font-heading text-3xl font-bold mb-6"><?php echo __('about.engagementTitle'); ?></h2>
            <p class="text-gray-600 leading-relaxed text-lg"><?php echo __('about.engagementText'); ?></p>
        </div>
    </section>
</div>
