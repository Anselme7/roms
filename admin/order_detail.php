<?php
/**
 * Fiche Détail & Traitement Commande - ROM'S NATURAL
 * Conforme PRD Section 8 & 11
 */

$adminTitle = "Traitement Commande";
$activeMenu = "orders";
require_once __DIR__ . '/header.php';
require_permission('orders');

$orderId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . BASE_URL . '/admin/orders.php');
    exit;
}

// Articles
$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE commande_id = ?");
$stmtItems->execute([$order['id']]);
$items = $stmtItems->fetchAll();

$messageSuccess = '';
$messageError = '';

// Mise à jour de la commande
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nouveauStatut = trim($_POST['statut_commande'] ?? $order['statut_commande']);
    $nouveauPaiement = trim($_POST['statut_paiement'] ?? $order['statut_paiement']);
    $livreurNom = trim($_POST['livreur_nom'] ?? $order['livreur_nom']);
    $notesInternes = trim($_POST['notes_internes'] ?? $order['notes_internes']);
    $motifAnnulation = trim($_POST['motif_annulation'] ?? $order['motif_annulation']);
    $referencePaiement = trim($_POST['reference_paiement'] ?? $order['reference_paiement']);

    // Si le statut passe à Annulée sans motif
    if ($nouveauStatut === 'Annulée' && empty($motifAnnulation)) {
        $messageError = "Le motif d'annulation est obligatoire lorsque la commande est annulée (PRD Section 8.1).";
    } else {
        $datePaiement = ($nouveauPaiement === 'Payé' && empty($order['date_paiement'])) ? date('Y-m-d H:i:s') : $order['date_paiement'];

        $stmtUp = $pdo->prepare("UPDATE orders SET 
            statut_commande = ?,
            statut_paiement = ?,
            livreur_nom = ?,
            notes_internes = ?,
            motif_annulation = ?,
            reference_paiement = ?,
            date_paiement = ?,
            date_mise_a_jour = CURRENT_TIMESTAMP
            WHERE id = ?");

        $stmtUp->execute([
            $nouveauStatut,
            $nouveauPaiement,
            $livreurNom,
            $notesInternes,
            $motifAnnulation,
            $referencePaiement,
            $datePaiement,
            $order['id']
        ]);

        $messageSuccess = "Commande mise à jour avec succès !";

        // Rechargement des données fraîches
        $stmt->execute([$orderId]);
        $order = $stmt->fetch();
    }
}

$orderStatuses = [
    'Nouvelle / reçue',
    'Confirmée',
    'En préparation',
    'Prête',
    'En livraison',
    'Terminée',
    'Annulée',
    'En attente'
];

$paymentStatuses = [
    'En attente',
    'À encaisser à la livraison',
    'Payé',
    'Échec ou rejeté',
    'Remboursé'
];
?>

