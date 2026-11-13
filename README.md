# 🌿 ROM'S NATURAL — Plateforme E-Commerce Complète

> **Plateforme e-commerce de vente de produits locaux transformés à Koumra (Province du Mandoul, Tchad)**  
> Développée conformément au Cahier des Charges Fonctionnel / Product Requirements Document (PRD v1.0).

---

## 📌 Présentation du Projet & Localisation

**ROM'S Natural** est basée à **Koumra dans la Province du Mandoul (Tchad)**, au cœur de la zone de production naturelle du beurre de karité, de l'huile de ricin, du tamarin sauvage et des trésors botaniques locaux.

Toutes les expéditions démarrent depuis l'atelier et la boutique de **Koumra** pour desservir :
- **Koumra (Ville Siège)** : Retrait gratuit en boutique ou livraison locale express (500 FCFA).
- **Provinces voisines du Sud (Sarh, Moyen-Chari, Moundou, Doba)** : Liaisons régulières rapides (1 500 à 2 500 FCFA).
- **N'Djamena (Capitale)** : Départs quotidiens de bus express depuis Koumra (3 500 FCFA – 24h à 48h).
- **Toutes les autres provinces du Tchad** : (3 000 à 5 000 FCFA).
- **Sous-Région CEMAC / Afrique Centrale (Cameroun, etc.)** : Fret routier transfrontalier (8 500 FCFA).
- **International & Diaspora (France, Europe, Monde)** : Fret aérien / DHL (18 000 FCFA).

---

## 📞 Coordonnées Officielles de Koumra

