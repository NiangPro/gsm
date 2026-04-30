# Base de données GIE Sokhna Maï

## Structure de la base de données

### Tables principales

| Table | Description | Relations |
|-------|-------------|-----------|
| `admins` | Utilisateurs administrateurs | - |
| `categories` | Catégories de produits | 1:N → products |
| `products` | Produits du catalogue | N:1 → categories, 1:N → order_items |
| `customers` | Clients | 1:N → orders |
| `orders` | Commandes | N:1 → customers, 1:N → order_items |
| `order_items` | Lignes de commande | N:1 → orders, N:1 → products |
| `team_members` | Membres de l'équipe | - |
| `blog_categories` | Catégories d'articles | 1:N → blog_posts |
| `blog_posts` | Articles de blog | N:1 → blog_categories, N:1 → admins |
| `contact_messages` | Messages de contact | N:1 → admins (réponse) |
| `partners` | Partenaires et événements | - |
| `site_settings` | Paramètres du site | - |
| `activity_logs` | Journal d'activité admin | N:1 → admins |

## Installation

### 1. Créer la base de données

Exécutez le fichier `schema.sql` dans phpMyAdmin ou en ligne de commande :

```bash
mysql -u root -p < schema.sql
```

Ou importez le fichier via l'interface de phpMyAdmin.

### 2. Insérer les données de test (optionnel)

```bash
mysql -u root -p gie_sokhna_mai < seed.sql
```

## Identifiants par défaut

- **Email** : `admin@sokhnamai.sn`
- **Mot de passe** : `admin123`

> **Important** : Changez le mot de passe par défaut en production !

## Configuration

Le fichier `includes/database.php` contient la configuration de connexion :

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'gie_sokhna_mai');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
```

Modifiez ces valeurs selon votre environnement.

## Diagramme des relations (ERD)

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  categories │────<│   products  │>────│ order_items │
└─────────────┘     └─────────────┘     └──────┬──────┘
                                               │
┌─────────────┐     ┌─────────────┐           │
│  customers  │────<│    orders   │>───────────┘
└─────────────┘     └─────────────┘

┌─────────────────┐     ┌─────────────┐
│ blog_categories │────<│  blog_posts │
└─────────────────┘     └──────┬──────┘
                               │
                         ┌─────┘
                         ▼
                    ┌─────────┐
                    │  admins │
                    └─────────┘
```

## Fonctions utilitaires disponibles

```php
// Connexion
$pdo = getDBConnection();

// Requêtes simples
$product = dbFetchOne("SELECT * FROM products WHERE id = ?", [1]);
$products = dbFetchAll("SELECT * FROM products WHERE category_id = ?", [1]);

// Insertion
$newId = dbInsert("INSERT INTO products (name, price) VALUES (?, ?)", ["Nouveau", 2500]);

// Mise à jour
$affected = dbUpdate("UPDATE products SET price = ? WHERE id = ?", [3000, 1]);

// Suppression
$deleted = dbDelete("DELETE FROM products WHERE id = ?", [1]);

// Transactions
dbBeginTransaction();
try {
    dbInsert("...", []);
    dbUpdate("...", []);
    dbCommit();
} catch (Exception $e) {
    dbRollback();
}
```

## Exemples de requêtes courantes

### Produits avec catégories
```sql
SELECT p.*, c.name_fr as category_name 
FROM products p 
JOIN categories c ON p.category_id = c.id 
WHERE p.is_available = 1;
```

### Commandes avec clients et items
```sql
SELECT o.*, c.name as customer_name, c.phone 
FROM orders o 
JOIN customers c ON o.customer_id = c.id 
WHERE o.status = 'En attente';
```

### Statistiques des ventes
```sql
SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as average_order
FROM orders 
WHERE status = 'Livrée';
```
