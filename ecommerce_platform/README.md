# 🛒 Plateforme E-commerce PHP

Bienvenue sur le dépôt de la **Plateforme E-commerce**. Ce projet est une application web complète développée en PHP (Full-Stack), offrant une interface utilisateur intuitive pour les clients et un tableau de bord d'administration robuste pour la gestion de la boutique.

---

## 🌟 Fonctionnalités Principales

### 👤 Côté Client (Front-Office)
* **Catalogue de Produits :** Navigation à travers les produits (`shop.php`), vue détaillée d'un produit (`sproduct.php`) et recherche en temps réel (`search_products.php`).
* **Panier d'Achat Dynamique (AJAX) :** Ajout au panier (`add_to_cart_ajax.php`), mise à jour des quantités (`update_cart_quantity.php`), suppression d'articles (`remove_cart_item.php`) et compteur dynamique (`cart_count.php`).
* **Authentification & Profil :** Inscription, connexion, déconnexion (`login.php`, `register.php`, `logout.php`) et gestion du profil utilisateur avec avatar (`profile.php`, `uploads/avatars/`).
* **Processus de Commande & Paiement :** Validation du panier (`process-order.php`) et intégration de paiement (`payment.php`, `process_payment.php`, `payment.js`).
* **Pages Informatives :** Accueil (`index.php`), À propos (`about.php`), Blog (`blog.php`) et Contact (`contact.php`).
* **Devise :** Gestion des taux de change (`get_exchange_rate.php`).

### 🛠️ Côté Administrateur (Back-Office)
Accessible via le répertoire `/admin`, sécurisé par une authentification dédiée (`admin_auth.php`).
* **Tableau de Bord :** Vue d'ensemble des statistiques de la boutique (`dashboard.php`).
* **Gestion des Produits (CRUD) :** Ajouter (`add_product.php`), modifier (`edit_product.php`), supprimer (`delete_product.php`) et lister les produits (`products.php`).
* **Gestion des Utilisateurs :** Ajouter, modifier, bannir/supprimer des utilisateurs, et attribuer des rôles administrateur/client (`toggle_user_role.php`, `users.php`).
* **Gestion des Paniers & Commandes :** Visualiser les paniers des utilisateurs (`carts.php`, `view_cart.php`), vider les paniers (`clear_user_cart.php`) et créer des commandes manuellement (`create_order.php`).

### 🔒 Sécurité
* **Protection CSRF :** Implémentation de jetons de sécurité pour prévenir les attaques CSRF (`csrf_protection.php`).
* **Journalisation d'Authentification :** Suivi des tentatives de connexion (`auth_logger.php`).
* **Séparation des sessions :** Accès strictement séparé entre les clients réguliers et les administrateurs.

---

## 📂 Architecture du Projet

Le projet suit une structure claire, séparant le back-office du front-office et organisant les assets logiquement :

```text
ecommerce_platform/
├── admin/                  # Tableau de bord d'administration et scripts CRUD
├── img/                    # Images du site (Bannières, Produits, Avatars, etc.)
├── uploads/avatars/        # Répertoire de stockage des photos de profil des utilisateurs
├── config.php              # Configuration de la base de données
├── functions.php           # Fonctions utilitaires globales (PHP)
├── style.css & script.js   # Styles et scripts du front-office
└── *.php                   # Pages publiques (Index, Shop, Cart, Login, etc.)
💻 Technologies Utilisées
Backend : PHP natif (Session, PDO/MySQLi)

Frontend : HTML5, CSS3, JavaScript (Vanilla & AJAX)

Base de données : MySQL

Serveur local recommandé : XAMPP / WAMP / MAMP

🚀 Installation & Déploiement Local
Suivez ces étapes pour exécuter le projet sur votre machine locale :

Prérequis

Avoir installé XAMPP, WAMP ou MAMP.

Un navigateur web moderne.

Étapes d'installation

Cloner ou Extraire le projet :
Placez le dossier ecommerce_platform dans le répertoire racine de votre serveur local :

XAMPP : C:/xampp/htdocs/

MAMP (Mac) : /Applications/MAMP/htdocs/ ou /Applications/XAMPP/xamppfiles/htdocs/

Configuration de la Base de Données :

Ouvrez phpMyAdmin (généralement via http://localhost/phpmyadmin).

Créez une nouvelle base de données (ex: ecommerce_db).

Importez le fichier SQL fourni avec le projet (si existant) ou référez-vous au fichier config.php pour voir la structure requise.

Liaison de la Base de Données :

Ouvrez le fichier config.php situé à la racine du projet.

Modifiez les identifiants de connexion selon votre configuration locale :

PHP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Laissez vide sous Windows/XAMPP, 'root' sous MAMP
define('DB_NAME', 'ecommerce_db'); // Le nom de la base créée à l'étape 2
Lancement de l'application :

Démarrez les modules Apache et MySQL depuis votre panneau de contrôle serveur (XAMPP/MAMP).

Ouvrez votre navigateur et accédez à :
👉 Front-office : http://localhost/ecommerce_platform/
👉 Back-office : http://localhost/ecommerce_platform/admin/

📝 Auteur / Contributeur
Développé par : Mounir
