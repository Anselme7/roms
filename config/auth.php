<?php
/**
 * Gestion de l'authentification et des autorisations (PRD Section 12)
 */

require_once __DIR__ . '/settings.php';

// Génération de jeton CSRF
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die("Erreur de sécurité : Jeton CSRF invalide ou expiré.");
        }
    }
}

// Vérifie si un administrateur est connecté
function is_admin_logged_in() {
    return !empty($_SESSION['admin_user_id']);
}

// Récupère l'utilisateur admin courant
function current_admin_user() {
    global $pdo;
    if (!is_admin_logged_in()) {
        return null;
    }
    $stmt = $pdo->prepare("SELECT id, nom, identifiant, email, role, dernier_acces FROM admin_users WHERE id = ? AND actif = 1");
    $stmt->execute([$_SESSION['admin_user_id']]);
    return $stmt->fetch();
}

// Exige d'être connecté comme admin
function require_admin_login() {
    if (!is_admin_logged_in()) {
        header('Location: ' . BASE_URL . '/admin/login.php');
        exit;
    }
}

// Vérification des droits selon les rôles du PRD (Section 12.2)
function has_permission($module) {
    if (!is_admin_logged_in()) return false;
    $role = $_SESSION['admin_role'] ?? '';

    // L'administrateur principal a tous les droits
    if ($role === 'Administrateur principal') return true;

    switch ($module) {
        case 'catalog': // Produits, catégories
            return in_array($role, ['Administrateur principal', 'Gestionnaire de catalogue']);
        case 'orders': // Commandes, livraisons, paiements
            return in_array($role, ['Administrateur principal', 'Gestionnaire des commandes']);
        case 'stock': // Stocks, mouvements, lots
            return in_array($role, ['Administrateur principal', 'Gestionnaire de stock']);
        case 'reports': // Consultation dashboard et rapports
            return in_array($role, ['Administrateur principal', 'Gestionnaire des commandes', 'Consultation / rapport']);
        case 'settings': // Paramètres généraux, utilisateurs
            return ($role === 'Administrateur principal');
        default:
            return false;
    }
}

function require_permission($module) {
    require_admin_login();
    if (!has_permission($module)) {
        http_response_code(403);
        include __DIR__ . '/../admin/unauthorized.php';
        exit;
    }
}

