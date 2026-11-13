<?php
/**
 * Paramètres Généraux de la Boutique & IA
 * Conforme PRD Section 6.3, 10, 11, 13 & 18
 */

$adminTitle = "Paramètres & Configuration";
$activeMenu = "settings";
require_once __DIR__ . '/header.php';
require_permission('settings');

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    $keys = [
        'site_name',
        'site_slogan',
        'site_description',
        'phone_contact',
        'phone_whatsapp',
        'email_contact',
        'store_address',
        'pickup_point_address',
        'opening_hours',
        'payment_cod_enabled',
        'payment_airtel_enabled',
        'payment_airtel_number',
        'payment_moov_enabled',
        'payment_moov_number',
        'payment_bank_enabled',
        'payment_bank_details',
        'gemini_api_key'
    ];

    foreach ($keys as $k) {
        $val = $_POST[$k] ?? '';
        updateSetting($k, $val);
    }

    $message = "Tous les paramètres ont été enregistrés avec succès.";
}
?>

<div class="space-y-6 max-w-5xl mx-auto">
    
    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm">
        <h2 class="text-xl font-bold text-stone-900">Paramètres Généraux de ROM'S NATURAL</h2>
        <p class="text-xs text-stone-500 mt-1">
            Configurez les coordonnées de la marque, les comptes de paiement au Tchad et la clé IA (PRD Sections 6.3, 10, 11 & 18).
        </p>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-700"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-8">
        <input type="hidden" name="save_settings" value="1">

        <!-- 1. Identité & Coordonnées -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-stone-900 pb-2 border-b border-stone-100 flex items-center gap-2">
                <i data-lucide="store" class="w-4 h-4 text-emerald-800"></i>
                <span>Identité de la Boutique & Contact</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Nom du Site</label>
                    <input type="text" name="site_name" value="<?= htmlspecialchars(getSetting('site_name', "ROM'S NATURAL")) ?>" class="w-full px-3 py-2 rounded-xl border border-stone-300 font-bold">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Slogan</label>
                    <input type="text" name="site_slogan" value="<?= htmlspecialchars(getSetting('site_slogan')) ?>" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                </div>
            </div>

            <div class="text-xs">
                <label class="block font-bold text-stone-700 uppercase mb-1">Description / SEO</label>
                <textarea name="site_description" rows="2" class="w-full px-3 py-2 rounded-xl border border-stone-300"><?= htmlspecialchars(getSetting('site_description')) ?></textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Téléphone Appel Client</label>
                    <input type="text" name="phone_contact" value="<?= htmlspecialchars(getSetting('phone_contact', '+235 66 28 00 00')) ?>" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                </div>
                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Numéro WhatsApp Officiel (PRD 11)</label>
                    <input type="text" name="phone_whatsapp" value="<?= htmlspecialchars(getSetting('phone_whatsapp', '23566280000')) ?>" placeholder="23566000000" class="w-full px-3 py-2 rounded-xl border border-stone-300 font-mono">
                    <span class="text-[10px] text-stone-400 mt-0.5 block">Format international sans + (ex: 23566280000)</span>
                </div>
                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Email Officiel</label>
                    <input type="email" name="email_contact" value="<?= htmlspecialchars(getSetting('email_contact', 'contact@romsnatural.td')) ?>" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Adresse Boutique & Siège (N'Djamena)</label>
                    <textarea name="store_address" rows="2" class="w-full px-3 py-2 rounded-xl border border-stone-300"><?= htmlspecialchars(getSetting('store_address')) ?></textarea>
                </div>
                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Point de Retrait des Commandes (PRD 7.2)</label>
                    <textarea name="pickup_point_address" rows="2" class="w-full px-3 py-2 rounded-xl border border-stone-300"><?= htmlspecialchars(getSetting('pickup_point_address')) ?></textarea>
                </div>
            </div>
        </div>

        <!-- 2. Modes de Paiement au Tchad (PRD Section 6.3 & 18.2) -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-5">
            <h3 class="font-bold text-sm text-stone-900 pb-2 border-b border-stone-100 flex items-center gap-2">
                <i data-lucide="credit-card" class="w-4 h-4 text-emerald-800"></i>
                <span>Comptes de Paiement au Tchad</span>
            </h3>

            <!-- Paiement à la livraison -->
            <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 text-xs space-y-2">
                <label class="flex items-center gap-2 font-bold text-stone-900 cursor-pointer">
                    <input type="checkbox" name="payment_cod_enabled" value="1" <?= getSetting('payment_cod_enabled', '1') === '1' ? 'checked' : '' ?> class="rounded text-emerald-800">
                    <span>Activer le paiement en espèces à la livraison (Cash à la remise)</span>
                </label>
                <p class="text-stone-500">Le client remet le montant total directement au coursier ou transporteur.</p>
            </div>

            <!-- Airtel Money & Moov Money -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-2xl bg-red-50/50 border border-red-200 space-y-2">
                    <label class="flex items-center gap-2 font-bold text-red-900 cursor-pointer">
                        <input type="checkbox" name="payment_airtel_enabled" value="1" <?= getSetting('payment_airtel_enabled', '1') === '1' ? 'checked' : '' ?> class="rounded text-red-700">
                        <span>Airtel Money Tchad</span>
                    </label>
                    <label class="block font-semibold text-stone-700">Instructions / Compte Marchand / USSD :</label>
                    <input type="text" name="payment_airtel_number" value="<?= htmlspecialchars(getSetting('payment_airtel_number', '+235 66 28 00 00 (*436#)')) ?>" class="w-full px-3 py-2 rounded-xl border border-red-200 bg-white">
                </div>

                <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-200 space-y-2">
                    <label class="flex items-center gap-2 font-bold text-blue-900 cursor-pointer">
                        <input type="checkbox" name="payment_moov_enabled" value="1" <?= getSetting('payment_moov_enabled', '1') === '1' ? 'checked' : '' ?> class="rounded text-blue-700">
                        <span>Moov Money Tchad</span>
                    </label>
                    <label class="block font-semibold text-stone-700">Instructions / Compte Marchand / USSD :</label>
                    <input type="text" name="payment_moov_number" value="<?= htmlspecialchars(getSetting('payment_moov_number', '+235 99 28 00 00 (*156#)')) ?>" class="w-full px-3 py-2 rounded-xl border border-blue-200 bg-white">
                </div>
            </div>

            <!-- Virement Bancaire -->
            <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 text-xs space-y-2">
                <label class="flex items-center gap-2 font-bold text-stone-900 cursor-pointer">
                    <input type="checkbox" name="payment_bank_enabled" value="1" <?= getSetting('payment_bank_enabled', '1') === '1' ? 'checked' : '' ?> class="rounded text-emerald-800">
                    <span>Activer le Virement / Dépôt Bancaire</span>
                </label>
                <label class="block font-semibold text-stone-700">Coordonnées Bancaires Complètes (Banque Commerciale du Chari, etc.) :</label>
                <textarea name="payment_bank_details" rows="3" class="w-full px-3 py-2 rounded-xl border border-stone-300 font-mono text-xs"><?= htmlspecialchars(getSetting('payment_bank_details')) ?></textarea>
            </div>
        </div>

        <!-- 3. Assistant Rédactionnel IA (PRD Section 10) -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-stone-100">
                <i data-lucide="sparkles" class="w-4 h-4 text-amber-500"></i>
                <h3 class="font-bold text-sm text-stone-900">Assistant IA pour fiches produits (PRD Section 10)</h3>
            </div>

            <p class="text-xs text-stone-500 leading-relaxed">
                Le système intègre un moteur rédactionnel intelligent autonome spécialement entraîné sur les produits du terroir tchadien (fonctionnant 100% hors-ligne). Vous pouvez également brancher votre clé d'API <strong>Google Gemini</strong> pour étendre la créativité rédactionnelle.
            </p>

            <div class="text-xs">
                <label class="block font-bold text-stone-700 uppercase mb-1">Clé API Google Gemini (Optionnelle)</label>
                <input type="password" name="gemini_api_key" value="<?= htmlspecialchars(getSetting('gemini_api_key')) ?>" placeholder="AIzaSy..." class="w-full px-3 py-2 rounded-xl border border-stone-300 font-mono">
                <span class="text-[10px] text-stone-400 mt-1 block">Si laissée vide, le moteur local ROM'S Natural prend le relais automatiquement.</span>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900">
                <strong>Rappel des limites impératives du PRD :</strong> L'IA ne peut jamais inventer d'ingrédients, ne peut faire de fausses promesses médicales et exige toujours une validation humaine avant publication.
            </div>
        </div>

        <button type="submit" class="px-8 py-4 bg-emerald-800 hover:bg-emerald-900 text-white font-extrabold text-xs uppercase tracking-wider rounded-2xl transition shadow-xl">
            Enregistrer tous les paramètres
        </button>
    </form>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

