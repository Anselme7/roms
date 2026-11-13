<?php
/**
 * Page À Propos - ROM'S NATURAL
 * Conforme PRD Section 5.1
 */

$pageTitle = "Notre Histoire & Valeurs";
$activeNav = "apropos";
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-emerald-950 text-white py-16">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-4">
        <span class="text-xs font-bold uppercase tracking-widest text-amber-400 bg-emerald-900/80 px-3 py-1 rounded-full border border-emerald-800">
            L'Âme de ROM'S Natural
        </span>
        <h1 class="text-3xl sm:text-5xl font-black">Sublimer la terre tchadienne</h1>
        <p class="text-stone-300 text-sm sm:text-base max-w-2xl mx-auto leading-relaxed">
            Une marque engagée pour la valorisation et la transformation locale de nos trésors sahéliens et savanicoles.
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14 space-y-12">
    
    <!-- Notre Mission -->
    <div class="bg-white p-8 sm:p-10 rounded-3xl border border-stone-200 shadow-sm space-y-5">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
                <i data-lucide="compass" class="w-6 h-6"></i>
            </div>
            <h2 class="text-xl sm:text-2xl font-black text-stone-900">Notre Mission</h2>
        </div>
        <p class="text-stone-600 text-sm leading-relaxed">
            Implantée au cœur de la verdoyante province du <strong>Mandoul, à Koumra</strong>, la marque <strong>ROM'S Natural</strong> puise son inspiration directement dans le berceau historique du karité tchadien, des graines de ricin, du tamarin sauvage et des cultures artisanales locales.
        </p>
        <p class="text-stone-600 text-sm leading-relaxed">
            Notre atelier de transformation à Koumra collabore étroitement avec les coopératives féminines locales (notamment la <em>Coopérative des Femmes Rokndoh</em>) pour extraire, formuler et conditionner des produits d'une pureté absolue. Depuis notre base de Koumra, nous organisons les expéditions vers Sarh, Moundou, N'Djamena, l'ensemble des provinces du Tchad, ainsi que vers la sous-région CEMAC et la diaspora internationale.
        </p>
    </div>

    <!-- Nos Engagements -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white p-6 rounded-3xl border border-stone-200 space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-800 flex items-center justify-center">
                <i data-lucide="handshake" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-stone-900 text-base">Commerce Équitable Local</h3>
            <p class="text-xs text-stone-500 leading-relaxed">
                Partenariat direct avec les groupements de femmes et récolteurs ruraux pour garantir un revenu juste et pérenne.
            </p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-stone-200 space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-800 flex items-center justify-center">
                <i data-lucide="leaf" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-stone-900 text-base">Transparence Totale</h3>
            <p class="text-xs text-stone-500 leading-relaxed">
                Aucun composant chimique masqué. Nos compositions sont limpides, naturelles et vérifiées avant toute mise sur le marché.
            </p>
        </div>

        <div class="bg-white p-6 rounded-3xl border border-stone-200 space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-800 flex items-center justify-center">
                <i data-lucide="shield-check" class="w-6 h-6"></i>
            </div>
            <h3 class="font-bold text-stone-900 text-base">Qualité & Traçabilité</h3>
            <p class="text-xs text-stone-500 leading-relaxed">
                Gestion rigoureuse des lots, des dates limites de consommation et des conditions de conservation optimales.
            </p>
        </div>
    </div>

    <!-- Invitation CTA -->
    <div class="bg-gradient-to-r from-emerald-900 to-emerald-950 p-8 sm:p-10 rounded-3xl text-white text-center space-y-4">
        <h3 class="text-xl sm:text-2xl font-black">Prenez part à l'aventure du naturel tchadien</h3>
        <p class="text-xs sm:text-sm text-stone-300 max-w-lg mx-auto">
            Découvrez nos créations artisanales et soutenez la production locale dès aujourd'hui.
        </p>
        <a href="<?= BASE_URL ?>/catalogue.php" class="inline-block px-8 py-3.5 bg-amber-500 hover:bg-amber-400 text-stone-950 font-black text-xs uppercase tracking-wider rounded-xl transition shadow-lg">
            Découvrir tous les produits
        </a>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

