/**
 * Logique Frontend pour ROM'S NATURAL
 */

document.addEventListener('DOMContentLoaded', () => {
    // Initialise les icônes Lucide si disponibles
    if (window.lucide) {
        window.lucide.createIcons();
    }

    // Gestion du menu mobile
    const mobileMenuBtn = document.getElementById('mobile-menu-toggle');
    const mobileNav = document.getElementById('mobile-nav');
    if (mobileMenuBtn && mobileNav) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileNav.classList.toggle('hidden');
        });
    }

    // Gestion du drawer panier
    const cartToggleBtns = document.querySelectorAll('.cart-toggle-btn');
    const cartDrawer = document.getElementById('cart-drawer');
    const cartBackdrop = document.getElementById('cart-backdrop');
    const closeCartBtn = document.getElementById('close-cart-btn');

    function openCart() {
        if (!cartDrawer || !cartBackdrop) return;
        cartDrawer.classList.remove('translate-x-full');
        cartBackdrop.classList.remove('hidden');
        setTimeout(() => cartBackdrop.classList.add('opacity-100'), 10);
        refreshCartDrawer();
    }

    function closeCart() {
        if (!cartDrawer || !cartBackdrop) return;
        cartDrawer.classList.add('translate-x-full');
        cartBackdrop.classList.remove('opacity-100');
        setTimeout(() => cartBackdrop.classList.add('hidden'), 300);
    }

    cartToggleBtns.forEach(btn => btn.addEventListener('click', (e) => {
        e.preventDefault();
        openCart();
    }));

    if (closeCartBtn) closeCartBtn.addEventListener('click', closeCart);
    if (cartBackdrop) cartBackdrop.addEventListener('click', closeCart);

    // Écouteur global pour les boutons "Ajouter au panier"
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.add-to-cart-btn');
        if (!btn) return;

        e.preventDefault();
        const productId = btn.dataset.productId;
        const variantSelect = document.getElementById('product-variant-select');
        const quantityInput = document.getElementById('product-quantity-input');

        const variantId = variantSelect ? variantSelect.value : (btn.dataset.variantId || '');
        const quantity = quantityInput ? quantityInput.value : (btn.dataset.quantity || 1);

        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = `<span class="inline-block animate-spin mr-2">⏳</span> Ajout en cours...`;

        try {
            const formData = new FormData();
            formData.append('action', 'add');
            formData.append('product_id', productId);
            if (variantId) formData.append('variant_id', variantId);
            formData.append('quantity', quantity);

            const res = await fetch(`${window.BASE_URL || ''}/api/cart.php`, {
                method: 'POST',
                body: formData
            });

            const data = await res.json();
            if (data.success) {
                updateCartBadges(data.cart_count);
                showToast('Article ajouté au panier !', 'success');
                openCart();
            } else {
                showToast(data.error || 'Erreur lors de l’ajout au panier', 'error');
            }
        } catch (err) {
            showToast('Une erreur réseau est survenue.', 'error');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
            if (window.lucide) window.lucide.createIcons();
        }
    });

    // Mise à jour de l'affichage du tiroir panier
    window.refreshCartDrawer = async function() {
        const itemsContainer = document.getElementById('cart-drawer-items');
        const subtotalEl = document.getElementById('cart-drawer-subtotal');
        if (!itemsContainer) return;

        itemsContainer.innerHTML = `<div class="p-8 text-center text-gray-500">Chargement de votre panier...</div>`;

        try {
            const res = await fetch(`${window.BASE_URL || ''}/api/cart.php?action=get`);
            const data = await res.json();

            if (data.success) {
                updateCartBadges(data.cart_count);
                if (subtotalEl) subtotalEl.textContent = data.cart_total_formatted;

                if (!data.items || data.items.length === 0) {
                    itemsContainer.innerHTML = `
                        <div class="py-16 text-center">
                            <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-700">
                                <i data-lucide="shopping-bag" class="w-8 h-8"></i>
                            </div>
                            <p class="font-semibold text-gray-700">Votre panier est vide</p>
                            <p class="text-xs text-gray-500 mt-1">Découvrez les trésors naturels de notre terroir tchadien !</p>
                            <a href="${window.BASE_URL || ''}/catalogue.php" class="mt-4 inline-block px-5 py-2.5 bg-emerald-800 text-white text-sm font-medium rounded-lg hover:bg-emerald-900 transition">
                                Explorer la boutique
                            </a>
                        </div>
                    `;
                    const checkoutBtn = document.getElementById('cart-drawer-checkout-btn');
                    if (checkoutBtn) checkoutBtn.classList.add('pointer-events-none', 'opacity-50');
                } else {
                    const checkoutBtn = document.getElementById('cart-drawer-checkout-btn');
                    if (checkoutBtn) checkoutBtn.classList.remove('pointer-events-none', 'opacity-50');

                    itemsContainer.innerHTML = data.items.map(item => {
                        const key = item.product_id + (item.variant_id ? '_' + item.variant_id : '');
                        const imgUrl = `${window.BASE_URL || ''}/assets/images/placeholder.php?name=${encodeURIComponent(item.name)}`;
                        return `
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                                <img src="${imgUrl}" alt="${item.name}" class="w-16 h-16 object-cover rounded-lg flex-shrink-0 bg-white">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">${item.name}</h4>
                                    ${item.variant_name ? `<p class="text-xs text-emerald-700 font-medium">${item.variant_name}</p>` : ''}
                                    <p class="text-xs font-bold text-gray-900 mt-0.5">${new Intl.NumberFormat('fr-FR').format(item.price)} FCFA</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <button onclick="updateCartItem('${key}', ${item.quantity - 1})" class="w-6 h-6 rounded bg-white border border-gray-200 flex items-center justify-center text-xs hover:bg-gray-100 font-bold">-</button>
                                        <span class="text-xs font-semibold px-2">${item.quantity}</span>
                                        <button onclick="updateCartItem('${key}', ${item.quantity + 1})" class="w-6 h-6 rounded bg-white border border-gray-200 flex items-center justify-center text-xs hover:bg-gray-100 font-bold">+</button>
                                    </div>
                                </div>
                                <button onclick="removeCartItem('${key}')" class="text-gray-400 hover:text-red-500 p-1 transition" title="Supprimer">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </div>
                        `;
                    }).join('');
                }
            }
        } catch (e) {
            itemsContainer.innerHTML = `<p class="text-center text-red-500 p-4">Erreur lors de la mise à jour.</p>`;
        } finally {
            if (window.lucide) window.lucide.createIcons();
        }
    };

    window.updateCartItem = async function(key, quantity) {
        try {
            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('key', key);
            formData.append('quantity', quantity);

            const res = await fetch(`${window.BASE_URL || ''}/api/cart.php`, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                refreshCartDrawer();
                if (window.refreshCartPage) window.refreshCartPage();
            } else {
                showToast(data.error || 'Erreur', 'error');
            }
        } catch (e) {
            showToast('Erreur de mise à jour.', 'error');
        }
    };

    window.removeCartItem = async function(key) {
        try {
            const formData = new FormData();
            formData.append('action', 'remove');
            formData.append('key', key);

            const res = await fetch(`${window.BASE_URL || ''}/api/cart.php`, { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                refreshCartDrawer();
                if (window.refreshCartPage) window.refreshCartPage();
                showToast('Article retiré', 'info');
            }
        } catch (e) {
            showToast('Erreur', 'error');
        }
    };

    function updateCartBadges(count) {
        document.querySelectorAll('.cart-badge-count').forEach(el => {
            el.textContent = count;
            if (count > 0) {
                el.classList.remove('hidden');
            } else {
                el.classList.add('hidden');
            }
        });
    }

    // Affichage des notifications Toast
    window.showToast = function(message, type = 'info') {
        const existing = document.querySelector('.rom-toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = 'rom-toast flex items-center gap-3 px-4 py-3 rounded-xl shadow-xl text-sm font-medium z-50';

        if (type === 'success') {
            toast.classList.add('bg-emerald-900', 'text-white', 'border', 'border-emerald-700');
        } else if (type === 'error') {
            toast.classList.add('bg-rose-900', 'text-white', 'border', 'border-rose-700');
        } else {
            toast.classList.add('bg-gray-900', 'text-white', 'border', 'border-gray-700');
        }

        toast.innerHTML = `<span>${message}</span>`;
        document.body.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    };
});

