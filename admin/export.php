<?php
/**
 * Exporteur CSV pour ROM'S NATURAL
 * Conforme PRD Section 13.1 (Export des données)
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../config/auth.php';

require_admin_login();

$type = $_GET['type'] ?? 'orders';
$filename = "export_" . $type . "_" . date('Ymd_His') . ".csv";

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$output = fopen('php://output', 'w');

// BOM UTF-8 pour Excel
fputs($output, "\xEF\xBB\xBF");

if ($type === 'products') {
    // En-têtes CSV Produits
    fputcsv($output, ['ID', 'Nom du produit', 'Catégorie', 'Prix (XAF)', 'Prix Promo (XAF)', 'Stock actuel', 'Seuil alerte', 'Disponible', 'En Vedette', 'Origine'], ';');

    $stmt = $pdo->query("SELECT p.id, p.nom, c.nom as cat_nom, p.prix, p.prix_promo, p.stock, p.seuil_alerte, p.disponible, p.en_vedette, p.origine 
        FROM products p 
        LEFT JOIN categories c ON p.categorie_id = c.id 
        ORDER BY p.id ASC");

    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['id'],
            $row['nom'],
            $row['cat_nom'] ?? 'Non classé',
            $row['prix'],
            $row['prix_promo'] ?: '',
            $row['stock'],
            $row['seuil_alerte'],
            $row['disponible'] ? 'Oui' : 'Non',
            $row['en_vedette'] ? 'Oui' : 'Non',
            $row['origine']
        ], ';');
    }

} elseif ($type === 'customers') {
    // En-têtes CSV Clients
    fputcsv($output, ['Nom Client', 'Téléphone', 'Email', 'Localité / Zone habituelle', 'Nombre de Commandes', 'Total Dépensé (XAF)'], ';');

    $stmt = $pdo->query("SELECT client_nom, client_telephone, client_email, zone_nom, COUNT(id) as total_commandes, SUM(total) as total_depense 
        FROM orders 
        GROUP BY client_telephone 
        ORDER BY total_depense DESC");

    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['client_nom'],
            $row['client_telephone'],
            $row['client_email'] ?: '-',
            $row['zone_nom'],
            $row['total_commandes'],
            $row['total_depense']
        ], ';');
    }

} else {
    // En-têtes CSV Commandes
    fputcsv($output, ['Référence', 'Date', 'Nom Client', 'Téléphone', 'Email', 'Mode Réception', 'Zone', 'Adresse', 'Mode Paiement', 'Statut Paiement', 'Statut Commande', 'Total (XAF)', 'Livreur'], ';');

    $stmt = $pdo->query("SELECT reference, date_creation, client_nom, client_telephone, client_email, mode_reception, zone_nom, adresse_detaillee, mode_paiement, statut_paiement, statut_commande, total, livreur_nom 
        FROM orders 
        ORDER BY id DESC");

    while ($row = $stmt->fetch()) {
        fputcsv($output, [
            $row['reference'],
            $row['date_creation'],
            $row['client_nom'],
            $row['client_telephone'],
            $row['client_email'] ?: '',
            $row['mode_reception'],
            $row['zone_nom'],
            $row['adresse_detaillee'],
            $row['mode_paiement'],
            $row['statut_paiement'],
            $row['statut_commande'],
            $row['total'],
            $row['livreur_nom'] ?: '-'
        ], ';');
    }
}

fclose($output);
exit;

