<?php
/**
 * Page FAQ & Conditions Générales - ROM'S NATURAL
 * Conforme PRD Section 5.1 & 14.2
 */

$pageTitle = "FAQ & Conditions de Vente";
$activeNav = "faq";
require_once __DIR__ . '/includes/header.php';
?>

<div class="bg-stone-100/70 border-b border-stone-200 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-2">
        <h1 class="text-2xl sm:text-3xl font-black text-stone-900">Questions Fréquentes & Modalités</h1>
        <p class="text-xs sm:text-sm text-stone-500 max-w-lg mx-auto">
            Retrouvez toutes les réponses concernant les commandes sans compte, nos zones de livraison au Tchad, les paiements et retours.
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-10">
    
    <!-- 1. Commandes & Compte Client -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-sm">
                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
            </div>
            <h2 class="text-lg font-bold text-stone-900">1. Commandes & Parcours d'achat</h2>
        </div>

        <div class="space-y-4 text-xs text-stone-600 leading-relaxed">
            <div>
                <h4 class="font-bold text-stone-800 text-sm">Dois-je créer un compte avec mot de passe pour commander ?</h4>
                <p class="mt-1">Non. Conformément à notre charte de simplicité (PRD Section 5.3), tout achat s'effectue en mode invité direct. Vous n'avez besoin que de votre nom, téléphone et adresse au Tchad.</p>
            </div>
            <div>
                <h4 class="font-bold text-stone-800 text-sm">Comment savoir si ma commande a bien été prise en compte ?</h4>
                <p class="mt-1">Dès validation sur le site, une page de confirmation s'affiche avec votre référence unique (ex: RN-2611-XXXX). Vous pouvez également suivre en direct son état sur notre page <a href="<?= BASE_URL ?>/suivi.php" class="text-emerald-800 font-bold underline">Suivi de commande</a>.</p>
            </div>
        </div>
    </div>

    <!-- 2. Modes de Paiement au Tchad (PRD Section 6.3) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4" id="paiements">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-sm">
                <i data-lucide="credit-card" class="w-5 h-5"></i>
            </div>
            <h2 class="text-lg font-bold text-stone-900">2. Modes de Paiement au Tchad</h2>
        </div>

        <div class="space-y-4 text-xs text-stone-600 leading-relaxed">
            <div>
                <h4 class="font-bold text-stone-800 text-sm">Quels sont les moyens de paiement acceptés ?</h4>
                <ul class="list-disc pl-5 mt-1 space-y-1">
                    <li><strong>Paiement en espèces à la livraison :</strong> Vous remettez le montant directement au coursier lors de la réception.</li>
                    <li><strong>Airtel Money Tchad :</strong> Via le code <code>*436#</code> vers notre compte marchand officiel <code><?= htmlspecialchars(getSetting('payment_airtel_number', '+235 66 28 00 00')) ?></code>.</li>
                    <li><strong>Moov Money Tchad :</strong> Via le code <code>*156#</code> vers notre compte marchand <code><?= htmlspecialchars(getSetting('payment_moov_number', '+235 99 28 00 00')) ?></code>.</li>
                    <li><strong>Virement bancaire / Dépôt :</strong> Banque Commerciale du Chari (BCC).</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- 3. Livraison Nationale (PRD Section 7) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-800 flex items-center justify-center font-bold text-sm">
                <i data-lucide="truck" class="w-5 h-5"></i>
            </div>
            <h2 class="text-lg font-bold text-stone-900">3. Zones de Livraison & Délais</h2>
        </div>

        <div class="space-y-4 text-xs text-stone-600 leading-relaxed">
            <p>Toutes nos expéditions partent directement de notre atelier et boutique centrale à <strong>Koumra (Province du Mandoul)</strong> :</p>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse border border-stone-200">
                    <thead>
                        <tr class="bg-stone-50 border-b border-stone-200 text-stone-800 font-bold">
                            <th class="p-2.5">Destination (Départ Koumra)</th>
                            <th class="p-2.5">Tarif (XAF)</th>
                            <th class="p-2.5">Délai estimé</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        <tr>
                            <td class="p-2.5 font-medium">Koumra Ville (Livraison locale express)</td>
                            <td class="p-2.5 font-bold text-emerald-800">500 FCFA</td>
                            <td class="p-2.5">1h à 3h</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-medium">Retrait en boutique ROM'S (Koumra)</td>
                            <td class="p-2.5 font-bold text-emerald-800">GRATUIT</td>
                            <td class="p-2.5">Sous 1h</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-medium">Sarh & Moyen-Chari (Proche Koumra)</td>
                            <td class="p-2.5 font-bold text-emerald-800">1 500 FCFA</td>
                            <td class="p-2.5">Le jour même / 24h</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-medium">Moundou & Doba (Logone)</td>
                            <td class="p-2.5 font-bold text-emerald-800">2 500 FCFA</td>
                            <td class="p-2.5">24h ouvrées</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-medium">N'Djamena (Capitale)</td>
                            <td class="p-2.5 font-bold text-emerald-800">3 500 FCFA</td>
                            <td class="p-2.5">24h à 48h</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-medium">Autres Provinces Tchad (Abéché, Pala, Kélo, etc.)</td>
                            <td class="p-2.5 font-bold text-emerald-800">3 000 à 5 000 FCFA</td>
                            <td class="p-2.5">48h à 72h</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-medium">Afrique Centrale / CEMAC (Cameroun, etc.)</td>
                            <td class="p-2.5 font-bold text-emerald-800">8 500 FCFA</td>
                            <td class="p-2.5">3 à 5 jours</td>
                        </tr>
                        <tr>
                            <td class="p-2.5 font-medium">International & Diaspora (France, Europe, Monde)</td>
                            <td class="p-2.5 font-bold text-emerald-800">18 000 FCFA</td>
                            <td class="p-2.5">5 à 8 jours (Fret aérien / DHL)</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 4. Retours, Réclamations & Confidentialité (PRD Section 14.2) -->
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4" id="retours">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-purple-100 text-purple-800 flex items-center justify-center font-bold text-sm">
                <i data-lucide="shield" class="w-5 h-5"></i>
            </div>
            <h2 class="text-lg font-bold text-stone-900">4. Retours & Confidentialité</h2>
        </div>

        <div class="space-y-4 text-xs text-stone-600 leading-relaxed">
            <div>
                <h4 class="font-bold text-stone-800 text-sm">Politique de retour et réclamation :</h4>
                <p class="mt-1">Si un produit arrive endommagé ou non conforme, vous disposez d'un délai de 48 heures après réception pour contacter notre service client avec une photo du lot concerné. Un échange ou remboursement immédiat sera effectué.</p>
            </div>
            <div id="confidentialite" class="pt-2 border-t border-stone-100">
                <h4 class="font-bold text-stone-800 text-sm">Protection des données personnelles :</h4>
                <p class="mt-1">Vos données de contact (nom, téléphone, adresse) sont strictement utilisées pour le traitement logistique de vos commandes. Elles ne sont jamais revendues ni cédées à des tiers.</p>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

