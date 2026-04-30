-- Script d'optimisation des indexes pour améliorer les performances
-- Exécuter ce script dans MySQL pour créer les indexes nécessaires

-- Index sur la table products
CREATE INDEX IF NOT EXISTS idx_products_name ON products(name);
CREATE INDEX IF NOT EXISTS idx_products_created_at ON products(created_at);
CREATE INDEX IF NOT EXISTS idx_products_is_available ON products(is_available);
CREATE INDEX IF NOT EXISTS idx_products_category_id ON products(category_id);
CREATE INDEX IF NOT EXISTS idx_products_slug ON products(slug);

-- Index sur la table order_items
CREATE INDEX IF NOT EXISTS idx_order_items_product_id ON order_items(product_id);
CREATE INDEX IF NOT EXISTS idx_order_items_order_id ON order_items(order_id);

-- Index sur la table categories
CREATE INDEX IF NOT EXISTS idx_categories_name_fr ON categories(name_fr);

-- Index composite pour les recherches fréquentes
CREATE INDEX IF NOT EXISTS idx_products_available_category ON products(is_available, category_id);
CREATE INDEX IF NOT EXISTS idx_products_search ON products(name, description_fr, ingredients_fr);

-- Analyser les tables pour mettre à jour les statistiques
ANALYZE TABLE products;
ANALYZE TABLE order_items;
ANALYZE TABLE categories;
