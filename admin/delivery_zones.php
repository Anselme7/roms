<?php
/**
 * Paramétrage des Zones de Livraison au Tchad
 * Conforme PRD Section 7.1
 */

$adminTitle = "Zones de Livraison au Tchad";
$activeMenu = "delivery";
require_once __DIR__ . '/header.php';
require_permission('settings');

$message = '';

// Ajout / Mise à jour zone
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_zone'])) {
    $zoneId = (int)($_POST['zone_id'] ?? 0);
    $nomZone = trim($_POST['nom_zone'] ?? '');
    $region = trim($_POST['region'] ?? 'Tchad');
    $tarif = (float)($_POST['tarif_xaf'] ?? 0);
    $delai = trim($_POST['delai_estime'] ?? '24h à 48h');
    $conditions = trim($_POST['conditions'] ?? '');
    $ordre = (int)($_POST['ordre'] ?? 0);

    if (!empty($nomZone)) {
        if ($zoneId > 0) {
            $pdo->prepare("UPDATE delivery_zones SET nom_zone = ?, region = ?, tarif_xaf = ?, delai_estime = ?, conditions = ?, ordre = ? WHERE id = ?")
                ->execute([$nomZone, $region, $tarif, $delai, $conditions, $ordre, $zoneId]);
            $message = "Zone de livraison mise à jour avec succès.";
        } else {
            $pdo->prepare("INSERT INTO delivery_zones (nom_zone, region, tarif_xaf, delai_estime, conditions, ordre, actif) VALUES (?, ?, ?, ?, ?, ?, 1)")
                ->execute([$nomZone, $region, $tarif, $delai, $conditions, $ordre]);
            $message = "Nouvelle zone de livraison ajoutée avec succès.";
        }
    }
}

// Activer / Désactiver
if (isset($_GET['toggle_actif'])) {
    $zId = (int)$_GET['toggle_actif'];
    $pdo->prepare("UPDATE delivery_zones SET actif = CASE WHEN actif = 1 THEN 0 ELSE 1 END WHERE id = ?")->execute([$zId]);
    header('Location: ' . BASE_URL . '/admin/delivery_zones.php');
    exit;
}

// Supprimer
if (isset($_GET['delete'])) {
    $zId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM delivery_zones WHERE id = ?")->execute([$zId]);
    header('Location: ' . BASE_URL . '/admin/delivery_zones.php');
    exit;
}

$zones = $pdo->query("SELECT * FROM delivery_zones ORDER BY ordre ASC")->fetchAll();

$editZone = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM delivery_zones WHERE id = ?");
    $stmt->execute([$editId]);
    $editZone = $stmt->fetch();
}
?>

<div class="space-y-6 max-w-6xl mx-auto">
    
    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm">
        <h2 class="text-xl font-bold text-stone-900">Zones & Tarifs de Livraison Nationale</h2>
        <p class="text-xs text-stone-500 mt-1">
            Configurez les localités tchadiennes couvertes, les tarifs en FCFA et les délais estimatifs (PRD Section 7.1).
        </p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-700"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Formulaire Création / Modification Zone (5 cols) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-stone-900 pb-2 border-b border-stone-100">
                <?= $editZone ? 'Modifier la zone' : 'Ajouter une zone desservie' ?>
            </h3>

            <form method="POST" class="space-y-3 text-xs">
                <input type="hidden" name="save_zone" value="1">
                <input type="hidden" name="zone_id" value="<?= $editZone['id'] ?? 0 ?>">

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Nom de la Zone / Localité *</label>
                    <input type="text" name="nom_zone" required value="<?= htmlspecialchars($editZone['nom_zone'] ?? '') ?>" placeholder="Ex: Moundou - Retrait agence" 
                           class="w-full px-3 py-2 rounded-xl border border-stone-300 font-semibold focus:outline-none focus:border-emerald-700">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Région / Province</label>
                        <input type="text" name="region" value="<?= htmlspecialchars($editZone['region'] ?? 'N\'Djamena') ?>" placeholder="Ex: Ouaddaï" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Tarif Livraison (XAF) *</label>
                        <input type="number" step="100" name="tarif_xaf" required value="<?= htmlspecialchars($editZone['tarif_xaf'] ?? 1500) ?>" class="w-full px-3 py-2 rounded-xl border border-stone-300 font-black">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Délai estimé *</label>
                        <input type="text" name="delai_estime" required value="<?= htmlspecialchars($editZone['delai_estime'] ?? '2h à 4h ouvrées') ?>" placeholder="Ex: 24h à 48h" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Ordre d'affichage</label>
                        <input type="number" name="ordre" value="<?= htmlspecialchars($editZone['ordre'] ?? count($zones) + 1) ?>" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Conditions Particulières / Transporteur</label>
                    <textarea name="conditions" rows="2" placeholder="Ex: Expédition sécurisée par bus express avec retrait au guichet..." class="w-full px-3 py-2 rounded-xl border border-stone-300"><?= htmlspecialchars($editZone['conditions'] ?? '') ?></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="flex-1 py-3 px-4 bg-emerald-800 hover:bg-emerald-900 text-white font-bold rounded-xl transition shadow">
                        <?= $editZone ? 'Mettre à jour la zone' : 'Enregistrer la zone' ?>
                    </button>
                    <?php if ($editZone): ?>
                        <a href="<?= BASE_URL ?>/admin/delivery_zones.php" class="px-3 py-3 bg-stone-100 text-stone-600 font-bold rounded-xl hover:bg-stone-200">
                            Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Liste des Zones Configurées (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-stone-100">
                <h3 class="font-bold text-sm text-stone-900">Zones Actuelles (<?= count($zones) ?>)</h3>
            </div>

            <div class="divide-y divide-stone-100 text-xs">
                <?php foreach ($zones as $z): ?>
                    <div class="p-4 flex items-center justify-between hover:bg-stone-50/70 transition">
                        <div class="space-y-1 max-w-sm">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-stone-900 text-sm"><?= htmlspecialchars($z['nom_zone']) ?></h4>
                                <span class="font-black text-emerald-800 text-xs"><?= formatPrice($z['tarif_xaf']) ?></span>
                            </div>
                            <p class="text-stone-500 text-[11px]">Délai : <strong><?= htmlspecialchars($z['delai_estime']) ?></strong></p>
                            <?php if (!empty($z['conditions'])): ?>
                                <p class="text-stone-400 text-[10px] line-clamp-1 italic"><?= htmlspecialchars($z['conditions']) ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <a href="<?= BASE_URL ?>/admin/delivery_zones.php?toggle_actif=<?= $z['id'] ?>" class="text-[11px] font-bold px-2 py-1 rounded-full border transition <?= $z['actif'] ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' ?>">
                                <?= $z['actif'] ? 'Active' : 'Désactivée' ?>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/delivery_zones.php?edit=<?= $z['id'] ?>" class="p-1.5 rounded-lg bg-stone-100 hover:bg-emerald-800 hover:text-white transition text-stone-700">
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/delivery_zones.php?delete=<?= $z['id'] ?>" class="p-1.5 rounded-lg bg-stone-100 hover:bg-rose-600 hover:text-white transition text-stone-700" onclick="return confirm('Supprimer cette zone de livraison ?')">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

