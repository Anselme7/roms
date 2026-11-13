<?php
/**
 * Suivi de Commande en Ligne - ROM'S NATURAL
 * Conforme PRD Section 5.1 & 7.3
 */

$pageTitle = "Suivi de commande";
$activeNav = "suivi";
require_once __DIR__ . '/includes/header.php';

$searchedRef = trim($_GET['ref'] ?? '');
?>

<div class="bg-stone-100/70 border-b border-stone-200 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-2">
        <h1 class="text-2xl sm:text-3xl font-black text-stone-900">Suivre l'état de ma commande</h1>
        <p class="text-xs sm:text-sm text-stone-500 max-w-md mx-auto">
            Saisissez la référence unique (reçue lors de votre achat) ou votre numéro de téléphone.
        </p>
    </div>
</div>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    
    <!-- Formulaire de recherche de commande -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <form id="track-form" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Référence de commande</label>
                    <input type="text" id="track-ref" value="<?= htmlspecialchars($searchedRef) ?>" placeholder="Ex: RN-2611-9872" 
                           class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700 font-mono">
                </div>
                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Ou Numéro de téléphone</label>
                    <input type="tel" id="track-phone" placeholder="Ex: +235 66 00 00 00" 
                           class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                </div>
            </div>

            <button type="submit" id="track-btn" 
                    class="w-full py-3.5 px-6 bg-emerald-800 hover:bg-emerald-900 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow flex items-center justify-center gap-2">
                <i data-lucide="search" class="w-4 h-4"></i>
                <span>Rechercher ma commande</span>
            </button>
        </form>

        <div id="track-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold"></div>
    </div>

    <!-- Résultat dynamique de suivi -->
    <div id="track-result" class="hidden mt-8 space-y-6">
        <!-- Rempli en JS -->
    </div>

</div>

