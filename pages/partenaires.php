<?php
$timeline = [
    ['year' => '2021', 'key' => 'foire', 'icon' => 'fa-calendar', 'image' => 'foire-innovation.png'],
    ['year' => '2022', 'key' => 'marche', 'icon' => 'fa-map-marker-alt', 'image' => null],
    ['year' => '2023', 'key' => 'humasol', 'icon' => 'fa-sun', 'image' => 'humasol-logo.png'],
];
?>

<div>
    <section class="bg-primary-600 text-white py-16">
        <div class="container mx-auto px-4 max-w-7xl text-center">
            <h1 class="text-4xl md:text-5xl font-heading font-bold mb-4"><?php echo __('partners.title'); ?></h1>
            <p class="text-lg opacity-90 max-w-2xl mx-auto"><?php echo __('partners.subtitle'); ?></p>
        </div>
    </section>

    <section class="py-20">
        <div class="container mx-auto px-4 max-w-7xl max-w-4xl">
            <div class="space-y-12">
                <?php foreach ($timeline as $item): ?>
                    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
                        <div class="flex flex-col md:flex-row">
                            <?php if ($item['image']): ?>
                                <div class="md:w-1/3 bg-gray-50 flex items-center justify-center p-6">
                                    <img src="<?php echo asset($item['image']); ?>" alt="<?php echo __('partners.' . $item['key'] . '.title'); ?>" 
                                         class="max-h-48 object-contain rounded">
                                </div>
                            <?php endif; ?>
                            <div class="p-6 md:p-8 <?php echo $item['image'] ? 'md:w-2/3' : 'w-full'; ?>">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-primary-100 text-primary-600">
                                        <i class="fas <?php echo $item['icon']; ?>"></i>
                                    </div>
                                    <span class="text-sm font-semibold px-3 py-1 rounded-full bg-accent-100 text-accent-700"><?php echo $item['year']; ?></span>
                                </div>
                                <h2 class="text-xl font-heading font-bold mb-1"><?php echo __('partners.' . $item['key'] . '.title'); ?></h2>
                                <p class="text-sm text-gray-500 mb-3"><?php echo __('partners.' . $item['key'] . '.date'); ?></p>
                                <p class="text-gray-600"><?php echo __('partners.' . $item['key'] . '.desc'); ?></p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
</div>
