<?php
/**
 * Gestion des Stocks, Mouvements & Suivi des Lots
 * Conforme PRD Section 9
 */

$adminTitle = "Stocks & Lots de Fabrication";
$activeMenu = "stock";
require_once __DIR__ . '/header.php';
require_permission('stock');

$message = '';

// Ajout d'un mouvement de stock (Entrée, Sortie, Correction)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_movement'])) {
    $produitId = (int)($_POST['produit_id'] ?? 0);
    $typeMvt = trim($_POST['type_mouvement'] ?? 'Entrée');
    $quantite = (int)($_POST['quantite'] ?? 0);
    $motif = trim($_POST['motif'] ?? 'Réapprovisionnement');

    if ($produitId > 0 && $quantite > 0) {
        $delta = ($typeMvt === 'Sortie' || $typeMvt === 'Perte') ? -$quantite : $quantite;
        
        $pdo->beginTransaction();
        $pdo->prepare("UPDATE products SET stock = MAX(0, stock + ?) WHERE id = ?")->execute([$delta, $produitId]);
        $pdo->prepare("INSERT INTO stock_movements (produit_id, type_mouvement, quantite, motif, utilisateur) VALUES (?, ?, ?, ?, ?)")
            ->execute([$produitId, $typeMvt, $quantite, $motif, $_SESSION['admin_name'] ?? 'Admin']);
        $pdo->commit();

        $message = "Mouvement de stock de $quantite unité(s) enregistré.";
    }
}

// Ajout d'un Lot de fabrication
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_batch'])) {
    $produitId = (int)($_POST['produit_id'] ?? 0);
    $numeroLot = trim($_POST['numero_lot'] ?? '');
    $quantite = (int)($_POST['quantite'] ?? 0);
    $dateFab = !empty($_POST['date_fabrication']) ? $_POST['date_fabrication'] : null;
    $dateLim = !empty($_POST['date_limite']) ? $_POST['date_limite'] : null;
    $notes = trim($_POST['notes'] ?? '');

    if ($produitId > 0 && !empty($numeroLot)) {
        $pdo->prepare("INSERT INTO product_batches (produit_id, numero_lot, quantite, date_fabrication, date_limite, notes) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$produitId, $numeroLot, $quantite, $dateFab, $dateLim, $notes]);
        $message = "Lot $numeroLot enregistré avec succès.";
    }
}

// Produits et stocks actuels
$products = $pdo->query("SELECT p.*, c.nom as categorie_nom FROM products p LEFT JOIN categories c ON p.categorie_id = c.id ORDER BY p.stock ASC")->fetchAll();