<script>
    const trackForm = document.getElementById('track-form');
    const trackBtn = document.getElementById('track-btn');
    const trackRefInput = document.getElementById('track-ref');
    const trackPhoneInput = document.getElementById('track-phone');
    const trackError = document.getElementById('track-error');
    const trackResult = document.getElementById('track-result');

    async function searchOrder() {
        const ref = trackRefInput.value.trim();
        const phone = trackPhoneInput.value.trim();

        if (!ref && !phone) {
            trackError.textContent = 'Veuillez renseigner votre référence ou numéro de téléphone.';
            trackError.classList.remove('hidden');
            return;
        }

        trackError.classList.add('hidden');
        trackBtn.disabled = true;
        const originalHtml = trackBtn.innerHTML;
        trackBtn.innerHTML = `<span>Recherche en cours...</span>`;

        try {
            const res = await fetch(`<?= BASE_URL ?>/api/track.php?ref=${encodeURIComponent(ref)}&phone=${encodeURIComponent(phone)}`);
            const data = await res.json();

            if (data.success && data.order) {
                renderOrderDetails(data.order);
            } else {
                trackResult.classList.add('hidden');
                trackError.textContent = data.error || 'Aucune commande trouvée.';
                trackError.classList.remove('hidden');
            }
        } catch (e) {
            trackError.textContent = 'Erreur lors de la recherche. Veuillez réessayer.';
            trackError.classList.remove('hidden');
        } finally {
            trackBtn.disabled = false;
            trackBtn.innerHTML = originalHtml;
            if (window.lucide) window.lucide.createIcons();
        }
    }

    function renderOrderDetails(order) {
        trackResult.classList.remove('hidden');

        // Calcul étape visuelle
        const steps = ['Nouvelle / reçue', 'Confirmée', 'En préparation', 'En livraison', 'Terminée'];
        const currentIdx = steps.indexOf(order.statut_commande);

        trackResult.innerHTML = `
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-6">
                
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 pb-4 border-b border-stone-100">
                    <div>
                        <span class="text-xs font-mono font-bold text-stone-400">RÉFÉRENCE : ${order.reference}</span>
                        <h2 class="text-xl font-black text-stone-900 mt-0.5">Commande de ${order.client_nom}</h2>
                    </div>
                    <div class="flex items-center gap-2">
                        ${order.statut_badge}
                        ${order.payment_badge}
                    </div>
                </div>

                <!-- Timeline de progression -->
                <div class="py-4">
                    <div class="grid grid-cols-4 gap-2 text-center text-xs">
                        <div class="space-y-2">
                            <div class="w-8 h-8 rounded-full ${currentIdx >= 0 ? 'bg-emerald-800 text-white font-bold' : 'bg-stone-200 text-stone-500'} flex items-center justify-center mx-auto">
                                ✓
                            </div>
                            <span class="font-bold text-stone-800 text-[11px] block">Reçue</span>
                        </div>
                        <div class="space-y-2">
                            <div class="w-8 h-8 rounded-full ${currentIdx >= 1 ? 'bg-emerald-800 text-white font-bold' : 'bg-stone-200 text-stone-500'} flex items-center justify-center mx-auto">
                                ${currentIdx >= 1 ? '✓' : '2'}
                            </div>
                            <span class="font-bold text-stone-800 text-[11px] block">Confirmée</span>
                        </div>
                        <div class="space-y-2">
                            <div class="w-8 h-8 rounded-full ${currentIdx >= 3 ? 'bg-emerald-800 text-white font-bold' : 'bg-stone-200 text-stone-500'} flex items-center justify-center mx-auto">
                                ${currentIdx >= 3 ? '✓' : '3'}
                            </div>
                            <span class="font-bold text-stone-800 text-[11px] block">En livraison</span>
                        </div>
                        <div class="space-y-2">
                            <div class="w-8 h-8 rounded-full ${currentIdx >= 4 ? 'bg-emerald-800 text-white font-bold' : 'bg-stone-200 text-stone-500'} flex items-center justify-center mx-auto">
                                ${currentIdx >= 4 ? '✓' : '4'}
                            </div>
                            <span class="font-bold text-stone-800 text-[11px] block">Livrée</span>
                        </div>
                    </div>
                </div>

                <!-- Info Livraison & Livreur -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 rounded-2xl bg-stone-50 border border-stone-200 text-xs text-stone-600">
                    <div>
                        <span class="font-bold text-stone-900 block mb-1">Mode & Zone de livraison :</span>
                        <p class="font-semibold text-emerald-900">${order.mode_reception}</p>
                        <p>${order.zone_nom || 'Zone locale'}</p>
                        <p class="text-stone-500 mt-1">${order.adresse_detaillee}</p>
                    </div>
                    <div>
                        <span class="font-bold text-stone-900 block mb-1">Livreur / Transporteur partenaire :</span>
                        <p class="font-bold text-stone-800">${order.livreur_nom}</p>
                        <span class="font-bold text-stone-900 block mt-2">Date d'enregistrement :</span>
                        <p>${order.date_creation}</p>
                    </div>
                </div>

                <!-- Articles -->
                <div class="space-y-2">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-stone-500">Articles (${order.items.length})</h4>
                    <div class="divide-y divide-stone-100 border border-stone-100 rounded-xl p-3 bg-stone-50/50">
                        ${order.items.map(item => `
                            <div class="py-2 flex justify-between items-center text-xs">
                                <div>
                                    <span class="font-bold text-stone-900">${item.nom_produit}</span>
                                    ${item.nom_variante ? `<span class="text-emerald-800 block text-[11px]">${item.nom_variante}</span>` : ''}
                                    <span class="text-stone-400">Qté : ${item.quantite}</span>
                                </div>
                                <span class="font-bold text-stone-900">${item.sous_total}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <!-- Total -->
                <div class="flex justify-between items-baseline pt-3 border-t border-stone-100">
                    <span class="font-bold text-stone-900 text-sm">Montant Total TTC :</span>
                    <span class="font-black text-emerald-950 text-xl">${order.total}</span>
                </div>

                <!-- Contact Rapide -->
                <div class="pt-2 text-center">
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('phone_whatsapp', '23566280000')) ?>?text=${encodeURIComponent("Bonjour ROM'S Natural, je souhaite avoir des informations sur ma commande " + order.reference)}" 
                       target="_blank" class="inline-flex items-center gap-2 text-xs font-bold text-emerald-800 hover:underline">
                        <span>Une question sur votre livraison ? Contactez le service logistique sur WhatsApp →</span>
                    </a>
                </div>

            </div>
        `;

        if (window.lucide) window.lucide.createIcons();
    }

    trackForm.addEventListener('submit', (e) => {
        e.preventDefault();
        searchOrder();
    });

    // Recherche automatique si paramètre GET ref présent
    if (trackRefInput.value.trim()) {
        searchOrder();
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

