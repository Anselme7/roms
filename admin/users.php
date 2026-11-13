<?php
/**
 * Gestion des Utilisateurs & Rôles Administrateurs
 * Conforme PRD Section 12.2
 */

$adminTitle = "Utilisateurs & Rôles";
$activeMenu = "users";
require_once __DIR__ . '/header.php';
require_permission('settings');

$message = '';
$error = '';

$roles = [
    'Administrateur principal',
    'Gestionnaire de catalogue',
    'Gestionnaire des commandes',
    'Gestionnaire de stock',
    'Consultation / rapport'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $userId = (int)($_POST['user_id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $identifiant = trim($_POST['identifiant'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'Gestionnaire des commandes');
    $password = $_POST['mot_de_passe'] ?? '';

    if (empty($nom) || empty($identifiant)) {
        $error = "Le nom et l'identifiant sont obligatoires.";
    } else {
        if ($userId > 0) {
            // Modification
            if (!empty($password)) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare("UPDATE admin_users SET nom = ?, identifiant = ?, email = ?, role = ?, mot_de_passe = ? WHERE id = ?")
                    ->execute([$nom, $identifiant, $email, $role, $hash, $userId]);
            } else {
                $pdo->prepare("UPDATE admin_users SET nom = ?, identifiant = ?, email = ?, role = ? WHERE id = ?")
                    ->execute([$nom, $identifiant, $email, $role, $userId]);
            }
            $message = "Utilisateur mis à jour.";
        } else {
            // Création
            if (empty($password)) {
                $error = "Le mot de passe est obligatoire pour un nouvel utilisateur.";
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $pdo->prepare("INSERT INTO admin_users (nom, identifiant, email, role, mot_de_passe, actif) VALUES (?, ?, ?, ?, ?, 1)")
                        ->execute([$nom, $identifiant, $email, $role, $hash]);
                    $message = "Nouvel utilisateur créé avec succès.";
                } catch (Exception $e) {
                    $error = "Cet identifiant est déjà utilisé.";
                }
            }
        }
    }
}

