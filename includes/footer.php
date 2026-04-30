    </main>

    <!-- Footer moderne -->
    <footer class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-white relative overflow-hidden">
        <!-- Pattern décoratif en arrière-plan -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute inset-0 bg-repeat" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23ffffff" fill-opacity="0.4"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <!-- Section principale du footer -->
        <div class="relative z-10 container mx-auto px-4 max-w-7xl">
            <!-- Newsletter et infos principales -->
            <div class="py-12 border-b border-gray-700">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-leaf text-white text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-heading text-2xl font-bold">GIE Sokhna Maï</h3>
                                <p class="text-green-400 text-sm">Produits naturels et artisanaux du Sénégal</p>
                            </div>
                        </div>
                        <p class="text-gray-300 leading-relaxed max-w-lg">
                            Découvrez nos produits authentiques, préparés avec amour par les femmes du GIE Sokhna Maï. 
                            Des confitures artisanales aux jus traditionnels, chaque produit raconte une histoire.
                        </p>
                    </div>
                    
                    <!-- Newsletter -->
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-xl p-6 border border-gray-700">
                        <h4 class="font-semibold text-lg mb-2 flex items-center">
                            <i class="fas fa-envelope text-green-400 mr-2"></i>
                            Restez connecté
                        </h4>
                        <p class="text-gray-400 text-sm mb-4">Recevez nos actualités et offres spéciales</p>
                        <form class="flex flex-col sm:flex-row gap-3">
                            <input type="email" placeholder="Votre email" 
                                   class="flex-1 px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-400 focus:ring-1 focus:ring-green-400">
                            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors font-medium">
                                S'abonner
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sections détaillées -->
            <div class="py-12">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- À propos -->
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center">
                            <i class="fas fa-info-circle text-green-400 mr-2"></i>
                            À propos
                        </h4>
                        <ul class="space-y-3">
                            <li>
                                <a href="?page=a-propos" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Notre histoire
                                </a>
                            </li>
                            <li>
                                <a href="?page=equipe" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Notre équipe
                                </a>
                            </li>
                            <li>
                                <a href="?page=partenaires" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Partenaires
                                </a>
                            </li>
                            <li>
                                <a href="?page=blog" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Blog & Actualités
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Produits -->
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center">
                            <i class="fas fa-shopping-basket text-green-400 mr-2"></i>
                            Nos produits
                        </h4>
                        <ul class="space-y-3">
                            <li>
                                <a href="?page=catalogue" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Tous les produits
                                </a>
                            </li>
                            <li>
                                <a href="?page=catalogue&category=1" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Confitures
                                </a>
                            </li>
                            <li>
                                <a href="?page=catalogue&category=2" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Jus & Boissons
                                </a>
                            </li>
                            <li>
                                <a href="?page=catalogue&category=3" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Produits naturels
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Service client -->
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center">
                            <i class="fas fa-headset text-green-400 mr-2"></i>
                            Service client
                        </h4>
                        <ul class="space-y-3">
                            <li>
                                <a href="?page=contact" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Nous contacter
                                </a>
                            </li>
                            <li>
                                <a href="?page=contact" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Livraison
                                </a>
                            </li>
                            <li>
                                <a href="?page=contact" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    Paiement sécurisé
                                </a>
                            </li>
                            <li>
                                <a href="?page=contact" class="text-gray-300 hover:text-green-400 transition-colors flex items-center">
                                    <i class="fas fa-chevron-right text-xs mr-2 opacity-50"></i>
                                    FAQ
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Contact -->
                    <div>
                        <h4 class="font-semibold text-lg mb-4 flex items-center">
                            <i class="fas fa-phone text-green-400 mr-2"></i>
                            Contact
                        </h4>
                        <div class="space-y-3">
                            <div class="flex items-start">
                                <i class="fas fa-envelope text-green-400 mt-1 mr-3 w-4"></i>
                                <div>
                                    <p class="text-gray-300 text-sm">gie.sokhna.mai@gmail.com</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-phone text-green-400 mt-1 mr-3 w-4"></i>
                                <div>
                                    <p class="text-gray-300 text-sm">+221 77 446 04 74</p>
                                    <p class="text-gray-400 text-xs">Du lundi au samedi, 8h-18h</p>
                                </div>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-green-400 mt-1 mr-3 w-4"></i>
                                <div>
                                    <p class="text-gray-300 text-sm">Dakar, Sénégal</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Réseaux sociaux -->
                        <div class="mt-6">
                            <p class="text-sm text-gray-400 mb-3">Suivez-nous</p>
                            <div class="flex gap-3">
                                <a href="https://www.facebook.com/giesokhnama" target="_blank" rel="noopener noreferrer" 
                                   class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-green-600 transition-all duration-300 group">
                                    <i class="fab fa-facebook-f text-sm group-hover:scale-110 transition-transform"></i>
                                </a>
                                <a href="https://www.instagram.com/giesokhnama" target="_blank" rel="noopener noreferrer" 
                                   class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-green-600 transition-all duration-300 group">
                                    <i class="fab fa-instagram text-sm group-hover:scale-110 transition-transform"></i>
                                </a>
                                <a href="https://wa.me/221774460474" target="_blank" rel="noopener noreferrer" 
                                   class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-green-600 transition-all duration-300 group">
                                    <i class="fab fa-whatsapp text-sm group-hover:scale-110 transition-transform"></i>
                                </a>
                                <a href="#" target="_blank" rel="noopener noreferrer" 
                                   class="w-10 h-10 bg-gray-700 rounded-full flex items-center justify-center hover:bg-green-600 transition-all duration-300 group">
                                    <i class="fab fa-youtube text-sm group-hover:scale-110 transition-transform"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section paiement et confiance -->
            <div class="py-8 border-t border-gray-700">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-center">
                    <div class="flex items-center justify-center gap-3">
                        <div class="w-12 h-12 bg-green-600/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-shield-alt text-green-400"></i>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold">Paiement sécurisé</p>
                            <p class="text-gray-400 text-sm">Transactions protégées</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-center gap-3">
                        <div class="w-12 h-12 bg-green-600/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-truck text-green-400"></i>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold">Livraison rapide</p>
                            <p class="text-gray-400 text-sm">À domicile dans tout le Sénégal</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-center gap-3">
                        <div class="w-12 h-12 bg-green-600/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-leaf text-green-400"></i>
                        </div>
                        <div class="text-left">
                            <p class="font-semibold">Produits naturels</p>
                            <p class="text-gray-400 text-sm">100% artisanaux et locaux</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Copyright et mentions légales -->
            <div class="py-6 border-t border-gray-700">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                    <div class="text-center md:text-left">
                        <p class="text-gray-400 text-sm">
                            © <?php echo date('Y'); ?> GIE Sokhna Maï. Tous droits réservés.
                        </p>
                        <div class="flex gap-4 mt-2">
                            <a href="#" class="text-gray-400 hover:text-green-400 text-sm transition-colors">Mentions légales</a>
                            <a href="#" class="text-gray-400 hover:text-green-400 text-sm transition-colors">Politique de confidentialité</a>
                            <a href="#" class="text-gray-400 hover:text-green-400 text-sm transition-colors">CGV</a>
                        </div>
                    </div>
                    
                    <div class="flex items-center gap-2">
                        <span class="text-gray-400 text-sm">Made with</span>
                        <i class="fas fa-heart text-red-500 text-sm"></i>
                        <span class="text-gray-400 text-sm">in Sénégal</span>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('mobileMenuBtn')?.addEventListener('click', function() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        });
    </script>
</body>
</html>
