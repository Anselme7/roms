<?php
/**
 * Générateur SVG dynamique pour les fiches produits sans image uploadée
 */
header('Content-Type: image/svg+xml; charset=utf-8');

$cat = $_GET['cat'] ?? 'cosmetiques';
$name = $_GET['name'] ?? 'ROM\'S NATURAL';

// Couleurs thématiques
$bgColors = [
    'cosmetiques-naturels' => ['#1b4332', '#2d6a4f', '#d8f3dc'],
    'alimentaires-transformes' => ['#7c2d12', '#c2410c', '#ffedd5'],
    'produits-artisanaux' => ['#78350f', '#b45309', '#fef3c7'],
    'agricoles-transformes' => ['#14532d', '#15803d', '#dcfce7'],
    'autres-produits' => ['#1e293b', '#334155', '#f1f5f9']
];

$theme = $bgColors[$cat] ?? ['#1b4332', '#2d6a4f', '#d8f3dc'];
$c1 = $theme[0];
$c2 = $theme[1];
$c3 = $theme[2];

$cleanName = htmlspecialchars(mb_strimwidth(basename($name, '.jpg'), 0, 30, '...'));
?>
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 600 600" width="600" height="600">
  <defs>
    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="<?= $c1 ?>" />
      <stop offset="100%" stop-color="<?= $c2 ?>" />
    </linearGradient>
    <filter id="shadow" x="-10%" y="-10%" width="120%" height="120%">
      <feDropShadow dx="0" dy="8" stdDeviation="12" flood-color="#000" flood-opacity="0.25"/>
    </filter>
  </defs>
  
  <rect width="600" height="600" fill="url(#grad)" />
  
  <!-- Motif décoratif sahélien géométrique -->
  <g opacity="0.08" stroke="#ffffff" stroke-width="2" fill="none">
    <circle cx="300" cy="300" r="260" />
    <circle cx="300" cy="300" r="210" stroke-dasharray="8 8" />
    <circle cx="300" cy="300" r="160" />
    <polygon points="300,50 550,470 50,470" />
    <polygon points="300,550 550,130 50,130" />
  </g>
  
  <!-- Carte centrale -->
  <circle cx="300" cy="270" r="115" fill="#ffffff" filter="url(#shadow)" opacity="0.95" />
  
  <!-- Icône naturelle / feuille / goutte -->
  <g transform="translate(255, 225) scale(1.5)" fill="<?= $c1 ?>">
    <path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" opacity="0.15" />
    <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z" fill="<?= $c1 ?>"/>
    <path d="M12 6v10" stroke="#ffffff" stroke-width="1.5" stroke-linecap="round"/>
  </g>
  
  <!-- Badge ROM'S NATURAL -->
  <rect x="180" y="420" width="240" height="34" rx="17" fill="#ffffff" opacity="0.9" />
  <text x="300" y="442" font-family="system-ui, -apple-system, sans-serif" font-size="13" font-weight="800" letter-spacing="2" fill="<?= $c1 ?>" text-anchor="middle">ROM'S NATURAL • TCHAD</text>
  
  <!-- Nom du produit -->
  <text x="300" y="485" font-family="system-ui, -apple-system, sans-serif" font-size="18" font-weight="600" fill="#ffffff" text-anchor="middle"><?= $cleanName ?></text>
  <text x="300" y="515" font-family="system-ui, -apple-system, sans-serif" font-size="12" fill="<?= $c3 ?>" text-anchor="middle" letter-spacing="1">PRODUIT LOCAL TRANSFORMÉ • 100% AUTHENTIQUE</text>
</svg>

