<?php
/**
 * Gestion des Familles & Catégories - ROM'S NATURAL
 * Conforme PRD Section 3
 */

$adminTitle = "Familles & Catégories";
$activeMenu = "categories";
require_once __DIR__ . '/header.php';
require_permission('catalog');

$message = '';

// Ajout / Édition catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_cat'])) {
    $catId = (int)($_POST['cat_id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $ordre = (int)($_POST['ordre'] ?? 0);

    if (!empty($nom)) {
        $slug = slugify($nom);
        if ($catId > 0) {
            $pdo->prepare("UPDATE categories SET nom = ?, description = ?, ordre = ? WHERE id = ?")
                ->execute([$nom, $description, $ordre, $catId]);
            $message = "Catégorie mise à jour avec succès.";
        } else {
            $pdo->prepare("INSERT INTO categories (nom, slug, description, ordre, statut) VALUES (?, ?, ?, ?, 1)")
                ->execute([$nom, $slug, $description, $ordre]);
            $message = "Nouvelle famille créée avec succès.";
        }
    }
}

// Activer / Désactiver
if (isset($_GET['toggle_statut'])) {
    $cId = (int)$_GET['toggle_statut'];
    $pdo->prepare("UPDATE categories SET statut = CASE WHEN statut = 1 THEN 0 ELSE 1 END WHERE id = ?")->execute([$cId]);
    header('Location: ' . BASE_URL . '/admin/categories.php');
    exit;
}

// Supprimer
if (isset($_GET['delete'])) {
    $cId = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$cId]);
    header('Location: ' . BASE_URL . '/admin/categories.php');
    exit;
}

$cats = $pdo->query("SELECT c.*, COUNT(p.id) as total_prods 
    FROM categories c 
    LEFT JOIN products p ON p.categorie_id = c.id 
    GROUP BY c.id 
    ORDER BY c.ordre ASC")->fetchAll();

$editCat = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$editId]);
    $editCat = $stmt->fetch();
}
?>

<div class="space-y-6 max-w-6xl mx-auto">
    
    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm">
        <h2 class="text-xl font-bold text-stone-900">Familles & Catégories de Produits</h2>
        <p class="text-xs text-stone-500 mt-1">
            Les catégories ne sont pas figées dans le code (PRD Section 3). Vous pouvez créer, modifier ou réorganiser toutes les familles.
        </p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-700"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Formulaire Création / Modification (4 cols) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-stone-900 pb-2 border-b border-stone-100">
                <?= $editCat ? 'Modifier la famille' : 'Ajouter une nouvelle famille' ?>
            </h3>

            <form method="POST" class="space-y-4 text-xs">
                <input type="hidden" name="save_cat" value="1">
                <input type="hidden" name="cat_id" value="<?= $editCat['id'] ?? 0 ?>">

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Nom de la Catégorie / Famille *</label>
                    <input type="text" name="nom" required value="<?= htmlspecialchars($editCat['nom'] ?? '') ?>" placeholder="Ex: Savons & Beurres Végétaux" 
                           class="w-full px-3 py-2.5 rounded-xl border border-stone-300 font-semibold focus:outline-none focus:border-emerald-700">
                </div>

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Description</label>
                    <textarea name="description" rows="3" placeholder="Présentation succincte pour le site public..." 
                              class="w-full px-3 py-2 rounded-xl border border-stone-300"><?= htmlspecialchars($editCat['description'] ?? '') ?></textarea>
                </div>

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Ordre d'affichage</label>
                    <input type="number" name="ordre" value="<?= htmlspecialchars($editCat['ordre'] ?? count($cats) + 1) ?>" 
                           class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    <span class="text-[10px] text-stone-400 mt-0.5 block">1 = premier dans le menu</span>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="flex-1 py-3 px-4 bg-emerald-800 hover:bg-emerald-900 text-white font-bold rounded-xl transition shadow">
                        <?= $editCat ? 'Mettre à jour' : 'Créer la famille' ?>
                    </button>
                    <?php if ($editCat): ?>
                        <a href="<?= BASE_URL ?>/admin/categories.php" class="px-3 py-3 bg-stone-100 hover:bg-stone-200 text-stone-600 font-bold rounded-xl transition">
                            Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Liste des Catégories Existantes (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-stone-100 flex items-center justify-between">
                <h3 class="font-bold text-sm text-stone-900">Familles Enregistrées (<?= count($cats) ?>)</h3>
            </div>

            <div class="divide-y divide-stone-100 text-xs">
                <?php foreach ($cats as $c): ?>
                    <div class="p-4 flex items-center justify-between hover:bg-stone-50/70 transition">
                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-stone-100 text-stone-600 font-mono font-bold flex items-center justify-center text-[10px]">
                                    <?= $c['ordre'] ?>
                                </span>
                                <h4 class="font-bold text-stone-900 text-sm"><?= htmlspecialchars($c['nom']) ?></h4>
                                <span class="text-[10px] bg-emerald-50 text-emerald-800 font-bold px-2 py-0.5 rounded-full">
                                    <?= $c['total_prods'] ?> produit<?= $c['total_prods'] > 1 ? 's' : '' ?>
                                </span>
                            </div>
                            <p class="text-stone-500 text-[11px] max-w-md line-clamp-1"><?= htmlspecialchars($c['description']) ?></p>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="<?= BASE_URL ?>/admin/categories.php?toggle_statut=<?= $c['id'] ?>" class="text-[11px] font-bold px-2 py-1 rounded-full border <?= $c['statut'] ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-stone-100 text-stone-400 border-stone-200' ?>">
                                <?= $c['statut'] ? 'Active' : 'Désactivée' ?>
                            </a>
                            <a href="<?= BASE_URL ?>/admin/categories.php?edit=<?= $c['id'] ?>" class="p-1.5 rounded-lg bg-stone-100 hover:bg-emerald-800 hover:text-white transition text-stone-700">
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </a>
                            <?php if ($c['total_prods'] == 0): ?>
                                <a href="<?= BASE_URL ?>/admin/categories.php?delete=<?= $c['id'] ?>" class="p-1.5 rounded-lg bg-stone-100 hover:bg-rose-600 hover:text-white transition text-stone-700" onclick="return confirm('Supprimer cette catégorie ?')">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

