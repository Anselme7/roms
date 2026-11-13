<?php
/**
 * Boutique / Catalogue - ROM'S NATURAL
 * Conforme PRD Section 5.1 & 5.2
 */

$pageTitle = "Boutique & Catalogue";
$activeNav = "catalogue";
require_once __DIR__ . '/includes/header.php';

// Paramètres de filtrage et recherche
$catSlug = trim($_GET['cat'] ?? '');
$search = trim($_GET['q'] ?? '');
$sort = trim($_GET['sort'] ?? 'newest');
$stockOnly = !empty($_GET['in_stock']);
$maxPrice = !empty($_GET['max_price']) ? (float)$_GET['max_price'] : 0;

// Construction de la requête SQL
$whereClauses = ["p.disponible = 1"];
$params = [];

if (!empty($catSlug)) {
    $whereClauses[] = "c.slug = ?";
    $params[] = $catSlug;
}

if (!empty($search)) {
    $whereClauses[] = "(p.nom LIKE ? OR p.description_courte LIKE ? OR p.composition LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($stockOnly) {
    $whereClauses[] = "p.stock > 0";
}

if ($maxPrice > 0) {
    $whereClauses[] = "p.prix <= ?";
    $params[] = $maxPrice;
}

$whereSql = implode(" AND ", $whereClauses);

// Tri
$orderBy = "p.id DESC";
switch ($sort) {
    case 'price_asc':
        $orderBy = "COALESCE(NULLIF(p.prix_promo, 0), p.prix) ASC";
        break;
    case 'price_desc':
        $orderBy = "COALESCE(NULLIF(p.prix_promo, 0), p.prix) DESC";
        break;
    case 'name_asc':
        $orderBy = "p.nom ASC";
        break;
    case 'newest':
    default:
        $orderBy = "p.nouveaute DESC, p.id DESC";
        break;
}

$sql = "SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug 
        FROM products p 
        LEFT JOIN categories c ON p.categorie_id = c.id 
        WHERE $whereSql 
        ORDER BY $orderBy";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Liste des catégories pour la barre de filtre
