<?php
/**
 * Tableau de bord Administrateur - ROM'S NATURAL
 * Conforme PRD Section 12.1
 */

$adminTitle = "Tableau de Bord";
$activeMenu = "dashboard";
require_once __DIR__ . '/header.php';

// Statistiques Clés (KPIs)
$totalSales = $pdo->query("SELECT SUM(total) FROM orders WHERE statut_commande != 'Annulée'")->fetchColumn() ?: 0;
$totalOrders = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0;
$completedOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE statut_commande = 'Terminée'")->fetchColumn() ?: 0;
$averageBasket = ($totalOrders > 0) ? ($totalSales / $totalOrders) : 0;

// Commandes récentes (10 dernières)
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 8")->fetchAll();

// Produits en alerte stock faible (PRD Section 9)
$lowStockProducts = $pdo->query("SELECT * FROM products WHERE stock <= seuil_alerte AND disponible = 1 ORDER BY stock ASC LIMIT 6")->fetchAll();

// Lots approchant de la date limite (dans les 6 prochains mois) (PRD Section 9)
$expiringDateThreshold = date('Y-m-d', strtotime('+6 months'));
$expiringBatches = $pdo->query("SELECT b.*, p.nom as produit_nom 
    FROM product_batches b 
    LEFT JOIN products p ON b.produit_id = p.id 
    WHERE b.date_limite <= '$expiringDateThreshold' 
    ORDER BY b.date_limite ASC LIMIT 5")->fetchAll();
?>

<!-- Cartes KPI (Chiffre d'affaires, Commandes, Panier Moyen, Taux de complétion) -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    
    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Chiffre d'Affaires</span>
            <p class="text-2xl font-black text-emerald-950 mt-1"><?= formatPrice($totalSales) ?></p>
            <span class="text-[11px] text-emerald-700 font-semibold mt-1 block">Commandes valides</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center">
            <i data-lucide="coins" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Commandes Reçues</span>
            <p class="text-2xl font-black text-stone-900 mt-1"><?= $totalOrders ?></p>
            <span class="text-[11px] text-stone-500 font-semibold mt-1 block"><?= $completedOrders ?> livrées avec succès</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-800 flex items-center justify-center">
            <i data-lucide="shopping-bag" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Panier Moyen</span>
            <p class="text-2xl font-black text-amber-700 mt-1"><?= formatPrice($averageBasket) ?></p>
            <span class="text-[11px] text-stone-500 font-semibold mt-1 block">Par client au Tchad</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-800 flex items-center justify-center">
            <i data-lucide="calculator" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm flex items-center justify-between">
        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-stone-500">Alertes Stock</span>
            <p class="text-2xl font-black <?= count($lowStockProducts) > 0 ? 'text-rose-600' : 'text-stone-900' ?> mt-1">
                <?= count($lowStockProducts) ?>
            </p>
            <span class="text-[11px] text-stone-500 font-semibold mt-1 block">Sous le seuil de réappro</span>
        </div>
        <div class="w-12 h-12 rounded-2xl <?= count($lowStockProducts) > 0 ? 'bg-rose-50 text-rose-700' : 'bg-stone-50 text-stone-500' ?> flex items-center justify-center">
            <i data-lucide="alert-triangle" class="w-6 h-6"></i>
        </div>
    </div>

</div>

<!-- Grille Principale : Commandes récentes & Alertes de gestion -->
<div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    
    <!-- Liste des Commandes Récentes (8 cols) -->
    <div class="lg:col-span-8 bg-white rounded-3xl border border-stone-200 p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
            <div>
                <h3 class="font-bold text-base text-stone-900">Commandes Récentes</h3>
                <p class="text-xs text-stone-500">Traitement, statuts et assignation livreur</p>
            </div>
            <a href="<?= BASE_URL ?>/admin/orders.php" class="text-xs font-bold text-emerald-800 hover:underline">
                Voir toutes les commandes →
            </a>
        </div>

        <?php if (empty($recentOrders)): ?>
            <p class="text-xs text-stone-500 py-6 text-center">Aucune commande enregistrée pour le moment.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-stone-400 font-semibold border-b border-stone-100 pb-2">
                            <th class="py-2.5">Réf & Client</th>
                            <th class="py-2.5">Date</th>
                            <th class="py-2.5">Zone</th>
                            <th class="py-2.5">Total</th>
                            <th class="py-2.5">Statut Commande</th>
                            <th class="py-2.5 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <?php foreach ($recentOrders as $ord): ?>
                            <tr class="hover:bg-stone-50/70 transition">
                                <td class="py-3">
                                    <span class="font-bold text-stone-900 font-mono block"><?= htmlspecialchars($ord['reference']) ?></span>
                                    <span class="text-stone-500"><?= htmlspecialchars($ord['client_nom']) ?> (<?= htmlspecialchars($ord['client_telephone']) ?>)</span>
                                </td>
                                <td class="py-3 text-stone-500 whitespace-nowrap"><?= formatDateFr($ord['date_creation'], false) ?></td>
                                <td class="py-3 text-stone-600 max-w-[140px] truncate" title="<?= htmlspecialchars($ord['zone_nom']) ?>">
                                    <?= htmlspecialchars($ord['zone_nom']) ?>
                                </td>
                                <td class="py-3 font-black text-stone-900 whitespace-nowrap"><?= formatPrice($ord['total']) ?></td>
                                <td class="py-3 whitespace-nowrap">
                                    <?= orderStatusBadge($ord['statut_commande']) ?>
                                </td>
                                <td class="py-3 text-right">
                                    <a href="<?= BASE_URL ?>/admin/order_detail.php?id=<?= $ord['id'] ?>" class="p-1.5 rounded-lg bg-stone-100 hover:bg-emerald-800 hover:text-white transition inline-block text-stone-700" title="Gérer la commande">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Colonne Latérale : Stocks Faibles & Lots (4 cols) -->
    <div class="lg:col-span-4 space-y-6">
        
        <!-- Alertes de Stock (PRD Section 9 & 12.1) -->
        <div class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <div class="flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600"></i>
                    <h3 class="font-bold text-sm text-stone-900">Alertes Stock Faible</h3>
                </div>
                <a href="<?= BASE_URL ?>/admin/stock.php" class="text-xs font-semibold text-emerald-800 hover:underline">Gérer</a>
            </div>

            <?php if (empty($lowStockProducts)): ?>
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-800 text-xs text-center font-medium">
                    Tous vos produits ont un niveau de stock satisfaisant.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($lowStockProducts as $lsp): ?>
                        <div class="flex items-center justify-between p-3 rounded-2xl bg-rose-50/70 border border-rose-100 text-xs">
                            <div class="min-w-0 pr-2">
                                <h4 class="font-bold text-stone-900 truncate"><?= htmlspecialchars($lsp['nom']) ?></h4>
                                <span class="text-rose-700 text-[11px]">Seuil alerte : <?= $lsp['seuil_alerte'] ?></span>
                            </div>
                            <span class="font-black text-rose-700 bg-white px-2.5 py-1 rounded-xl shadow-xs">
                                <?= $lsp['stock'] ?> restant<?= $lsp['stock'] > 1 ? 's' : '' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Traçabilité des Lots & Dates Limites (PRD Section 9) -->
        <div class="bg-white rounded-3xl border border-stone-200 p-6 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <div class="flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-amber-600"></i>
                    <h3 class="font-bold text-sm text-stone-900">Suivi des Lots & DLUO</h3>
                </div>
                <a href="<?= BASE_URL ?>/admin/stock.php" class="text-xs font-semibold text-emerald-800 hover:underline">Lots</a>
            </div>

            <?php if (empty($expiringBatches)): ?>
                <p class="text-xs text-stone-500 text-center py-2">Aucun lot enregistré ou proche de date limite.</p>
            <?php else: ?>
                <div class="space-y-2.5">
                    <?php foreach ($expiringBatches as $batch): ?>
                        <div class="p-3 rounded-2xl bg-stone-50 border border-stone-200 text-xs space-y-1">
                            <div class="flex justify-between items-center">
                                <span class="font-mono font-bold text-stone-900"><?= htmlspecialchars($batch['numero_lot']) ?></span>
                                <span class="text-stone-500 font-medium">Qté: <?= $batch['quantite'] ?></span>
                            </div>
                            <p class="text-stone-700 font-medium truncate"><?= htmlspecialchars($batch['produit_nom']) ?></p>
                            <span class="text-[11px] text-amber-800 block">Date limite : <?= formatDateFr($batch['date_limite'], false) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Accès Rapide & Actions Administrateur -->
        <div class="bg-gradient-to-br from-emerald-950 to-emerald-900 p-6 rounded-3xl text-white space-y-3 shadow-md">
            <h4 class="font-bold text-sm text-amber-300">Actions Rapides</h4>
            <div class="space-y-2 text-xs">
                <a href="<?= BASE_URL ?>/admin/product_edit.php" class="block p-2.5 rounded-xl bg-white/10 hover:bg-white/20 transition font-semibold flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-amber-400"></i>
                    <span>Ajouter un produit (avec IA)</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/delivery_zones.php" class="block p-2.5 rounded-xl bg-white/10 hover:bg-white/20 transition font-semibold flex items-center gap-2">
                    <i data-lucide="map" class="w-4 h-4 text-amber-400"></i>
                    <span>Configurer les zones de livraison</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/settings.php" class="block p-2.5 rounded-xl bg-white/10 hover:bg-white/20 transition font-semibold flex items-center gap-2">
                    <i data-lucide="sliders" class="w-4 h-4 text-amber-400"></i>
                    <span>Coordonnées Mobile Money & Virement</span>
                </a>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

