<?php
/**
 * Endpoint API pour la génération de brouillons de fiches produits par IA
 */

require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../includes/ai_helper.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_admin_logged_in() || !has_permission('catalog')) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Accès refusé. Session administrateur requise.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

// Récupération des données POST
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    $data = $_POST;
}

$result = ProductAIAssistant::generateDraft($data);
echo json_encode($result);

