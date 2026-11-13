<?php
/**
 * Page de Confirmation de Commande - ROM'S NATURAL
 * Conforme PRD Section 6.1
 */

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/functions.php';

$ref = trim($_GET['ref'] ?? '');
if (empty($ref)) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM orders WHERE reference = ?");
$stmt->execute([$ref]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

// Articles
$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE commande_id = ?");
$stmtItems->execute([$order['id']]);
$items = $stmtItems->fetchAll();

$pageTitle = "Commande confirmée #" . $order['reference'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    
    <!-- En-tête Succès -->
    <div class="text-center space-y-4 mb-10">
        <div class="w-20 h-20 bg-emerald-100 text-emerald-800 rounded-full flex items-center justify-center mx-auto shadow-inner">
            <i data-lucide="check" class="w-10 h-10 stroke-[3]"></i>
        </div>
        <span class="text-xs font-bold uppercase tracking-widest text-emerald-800 bg-emerald-50 px-3 py-1 rounded-full border border-emerald-200">
            Commande enregistrée avec succès
        </span>
        <h1 class="text-2xl sm:text-4xl font-black text-stone-900">Merci pour votre confiance, <?= htmlspecialchars($order['client_nom']) ?> !</h1>
        <p class="text-stone-600 text-sm max-w-lg mx-auto">
            Votre commande a bien été reçue par notre équipe ROM'S Natural. Vous pouvez suivre son avancement en direct avec votre référence unique ci-dessous.
        </p>

        <!-- Référence cliquable / copiable -->
        <div class="inline-flex items-center gap-3 px-6 py-3 bg-stone-100 rounded-2xl border border-stone-300 font-mono text-base font-black text-emerald-950">
            <span>Réf : <?= htmlspecialchars($order['reference']) ?></span>
            <button onclick="navigator.clipboard.writeText('<?= $order['reference'] ?>'); showToast('Référence copiée !', 'success')" class="text-xs text-stone-500 hover:text-stone-900" title="Copier la référence">
                <i data-lucide="copy" class="w-4 h-4"></i>
            </button>
        </div>
    </div>

    <!-- Instructions Spécifiques selon Paiement -->
    <div class="bg-amber-50 border border-amber-200 rounded-3xl p-6 sm:p-8 mb-8 space-y-3">
        <div class="flex items-center gap-2 text-amber-900 font-bold text-sm">
            <i data-lucide="info" class="w-5 h-5 text-amber-700"></i>
            <span>Modalité de paiement : <?= htmlspecialchars($order['mode_paiement']) ?></span>
        </div>

        <?php if ($order['mode_paiement'] === 'Paiement à la livraison'): ?>
            <p class="text-xs text-amber-900 leading-relaxed">
                Le montant total de <strong><?= formatPrice($order['total']) ?></strong> sera à régler en espèces directement au livreur lors de la remise de votre commande.
            </p>
        <?php elseif (str_contains($order['mode_paiement'], 'Mobile Money')): ?>
            <div class="text-xs text-amber-900 space-y-2">
                <p>Pour finaliser le règlement de votre commande par Mobile Money :</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs pt-1">
                    <div class="p-3 bg-white rounded-xl border border-amber-200">
                        <strong class="text-red-700 block">Airtel Money Tchad :</strong>
                        Tapez <strong>*436#</strong> puis envoyez <strong><?= formatPrice($order['total']) ?></strong> au <strong><?= htmlspecialchars(getSetting('payment_airtel_number', '+235 66 28 00 00')) ?></strong>.
                    </div>
                    <div class="p-3 bg-white rounded-xl border border-amber-200">
                        <strong class="text-blue-700 block">Moov Money Tchad :</strong>
                        Tapez <strong>*156#</strong> puis envoyez <strong><?= formatPrice($order['total']) ?></strong> au <strong><?= htmlspecialchars(getSetting('payment_moov_number', '+235 99 28 00 00')) ?></strong>.
                    </div>
                </div>
                <p class="pt-1 text-[11px] text-amber-800">
                    * Notre équipe vérifiera la réception de la transaction avant l'expédition de votre colis.
                </p>
            </div>
        <?php elseif (str_contains($order['mode_paiement'], 'Virement')): ?>
            <div class="text-xs text-amber-900 space-y-1">
                <p>Veuillez effectuer votre virement ou dépôt bancaire avec les coordonnées suivantes :</p>
                <pre class="bg-white p-3 rounded-xl border border-amber-200 font-mono text-xs"><?= htmlspecialchars(getSetting('payment_bank_details')) ?></pre>
                <p class="text-[11px] text-amber-800">Précisez en motif de virement votre référence : <strong><?= htmlspecialchars($order['reference']) ?></strong>.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Récapitulatif Complet de la commande -->
    <div class="bg-white rounded-3xl border border-stone-200 overflow-hidden shadow-sm space-y-6 p-6 sm:p-8" id="printable-invoice">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-6 border-b border-stone-100">
            <div>
                <span class="text-xs text-stone-400 font-semibold uppercase tracking-wider">Statut actuel</span>
                <div class="mt-1 flex items-center gap-2">
                    <?= orderStatusBadge($order['statut_commande']) ?>
                    <?= paymentStatusBadge($order['statut_paiement']) ?>
                </div>
            </div>
            <div class="text-left sm:text-right text-xs text-stone-500">
                <span>Date de commande :</span>
                <p class="font-bold text-stone-900 mt-0.5"><?= formatDateFr($order['date_creation']) ?></p>
            </div>
        </div>

        <!-- Détails de Livraison -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-xs text-stone-600 bg-stone-50 p-4 rounded-2xl border border-stone-200">
            <div>
                <span class="font-bold text-stone-900 block mb-1">Destinataire :</span>
                <p class="font-semibold text-stone-800"><?= htmlspecialchars($order['client_nom']) ?></p>
                <p>Tél : <?= htmlspecialchars($order['client_telephone']) ?></p>
                <?php if (!empty($order['client_email'])): ?>
                    <p>Email : <?= htmlspecialchars($order['client_email']) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <span class="font-bold text-stone-900 block mb-1">Lieu & Mode de réception :</span>
                <p class="font-semibold text-emerald-900"><?= htmlspecialchars($order['mode_reception']) ?></p>
                <p><?= htmlspecialchars($order['zone_nom']) ?></p>
                <p class="text-stone-500 mt-0.5"><?= htmlspecialchars($order['adresse_detaillee']) ?></p>
            </div>
        </div>

        <!-- Lignes Articles -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-stone-500">Articles commandés</h3>
            <div class="divide-y divide-stone-100 border-t border-b border-stone-100">
                <?php foreach ($items as $item): ?>
                    <div class="py-3 flex items-center justify-between text-xs">
                        <div class="flex-1 min-w-0 pr-4">
                            <h4 class="font-bold text-stone-900 truncate"><?= htmlspecialchars($item['nom_produit']) ?></h4>
                            <?php if (!empty($item['nom_variante'])): ?>
                                <span class="text-emerald-800 text-[11px] block"><?= htmlspecialchars($item['nom_variante']) ?></span>
                            <?php endif; ?>
                            <span class="text-stone-400">Quantité : <?= $item['quantite'] ?> × <?= formatPrice($item['prix_unitaire']) ?></span>
                        </div>
                        <span class="font-black text-stone-900 text-sm"><?= formatPrice($item['sous_total']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Totaux -->
        <div class="space-y-2 text-xs text-stone-600 pt-2">
            <div class="flex justify-between">
                <span>Sous-total articles :</span>
                <span class="font-bold text-stone-900"><?= formatPrice($order['sous_total']) ?></span>
            </div>
            <div class="flex justify-between">
                <span>Frais de livraison :</span>
                <span class="font-bold text-stone-900"><?= formatPrice($order['frais_livraison']) ?></span>
            </div>
            <div class="flex justify-between text-base pt-3 border-t border-stone-200">
                <span class="font-black text-stone-900">Total général :</span>
                <span class="font-black text-emerald-950 text-xl"><?= formatPrice($order['total']) ?></span>
            </div>
        </div>

    </div>

    <!-- Actions Utilisateur -->
    <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4 no-print">
        <a href="<?= getWhatsAppOrderLink($order['reference'], $order['total'], $order['client_nom']) ?>" 
           target="_blank" rel="noopener noreferrer"
           class="w-full sm:w-auto px-6 py-3.5 bg-[#25D366] hover:bg-[#20ba59] text-white font-extrabold text-xs rounded-2xl transition shadow-md flex items-center justify-center gap-2">
            <i data-lucide="message-circle" class="w-4 h-4 fill-white stroke-none"></i>
            <span>Notifier ROM'S Natural sur WhatsApp</span>
        </a>

        <button onclick="window.print()" class="w-full sm:w-auto px-6 py-3.5 bg-white border border-stone-300 hover:bg-stone-100 text-stone-700 font-bold text-xs rounded-2xl transition flex items-center justify-center gap-2 shadow-sm">
            <i data-lucide="printer" class="w-4 h-4"></i>
            <span>Imprimer mon reçu</span>
        </button>

        <a href="<?= BASE_URL ?>/catalogue.php" class="w-full sm:w-auto px-6 py-3.5 bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs rounded-2xl transition flex items-center justify-center gap-2 shadow-sm">
            <span>Retourner à la boutique</span>
        </a>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

