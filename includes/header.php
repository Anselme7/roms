<?php
/**
 * En-tête public pour ROM'S NATURAL
 */
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/functions.php';

$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}

// Catégories actives pour le menu
$menuCategories = $pdo->query("SELECT id, nom, slug FROM categories WHERE statut = 1 ORDER BY ordre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : '' ?><?= htmlspecialchars(getSetting('site_name', "ROM'S NATURAL")) ?> - Terroirs du Tchad</title>
    <meta name="description" content="<?= htmlspecialchars(getSetting('site_description', "Boutique en ligne officielle de ROM'S Natural : cosmétiques naturels, miels, sirops et trésors du terroir tchadien.")) ?>">

    <!-- Polices Google : Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            850: '#153e2e',
                            950: '#0b241b'
                        },
                        amber: {
                            450: '#d4a373',
                            550: '#bc6c25'
                        }
                    }
                }
            }
        }
    </script>

    <!-- Icônes Lucide -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- Styles sur-mesure -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">

    <script>
        window.BASE_URL = "<?= BASE_URL ?>";
    </script>
</head>
<body class="flex flex-col min-h-full bg-[#fbf9f5] text-[#242b26] antialiased">

    <!-- Barre d'annonce supérieure -->
    <div class="bg-emerald-950 text-emerald-100 text-xs py-2 px-4 border-b border-emerald-900">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Boutique basée à Koumra (Mandoul) • Expéditions régulières dans tout le Tchad & à l'International</span>
            </div>
            <div class="flex items-center gap-4 text-xs">
                <a href="tel:<?= preg_replace('/[^0-9]/', '', getSetting('phone_contact', '+235 62 89 10 85')) ?>" class="hover:text-amber-300 transition flex items-center gap-1">
                    <i data-lucide="phone" class="w-3.5 h-3.5"></i>
                    <span><?= htmlspecialchars(getSetting('phone_contact', '+235 62 89 10 85 / 98 20 53 50')) ?></span>
                </a>
                <span class="text-emerald-800">|</span>
                <a href="<?= BASE_URL ?>/suivi.php" class="hover:text-amber-300 transition flex items-center gap-1 font-medium">
                    <i data-lucide="package-search" class="w-3.5 h-3.5"></i>
                    <span>Suivre ma commande</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Header Navigation Principale -->
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md shadow-sm border-b border-stone-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 gap-4">
                
                <!-- Logo Officiel & Identité de Marque -->
                <a href="<?= BASE_URL ?>/index.php" class="flex items-center gap-3 group flex-shrink-0">
                    <div class="w-12 h-12 rounded-2xl bg-black flex items-center justify-center p-0.5 shadow-md border border-amber-500/30 group-hover:scale-105 transition-transform overflow-hidden">
                        <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="ROM'S NATURAL" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <span class="text-2xl font-black tracking-tight text-emerald-950 block leading-none font-serif">ROM'S</span>
                        <span class="text-[10px] font-bold uppercase tracking-widest text-amber-700 block mt-0.5">Natural • Koumra (Mandoul)</span>
                    </div>
                </a>

                <!-- Liens de Navigation Desktop -->
                <nav class="hidden md:flex items-center gap-6 text-sm font-semibold text-stone-700">
                    <a href="<?= BASE_URL ?>/index.php" class="hover:text-emerald-800 transition py-1 <?= (!isset($activeNav) || $activeNav === 'home') ? 'text-emerald-800 border-b-2 border-emerald-800' : '' ?>">Accueil</a>
                    
                    <div class="relative group py-1">
                        <a href="<?= BASE_URL ?>/catalogue.php" class="hover:text-emerald-800 transition flex items-center gap-1 <?= (isset($activeNav) && $activeNav === 'catalogue') ? 'text-emerald-800 border-b-2 border-emerald-800' : '' ?>">
                            <span>Boutique</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 text-stone-400 group-hover:text-emerald-800 transition"></i>
                        </a>
                        <div class="absolute left-0 mt-2 w-64 bg-white rounded-2xl shadow-xl border border-stone-100 p-2 hidden group-hover:block transition-all z-50">
                            <a href="<?= BASE_URL ?>/catalogue.php" class="block px-3 py-2 text-xs font-bold uppercase text-emerald-800 hover:bg-emerald-50 rounded-xl mb-1">
                                Tous les produits
                            </a>
                            <hr class="my-1 border-stone-100">
                            <?php foreach ($menuCategories as $mCat): ?>
                                <a href="<?= BASE_URL ?>/catalogue.php?cat=<?= urlencode($mCat['slug']) ?>" class="block px-3 py-2 text-sm text-stone-700 hover:text-emerald-900 hover:bg-stone-50 rounded-xl transition">
                                    <?= htmlspecialchars($mCat['nom']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <a href="<?= BASE_URL ?>/a-propos.php" class="hover:text-emerald-800 transition py-1 <?= (isset($activeNav) && $activeNav === 'apropos') ? 'text-emerald-800 border-b-2 border-emerald-800' : '' ?>">Notre Histoire</a>
                    <a href="<?= BASE_URL ?>/contact.php" class="hover:text-emerald-800 transition py-1 <?= (isset($activeNav) && $activeNav === 'contact') ? 'text-emerald-800 border-b-2 border-emerald-800' : '' ?>">Contact</a>
                    <a href="<?= BASE_URL ?>/faq.php" class="hover:text-emerald-800 transition py-1 <?= (isset($activeNav) && $activeNav === 'faq') ? 'text-emerald-800 border-b-2 border-emerald-800' : '' ?>">FAQ & Livraison</a>
                </nav>

                <!-- Actions et Boutons droite -->
                <div class="flex items-center gap-3">
                    <!-- Recherche rapide -->
                    <form action="<?= BASE_URL ?>/catalogue.php" method="GET" class="hidden lg:flex items-center relative">
                        <input type="text" name="q" placeholder="Rechercher (karité, miel, chébé...)" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                               class="w-56 pl-9 pr-3 py-2 text-xs rounded-full bg-stone-100 border border-transparent focus:bg-white focus:border-emerald-700 focus:outline-none transition">
                        <i data-lucide="search" class="w-4 h-4 text-stone-400 absolute left-3 pointer-events-none"></i>
                    </form>

                    <!-- Contact WhatsApp direct -->
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('phone_whatsapp', '23566280000')) ?>?text=<?= urlencode("Bonjour ROM'S Natural, je souhaite me renseigner sur vos produits.") ?>" 
                       target="_blank" rel="noopener noreferrer"
                       class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-bold text-emerald-900 bg-emerald-100/70 hover:bg-emerald-200/80 rounded-full transition"
                       title="Discuter directement sur WhatsApp">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <i data-lucide="message-circle" class="w-4 h-4 text-emerald-700"></i>
                        <span>WhatsApp</span>
                    </a>

                    <!-- Bouton Panier interactif -->
                    <button type="button" class="cart-toggle-btn relative p-2.5 rounded-full bg-emerald-900 text-amber-300 hover:bg-emerald-950 transition shadow-sm flex items-center justify-center">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i>
                        <span class="cart-badge-count <?= $cartCount > 0 ? '' : 'hidden' ?> absolute -top-1 -right-1 bg-amber-500 text-stone-900 text-[11px] font-black w-5 h-5 rounded-full flex items-center justify-center shadow">
                            <?= $cartCount ?>
                        </span>
                    </button>

                    <!-- Menu burger Mobile -->
                    <button type="button" id="mobile-menu-toggle" class="md:hidden p-2 rounded-xl text-stone-700 hover:bg-stone-100 transition">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>

            </div>
        </div>

        <!-- Menu Mobile déroulant -->
        <div id="mobile-nav" class="hidden md:hidden border-t border-stone-200 bg-white px-4 pt-3 pb-6 space-y-3">
            <form action="<?= BASE_URL ?>/catalogue.php" method="GET" class="relative mb-3">
                <input type="text" name="q" placeholder="Rechercher un produit..." 
                       class="w-full pl-9 pr-3 py-2 text-sm rounded-xl bg-stone-100 border border-stone-200 focus:outline-none focus:border-emerald-700">
                <i data-lucide="search" class="w-4 h-4 text-stone-400 absolute left-3 top-3"></i>
            </form>
            <a href="<?= BASE_URL ?>/index.php" class="block py-2 text-sm font-bold text-stone-800">Accueil</a>
            <a href="<?= BASE_URL ?>/catalogue.php" class="block py-2 text-sm font-bold text-stone-800">Tous les produits</a>
            <div class="pl-3 border-l-2 border-emerald-800 space-y-2 py-1">
                <?php foreach ($menuCategories as $mCat): ?>
                    <a href="<?= BASE_URL ?>/catalogue.php?cat=<?= urlencode($mCat['slug']) ?>" class="block text-xs text-stone-600 hover:text-emerald-800">
                        • <?= htmlspecialchars($mCat['nom']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <a href="<?= BASE_URL ?>/a-propos.php" class="block py-2 text-sm font-semibold text-stone-700">Notre Histoire & Valeurs</a>
            <a href="<?= BASE_URL ?>/suivi.php" class="block py-2 text-sm font-semibold text-stone-700">Suivre ma commande</a>
            <a href="<?= BASE_URL ?>/contact.php" class="block py-2 text-sm font-semibold text-stone-700">Contact & Adresses</a>
            <a href="<?= BASE_URL ?>/faq.php" class="block py-2 text-sm font-semibold text-stone-700">FAQ & Modalités</a>
        </div>
    </header>

    <!-- Drawer Panier Latéral Coulissant -->
    <div id="cart-backdrop" class="fixed inset-0 bg-stone-900/50 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity"></div>
    <div id="cart-drawer" class="fixed top-0 right-0 h-full w-full max-w-md bg-white shadow-2xl z-50 transform translate-x-full cart-drawer flex flex-col">
        <!-- Entête du panier -->
        <div class="p-5 border-b border-stone-100 flex items-center justify-between bg-emerald-950 text-white">
            <div class="flex items-center gap-2">
                <i data-lucide="shopping-bag" class="w-5 h-5 text-amber-300"></i>
                <h3 class="font-bold text-lg">Votre Panier</h3>
            </div>
            <button id="close-cart-btn" class="p-1.5 rounded-full hover:bg-emerald-900 text-stone-300 hover:text-white transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Liste des articles -->
        <div id="cart-drawer-items" class="flex-1 overflow-y-auto p-5 space-y-3">
            <!-- Rempli dynamiquement en JS -->
        </div>

        <!-- Pied de page du panier -->
        <div class="p-5 border-t border-stone-100 bg-stone-50 space-y-3">
            <div class="flex items-center justify-between text-stone-600 text-sm">
                <span>Sous-total articles :</span>
                <span id="cart-drawer-subtotal" class="font-extrabold text-stone-900 text-base">0 FCFA</span>
            </div>
            <p class="text-[11px] text-stone-500 leading-tight">
                * Les frais de livraison sont calculés à l'étape suivante selon votre ville / quartier au Tchad.
            </p>
            <div class="grid grid-cols-2 gap-2 pt-1">
                <a href="<?= BASE_URL ?>/panier.php" class="block text-center py-3 px-4 rounded-xl border border-stone-300 text-stone-700 font-bold text-xs hover:bg-stone-100 transition">
                    Voir panier
                </a>
                <a id="cart-drawer-checkout-btn" href="<?= BASE_URL ?>/commande.php" class="block text-center py-3 px-4 rounded-xl bg-emerald-800 text-white font-bold text-xs hover:bg-emerald-900 transition shadow-md shadow-emerald-900/20">
                    Commander
                </a>
            </div>
        </div>
    </div>

    <!-- Conteneur de page principal -->
    <main class="flex-1">
