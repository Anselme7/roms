<?php
/**
 * Page de Commande / Checkout Invité - ROM'S NATURAL
 * Conforme PRD Section 6 & 7
 */

require_once __DIR__ . '/config/settings.php';
require_once __DIR__ . '/includes/functions.php';

$cartItems = $_SESSION['cart'] ?? [];
if (empty($cartItems)) {
    header('Location: ' . BASE_URL . '/panier.php');
    exit;
}

$pageTitle = "Finaliser ma commande";
require_once __DIR__ . '/includes/header.php';

// Sous-total
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['price'] * $item['quantity']);
}

// Zones de livraison actives (PRD Section 7.1)
$zones = $pdo->query("SELECT * FROM delivery_zones WHERE actif = 1 ORDER BY ordre ASC")->fetchAll();

// Génération d'un jeton anti-doublon (PRD Section 14.3)
$submitToken = bin2hex(random_bytes(16));
?>

<div class="bg-stone-100/70 border-b border-stone-200 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-2xl sm:text-3xl font-black text-stone-900">Validation de votre commande</h1>
        <p class="text-xs sm:text-sm text-stone-500 mt-1">
            <span class="inline-flex items-center gap-1 text-emerald-800 font-bold">
                <i data-lucide="shield-check" class="w-4 h-4"></i> Commande express en mode invité
            </span> — Remplissez vos coordonnées pour recevoir votre colis.
        </p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <form id="checkout-form" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        <input type="hidden" name="submit_token" value="<?= $submitToken ?>">

        <!-- Formulaire Coordonnées & Livraison (8 cols) -->
        <div class="lg:col-span-7 space-y-8">
            
            <!-- Étape 1 : Coordonnées Client (PRD Section 6.2) -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-stone-100">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm">1</div>
                    <h2 class="text-base font-bold text-stone-900">Coordonnées de l'acheteur / destinataire</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Nom et Prénom *</label>
                        <input type="text" name="client_nom" required placeholder="Ex: Mahamat Ali" 
                               class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Numéro de Téléphone *</label>
                        <input type="tel" name="client_telephone" required placeholder="Ex: +235 66 00 00 00" 
                               class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                        <span class="text-[10px] text-stone-400 mt-0.5 block">Joignable pour le livreur (Airtel ou Moov)</span>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Adresse E-mail (Facultative)</label>
                    <input type="email" name="client_email" placeholder="Pour recevoir votre reçu électronique" 
                           class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                </div>
            </div>

            <!-- Étape 2 : Mode de Réception & Zone de Livraison (PRD Section 7) -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-stone-100">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm">2</div>
                    <h2 class="text-base font-bold text-stone-900">Mode de réception & Destination (Départ : Koumra, Mandoul)</h2>
                </div>

                <!-- Choix Réception -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="p-4 rounded-2xl border-2 cursor-pointer transition flex items-start gap-3 border-emerald-800 bg-emerald-50/40" id="label-mode-livraison">
                        <input type="radio" name="mode_reception" value="Livraison à domicile" checked class="mt-1 text-emerald-800 focus:ring-emerald-700" onchange="toggleReceptionMode(this.value)">
                        <div>
                            <span class="font-bold text-sm text-stone-900 block">Livraison à domicile / Expédition</span>
                            <span class="text-xs text-stone-500">Koumra, provinces du Tchad, CEMAC & Monde</span>
                        </div>
                    </label>

                    <label class="p-4 rounded-2xl border-2 cursor-pointer transition flex items-start gap-3 border-stone-200 bg-white" id="label-mode-retrait">
                        <input type="radio" name="mode_reception" value="Retrait au point de vente ROM'S" class="mt-1 text-emerald-800 focus:ring-emerald-700" onchange="toggleReceptionMode(this.value)">
                        <div>
                            <span class="font-bold text-sm text-stone-900 block">Retrait en boutique</span>
                            <span class="text-xs text-emerald-800 font-bold">Gratuit (Koumra - Mandoul)</span>
                        </div>
                    </label>
                </div>

                <!-- Zone de livraison spécifique -->
                <div id="section-zone-livraison" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Votre Ville ou Destination de Livraison *</label>
                        <select name="zone_id" id="zone-select" class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 bg-white font-semibold focus:outline-none focus:border-emerald-700" onchange="updateDeliveryFee()">
                            <option value="" data-fee="0">-- Sélectionnez votre destination (Départ : Koumra) --</option>
                            <?php foreach ($zones as $z): ?>
                                <option value="<?= $z['id'] ?>" data-fee="<?= $z['tarif_xaf'] ?>" data-delai="<?= htmlspecialchars($z['delai_estime']) ?>">
                                    <?= htmlspecialchars($z['nom_zone']) ?> — <?= formatPrice($z['tarif_xaf']) ?> (Délai : <?= htmlspecialchars($z['delai_estime']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Quartier, Repère précis ou Adresse *</label>
                        <textarea name="adresse_detaillee" rows="2" placeholder="Ex: Koumra Centre, ou N'Djamena Sabangali, ou Douala Cameroun..." 
                                  class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700"></textarea>
                    </div>
                </div>

                <!-- Info Retrait si sélectionné -->
                <div id="section-info-retrait" class="hidden p-4 rounded-2xl bg-amber-50 border border-amber-200 text-xs text-amber-900 space-y-1">
                    <p class="font-bold">Point de retrait officiel :</p>
                    <p><?= htmlspecialchars(getSetting('pickup_point_address', "Boutique ROM'S Natural, Grand Marché, Koumra (Mandoul)")) ?></p>
                    <p class="text-amber-800">Votre commande sera prête sous 1h après confirmation téléphonique au +235 62 89 10 85.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Instructions complémentaires (Facultatif)</label>
                    <input type="text" name="notes_client" placeholder="Ex: Livraison après 14h, ou appeler mon collègue si injoignable" 
                           class="w-full px-4 py-2.5 text-xs rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                </div>
            </div>

            <!-- Étape 3 : Mode de Paiement (PRD Section 6.3) -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-5">
                <div class="flex items-center gap-3 pb-3 border-b border-stone-100">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm">3</div>
                    <h2 class="text-base font-bold text-stone-900">Moyen de paiement</h2>
                </div>

                <div class="space-y-3">
                    <!-- Paiement à la livraison -->
                    <label class="p-4 rounded-2xl border-2 flex items-start gap-3 cursor-pointer border-emerald-800 bg-emerald-50/30 payment-option" onclick="selectPayment(this)">
                        <input type="radio" name="mode_paiement" value="Paiement à la livraison" checked class="mt-1 text-emerald-800 focus:ring-emerald-700">
                        <div class="flex-1">
                            <span class="font-bold text-sm text-stone-900 block">Paiement en espèces à la livraison (Cash)</span>
                            <span class="text-xs text-stone-500">Réglez directement au livreur au moment de la réception de votre colis.</span>
                        </div>
                    </label>

                    <!-- Mobile Money (Airtel / Moov) -->
                    <label class="p-4 rounded-2xl border-2 flex items-start gap-3 cursor-pointer border-stone-200 bg-white payment-option" onclick="selectPayment(this)">
                        <input type="radio" name="mode_paiement" value="Mobile Money (Airtel / Moov Tchad)" class="mt-1 text-emerald-800 focus:ring-emerald-700">
                        <div class="flex-1">
                            <span class="font-bold text-sm text-stone-900 block">Mobile Money (Airtel Money *436# ou Moov Money *156#)</span>
                            <span class="text-xs text-stone-500">Paiement direct sécurisé par téléphone mobile.</span>
                            
                            <div class="mt-3 p-3 bg-stone-50 rounded-xl border border-stone-200 text-xs text-stone-700 space-y-2">
                                <p class="font-semibold text-emerald-900">Numéros marchands officiels ROM'S Natural :</p>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-[11px]">
                                    <div class="p-2 bg-red-50 text-red-900 rounded-lg border border-red-200">
                                        <span class="font-bold block">Airtel Money Tchad</span>
                                        <span><?= htmlspecialchars(getSetting('payment_airtel_number', '+235 66 28 00 00 (*436#)')) ?></span>
                                    </div>
                                    <div class="p-2 bg-blue-50 text-blue-900 rounded-lg border border-blue-200">
                                        <span class="font-bold block">Moov Money Tchad</span>
                                        <span><?= htmlspecialchars(getSetting('payment_moov_number', '+235 99 28 00 00 (*156#)')) ?></span>
                                    </div>
                                </div>
                                <div class="pt-2">
                                    <label class="block text-[10px] font-bold uppercase text-stone-600 mb-1">Numéro de transaction ou référence SMS (Optionnel lors de la commande)</label>
                                    <input type="text" name="reference_paiement" placeholder="Ex: TX-98402 ou n° émetteur" class="w-full px-3 py-1.5 text-xs rounded-lg border border-stone-300">
                                </div>
                            </div>
                        </div>
                    </label>

                    <!-- Virement Bancaire -->
                    <label class="p-4 rounded-2xl border-2 flex items-start gap-3 cursor-pointer border-stone-200 bg-white payment-option" onclick="selectPayment(this)">
                        <input type="radio" name="mode_paiement" value="Virement bancaire / Dépôt" class="mt-1 text-emerald-800 focus:ring-emerald-700">
                        <div class="flex-1">
                            <span class="font-bold text-sm text-stone-900 block">Virement bancaire / Dépôt guichet</span>
                            <span class="text-xs text-stone-500">Banque Commerciale du Chari (BCC). Les coordonnées complètes s'afficheront sur votre reçu.</span>
                        </div>
                    </label>
                </div>
            </div>

        </div>

        <!-- Récapitulatif Panier & Validation (5 cols) -->
        <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-6">
                <h3 class="font-bold text-stone-900 text-base">Votre commande (<?= count($cartItems) ?> articles)</h3>

                <div class="divide-y divide-stone-100 max-h-72 overflow-y-auto pr-1">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="py-3 flex items-center gap-3">
                            <img src="<?= BASE_URL ?>/assets/images/placeholder.php?name=<?= urlencode($item['name']) ?>" 
                                 alt="" class="w-12 h-12 rounded-xl object-cover bg-stone-50 border border-stone-100">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-stone-900 truncate"><?= htmlspecialchars($item['name']) ?></h4>
                                <?php if (!empty($item['variant_name'])): ?>
                                    <span class="text-[10px] text-emerald-800 font-medium block"><?= htmlspecialchars($item['variant_name']) ?></span>
                                <?php endif; ?>
                                <span class="text-xs text-stone-500">Qté: <?= $item['quantity'] ?> × <?= formatPrice($item['price']) ?></span>
                            </div>
                            <span class="text-xs font-bold text-stone-900"><?= formatPrice($item['price'] * $item['quantity']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="space-y-3 pt-3 border-t border-stone-100 text-xs text-stone-600">
                    <div class="flex justify-between">
                        <span>Sous-total produits :</span>
                        <span class="font-bold text-stone-900" id="subtotal-val"><?= formatPrice($subtotal) ?></span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Frais de livraison :</span>
                        <span class="font-bold text-emerald-800" id="shipping-fee-val">0 FCFA</span>
                    </div>
                    <div class="pt-3 border-t border-stone-200 flex justify-between items-baseline">
                        <span class="font-black text-stone-900 text-base">Total à payer :</span>
                        <span class="font-black text-emerald-950 text-2xl" id="total-val"><?= formatPrice($subtotal) ?></span>
                    </div>
                </div>

                <!-- Message d'erreur dynamique -->
                <div id="checkout-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold"></div>

                <!-- Bouton Confirmer -->
                <button type="submit" id="submit-order-btn" 
                        class="w-full py-4 px-6 bg-emerald-800 hover:bg-emerald-900 text-white font-black text-sm uppercase tracking-wider rounded-2xl transition shadow-xl shadow-emerald-800/25 flex items-center justify-center gap-2">
                    <i data-lucide="check" class="w-5 h-5"></i>
                    <span>Confirmer ma commande</span>
                </button>

                <p class="text-[11px] text-stone-400 text-center leading-tight">
                    En confirmant, vous recevrez un numéro de suivi unique pour suivre en direct la préparation et l'expédition de vos produits.
                </p>
            </div>
        </div>

    </form>
</div>

<script>
    const subtotal = <?= (float)$subtotal ?>;

    function toggleReceptionMode(mode) {
        const zoneSection = document.getElementById('section-zone-livraison');
        const retraitInfo = document.getElementById('section-info-retrait');
        const labelLivraison = document.getElementById('label-mode-livraison');
        const labelRetrait = document.getElementById('label-mode-retrait');

        if (mode === 'Retrait au point de vente ROM\'S') {
            zoneSection.classList.add('hidden');
            retraitInfo.classList.remove('hidden');
            labelRetrait.classList.add('border-emerald-800', 'bg-emerald-50/40');
            labelRetrait.classList.remove('border-stone-200', 'bg-white');
            labelLivraison.classList.remove('border-emerald-800', 'bg-emerald-50/40');
            labelLivraison.classList.add('border-stone-200', 'bg-white');
            updateDeliveryFee(0);
        } else {
            zoneSection.classList.remove('hidden');
            retraitInfo.classList.add('hidden');
            labelLivraison.classList.add('border-emerald-800', 'bg-emerald-50/40');
            labelLivraison.classList.remove('border-stone-200', 'bg-white');
            labelRetrait.classList.remove('border-emerald-800', 'bg-emerald-50/40');
            labelRetrait.classList.add('border-stone-200', 'bg-white');
            updateDeliveryFee();
        }
    }

    function updateDeliveryFee(forcedFee = null) {
        let fee = 0;
        if (forcedFee !== null) {
            fee = forcedFee;
        } else {
            const zoneSelect = document.getElementById('zone-select');
            if (zoneSelect && zoneSelect.selectedIndex >= 0) {
                const opt = zoneSelect.options[zoneSelect.selectedIndex];
                fee = parseFloat(opt.dataset.fee) || 0;
            }
        }

        const total = subtotal + fee;
        document.getElementById('shipping-fee-val').textContent = new Intl.NumberFormat('fr-FR').format(fee) + ' FCFA';
        document.getElementById('total-val').textContent = new Intl.NumberFormat('fr-FR').format(total) + ' FCFA';
    }

    function selectPayment(element) {
        document.querySelectorAll('.payment-option').forEach(el => {
            el.classList.remove('border-emerald-800', 'bg-emerald-50/30');
            el.classList.add('border-stone-200', 'bg-white');
        });
        element.classList.add('border-emerald-800', 'bg-emerald-50/30');
        element.classList.remove('border-stone-200', 'bg-white');
    }

    // Soumission AJAX Checkout avec protection double clic
    const checkoutForm = document.getElementById('checkout-form');
    const submitBtn = document.getElementById('submit-order-btn');
    const errorBox = document.getElementById('checkout-error');

    checkoutForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        errorBox.classList.add('hidden');

        submitBtn.disabled = true;
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.innerHTML = `<span class="inline-block animate-spin mr-2">⏳</span> Enregistrement de votre commande...`;

        try {
            const formData = new FormData(checkoutForm);
            const res = await fetch('<?= BASE_URL ?>/api/checkout.php', {
                method: 'POST',
                body: formData
            });

            const data = await res.json();
            if (data.success && data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                errorBox.textContent = data.error || 'Une erreur est survenue lors de la validation.';
                errorBox.classList.remove('hidden');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnHtml;
                if (window.lucide) window.lucide.createIcons();
            }
        } catch (err) {
            errorBox.textContent = 'Erreur réseau. Veuillez vérifier votre connexion internet et réessayer.';
            errorBox.classList.remove('hidden');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
            if (window.lucide) window.lucide.createIcons();
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

