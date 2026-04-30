-- ============================================================
-- GIE Sokhna Maï - Base de données MySQL
-- ============================================================

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS gie_sokhna_mai 
    CHARACTER SET utf8mb4 
    COLLATE utf8mb4_unicode_ci;

USE gie_sokhna_mai;

-- ============================================================
-- TABLE: admins
-- Administrateurs du système
-- ============================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'editor') DEFAULT 'admin',
    is_active BOOLEAN DEFAULT TRUE,
    last_login DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: categories
-- Catégories de produits
-- ============================================================
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_fr VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description_fr TEXT,
    description_en TEXT,
    icon VARCHAR(50),
    color VARCHAR(7) DEFAULT '#16a34a',
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: products
-- Produits du GIE
-- ============================================================
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(150) NOT NULL UNIQUE,
    description_fr TEXT,
    description_en TEXT,
    ingredients_fr VARCHAR(500),
    ingredients_en VARCHAR(500),
    price DECIMAL(10, 2) NOT NULL,
    weight VARCHAR(20),
    stock_quantity INT DEFAULT 0,
    image_url VARCHAR(255),
    is_featured BOOLEAN DEFAULT FALSE,
    is_available BOOLEAN DEFAULT TRUE,
    meta_title_fr VARCHAR(100),
    meta_title_en VARCHAR(100),
    meta_description_fr VARCHAR(255),
    meta_description_en VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: customers
-- Clients (avec espace client)
-- ============================================================
CREATE TABLE customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(255) UNIQUE,
    password_hash VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    notes TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_phone (phone),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: orders
-- Commandes clients
-- ============================================================
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(20) NOT NULL UNIQUE,
    customer_id INT NOT NULL,
    status ENUM('En attente', 'Confirmée', 'En préparation', 'En livraison', 'Livrée', 'Annulée') DEFAULT 'En attente',
    total_amount DECIMAL(12, 2) NOT NULL,
    delivery_address TEXT,
    delivery_city VARCHAR(100),
    delivery_notes TEXT,
    payment_method ENUM('Espèces', 'Mobile Money', 'Carte', 'Virement') DEFAULT 'Espèces',
    payment_status ENUM('En attente', 'Payée', 'Partielle', 'Remboursée') DEFAULT 'En attente',
    whatsapp_sent BOOLEAN DEFAULT FALSE,
    processed_by INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES admins(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: order_items
-- Lignes de commande (produits dans une commande)
-- ============================================================
CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    weight VARCHAR(20),
    subtotal DECIMAL(12, 2) NOT NULL,
    notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: team_members
-- Membres de l'équipe
-- ============================================================
CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    role_fr VARCHAR(100) NOT NULL,
    role_en VARCHAR(100),
    description_fr TEXT,
    description_en TEXT,
    photo_url VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: blog_categories
-- Catégories d'articles de blog
-- ============================================================
CREATE TABLE blog_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_fr VARCHAR(100) NOT NULL,
    name_en VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description_fr TEXT,
    description_en TEXT,
    color VARCHAR(7) DEFAULT '#16a34a',
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: blog_posts
-- Articles de blog
-- ============================================================
CREATE TABLE blog_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT,
    author_id INT,
    title_fr VARCHAR(255) NOT NULL,
    title_en VARCHAR(255),
    slug VARCHAR(255) NOT NULL UNIQUE,
    excerpt_fr TEXT,
    excerpt_en TEXT,
    content_fr LONGTEXT,
    content_en LONGTEXT,
    featured_image VARCHAR(255),
    emoji VARCHAR(10),
    is_published BOOLEAN DEFAULT FALSE,
    published_at DATETIME,
    view_count INT DEFAULT 0,
    meta_title_fr VARCHAR(100),
    meta_title_en VARCHAR(100),
    meta_description_fr VARCHAR(255),
    meta_description_en VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (author_id) REFERENCES admins(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_published (is_published, published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: contact_messages
-- Messages du formulaire de contact
-- ============================================================
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    ip_address VARCHAR(45),
    is_read BOOLEAN DEFAULT FALSE,
    replied_by INT,
    replied_at DATETIME,
    reply_message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (replied_by) REFERENCES admins(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: partners
-- Partenaires et événements
-- ============================================================
CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    name_en VARCHAR(150),
    type ENUM('Partenaire', 'Événement', 'Sponsoring', 'Collaboration') DEFAULT 'Partenaire',
    year VARCHAR(4),
    date_fr VARCHAR(50),
    date_en VARCHAR(50),
    description_fr TEXT,
    description_en TEXT,
    logo_url VARCHAR(255),
    website_url VARCHAR(255),
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: site_settings
-- Paramètres du site
-- ============================================================
CREATE TABLE site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT,
    setting_type ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
    description VARCHAR(255),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TABLE: activity_logs
-- Journal d'activité admin
-- ============================================================
CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    details JSON,
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERTION DES DONNÉES PAR DÉFAUT
-- ============================================================

-- Admin par défaut (mot de passe: admin123)
-- Hash bcrypt pour 'admin123'
INSERT INTO admins (email, password_hash, name, role) VALUES
('admin@sokhnamai.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'super_admin');

-- Catégories de produits
INSERT INTO categories (name_fr, name_en, slug, description_fr, description_en, icon, color, display_order) VALUES
('Confitures', 'Jams', 'confitures', 'Confitures artisanales préparées avec des fruits frais du Sénégal', 'Artisanal jams made with fresh Senegalese fruits', 'fa-jar', '#16a34a', 1),
('Jus', 'Juices', 'jus', 'Jus naturels pressés à froid', 'Cold-pressed natural juices', 'fa-glass-whiskey', '#d97706', 2),
('Sirops', 'Syrups', 'sirops', 'Sirops concentrés pour boissons rafraîchissantes', 'Concentrated syrups for refreshing drinks', 'fa-wine-bottle', '#7c3aed', 3),
('Céréales', 'Cereals', 'cereales', 'Céréales et poudres nutritives', 'Nutritious cereals and powders', 'fa-seedling', '#059669', 4);

-- Catégories de blog
INSERT INTO blog_categories (name_fr, name_en, slug, description_fr, description_en, color) VALUES
('Santé', 'Health', 'sante', 'Articles sur la santé et le bien-être', 'Health and wellness articles', '#16a34a'),
('Recettes', 'Recipes', 'recettes', 'Recettes et idées culinaires', 'Recipes and culinary ideas', '#d97706'),
('Événements', 'Events', 'evenements', 'Actualités et événements du GIE', 'GIE news and events', '#7c3aed'),
('Savoir-faire', 'Know-how', 'savoir-faire', 'Notre expertise et techniques', 'Our expertise and techniques', '#059669'),
('Conseils', 'Tips', 'conseils', 'Conseils d\'utilisation', 'Usage tips', '#0891b2'),
('Portraits', 'Portraits', 'portraits', 'Témoignages et portraits', 'Testimonials and portraits', '#be123c');

-- Paramètres du site
INSERT INTO site_settings (setting_key, setting_value, setting_type, description) VALUES
('site_name', 'GIE Sokhna Maï', 'string', 'Nom du site'),
('site_email', 'gie.sokhnamai@gmail.com', 'string', 'Email de contact'),
('site_phone', '+221 77 446 04 74', 'string', 'Téléphone de contact'),
('site_address', 'Sénégal', 'string', 'Adresse'),
('whatsapp_number', '221774460474', 'string', 'Numéro WhatsApp (sans +)'),
('currency', 'FCFA', 'string', 'Devise'),
('maintenance_mode', '0', 'boolean', 'Mode maintenance'),
('items_per_page', '12', 'integer', 'Éléments par page');
