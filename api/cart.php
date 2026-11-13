<?php
/**
 * Gestion du Panier d'achat en session pour ROM'S NATURAL
 */

require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get';

switch ($action) {
    case 'add':
        $productId = (int)($_POST['product_id'] ?? 0);
        $variantId = !empty($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));

        if ($productId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Produit non spécifié.']);
            exit;
        }

        // Vérification produit en base
        $stmt = $pdo->prepare("SELECT id, nom, slug, prix, prix_promo, stock, disponible, photo_principale FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();

        if (!$product || !$product['disponible']) {
            echo json_encode(['success' => false, 'error' => 'Ce produit n’est plus disponible à la vente.']);
            exit;
        }

        $variantName = '';
        $unitPrice = ($product['prix_promo'] > 0) ? $product['prix_promo'] : $product['prix'];
        $availableStock = (int)$product['stock'];

        // Si une variante est choisie
        if ($variantId) {
            $stmtV = $pdo->prepare("SELECT id, titre, format_poids, prix, stock FROM product_variants WHERE id = ? AND produit_id = ?");
            $stmtV->execute([$variantId, $productId]);
            $variant = $stmtV->fetch();
            if ($variant) {
                $variantName = $variant['titre'] . ' (' . $variant['format_poids'] . ')';
                if ($variant['prix'] > 0) {
                    $unitPrice = $variant['prix'];
                }
                if ($variant['stock'] > 0) {
                    $availableStock = (int)$variant['stock'];
                }
            }
        }

        $cartKey = $productId . ($variantId ? '_' . $variantId : '');
        $currentInCart = isset($_SESSION['cart'][$cartKey]) ? $_SESSION['cart'][$cartKey]['quantity'] : 0;
        $newQty = $currentInCart + $quantity;

        if ($newQty > $availableStock) {
            echo json_encode([
                'success' => false,
                'error' => 'Stock insuffisant pour ce produit (maximum disponible : ' . $availableStock . ').'
            ]);
            exit;
        }

        $_SESSION['cart'][$cartKey] = [
            'product_id' => $productId,
            'variant_id' => $variantId,
            'name' => $product['nom'],
            'variant_name' => $variantName,
            'slug' => $product['slug'],
            'price' => (float)$unitPrice,
            'quantity' => $newQty,
            'photo' => $product['photo_principale'],
            'max_stock' => $availableStock
        ];

        echo json_encode([
            'success' => true,
            'message' => 'Produit ajouté au panier !',
            'cart_count' => getCartCount(),
            'cart_total' => getCartTotal(),
            'cart_total_formatted' => formatPrice(getCartTotal())
        ]);
        break;

    case 'update':
        $key = $_POST['key'] ?? '';
        $quantity = (int)($_POST['quantity'] ?? 1);

        if (isset($_SESSION['cart'][$key])) {
            if ($quantity <= 0) {
                unset($_SESSION['cart'][$key]);
            } else {
                $max = $_SESSION['cart'][$key]['max_stock'];
                if ($quantity > $max) {
                    echo json_encode([
                        'success' => false,
                        'error' => 'Quantité demandée supérieure au stock disponible (' . $max . ').'
                    ]);
                    exit;
                }
                $_SESSION['cart'][$key]['quantity'] = $quantity;
            }
        }

        echo json_encode([
            'success' => true,
            'cart_count' => getCartCount(),
            'cart_total' => getCartTotal(),
            'cart_total_formatted' => formatPrice(getCartTotal()),
            'items' => array_values($_SESSION['cart'])
        ]);
        break;

    case 'remove':
        $key = $_POST['key'] ?? '';
        if (isset($_SESSION['cart'][$key])) {
            unset($_SESSION['cart'][$key]);
        }

        echo json_encode([
            'success' => true,
            'message' => 'Article retiré du panier.',
            'cart_count' => getCartCount(),
            'cart_total' => getCartTotal(),
            'cart_total_formatted' => formatPrice(getCartTotal())
        ]);
        break;

    case 'clear':
        $_SESSION['cart'] = [];
        echo json_encode([
            'success' => true,
            'message' => 'Panier vidé.',
            'cart_count' => 0,
            'cart_total' => 0,
            'cart_total_formatted' => formatPrice(0)
        ]);
        break;

    case 'get':
    default:
        echo json_encode([
            'success' => true,
            'cart_count' => getCartCount(),
            'cart_total' => getCartTotal(),
            'cart_total_formatted' => formatPrice(getCartTotal()),
            'items' => array_values($_SESSION['cart'])
        ]);
        break;
}

function getCartCount() {
    $count = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

function getCartTotal() {
    $total = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += ($item['price'] * $item['quantity']);
        }
    }
    return $total;
}

