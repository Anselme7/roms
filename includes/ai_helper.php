<?php
/**
 * Assistant IA pour la rédaction des fiches produits (PRD Section 10)
 * Respect strict des limites impératives :
 * - Aucune invention d'ingrédients, de labels, de dates ou de prix
 * - Aucune promesse médicale ou de guérison
 * - Brouillon modifiable avec validation humaine obligatoire
 */

require_once __DIR__ . '/../config/settings.php';

class ProductAIAssistant {

    /**
     * Génère un brouillon de fiche produit
     *
     * @param array $input Données fournies par l'administrateur
     * @return array Proposition de titre, description courte, description détaillée, conseils d'utilisation
     */
    public static function generateDraft(array $input) {
        $nom = trim($input['nom'] ?? '');
        $categorie = trim($input['categorie'] ?? '');
        $composition = trim($input['composition'] ?? '');
        $origine = trim($input['origine'] ?? '');
        $format = trim($input['format'] ?? '');
        $usages = trim($input['usages'] ?? '');
        $caracteristiques = trim($input['caracteristiques'] ?? '');

        if (empty($nom)) {
            return [
                'success' => false,
                'error' => 'Le nom du produit est obligatoire pour générer un brouillon.'
            ];
        }

        $apiKey = getSetting('gemini_api_key', '');

        // Si une clé Gemini API est configurée, tenter l'appel API direct
        if (!empty($apiKey)) {
            $geminiResult = self::callGeminiAPI($apiKey, [
                'nom' => $nom,
                'categorie' => $categorie,
                'composition' => $composition,
                'origine' => $origine,
                'format' => $format,
                'usages' => $usages,
                'caracteristiques' => $caracteristiques
            ]);

            if ($geminiResult && !empty($geminiResult['description_courte'])) {
                return [
                    'success' => true,
                    'is_draft' => true,
                    'provider' => 'Gemini API',
                    'disclaimer' => "Brouillon généré par IA - Vérification et validation humaine obligatoires avant toute publication (Conforme PRD Section 10).",
                    'data' => $geminiResult
                ];
            }
        }

        // Modèle rédactionnel intelligent local / de secours (haute fidélité contextuelle Tchad & bio)
        $localResult = self::generateLocalDraft($nom, $categorie, $composition, $origine, $format, $usages, $caracteristiques);
        return [
            'success' => true,
            'is_draft' => true,
            'provider' => 'Moteur Rédactionnel ROM\'S Natural',
            'disclaimer' => "Brouillon généré par IA - Vérification et validation humaine obligatoires avant toute publication (Conforme PRD Section 10).",
            'data' => $localResult
        ];
    }

