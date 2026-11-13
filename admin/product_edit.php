<?php
/**
 * Création & Modification de Produit avec Assistant IA
 * Conforme PRD Section 4.1, 4.2 & Section 10
 */

$adminTitle = "Édition Produit";
$activeMenu = "products";
require_once __DIR__ . '/header.php';
require_permission('catalog');

$productId = (int)($_GET['id'] ?? 0);
$product = null;

if ($productId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
}

$categories = $pdo->query("SELECT id, nom FROM categories WHERE statut = 1 ORDER BY ordre ASC")->fetchAll();

$messageSuccess = '';
$messageError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_product'])) {
    $nom = trim($_POST['nom'] ?? '');
    $categorieId = (int)($_POST['categorie_id'] ?? 0);
    $prix = (float)($_POST['prix'] ?? 0);
    $prixPromo = !empty($_POST['prix_promo']) ? (float)$_POST['prix_promo'] : null;
    $stock = (int)($_POST['stock'] ?? 0);
    $seuilAlerte = (int)($_POST['seuil_alerte'] ?? 5);
    $descriptionCourte = trim($_POST['description_courte'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $composition = trim($_POST['composition'] ?? '');
    $origine = trim($_POST['origine'] ?? '');
    $conseils = trim($_POST['conseils_utilisation'] ?? '');
    $precautions = trim($_POST['precautions'] ?? '');
    $conservation = trim($_POST['conservation'] ?? '');
    $enVedette = !empty($_POST['en_vedette']) ? 1 : 0;
    $nouveaute = !empty($_POST['nouveaute']) ? 1 : 0;
    $disponible = !empty($_POST['disponible']) ? 1 : 0;

    if (empty($nom) || $prix <= 0) {
        $messageError = "Le nom du produit et un prix supérieur à 0 XAF sont obligatoires.";
    } else {
        $slug = slugify($nom);
        // Gestion de photo si uploadée
        $photoPrincipale = $product['photo_principale'] ?? '';
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                $photoName = $slug . '-' . time() . '.' . $ext;
                $target = ROM_ROOT . '/assets/images/products/' . $photoName;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
                    $photoPrincipale = $photoName;
                }
            }
        }

        if ($product) {
            // Mise à jour
            $stmtUp = $pdo->prepare("UPDATE products SET 
                nom = ?, categorie_id = ?, prix = ?, prix_promo = ?, stock = ?, seuil_alerte = ?,
                description_courte = ?, description = ?, composition = ?, origine = ?,
                conseils_utilisation = ?, precautions = ?, conservation = ?,
                en_vedette = ?, nouveaute = ?, disponible = ?, photo_principale = ?,
                date_modification = CURRENT_TIMESTAMP
                WHERE id = ?");

            $stmtUp->execute([
                $nom, $categorieId ?: null, $prix, $prixPromo, $stock, $seuilAlerte,
                $descriptionCourte, $description, $composition, $origine,
                $conseils, $precautions, $conservation,
                $enVedette, $nouveaute, $disponible, $photoPrincipale,
                $product['id']
            ]);
            $messageSuccess = "Fiche produit modifiée avec succès !";
            // Recharger
            $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            $product = $stmt->fetch();
        } else {
            // Insertion nouveau
            $stmtIns = $pdo->prepare("INSERT INTO products (
                nom, slug, categorie_id, prix, prix_promo, stock, seuil_alerte,
                description_courte, description, composition, origine,
                conseils_utilisation, precautions, conservation,
                en_vedette, nouveaute, disponible, photo_principale
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            $stmtIns->execute([
                $nom, $slug, $categorieId ?: null, $prix, $prixPromo, $stock, $seuilAlerte,
                $descriptionCourte, $description, $composition, $origine,
                $conseils, $precautions, $conservation,
                $enVedette, $nouveaute, $disponible, $photoPrincipale
            ]);
            $newId = $pdo->lastInsertId();

            // Mouvement de stock initial
            if ($stock > 0) {
                $pdo->prepare("INSERT INTO stock_movements (produit_id, type_mouvement, quantite, motif, utilisateur) VALUES (?, 'Entrée', ?, 'Création fiche produit', ?)")
                    ->execute([$newId, $stock, $_SESSION['admin_name'] ?? 'Admin']);
            }

            header('Location: ' . BASE_URL . '/admin/product_edit.php?id=' . $newId . '&created=1');
            exit;
        }
    }
}