// Lots enregistrés
$batches = $pdo->query("SELECT b.*, p.nom as produit_nom 
    FROM product_batches b 
    LEFT JOIN products p ON b.produit_id = p.id 
    ORDER BY b.id DESC LIMIT 15")->fetchAll();

// Mouvements récents
$movements = $pdo->query("SELECT m.*, p.nom as produit_nom 
    FROM stock_movements m 
    LEFT JOIN products p ON m.produit_id = p.id 
    ORDER BY m.id DESC LIMIT 20")->fetchAll();
?>

<div class="space-y-8 max-w-6xl mx-auto">
    
    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row items-baseline justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-stone-900">Stocks, Mouvements & Lots (PRD Section 9)</h2>
            <p class="text-xs text-stone-500 mt-0.5">Suivi des entrées/sorties de marchandises et traçabilité des dates limites de conservation.</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-700"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
    <?php endif; ?>

    <!-- Formulaires d'Actions Rapides (Mouvements & Lots) -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
        
        <!-- Enregistrer une entrée / sortie de stock -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-stone-100">
                <i data-lucide="refresh-cw" class="w-4 h-4 text-emerald-800"></i>
                <h3 class="font-bold text-sm text-stone-900">Nouveau Mouvement de Stock</h3>
            </div>

            <form method="POST" class="space-y-3 text-xs">
                <input type="hidden" name="add_movement" value="1">
                
                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Produit concerné *</label>
                    <select name="produit_id" required class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white font-medium">
                        <?php foreach ($products as $pr): ?>
                            <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['nom']) ?> (Stock actuel: <?= $pr['stock'] ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Type de Mouvement</label>
                        <select name="type_mouvement" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white font-medium">
                            <option value="Entrée">Entrée (Approvisionnement)</option>
                            <option value="Sortie">Sortie (Avarie / Démo)</option>
                            <option value="Correction">Correction inventaire</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Quantité *</label>
                        <input type="number" name="quantite" required min="1" value="10" class="w-full px-3 py-2 rounded-xl border border-stone-300 font-bold">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Motif / Justificatif</label>
                    <input type="text" name="motif" placeholder="Ex: Livraison récolte Mandoul, Inventaire mensuel..." class="w-full px-3 py-2 rounded-xl border border-stone-300">
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-emerald-800 hover:bg-emerald-900 text-white font-bold rounded-xl transition shadow">
                    Valider le mouvement
                </button>
            </form>
        </div>

        <!-- Enregistrer un Lot de Fabrication (PRD Section 9) -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-stone-100">
                <i data-lucide="tag" class="w-4 h-4 text-amber-700"></i>
                <h3 class="font-bold text-sm text-stone-900">Enregistrer un Lot & DLUO</h3>
            </div>

            <form method="POST" class="space-y-3 text-xs">
                <input type="hidden" name="add_batch" value="1">

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Produit *</label>
                    <select name="produit_id" required class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white font-medium">
                        <?php foreach ($products as $pr): ?>
                            <option value="<?= $pr['id'] ?>"><?= htmlspecialchars($pr['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Numéro de Lot *</label>
                        <input type="text" name="numero_lot" required placeholder="LOT-26-001" class="w-full px-3 py-2 rounded-xl border border-stone-300 font-mono font-bold">
                    </div>
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Quantité du Lot</label>
                        <input type="number" name="quantite" min="1" value="50" class="w-full px-3 py-2 rounded-xl border border-stone-300 font-bold">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Date Fabrication</label>
                        <input type="date" name="date_fabrication" value="<?= date('Y-m-d') ?>" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Date Limite (DLUO / DLC)</label>
                        <input type="date" name="date_limite" value="<?= date('Y-m-d', strtotime('+18 months')) ?>" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl transition shadow">
                    Enregistrer le lot
                </button>
            </form>
        </div>

    </div>

    <!-- Tableau État des Stocks -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-stone-100 flex items-center justify-between">
            <h3 class="font-bold text-sm text-stone-900">État Actuel des Stocks</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-600 font-bold uppercase">
                        <th class="py-3 px-4">Produit</th>
                        <th class="py-3 px-4">Catégorie</th>
                        <th class="py-3 px-4">Stock Disponible</th>
                        <th class="py-3 px-4">Seuil d'Alerte</th>
                        <th class="py-3 px-4">État</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php foreach ($products as $pr): 
                        $isLow = ($pr['stock'] <= $pr['seuil_alerte']);
                    ?>
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="py-3 px-4 font-bold text-stone-900"><?= htmlspecialchars($pr['nom']) ?></td>
                            <td class="py-3 px-4 text-stone-500"><?= htmlspecialchars($pr['categorie_nom'] ?? 'Général') ?></td>
                            <td class="py-3 px-4 font-mono font-black text-sm <?= $isLow ? 'text-rose-600' : 'text-stone-900' ?>">
                                <?= $pr['stock'] ?>
                            </td>
                            <td class="py-3 px-4 text-stone-500 font-semibold"><?= $pr['seuil_alerte'] ?></td>
                            <td class="py-3 px-4">
                                <?php if ($pr['stock'] <= 0): ?>
                                    <span class="px-2 py-0.5 rounded-full bg-stone-900 text-white font-bold text-[10px]">Rupture</span>
                                <?php elseif ($isLow): ?>
                                    <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px]">Stock Faible</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">Optimal</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Historique des Mouvements de Stock (PRD Section 9 Traçabilité) -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-stone-100">
            <h3 class="font-bold text-sm text-stone-900">Journal de Traçabilité des Mouvements</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-stone-50 border-b border-stone-200 text-stone-600 font-bold uppercase">
                        <th class="py-3 px-4">Date</th>
                        <th class="py-3 px-4">Produit</th>
                        <th class="py-3 px-4">Type</th>
                        <th class="py-3 px-4">Quantité</th>
                        <th class="py-3 px-4">Motif</th>
                        <th class="py-3 px-4">Auteur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    <?php foreach ($movements as $m): ?>
                        <tr class="hover:bg-stone-50/70 transition">
                            <td class="py-2.5 px-4 text-stone-500 whitespace-nowrap"><?= formatDateFr($m['date_mouvement']) ?></td>
                            <td class="py-2.5 px-4 font-semibold text-stone-900"><?= htmlspecialchars($m['produit_nom']) ?></td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-bold <?= str_contains($m['type_mouvement'], 'Sortie') || str_contains($m['type_mouvement'], 'Vente') ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-900' ?>">
                                    <?= htmlspecialchars($m['type_mouvement']) ?>
                                </span>
                            </td>
                            <td class="py-2.5 px-4 font-mono font-bold text-stone-900"><?= $m['quantite'] ?></td>
                            <td class="py-2.5 px-4 text-stone-600"><?= htmlspecialchars($m['motif'] ?? '-') ?></td>
                            <td class="py-2.5 px-4 text-stone-500"><?= htmlspecialchars($m['utilisateur'] ?? 'Système') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

