/**
 * Système de gestion de panier pour GIE Sokhna Maï
 * Utilise localStorage pour la persistance des données
 */

class CartManager {
    constructor() {
        this.cart = this.loadCart();
        this.init();
    }

    init() {
        this.updateCartUI();
        this.setupEventListeners();
    }

    /**
     * Charger le panier depuis localStorage
     */
    loadCart() {
        try {
            const savedCart = localStorage.getItem('sokhnaMaiCart');
            return savedCart ? JSON.parse(savedCart) : [];
        } catch (error) {
            console.error('Erreur lors du chargement du panier:', error);
            return [];
        }
    }

    /**
     * Sauvegarder le panier dans localStorage
     */
    saveCart() {
        try {
            localStorage.setItem('sokhnaMaiCart', JSON.stringify(this.cart));
            this.updateCartUI();
        } catch (error) {
            console.error('Erreur lors de la sauvegarde du panier:', error);
        }
    }

    /**
     * Ajouter un produit au panier
     */
    addToCart(product, quantity = 1) {
        const existingItem = this.cart.find(item => item.id === product.id);
        
        if (existingItem) {
            existingItem.quantity += quantity;
        } else {
            this.cart.push({
                id: product.id,
                name: product.name,
                price: product.price,
                weight: product.weight,
                image: product.image,
                category: product.category,
                quantity: quantity,
                addedAt: new Date().toISOString()
            });
        }
        
        this.saveCart();
        this.showNotification('Produit ajouté au panier', 'success');
        this.animateCartIcon();
    }

    /**
     * Mettre à jour la quantité d'un produit
     */
    updateQuantity(productId, quantity) {
        const item = this.cart.find(item => item.id === productId);
        
        if (item) {
            if (quantity <= 0) {
                this.removeFromCart(productId);
            } else {
                item.quantity = quantity;
                this.saveCart();
            }
        }
    }

    /**
     * Supprimer un produit du panier
     */
    removeFromCart(productId) {
        this.cart = this.cart.filter(item => item.id !== productId);
        this.saveCart();
        this.showNotification('Produit retiré du panier', 'info');
    }

    /**
     * Vider le panier
     */
    clearCart() {
        this.cart = [];
        this.saveCart();
        this.showNotification('Panier vidé', 'info');
    }
    
    /**
     * Vider le panier après commande réussie
     */
    clearCartAfterOrder() {
        this.cart = [];
        this.saveCart();
        // Pas de notification pour éviter de confusion après redirection
    }

    /**
     * Obtenir le nombre total d'articles
     */
    getTotalItems() {
        return this.cart.reduce((total, item) => total + item.quantity, 0);
    }

