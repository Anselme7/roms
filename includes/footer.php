<?php
/**
 * Pied de page public pour ROM'S NATURAL
 */
?>
    </main>

    <!-- Section Réassurance Client (PRD Section 5.1 & 14) -->
    <section class="bg-emerald-900 text-white py-12 mt-16 border-t border-emerald-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-800 flex items-center justify-center text-amber-300 flex-shrink-0 shadow-inner">
                        <i data-lucide="leaf" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-white">Basé à Koumra (Mandoul)</h4>
                        <p class="text-xs text-emerald-200 mt-1 leading-relaxed">Au cœur du terroir tchadien : karité pur, ricin végétal, tamarin Rokndoh et miels.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-800 flex items-center justify-center text-amber-300 flex-shrink-0 shadow-inner">
                        <i data-lucide="truck" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-white">Expédition Nationale & Export</h4>
                        <p class="text-xs text-emerald-200 mt-1 leading-relaxed">Départs depuis Koumra vers Sarh, Moundou, N'Djamena, tout le Tchad et la zone CEMAC / Diaspora.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-800 flex items-center justify-center text-amber-300 flex-shrink-0 shadow-inner">
                        <i data-lucide="wallet" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-white">Paiement Souple</h4>
                        <p class="text-xs text-emerald-200 mt-1 leading-relaxed">Paiement cash à la remise, Mobile Money (Airtel / Moov Money Tchad) ou virement bancaire.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-800 flex items-center justify-center text-amber-300 flex-shrink-0 shadow-inner">
                        <i data-lucide="user-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-sm text-white">Achat Facile Sans Compte</h4>
                        <p class="text-xs text-emerald-200 mt-1 leading-relaxed">Commandez directement en mode invité en 1 minute chrono avec votre seul numéro de téléphone.</p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer Détaillé -->
    <footer class="bg-emerald-950 text-stone-300 pt-16 pb-12 border-t border-emerald-900/60 text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">
                
                <!-- Col 1: Bio de la marque -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-black flex items-center justify-center p-1 border border-amber-500/30 overflow-hidden shadow-md">
                            <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="ROM'S NATURAL" class="w-full h-full object-contain">
                        </div>
                        <div>
                            <span class="text-xl font-black tracking-tight text-white font-serif block">ROM'S NATURAL</span>
                            <span class="text-[10px] font-bold uppercase tracking-widest text-amber-400 block">Koumra • Mandoul • Tchad</span>
                        </div>
                    </div>
                    <p class="text-xs text-stone-400 leading-relaxed max-w-sm">
                        Établie à Koumra dans la province du Mandoul, ROM'S Natural transforme artisanalement les pépites de la flore tchadienne (karité, ricin, tamarin, miel et soins authentiques) pour les expédier partout au Tchad et à l'international.
                    </p>
                    <div class="pt-2 flex items-center gap-3">
                        <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('phone_whatsapp', '23562891085')) ?>" target="_blank" class="w-9 h-9 rounded-full bg-emerald-900 hover:bg-emerald-800 text-emerald-300 flex items-center justify-center transition">
                            <i data-lucide="message-circle" class="w-4 h-4"></i>
                        </a>
                        <a href="tel:<?= preg_replace('/[^0-9]/', '', getSetting('phone_contact', '+235 62 89 10 85')) ?>" class="w-9 h-9 rounded-full bg-emerald-900 hover:bg-emerald-800 text-emerald-300 flex items-center justify-center transition">
                            <i data-lucide="phone" class="w-4 h-4"></i>
                        </a>
                        <a href="mailto:<?= htmlspecialchars(getSetting('email_contact', 'anoubaissem7@gmail.com')) ?>" class="w-9 h-9 rounded-full bg-emerald-900 hover:bg-emerald-800 text-emerald-300 flex items-center justify-center transition">
                            <i data-lucide="mail" class="w-4 h-4"></i>
                        </a>
                    </div>
                </div>

                <!-- Col 2: Familles du catalogue -->
                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-4">Nos Familles</h5>
                    <ul class="space-y-2 text-xs text-stone-400">
                        <li><a href="<?= BASE_URL ?>/catalogue.php?cat=cosmetiques-naturels" class="hover:text-white transition">Cosmétiques naturels</a></li>
                        <li><a href="<?= BASE_URL ?>/catalogue.php?cat=alimentaires-transformes" class="hover:text-white transition">Produits alimentaires</a></li>
                        <li><a href="<?= BASE_URL ?>/catalogue.php?cat=produits-artisanaux" class="hover:text-white transition">Artisanat tchadien</a></li>
                        <li><a href="<?= BASE_URL ?>/catalogue.php?cat=agricoles-transformes" class="hover:text-white transition">Produits agricoles</a></li>
                        <li><a href="<?= BASE_URL ?>/catalogue.php" class="hover:text-white transition text-amber-300 font-semibold">Tous les produits →</a></li>
                    </ul>
                </div>

                <!-- Col 3: Liens Utiles -->
                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-4">Commandes & Aide</h5>
                    <ul class="space-y-2 text-xs text-stone-400">
                        <li><a href="<?= BASE_URL ?>/suivi.php" class="hover:text-white transition">Suivre une commande</a></li>
                        <li><a href="<?= BASE_URL ?>/panier.php" class="hover:text-white transition">Mon panier</a></li>
                        <li><a href="<?= BASE_URL ?>/faq.php" class="hover:text-white transition">Zones de livraison & Délais</a></li>
                        <li><a href="<?= BASE_URL ?>/faq.php#paiements" class="hover:text-white transition">Moyens de paiement au Tchad</a></li>
                        <li><a href="<?= BASE_URL ?>/faq.php#retours" class="hover:text-white transition">Politique de retours</a></li>
                        <li><a href="<?= BASE_URL ?>/faq.php#confidentialite" class="hover:text-white transition">Confidentialité des données</a></li>
                    </ul>
                </div>

                <!-- Col 4: Contacts Physiques -->
                <div>
                    <h5 class="text-xs font-bold uppercase tracking-wider text-amber-400 mb-4">Nous Trouver</h5>
                    <div class="space-y-3 text-xs text-stone-400">
                        <div class="flex items-start gap-2">
                            <i data-lucide="map-pin" class="w-4 h-4 text-emerald-400 flex-shrink-0 mt-0.5"></i>
                            <span><?= nl2br(htmlspecialchars(getSetting('store_address', "Boutique & Atelier ROM'S Natural, Quartier Representant 1, Koumra, Province du Mandoul, Tchad"))) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="clock" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span><?= htmlspecialchars(getSetting('opening_hours', 'Lun - Sam : 08h00 - 18h30')) ?></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <i data-lucide="phone-call" class="w-4 h-4 text-emerald-400 flex-shrink-0"></i>
                            <span><?= htmlspecialchars(getSetting('phone_contact', '+235 66 28 00 00')) ?></span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Mentions de bas de page -->
            <div class="mt-12 pt-8 border-t border-emerald-900/40 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-stone-500">
                <p>&copy; <?= date('Y') ?> ROM'S NATURAL SARL. Tous droits réservés. République du Tchad.</p>
                <div class="flex items-center gap-6">
                    <span class="text-amber-400/80 font-medium">Fait avec fierté au Tchad 🇹🇩</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bouton Flottant WhatsApp (PRD Section 11) -->
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', getSetting('phone_whatsapp', '23566280000')) ?>?text=<?= urlencode("Bonjour ROM'S Natural, je vous contacte depuis votre boutique en ligne.") ?>" 
       target="_blank" rel="noopener noreferrer"
       class="floating-whatsapp flex items-center justify-center w-14 h-14 bg-[#25D366] text-white rounded-full transition-transform"
       title="Assistance WhatsApp en direct">
        <i data-lucide="message-circle" class="w-7 h-7 fill-white stroke-none"></i>
    </a>

    <!-- Scripts Javascripts -->
    <script src="<?= BASE_URL ?>/assets/js/main.js"></script>
    <script>
        if (window.lucide) {
            window.lucide.createIcons();
        }
    </script>
</body>
</html>
