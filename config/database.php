<?php
/**
 * Configuration de la Base de Données pour ROM'S NATURAL
 * Support natif SQLite (zéro configuration requise sur XAMPP)
 * avec possibilité de bascule vers MySQL si besoin.
 */

if (!defined('ROM_ROOT')) {
    define('ROM_ROOT', dirname(__DIR__));
}

$dbDir = ROM_ROOT . '/data';
if (!is_dir($dbDir)) {
    @mkdir($dbDir, 0777, true);
}

$sqliteFile = $dbDir . '/roms_database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $sqliteFile, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5
    ]);
    // Active les clés étrangères sous SQLite
    $pdo->exec("PRAGMA foreign_keys = ON;");
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

/**
 * Initialisation automatique des tables et données par défaut
 */
function initDatabase($pdo) {
    // 1. Table Utilisateurs Administrateurs
    $pdo->exec("CREATE TABLE IF NOT EXISTS admin_users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        identifiant TEXT UNIQUE NOT NULL,
        email TEXT,
        mot_de_passe TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'Administrateur principal',
        actif INTEGER NOT NULL DEFAULT 1,
        dernier_acces DATETIME,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 2. Table Catégories
    $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        description TEXT,
        image TEXT,
        parent_id INTEGER DEFAULT 0,
        ordre INTEGER DEFAULT 0,
        statut INTEGER DEFAULT 1
    );");

    // 3. Table Produits
    $pdo->exec("CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        categorie_id INTEGER,
        description_courte TEXT,
        description TEXT,
        prix REAL NOT NULL DEFAULT 0,
        prix_promo REAL DEFAULT NULL,
        photo_principale TEXT,
        galerie TEXT,
        disponible INTEGER DEFAULT 1,
        en_vedette INTEGER DEFAULT 0,
        nouveaute INTEGER DEFAULT 0,
        stock INTEGER DEFAULT 0,
        seuil_alerte INTEGER DEFAULT 5,
        composition TEXT,
        origine TEXT,
        conseils_utilisation TEXT,
        precautions TEXT,
        conservation TEXT,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_modification DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE SET NULL
    );");

    // 4. Table Variantes de Produits
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_variants (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        produit_id INTEGER NOT NULL,
        titre TEXT NOT NULL,
        format_poids TEXT,
        prix REAL DEFAULT NULL,
        stock INTEGER DEFAULT 0,
        FOREIGN KEY (produit_id) REFERENCES products(id) ON DELETE CASCADE
    );");

    // 5. Table Lots de fabrication et DLUO / Péremption
    $pdo->exec("CREATE TABLE IF NOT EXISTS product_batches (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        produit_id INTEGER NOT NULL,
        variante_id INTEGER DEFAULT NULL,
        numero_lot TEXT NOT NULL,
        quantite INTEGER NOT NULL DEFAULT 0,
        date_fabrication DATE,
        date_limite DATE,
        notes TEXT,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (produit_id) REFERENCES products(id) ON DELETE CASCADE
    );");

    // 6. Table Zones de Livraison au Tchad
    $pdo->exec("CREATE TABLE IF NOT EXISTS delivery_zones (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom_zone TEXT NOT NULL,
        region TEXT DEFAULT 'Tchad',
        tarif_xaf REAL NOT NULL DEFAULT 0,
        delai_estime TEXT NOT NULL,
        conditions TEXT,
        actif INTEGER DEFAULT 1,
        ordre INTEGER DEFAULT 0
    );");

    // 7. Table Commandes
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        reference TEXT UNIQUE NOT NULL,
        client_nom TEXT NOT NULL,
        client_telephone TEXT NOT NULL,
        client_email TEXT,
        zone_id INTEGER,
        zone_nom TEXT,
        adresse_detaillee TEXT NOT NULL,
        mode_reception TEXT NOT NULL DEFAULT 'Livraison',
        mode_paiement TEXT NOT NULL DEFAULT 'Paiement à la livraison',
        statut_paiement TEXT NOT NULL DEFAULT 'À encaisser à la livraison',
        statut_commande TEXT NOT NULL DEFAULT 'Nouvelle / reçue',
        reference_paiement TEXT,
        sous_total REAL NOT NULL DEFAULT 0,
        frais_livraison REAL NOT NULL DEFAULT 0,
        total REAL NOT NULL DEFAULT 0,
        notes_client TEXT,
        notes_internes TEXT,
        motif_annulation TEXT,
        livreur_nom TEXT,
        date_paiement DATETIME,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_mise_a_jour DATETIME DEFAULT CURRENT_TIMESTAMP
    );");

    // 8. Table Lignes de commande
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        commande_id INTEGER NOT NULL,
        produit_id INTEGER,
        nom_produit TEXT NOT NULL,
        nom_variante TEXT,
        prix_unitaire REAL NOT NULL,
        quantite INTEGER NOT NULL,
        sous_total REAL NOT NULL,
        photo TEXT,
        FOREIGN KEY (commande_id) REFERENCES orders(id) ON DELETE CASCADE
    );");

    // 9. Table Mouvements de stock
    $pdo->exec("CREATE TABLE IF NOT EXISTS stock_movements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        produit_id INTEGER NOT NULL,
        variante_id INTEGER,
        lot_id INTEGER,
        type_mouvement TEXT NOT NULL, /* Entrée, Sortie, Vente, Correction */
        quantite INTEGER NOT NULL,
        motif TEXT,
        utilisateur TEXT,
        date_mouvement DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (produit_id) REFERENCES products(id) ON DELETE CASCADE
    );");

    // 10. Table Paramètres Généraux
    $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
        cle TEXT PRIMARY KEY,
        valeur TEXT
    );");

    // Vérifier si des données initiales doivent être insérées
    $checkUser = $pdo->query("SELECT COUNT(*) FROM admin_users")->fetchColumn();
    if ($checkUser == 0) {
        seedInitialData($pdo);
    }
}

