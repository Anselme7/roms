<?php
/**
 * Page d'accès refusé (Droits insuffisants selon rôle)
 */
require_once __DIR__ . '/../config/settings.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accès Non Autorisé | ROM'S Natural Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-100 flex items-center justify-center min-h-screen p-4">
    <div class="bg-white p-8 rounded-3xl border border-stone-200 max-w-md w-full text-center space-y-4 shadow-xl">
        <div class="w-16 h-16 bg-rose-100 text-rose-700 rounded-full flex items-center justify-center mx-auto text-2xl font-bold">
            ⚠️
        </div>
        <h1 class="text-xl font-black text-stone-900">Accès Restreint</h1>
        <p class="text-xs text-stone-600 leading-relaxed">
            Votre profil administrateur ne dispose pas des permissions requises pour accéder à ce module (PRD Section 12.2).
        </p>
        <a href="<?= BASE_URL ?>/admin/index.php" class="inline-block px-5 py-2.5 bg-emerald-800 text-white font-bold text-xs rounded-xl hover:bg-emerald-900 transition">
            Retour au Tableau de Bord
        </a>
    </div>
</body>
</html>

