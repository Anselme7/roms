<?php
/**
 * Gestion des Commandes - ROM'S NATURAL
 * Conforme PRD Section 8
 */

$adminTitle = "Gestion des Commandes";
$activeMenu = "orders";
require_once __DIR__ . '/header.php';
require_permission('orders');

// Filtres
$statutFiltre = trim($_GET['statut'] ?? '');
$paiementFiltre = trim($_GET['paiement'] ?? '');
$search = trim($_GET['q'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($statutFiltre)) {
    $where[] = "statut_commande = ?";
    $params[] = $statutFiltre;
}

if (!empty($paiementFiltre)) {
    $where[] = "statut_paiement = ?";
    $params[] = $paiementFiltre;
}

if (!empty($search)) {
    $where[] = "(reference LIKE ? OR client_nom LIKE ? OR client_telephone LIKE ? OR zone_nom LIKE ?)";
    $like = '%' . $search . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$whereSql = implode(" AND ", $where);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE $whereSql ORDER BY id DESC");
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Liste des statuts officiels du PRD (Section 8.1 & 6.4)
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

<div class="space-y-6">
    
    <!-- Entête & Filtres -->
    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row items-baseline justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-stone-900">Suivi & Traitement des Ventes</h2>
                <p class="text-xs text-stone-500 mt-0.5">Filtrer par statut de livraison, paiement ou référence client.</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 bg-stone-100 rounded-full text-stone-700">
                <?= count($orders) ?> commande<?= count($orders) > 1 ? 's' : '' ?> trouvée<?= count($orders) > 1 ? 's' : '' ?>
            </span>
        </div>

        <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-3 text-xs pt-2">
            <!-- Recherche textuelle -->
            <div class="sm:col-span-2 relative">
                <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher par référence, nom, tél..." 
                       class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                <i data-lucide="search" class="w-4 h-4 text-stone-400 absolute left-3 top-3"></i>
            </div>

            <!-- Filtre Statut Commande -->
            <div>
                <select name="statut" class="w-full px-3 py-2.5 rounded-xl border border-stone-300 bg-white font-medium focus:outline-none focus:border-emerald-700">
                    <option value="">Tous les statuts de commande</option>
                    <?php foreach ($orderStatuses as $st): ?>
                        <option value="<?= $st ?>" <?= $statutFiltre === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtre Statut Paiement -->
            <div>
                <select name="paiement" class="w-full px-3 py-2.5 rounded-xl border border-stone-300 bg-white font-medium focus:outline-none focus:border-emerald-700">
                    <option value="">Tous les statuts de paiement</option>
                    <?php foreach ($paymentStatuses as $pst): ?>
                        <option value="<?= $pst ?>" <?= $paiementFiltre === $pst ? 'selected' : '' ?>><?= $pst ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="sm:col-span-4 flex items-center justify-end gap-2 pt-1">
                <?php if (!empty($search) || !empty($statutFiltre) || !empty($paiementFiltre)): ?>
                    <a href="<?= BASE_URL ?>/admin/orders.php" class="px-4 py-2 text-rose-700 bg-rose-50 rounded-xl hover:bg-rose-100 font-semibold transition">
                        Effacer filtres
                    </a>
                <?php endif; ?>
                <button type="submit" class="px-6 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white rounded-xl font-bold transition shadow-sm">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Tableau des commandes -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <?php if (empty($orders)): ?>
            <div class="p-12 text-center text-xs text-stone-500 space-y-2">
                <i data-lucide="inbox" class="w-8 h-8 text-stone-300 mx-auto"></i>
                <p>Aucune commande ne correspond aux critères sélectionnés.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 text-stone-600 font-bold uppercase tracking-wider">
                            <th class="py-3 px-4">Référence</th>
                            <th class="py-3 px-4">Client</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Zone & Mode</th>
                            <th class="py-3 px-4">Total</th>
                            <th class="py-3 px-4">Paiement</th>
                            <th class="py-3 px-4">Statut Commande</th>
                            <th class="py-3 px-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <?php foreach ($orders as $o): ?>
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-stone-900 whitespace-nowrap">
                                    <a href="<?= BASE_URL ?>/admin/order_detail.php?id=<?= $o['id'] ?>" class="hover:text-emerald-800 underline">
                                        <?= htmlspecialchars($o['reference']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="font-bold text-stone-900 block"><?= htmlspecialchars($o['client_nom']) ?></span>
                                    <a href="tel:<?= preg_replace('/[^0-9]/', '', $o['client_telephone']) ?>" class="text-stone-500 hover:text-emerald-800">
                                        <?= htmlspecialchars($o['client_telephone']) ?>
                                    </a>
                                </td>
                                <td class="py-3.5 px-4 text-stone-500 whitespace-nowrap">
                                    <?= formatDateFr($o['date_creation']) ?>
                                </td>
                                <td class="py-3.5 px-4 max-w-[180px] truncate" title="<?= htmlspecialchars($o['zone_nom']) ?>">
                                    <span class="font-semibold text-stone-800 block truncate"><?= htmlspecialchars($o['zone_nom'] ?: $o['mode_reception']) ?></span>
                                    <span class="text-stone-400 text-[11px] truncate block"><?= htmlspecialchars($o['mode_reception']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 font-black text-stone-900 whitespace-nowrap">
                                    <?= formatPrice($o['total']) ?>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <?= paymentStatusBadge($o['statut_paiement']) ?>
                                    <span class="text-[10px] text-stone-400 block mt-0.5"><?= htmlspecialchars($o['mode_paiement']) ?></span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <?= orderStatusBadge($o['statut_commande']) ?>
                                </td>
                                <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                    <a href="<?= BASE_URL ?>/admin/order_detail.php?id=<?= $o['id'] ?>" 
                                       class="px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-800 hover:bg-emerald-800 hover:text-white font-bold transition inline-flex items-center gap-1 shadow-xs">
                                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i>
                                        <span>Gérer</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

