<?php
/**
 * Gestion du Catalogue Produits - ROM'S NATURAL
 * Conforme PRD Section 4.2
 */

$adminTitle = "Catalogue & Fiches Produits";
$activeMenu = "products";
require_once __DIR__ . '/header.php';
require_permission('catalog');

// Actions rapides : Dépublier / Publier / Dupliquer / Supprimer
if (isset($_GET['toggle_dispo'])) {
    $pId = (int)$_GET['toggle_dispo'];
    $pdo->prepare("UPDATE products SET disponible = CASE WHEN disponible = 1 THEN 0 ELSE 1 END WHERE id = ?")->execute([$pId]);
    header('Location: ' . BASE_URL . '/admin/products.php');
    exit;
}

if (isset($_GET['duplicate'])) {
    $pId = (int)$_GET['duplicate'];
    $stmtP = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmtP->execute([$pId]);
    $p = $stmtP->fetch();
    if ($p) {
        $newSlug = $p['slug'] . '-copie-' . time();
        $newNom = $p['nom'] . ' (Copie)';
        $stmtDup = $pdo->prepare("INSERT INTO products (
            nom, slug, categorie_id, description_courte, description, prix, prix_promo,
            photo_principale, stock, seuil_alerte, en_vedette, nouveaute, composition, origine,
            conseils_utilisation, precautions, conservation, disponible
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1, ?, ?, ?, ?, ?, 0)");
        $stmtDup->execute([
            $newNom, $newSlug, $p['categorie_id'], $p['description_courte'], $p['description'],
            $p['prix'], $p['prix_promo'], $p['photo_principale'], $p['stock'], $p['seuil_alerte'],
            $p['composition'], $p['origine'], $p['conseils_utilisation'], $p['precautions'],
            $p['conservation']
        ]);
    }
    header('Location: ' . BASE_URL . '/admin/products.php');
    exit;
}

if (isset($_GET['delete'])) {
    $pId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$pId]);
    header('Location: ' . BASE_URL . '/admin/products.php');
    exit;
}

// Filtres
$search = trim($_GET['q'] ?? '');
$catId = (int)($_GET['cat'] ?? 0);

$where = ["1=1"];
$params = [];

if ($catId > 0) {
    $where[] = "p.categorie_id = ?";
    $params[] = $catId;
}

