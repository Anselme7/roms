<?php
/**
 * Paramètres généraux et helpers de configuration pour ROM'S NATURAL
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/database.php';

// Chargement des paramètres depuis la table settings
function getSetting($key, $default = '') {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT valeur FROM settings WHERE cle = ?");
        $stmt->execute([$key]);
        $val = $stmt->fetchColumn();
        return ($val !== false) ? $val : $default;
    } catch (Exception $e) {
        return $default;
    }
}

function updateSetting($key, $val) {
    global $pdo;
    $stmt = $pdo->prepare("INSERT OR REPLACE INTO settings (cle, valeur) VALUES (?, ?)");
    return $stmt->execute([$key, $val]);
}

// Détection du chemin de base dynamique (ex: /roms ou '')
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = !empty($scriptName) ? str_replace('\\', '/', dirname($scriptName)) : '';
$basePath = preg_replace('/(\/admin|\/api|\/includes)$/', '', $scriptDir);
if ($basePath === '' || $basePath === '/' || $basePath === '.') {
    $baseUri = '';
} else {
    $baseUri = rtrim($basePath, '/');
}
define('BASE_URL', $baseUri);

define('SITE_NAME', getSetting('site_name', "ROM'S NATURAL"));
define('CURRENCY', 'XAF');
define('CURRENCY_SYMBOL', 'FCFA');

