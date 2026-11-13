<?php
/**
 * Traitement de la commande en mode invité (PRD Section 6)
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

if (empty($_SESSION['cart'])) {
    echo json_encode(['success' => false, 'error' => 'Votre panier est vide.']);
    exit;
}

// Protection anti double soumission
$submitToken = $_POST['submit_token'] ?? '';
if (!empty($submitToken) && isset($_SESSION['last_submit_token']) && $_SESSION['last_submit_token'] === $submitToken) {
    echo json_encode(['success' => false, 'error' => 'Cette commande a déjà été soumise.']);
    exit;
}
$_SESSION['last_submit_token'] = $submitToken;

$clientNom = trim($_POST['client_nom'] ?? '');
$clientTelephone = trim($_POST['client_telephone'] ?? '');
$clientEmail = trim($_POST['client_email'] ?? '');
$modeReception = trim($_POST['mode_reception'] ?? 'Livraison à domicile');
$zoneId = (int)($_POST['zone_id'] ?? 0);
$adresseDetaillee = trim($_POST['adresse_detaillee'] ?? '');
$modePaiement = trim($_POST['mode_paiement'] ?? 'Paiement à la livraison');
$referencePaiement = trim($_POST['reference_paiement'] ?? '');
$notesClient = trim($_POST['notes_client'] ?? '');

// Validation
if (empty($clientNom)) {
    echo json_encode(['success' => false, 'error' => 'Veuillez saisir votre nom et prénom.']);
    exit;
}

if (empty($clientTelephone)) {
    echo json_encode(['success' => false, 'error' => 'Veuillez saisir un numéro de téléphone joignable au Tchad.']);
    exit;
}

$fraisLivraison = 0;
$zoneNom = '';

if ($modeReception === 'Retrait au point de vente ROM\'S') {
    $fraisLivraison = 0;
    $zoneNom = 'Point de retrait ROM\'S (Koumra, Mandoul)';
    if (empty($adresseDetaillee)) {
        $adresseDetaillee = 'Retrait en boutique officielle ROM\'S Natural (Koumra, Mandoul)';
    }
} else {
    // Mode Livraison : vérification obligatoire de la zone desservie (PRD Section 7.1)
    if ($zoneId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Veuillez sélectionner une zone de livraison desservie.']);
        exit;
    }

    $stmtZone = $pdo->prepare("SELECT nom_zone, tarif_xaf, actif FROM delivery_zones WHERE id = ?");
    $stmtZone->execute([$zoneId]);
    $zone = $stmtZone->fetch();

    if (!$zone || !$zone['actif']) {
        echo json_encode(['success' => false, 'error' => 'Cette zone de livraison n’est actuellement pas desservie. Contactez-nous sur WhatsApp pour une solution sur-mesure.']);
        exit;
    }

    $zoneNom = $zone['nom_zone'];
    $fraisLivraison = (float)$zone['tarif_xaf'];

    if (empty($adresseDetaillee)) {
        echo json_encode(['success' => false, 'error' => 'Veuillez préciser votre quartier, repère ou rue pour le livreur.']);
        exit;
    }
}

// Calcul du sous-total et vérification finale des stocks
$sousTotal = 0;
$itemsToOrder = [];

foreach ($_SESSION['cart'] as $item) {
    $pId = $item['product_id'];
    $vId = $item['variant_id'];
    $qty = (int)$item['quantity'];

    // Vérification stock réel
    $stmtP = $pdo->prepare("SELECT id, nom, prix, prix_promo, stock, disponible, photo_principale FROM products WHERE id = ?");
    $stmtP->execute([$pId]);
    $prod = $stmtP->fetch();

    if (!$prod || !$prod['disponible'] || $prod['stock'] < $qty) {
        echo json_encode([
            'success' => false,
            'error' => 'Le produit "' . ($prod['nom'] ?? 'Inconnu') . '" n’a plus assez de stock disponible pour honorer votre quantité.'
        ]);
        exit;
    }

    $price = ($prod['prix_promo'] > 0) ? $prod['prix_promo'] : $prod['prix'];
    $variantTitle = $item['variant_name'] ?? '';

    if ($vId) {
        $stmtV = $pdo->prepare("SELECT prix FROM product_variants WHERE id = ?");
        $stmtV->execute([$vId]);
        $vData = $stmtV->fetch();
        if ($vData && $vData['prix'] > 0) {
            $price = $vData['prix'];
        }
    }

    $lineTotal = $price * $qty;
    $sousTotal += $lineTotal;

    $itemsToOrder[] = [
        'product_id' => $pId,
        'variant_id' => $vId,
        'nom' => $prod['nom'],
        'variante' => $variantTitle,
        'prix_unitaire' => $price,
        'quantite' => $qty,
        'sous_total' => $lineTotal,
        'photo' => $prod['photo_principale']
    ];
}

$total = $sousTotal + $fraisLivraison;

// Statut de paiement initial selon PRD Section 6.4
$statutPaiement = 'En attente';
if ($modePaiement === 'Paiement à la livraison') {
    $statutPaiement = 'À encaisser à la livraison';
}

// Génération référence unique (ex: RN-2611-9872)
$reference = 'RN-' . date('y') . date('m') . '-' . strtoupper(substr(uniqid(), -4));

try {
    $pdo->beginTransaction();

    // Insertion commande
    $stmtIns = $pdo->prepare("INSERT INTO orders (
        reference, client_nom, client_telephone, client_email, zone_id, zone_nom,
        adresse_detaillee, mode_reception, mode_paiement, statut_paiement, statut_commande,
        reference_paiement, sous_total, frais_livraison, total, notes_client
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Nouvelle / reçue', ?, ?, ?, ?, ?)");

    $stmtIns->execute([
        $reference,
        $clientNom,
        $clientTelephone,
        $clientEmail,
        $zoneId ?: null,
        $zoneNom,
        $adresseDetaillee,
        $modeReception,
        $modePaiement,
        $statutPaiement,
        $referencePaiement,
        $sousTotal,
        $fraisLivraison,
        $total,
        $notesClient
    ]);

    $commandeId = $pdo->lastInsertId();

    // Insertion des lignes et décrémentation des stocks (PRD Section 9)
    $stmtItem = $pdo->prepare("INSERT INTO order_items (
        commande_id, produit_id, nom_produit, nom_variante, prix_unitaire, quantite, sous_total, photo
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

    $stmtStockDec = $pdo->prepare("UPDATE products SET stock = MAX(0, stock - ?) WHERE id = ?");
    $stmtMvt = $pdo->prepare("INSERT INTO stock_movements (produit_id, variante_id, type_mouvement, quantite, motif, utilisateur) VALUES (?, ?, 'Sortie (Vente)', ?, ?, 'Client Invité')");

    foreach ($itemsToOrder as $item) {
        $stmtItem->execute([
            $commandeId,
            $item['product_id'],
            $item['nom'],
            $item['variante'],
            $item['prix_unitaire'],
            $item['quantite'],
            $item['sous_total'],
            $item['photo']
        ]);

        // Décrémente le stock produit
        $stmtStockDec->execute([$item['quantite'], $item['product_id']]);

        // Enregistre le mouvement de stock pour traçabilité
        $stmtMvt->execute([
            $item['product_id'],
            $item['variant_id'] ?: null,
            $item['quantite'],
            'Vente commande ' . $reference
        ]);
    }

    $pdo->commit();

    // Vider le panier
    $_SESSION['cart'] = [];
    $_SESSION['last_order_reference'] = $reference;

    echo json_encode([
        'success' => true,
        'message' => 'Commande validée avec succès !',
        'reference' => $reference,
        'redirect_url' => BASE_URL . '/confirmation.php?ref=' . urlencode($reference)
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'error' => 'Une erreur est survenue lors de l’enregistrement de votre commande : ' . $e->getMessage()
    ]);
}

