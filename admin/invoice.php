<?php
/**
 * Bon de Commande & Bordereau de Livraison Imprimable (Facturette Pro)
 * Conforme PRD Section 8.2
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_permission('orders');

$orderId = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    die("Commande introuvable.");
}

$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE commande_id = ?");
$stmtItems->execute([$order['id']]);
$items = $stmtItems->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bon de Commande <?= htmlspecialchars($order['reference']) ?> | ROM'S NATURAL</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Space+Mono:wght@700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f5f5f5; }
        .font-mono-bold { font-family: 'Space Mono', monospace; font-weight: 700; }
        @media print {
            body { background: white; }
            .no-print { display: none !important; }
            .print-shadow-none { box-shadow: none !important; border: 1px solid #ddd !important; }
        }
    </style>
</head>
<body class="py-8 px-4">

    <!-- Boutons d'impression et retour -->
    <div class="max-w-3xl mx-auto mb-6 flex items-center justify-between no-print">
        <a href="<?= BASE_URL ?>/admin/order_detail.php?id=<?= $order['id'] ?>" class="text-xs font-bold text-stone-600 hover:text-emerald-800 transition">
            ← Revenir au détail de la commande
        </a>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2.5 bg-emerald-800 text-white font-extrabold text-xs rounded-xl hover:bg-emerald-900 transition shadow">
                🖨️ Imprimer / Exporter en PDF
            </button>
        </div>
    </div>

    <!-- Feuille de Bon de Commande / Facture -->
    <div class="max-w-3xl mx-auto bg-white p-8 sm:p-12 rounded-3xl border border-stone-200 shadow-xl print-shadow-none space-y-8">
        
        <!-- En-tête officiel -->
        <div class="flex items-start justify-between border-b-2 border-emerald-900 pb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl bg-black border border-amber-500/40 p-1 flex items-center justify-center overflow-hidden flex-shrink-0">
                    <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="ROM'S NATURAL" class="w-full h-full object-contain">
                </div>
                <div>
                    <h1 class="text-2xl font-black text-emerald-950 tracking-tight font-serif">ROM'S NATURAL</h1>
                    <p class="text-xs font-bold text-amber-700 uppercase tracking-widest mt-0.5">Produits Locaux Transformés à Koumra (Mandoul)</p>
                    <p class="text-[11px] text-stone-500 mt-1 max-w-sm">
                        Boutique & Atelier : Koumra, Province du Mandoul, Tchad<br>
                        Tél / WhatsApp : <?= htmlspecialchars(getSetting('phone_contact', '+235 62 89 10 85 / 98 20 53 50')) ?>
                    </p>
                </div>
            </div>
            <div class="text-right">
                <span class="inline-block px-3 py-1 bg-stone-100 rounded-lg text-xs font-bold uppercase tracking-wider text-stone-800">
                    Bon de Commande & Livraison
                </span>
                <p class="text-lg font-mono-bold text-stone-900 mt-2"><?= htmlspecialchars($order['reference']) ?></p>
                <p class="text-xs text-stone-500 mt-1">Date : <?= formatDateFr($order['date_creation']) ?></p>
            </div>
        </div>

        <!-- Coordonnées Destinataire & Livraison -->
        <div class="grid grid-cols-2 gap-6 p-4 rounded-2xl bg-stone-50 border border-stone-200 text-xs text-stone-700">
            <div>
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Destinataire :</span>
                <h3 class="text-sm font-bold text-stone-900 mt-0.5"><?= htmlspecialchars($order['client_nom']) ?></h3>
                <p class="font-semibold text-stone-800 mt-1">Tél : <?= htmlspecialchars($order['client_telephone']) ?></p>
                <?php if (!empty($order['client_email'])): ?>
                    <p class="text-stone-500"><?= htmlspecialchars($order['client_email']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <span class="text-[10px] font-bold text-stone-400 uppercase tracking-wider block">Modalités de Livraison :</span>
                <p class="font-bold text-emerald-900 mt-0.5"><?= htmlspecialchars($order['mode_reception']) ?></p>
                <p class="font-semibold text-stone-800"><?= htmlspecialchars($order['zone_nom']) ?></p>
                <p class="text-stone-500 mt-0.5"><?= htmlspecialchars($order['adresse_detaillee']) ?></p>
                <?php if (!empty($order['livreur_nom'])): ?>
                    <p class="text-[11px] font-bold text-amber-900 mt-1">Livreur : <?= htmlspecialchars($order['livreur_nom']) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($order['notes_client'])): ?>
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900">
                <strong>Instructions particulières du client :</strong> <?= htmlspecialchars($order['notes_client']) ?>
            </div>
        <?php endif; ?>

        <!-- Tableau des Lignes Articles -->
        <table class="w-full text-left text-xs border-collapse">
            <thead>
                <tr class="border-b-2 border-stone-200 text-stone-600 font-bold uppercase tracking-wider">
                    <th class="py-2.5">Désignation de l'article</th>
                    <th class="py-2.5 text-center">Quantité</th>
                    <th class="py-2.5 text-right">Prix Unitaire</th>
                    <th class="py-2.5 text-right">Montant (XAF)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td class="py-3 pr-4">
                            <span class="font-bold text-stone-900 block"><?= htmlspecialchars($item['nom_produit']) ?></span>
                            <?php if (!empty($item['nom_variante'])): ?>
                                <span class="text-[11px] text-emerald-800"><?= htmlspecialchars($item['nom_variante']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="py-3 text-center font-bold text-stone-800"><?= $item['quantite'] ?></td>
                        <td class="py-3 text-right text-stone-600"><?= formatPrice($item['prix_unitaire']) ?></td>
                        <td class="py-3 text-right font-bold text-stone-900"><?= formatPrice($item['sous_total']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Totaux & Statut Règlement -->
        <div class="flex justify-between items-start pt-4 border-t-2 border-stone-200 text-xs">
            <div class="space-y-1.5 max-w-xs">
                <div>
                    <span class="font-bold text-stone-700">Mode de Paiement :</span>
                    <span class="font-semibold text-stone-900"><?= htmlspecialchars($order['mode_paiement']) ?></span>
                </div>
                <div>
                    <span class="font-bold text-stone-700">Statut de Règlement :</span>
                    <span class="font-bold <?= $order['statut_paiement'] === 'Payé' ? 'text-emerald-700' : 'text-amber-700' ?>">
                        <?= htmlspecialchars($order['statut_paiement']) ?>
                    </span>
                </div>
                <?php if (!empty($order['reference_paiement'])): ?>
                    <div>
                        <span class="font-bold text-stone-700">Réf Transaction :</span>
                        <span class="font-mono"><?= htmlspecialchars($order['reference_paiement']) ?></span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="space-y-2 w-64 text-right">
                <div class="flex justify-between text-stone-600">
                    <span>Sous-total articles :</span>
                    <span class="font-bold text-stone-900"><?= formatPrice($order['sous_total']) ?></span>
                </div>
                <div class="flex justify-between text-stone-600">
                    <span>Frais d'acheminement :</span>
                    <span class="font-bold text-stone-900"><?= formatPrice($order['frais_livraison']) ?></span>
                </div>
                <div class="flex justify-between text-base font-black text-emerald-950 pt-2 border-t border-stone-200">
                    <span>TOTAL À RÉGLER :</span>
                    <span class="text-lg"><?= formatPrice($order['total']) ?></span>
                </div>
            </div>
        </div>

        <!-- Zone d'Émargement & Décharge (PRD 7.3) -->
        <div class="grid grid-cols-2 gap-8 pt-8 border-t border-dashed border-stone-300 text-xs text-stone-600">
            <div class="space-y-12">
                <div>
                    <span class="font-bold text-stone-800 uppercase text-[10px]">Pour ROM'S NATURAL (Expédition / Livreur) :</span>
                    <p class="text-[11px] text-stone-400 mt-1">Nom et signature :</p>
                </div>
                <div class="border-b border-stone-300 w-48"></div>
            </div>

            <div class="space-y-12 text-right">
                <div>
                    <span class="font-bold text-stone-800 uppercase text-[10px]">Reçu par le Client (Décharge de conformité) :</span>
                    <p class="text-[11px] text-stone-400 mt-1">Date et signature du réceptionnaire :</p>
                </div>
                <div class="border-b border-stone-300 w-48 ml-auto"></div>
            </div>
        </div>

        <!-- Mention Légale de Pied de Page -->
        <p class="text-[10px] text-stone-400 text-center pt-4 border-t border-stone-100">
            ROM'S NATURAL SARL • Valorisation des terroirs du Tchad • N'Djamena, République du Tchad. Merci pour votre fidélité !
        </p>

    </div>

</body>
</html>