- **Téléphones Directs :** `+235 62 89 10 85` / `+235 98 20 53 50`
- **WhatsApp Officiel :** `+235 62 89 10 85`
- **Adresse Physique :** Boutique & Atelier ROM'S Natural, Grand Marché / Centre Commercial, Koumra, Mandoul, Tchad.
- **Paiements Mobile Money Tchad :**
  - **Airtel Money :** `+235 62 89 10 85` (*436#)
  - **Moov Money :** `+235 98 20 53 50` (*156#)

---

## 🛍️ Nouveaux Produits Réels Intégrés

1. **Lait Corporel Concentré au Beurre de Karité - RN Beauté Naturel (Made in Chad)**
   - Soin réparateur intense peaux sèches et abîmées. Photo officielle haute définition.
2. **Huile de Ricin Pure Végétale du Mandoul (Koumra)**
   - Pression artisanale locale, stimule la pousse des cheveux et fortifie les ongles.
3. **Sirop de Tamarin Artisanal 100% BIO - Coopérative des Femmes Rokndoh de Mandoul-Koumra**
   - Élaboré par les groupements de femmes de Koumra, tonique et digestif.
4. **Savon Artisanal Sculpté Pur Karité & Miel**
   - Pièce d'artisanat cosmétique moulée aux motifs d'abeilles et de fleurs.

---

## 🚀 Démarrage Rapide

### Option A : Via XAMPP (Recommandé)
1. Ouvrez le **XAMPP Control Panel** (`c:\xampp\xampp-control.exe`).
2. Cliquez sur **Start** en face du module **Apache**.
3. Ouvrez votre navigateur web :
   - **Boutique publique :** [http://localhost/roms/](http://localhost/roms/)
   - **Espace Administration :** [http://localhost/roms/admin/](http://localhost/roms/admin/)

### Option B : Serveur PHP Intégré
Dans le terminal PowerShell :
```powershell
& "c:\xampp\php\php.exe" -S localhost:8000 -t c:\xampp\htdocs\roms
```
Accédez ensuite à `http://localhost:8000/`.

---

## 🔐 Identifiants Administrateur par Défaut

- **Identifiant :** `admin`
- **Mot de passe :** `admin123`
- **Rôle :** Administrateur principal *(accès complet à tous les modules)*

> 💡 *D'autres utilisateurs et rôles peuvent être créés depuis le menu **Utilisateurs & Rôles**.*

---

## 📋 Couverture Intégrale des Spécifications du PRD

| Section PRD | Fonctionnalité | Implémentation |
|---|---|---|
| **Section 3** | Familles de produits | 5 familles dynamiques (Cosmétiques, Alimentaires, Artisanaux, Agricoles, Extensible) |
| **Section 4** | Fiches & Catalogue | Prix en Franc CFA (XAF), photos, composition, origine/producteur, conseils, variantes de conditionnement, alertes rupture |
| **Section 5** | Expérience Client | Mobile-first, recherche mot-clé temps réel, filtres par catégorie/disponibilité, tri par prix/nouveauté |
| **Section 5.3 & 6** | Commande Invité | Parcours d'achat direct sans création de compte, formulaire concis, protection anti-doublon |
| **Section 6.3** | Moyens de Paiement | Paiement à la remise (Cash), Airtel Money (*436#), Moov Money (*156#), Virement bancaire (BCC) |
| **Section 7** | Livraison Nationale | Paramétrage des zones (N'Djamena Centre, Banlieues, Moundou, Sarh, Abéché, etc.) avec tarifs et délais estimatifs |
| **Section 7.3 & 5.1** | Suivi de Commande | Page publique `/suivi.php` avec référence unique (`RN-2611-XXXX`) et barre de progression |
| **Section 8** | Gestion Commandes | 8 statuts officiels, motifs d'annulation, assignation livreur/transporteur, impression de bon de commande |
| **Section 9** | Stocks & Traçabilité | Alertes stock faible, journal d'entrées/sorties, gestion des numéros de lot et alertes de péremption DLUO |
| **Section 10** | Assistant IA Rédaction | Module d'aide à la rédaction avec respect absolu des limites (aucun ingrédient inventé, validation humaine obligatoire) |
| **Section 11** | WhatsApp Officiel | Accès WhatsApp flottant, message prérempli produit & commande, notification client en 1 clic par l'admin |
| **Section 12** | Administration & Rôles | 5 rôles avec permissions distinctes (Admin principal, Catalogue, Commandes, Stock, Consultation) |

---

## 📁 Architecture des Fichiers

```
c:\xampp\htdocs\roms/
├── config/
│   ├── database.php        # Initialisation PDO SQLite (auto-création des tables et données seed)
│   ├── settings.php        # Configuration dynamique, devise XAF, constantes
│   └── auth.php            # Authentification, sessions sécurisées, vérification des rôles et CSRF
├── includes/
│   ├── header.php          # En-tête public responsive, panier slide-over, navigation mobile
│   ├── footer.php          # Pied de page, réassurance client, bouton flottant WhatsApp
│   ├── functions.php       # Formatage prix FCFA, badges statuts, liens WhatsApp
│   └── ai_helper.php       # Moteur rédactionnel IA conforme PRD Section 10 (Local + Gemini API)
├── assets/
│   ├── css/style.css       # Palette naturelle, polices, responsive & impression
│   ├── js/main.js          # Panier AJAX temps réel, tiroir coulissant, toasts
│   └── images/             # Générateur SVG placeholder et dossier pour uploads de photos
├── api/
│   ├── cart.php            # API Panier (Ajout, modification, suppression, vérification stock)
│   ├── checkout.php        # Validation commande invité, calcul livraison, mise à jour stocks
│   ├── track.php           # API de suivi de commande public
│   └── ai_generate.php     # Endpoint AJAX pour l'assistant IA de fiches produits
├── admin/
│   ├── index.php           # Tableau de bord complet (KPI, ventes, alertes stock & péremption)
│   ├── login.php / logout  # Connexion et déconnexion sécurisées
│   ├── orders.php          # Liste des commandes avec filtres et recherche
│   ├── order_detail.php    # Détail commande, bon imprimable, notification WhatsApp
│   ├── products.php        # Catalogue produits, recherche, publication/dépublication
│   ├── product_edit.php    # Fiche produit + Assistant Rédactionnel IA intégré
│   ├── categories.php      # Gestion dynamique des catégories et familles
│   ├── stock.php           # Gestion des stocks, mouvements et lots de fabrication (DLUO)
│   ├── delivery_zones.php  # Configuration des zones et tarifs de livraison au Tchad
│   ├── users.php           # Gestion des utilisateurs et affectation des 5 rôles
│   └── settings.php        # Paramètres généraux, WhatsApp, Airtel/Moov Money, Banque, IA
├── index.php               # Page d'accueil vitrine
├── catalogue.php           # Boutique / catalogue filtrable
├── produit.php             # Page détail produit avec choix de variante et bouton WhatsApp
├── panier.php              # Récapitulatif du panier
├── commande.php            # Checkout invité avec calcul dynamique des frais
├── confirmation.php        # Reçu de commande avec instructions de paiement et référence unique
├── suivi.php               # Suivi public de commande en direct
├── a-propos.php            # Présentation de la marque, valeurs et terroirs du Tchad
├── contact.php             # Contact, coordonnées à Chagoua N'Djamena et WhatsApp
└── faq.php                 # FAQ, conditions de vente, livraison, retours et confidentialité
```

#   r o m s  
 