$allCats = $pdo->query("SELECT c.*, COUNT(p.id) as count_prods 
    FROM categories c 
    LEFT JOIN products p ON p.categorie_id = c.id AND p.disponible = 1 
    WHERE c.statut = 1 
    GROUP BY c.id 
    ORDER BY c.ordre ASC")->fetchAll();

// Catégorie active pour titre
$currentCategoryName = "Tous nos produits";
if (!empty($catSlug)) {
    foreach ($allCats as $ac) {
        if ($ac['slug'] === $catSlug) {
            $currentCategoryName = $ac['nom'];
            break;
        }
    }
}
?>

<div class="bg-stone-100/80 border-b border-stone-200 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row items-baseline justify-between gap-4">
            <div>
                <nav class="flex text-xs text-stone-500 mb-2 gap-2">
                    <a href="<?= BASE_URL ?>/index.php" class="hover:text-emerald-800">Accueil</a>
                    <span>/</span>
                    <span class="text-stone-800 font-semibold">Boutique</span>
                    <?php if (!empty($catSlug)): ?>
                        <span>/</span>
                        <span class="text-emerald-800 font-semibold"><?= htmlspecialchars($currentCategoryName) ?></span>
                    <?php endif; ?>
                </nav>
                <h1 class="text-2xl sm:text-3xl font-black text-stone-900"><?= htmlspecialchars($currentCategoryName) ?></h1>
            </div>
            <p class="text-xs sm:text-sm text-stone-500">
                <span class="font-bold text-stone-900"><?= count($products) ?></span> article<?= count($products) > 1 ? 's' : '' ?> trouvé<?= count($products) > 1 ? 's' : '' ?>
            </p>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Colonne Filtres (Desktop & Mobile) -->
        <aside class="space-y-6">
            <div class="bg-white p-6 rounded-3xl border border-stone-200 space-y-6">
                
                <!-- Recherche -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-3">Recherche</h3>
                    <form action="<?= BASE_URL ?>/catalogue.php" method="GET">
                        <?php if (!empty($catSlug)): ?>
                            <input type="hidden" name="cat" value="<?= htmlspecialchars($catSlug) ?>">
                        <?php endif; ?>
                        <div class="relative">
                            <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Miel, karité, huile..."
                                   class="w-full pl-9 pr-3 py-2 text-xs rounded-xl bg-stone-100 border border-stone-200 focus:outline-none focus:border-emerald-700">
                            <i data-lucide="search" class="w-4 h-4 text-stone-400 absolute left-3 top-2.5"></i>
                        </div>
                    </form>
                </div>

                <hr class="border-stone-100">

                <!-- Catégories -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-3">Catégories</h3>
                    <ul class="space-y-1 text-xs">
                        <li>
                            <a href="<?= BASE_URL ?>/catalogue.php<?= !empty($search) ? '?q=' . urlencode($search) : '' ?>" 
                               class="flex items-center justify-between py-1.5 px-2.5 rounded-lg transition <?= empty($catSlug) ? 'bg-emerald-800 text-white font-bold' : 'text-stone-700 hover:bg-stone-50' ?>">
                                <span>Tous les produits</span>
                            </a>
                        </li>
                        <?php foreach ($allCats as $c): ?>
                            <li>
                                <a href="<?= BASE_URL ?>/catalogue.php?cat=<?= urlencode($c['slug']) ?><?= !empty($search) ? '&q=' . urlencode($search) : '' ?>" 
                                   class="flex items-center justify-between py-1.5 px-2.5 rounded-lg transition <?= ($catSlug === $c['slug']) ? 'bg-emerald-800 text-white font-bold' : 'text-stone-700 hover:bg-stone-50' ?>">
                                    <span class="truncate"><?= htmlspecialchars($c['nom']) ?></span>
                                    <span class="text-[11px] <?= ($catSlug === $c['slug']) ? 'text-emerald-200' : 'text-stone-400' ?>">(<?= $c['count_prods'] ?>)</span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <hr class="border-stone-100">

                <!-- Filtre Disponibilité -->
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-stone-800 mb-3">Disponibilité</h3>
                    <label class="flex items-center gap-2 text-xs text-stone-700 cursor-pointer">
                        <input type="checkbox" onchange="window.location.href='<?= BASE_URL ?>/catalogue.php?<?= http_build_query(array_merge($_GET, ['in_stock' => $stockOnly ? '' : '1'])) ?>'" <?= $stockOnly ? 'checked' : '' ?> class="rounded text-emerald-800 focus:ring-emerald-700">
                        <span>En stock uniquement</span>
                    </label>
                </div>

                <!-- Réinitialiser filtres -->
                <?php if (!empty($catSlug) || !empty($search) || $stockOnly || $sort !== 'newest'): ?>
                    <a href="<?= BASE_URL ?>/catalogue.php" class="block text-center py-2 px-3 text-xs font-semibold text-rose-700 bg-rose-50 rounded-xl hover:bg-rose-100 transition">
                        Réinitialiser tous les filtres
                    </a>
                <?php endif; ?>

            </div>

            <!-- Encadré WhatsApp Réassurance -->
            <div class="bg-emerald-900 text-white p-6 rounded-3xl space-y-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-800 flex items-center justify-center text-amber-300">
                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                </div>
                <h4 class="font-bold text-sm">Une question sur un produit ?</h4>
                <p class="text-xs text-stone-300 leading-relaxed">
                    Contactez-nous directement sur WhatsApp pour être guidé(e) selon votre type de peau ou vos besoins.
                </p>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('phone_whatsapp', '23566280000')) ?>" target="_blank"
                   class="inline-block mt-2 text-xs font-bold text-amber-300 hover:text-white transition">
                    Écrire à un conseiller →
                </a>
            </div>
        </aside>

        <!-- Grille de Produits -->
        <main class="lg:col-span-3 space-y-6">
            
            <!-- Barre de tri -->
            <div class="bg-white p-4 rounded-2xl border border-stone-200 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-stone-600">
                    Affichage de <span class="font-bold text-stone-900"><?= count($products) ?></span> résultat<?= count($products) > 1 ? 's' : '' ?>
                </div>
                
                <div class="flex items-center gap-2 text-xs w-full sm:w-auto justify-end">
                    <span class="text-stone-500 font-medium">Trier par :</span>
                    <select onchange="window.location.href='<?= BASE_URL ?>/catalogue.php?<?= http_build_query(array_merge($_GET, ['sort' => ''])) ?>' + this.value"
                            class="px-3 py-1.5 rounded-xl border border-stone-200 bg-stone-50 text-stone-800 text-xs font-semibold focus:outline-none focus:border-emerald-700">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Nouveautés & Vedettes</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Prix croissant</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Prix décroissant</option>
                        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Nom alphabétique</option>
                    </select>
                </div>
            </div>

            <!-- Grille -->
            <?php if (empty($products)): ?>
                <div class="bg-white rounded-3xl border border-stone-200 p-12 text-center space-y-4">
                    <div class="w-16 h-16 bg-stone-100 rounded-full flex items-center justify-center mx-auto text-stone-400">
                        <i data-lucide="package-open" class="w-8 h-8"></i>
                    </div>
                    <h3 class="font-bold text-base text-stone-900">Aucun produit ne correspond à votre sélection</h3>
                    <p class="text-xs text-stone-500 max-w-md mx-auto">
                        Essayez de modifier vos mots-clés ou consultez l'ensemble de notre catalogue du terroir tchadien.
                    </p>
                    <a href="<?= BASE_URL ?>/catalogue.php" class="inline-block px-5 py-2.5 bg-emerald-800 text-white text-xs font-bold rounded-xl hover:bg-emerald-900 transition">
                        Voir tout le catalogue
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $prod): ?>
                        <div class="product-card bg-white rounded-3xl border border-stone-200 overflow-hidden flex flex-col justify-between">
                            
                            <div>
                                <!-- Image & Badges -->
                                <div class="relative aspect-square overflow-hidden bg-stone-50">
                                    <a href="<?= BASE_URL ?>/produit.php?id=<?= $prod['id'] ?>">
                                        <img src="<?= getProductImageUrl($prod['photo_principale'], $prod['categorie_slug']) ?>" 
                                             alt="<?= htmlspecialchars($prod['nom']) ?>" 
                                             class="w-full h-full object-cover">
                                    </a>

                                    <?php if ($prod['prix_promo'] > 0): ?>
                                        <span class="absolute top-3 left-3 bg-rose-600 text-white text-[10px] font-black uppercase px-2 py-0.5 rounded-full shadow">
                                            Promo
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($prod['nouveaute']): ?>
                                        <span class="absolute top-3 right-3 bg-amber-500 text-stone-950 text-[10px] font-black uppercase px-2 py-0.5 rounded-full shadow">
                                            Nouveau
                                        </span>
                                    <?php endif; ?>

                                    <?php if ($prod['stock'] <= 0): ?>
                                        <div class="absolute inset-0 bg-stone-950/60 backdrop-blur-xs flex items-center justify-center">
                                            <span class="bg-stone-900 text-white text-xs font-bold px-3 py-1.5 rounded-full border border-stone-700">
                                                Rupture temporaire
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Infos -->
                                <div class="p-5 space-y-2">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700">
                                        <?= htmlspecialchars($prod['categorie_nom'] ?? 'Produit Local') ?>
                                    </span>
                                    <h3 class="font-bold text-stone-900 text-sm line-clamp-1 hover:text-emerald-800 transition">
                                        <a href="<?= BASE_URL ?>/produit.php?id=<?= $prod['id'] ?>">
                                            <?= htmlspecialchars($prod['nom']) ?>
                                        </a>
                                    </h3>
                                    <p class="text-xs text-stone-500 line-clamp-2 leading-relaxed">
                                        <?= htmlspecialchars($prod['description_courte']) ?>
                                    </p>
                                    <?php if (!empty($prod['origine'])): ?>
                                        <p class="text-[11px] text-emerald-800 font-medium flex items-center gap-1 pt-1 truncate">
                                            <i data-lucide="map-pin" class="w-3 h-3 flex-shrink-0"></i>
                                            <span class="truncate"><?= htmlspecialchars($prod['origine']) ?></span>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Bas de carte : Prix & Ajout -->
                            <div class="p-5 pt-0">
                                <div class="pt-3 border-t border-stone-100 flex items-center justify-between">
                                    <div>
                                        <?php if ($prod['prix_promo'] > 0): ?>
                                            <span class="text-[11px] text-stone-400 line-through block leading-none"><?= formatPrice($prod['prix']) ?></span>
                                            <span class="text-sm font-black text-rose-600"><?= formatPrice($prod['prix_promo']) ?></span>
                                        <?php else: ?>
                                            <span class="text-sm font-black text-stone-900"><?= formatPrice($prod['prix']) ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="flex items-center gap-1.5">
                                        <a href="<?= BASE_URL ?>/produit.php?id=<?= $prod['id'] ?>" class="p-2 rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-100 transition" title="Consulter la fiche">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <?php if ($prod['stock'] > 0): ?>
                                            <button class="add-to-cart-btn px-3 py-2 rounded-xl bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs transition flex items-center gap-1 shadow-sm"
                                                    data-product-id="<?= $prod['id'] ?>">
                                                <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                                                <span>Ajouter</span>
                                            </button>
                                        <?php else: ?>
                                            <span class="text-[11px] font-semibold text-rose-600 bg-rose-50 px-2.5 py-1.5 rounded-lg">
                                                Épuisé
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </main>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