    /**
     * Obtenir le prix total
     */
    getTotalPrice() {
        return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    /**
     * Mettre à jour l'interface du panier
     */
    updateCartUI() {
        const itemCount = this.getTotalItems();
        
        // Mettre à jour le compteur desktop
        const cartCounter = document.getElementById('cartCounter');
        if (cartCounter) {
            cartCounter.textContent = itemCount;
            cartCounter.style.display = itemCount > 0 ? 'flex' : 'none';
        }
        
        // Mettre à jour le compteur mobile
        const cartCounterMobile = document.getElementById('cartCounterMobile');
        if (cartCounterMobile) {
            cartCounterMobile.textContent = itemCount;
            cartCounterMobile.style.display = itemCount > 0 ? 'flex' : 'none';
        }

        // Mettre à jour la page panier si on est dessus
        if (window.location.pathname.includes('panier') || window.location.search.includes('page=panier')) {
            this.updateCartPage();
        }
    }

    /**
     * Mettre à jour la page panier
     */
    updateCartPage() {
        const cartItems = document.getElementById('cartItems');
        const cartEmpty = document.getElementById('cartEmpty');
        const cartSummary = document.getElementById('cartSummary');
        
        if (!cartItems) return;

        if (this.cart.length === 0) {
            cartItems.innerHTML = '';
            cartEmpty.style.display = 'block';
            cartSummary.style.display = 'none';
        } else {
            cartEmpty.style.display = 'none';
            cartSummary.style.display = 'block';
            this.renderCartItems();
            this.updateCartSummary();
        }
    }

    /**
     * Afficher les articles du panier
     */
    renderCartItems() {
        const cartItems = document.getElementById('cartItems');
        if (!cartItems) return;

        cartItems.innerHTML = this.cart.map(item => `
            <div class="cart-item bg-white rounded-lg border border-gray-200 p-4 mb-4" data-product-id="${item.id}">
                <div class="flex items-center gap-4">
                    <img src="${this.getImageSrc(item.image)}" alt="${item.name}" class="w-20 h-20 rounded-lg object-cover" onerror="this.src='assets/images/default-product.jpg'">
                    <div class="flex-1">
                        <h3 class="font-semibold text-lg">${item.name}</h3>
                        <p class="text-sm text-gray-600">${item.category} • ${item.weight}</p>
                        <p class="text-primary-600 font-bold">${this.formatPrice(item.price)}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex items-center gap-2">
                            <button onclick="cartManager.updateQuantity(${item.id}, ${item.quantity - 1})"
                                    class="w-8 h-8 rounded-full border border-gray-300 hover:bg-gray-100 transition-colors"
                                    ${item.quantity <= 1 ? 'disabled' : ''}>
                                <i class="fas fa-minus text-xs"></i>
                            </button>
                            <span class="w-12 text-center font-medium">${item.quantity}</span>
                            <button onclick="cartManager.updateQuantity(${item.id}, ${item.quantity + 1})"
                                    class="w-8 h-8 rounded-full border border-gray-300 hover:bg-gray-100 transition-colors">
                                <i class="fas fa-plus text-xs"></i>
                            </button>
                        </div>
                        <button onclick="cartManager.removeFromCart(${item.id})"
                                class="text-red-500 hover:text-red-700 transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }

    /**
     * Mettre à jour le résumé du panier
     */
    updateCartSummary() {
        const summary = document.getElementById('cartSummary');
        if (!summary) return;

        const subtotal = this.getTotalPrice();
        const shipping = subtotal > 5000 ? 0 : 1500; // Livraison gratuite à partir de 5000 FCFA
        const total = subtotal + shipping;

        summary.innerHTML = `
            <div class="bg-white rounded-lg border border-gray-200 p-6">
                <h3 class="text-lg font-semibold mb-4">Résumé de la commande</h3>
                <div class="space-y-3 mb-4">
                    <div class="flex justify-between">
                        <span>Sous-total (${this.getTotalItems()} articles)</span>
                        <span>${this.formatPrice(subtotal)}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Livraison</span>
                        <span>${shipping === 0 ? 'Gratuite' : this.formatPrice(shipping)}</span>
                    </div>
                    ${shipping === 0 ? '<p class="text-sm text-green-600">Livraison offerte!</p>' : '<p class="text-sm text-gray-500">Livraison gratuite à partir de 5 000 FCFA</p>'}
                    <div class="border-t pt-3">
                        <div class="flex justify-between font-bold text-lg">
                            <span>Total</span>
                            <span class="text-primary-600">${this.formatPrice(total)}</span>
                        </div>
                    </div>
                </div>
                <button onclick="cartManager.proceedToCheckout()" 
                        class="w-full bg-primary-600 text-white py-3 rounded-lg hover:bg-primary-700 transition-colors font-medium">
                    Passer la commande
                </button>
                <button onclick="cartManager.clearCart()" 
                        class="w-full mt-2 border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 transition-colors">
                    Vider le panier
                </button>
            </div>
        `;
    }

    /**
     * Procéder au checkout
     */
    proceedToCheckout() {
        const cartData = JSON.stringify(this.cart);
        const total = this.getTotalPrice();
        
        // Créer un formulaire temporaire pour envoyer les données en POST
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '?page=checkout';
        
        // Ajouter les données du panier
        const cartInput = document.createElement('input');
        cartInput.type = 'hidden';
        cartInput.name = 'cart_data';
        cartInput.value = cartData;
        form.appendChild(cartInput);
        
        // Ajouter le total
        const totalInput = document.createElement('input');
        totalInput.type = 'hidden';
        totalInput.name = 'total_amount';
        totalInput.value = total;
        form.appendChild(totalInput);
        
        // Soumettre le formulaire
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    /**
     * Configurer les écouteurs d'événements
     */
    setupEventListeners() {
        // Écouter les clics sur les boutons d'ajout au panier
        document.addEventListener('click', (e) => {
            if (e.target.matches('.add-to-cart-btn')) {
                e.preventDefault();
                const productData = JSON.parse(e.target.dataset.product);
                const quantity = parseInt(e.target.dataset.quantity || 1);
                this.addToCart(productData, quantity);
            }
        });
    }

    /**
     * Afficher une notification
     */
    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-20 right-4 z-50 px-4 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300 ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-blue-500 text-white'
        }`;
        notification.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Animation d'entrée
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 100);
        
        // Animation de sortie et suppression
        setTimeout(() => {
            notification.style.transform = 'translateX(100%)';
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }

    /**
     * Animer l'icône du panier
     */
    animateCartIcon() {
        const cartIcon = document.getElementById('cartIcon');
        if (cartIcon) {
            cartIcon.classList.add('animate-bounce');
            setTimeout(() => {
                cartIcon.classList.remove('animate-bounce');
            }, 1000);
        }
    }

    /**
     * Formater le prix
     */
    formatPrice(price) {
        return new Intl.NumberFormat('fr-FR').format(price) + ' FCFA';
    }

    /**
     * Obtenir le chemin correct de l'image
     */
    getImageSrc(imageUrl) {
        if (!imageUrl) return 'assets/images/default-product.jpg';

        // Si c'est une URL externe (commence par http:// ou https://)
        if (imageUrl.startsWith('http://') || imageUrl.startsWith('https://')) {
            return imageUrl;
        }

        // Si le chemin commence déjà par 'assets/', le laisser tel quel
        if (imageUrl.startsWith('assets/')) {
            return imageUrl;
        }

        // Sinon, ajouter 'assets/' devant
        return 'assets/' + imageUrl;
    }
}

// Initialiser le gestionnaire de panier
let cartManager;

document.addEventListener('DOMContentLoaded', function() {
    cartManager = new CartManager();
    
    // Vérifier si nous revenons de WhatsApp après une commande
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('order_success') === 'true') {
        cartManager.clearCartAfterOrder();
        // Afficher un message de succès
        cartManager.showNotification('Commande confirmée! Merci pour votre achat.', 'success');
        // Nettoyer l'URL
        window.history.replaceState({}, '', window.location.pathname);
    }
});

// Rendre l'instance disponible globalement
window.cartManager = cartManager;