    /**
     * Générateur local déterministe et contextuel (garantit un fonctionnement 100% hors-ligne)
     */
    private static function generateLocalDraft($nom, $categorie, $composition, $origine, $format, $usages, $caracteristiques) {
        $titrePropose = $nom;
        if (!empty($format) && stripos($nom, $format) === false) {
            $titrePropose .= ' (' . $format . ')';
        }

        // Description courte
        $descCourteParts = [];
        $descCourteParts[] = "Découvrez " . $nom . ", issu du savoir-faire artisanal et de la richesse des terroirs du Tchad.";
        if (!empty($caracteristiques)) {
            $descCourteParts[] = ucfirst($caracteristiques) . ".";
        }
        if (!empty($composition)) {
            $descCourteParts[] = "Formulé à base de : " . $composition . ".";
        }
        if (!empty($format)) {
            $descCourteParts[] = "Conditionnement soigné en " . $format . ".";
        }
        $descriptionCourte = implode(" ", $descCourteParts);

        // Description détaillée
        $detailed = "### Présentation du produit\n\n";
        $detailed .= "Le produit **" . $nom . "** est sélectionné et valorisé par **ROM'S Natural** pour célébrer la qualité des ressources naturelles locales tchadiennes.\n\n";

        if (!empty($origine)) {
            $detailed .= "**Origine et Terroir :**\nIssu de " . $origine . ", ce produit reflète un engagement direct en faveur de la transformation locale et des filières durables au Tchad.\n\n";
        }

        if (!empty($composition)) {
            $detailed .= "**Composition confirmée :**\n" . $composition . "\n*(Note : Aucun ingrédient supplémentaire non vérifié n'a été ajouté)*.\n\n";
        }

        if (!empty($caracteristiques)) {
            $detailed .= "**Caractéristiques principales :**\n- " . str_replace("\n", "\n- ", $caracteristiques) . "\n\n";
        }

        // Conseils d'utilisation
        $conseils = "";
        if (!empty($usages)) {
            $conseils = "Conseils recommandés : " . $usages . ".\nAppliquer ou consommer avec modération selon vos habitudes.";
        } else {
            $conseils = "Prendre une quantité adaptée selon vos besoins. Bien refermer après usage et conserver dans un endroit sec et tempéré.";
        }

        // Précautions neutres
        $precautions = "Conserver hors de portée des jeunes enfants. En cas de réaction cutanée ou d'allergie connue aux ingrédients mentionnés, cesser l'utilisation. Ne remplace pas un avis médical.";

        return [
            'titre' => $titrePropose,
            'description_courte' => $descriptionCourte,
            'description_detaillee' => $detailed,
            'conseils_utilisation' => $conseils,
            'precautions' => $precautions
        ];
    }

    /**
     * Appel à l'API Gemini si la clé est fournie
     */
    private static function callGeminiAPI($apiKey, array $params) {
        $prompt = "Tu es l'assistant de rédaction spécialisé pour la marque 'ROM'S Natural', une boutique e-commerce de produits locaux transformés au Tchad (cosmétiques naturels, karité, chébé, baobab, karkadé, miels, farines locales, etc.).\n\n";
        $prompt .= "Consignes impératives issues du Cahier des Charges (PRD Section 10) :\n";
        $prompt .= "1. N'invente JAMAIS d'ingrédients, de labels, de certifications, de fabricants, de dates ou de prix non mentionnés.\n";
        $prompt .= "2. Ne formule JAMAIS de promesses médicales, de guérison ou de résultats garantis.\n";
        $prompt .= "3. Adopte un ton chaleureux, valorisant, authentique et professionnel adapté au public tchadien et africain.\n\n";
        $prompt .= "Informations fournies par l'administrateur :\n";
        $prompt .= "- Nom du produit : " . $params['nom'] . "\n";
        $prompt .= "- Catégorie : " . $params['categorie'] . "\n";
        $prompt .= "- Composition confirmée : " . $params['composition'] . "\n";
        $prompt .= "- Origine / Coopérative : " . $params['origine'] . "\n";
        $prompt .= "- Format / Conditionnement : " . $params['format'] . "\n";
        $prompt .= "- Usages connus : " . $params['usages'] . "\n";
        $prompt .= "- Caractéristiques : " . $params['caracteristiques'] . "\n\n";
        $prompt .= "Réponds UNIQUEMENT sous forme d'un objet JSON strict avec ces 5 clés exactes sans texte avant ou après :\n";
        $prompt .= "{\n";
        $prompt .= '  "titre": "Titre valorisant proposé",' . "\n";
        $prompt .= '  "description_courte": "Description courte (2 à 3 phrases percutantes)",' . "\n";
        $prompt .= '  "description_detaillee": "Description détaillée structurée en markdown",' . "\n";
        $prompt .= '  "conseils_utilisation": "Conseils d\'utilisation précis basés sur les usages communiqués",' . "\n";
        $prompt .= '  "precautions": "Précautions de bon sens et de conservation"' . "\n";
        $prompt .= "}";

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . urlencode($apiKey);

        $payload = json_encode([
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $prompt]]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.4,
                'responseMimeType' => 'application/json'
            ]
        ]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            $parsed = json_decode($rawText, true);
            if (is_array($parsed)) {
                return $parsed;
            }
        }
        return null;
    }
}

