<?php
/**
 * Page Contact & Adresses - ROM'S NATURAL
 * Conforme PRD Section 5.1 & 11
 */

$pageTitle = "Contact & Localisation";
$activeNav = "contact";
require_once __DIR__ . '/includes/header.php';

$sentSuccess = false;
$sendError = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $sujet = trim($_POST['sujet'] ?? 'Demande depuis le site');
    $message = trim($_POST['message'] ?? '');
    $recipient = getSetting('email_contact', 'anoubaissem7@gmail.com');

    if ($nom !== '' && $telephone !== '' && $message !== '' && filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
        $mailSubject = "Message site ROM'S Natural : " . preg_replace('/[\\r\\n]+/', ' ', $sujet);
        $mailBody = "Nom : {$nom}\nTéléphone : {$telephone}\nObjet : {$sujet}\n\nMessage :\n{$message}";
        $mailHeaders = "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Reply-To: " . preg_replace('/[\\r\\n]+/', '', $recipient) . "\r\n";
        $sentSuccess = mail($recipient, $mailSubject, $mailBody, $mailHeaders);
        $sendError = !$sentSuccess;
    } else {
        $sendError = true;
    }
}
?>

<div class="bg-stone-100/70 border-b border-stone-200 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-2">
        <h1 class="text-2xl sm:text-3xl font-black text-stone-900">Nous Contacter</h1>
        <p class="text-xs sm:text-sm text-stone-500 max-w-md mx-auto">
            Une question sur une commande, un partenariat ou un conseil produit ? Notre équipe à Koumra (Mandoul) est à votre entière écoute.
        </p>
    </div>
</div>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Coordonnées et WhatsApp direct (5 cols) -->
        <div class="lg:col-span-5 space-y-6">
            
            <!-- Carte WhatsApp Direct (PRD Section 11) -->
            <div class="bg-[#25D366] text-white p-6 sm:p-8 rounded-3xl shadow-lg space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white/20 flex items-center justify-center">
                        <i data-lucide="message-circle" class="w-7 h-7 fill-white stroke-none"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-lg leading-tight">Assistance WhatsApp</h3>
                        <p class="text-xs text-emerald-100">Koumra, Mandoul • Réponse 7j/7</p>
                    </div>
                </div>
                <p class="text-xs text-white/90 leading-relaxed">
                    Échangez directement avec notre équipe à Koumra pour toute question sur nos laits au karité, huile de ricin, tamarin BIO ou expéditions au Tchad et à l'international.
                </p>
                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('phone_whatsapp', '23562891085')) ?>?text=<?= urlencode("Bonjour ROM'S Natural Koumra, je vous contacte depuis votre site.") ?>" 
                   target="_blank" rel="noopener noreferrer"
                   class="block text-center py-3.5 px-6 bg-white hover:bg-stone-100 text-[#128C7E] font-black text-xs uppercase tracking-wider rounded-2xl transition shadow">
                    Ouvrir une discussion WhatsApp
                </a>
            </div>

            <!-- Adresses & Téléphones -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm space-y-5">
                <h3 class="font-bold text-stone-900 text-base">Nos Coordonnées</h3>

                <div class="space-y-4 text-xs text-stone-600">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="map-pin" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="font-bold text-stone-900 block">Boutique & Siège :</span>
                            <p><?= nl2br(htmlspecialchars(getSetting('store_address', "Boutique & Atelier ROM'S Natural, Quartier Representant 1, Koumra, Province du Mandoul, Tchad"))) ?></p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="font-bold text-stone-900 block">Téléphone direct :</span>
                            <p><?= htmlspecialchars(getSetting('phone_contact', '+235 66 28 00 00')) ?></p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="font-bold text-stone-900 block">Courrier électronique :</span>
                            <p><?= htmlspecialchars(getSetting('email_contact', 'anoubaissem7@gmail.com')) ?></p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <span class="font-bold text-stone-900 block">Horaires de réception :</span>
                            <p><?= htmlspecialchars(getSetting('opening_hours', 'Lundi au Samedi : 08h00 - 18h30')) ?></p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Formulaire de Message (7 cols) -->
        <div class="lg:col-span-7 bg-white p-6 sm:p-10 rounded-3xl border border-stone-200 shadow-sm space-y-6">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-emerald-800">Écrivez-nous</span>
                <h2 class="text-xl sm:text-2xl font-black text-stone-900 mt-1">Laissez-nous un message</h2>
            </div>

            <?php if ($sentSuccess): ?>
                <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-700 flex-shrink-0"></i>
                    <span>Votre message a été transmis avec succès. Notre équipe vous recontactera rapidement.</span>
                </div>
            <?php endif; ?>

            <?php if ($sendError): ?>
                <div class="p-4 bg-red-50 border border-red-200 rounded-2xl text-red-900 text-xs font-semibold flex items-center gap-2">
                    <i data-lucide="alert-circle" class="w-5 h-5 text-red-700 flex-shrink-0"></i>
                    <span>Votre message n’a pas pu être envoyé. Veuillez réessayer ou nous écrire directement à <?= htmlspecialchars(getSetting('email_contact', 'anoubaissem7@gmail.com')) ?>.</span>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Votre Nom *</label>
                        <input type="text" name="nom" required placeholder="Ex: Fatimé Souleyman" 
                               class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Téléphone *</label>
                        <input type="tel" name="telephone" required placeholder="+235 66 00 00 00" 
                               class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Objet de votre demande</label>
                    <input type="text" name="sujet" placeholder="Renseignement produit, commande, partenariat..." 
                           class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Votre Message *</label>
                    <textarea name="message" rows="5" required placeholder="Détaillez votre message..." 
                              class="w-full px-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700"></textarea>
                </div>

                <button type="submit" class="w-full sm:w-auto px-8 py-3.5 bg-emerald-800 hover:bg-emerald-900 text-white font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow">
                    Envoyer le message
                </button>
            </form>
        </div>

    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