if (!empty($search)) {
    $where[] = "(p.nom LIKE ? OR p.composition LIKE ? OR p.origine LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$whereSql = implode(" AND ", $where);
$stmt = $pdo->prepare("SELECT p.*, c.nom as categorie_nom, c.slug as categorie_slug 
    FROM products p 
    LEFT JOIN categories c ON p.categorie_id = c.id 
    WHERE $whereSql 
    ORDER BY p.id DESC");
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = $pdo->query("SELECT id, nom FROM categories WHERE statut = 1 ORDER BY ordre ASC")->fetchAll();
?>

<div class="space-y-6">
    
    <!-- En-tête avec bouton Nouveau Produit -->
    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row items-baseline sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-stone-900">Catalogue des Produits</h2>
            <p class="text-xs text-stone-500 mt-0.5">Gérez les fiches techniques, prix XAF, visuels et niveaux de stocks.</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/product_edit.php" class="px-5 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs rounded-xl transition shadow flex items-center gap-2">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Nouveau Produit (Assistant IA)</span>
        </a>
    </div>

    <!-- Barre de Recherche et Filtres -->
    <div class="bg-white p-4 rounded-2xl border border-stone-200 shadow-sm">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div class="relative sm:col-span-2">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher par nom, ingrédient ou terroir..." 
                       class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                <i data-lucide="search" class="w-4 h-4 text-stone-400 absolute left-3 top-3"></i>
            </div>
            <div class="flex gap-2">
                <select name="cat" class="flex-1 px-3 py-2.5 rounded-xl border border-stone-300 bg-white focus:outline-none focus:border-emerald-700">
                    <option value="">Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $catId === $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nom']) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="px-4 py-2.5 bg-stone-800 text-white font-bold rounded-xl hover:bg-black transition">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Tableau des Produits -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-600 font-bold uppercase tracking-wider">
                        <th class="py-3 px-4">Produit</th>
                        <th class="py-3 px-4">Catégorie</th>
                        <th class="py-3 px-4">Prix Public</th>
                        <th class="py-3 px-4">Stock</th>
                        <th class="py-3 px-4">Mise en avant</th>
                        <th class="py-3 px-4">Disponibilité</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php foreach ($products as $p): ?>
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-3">
                                    <img src="<?= getProductImageUrl($p['photo_principale'], $p['categorie_slug']) ?>" 
                                         alt="" class="w-12 h-12 rounded-xl object-cover bg-stone-100 border border-stone-200 flex-shrink-0">
                                    <div class="min-w-0 max-w-xs">
                                        <h4 class="font-bold text-stone-900 truncate hover:text-emerald-800">
                                            <a href="<?= BASE_URL ?>/admin/product_edit.php?id=<?= $p['id'] ?>">
                                                <?= htmlspecialchars($p['nom']) ?>
                                            </a>
                                        </h4>
                                        <span class="text-[11px] text-stone-500 truncate block"><?= htmlspecialchars($p['origine'] ?? 'Tchad') ?></span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="px-2.5 py-1 rounded-full bg-stone-100 text-stone-700 font-semibold text-[11px]">
                                    <?= htmlspecialchars($p['categorie_nom'] ?? 'Général') ?>
                                </span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap font-bold text-stone-900">
                                <?php if ($p['prix_promo'] > 0): ?>
                                    <span class="text-rose-600 font-black"><?= formatPrice($p['prix_promo']) ?></span>
                                    <span class="text-stone-400 line-through text-[10px] block"><?= formatPrice($p['prix']) ?></span>
                                <?php else: ?>
                                    <?= formatPrice($p['prix']) ?>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <?php if ($p['stock'] <= $p['seuil_alerte']): ?>
                                    <span class="inline-flex items-center gap-1 font-black text-rose-700 bg-rose-50 px-2 py-0.5 rounded-lg border border-rose-200">
                                        ⚠️ <?= $p['stock'] ?> (Alerte)
                                    </span>
                                <?php else: ?>
                                    <span class="font-bold text-stone-700"><?= $p['stock'] ?> en stock</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap space-y-1">
                                <?php if ($p['en_vedette']): ?>
                                    <span class="inline-block bg-amber-100 text-amber-900 text-[10px] font-black px-2 py-0.5 rounded-md">Vedette</span>
                                <?php endif; ?>
                                <?php if ($p['nouveaute']): ?>
                                    <span class="inline-block bg-emerald-100 text-emerald-900 text-[10px] font-black px-2 py-0.5 rounded-md">Nouveau</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <a href="<?= BASE_URL ?>/admin/products.php?toggle_dispo=<?= $p['id'] ?>" 
                                   class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-1 rounded-full border transition <?= $p['disponible'] ? 'bg-emerald-50 text-emerald-800 border-emerald-200 hover:bg-emerald-100' : 'bg-stone-100 text-stone-500 border-stone-200 hover:bg-stone-200' ?>"
                                   title="Cliquer pour changer la visibilité">
                                    <span class="w-1.5 h-1.5 rounded-full <?= $p['disponible'] ? 'bg-emerald-600' : 'bg-stone-400' ?>"></span>
                                    <span><?= $p['disponible'] ? 'Actif' : 'Masqué' ?></span>
                                </a>
                            </td>
                            <td class="py-3 px-4 text-right whitespace-nowrap space-x-1">
                                <a href="<?= BASE_URL ?>/admin/product_edit.php?id=<?= $p['id'] ?>" class="p-1.5 rounded-lg bg-stone-100 text-stone-700 hover:bg-emerald-800 hover:text-white transition inline-block" title="Modifier">
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/products.php?duplicate=<?= $p['id'] ?>" class="p-1.5 rounded-lg bg-stone-100 text-stone-700 hover:bg-amber-600 hover:text-white transition inline-block" title="Dupliquer" onclick="return confirm('Dupliquer ce produit ?')">
                                    <i data-lucide="copy" class="w-3.5 h-3.5"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/products.php?delete=<?= $p['id'] ?>" class="p-1.5 rounded-lg bg-stone-100 text-stone-700 hover:bg-rose-600 hover:text-white transition inline-block" title="Supprimer" onclick="return confirm('Confirmer la suppression définitive de ce produit ?')">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

