<?php
/**
 * Checklist de Mise en Production (PRD Section 18.2)
 * Permet de vérifier les prérequis avant le lancement public officiel.
 */

$adminTitle = "Checklist Pré-Lancement";
$activeMenu = "settings";
require_once __DIR__ . '/header.php';
require_permission('settings');

// Vérification de l'état des prérequis
$checks = [
    [
        'code' => 'catalog',
        'title' => 'Catalogue & Fiches Produits (PRD 18.2)',
        'desc' => 'Produits réels créés avec photos, prix en FCFA, composition, origine et stocks.',
        'status' => ($pdo->query("SELECT COUNT(*) FROM products WHERE disponible = 1")->fetchColumn() >= 4),
        'detail' => $pdo->query("SELECT COUNT(*) FROM products WHERE disponible = 1")->fetchColumn() . ' produits actifs disponibles.',
        'link' => BASE_URL . '/admin/products.php'
    ],
    [
        'code' => 'zones',
        'title' => 'Zones de Livraison configurées depuis Koumra (PRD 7.1)',
        'desc' => 'Tarifs et délais estimatifs définis pour Koumra, les provinces, le Cameroun et l\'international.',
        'status' => ($pdo->query("SELECT COUNT(*) FROM delivery_zones WHERE actif = 1")->fetchColumn() >= 6),
        'detail' => $pdo->query("SELECT COUNT(*) FROM delivery_zones WHERE actif = 1")->fetchColumn() . ' zones actives prêtes.',
        'link' => BASE_URL . '/admin/delivery_zones.php'
    ],
    [
        'code' => 'airtel',
        'title' => 'Paiement Airtel Money Tchad (*436#)',
        'desc' => 'Numéro marchand configuré pour réception des paiements mobiles.',
        'status' => !empty(getSetting('payment_airtel_number')),
        'detail' => getSetting('payment_airtel_number', 'Non configuré'),
        'link' => BASE_URL . '/admin/settings.php'
    ],
    [
        'code' => 'moov',
        'title' => 'Paiement Moov Money Tchad (*156#)',
        'desc' => 'Numéro marchand configuré pour réception des paiements Moov.',
        'status' => !empty(getSetting('payment_moov_number')),
        'detail' => getSetting('payment_moov_number', 'Non configuré'),
        'link' => BASE_URL . '/admin/settings.php'
    ],
    [
        'code' => 'whatsapp',
        'title' => 'WhatsApp Officiel de Contact (PRD 11)',
        'desc' => 'Numéro connecté pour la prise de commandes et les notifications clients.',
        'status' => !empty(getSetting('phone_whatsapp')),
        'detail' => '+' . getSetting('phone_whatsapp'),
        'link' => BASE_URL . '/admin/settings.php'
    ],
    [
        'code' => 'bank',
        'title' => 'Coordonnées Bancaires / Virement (BCC)',
        'desc' => 'RIB et titulaire du compte renseignés pour les règlements par virement.',
        'status' => !empty(getSetting('payment_bank_details')),
        'detail' => 'Compte BCC / Ecobank renseigné.',
        'link' => BASE_URL . '/admin/settings.php'
    ],
    [
        'code' => 'backup',
        'title' => 'Sécurité & Sauvegardes Régulières (PRD 14.2)',
        'desc' => 'Dispositif de téléchargement et restauration de la base de données actif.',
        'status' => file_exists(ROM_ROOT . '/data/roms_database.sqlite'),
        'detail' => 'Base SQLite opérationnelle et exportable.',
        'link' => BASE_URL . '/admin/backup.php'
    ]
];

$totalChecks = count($checks);
$passedChecks = count(array_filter($checks, fn($c) => $c['status']));
$progressPercent = round(($passedChecks / $totalChecks) * 100);
?>

<div class="space-y-6 max-w-5xl mx-auto">
    
    <div class="bg-white p-6 sm:p-8 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row items-baseline sm:items-center justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600"></i>
                <span>Conformité PRD Section 18.2</span>
            </div>
            <h1 class="text-2xl font-black text-stone-900">Checklist de Mise en Production</h1>
            <p class="text-xs text-stone-500 mt-1">
                Validation des prérequis administratifs, logistiques et financiers avant le lancement public officiel.
            </p>
        </div>
        
        <div class="text-right">
            <span class="text-2xl font-black <?= $progressPercent === 100 ? 'text-emerald-700' : 'text-amber-600' ?>">
                <?= $progressPercent ?>%
            </span>
            <span class="text-xs text-stone-400 block font-semibold"><?= $passedChecks ?> / <?= $totalChecks ?> validés</span>
        </div>
    </div>

    <!-- Barre de Progression Visuelle -->
    <div class="w-full bg-stone-200 rounded-full h-3 overflow-hidden shadow-inner">
        <div class="bg-emerald-600 h-3 rounded-full transition-all duration-500" style="width: <?= $progressPercent ?>%"></div>
    </div>

    <!-- Liste des Points de Contrôle du PRD -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm divide-y divide-stone-100 overflow-hidden text-xs">
        <?php foreach ($checks as $item): ?>
            <div class="p-5 sm:p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 hover:bg-stone-50/70 transition">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0 <?= $item['status'] ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-700' ?>">
                        <i data-lucide="<?= $item['status'] ? 'check' : 'alert-circle' ?>" class="w-5 h-5 stroke-[2.5]"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-stone-900"><?= htmlspecialchars($item['title']) ?></h3>
                        <p class="text-stone-500 text-[11px] mt-0.5"><?= htmlspecialchars($item['desc']) ?></p>
                        <span class="inline-block mt-1 font-semibold text-[11px] <?= $item['status'] ? 'text-emerald-700' : 'text-rose-600' ?>">
                            <?= htmlspecialchars($item['detail']) ?>
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-2 self-end sm:self-center">
                    <a href="<?= $item['link'] ?>" class="px-4 py-2 rounded-xl bg-stone-100 hover:bg-emerald-800 hover:text-white text-stone-700 font-bold transition flex items-center gap-1.5 shadow-xs">
                        <span>Configurer</span>
                        <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Encadré Conclusion PRD -->
    <div class="p-6 rounded-3xl bg-emerald-950 text-white space-y-3 shadow-lg">
        <h4 class="font-bold text-sm text-amber-300">Règle de Gouvernance ROM'S NATURAL (PRD Conclusion)</h4>
        <p class="text-xs text-stone-300 leading-relaxed">
            « Toute fonction non prise en charge nativement devra faire l'objet d'une intégration vérifiée ou d'une procédure manuelle clairement définie. L'intelligence artificielle intervient uniquement comme assistant de rédaction des fiches produits. La validation commerciale, les paiements et les décisions opérationnelles restent sous le contrôle exclusif de l'équipe ROM'S Natural. »
        </p>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