<div class="space-y-6 max-w-6xl mx-auto">
    
    <!-- Barre supérieure de navigation -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <a href="<?= BASE_URL ?>/admin/orders.php" class="p-2 bg-white rounded-xl border border-stone-200 text-stone-600 hover:bg-stone-50 transition" title="Retour aux commandes">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                </a>
                <h1 class="text-xl sm:text-2xl font-black text-stone-900 font-mono">
                    Commande <?= htmlspecialchars($order['reference']) ?>
                </h1>
            </div>
            <p class="text-xs text-stone-500 mt-1 pl-11">
                Enregistrée le <?= formatDateFr($order['date_creation']) ?>
            </p>
        </div>

        <!-- Actions rapides : Impression & WhatsApp Client (PRD Section 11) -->
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-4 py-2 bg-white border border-stone-300 hover:bg-stone-50 rounded-xl text-stone-700 font-bold text-xs transition flex items-center gap-1.5 shadow-xs">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Imprimer bon de commande</span>
            </button>

            <a href="<?= getWhatsAppAdminToCustomerLink($order['client_telephone'], $order['reference'], $order['statut_commande'], $order['client_nom']) ?>" 
               target="_blank" rel="noopener noreferrer"
               class="px-4 py-2 bg-[#25D366] hover:bg-[#20ba59] text-white font-extrabold text-xs rounded-xl transition flex items-center gap-1.5 shadow-sm">
                <i data-lucide="message-circle" class="w-4 h-4 fill-white stroke-none"></i>
                <span>Notifier le client sur WhatsApp</span>
            </a>
        </div>
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Colonne Gauche : Détails & Facturette Imprimable (7 cols) -->
        <div class="lg:col-span-7 space-y-6" id="printable-invoice">
            
            <!-- Informations Client & Livraison -->
            <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                    <h3 class="font-bold text-sm text-stone-900">Coordonnées Destinataire & Lieu</h3>
                    <div class="flex items-center gap-2">
                        <?= orderStatusBadge($order['statut_commande']) ?>
                        <?= paymentStatusBadge($order['statut_paiement']) ?>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                    <div>
                        <span class="text-stone-400 font-bold uppercase text-[10px]">Client / Destinataire :</span>
                        <p class="font-bold text-stone-900 mt-0.5"><?= htmlspecialchars($order['client_nom']) ?></p>
                        <p class="text-stone-600 mt-1 flex items-center gap-1">
                            <i data-lucide="phone" class="w-3.5 h-3.5 text-stone-400"></i>
                            <a href="tel:<?= preg_replace('/[^0-9]/', '', $order['client_telephone']) ?>" class="hover:underline font-semibold"><?= htmlspecialchars($order['client_telephone']) ?></a>
                        </p>
                        <?php if (!empty($order['client_email'])): ?>
                            <p class="text-stone-500 mt-0.5 flex items-center gap-1">
                                <i data-lucide="mail" class="w-3.5 h-3.5 text-stone-400"></i>
                                <span><?= htmlspecialchars($order['client_email']) ?></span>
                            </p>
                        <?php endif; ?>
                    </div>

                    <div>
                        <span class="text-stone-400 font-bold uppercase text-[10px]">Lieu de Réception :</span>
                        <p class="font-bold text-emerald-900 mt-0.5"><?= htmlspecialchars($order['mode_reception']) ?></p>
                        <p class="text-stone-700 font-medium"><?= htmlspecialchars($order['zone_nom']) ?></p>
                        <p class="text-stone-500 mt-1"><?= htmlspecialchars($order['adresse_detaillee']) ?></p>
                    </div>
                </div>

                <?php if (!empty($order['notes_client'])): ?>
                    <div class="p-3 bg-stone-50 rounded-xl border border-stone-200 text-xs">
                        <span class="font-bold text-stone-700 block mb-0.5">Note du client :</span>
                        <p class="italic text-stone-600">"<?= htmlspecialchars($order['notes_client']) ?>"</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Articles Commandés -->
            <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
                <h3 class="font-bold text-sm text-stone-900 pb-3 border-b border-stone-100">
                    Articles Commandés (<?= count($items) ?>)
                </h3>

                <div class="divide-y divide-stone-100">
                    <?php foreach ($items as $it): ?>
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="flex-1 min-w-0 pr-4">
                                <h4 class="font-bold text-stone-900 truncate"><?= htmlspecialchars($it['nom_produit']) ?></h4>
                                <?php if (!empty($it['nom_variante'])): ?>
                                    <span class="text-emerald-800 text-[11px] font-semibold block"><?= htmlspecialchars($it['nom_variante']) ?></span>
                                <?php endif; ?>
                                <span class="text-stone-500"><?= $it['quantite'] ?> × <?= formatPrice($it['prix_unitaire']) ?></span>
                            </div>
                            <span class="font-black text-stone-900 text-sm whitespace-nowrap"><?= formatPrice($it['sous_total']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="pt-4 border-t border-stone-100 space-y-2 text-xs">
                    <div class="flex justify-between text-stone-600">
                        <span>Sous-total articles :</span>
                        <span class="font-bold text-stone-900"><?= formatPrice($order['sous_total']) ?></span>
                    </div>
                    <div class="flex justify-between text-stone-600">
                        <span>Frais d'acheminement / livraison :</span>
                        <span class="font-bold text-stone-900"><?= formatPrice($order['frais_livraison']) ?></span>
                    </div>
                    <div class="flex justify-between text-base pt-3 border-t border-stone-200">
                        <span class="font-black text-stone-900">Total Général :</span>
                        <span class="font-black text-emerald-950 text-xl"><?= formatPrice($order['total']) ?></span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Colonne Droite : Formulaire de Mise à Jour du Statut & Livreur (5 cols) -->
        <div class="lg:col-span-5 space-y-6 no-print">
            <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-5">
                <h3 class="font-bold text-base text-stone-900 pb-3 border-b border-stone-100">
                    Mise à jour & Logistique
                </h3>

                <form method="POST" class="space-y-4 text-xs">
                    
                    <!-- Statut Commande -->
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Statut de la commande (PRD 8.1) *</label>
                        <select name="statut_commande" id="select-statut" class="w-full px-3 py-2.5 rounded-xl border border-stone-300 font-bold focus:outline-none focus:border-emerald-700">
                            <?php foreach ($orderStatuses as $st): ?>
                                <option value="<?= $st ?>" <?= $order['statut_commande'] === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Motif Annulation (si annulée) -->
                    <div id="box-motif-annulation" class="<?= $order['statut_commande'] === 'Annulée' ? '' : 'hidden' ?> space-y-1">
                        <label class="block font-bold text-rose-700 uppercase">Motif obligatoire d'annulation *</label>
                        <input type="text" name="motif_annulation" value="<?= htmlspecialchars($order['motif_annulation'] ?? '') ?>" placeholder="Ex: Rupture fournisseur, client injoignable..." 
                               class="w-full px-3 py-2.5 rounded-xl border border-rose-300 bg-rose-50/50">
                    </div>

                    <!-- Statut Paiement -->
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Statut de paiement (PRD 6.4) *</label>
                        <select name="statut_paiement" class="w-full px-3 py-2.5 rounded-xl border border-stone-300 font-bold focus:outline-none focus:border-emerald-700">
                            <?php foreach ($paymentStatuses as $pst): ?>
                                <option value="<?= $pst ?>" <?= $order['statut_paiement'] === $pst ? 'selected' : '' ?>><?= $pst ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="text-[11px] text-stone-500 mt-1 block">Mode choisi : <?= htmlspecialchars($order['mode_paiement']) ?></span>
                    </div>

                    <!-- Réf transaction paiement -->
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Référence Transaction / Bordereau</label>
                        <input type="text" name="reference_paiement" value="<?= htmlspecialchars($order['reference_paiement'] ?? '') ?>" placeholder="Ex: SMS Airtel Money ou N° virement" 
                               class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>

                    <!-- Assignation Livreur / Transporteur -->
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Livreur ou Transporteur Partenaire (PRD 8.2)</label>
                        <input type="text" name="livreur_nom" value="<?= htmlspecialchars($order['livreur_nom'] ?? '') ?>" placeholder="Ex: Aliou (Moto 04) ou Bus Express Moundou" 
                               class="w-full px-3 py-2 rounded-xl border border-stone-300">
                    </div>

                    <!-- Notes Internes -->
                    <div>
                        <label class="block font-bold text-stone-700 uppercase mb-1">Notes Internes de l'Équipe</label>
                        <textarea name="notes_internes" rows="3" placeholder="Informations confidentielles, remarques sur la livraison..." 
                                  class="w-full px-3 py-2 rounded-xl border border-stone-300"><?= htmlspecialchars($order['notes_internes'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="w-full py-3.5 px-4 bg-emerald-800 hover:bg-emerald-900 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow">
                        Enregistrer les modifications
                    </button>
                </form>
            </div>

            <!-- Récap Historique -->
            <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200 text-xs text-stone-500 space-y-1">
                <p>Création : <strong><?= formatDateFr($order['date_creation']) ?></strong></p>
                <p>Dernière modification : <strong><?= formatDateFr($order['date_mise_a_jour']) ?></strong></p>
                <?php if (!empty($order['date_paiement'])): ?>
                    <p class="text-emerald-800 font-semibold">Paiement validé le : <?= formatDateFr($order['date_paiement']) ?></p>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script>
    const selectStatut = document.getElementById('select-statut');
    const boxMotif = document.getElementById('box-motif-annulation');

    selectStatut.addEventListener('change', () => {
        if (selectStatut.value === 'Annulée') {
            boxMotif.classList.remove('hidden');
        } else {
            boxMotif.classList.add('hidden');
        }
    });
</script>

<?php require_once __DIR__ . '/footer.php'; ?>

