<?php
/**
 * Fonctions utilitaires pour ROM'S NATURAL
 */

require_once __DIR__ . '/../config/settings.php';

/**
 * Formatage des prix en Franc CFA (XAF)
 */
function formatPrice($amount) {
    if ($amount === null || $amount === '') return '0 FCFA';
    return number_format((float)$amount, 0, ',', ' ') . ' FCFA';
}

/**
 * Formatage des dates au format français
 */
function formatDateFr($datetime, $withHour = true) {
    if (!$datetime) return '-';
    $timestamp = strtotime($datetime);
    if (!$timestamp) return '-';
    if ($withHour) {
        return date('d/m/Y à H:i', $timestamp);
    }
    return date('d/m/Y', $timestamp);
}

/**
 * Badge de statut de commande conforme PRD (Section 8.1)
 */
function orderStatusBadge($status) {
    $colors = [
        'Nouvelle / reçue' => 'bg-blue-100 text-blue-800 border-blue-200',
        'Confirmée' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
        'En préparation' => 'bg-amber-100 text-amber-800 border-amber-200',
        'Prête' => 'bg-purple-100 text-purple-800 border-purple-200',
        'En livraison' => 'bg-sky-100 text-sky-800 border-sky-200',
        'Terminée' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'Annulée' => 'bg-rose-100 text-rose-800 border-rose-200',
        'En attente' => 'bg-orange-100 text-orange-800 border-orange-200',
    ];

    $cls = $colors[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border ' . $cls . '">' . htmlspecialchars($status) . '</span>';
}

/**
 * Badge de statut de paiement conforme PRD (Section 6.4)
 */
function paymentStatusBadge($status) {
    $colors = [
        'En attente' => 'bg-amber-100 text-amber-800 border-amber-200',
        'À encaisser à la livraison' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'Payé' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
        'Échec ou rejeté' => 'bg-rose-100 text-rose-800 border-rose-200',
        'Remboursé' => 'bg-gray-100 text-gray-800 border-gray-200',
    ];

    $cls = $colors[$status] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border ' . $cls . '">' . htmlspecialchars($status) . '</span>';
}

/**
 * Génère le lien WhatsApp officiel prérempli (PRD Section 11)
 */
function getWhatsAppProductLink($productName, $productId = null) {
    $phone = getSetting('phone_whatsapp', '23566280000');
    // Nettoyer le numéro (chiffres uniquement)
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    $text = "Bonjour ROM'S Natural, je suis intéressé(e) par votre produit : *" . $productName . "*";
    if ($productId) {
        $text .= " (Réf: #" . $productId . ")";
    }
    $text .= ". Pouvez-vous me donner plus de détails ?";
    return "https://wa.me/" . $cleanPhone . "?text=" . urlencode($text);
}

function getWhatsAppOrderLink($orderRef, $total, $clientName = '') {
    $phone = getSetting('phone_whatsapp', '23566280000');
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    $text = "Bonjour ROM'S Natural, je vous contacte concernant ma commande *" . $orderRef . "* d'un montant de *" . formatPrice($total) . "*";
    if (!empty($clientName)) {
        $text .= " au nom de " . $clientName;
    }
    $text .= ". Merci de m'indiquer son état d'avancement.";
    return "https://wa.me/" . $cleanPhone . "?text=" . urlencode($text);
}

function getWhatsAppAdminToCustomerLink($customerPhone, $orderRef, $status, $clientName) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $customerPhone);
    if (strlen($cleanPhone) === 8 && !str_starts_with($cleanPhone, '235')) {
        $cleanPhone = '235' . $cleanPhone; // Indicatif Tchad par défaut
    }
    $text = "Bonjour " . $clientName . ",\n\nIci l'équipe *ROM'S Natural* au Tchad concernant votre commande *" . $orderRef . "*.\nVotre commande est actuellement : *" . $status . "*.\n\nNous restons à votre entière disposition pour tout renseignement.";
    return "https://wa.me/" . $cleanPhone . "?text=" . urlencode($text);
}

/**
 * Slugification pour URLs propres
 */
function slugify($text) {
    $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    return trim($text, '-');
}

/**
 * Image helper : renvoie l'URL de l'image ou un SVG personnalisé de haute qualité
 */
function getProductImageUrl($filename, $categorySlug = 'cosmetiques-naturels') {
    if (!empty($filename) && file_exists(ROM_ROOT . '/assets/images/products/' . $filename)) {
        return BASE_URL . '/assets/images/products/' . htmlspecialchars($filename);
    }
    // Générateur d'illustration SVG stylisée aux couleurs ROM'S Natural
    return BASE_URL . '/assets/images/placeholder.php?cat=' . urlencode($categorySlug) . '&name=' . urlencode($filename ?? 'Produit');
}