// Activer / Désactiver
if (isset($_GET['toggle_actif'])) {
    $uId = (int)$_GET['toggle_actif'];
    if ($uId !== (int)$_SESSION['admin_user_id']) {
        $pdo->prepare("UPDATE admin_users SET actif = CASE WHEN actif = 1 THEN 0 ELSE 1 END WHERE id = ?")->execute([$uId]);
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

// Supprimer
if (isset($_GET['delete'])) {
    $uId = (int)$_GET['delete'];
    if ($uId !== (int)$_SESSION['admin_user_id']) {
        $pdo->prepare("DELETE FROM admin_users WHERE id = ?")->execute([$uId]);
    }
    header('Location: ' . BASE_URL . '/admin/users.php');
    exit;
}

$users = $pdo->query("SELECT * FROM admin_users ORDER BY id ASC")->fetchAll();

$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editUser = $stmt->fetch();
}
?>

<div class="space-y-6 max-w-6xl mx-auto">
    
    <div class="bg-white p-6 rounded-3xl border border-stone-200 shadow-sm">
        <h2 class="text-xl font-bold text-stone-900">Comptes d'Administration & Rôles</h2>
        <p class="text-xs text-stone-500 mt-1">
            Gérez les autorisations d'accès selon les 5 profils du PRD (Section 12.2).
        </p>
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

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Formulaire Utilisateur (5 cols) -->
        <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-stone-200 shadow-sm space-y-4">
            <h3 class="font-bold text-sm text-stone-900 pb-2 border-b border-stone-100">
                <?= $editUser ? 'Modifier l’utilisateur' : 'Créer un nouvel utilisateur' ?>
            </h3>

            <form method="POST" class="space-y-3 text-xs">
                <input type="hidden" name="save_user" value="1">
                <input type="hidden" name="user_id" value="<?= $editUser['id'] ?? 0 ?>">

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Nom complet *</label>
                    <input type="text" name="nom" required value="<?= htmlspecialchars($editUser['nom'] ?? '') ?>" placeholder="Ex: Awa Djibril" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                </div>

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Identifiant de connexion *</label>
                    <input type="text" name="identifiant" required value="<?= htmlspecialchars($editUser['identifiant'] ?? '') ?>" placeholder="awa" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                </div>

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Adresse Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($editUser['email'] ?? '') ?>" placeholder="awa@romsnatural.td" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                </div>

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">Rôle et Permissions (PRD 12.2) *</label>
                    <select name="role" class="w-full px-3 py-2 rounded-xl border border-stone-300 bg-white font-medium">
                        <?php foreach ($roles as $r): ?>
                            <option value="<?= $r ?>" <?= (($editUser['role'] ?? '') === $r) ? 'selected' : '' ?>><?= $r ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block font-bold text-stone-700 uppercase mb-1">
                        Mot de passe <?= $editUser ? '(Laisser vide pour ne pas changer)' : '*' ?>
                    </label>
                    <input type="password" name="mot_de_passe" <?= $editUser ? '' : 'required' ?> placeholder="••••••••" class="w-full px-3 py-2 rounded-xl border border-stone-300">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="flex-1 py-3 px-4 bg-emerald-800 hover:bg-emerald-900 text-white font-bold rounded-xl transition shadow">
                        <?= $editUser ? 'Mettre à jour' : 'Créer le compte' ?>
                    </button>
                    <?php if ($editUser): ?>
                        <a href="<?= BASE_URL ?>/admin/users.php" class="px-3 py-3 bg-stone-100 text-stone-600 font-bold rounded-xl hover:bg-stone-200">
                            Annuler
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Liste des Utilisateurs (7 cols) -->
        <div class="lg:col-span-7 bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-stone-100">
                <h3 class="font-bold text-sm text-stone-900">Utilisateurs Enregistrés</h3>
            </div>

            <div class="divide-y divide-stone-100 text-xs">
                <?php foreach ($users as $u): ?>
                    <div class="p-4 flex items-center justify-between hover:bg-stone-50/70 transition">
                        <div>
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-stone-900"><?= htmlspecialchars($u['nom']) ?></h4>
                                <span class="font-mono text-stone-400 text-[11px]">(@<?= htmlspecialchars($u['identifiant']) ?>)</span>
                            </div>
                            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-800 font-bold text-[10px]">
                                <?= htmlspecialchars($u['role']) ?>
                            </span>
                            <span class="text-stone-400 text-[10px] block mt-0.5">Dernier accès : <?= formatDateFr($u['dernier_acces']) ?></span>
                        </div>

                        <div class="flex items-center gap-2">
                            <?php if ($u['id'] !== (int)$_SESSION['admin_user_id']): ?>
                                <a href="<?= BASE_URL ?>/admin/users.php?toggle_actif=<?= $u['id'] ?>" class="text-[11px] font-bold px-2 py-1 rounded-full border <?= $u['actif'] ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-rose-50 text-rose-700 border-rose-200' ?>">
                                    <?= $u['actif'] ? 'Actif' : 'Inactif' ?>
                                </a>
                            <?php else: ?>
                                <span class="text-[10px] text-stone-400 font-bold px-2 py-1">Vous</span>
                            <?php endif; ?>

                            <a href="<?= BASE_URL ?>/admin/users.php?edit=<?= $u['id'] ?>" class="p-1.5 rounded-lg bg-stone-100 hover:bg-emerald-800 hover:text-white transition text-stone-700">
                                <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                            </a>

                            <?php if ($u['id'] !== (int)$_SESSION['admin_user_id']): ?>
                                <a href="<?= BASE_URL ?>/admin/users.php?delete=<?= $u['id'] ?>" class="p-1.5 rounded-lg bg-stone-100 hover:bg-rose-600 hover:text-white transition text-stone-700" onclick="return confirm('Supprimer cet utilisateur ?')">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>

</div>

<?php require_once __DIR__ . '/footer.php'; ?>

