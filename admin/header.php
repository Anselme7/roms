<?php
/**
 * En-tête Administration - ROM'S NATURAL
 * Conforme PRD Section 12
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/functions.php';

require_admin_login();

$currentAdmin = current_admin_user();
$adminRole = $_SESSION['admin_role'] ?? 'Gestionnaire';

// Vérification alertes stock faible
$lowStockCount = $pdo->query("SELECT COUNT(*) FROM products WHERE stock <= seuil_alerte AND disponible = 1")->fetchColumn();

// Commandes en attente de traitement
$pendingOrdersCount = $pdo->query("SELECT COUNT(*) FROM orders WHERE statut_commande IN ('Nouvelle / reçue', 'En attente')")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr" class="h-full bg-stone-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($adminTitle) ? htmlspecialchars($adminTitle) . ' | ' : '' ?>Administration ROM'S NATURAL</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        window.BASE_URL = "<?= BASE_URL ?>";
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="flex h-full text-stone-800 antialiased overflow-hidden">

    <!-- Sidebar Latérale Desktop -->
    <aside class="hidden lg:flex flex-col w-64 bg-emerald-950 text-stone-300 flex-shrink-0 border-r border-emerald-900">
        <!-- Logo -->
        <div class="p-6 border-b border-emerald-900 flex items-center gap-3">
            <div class="w-11 h-11 rounded-xl bg-black border border-amber-500/30 flex items-center justify-center p-0.5 overflow-hidden shadow">
                <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="ROM'S NATURAL" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="font-extrabold text-white text-base leading-none font-serif">ROM'S NATURAL</h1>
                <span class="text-[10px] text-amber-400 font-bold uppercase tracking-wider">Koumra • Mandoul</span>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-1.5 text-xs font-semibold">
            
            <a href="<?= BASE_URL ?>/admin/index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= (!isset($activeMenu) || $activeMenu === 'dashboard') ? 'bg-emerald-800 text-white shadow' : 'hover:bg-emerald-900 text-stone-300' ?>">
                <i data-lucide="layout-dashboard" class="w-4 h-4 text-amber-300"></i>
                <span>Tableau de bord</span>
            </a>

            <?php if (has_permission('orders')): ?>
                <a href="<?= BASE_URL ?>/admin/orders.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= (isset($activeMenu) && $activeMenu === 'orders') ? 'bg-emerald-800 text-white shadow' : 'hover:bg-emerald-900 text-stone-300' ?>">
                    <div class="flex items-center gap-3">
                        <i data-lucide="shopping-bag" class="w-4 h-4 text-amber-300"></i>
                        <span>Commandes</span>
                    </div>
                    <?php if ($pendingOrdersCount > 0): ?>
                        <span class="bg-amber-500 text-stone-950 text-[10px] font-black px-2 py-0.5 rounded-full">
                            <?= $pendingOrdersCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if (has_permission('catalog')): ?>
                <a href="<?= BASE_URL ?>/admin/products.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= (isset($activeMenu) && $activeMenu === 'products') ? 'bg-emerald-800 text-white shadow' : 'hover:bg-emerald-900 text-stone-300' ?>">
                    <i data-lucide="package" class="w-4 h-4 text-amber-300"></i>
                    <span>Produits & Catalogue</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/categories.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= (isset($activeMenu) && $activeMenu === 'categories') ? 'bg-emerald-800 text-white shadow' : 'hover:bg-emerald-900 text-stone-300' ?>">
                    <i data-lucide="layers" class="w-4 h-4 text-amber-300"></i>
                    <span>Familles & Catégories</span>
                </a>
            <?php endif; ?>

            <?php if (has_permission('stock')): ?>
                <a href="<?= BASE_URL ?>/admin/stock.php" class="flex items-center justify-between px-3.5 py-2.5 rounded-xl transition <?= (isset($activeMenu) && $activeMenu === 'stock') ? 'bg-emerald-800 text-white shadow' : 'hover:bg-emerald-900 text-stone-300' ?>">
                    <div class="flex items-center gap-3">
                        <i data-lucide="boxes" class="w-4 h-4 text-amber-300"></i>
                        <span>Stocks, Mouvements & Lots</span>
                    </div>
                    <?php if ($lowStockCount > 0): ?>
                        <span class="bg-rose-500 text-white text-[10px] font-black px-1.5 py-0.5 rounded-full" title="Produits en alerte de stock">
                            <?= $lowStockCount ?>
                        </span>
                    <?php endif; ?>
                </a>
            <?php endif; ?>

            <?php if (has_permission('settings')): ?>
                <a href="<?= BASE_URL ?>/admin/delivery_zones.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= (isset($activeMenu) && $activeMenu === 'delivery') ? 'bg-emerald-800 text-white shadow' : 'hover:bg-emerald-900 text-stone-300' ?>">
                    <i data-lucide="truck" class="w-4 h-4 text-amber-300"></i>
                    <span>Zones de Livraison Tchad</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/users.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= (isset($activeMenu) && $activeMenu === 'users') ? 'bg-emerald-800 text-white shadow' : 'hover:bg-emerald-900 text-stone-300' ?>">
                    <i data-lucide="users" class="w-4 h-4 text-amber-300"></i>
                    <span>Utilisateurs & Rôles</span>
                </a>

                <a href="<?= BASE_URL ?>/admin/settings.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= (isset($activeMenu) && $activeMenu === 'settings') ? 'bg-emerald-800 text-white shadow' : 'hover:bg-emerald-900 text-stone-300' ?>">
                    <i data-lucide="settings" class="w-4 h-4 text-amber-300"></i>
                    <span>Paramètres & IA</span>
                </a>
            <?php endif; ?>

            <hr class="border-emerald-900 my-3">

            <a href="<?= BASE_URL ?>/index.php" target="_blank" class="flex items-center gap-3 px-3.5 py-2 rounded-xl text-stone-400 hover:text-white hover:bg-emerald-900 transition">
                <i data-lucide="external-link" class="w-4 h-4"></i>
                <span>Voir la boutique en ligne</span>
            </a>
        </nav>

        <!-- Profil Connecté -->
        <div class="p-4 border-t border-emerald-900 bg-emerald-950 flex items-center justify-between">
            <div class="min-w-0 pr-2">
                <span class="block text-xs font-bold text-white truncate"><?= htmlspecialchars($_SESSION['admin_name'] ?? 'Admin') ?></span>
                <span class="block text-[10px] text-amber-300 truncate"><?= htmlspecialchars($adminRole) ?></span>
            </div>
            <a href="<?= BASE_URL ?>/admin/logout.php" class="p-2 text-stone-400 hover:text-rose-400 transition" title="Se déconnecter">
                <i data-lucide="log-out" class="w-4 h-4"></i>
            </a>
        </div>
    </aside>

    <!-- Zone Centrale Principale -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        
        <!-- Header Supérieur Mobile & Desktop -->
        <header class="h-16 bg-white border-b border-stone-200 flex items-center justify-between px-4 sm:px-6 z-10">
            <div class="flex items-center gap-3">
                <!-- Toggle Mobile -->
                <button id="admin-mobile-toggle" class="lg:hidden p-2 rounded-lg text-stone-600 hover:bg-stone-100">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <h2 class="text-base font-bold text-stone-900"><?= htmlspecialchars($adminTitle ?? 'Tableau de bord') ?></h2>
            </div>

            <div class="flex items-center gap-3">
                <?php if ($lowStockCount > 0 && has_permission('stock')): ?>
                    <a href="<?= BASE_URL ?>/admin/stock.php" class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold hover:bg-rose-100 transition" title="Produits sous le seuil d'alerte">
                        <i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-rose-600"></i>
                        <span><?= $lowStockCount ?> alerte<?= $lowStockCount > 1 ? 's' : '' ?> stock</span>
                    </a>
                <?php endif; ?>

                <a href="<?= BASE_URL ?>/index.php" target="_blank" class="hidden sm:inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border border-stone-200 text-xs font-semibold text-stone-700 hover:bg-stone-50 transition">
                    <i data-lucide="store" class="w-3.5 h-3.5 text-emerald-800"></i>
                    <span>Boutique publique</span>
                </a>
            </div>
        </header>

        <!-- Contenu de la Page avec scrollbar indépendante -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">

