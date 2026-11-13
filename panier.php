<?php
/**
 * Page Panier - ROM'S NATURAL
 * Conforme PRD Section 5.1 & 6.1
 */

$pageTitle = "Mon Panier";
require_once __DIR__ . '/includes/header.php';

$cartItems = $_SESSION['cart'] ?? [];
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}
?>

<div class="bg-stone-100/70 border-b border-stone-200 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-3xl font-black text-stone-900">Mon Panier d'achat</h1>
        <p class="text-xs sm:text-sm text-stone-500 mt-1">Vérifiez vos produits avant de finaliser votre commande en mode invité.</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <?php if (empty($cartItems)): ?>
        <div class="bg-white rounded-3xl border border-stone-200 p-12 text-center max-w-xl mx-auto space-y-4">
            <div class="w-20 h-20 bg-emerald-50 text-emerald-800 rounded-full flex items-center justify-center mx-auto">
                <i data-lucide="shopping-bag" class="w-10 h-10"></i>
            </div>
            <h2 class="text-xl font-bold text-stone-900">Votre panier est actuellement vide</h2>
            <p class="text-xs text-stone-500 leading-relaxed">
                Découvrez nos trésors naturels transformés au Tchad (beurres de karité, pommade de chébé, miels purs du Guéra, et douceurs sahéliennes).
            </p>
            <a href="<?= BASE_URL ?>/catalogue.php" class="inline-block px-6 py-3 bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs rounded-xl transition shadow">
                Explorer notre boutique
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Liste des articles -->
            <div class="lg:col-span-8 bg-white rounded-3xl border border-stone-200 p-6 space-y-4 shadow-sm">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                    <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Articles sélectionnés</span>
                    <button onclick="clearFullCart()" class="text-xs text-rose-600 hover:underline font-semibold flex items-center gap-1">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                        <span>Vider le panier</span>
                    </button>
                </div>

                <div class="divide-y divide-stone-100">
                    <?php foreach ($cartItems as $key => $item): 
                        $lineTotal = $item['price'] * $item['quantity'];
                    ?>
                        <div class="py-4 flex flex-col sm:flex-row items-center gap-4">
                            <img src="<?= BASE_URL ?>/assets/images/placeholder.php?name=<?= urlencode($item['name']) ?>" 
                                 alt="<?= htmlspecialchars($item['name']) ?>" 
                                 class="w-20 h-20 object-cover rounded-2xl bg-stone-50 border border-stone-100 flex-shrink-0">
                            
                            <div class="flex-1 min-w-0 text-center sm:text-left">
                                <h3 class="font-bold text-sm text-stone-900 hover:text-emerald-800 transition">
                                    <a href="<?= BASE_URL ?>/produit.php?id=<?= $item['product_id'] ?>">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                </h3>
                                <?php if (!empty($item['variant_name'])): ?>
                                    <p class="text-xs text-emerald-800 font-semibold mt-0.5"><?= htmlspecialchars($item['variant_name']) ?></p>
                                <?php endif; ?>
                                <p class="text-xs text-stone-500 mt-1">Prix unitaire : <?= formatPrice($item['price']) ?></p>
                            </div>

                            <!-- Contrôle Quantité -->
                            <div class="flex items-center border border-stone-200 rounded-xl bg-stone-50 overflow-hidden">
                                <button onclick="updateCartItem('<?= $key ?>', <?= $item['quantity'] - 1 ?>)" class="px-3 py-1.5 text-stone-600 hover:bg-stone-200 font-bold text-xs">-</button>
                                <span class="px-3 text-xs font-bold text-stone-900"><?= $item['quantity'] ?></span>
                                <button onclick="updateCartItem('<?= $key ?>', <?= $item['quantity'] + 1 ?>)" class="px-3 py-1.5 text-stone-600 hover:bg-stone-200 font-bold text-xs">+</button>
                            </div>

                            <!-- Total Ligne -->
                            <div class="text-right sm:w-28">
                                <span class="font-black text-stone-900 text-sm block"><?= formatPrice($lineTotal) ?></span>
                                <button onclick="removeCartItem('<?= $key ?>')" class="text-stone-400 hover:text-rose-600 text-xs transition mt-1">
                                    Supprimer
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-4 border-t border-stone-100 flex justify-between items-center text-xs">
                    <a href="<?= BASE_URL ?>/catalogue.php" class="text-emerald-800 font-bold hover:underline flex items-center gap-1">
                        ← Continuer mes achats
                    </a>
                </div>
            </div>

            <!-- Récapitulatif et passage à la commande -->
            <div class="lg:col-span-4 bg-white rounded-3xl border border-stone-200 p-6 space-y-6 shadow-sm">
                <h3 class="font-bold text-stone-900 text-base">Récapitulatif de la commande</h3>

                <div class="space-y-3 text-xs text-stone-600">
                    <div class="flex justify-between">
                        <span>Sous-total articles :</span>
                        <span class="font-bold text-stone-900 text-sm"><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="flex justify-between text-stone-500">
                        <span>Frais de livraison :</span>
                        <span class="italic font-medium">Calculés à la validation</span>
                    </div>
                    <div class="pt-3 border-t border-stone-100 flex justify-between items-baseline">
                        <span class="font-extrabold text-stone-900 text-sm">Total estimatif :</span>
                        <span class="font-black text-emerald-900 text-xl"><?= formatPrice($subtotal) ?></span>
                    </div>
                </div>

                <div class="p-3 bg-emerald-50 rounded-2xl text-[11px] text-emerald-900 space-y-1">
                    <div class="font-bold flex items-center gap-1">
                        <i data-lucide="shield-check" class="w-3.5 h-3.5 text-emerald-700"></i>
                        <span>Achat express sans compte</span>
                    </div>
                    <p>Vos coordonnées de contact et d'adresse vous seront demandées à l'étape suivante.</p>
                </div>

                <a href="<?= BASE_URL ?>/commande.php" class="block w-full py-4 px-6 bg-emerald-800 hover:bg-emerald-900 text-white font-extrabold text-xs uppercase tracking-wider rounded-2xl transition shadow-lg shadow-emerald-800/20 text-center">
                    Passer la commande →
                </a>

                <div class="text-center pt-2">
                    <span class="text-[11px] text-stone-400">Paiement à la livraison au Tchad ou Mobile Money</span>
                </div>
            </div>

        </div>
    <?php endif; ?>
</div>

<script>
    window.refreshCartPage = function() {
        window.location.reload();
    };

    window.clearFullCart = async function() {
        if (!confirm('Voulez-vous vraiment vider votre panier ?')) return;
        try {
            await fetch('<?= BASE_URL ?>/api/cart.php?action=clear');
            window.location.reload();
        } catch (e) {
            alert('Erreur lors du nettoyage du panier.');
        }
    };
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

