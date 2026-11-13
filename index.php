<?php
/**
 * Page d'Accueil - ROM'S NATURAL
 * Conforme PRD Sections 1, 2, 3, 5
 */

$pageTitle = "Accueil";
$activeNav = "home";
require_once __DIR__ . '/includes/header.php';

// Récupération des catégories avec nombre de produits
$stmtCats = $pdo->query("SELECT c.*, COUNT(p.id) as total_prods 
    FROM categories c 
    LEFT JOIN products p ON p.categorie_id = c.id AND p.disponible = 1 
    WHERE c.statut = 1 
    GROUP BY c.id 
    ORDER BY c.ordre ASC");
$categories = $stmtCats->fetchAll();

// Produits en vedette
$stmtFeatured = $pdo->query("SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug 
    FROM products p 
    LEFT JOIN categories c ON p.categorie_id = c.id 
    WHERE p.en_vedette = 1 AND p.disponible = 1 
    ORDER BY p.id DESC LIMIT 6");
$featuredProducts = $stmtFeatured->fetchAll();

// Nouveautés
$stmtNew = $pdo->query("SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug 
    FROM products p 
    LEFT JOIN categories c ON p.categorie_id = c.id 
    WHERE p.nouveaute = 1 AND p.disponible = 1 
    ORDER BY p.id DESC LIMIT 4");
$newProducts = $stmtNew->fetchAll();
?>

<!-- Hero Banner Principal -->
<section class="relative overflow-hidden bg-gradient-to-b from-emerald-950 via-emerald-900 to-emerald-850 text-white py-16 md:py-24">
    <!-- Motif géométrique discret de fond -->
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
            <defs>
                <pattern id="hero-pattern" width="60" height="60" patternUnits="userSpaceOnUse">
                    <circle cx="30" cy="30" r="1.5" fill="#ffffff" />
                    <circle cx="30" cy="30" r="18" fill="none" stroke="#ffffff" stroke-width="0.5" stroke-dasharray="2 4" />
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#hero-pattern)" />
        </svg>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-emerald-800/80 border border-emerald-700/60 text-amber-300 text-xs font-semibold uppercase tracking-wider">
                    <i data-lucide="map-pin" class="w-4 h-4 text-amber-400"></i>
                    <span>Boutique à Koumra (Mandoul) • Expéditions Tchad & International</span>
                </div>

                <h1 class="text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    La richesse pure de notre terre, <br class="hidden sm:inline">
                    <span class="text-amber-400">façonnée au cœur du Mandoul.</span>
                </h1>

                <p class="text-stone-300 text-base sm:text-lg max-w-2xl leading-relaxed">
                    Lait corporel au karité pur de Koumra, huile de ricin fortifiante, sirop de tamarin 100% BIO de la Coopérative Rokndoh, savons sculptés artisanaux et miels sauvages. Commandez sans compte, expédié directement depuis Koumra vers votre ville ou pays.
                </p>

                <!-- Boutons d'actions -->
                <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-2">
                    <a href="<?= BASE_URL ?>/catalogue.php" class="w-full sm:w-auto px-8 py-4 bg-amber-500 hover:bg-amber-400 text-stone-950 font-extrabold text-sm rounded-2xl shadow-xl shadow-amber-500/20 transition flex items-center justify-center gap-2">
                        <span>Explorer la boutique</span>
                        <i data-lucide="arrow-right" class="w-4 h-4"></i>
                    </a>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('phone_whatsapp', '23562891085')) ?>?text=<?= urlencode("Bonjour ROM'S Natural Koumra, je souhaite passer commande.") ?>" 
                       target="_blank"
                       class="w-full sm:w-auto px-7 py-4 bg-white/10 hover:bg-white/20 text-white font-bold text-sm rounded-2xl border border-white/20 transition flex items-center justify-center gap-2">
                        <i data-lucide="message-circle" class="w-4 h-4 text-emerald-400"></i>
                        <span>Commander sur WhatsApp</span>
                    </a>
                </div>

                <!-- Garanties rapides -->
                <div class="pt-4 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-xs text-stone-300">
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400"></i>
                        <span>Départ direct Koumra (Mandoul)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400"></i>
                        <span>Livraison Tchad & CEMAC</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i data-lucide="check-circle-2" class="w-4 h-4 text-amber-400"></i>
                        <span>Airtel Money & Moov Money</span>
                    </div>
                </div>
            </div>

            <!-- Visuel Hero Carte Produit Vedette Réel -->
            <div class="lg:col-span-5 flex justify-center">
                <div class="relative w-full max-w-sm">
                    <div class="absolute -top-4 -left-4 w-72 h-72 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="bg-gradient-to-b from-stone-900 to-emerald-950 p-6 rounded-3xl border border-emerald-700/40 shadow-2xl space-y-5 text-white">
                        <div class="relative aspect-square rounded-2xl overflow-hidden bg-white/5 border border-emerald-800 flex items-center justify-center">
                            <img src="<?= BASE_URL ?>/assets/images/products/lait-corporel-karite-rn.jpg" alt="Lait Corporel Karité RN" class="w-full h-full object-cover">
                            <span class="absolute top-3 left-3 bg-amber-500 text-stone-950 text-[10px] font-black uppercase tracking-wider px-2.5 py-1 rounded-full shadow">
                                Made in Chad • Koumra
                            </span>
                        </div>
                        <div>
                            <span class="text-xs text-amber-400 font-semibold uppercase tracking-wider">Cosmétiques Naturels</span>
                            <h3 class="text-lg font-bold text-white mt-1">Lait Corporel au Karité RN</h3>
                            <p class="text-xs text-stone-300 mt-1 line-clamp-2">Soin concentré réparateur pour peaux sèches et abîmées. Fabriqué à Koumra.</p>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-emerald-900">
                            <div>
                                <span class="text-xs text-stone-400">Prix unitaire</span>
                                <p class="text-xl font-extrabold text-amber-300">4 500 FCFA</p>
                            </div>
                            <a href="<?= BASE_URL ?>/catalogue.php" class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-600 text-white font-bold text-xs rounded-xl transition flex items-center gap-1.5 shadow">
                                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                <span>Découvrir</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Section Familles de Produits (PRD Section 3) -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row items-baseline justify-between mb-10 gap-2">
        <div>
            <span class="text-xs font-bold text-amber-700 uppercase tracking-widest">Nos Terroirs & Collections</span>
            <h2 class="text-2xl sm:text-3xl font-black text-stone-900 mt-1">Les familles de produits</h2>
        </div>
        <a href="<?= BASE_URL ?>/catalogue.php" class="text-xs font-bold text-emerald-800 hover:text-emerald-950 flex items-center gap-1">
            <span>Voir tout le catalogue</span>
            <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
        </a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <?php 
        $catIcons = [
            'cosmetiques-naturels' => ['icon' => 'sparkles', 'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200'],
            'alimentaires-transformes' => ['icon' => 'utensils', 'bg' => 'bg-amber-50 text-amber-800 border-amber-200'],
            'produits-artisanaux' => ['icon' => 'gem', 'bg' => 'bg-stone-50 text-stone-800 border-stone-200'],
            'agricoles-transformes' => ['icon' => 'wheat', 'bg' => 'bg-orange-50 text-orange-800 border-orange-200'],
            'autres-produits' => ['icon' => 'box', 'bg' => 'bg-blue-50 text-blue-800 border-blue-200']
        ];
        foreach ($categories as $cat): 
            $theme = $catIcons[$cat['slug']] ?? ['icon' => 'package', 'bg' => 'bg-emerald-50 text-emerald-800 border-emerald-200'];
        ?>
            <a href="<?= BASE_URL ?>/catalogue.php?cat=<?= urlencode($cat['slug']) ?>" class="group block p-6 bg-white rounded-3xl border border-stone-200/80 shadow-sm hover:shadow-md hover:border-emerald-700 transition">
                <div class="w-12 h-12 rounded-2xl <?= $theme['bg'] ?> border flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                    <i data-lucide="<?= $theme['icon'] ?>" class="w-6 h-6"></i>
                </div>
                <h3 class="font-bold text-base text-stone-900 group-hover:text-emerald-800 transition">
                    <?= htmlspecialchars($cat['nom']) ?>
                </h3>
                <p class="text-xs text-stone-500 mt-2 line-clamp-2 leading-relaxed">
                    <?= htmlspecialchars($cat['description']) ?>
                </p>
                <div class="mt-4 flex items-center justify-between text-xs font-semibold text-stone-400 group-hover:text-emerald-800 pt-3 border-t border-stone-100">
                    <span><?= $cat['total_prods'] ?> produit<?= $cat['total_prods'] > 1 ? 's' : '' ?></span>
                    <i data-lucide="chevron-right" class="w-4 h-4 transform group-hover:translate-x-1 transition"></i>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<!-- Section Produits en Vedette (PRD Section 4.2) -->
<section class="py-12 bg-white border-y border-stone-200/60">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-baseline justify-between mb-8 gap-2">
            <div>
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-widest">Sélection Exclusive</span>
                <h2 class="text-2xl sm:text-3xl font-black text-stone-900 mt-1">Produits mis en avant</h2>
            </div>
            <a href="<?= BASE_URL ?>/catalogue.php" class="text-xs font-bold text-emerald-800 hover:underline">
                Consulter tous les articles →
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($featuredProducts as $prod): ?>
                <div class="product-card bg-stone-50/60 rounded-3xl border border-stone-200 overflow-hidden flex flex-col">
                    <!-- Image et badges -->
                    <div class="relative aspect-square overflow-hidden bg-stone-100">
                        <img src="<?= getProductImageUrl($prod['photo_principale'], $prod['categorie_slug']) ?>" 
                             alt="<?= htmlspecialchars($prod['nom']) ?>" 
                             class="w-full h-full object-cover">
                        
                        <?php if ($prod['prix_promo'] > 0): ?>
                            <span class="absolute top-3 left-3 bg-rose-600 text-white text-[10px] font-black uppercase px-2.5 py-1 rounded-full shadow">
                                Promo
                            </span>
                        <?php endif; ?>

                        <?php if ($prod['stock'] <= 0): ?>
                            <div class="absolute inset-0 bg-stone-900/60 backdrop-blur-xs flex items-center justify-center">
                                <span class="bg-stone-900 text-white text-xs font-bold px-3 py-1.5 rounded-full border border-stone-700">
                                    Rupture de stock
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Contenu fiche -->
                    <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-700">
                                <?= htmlspecialchars($prod['categorie_nom'] ?? 'Produit Tchad') ?>
                            </span>
                            <h3 class="font-bold text-stone-900 text-base mt-1 line-clamp-1 hover:text-emerald-800 transition">
                                <a href="<?= BASE_URL ?>/produit.php?id=<?= $prod['id'] ?>">
                                    <?= htmlspecialchars($prod['nom']) ?>
                                </a>
                            </h3>
                            <p class="text-xs text-stone-500 mt-1 line-clamp-2 leading-relaxed">
                                <?= htmlspecialchars($prod['description_courte']) ?>
                            </p>
                            <?php if (!empty($prod['origine'])): ?>
                                <p class="text-[11px] text-emerald-800 font-medium mt-2 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                                    <span class="truncate"><?= htmlspecialchars($prod['origine']) ?></span>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Prix & Boutons -->
                        <div class="pt-3 border-t border-stone-200 flex items-center justify-between">
                            <div>
                                <?php if ($prod['prix_promo'] > 0): ?>
                                    <span class="text-xs text-stone-400 line-through block leading-none"><?= formatPrice($prod['prix']) ?></span>
                                    <span class="text-base font-black text-rose-600"><?= formatPrice($prod['prix_promo']) ?></span>
                                <?php else: ?>
                                    <span class="text-base font-black text-stone-900"><?= formatPrice($prod['prix']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <a href="<?= BASE_URL ?>/produit.php?id=<?= $prod['id'] ?>" class="p-2.5 rounded-xl border border-stone-300 text-stone-700 hover:bg-stone-100 transition" title="Voir le détail">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <?php if ($prod['stock'] > 0): ?>
                                    <button class="add-to-cart-btn px-3.5 py-2.5 rounded-xl bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs transition flex items-center gap-1 shadow-sm"
                                            data-product-id="<?= $prod['id'] ?>" title="Ajouter au panier">
                                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                                        <span>Commander</span>
                                    </button>
                                <?php else: ?>
                                    <button disabled class="px-3.5 py-2.5 rounded-xl bg-stone-200 text-stone-400 text-xs font-medium cursor-not-allowed">
                                        Épuisé
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Comment commander : Parcours court sans compte (PRD Section 5.3 & 6.1) -->
<section class="py-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="text-xs font-bold text-amber-700 uppercase tracking-widest">Simple & Rassurant</span>
        <h2 class="text-2xl sm:text-3xl font-black text-stone-900 mt-1">Commander en 3 étapes simples</h2>
        <p class="text-xs sm:text-sm text-stone-500 mt-2">Aucun mot de passe à mémoriser, aucune inscription fastidieuse requise.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <div class="p-6 bg-white rounded-3xl border border-stone-200 text-center space-y-4">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-800 rounded-2xl flex items-center justify-center mx-auto text-xl font-black shadow-inner">
                1
            </div>
            <h3 class="font-bold text-stone-900 text-base">Choisissez vos produits</h3>
            <p class="text-xs text-stone-500 leading-relaxed">
                Parcourez notre catalogue 100% tchadien, ajoutez vos articles et variantes préférés au panier en un clic.
            </p>
        </div>

        <div class="p-6 bg-white rounded-3xl border border-stone-200 text-center space-y-4">
            <div class="w-14 h-14 bg-amber-100 text-amber-800 rounded-2xl flex items-center justify-center mx-auto text-xl font-black shadow-inner">
                2
            </div>
            <h3 class="font-bold text-stone-900 text-base">Indiquez votre adresse</h3>
            <p class="text-xs text-stone-500 leading-relaxed">
                Renseignez simplement votre nom, votre numéro de téléphone et votre quartier ou ville au Tchad pour la livraison.
            </p>
        </div>

        <div class="p-6 bg-white rounded-3xl border border-stone-200 text-center space-y-4">
            <div class="w-14 h-14 bg-emerald-900 text-amber-300 rounded-2xl flex items-center justify-center mx-auto text-xl font-black shadow-inner">
                3
            </div>
            <h3 class="font-bold text-stone-900 text-base">Recevez et réglez</h3>
            <p class="text-xs text-stone-500 leading-relaxed">
                Payez à la remise de votre colis par le livreur, ou par Airtel Money / Moov Money en toute sécurité.
            </p>
        </div>

    </div>
</section>

<!-- Bannière WhatsApp Dédiée -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-12">
    <div class="bg-gradient-to-r from-emerald-900 to-emerald-950 rounded-3xl p-8 sm:p-12 text-white relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-8 shadow-xl">
        <div class="space-y-3 text-center md:text-left z-10">
            <span class="text-xs font-bold uppercase tracking-widest text-amber-400">Assistance & Conseil Personnalisé</span>
            <h3 class="text-2xl sm:text-3xl font-black">Besoin d'un conseil sur nos produits du Tchad ?</h3>
            <p class="text-xs sm:text-sm text-stone-300 max-w-xl">
                Notre équipe à N'Djamena est joignable directement par WhatsApp pour répondre à vos questions sur les modes d'application, ingrédients et commandes sur-mesure.
            </p>
        </div>
        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('phone_whatsapp', '23566280000')) ?>?text=<?= urlencode("Bonjour ROM'S Natural, j'aimerais avoir des conseils sur vos produits.") ?>" 
           target="_blank"
           class="px-8 py-4 bg-[#25D366] hover:bg-[#20ba59] text-white font-extrabold text-sm rounded-2xl shadow-lg transition flex items-center gap-2 flex-shrink-0 z-10">
            <i data-lucide="message-circle" class="w-5 h-5 fill-white stroke-none"></i>
            <span>Discuter avec un conseiller</span>
        </a>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