/**
 * Insertion des données par défaut et démo réalistes pour ROM'S NATURAL
 */
function seedInitialData($pdo) {
    // 1. Utilisateur Admin par défaut (Mot de passe: admin123)
    $stmt = $pdo->prepare("INSERT INTO admin_users (nom, identifiant, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        'Administrateur ROMS',
        'admin',
        'direction@romsnatural.td',
        password_hash('admin123', PASSWORD_DEFAULT),
        'Administrateur principal'
    ]);

    // 2. Paramètres généraux de la boutique
    $settings = [
        'site_name' => "ROM'S NATURAL",
        'site_slogan' => "Produits locaux transformés au Tchad - 100% Naturel & Authentique",
        'site_description' => "Plateforme officielle e-commerce de ROM'S Natural : cosmétiques naturels, miels, sirops artisanaux, farines nutritives et trésors issus des terroirs tchadiens.",
        'currency' => 'XAF',
        'currency_symbol' => 'FCFA',
        'phone_contact' => '+235 66 28 00 00',
        'phone_whatsapp' => '23566280000',
        'email_contact' => 'anoubaissem7@gmail.com',
        'store_address' => "Boutique & Atelier ROM'S Natural, Quartier Representant 1, Koumra, Province du Mandoul, Tchad",
        'opening_hours' => 'Lundi au Samedi : 08h00 - 18h30',
        'payment_cod_enabled' => '1',
        'payment_airtel_enabled' => '1',
        'payment_airtel_number' => '+235 66 28 00 00 (*436#)',
        'payment_moov_enabled' => '1',
        'payment_moov_number' => '+235 99 28 00 00 (*156#)',
        'payment_bank_enabled' => '1',
        'payment_bank_details' => "Banque Commerciale du Chari (BCC)\nTitulaire : ROM'S NATURAL SARL\nNuméro de Compte : 00234-118940-01\nAgence : N'Djamena Siège",
        'gemini_api_key' => '',
        'pickup_point_address' => "Boutique ROM'S Natural, Chagoua face station Total, N'Djamena"
    ];

    $stmtSet = $pdo->prepare("INSERT OR REPLACE INTO settings (cle, valeur) VALUES (?, ?)");
    foreach ($settings as $k => $v) {
        $stmtSet->execute([$k, $v]);
    }

    // 3. Les 4 familles de produits + 1 extensible selon PRD
    $categories = [
        [
            'id' => 1,
            'nom' => 'Cosmétiques naturels',
            'slug' => 'cosmetiques-naturels',
            'description' => 'Beurres de karité purs, huiles végétales locales de Balanites, pommades capillaires au Chébé, savons artisanaux doux.',
            'ordre' => 1
        ],
        [
            'id' => 2,
            'nom' => 'Produits alimentaires transformés',
            'slug' => 'alimentaires-transformes',
            'description' => 'Sirops artisanaux d’hibiscus (Karkadé), miels purs du Guéra, poudres nutritives de Baobab et farines locales.',
            'ordre' => 2
        ],
        [
            'id' => 3,
            'nom' => 'Produits artisanaux',
            'slug' => 'produits-artisanaux',
            'description' => 'Vannerie traditionnelle tchadienne, trousses de toilette en toile locale et accessoires fabriqués artisanalement.',
            'ordre' => 3
        ],
        [
            'id' => 4,
            'nom' => 'Produits agricoles transformés',
            'slug' => 'agricoles-transformes',
            'description' => 'Sésame blanc purifié, huile d’arachide vierge pressée à froid, moringa et épices valorisées des terroirs tchadiens.',
            'ordre' => 4
        ],
        [
            'id' => 5,
            'nom' => 'Autres produits locaux',
            'slug' => 'autres-produits',
            'description' => 'Sélections spéciales et nouveautés transformées issues des provinces du Tchad.',
            'ordre' => 5
        ]
    ];

    $stmtCat = $pdo->prepare("INSERT INTO categories (id, nom, slug, description, ordre, statut) VALUES (?, ?, ?, ?, ?, 1)");
    foreach ($categories as $cat) {
        $stmtCat->execute([$cat['id'], $cat['nom'], $cat['slug'], $cat['description'], $cat['ordre']]);
    }

    // 4. Zones de livraison au Tchad (PRD Section 7)
    $zones = [
        [
            'nom' => "N'Djamena - Centre & Quartiers Résidentiels (Sabangali, Klemat, Amriguébé, Chagoua)",
            'region' => "N'Djamena",
            'tarif' => 1500,
            'delai' => '2h à 4h ouvrées',
            'conditions' => 'Livraison directe à moto à votre domicile ou bureau.',
            'ordre' => 1
        ],
        [
            'nom' => "N'Djamena - Périphérie & Banlieues (Farcha, Walia, Toukra, Diguel, Lamadji)",
            'region' => "N'Djamena",
            'tarif' => 2500,
            'delai' => 'Dans la journée (avant 18h)',
            'conditions' => 'Livraison à domicile avec contact préalable du livreur.',
            'ordre' => 2
        ],
        [
            'nom' => "Moundou (Logone Occidental) - Retrait en agence partenaire",
            'region' => 'Sud',
            'tarif' => 4000,
            'delai' => '24h à 48h',
            'conditions' => 'Expédition sécurisée par bus express avec retrait au guichet transporteur.',
            'ordre' => 3
        ],
        [
            'nom' => "Sarh (Moyen-Chari) - Retrait en agence partenaire",
            'region' => 'Sud',
            'tarif' => 4500,
            'delai' => '24h à 48h',
            'conditions' => 'Envoi par transporteur express fiable et suivi SMS dès réception.',
            'ordre' => 4
        ],
        [
            'nom' => "Abéché (Ouaddaï) - Retrait en agence partenaire",
            'region' => 'Est',
            'tarif' => 5500,
            'delai' => '48h à 72h',
            'conditions' => 'Expédition sécurisée avec emballage renforcé anti-chocs.',
            'ordre' => 5
        ],
        [
            'nom' => "Kélo & Pala (Mayo-Kebbi) - Retrait en point relais",
            'region' => 'Sud-Ouest',
            'tarif' => 5000,
            'delai' => '48h',
            'conditions' => 'Retrait à l’agence partenaire du centre-ville.',
            'ordre' => 6
        ]
    ];

    $stmtZone = $pdo->prepare("INSERT INTO delivery_zones (nom_zone, region, tarif_xaf, delai_estime, conditions, actif, ordre) VALUES (?, ?, ?, ?, ?, 1, ?)");
    foreach ($zones as $z) {
        $stmtZone->execute([$z['nom'], $z['region'], $z['tarif'], $z['delai'], $z['conditions'], $z['ordre']]);
    }

    // 5. Produits réalistes initiaux
    $products = [
        [
            'nom' => 'Beurre de Karité Pur Brut du Mandoul (250g)',
            'slug' => 'beurre-de-karite-pur-brut-mandoul-250g',
            'categorie_id' => 1,
            'description_courte' => 'Beurre de karité 100% pur, extrait traditionnellement par les groupements de femmes du Mandoul. Sans parfum ni conservateur.',
            'description' => "Le Beurre de Karité de ROM'S Natural est issu d'une transformation artisanale minutieuse préservant toutes ses vitamines A, D, E et F. Idéal pour nourrir en profondeur les peaux sèches, réparer les pointes abîmées des cheveux crépus et bouclés, et protéger la peau contre les vents desséchants du Sahel. Un soin noble et authentique directement extrait de la flore tchadienne.",
            'prix' => 3500,
            'prix_promo' => null,
            'stock' => 45,
            'seuil_alerte' => 10,
            'en_vedette' => 1,
            'nouveaute' => 1,
            'composition' => '100% Butyrospermum Parkii Butter (Beurre de karité brut pur, première pression traditionnelle).',
            'origine' => 'Province du Mandoul, Tchad - Récolte sauvage et transformation coopérative locale.',
            'conseils_utilisation' => 'Chauffer une noisette au creux de la main avant application sur le visage, le corps ou les longueurs de vos cheveux. Utilisation quotidienne matin et soir.',
            'precautions' => 'Usage externe uniquement. Conserver à température ambiante à l’abri du soleil direct.',
            'conservation' => '24 mois après fabrication dans un endroit sec et tempéré.',
            'photo' => 'karite-brut.jpg'
        ],
        [
            'nom' => 'Pommade Capillaire Fortifiante au Chébé Authentique (200ml)',
            'slug' => 'pommade-capillaire-chebe-authentique-200ml',
            'categorie_id' => 1,
            'description_courte' => 'Formule ancestrale des femmes Bassara du Tchad pour retenir la longueur, limiter la casse et assouplir la fibre capillaire.',
            'description' => "Célèbre secret de beauté tchadien mondialement reconnu, la poudre de Chébé (graines de Croton gratissimus grillées et pilées) est ici incorporée dans une base fondante d'huiles végétales locales. Cette pommade gaine la tige capillaire, scelle l'hydratation durablement et stimule la vitalité des cheveux texturés, crépus et frisés.",
            'prix' => 4500,
            'prix_promo' => 4000,
            'stock' => 30,
            'seuil_alerte' => 8,
            'en_vedette' => 1,
            'nouveaute' => 0,
            'composition' => 'Beurre de karité tchadien, Poudre de graines de Chébé purifiées, Huile de Balanites, Huile de Ricin locale, Huile essentielle de lavande douce.',
            'origine' => 'Recette traditionnelle tchadienne, Guéra / Batha, conditionné à N’Djamena.',
            'conseils_utilisation' => 'Appliquer sur cheveux humides préalablement hydratés après votre shampoing ou vaporisateur, mèches par mèches. Tresser pour maximiser la rétention.',
            'precautions' => 'Éviter le contact direct avec le cuir chevelu sensible. Ne pas ingérer.',
            'conservation' => 'À conserver hermétiquement fermé à l’abri de la chaleur.',
            'photo' => 'pommade-chebe.jpg'
        ],
        [
            'nom' => 'Huile de Dattier du Désert (Balanites) Vierge (100ml)',
            'slug' => 'huile-dattier-desert-balanites-100ml',
            'categorie_id' => 1,
            'description_courte' => 'Huile sèche précieuse régénérante, riche en acides gras insaturés et antioxydants. Toucher sec velouté.',
            'description' => "L'arbre Balanites aegyptiaca, véritable sentinelle des savanes sahéliennes tchadiennes, produit une amande d'une richesse prodigieuse. Pressée à froid avec soin, cette huile pénètre instantanément sans laisser de film gras. Elle apaise les irritations, régule les peaux mixtes à grasses et apporte brillance aux cheveux.",
            'prix' => 5000,
            'prix_promo' => null,
            'stock' => 25,
            'seuil_alerte' => 5,
            'en_vedette' => 1,
            'nouveaute' => 1,
            'composition' => '100% Balanites Aegyptiaca Seed Oil (Huile de graine de Balanites vierge, pressée à froid).',
            'origine' => 'Province du Kanem et Chari-Baguirmi, Tchad.',
            'conseils_utilisation' => 'Appliquer 3 à 4 gouttes sur le visage propre en massage circulaire matin et soir. Peut s’utiliser en bain d’huile pour cuir chevelu sec.',
            'precautions' => 'Usage externe.',
            'conservation' => 'À conserver à l’abri de la lumière et de l’humidité.',
            'photo' => 'huile-balanites.jpg'
        ],
        [
            'nom' => 'Miel Pur Doré des Monts du Guéra (500g)',
            'slug' => 'miel-pur-dore-monts-guera-500g',
            'categorie_id' => 2,
            'description_courte' => 'Miel polyfloral sauvage non pasteurisé, récolté dans les massifs rocheux préservés du Guéra. Arôme riche et boisé.',
            'description' => "Issu d'une apiculture respectueuse et traditionnelle dans les monts du Guéra, ce miel de savane tchadienne offre des notes chaleureuses, boisées et légèrement ambrées. Récolté à maturité parfaite, non chauffé pour conserver toutes ses enzymes vivantes, son pollen et ses propriétés fortifiantes naturelles.",
            'prix' => 4500,
            'prix_promo' => null,
            'stock' => 40,
            'seuil_alerte' => 10,
            'en_vedette' => 1,
            'nouveaute' => 0,
            'composition' => '100% Miel pur d’abeilles sauvages (sans sucre ajouté ni conservateur).',
            'origine' => 'Massif du Guéra (Mongo / Bitkine), Tchad.',
            'conseils_utilisation' => 'Une cuillère chaque matin à jeun pour tonifier l’organisme, ou pour sucrer naturellement vos tisanes de karkadé et bouillies.',
            'precautions' => 'Déconseillé aux enfants de moins de 12 mois.',
            'conservation' => 'Conserver à température ambiante. La cristallisation naturelle est un gage de pureté.',
            'photo' => 'miel-guera.jpg'
        ],
        [
            'nom' => 'Sirop Artisanal de Karkadé (Bissap Tchadien) (750ml)',
            'slug' => 'sirop-artisanal-karkade-bissap-750ml',
            'categorie_id' => 2,
            'description_courte' => 'Concentré de fleurs d’hibiscus sabdariffa fraîches et menthe sauvage. Boisson rafraîchissante et revigorante.',
            'description' => "Élaboré selon les secrets des meilleures recettes familiales de N'Djamena, ce sirop de karkadé concentre la saveur acidulée et tonique des calices d'hibiscus du Tchad relevés d'une touche subtile de menthe fraîche. Se dilue simplement dans de l'eau glacée pour une pause fraîcheur bienfaisante.",
            'prix' => 3000,
            'prix_promo' => null,
            'stock' => 50,
            'seuil_alerte' => 12,
            'en_vedette' => 0,
            'nouveaute' => 1,
            'composition' => 'Infusion de calices d’Hibiscus Sabdariffa du Tchad, sucre de canne, extrait naturel de menthe sauvage, jus de citron naturel.',
            'origine' => 'Hibiscus cultivé au Mayo-Kebbi, transformé et embouteillé à N’Djamena.',
            'conseils_utilisation' => 'Diluer 1 volume de sirop pour 5 à 6 volumes d’eau fraîche. Servir très frais avec des glaçons.',
            'precautions' => 'Bien agiter avant utilisation.',
            'conservation' => 'À conserver au frais après ouverture et à consommer dans les 30 jours.',
            'photo' => 'sirop-karkade.jpg'
        ],
        [
            'nom' => 'Poudre Pure de Pulpe de Baobab - Pain de Singe (250g)',
            'slug' => 'poudre-baobab-pain-de-singe-250g',
            'categorie_id' => 2,
            'description_courte' => 'Super-aliment tchadien naturellement riche en vitamine C, calcium, fibres et antioxydants énergisants.',
            'description' => "La pulpe du fruit du majestueux baobab sèche naturellement sur l'arbre avant d'être récoltée et tamisée finement. Véritable concentré de vitalité, cette poudre acidulée apporte 6 fois plus de vitamine C qu'une orange et renforce le système immunitaire tout en facilitant le transit intestinal.",
            'prix' => 2800,
            'prix_promo' => 2500,
            'stock' => 35,
            'seuil_alerte' => 8,
            'en_vedette' => 0,
            'nouveaute' => 0,
            'composition' => '100% Pulpe de fruit de Baobab (Adansonia digitata) finement moulue.',
            'origine' => 'Région du Logone Oriental, Tchad.',
            'conseils_utilisation' => 'Mélanger 1 à 2 cuillères à soupe dans un verre d’eau, un yaourt, un jus frais ou vos préparations de crêpes et smoothies.',
            'precautions' => 'Refermer le sachet après chaque utilisation.',
            'conservation' => 'À l’abri de l’humidité et de la chaleur.',
            'photo' => 'poudre-baobab.jpg'
        ],
        [
            'nom' => 'Farine Nutritive Infantile & Familiale au Mil et Spiruline du Kanem (500g)',
            'slug' => 'farine-nutritive-mil-spiruline-kanem-500g',
            'categorie_id' => 2,
            'description_courte' => 'Mélange fortifiant équilibré de petit mil grillé, soja torréfié et spiruline pure du Lac Tchad (Dihé).',
            'description' => "Développée pour offrir une nutrition optimale aux enfants en croissance et à toute la famille, cette farine associe la douceur du mil sahélien aux protéines exceptionnelles de la spiruline tchadienne ancestrale (Dihé). Une bouillie saine, savoureuse et très facile à digérer.",
            'prix' => 3200,
            'prix_promo' => null,
            'stock' => 40,
            'seuil_alerte' => 10,
            'en_vedette' => 1,
            'nouveaute' => 0,
            'composition' => 'Petit mil tchadien décortiqué et précuit (60%), graines de soja torréfiées (25%), spiruline sauvage du Kanem (10%), poudre de souchet doux (5%).',
            'origine' => 'Spiruline récoltée au Kanem (ouadis du Lac Tchad), mil du Guéra.',
            'conseils_utilisation' => 'Délayer 4 cuillères à soupe dans 250ml d’eau ou de lait tiède. Cuire à feu doux pendant 5 à 7 minutes en remuant constamment.',
            'precautions' => 'Ne pas donner de bouillie trop chaude.',
            'conservation' => 'Conserver dans un récipient étanche à température ambiante.',
            'photo' => 'farine-spiruline.jpg'
        ],
        [
            'nom' => 'Huile Vierge d’Arachide Pressée à Froid (1 Litre)',
            'slug' => 'huile-vierge-arachide-pressee-a-froid-1l',
            'categorie_id' => 4,
            'description_courte' => 'Huile gastronomique non raffinée, au parfum délicat d’arachide grillée. Riche en oméga-6 et oméga-9.',
            'description' => "Obtenue par pression mécanique lente de graines d'arachides soigneusement sélectionnées et torréfiées au feu de bois dans le Moyen-Chari. Cette huile non raffinée conserve toute sa richesse aromatique naturelle et sublime les sauces gombo, les salades et les grillades tchadiennes.",
            'prix' => 2500,
            'prix_promo' => null,
            'stock' => 60,
            'seuil_alerte' => 15,
            'en_vedette' => 0,
            'nouveaute' => 1,
            'composition' => '100% Huile d’arachide vierge de première pression à froid.',
            'origine' => 'Bassin agricole de Sarh et Koumra, Tchad.',
            'conseils_utilisation' => 'Idéale pour assaisonnements, cuisson douce et relèvement gustatif des sauces traditionnelles.',
            'precautions' => 'Contient des arachides (allergène).',
            'conservation' => 'À l’abri de la chaleur et des rayons du soleil.',
            'photo' => 'huile-arachide.jpg'
        ],
        [
            'nom' => 'Panier Tressé Artisanal en Fibres de Dattier du Sahel',
            'slug' => 'panier-tresse-artisanal-fibres-dattier-sahel',
            'categorie_id' => 3,
            'description_courte' => 'Corbeille décorative et utilitaire façonnée à la main par les artisanes du Chari-Baguirmi.',
            'description' => "Chaque pièce est unique et témoigne du savoir-faire séculaire du tressage de palmes de dattier et de doum. Résistant, écologique et élégant, ce panier convient parfaitement pour vos présentations de fruits, le rangement des cosmétiques ou la décoration d'intérieur chaleureuse.",
            'prix' => 6500,
            'prix_promo' => null,
            'stock' => 18,
            'seuil_alerte' => 4,
            'en_vedette' => 0,
            'nouveaute' => 0,
            'composition' => 'Fibres végétales 100% naturelles (palmes de doum et jonc sahélien teintées aux pigments végétaux).',
            'origine' => 'Atelier coopératif artisanal de Gaoui, Chari-Baguirmi, Tchad.',
            'conseils_utilisation' => 'Nettoyer à l’aide d’un chiffon doux sec ou légèrement humide.',
            'precautions' => 'Ne pas immerger complètement dans l’eau.',
            'conservation' => 'Garder au sec.',
            'photo' => 'panier-artisanal.jpg'
        ]
    ];

    $stmtProd = $pdo->prepare("INSERT INTO products (
        nom, slug, categorie_id, description_courte, description, prix, prix_promo,
        photo_principale, stock, seuil_alerte, en_vedette, nouveaute,
        composition, origine, conseils_utilisation, precautions, conservation
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($products as $p) {
        $stmtProd->execute([
            $p['nom'],
            $p['slug'],
            $p['categorie_id'],
            $p['description_courte'],
            $p['description'],
            $p['prix'],
            $p['prix_promo'],
            $p['photo'],
            $p['stock'],
            $p['seuil_alerte'],
            $p['en_vedette'],
            $p['nouveaute'],
            $p['composition'],
            $p['origine'],
            $p['conseils_utilisation'],
            $p['precautions'],
            $p['conservation']
        ]);
        $prodId = $pdo->lastInsertId();

        // Ajout d'un lot initial pour chaque produit (PRD Section 9)
        $lotCode = 'LOT-' . date('y') . '-' . sprintf('%03d', $prodId);
        $dateFab = date('Y-m-d', strtotime('-2 months'));
        $dateLim = date('Y-m-d', strtotime('+18 months'));
        $pdo->prepare("INSERT INTO product_batches (produit_id, numero_lot, quantite, date_fabrication, date_limite, notes) VALUES (?, ?, ?, ?, ?, ?)")
            ->execute([$prodId, $lotCode, $p['stock'], $dateFab, $dateLim, 'Lot initial de lancement certifié conforme']);

        // Mouvement de stock d'entrée initial
        $pdo->prepare("INSERT INTO stock_movements (produit_id, type_mouvement, quantite, motif, utilisateur) VALUES (?, ?, ?, ?, ?)")
            ->execute([$prodId, 'Entrée', $p['stock'], 'Approvisionnement initial de lancement boutique', 'Système']);
    }

    // Variantes pour le Beurre de karité (ex: 250g, 500g, 1kg)
    $stmtVar = $pdo->prepare("INSERT INTO product_variants (produit_id, titre, format_poids, prix, stock) VALUES (?, ?, ?, ?, ?)");
    $stmtVar->execute([1, 'Pot en verre 250g', '250g', 3500, 25]);
    $stmtVar->execute([1, 'Pot familial 500g', '500g', 6000, 15]);
    $stmtVar->execute([1, 'Seau économique 1kg', '1kg', 11000, 5]);

    // Variantes pour l'huile de Balanites (100ml, 250ml)
    $stmtVar->execute([3, 'Flacon pompe 100ml', '100ml', 5000, 15]);
    $stmtVar->execute([3, 'Flacon éco 250ml', '250ml', 10500, 10]);

    // 6. Exemple de commande démo pour illustrer le dashboard et le suivi
    $orderRef = 'RN-26-' . strtoupper(substr(uniqid(), -5));
    $stmtOrder = $pdo->prepare("INSERT INTO orders (
        reference, client_nom, client_telephone, client_email, zone_id, zone_nom,
        adresse_detaillee, mode_reception, mode_paiement, statut_paiement, statut_commande,
        reference_paiement, sous_total, frais_livraison, total, notes_client, notes_internes, livreur_nom
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmtOrder->execute([
        $orderRef,
        'Mahamat Moussa',
        '+235 66 11 22 33',
        'mahamat.m@example.td',
        1,
        "N'Djamena - Centre & Quartiers Résidentiels",
        'Avenue Charles de Gaulle, face Ministère des Finances, Sabangali',
        'Livraison à domicile',
        'Paiement à la livraison',
        'À encaisser à la livraison',
        'En cours de livraison',
        '',
        12500,
        1500,
        14000,
        'Appeler dès que vous êtes devant le portail vert.',
        'Colis remis au coursier Aliou à 11h15.',
        'Aliou (Moto 04)'
    ]);
    $orderId = $pdo->lastInsertId();

    $pdo->prepare("INSERT INTO order_items (commande_id, produit_id, nom_produit, nom_variante, prix_unitaire, quantite, sous_total, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$orderId, 1, 'Beurre de Karité Pur Brut du Mandoul (250g)', 'Pot en verre 250g', 3500, 1, 3500, 'karite-brut.jpg']);
    $pdo->prepare("INSERT INTO order_items (commande_id, produit_id, nom_produit, nom_variante, prix_unitaire, quantite, sous_total, photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
        ->execute([$orderId, 4, 'Miel Pur Doré des Monts du Guéra (500g)', 'Pot 500g', 4500, 2, 9000, 'miel-guera.jpg']);
}

// Lancer l'initialisation si nécessaire
initDatabase($pdo);
