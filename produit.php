<?php
/**
 * Fiche Détail Produit - ROM'S NATURAL
 * Conforme PRD Section 4.1 & 5.1
 */

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/functions.php';

$productId = (int)($_GET['id'] ?? 0);
$slug = trim($_GET['slug'] ?? '');

if ($productId > 0) {
    $stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug 
        FROM products p 
        LEFT JOIN categories c ON p.categorie_id = c.id 
        WHERE p.id = ? AND p.disponible = 1");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
} elseif (!empty($slug)) {
    $stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug 
        FROM products p 
        LEFT JOIN categories c ON p.categorie_id = c.id 
        WHERE p.slug = ? AND p.disponible = 1");
    $stmt->execute([$slug]);
    $product = $stmt->fetch();
} else {
    $product = null;
}

if (!$product) {
    header('Location: ' . BASE_URL . '/catalogue.php');
    exit;
}

$pageTitle = $product['nom'];
$activeNav = "catalogue";
require_once __DIR__ . '/includes/header.php';

// Récupération des variantes
$stmtVar = $pdo->prepare("SELECT * FROM product_variants WHERE produit_id = ? ORDER BY id ASC");
$stmtVar->execute([$product['id']]);
$variants = $stmtVar->fetchAll();

// Produits apparentés / recommandés de la même catégorie
$stmtRelated = $pdo->prepare("SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug 
    FROM products p 
    LEFT JOIN categories c ON p.categorie_id = c.id 
    WHERE p.categorie_id = ? AND p.id != ? AND p.disponible = 1 
    ORDER BY p.id DESC LIMIT 4");
$stmtRelated->execute([$product['categorie_id'], $product['id']]);
$relatedProducts = $stmtRelated->fetchAll();

$currentPrice = ($product['prix_promo'] > 0) ? $product['prix_promo'] : $product['prix'];
?>

<!-- Fil d'Ariane -->
<div class="bg-stone-100/70 border-b border-stone-200 py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <nav class="flex text-xs text-stone-500 gap-2 items-center flex-wrap">
            <a href="<?= BASE_URL ?>/index.php" class="hover:text-emerald-800">Accueil</a>
            <span>/</span>
            <a href="<?= BASE_URL ?>/catalogue.php" class="hover:text-emerald-800">Boutique</a>
            <span>/</span>
            <a href="<?= BASE_URL ?>/catalogue.php?cat=<?= urlencode($product['categorie_slug']) ?>" class="hover:text-emerald-800">
                <?= htmlspecialchars($product['categorie_nom']) ?>
            </a>
            <span>/</span>
            <span class="text-stone-800 font-semibold truncate max-w-xs"><?= htmlspecialchars($product['nom']) ?></span>
        </nav>
    </div>
</div>

<!-- Section Principale Fiche Produit -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
        
        <!-- Galerie Photo -->
        <div class="lg:col-span-6 space-y-4">
            <div class="relative aspect-square rounded-3xl overflow-hidden bg-white border border-stone-200 shadow-sm flex items-center justify-center">
                <img id="main-product-image" 
                     src="<?= getProductImageUrl($product['photo_principale'], $product['categorie_slug']) ?>" 
                     alt="<?= htmlspecialchars($product['nom']) ?>" 
                     class="w-full h-full object-cover">
                
                <?php if ($product['prix_promo'] > 0): ?>
                    <span class="absolute top-4 left-4 bg-rose-600 text-white text-xs font-black uppercase px-3 py-1 rounded-full shadow">
                        Promotion
                    </span>
                <?php endif; ?>

                <?php if ($product['stock'] <= 0): ?>
                    <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-xs flex items-center justify-center">
                        <span class="bg-stone-900 text-white text-sm font-bold px-4 py-2 rounded-full border border-stone-700">
                            Actuellement en rupture de stock
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Badges Terroir & Authenticité -->
            <div class="grid grid-cols-3 gap-3 text-center">
                <div class="p-3 bg-white rounded-2xl border border-stone-200 text-xs">
                    <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-700 mx-auto mb-1"></i>
                    <span class="font-bold text-stone-800 block">100% Naturel</span>
                    <span class="text-[10px] text-stone-500">Sans additif toxique</span>
                </div>
                <div class="p-3 bg-white rounded-2xl border border-stone-200 text-xs">
                    <i data-lucide="map-pin" class="w-4 h-4 text-emerald-700 mx-auto mb-1"></i>
                    <span class="font-bold text-stone-800 block">Origine Tchad</span>
                    <span class="text-[10px] text-stone-500">Terroirs locaux</span>
                </div>
                <div class="p-3 bg-white rounded-2xl border border-stone-200 text-xs">
                    <i data-lucide="truck" class="w-4 h-4 text-emerald-700 mx-auto mb-1"></i>
                    <span class="font-bold text-stone-800 block">Livraison Directe</span>
                    <span class="text-[10px] text-stone-500">À votre porte</span>
                </div>
            </div>
        </div>

        <!-- Détails, Achat & Variantes -->
        <div class="lg:col-span-6 space-y-6">
            
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-amber-700 bg-amber-50 px-3 py-1 rounded-full border border-amber-200">
                    <?= htmlspecialchars($product['categorie_nom']) ?>
                </span>
                <h1 class="text-2xl sm:text-3xl font-black text-stone-900 mt-3 leading-tight">
                    <?= htmlspecialchars($product['nom']) ?>
                </h1>
                <p class="text-stone-600 text-sm mt-3 leading-relaxed">
                    <?= htmlspecialchars($product['description_courte']) ?>
                </p>
            </div>

            <!-- Prix -->
            <div class="p-4 bg-stone-50 rounded-2xl border border-stone-200 flex items-baseline gap-4">
                <?php if ($product['prix_promo'] > 0): ?>
                    <span class="text-2xl sm:text-3xl font-black text-rose-600" id="display-price">
                        <?= formatPrice($product['prix_promo']) ?>
                    </span>
                    <span class="text-sm text-stone-400 line-through">
                        <?= formatPrice($product['prix']) ?>
                    </span>
                    <span class="text-xs font-bold text-rose-700 bg-rose-100 px-2 py-0.5 rounded-md">
                        Économisez <?= formatPrice($product['prix'] - $product['prix_promo']) ?>
                    </span>
                <?php else: ?>
                    <span class="text-2xl sm:text-3xl font-black text-stone-900" id="display-price">
                        <?= formatPrice($product['prix']) ?>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Formulaire de commande & Choix variante -->
            <div class="space-y-4 pt-2">
                
                <!-- Variantes si disponibles (PRD Section 4.1) -->
                <?php if (!empty($variants)): ?>
                    <div>
                        <label for="product-variant-select" class="block text-xs font-bold text-stone-700 uppercase tracking-wider mb-2">
                            Choisir un format / conditionnement :
                        </label>
                        <select id="product-variant-select" class="w-full p-3 rounded-xl border border-stone-300 bg-white text-sm font-semibold focus:outline-none focus:border-emerald-700">
                            <?php foreach ($variants as $v): ?>
                                <option value="<?= $v['id'] ?>" data-price="<?= $v['prix'] > 0 ? $v['prix'] : $currentPrice ?>">
                                    <?= htmlspecialchars($v['titre']) ?> (<?= htmlspecialchars($v['format_poids']) ?>) 
                                    <?= $v['prix'] > 0 ? ' - ' . formatPrice($v['prix']) : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Quantité & Ajout Panier -->
                <div class="flex items-center gap-4">
                    <div class="w-32">
                        <label for="product-quantity-input" class="block text-[11px] font-bold text-stone-600 uppercase mb-1">Quantité</label>
                        <div class="flex items-center border border-stone-300 rounded-xl bg-white overflow-hidden">
                            <button type="button" onclick="const input = document.getElementById('product-quantity-input'); if(input.value > 1) input.value--" class="px-3 py-2 text-stone-600 hover:bg-stone-100 font-bold">-</button>
                            <input type="number" id="product-quantity-input" value="1" min="1" max="<?= $product['stock'] ?>" class="w-full text-center text-sm font-bold focus:outline-none py-2">
                            <button type="button" onclick="const input = document.getElementById('product-quantity-input'); if(input.value < <?= $product['stock'] ?>) input.value++" class="px-3 py-2 text-stone-600 hover:bg-stone-100 font-bold">+</button>
                        </div>
                    </div>

                    <div class="flex-1 pt-4">
                        <?php if ($product['stock'] > 0): ?>
                            <button class="add-to-cart-btn w-full py-3.5 px-6 rounded-2xl bg-emerald-800 hover:bg-emerald-900 text-white font-extrabold text-sm transition shadow-lg shadow-emerald-800/20 flex items-center justify-center gap-2"
                                    data-product-id="<?= $product['id'] ?>">
                                <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                                <span>Ajouter au panier</span>
                            </button>
                        <?php else: ?>
                            <button disabled class="w-full py-3.5 px-6 rounded-2xl bg-stone-200 text-stone-400 font-bold text-sm cursor-not-allowed">
                                Rupture temporaire de stock
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bouton WhatsApp Officiel Direct (PRD Section 11) -->
                <a href="<?= getWhatsAppProductLink($product['nom'], $product['id']) ?>" 
                   target="_blank" rel="noopener noreferrer"
                   class="w-full py-3 px-4 rounded-2xl border-2 border-[#25D366] text-[#128C7E] hover:bg-[#25D366]/10 font-bold text-xs transition flex items-center justify-center gap-2">
                    <i data-lucide="message-circle" class="w-4 h-4 fill-[#25D366] stroke-none"></i>
                    <span>Commander ou poser une question directement via WhatsApp</span>
                </a>

                <!-- Indication Stock -->
                <div class="text-xs flex items-center gap-2 pt-1">
                    <?php if ($product['stock'] > 10): ?>
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                        <span class="text-emerald-800 font-medium">En stock - Expédition immédiate</span>
                    <?php elseif ($product['stock'] > 0): ?>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <span class="text-amber-800 font-medium">Plus que <?= $product['stock'] ?> articles en stock !</span>
                    <?php else: ?>
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span>
                        <span class="text-rose-700 font-medium">En cours de réapprovisionnement</span>
                    <?php endif; ?>
                </div>

            </div>

            <hr class="border-stone-200">

            <!-- Onglets Fiche Technique / Composition / Origine (PRD Section 4.1) -->
            <div class="space-y-4">
                <h3 class="text-sm font-black uppercase tracking-wider text-stone-900">Informations détaillées</h3>
                
                <div class="space-y-3 text-xs leading-relaxed text-stone-600">
                    <?php if (!empty($product['origine'])): ?>
                        <div class="p-3 bg-stone-50 rounded-xl border border-stone-200">
                            <span class="font-bold text-stone-900 block mb-0.5">Origine & Producteur :</span>
                            <p><?= htmlspecialchars($product['origine']) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['composition'])): ?>
                        <div class="p-3 bg-stone-50 rounded-xl border border-stone-200">
                            <span class="font-bold text-stone-900 block mb-0.5">Composition & Ingrédients :</span>
                            <p><?= nl2br(htmlspecialchars($product['composition'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['conseils_utilisation'])): ?>
                        <div class="p-3 bg-stone-50 rounded-xl border border-stone-200">
                            <span class="font-bold text-stone-900 block mb-0.5">Conseils d'utilisation :</span>
                            <p><?= nl2br(htmlspecialchars($product['conseils_utilisation'])) ?></p>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($product['precautions'])): ?>
                        <div class="p-3 bg-amber-50/70 rounded-xl border border-amber-200 text-amber-900">
                            <span class="font-bold block mb-0.5">Précautions & Conservation :</span>
                            <p><?= nl2br(htmlspecialchars($product['precautions'])) ?></p>
                            <?php if (!empty($product['conservation'])): ?>
                                <p class="mt-1 font-semibold"><?= htmlspecialchars($product['conservation']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="pt-2 text-stone-700">
                        <h4 class="font-bold text-xs uppercase tracking-wider text-stone-900 mb-2">Description complète :</h4>
                        <div class="prose prose-sm text-stone-600">
                            <?= nl2br(htmlspecialchars($product['description'])) ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Section Produits Associés (PRD Section 4.2) -->
    <?php if (!empty($relatedProducts)): ?>
        <section class="mt-20 pt-12 border-t border-stone-200">
            <h2 class="text-xl sm:text-2xl font-black text-stone-900 mb-6">Vous pourriez aussi aimer</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <?php foreach ($relatedProducts as $rel): ?>
                    <div class="product-card bg-white rounded-3xl border border-stone-200 overflow-hidden flex flex-col justify-between">
                        <div class="relative aspect-square overflow-hidden bg-stone-50">
                            <a href="<?= BASE_URL ?>/produit.php?id=<?= $rel['id'] ?>">
                                <img src="<?= getProductImageUrl($rel['photo_principale'], $rel['categorie_slug']) ?>" 
                                     alt="<?= htmlspecialchars($rel['nom']) ?>" 
                                     class="w-full h-full object-cover">
                            </a>
                        </div>
                        <div class="p-4 space-y-2">
                            <h4 class="font-bold text-stone-900 text-xs line-clamp-1 hover:text-emerald-800">
                                <a href="<?= BASE_URL ?>/produit.php?id=<?= $rel['id'] ?>">
                                    <?= htmlspecialchars($rel['nom']) ?>
                                </a>
                            </h4>
                            <p class="text-xs font-black text-emerald-900"><?= formatPrice($rel['prix']) ?></p>
                            <a href="<?= BASE_URL ?>/produit.php?id=<?= $rel['id'] ?>" class="block text-center py-2 px-3 bg-stone-100 hover:bg-stone-200 text-stone-800 text-[11px] font-bold rounded-xl transition">
                                Voir le produit
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<script>
    // Mise à jour dynamique du prix si une variante est sélectionnée
    const variantSelect = document.getElementById('product-variant-select');
    const displayPrice = document.getElementById('display-price');
    if (variantSelect && displayPrice) {
        variantSelect.addEventListener('change', () => {
            const selectedOpt = variantSelect.options[variantSelect.selectedIndex];
            const price = parseFloat(selectedOpt.dataset.price);
            if (!isNaN(price) && price > 0) {
                displayPrice.textContent = new Intl.NumberFormat('fr-FR').format(price) + ' FCFA';
            }
        });
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

