<?php
/**
 * Connexion Administration - ROM'S NATURAL
 * Conforme PRD Section 12 & 14.2
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth.php';

if (is_admin_logged_in()) {
    header('Location: ' . BASE_URL . '/admin/index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = trim($_POST['identifiant'] ?? '');
    $password = $_POST['mot_de_passe'] ?? '';

    if (empty($identifiant) || empty($password)) {
        $error = 'Veuillez saisir votre identifiant et votre mot de passe.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE (identifiant = ? OR email = ?) AND actif = 1");
        $stmt->execute([$identifiant, $identifiant]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            // Connexion réussie
            $_SESSION['admin_user_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['nom'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_login'] = $user['identifiant'];

            // Mise à jour date dernier accès
            $pdo->prepare("UPDATE admin_users SET dernier_acces = CURRENT_TIMESTAMP WHERE id = ?")->execute([$user['id']]);

            header('Location: ' . BASE_URL . '/admin/index.php');
            exit;
        } else {
            $error = 'Identifiant ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="h-full bg-stone-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Administration | ROM'S NATURAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="flex items-center justify-center min-h-full p-4">

    <div class="max-w-md w-full bg-white rounded-3xl border border-stone-200 shadow-2xl p-8 space-y-6">
        
        <div class="text-center space-y-3">
            <div class="w-20 h-20 bg-black rounded-3xl border border-amber-500/40 p-1 flex items-center justify-center mx-auto shadow-xl overflow-hidden">
                <img src="<?= BASE_URL ?>/assets/images/logo.png" alt="ROM'S NATURAL" class="w-full h-full object-contain">
            </div>
            <div>
                <h1 class="text-2xl font-black text-stone-900 font-serif">ROM'S NATURAL</h1>
                <p class="text-xs text-amber-700 font-bold uppercase tracking-wider">Koumra (Mandoul) • Administration</p>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 text-rose-600 flex-shrink-0"></i>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Identifiant ou Email</label>
                <div class="relative">
                    <input type="text" name="identifiant" required autofocus placeholder="admin" value="admin"
                           class="w-full pl-10 pr-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700 font-medium">
                    <i data-lucide="user" class="w-4 h-4 text-stone-400 absolute left-3.5 top-3.5"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-stone-700 uppercase mb-1">Mot de passe</label>
                <div class="relative">
                    <input type="password" name="mot_de_passe" required placeholder="••••••••" value="admin123"
                           class="w-full pl-10 pr-4 py-3 text-sm rounded-xl border border-stone-300 focus:outline-none focus:border-emerald-700">
                    <i data-lucide="lock" class="w-4 h-4 text-stone-400 absolute left-3.5 top-3.5"></i>
                </div>
            </div>

            <button type="submit" class="w-full py-3.5 px-6 bg-emerald-900 hover:bg-emerald-950 text-amber-300 font-extrabold text-xs uppercase tracking-wider rounded-xl transition shadow-lg flex items-center justify-center gap-2">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Se connecter</span>
            </button>
        </form>

        <div class="pt-4 border-t border-stone-100 text-center space-y-2 text-xs text-stone-400">
            <p>Compte par défaut pré-configuré : <strong class="text-stone-700">admin</strong> / <strong class="text-stone-700">admin123</strong></p>
            <a href="<?= BASE_URL ?>/index.php" class="text-emerald-800 hover:underline font-semibold block">
                ← Retourner sur la boutique publique
            </a>
        </div>

    </div>

    <script>
        if (window.lucide) window.lucide.createIcons();
    </script>
</body>
</html>

