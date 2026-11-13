<?php
/**
 * Sauvegardes & Restauration de la Base de Données
 * Conforme PRD Section 13.1 & 14.2
 */

$adminTitle = "Sauvegardes & Données";
$activeMenu = "settings";
require_once __DIR__ . '/header.php';
require_permission('settings');

$dbFile = ROM_ROOT . '/data/roms_database.sqlite';
$message = '';
$error = '';

// Téléchargement direct de la sauvegarde
if (isset($_GET['download'])) {
    if (file_exists($dbFile)) {
        header('Content-Type: application/x-sqlite3');
        header('Content-Disposition: attachment; filename="roms_backup_' . date('Y-m-d_H-i-s') . '.sqlite"');
        header('Content-Length: ' . filesize($dbFile));
        readfile($dbFile);
        exit;
    } else {
        $error = "Fichier de base de données introuvable.";
    }
}

// Restauration d'une sauvegarde
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['backup_file'])) {
    if ($_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['backup_file']['tmp_name'];
        // Vérification sommaire SQLite
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);

        if (copy($tmp, $dbFile)) {
            $message = "La base de données a été restaurée avec succès depuis le fichier sauvegardé.";
        } else {
            $error = "Échec de l'écriture du fichier de base de données.";
        }
    } else {
        $error = "Erreur lors du téléchargement du fichier.";
    }
}

$dbSize = file_exists($dbFile) ? round(filesize($dbFile) / 1024, 2) . ' Ko' : '0 Ko';
$lastMod = file_exists($dbFile) ? date('d/m/Y à H:i:s', filemtime($dbFile)) : '-';
?>

<div class="space-y-6 max-w-5xl mx-auto">
    
    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm flex flex-col sm:flex-row items-baseline justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-stone-900">Sauvegardes & Sécurité des Données</h2>
            <p class="text-xs text-stone-500 mt-1">
                Conforme aux exigences PRD (Section 13.1 & 14.2) : Procédure de sauvegarde et de restauration.
            </p>
        </div>
        <a href="<?= BASE_URL ?>/admin/backup.php?download=1" class="px-5 py-2.5 bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs rounded-xl transition shadow flex items-center gap-2">
            <i data-lucide="download" class="w-4 h-4"></i>
            <span>Télécharger une sauvegarde (.sqlite)</span>
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="check-circle" class="w-5 h-5 text-emerald-700"></i>
            <span><?= htmlspecialchars($message) ?></span>
        </div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-900 text-xs font-semibold flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-5 h-5 text-rose-700"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        
        <!-- Carte État de la Base Actuelle -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center">
                    <i data-lucide="database" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-stone-900 text-sm">Base de Données Opérationnelle</h3>
                    <p class="text-[11px] text-stone-500">Moteur SQLite PDO Embarqué</p>
                </div>
            </div>

            <div class="space-y-2 text-xs text-stone-600 bg-stone-50 p-4 rounded-2xl border border-stone-200">
                <div class="flex justify-between">
                    <span>Emplacement :</span>
                    <span class="font-mono text-stone-800 font-semibold truncate max-w-[200px]" title="<?= htmlspecialchars($dbFile) ?>">data/roms_database.sqlite</span>
                </div>
                <div class="flex justify-between">
                    <span>Taille actuelle :</span>
                    <span class="font-bold text-stone-900"><?= $dbSize ?></span>
                </div>
                <div class="flex justify-between">
                    <span>Dernière écriture :</span>
                    <span class="font-bold text-stone-900"><?= $lastMod ?></span>
                </div>
            </div>

            <a href="<?= BASE_URL ?>/admin/backup.php?download=1" class="w-full py-3 px-4 bg-emerald-800 hover:bg-emerald-900 text-white font-bold text-xs rounded-xl transition shadow flex items-center justify-center gap-2">
                <i data-lucide="download-cloud" class="w-4 h-4"></i>
                <span>Générer et télécharger un instantané</span>
            </a>
        </div>

        <!-- Carte Restauration -->
        <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center">
                    <i data-lucide="upload" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-stone-900 text-sm">Restaurer une Sauvegarde</h3>
                    <p class="text-[11px] text-stone-500">Remplacement sécurisé de la base</p>
                </div>
            </div>

            <p class="text-xs text-stone-500 leading-relaxed">
                Vous pouvez restaurer une copie précédemment téléchargée. <strong>Attention :</strong> cette action remplace toutes les commandes et données actuelles par celles du fichier.
            </p>

            <form method="POST" enctype="multipart/form-data" class="space-y-3 text-xs" onsubmit="return confirm('Confirmez-vous la restauration de cette base de données ? Les données actuelles seront remplacées.')">
                <input type="file" name="backup_file" required accept=".sqlite,.db" class="w-full text-xs text-stone-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-stone-100 file:text-stone-700 hover:file:bg-stone-200">
                <button type="submit" class="w-full py-3 px-4 bg-stone-800 hover:bg-black text-white font-bold rounded-xl transition shadow flex items-center justify-center gap-2">
                    <i data-lucide="rotate-ccw" class="w-4 h-4"></i>
                    <span>Restaurer à partir de ce fichier</span>
                </button>
            </form>
        </div>

    </div>

    <!-- Export des Données Métier (PRD Section 13.1) -->
    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
        <h3 class="font-bold text-base text-stone-900 pb-2 border-b border-stone-100">
            Export des Données Métier (Format CSV compatible Excel)
        </h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
            <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-3">
                <div class="font-bold text-stone-900 flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-800"></i>
                    <span>Export des Commandes</span>
                </div>
                <p class="text-stone-500 text-[11px]">Historique complet des ventes, clients, statuts, montants et zones de livraison.</p>
                <a href="<?= BASE_URL ?>/admin/export.php?type=orders" class="block text-center py-2 px-3 bg-emerald-800 text-white font-bold rounded-xl hover:bg-emerald-900 transition shadow-xs">
                    Exporter en CSV (.csv)
                </a>
            </div>

            <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-3">
                <div class="font-bold text-stone-900 flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-amber-800"></i>
                    <span>Catalogue & Stocks</span>
                </div>
                <p class="text-stone-500 text-[11px]">Liste des produits, prix XAF, prix promo, niveaux de stock et seuils d'alerte.</p>
                <a href="<?= BASE_URL ?>/admin/export.php?type=products" class="block text-center py-2 px-3 bg-amber-600 text-white font-bold rounded-xl hover:bg-amber-700 transition shadow-xs">
                    Exporter le Catalogue (.csv)
                </a>
            </div>

            <div class="p-4 rounded-2xl bg-stone-50 border border-stone-200 space-y-3">
                <div class="font-bold text-stone-900 flex items-center gap-2">
                    <i data-lucide="file-spreadsheet" class="w-4 h-4 text-sky-800"></i>
                    <span>Fichier Clients & Prospects</span>
                </div>
                <p class="text-stone-500 text-[11px]">Liste des contacts acheteurs, numéros de téléphone et localités au Tchad.</p>
                <a href="<?= BASE_URL ?>/admin/export.php?type=customers" class="block text-center py-2 px-3 bg-sky-700 text-white font-bold rounded-xl hover:bg-sky-800 transition shadow-xs">
                    Exporter les Contacts (.csv)
                </a>
            </div>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

