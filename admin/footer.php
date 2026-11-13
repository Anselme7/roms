<?php
/**
 * Pied de page Administration - ROM'S NATURAL
 */
?>
        </main>
    </div>

    <!-- Tiroir Mobile pour Navigation Admin -->
    <div id="admin-mobile-drawer" class="fixed inset-0 z-50 bg-stone-900/50 backdrop-blur-xs hidden">
        <div class="w-64 h-full bg-emerald-950 text-stone-300 flex flex-col p-4 space-y-3">
            <div class="flex items-center justify-between pb-3 border-b border-emerald-900">
                <span class="font-bold text-white text-sm">ROM'S NATURAL ADMIN</span>
                <button id="admin-mobile-close" class="p-1 text-stone-400 hover:text-white">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div class="flex-1 space-y-1 text-xs">
                <a href="<?= BASE_URL ?>/admin/index.php" class="block py-2 px-3 rounded-lg hover:bg-emerald-900">Tableau de bord</a>
                <a href="<?= BASE_URL ?>/admin/orders.php" class="block py-2 px-3 rounded-lg hover:bg-emerald-900">Commandes</a>
                <a href="<?= BASE_URL ?>/admin/products.php" class="block py-2 px-3 rounded-lg hover:bg-emerald-900">Produits</a>
                <a href="<?= BASE_URL ?>/admin/categories.php" class="block py-2 px-3 rounded-lg hover:bg-emerald-900">Familles / Catégories</a>
                <a href="<?= BASE_URL ?>/admin/stock.php" class="block py-2 px-3 rounded-lg hover:bg-emerald-900">Stocks & Lots</a>
                <a href="<?= BASE_URL ?>/admin/delivery_zones.php" class="block py-2 px-3 rounded-lg hover:bg-emerald-900">Zones Livraison Tchad</a>
                <a href="<?= BASE_URL ?>/admin/users.php" class="block py-2 px-3 rounded-lg hover:bg-emerald-900">Utilisateurs</a>
                <a href="<?= BASE_URL ?>/admin/settings.php" class="block py-2 px-3 rounded-lg hover:bg-emerald-900">Paramètres</a>
                <hr class="border-emerald-900 my-2">
                <a href="<?= BASE_URL ?>/index.php" target="_blank" class="block py-2 px-3 rounded-lg text-amber-300">Voir la boutique</a>
                <a href="<?= BASE_URL ?>/admin/logout.php" class="block py-2 px-3 rounded-lg text-rose-400">Déconnexion</a>
            </div>
        </div>
    </div>

    <script>
        if (window.lucide) window.lucide.createIcons();

        const mobileToggle = document.getElementById('admin-mobile-toggle');
        const mobileDrawer = document.getElementById('admin-mobile-drawer');
        const mobileClose = document.getElementById('admin-mobile-close');

        if (mobileToggle && mobileDrawer && mobileClose) {
            mobileToggle.addEventListener('click', () => mobileDrawer.classList.remove('hidden'));
            mobileClose.addEventListener('click', () => mobileDrawer.classList.add('hidden'));
            mobileDrawer.addEventListener('click', (e) => {
                if (e.target === mobileDrawer) mobileDrawer.classList.add('hidden');
            });
        }
    </script>
</body>
</html>