if (isset($_GET['created'])) {
    $messageSuccess = "Nouveau produit créé avec succès dans le catalogue !";
}
?>

<div class="space-y-6 max-w-6xl mx-auto">
    
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="<?= BASE_URL ?>/admin/products.php" class="p-2 bg-white rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-50 transition">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
            </a>
            <h1 class="text-xl sm:text-2xl font-black text-stone-900">
                <?= $product ? 'Modifier : ' . htmlspecialchars($product['nom']) : 'Créer une nouvelle fiche produit' ?>
            </h1>
        </div>
        
        <?php if ($product): ?>
            <a href="<?= BASE_URL ?>/produit.php?id=<?= $product['id'] ?>" target="_blank" class="px-3 py-1.5 rounded-lg border border-stone-300 bg-white text-xs font-semibold text-stone-700 hover:bg-stone-50 transition flex items-center gap-1.5">
                <i data-lucide="external-link" class="w-3.5 h-3.5"></i>
                <span>Voir en ligne</span>
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($messageSuccess)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-700"></i>
            <span><?= htmlspecialchars($messageSuccess) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($messageError)): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-700"></i>
            <span><?= htmlspecialchars($messageError) ?></span>
        </div>
    <?php endif; ?>

    <!-- PANNEAU ASSISTANT IA (PRD SECTION 10) -->
    <div class="bg-gradient-to-r from-emerald-950 via-emerald-900 to-emerald-850 text-white rounded-3xl p-6 sm:p-8 shadow-xl space-y-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-emerald-800">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-400 text-stone-950 flex items-center justify-center font-black">
                    ✨
                </div>
                <div>
                    <h3 class="font-extrabold text-base text-white">Assistant IA Rédactionnel (PRD Section 10)</h3>
                    <p class="text-xs text-stone-300">Générez un premier brouillon conforme aux données réelles de votre produit.</p>
                </div>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider bg-emerald-800 text-amber-300 px-3 py-1 rounded-full border border-emerald-700">
                Validation humaine obligatoire
            </span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-amber-300 uppercase mb-1">Nom brut du produit</label>
                <input type="text" id="ai-nom" value="<?= htmlspecialchars($product['nom'] ?? '') ?>" placeholder="Ex: Savon artisanal à la spiruline" class="w-full px-3 py-2 rounded-xl bg-emerald-900/80 border border-emerald-700 text-white placeholder-stone-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-amber-300 uppercase mb-1">Origine / Terroir Tchad</label>
                <input type="text" id="ai-origine" value="<?= htmlspecialchars($product['origine'] ?? '') ?>" placeholder="Ex: Ouadis du Kanem / N'Djamena" class="w-full px-3 py-2 rounded-xl bg-emerald-900/80 border border-emerald-700 text-white placeholder-stone-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-amber-300 uppercase mb-1">Format / Poids</label>
                <input type="text" id="ai-format" placeholder="Ex: Pain de 150g ou Flacon 200ml" class="w-full px-3 py-2 rounded-xl bg-emerald-900/80 border border-emerald-700 text-white placeholder-stone-400 focus:outline-none">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
            <div>
                <label class="block text-[11px] font-bold text-amber-300 uppercase mb-1">Composition confirmée (Ingrédients réels connus)</label>
                <input type="text" id="ai-composition" value="<?= htmlspecialchars($product['composition'] ?? '') ?>" placeholder="Ex: Huile de coco saponifiée, spiruline tchadienne, huile de neem" class="w-full px-3 py-2 rounded-xl bg-emerald-900/80 border border-emerald-700 text-white placeholder-stone-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-bold text-amber-300 uppercase mb-1">Usages connus / Bienfaits réels</label>
                <input type="text" id="ai-usages" value="<?= htmlspecialchars($product['conseils_utilisation'] ?? '') ?>" placeholder="Ex: Toilette quotidienne, peaux à imperfections, mousse onctueuse" class="w-full px-3 py-2 rounded-xl bg-emerald-900/80 border border-emerald-700 text-white placeholder-stone-400 focus:outline-none">
            </div>
        </div>

        <div class="pt-2 flex items-center justify-between">
            <button type="button" id="btn-generate-ai" class="px-5 py-2.5 bg-amber-400 hover:bg-amber-300 text-stone-950 font-black text-xs uppercase tracking-wider rounded-xl transition shadow flex items-center gap-2">
                <i data-lucide="sparkles" class="w-4 h-4"></i>
                <span>Générer un brouillon avec l'IA</span>
            </button>
            <span class="text-[11px] text-stone-400 hidden sm:inline">* Respecte strictement les règles du PRD : aucun ingrédient ni faux label inventé.</span>
        </div>

        <!-- Zone d'affichage du Brouillon IA -->
        <div id="ai-draft-container" class="hidden mt-4 p-5 rounded-2xl bg-white text-stone-800 space-y-3 shadow-lg">
            <div class="flex items-center justify-between border-b border-stone-200 pb-2">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-md bg-amber-100 text-amber-900 text-[10px] font-extrabold uppercase">Brouillon IA généré</span>
                    <span id="ai-provider-badge" class="text-[10px] text-stone-500 font-semibold"></span>
                </div>
                <button type="button" id="btn-apply-ai" class="px-3.5 py-1.5 bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs rounded-lg transition flex items-center gap-1 shadow-sm">
                    <i data-lucide="arrow-down-circle" class="w-3.5 h-3.5"></i>
                    <span>Insérer dans la fiche produit</span>
                </button>
            </div>
            <p id="ai-disclaimer" class="text-[11px] text-stone-500 italic"></p>
            <div class="text-xs space-y-2 bg-stone-50 p-3 rounded-xl border border-stone-200">
                <p><strong>Titre proposé :</strong> <span id="draft-titre" class="text-stone-900 font-semibold"></span></p>
                <p><strong>Description courte :</strong> <span id="draft-desc-courte" class="text-stone-700"></span></p>
                <p><strong>Description détaillée :</strong> <span id="draft-desc-longue" class="text-stone-600 line-clamp-3"></span></p>
                <p><strong>Conseils proposés :</strong> <span id="draft-conseils" class="text-stone-600"></span></p>
            </div>
        </div>
    </div>

    <!-- Formulaire d'édition de la fiche (Validation humaine) -->
    <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <input type="hidden" name="save_product" value="1">

        <!-- Colonne Gauche : Données Textuelles (8 cols) -->
        <div class="lg:col-span-8 space-y-6 bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm">
            
            <div>
                <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Nom du Produit *</label>
                <input type="text" name="nom" id="form-nom" required value="<?= htmlspecialchars($product['nom'] ?? '') ?>" placeholder="Ex: Beurre de Karité Pur Brut du Mandoul (250g)" 
                       class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 font-bold focus:outline-none focus:border-emerald-700">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Famille / Catégorie *</label>
                    <select name="categorie_id" id="form-categorie" class="w-full px-3 py-2.5 rounded-xl border border-stone-300 bg-white text-xs font-semibold focus:outline-none focus:border-emerald-700">
                        <option value="">-- Choisir une catégorie --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($product['categorie_id'] ?? 0) == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Origine / Terroir / Producteur</label>
                    <input type="text" name="origine" id="form-origine" value="<?= htmlspecialchars($product['origine'] ?? '') ?>" placeholder="Ex: Province du Mandoul, Tchad" 
                           class="w-full px-3 py-2.5 text-xs rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Description courte (Accroche catalogue) *</label>
                <textarea name="description_courte" id="form-desc-courte" rows="2" placeholder="Résumé percutant en 2 phrases pour le catalogue..." 
                          class="w-full px-4 py-2.5 text-xs rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700"><?= htmlspecialchars($product['description_courte'] ?? '') ?></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Description Détaillée & Présentation</label>
                <textarea name="description" id="form-desc-longue" rows="6" placeholder="Histoire du produit, méthode de transformation, qualités sahéliennes..." 
                          class="w-full px-4 py-3 text-xs rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Composition & Ingrédients confirmés (PRD 4.1)</label>
                    <textarea name="composition" id="form-composition" rows="3" placeholder="100% beurre de karité pur, huile de balanites..." 
                              class="w-full px-3 py-2 text-xs rounded-xl border border-stone-300"><?= htmlspecialchars($product['composition'] ?? '') ?></textarea>
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Conseils d'utilisation</label>
                    <textarea name="conseils_utilisation" id="form-conseils" rows="3" placeholder="Chauffer au creux de la main avant application..." 
                              class="w-full px-3 py-2 text-xs rounded-xl border border-stone-300"><?= htmlspecialchars($product['conseils_utilisation'] ?? '') ?></textarea>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Précautions d'emploi</label>
                    <input type="text" name="precautions" id="form-precautions" value="<?= htmlspecialchars($product['precautions'] ?? '') ?>" placeholder="Usage externe uniquement..." 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-stone-300">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Conservation & DLUO</label>
                    <input type="text" name="conservation" id="form-conservation" value="<?= htmlspecialchars($product['conservation'] ?? '') ?>" placeholder="24 mois à l'abri de l'humidité..." 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-stone-300">
                </div>
            </div>

        </div>

        <!-- Colonne Droite : Prix, Stocks & Paramètres (4 cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
                <h3 class="font-bold text-sm text-stone-900 pb-2 border-b border-stone-100">Tarification en Franc CFA (XAF)</h3>
                
                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Prix de vente public (XAF) *</label>
                    <input type="number" step="50" name="prix" required value="<?= htmlspecialchars($product['prix'] ?? 0) ?>" 
                           class="w-full px-3 py-2.5 text-sm font-black rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Prix Promotionnel (Optionnel)</label>
                    <input type="number" step="50" name="prix_promo" value="<?= htmlspecialchars($product['prix_promo'] ?? '') ?>" placeholder="Laisser vide si pas de promo" 
                           class="w-full px-3 py-2 text-xs rounded-xl border border-stone-300">
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
                <h3 class="font-bold text-sm text-stone-900 pb-2 border-b border-stone-100">Gestion des Stocks (PRD 9)</h3>
                
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Stock actuel</label>
                        <input type="number" name="stock" value="<?= htmlspecialchars($product['stock'] ?? 0) ?>" 
                               class="w-full px-3 py-2 font-black rounded-xl border border-stone-300">
                    </div>
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Seuil Alerte</label>
                        <input type="number" name="seuil_alerte" value="<?= htmlspecialchars($product['seuil_alerte'] ?? 5) ?>" 
                               class="w-full px-3 py-2 font-bold rounded-xl border border-stone-300">
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
                <h3 class="font-bold text-sm text-stone-900 pb-2 border-b border-stone-100">Visuel & Publication</h3>
                
                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Image du Produit</label>
                    <?php if (!empty($product['photo_principale'])): ?>
                        <div class="mb-2 w-20 h-20 rounded-xl overflow-hidden border border-stone-200">
                            <img src="<?= getProductImageUrl($product['photo_principale'], $product['slug'] ?? '') ?>" alt="" class="w-full h-full object-cover">
                        </div>
                    <?php endif; ?>
                    <input type="file" name="photo" accept="image/*" class="w-full text-xs text-stone-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-emerald-50 file:text-emerald-800 hover:file:bg-emerald-100">
                </div>

                <div class="space-y-2 pt-2 border-t border-stone-100 text-xs font-semibold text-stone-700">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="disponible" value="1" <?= (!isset($product) || $product['disponible']) ? 'checked' : '' ?> class="rounded text-emerald-800">
                        <span>Disponible à la vente (Publié)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="en_vedette" value="1" <?= (!empty($product['en_vedette'])) ? 'checked' : '' ?> class="rounded text-emerald-800">
                        <span>Mettre en avant sur l'accueil</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="nouveaute" value="1" <?= (!empty($product['nouveaute'])) ? 'checked' : '' ?> class="rounded text-emerald-800">
                        <span>Badge "Nouveauté"</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 bg-emerald-800 hover:bg-emerald-900 text-white font-black text-xs uppercase tracking-wider rounded-xl transition shadow flex items-center justify-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    <span>Enregistrer la fiche produit</span>
                </button>
            </div>

        </div>

    </form>
</div>

<script>
    // Logique AJAX Assistant IA (PRD Section 10)
    const btnGenAI = document.getElementById('btn-generate-ai');
    const draftContainer = document.getElementById('ai-draft-container');
    const btnApplyAI = document.getElementById('btn-apply-ai');

    let generatedDraftData = null;

    btnGenAI.addEventListener('click', async () => {
        const nom = document.getElementById('ai-nom').value.trim() || document.getElementById('form-nom').value.trim();
        const origine = document.getElementById('ai-origine').value.trim();
        const format = document.getElementById('ai-format').value.trim();
        const composition = document.getElementById('ai-composition').value.trim();
        const usages = document.getElementById('ai-usages').value.trim();
        const catSelect = document.getElementById('form-categorie');
        const categorie = catSelect.selectedIndex > 0 ? catSelect.options[catSelect.selectedIndex].text : '';

        if (!nom) {
            alert('Veuillez au moins saisir le nom du produit.');
            return;
        }

        btnGenAI.disabled = true;
        btnGenAI.innerHTML = `<span class="inline-block animate-spin mr-1">⏳</span> Rédaction en cours...`;

        try {
            const res = await fetch('<?= BASE_URL ?>/api/ai_generate.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    nom: nom,
                    origine: origine,
                    format: format,
                    composition: composition,
                    usages: usages,
                    categorie: categorie
                })
            });

            const json = await res.json();
            if (json.success && json.data) {
                generatedDraftData = json.data;
                document.getElementById('ai-provider-badge').textContent = '• ' + json.provider;
                document.getElementById('ai-disclaimer').textContent = json.disclaimer;
                document.getElementById('draft-titre').textContent = json.data.titre || nom;
                document.getElementById('draft-desc-courte').textContent = json.data.description_courte || '';
                document.getElementById('draft-desc-longue').textContent = json.data.description_detaillee || '';
                document.getElementById('draft-conseils').textContent = json.data.conseils_utilisation || '';

                draftContainer.classList.remove('hidden');
                draftContainer.scrollIntoView({ behavior: 'smooth' });
            } else {
                alert(json.error || 'Erreur lors de la génération.');
            }
        } catch (e) {
            alert('Erreur réseau lors de la génération assistée.');
        } finally {
            btnGenAI.disabled = false;
            btnGenAI.innerHTML = `<i data-lucide="sparkles" class="w-4 h-4"></i> <span>Générer un brouillon avec l'IA</span>`;
            if (window.lucide) window.lucide.createIcons();
        }
    });

    btnApplyAI.addEventListener('click', () => {
        if (!generatedDraftData) return;
        if (confirm('Remplacer les champs du formulaire par ce brouillon validé ?')) {
            if (generatedDraftData.titre) document.getElementById('form-nom').value = generatedDraftData.titre;
            if (generatedDraftData.description_courte) document.getElementById('form-desc-courte').value = generatedDraftData.description_courte;
            if (generatedDraftData.description_detaillee) document.getElementById('form-desc-longue').value = generatedDraftData.description_detaillee;
            if (generatedDraftData.conseils_utilisation) document.getElementById('form-conseils').value = generatedDraftData.conseils_utilisation;
            if (generatedDraftData.precautions) document.getElementById('form-precautions').value = generatedDraftData.precautions;

            const aiOrigine = document.getElementById('ai-origine').value.trim();
            if (aiOrigine) document.getElementById('form-origine').value = aiOrigine;

            const aiComp = document.getElementById('ai-composition').value.trim();
            if (aiComp) document.getElementById('form-composition').value = aiComp;

            document.getElementById('form-nom').scrollIntoView({ behavior: 'smooth' });
            showToast ? showToast('Brouillon inséré ! Vous pouvez maintenant le peaufiner et l’enregistrer.', 'success') : alert('Brouillon inséré !');
        }
    });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

