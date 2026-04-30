<?php
// Page de contact pour GIE Sokhna Maï
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Démarrer la session si pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
$success = '';

// Affichage des messages de session
if (isset($_SESSION['contact_success'])) {
    $success = $_SESSION['contact_success'];
    unset($_SESSION['contact_success']);
}
if (isset($_SESSION['contact_error'])) {
    $error = $_SESSION['contact_error'];
    unset($_SESSION['contact_error']);
}
?>

<div>
    <section class="bg-gradient-to-br from-primary-50 to-gray-100 py-20">
        <div class="container mx-auto px-4 max-w-7xl text-center max-w-3xl">
            <h1 class="text-4xl md:text-5xl font-heading font-bold mb-6"><?php echo __('contact.title'); ?></h1>
            <p class="text-lg text-gray-600"><?php echo __('contact.subtitle'); ?></p>
        </div>
    </section>

    <section class="py-16">
        <div class="container mx-auto px-4 max-w-7xl max-w-5xl">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-10">
                <div class="lg:col-span-2 space-y-6">
                    <div>
                        <h2 class="font-heading text-2xl font-bold mb-6"><?php echo __('contact.coordTitle'); ?></h2>
                        <div class="space-y-5">
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div>
                                    <p class="font-medium"><?php echo __('contact.email'); ?></p>
                                    <p class="text-sm text-gray-600">gie.sokhna.mai@gmail.com</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <p class="font-medium"><?php echo __('contact.phone'); ?></p>
                                    <p class="text-sm text-gray-600">+221 77 446 04 74</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-4">
                                <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center shrink-0">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div>
                                    <p class="font-medium"><?php echo __('contact.address'); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo __('contact.addressValue'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <a href="https://wa.me/221774460474" target="_blank" rel="noopener noreferrer" 
                       class="inline-flex items-center justify-center w-full px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                        <i class="fab fa-whatsapp mr-2"></i> <?php echo __('contact.whatsapp'); ?>
                    </a>
                    <div>
                        <h3 class="font-heading font-semibold mb-3"><?php echo __('contact.followUs'); ?></h3>
                        <div class="flex gap-3">
                            <a href="https://www.facebook.com/giesokhnama" target="_blank" rel="noopener noreferrer" 
                               class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center hover:bg-primary-200 transition-colors">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://www.instagram.com/giesokhnama" target="_blank" rel="noopener noreferrer" 
                               class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center hover:bg-primary-200 transition-colors">
                                <i class="fab fa-instagram"></i>
                            </a>
                            <a href="https://wa.me/221774460474" target="_blank" rel="noopener noreferrer" 
                               class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center hover:bg-primary-200 transition-colors">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="lg:col-span-3 bg-white rounded-lg border border-gray-200 shadow-sm">
                    <div class="p-8">
                        <h2 class="font-heading text-2xl font-bold mb-6"><?php echo __('contact.formTitle'); ?></h2>
                        
                        <?php if ($error): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-4"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-4"><?php echo __('contact.success'); ?></div>
                        <?php endif; ?>

                        <form action="contact_handler.php" method="POST" class="space-y-5">
                            <input type="hidden" name="redirect" value="index.php?page=contact">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium"><?php echo __('contact.fullName'); ?> *</label>
                                    <input type="text" name="name" required maxlength="100" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                           placeholder="<?php echo __('contact.fullName'); ?>">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium"><?php echo __('contact.email'); ?> *</label>
                                    <input type="email" name="email" required maxlength="255" 
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                           placeholder="votre@email.com">
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium"><?php echo __('contact.phone'); ?></label>
                                    <input type="tel" name="phone" maxlength="50"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                           placeholder="+221 XX XXX XX XX">
                                </div>
                                <div class="space-y-2">
                                    <label class="block text-sm font-medium"><?php echo __('contact.subject'); ?> *</label>
                                    <input type="text" name="subject" required maxlength="200"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                           placeholder="<?php echo __('contact.subjectPlaceholder'); ?>">
                                </div>
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium"><?php echo __('contact.message'); ?> *</label>
                                <textarea name="message" rows="5" required maxlength="1000"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                          placeholder="<?php echo __('contact.messagePlaceholder'); ?>"></textarea>
                            </div>
                            <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 transition-colors">
                                <i class="fas fa-envelope mr-2"></i> <?php echo __('contact.send'); ?>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
