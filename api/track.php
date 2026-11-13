<?php
/**
 * Recherche et suivi de commande public (PRD Section 5.1 & 7.3)
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$ref = trim($_GET['ref'] ?? $_POST['ref'] ?? '');
$phone = trim($_GET['phone'] ?? $_POST['phone'] ?? '');

if (empty($ref) && empty($phone)) {
    echo json_encode(['success' => false, 'error' => 'Veuillez saisir votre référence de commande ou votre numéro de téléphone.']);
    exit;
}

if (!empty($ref)) {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE reference = ?");
    $stmt->execute([$ref]);
    $order = $stmt->fetch();
} else {
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE client_telephone LIKE ? ORDER BY id DESC LIMIT 1");
    $stmt->execute(['%' . $cleanPhone . '%']);
    $order = $stmt->fetch();
}

if (!$order) {
    echo json_encode(['success' => false, 'error' => 'Aucune commande trouvée avec ces identifiants.']);
    exit;
}

// Lignes de commande
$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE commande_id = ?");
$stmtItems->execute([$order['id']]);
$items = $stmtItems->fetchAll();

echo json_encode([
    'success' => true,
    'order' => [
        'reference' => $order['reference'],
        'client_nom' => $order['client_nom'],
        'date_creation' => formatDateFr($order['date_creation']),
        'statut_commande' => $order['statut_commande'],
        'statut_badge' => orderStatusBadge($order['statut_commande']),
        'statut_paiement' => $order['statut_paiement'],
        'payment_badge' => paymentStatusBadge($order['statut_paiement']),
        'mode_reception' => $order['mode_reception'],
        'zone_nom' => $order['zone_nom'],
        'adresse_detaillee' => $order['adresse_detaillee'],
        'livreur_nom' => $order['livreur_nom'] ?? 'Non assigné pour l’instant',
        'sous_total' => formatPrice($order['sous_total']),
        'frais_livraison' => formatPrice($order['frais_livraison']),
        'total' => formatPrice($order['total']),
        'items' => array_map(function($i) {
            return [
                'nom_produit' => $i['nom_produit'],
                'nom_variante' => $i['nom_variante'],
                'quantite' => $i['quantite'],
                'prix_unitaire' => formatPrice($i['prix_unitaire']),
                'sous_total' => formatPrice($i['sous_total'])
            ];
        }, $items)
    ]
]);

