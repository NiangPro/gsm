# GIE Sokhna Maï - Version PHP

Clone identique du projet React original, converti en PHP avec Tailwind CSS (CDN).

## Structure du projet

```
gie-sokhna-mai-php/
├── assets/              # Images et ressources statiques
├── includes/            # Fichiers communs (header, footer, config)
├── pages/               # Pages publiques
├── admin/               # Pages d'administration
├── lang/                # Fichiers de traduction (fr/en)
├── index.php            # Routeur principal
└── .htaccess            # Configuration Apache
```

## Pages publiques

- **Accueil** (`/`) - Page d'accueil avec hero, produits phares et valeurs
- **À propos** (`?page=a-propos`) - Histoire et mission du GIE
- **Catalogue** (`?page=catalogue`) - Produits avec filtres par catégorie
- **Boutique** (`?page=boutique`) - Formulaire de commande via WhatsApp
- **Blog** (`?page=blog`) - Actualités et articles
- **Équipe** (`?page=equipe`) - Membres de l'équipe
- **Contact** (`?page=contact`) - Formulaire de contact
- **Partenaires** (`?page=partenaires`) - Événements et partenaires

## Espace Admin

Accès : `?page=admin`

- **Identifiants par défaut** : admin@sokhnamai.sn / admin123
- **Dashboard** - Statistiques et aperçu
- **Produits** - Gestion des produits (CRUD)
- **Commandes** - Gestion des commandes avec statuts
- **Équipe** - Gestion des membres

## Fonctionnalités

✅ Multi-langue (FR/EN)  
✅ Design responsive avec Tailwind CSS  
✅ Navigation avec menu mobile  
✅ Formulaires fonctionnels (contact, commande)  
✅ Intégration WhatsApp pour les commandes  
✅ Authentification admin  
✅ Dashboard admin avec statistiques  
✅ Gestion CRUD des produits (mock)  
✅ Gestion des commandes avec statuts  

## Technologies

- PHP 7.4+
- Tailwind CSS (CDN)
- Font Awesome (CDN)
- Google Fonts (Playfair Display, Lato)
- Sessions PHP pour l'authentification

## Installation

1. Placer le dossier dans le répertoire web (ex: `wamp64/www/`)
2. Accéder à `http://localhost/gie-sokhna-mai-php/`
3. Pour l'admin : `http://localhost/gie-sokhna-mai-php/?page=admin`

## Personnalisation

### Couleurs
Modifier les couleurs dans `includes/header.php` :
```javascript
colors: {
    primary: {
        DEFAULT: '#16a34a', // Vert principal
        // ...
    },
    accent: {
        DEFAULT: '#d97706', // Ambre accent
    }
}
```

### Traductions
Modifier les fichiers dans `lang/fr.php` et `lang/en.php`.

## Notes

- Ce projet est une conversion statique/mock du projet React original
- Les données sont stockées en mémoire (pas de base de données requise)
- Pour une version production, il faudrait ajouter une base de données MySQL
