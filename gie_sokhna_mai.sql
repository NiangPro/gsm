-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 30 avr. 2026 à 16:01
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gie_sokhna_mai`
--

-- --------------------------------------------------------

--
-- Structure de la table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`details`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `name` varchar(100) NOT NULL,
  `role` enum('super_admin','admin','editor') DEFAULT 'admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `email`, `password_hash`, `name`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin@sokhnamai.sn', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrateur', 'super_admin', 1, NULL, '2026-04-16 11:20:19', '2026-04-16 11:20:19');

-- --------------------------------------------------------

--
-- Structure de la table `admin_settings`
--

CREATE TABLE `admin_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('profile','system','security') DEFAULT 'system',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin_settings`
--

INSERT INTO `admin_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `created_at`, `updated_at`) VALUES
(1, 'admin_name', 'Administrateur', 'profile', '2026-04-23 15:39:31', '2026-04-23 15:39:31'),
(2, 'admin_email', 'admin@sokhnamai.sn', 'profile', '2026-04-23 15:39:31', '2026-04-23 15:39:31'),
(3, 'admin_phone', '+221 77 446 04 74', 'profile', '2026-04-23 15:39:31', '2026-04-23 15:40:19'),
(4, 'site_name', 'GIE Sokhna Maï', 'system', '2026-04-23 15:39:31', '2026-04-23 15:39:31'),
(5, 'site_description', 'Produits naturels et artisanaux du Sénégal', 'system', '2026-04-23 15:39:31', '2026-04-23 15:39:31'),
(6, 'site_email', 'contact@sokhnamai.sn', 'system', '2026-04-23 15:39:31', '2026-04-23 15:39:31'),
(7, 'site_phone', '+221 77 446 04 74', 'system', '2026-04-23 15:39:31', '2026-04-23 15:42:10'),
(8, 'site_address', 'Dakar, Sénégal', 'system', '2026-04-23 15:39:31', '2026-04-23 15:39:31'),
(9, 'maintenance_mode', '0', 'system', '2026-04-23 15:39:31', '2026-04-23 15:39:31'),
(10, 'email_notifications', '1', 'security', '2026-04-23 15:39:31', '2026-04-23 15:39:31'),
(11, 'backup_frequency', 'daily', 'security', '2026-04-23 15:39:31', '2026-04-23 15:39:31'),
(12, 'session_timeout', '3600', 'security', '2026-04-23 15:39:31', '2026-04-23 15:39:31');

-- --------------------------------------------------------

--
-- Structure de la table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `email`, `password`, `name`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@sokhnamai.sn', '$2y$10$hj43O/dN1dUqxkW3u3hirOv9tnqERyyp7Ednej1PpI0lbVnqZZJHu', 'Administrateur', '2026-04-23 15:39:32', '2026-04-23 15:39:32');

-- --------------------------------------------------------

--
-- Structure de la table `blog_categories`
--

CREATE TABLE `blog_categories` (
  `id` int(11) NOT NULL,
  `name_fr` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#16a34a',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `blog_categories`
--

INSERT INTO `blog_categories` (`id`, `name_fr`, `name_en`, `slug`, `description_fr`, `description_en`, `color`, `is_active`, `created_at`) VALUES
(1, 'Santé', 'Health', 'sante', 'Articles sur la santé et le bien-être', 'Health and wellness articles', '#16a34a', 1, '2026-04-16 11:20:20'),
(2, 'Recettes', 'Recipes', 'recettes', 'Recettes et idées culinaires', 'Recipes and culinary ideas', '#d97706', 1, '2026-04-16 11:20:20'),
(3, 'Événements', 'Events', 'evenements', 'Actualités et événements du GIE', 'GIE news and events', '#7c3aed', 1, '2026-04-16 11:20:20'),
(4, 'Savoir-faire', 'Know-how', 'savoir-faire', 'Notre expertise et techniques', 'Our expertise and techniques', '#059669', 1, '2026-04-16 11:20:20'),
(5, 'Conseils', 'Tips', 'conseils', 'Conseils d\'utilisation', 'Usage tips', '#0891b2', 1, '2026-04-16 11:20:20'),
(6, 'Portraits', 'Portraits', 'portraits', 'Témoignages et portraits', 'Testimonials and portraits', '#be123c', 1, '2026-04-16 11:20:20');

-- --------------------------------------------------------

--
-- Structure de la table `blog_posts`
--

CREATE TABLE `blog_posts` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `title_fr` varchar(255) NOT NULL,
  `title_en` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt_fr` text DEFAULT NULL,
  `excerpt_en` text DEFAULT NULL,
  `content_fr` longtext DEFAULT NULL,
  `content_en` longtext DEFAULT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `emoji` varchar(10) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 0,
  `published_at` datetime DEFAULT NULL,
  `view_count` int(11) DEFAULT 0,
  `meta_title_fr` varchar(100) DEFAULT NULL,
  `meta_title_en` varchar(100) DEFAULT NULL,
  `meta_description_fr` varchar(255) DEFAULT NULL,
  `meta_description_en` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `blog_posts`
--

INSERT INTO `blog_posts` (`id`, `category_id`, `author_id`, `title_fr`, `title_en`, `slug`, `excerpt_fr`, `excerpt_en`, `content_fr`, `content_en`, `featured_image`, `emoji`, `is_published`, `published_at`, `view_count`, `meta_title_fr`, `meta_title_en`, `meta_description_fr`, `meta_description_en`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Les bienfaits du Moringa : un super aliment à découvrir', 'The benefits of Moringa: a superfood to discover', 'bienfaits-moringa-super-aliment', 'Le moringa est considéré comme l\'un des super aliments les plus complets. Riche en vitamines, minéraux et antioxydants, découvrez pourquoi il est surnommé \"l\'arbre miracle\".', 'Moringa is considered one of the most complete superfoods. Rich in vitamins, minerals and antioxidants, discover why it is called \"the miracle tree\".', '<p>Le moringa oleifera, souvent appelé \"l\'arbre miracle\", est une plante originaire d\'Inde mais qui pousse également très bien en Afrique de l\'Ouest. Ses feuilles sont d\'une valeur nutritionnelle exceptionnelle.</p><h2>Riche en nutriments</h2><p>Les feuilles de moringa contiennent :</p><ul><li>7 fois plus de vitamine C que les oranges</li><li>4 fois plus de calcium que le lait</li><li>4 fois plus de vitamine A que les carottes</li><li>3 fois plus de potassium que les bananes</li><li>2 fois plus de protéines que le yaourt</li></ul>', '<p>Moringa oleifera, often called the \"miracle tree\", is a plant native to India but also grows well in West Africa. Its leaves have exceptional nutritional value.</p><h2>Rich in nutrients</h2><p>Moringa leaves contain:</p><ul><li>7 times more vitamin C than oranges</li><li>4 times more calcium than milk</li><li>4 times more vitamin A than carrots</li><li>3 times more potassium than bananas</li><li>2 times more protein than yogurt</li></ul>', NULL, '🌿', 1, '2026-03-15 08:00:00', 0, NULL, NULL, NULL, NULL, '2026-03-15 00:00:00', '2026-04-16 11:21:00'),
(2, 2, 1, 'Recette : Smoothie Bouye-Mangue rafraîchissant', 'Recipe: Refreshing Bouye-Mango Smoothie', 'recette-smoothie-bouye-mangue', 'Préparez un délicieux smoothie en combinant notre jus de bouye avec de la mangue fraîche. Une recette simple et nutritive pour toute la famille.', 'Prepare a delicious smoothie by combining our bouye juice with fresh mango. A simple and nutritious recipe for the whole family.', '<h2>Ingrédients (pour 2 personnes)</h2><ul><li>250ml de jus de bouye</li><li>1 mangue mûre</li><li>1 banane</li><li>1 yaourt nature</li><li>Miel au goût</li><li>Glaçons</li></ul><h2>Préparation</h2><p>Mixez tous les ingrédients jusqu\'à obtenir une consistance lisse. Servez immédiatement bien frais.</p>', '<h2>Ingredients (for 2 people)</h2><ul><li>250ml of bouye juice</li><li>1 ripe mango</li><li>1 banana</li><li>1 plain yogurt</li><li>Honey to taste</li><li>Ice cubes</li></ul><h2>Preparation</h2><p>Blend all ingredients until smooth. Serve immediately while chilled.</p>', NULL, '🥤', 1, '2026-03-10 10:00:00', 0, NULL, NULL, NULL, NULL, '2026-03-10 00:00:00', '2026-04-16 11:21:00'),
(3, 3, 1, 'GIE Sokhna Maï à la Foire Internationale de Dakar', 'GIE Sokhna Maï at the Dakar International Fair', 'sokhna-mai-fidak-2025', 'Notre groupement a participé à la FIDAK cette année avec un stand dédié à nos produits transformés. Un succès retentissant !', 'Our group participated in FIDAK this year with a dedicated stand for our processed products. A resounding success!', '<p>Cette année, le GIE Sokhna Maï a eu l\'honneur de participer à la Foire Internationale de Dakar (FIDAK). Notre stand, situé dans le hall des produits locaux, a attiré de nombreux visiteurs curieux de découvrir nos confitures artisanales et nos jus naturels.</p><h2>Un succès commercial</h2><p>Au cours des 10 jours de la foire, nous avons :</p><ul><li>Vendu plus de 500 unités de produits</li><li>Établi 15 partenariats commerciaux</li><li>Recruté 3 nouveaux membres pour le GIE</li></ul>', '<p>This year, GIE Sokhna Maï had the honor of participating in the Dakar International Fair (FIDAK). Our stand, located in the local products hall, attracted many visitors curious to discover our artisanal jams and natural juices.</p><h2>A commercial success</h2><p>During the 10 days of the fair, we:</p><ul><li>Sold over 500 product units</li><li>Established 15 commercial partnerships</li><li>Recruited 3 new members for the GIE</li></ul>', NULL, '🎪', 1, '2026-03-05 14:00:00', 0, NULL, NULL, NULL, NULL, '2026-03-05 00:00:00', '2026-04-16 11:21:00'),
(4, 4, 1, 'Le Bissap : de la fleur au verre', 'Bissap: from flower to glass', 'bissap-fleur-au-verre', 'Découvrez comment nous transformons les fleurs d\'hibiscus en un délicieux jus de bissap. Le processus complet de fabrication artisanale.', 'Discover how we transform hibiscus flowers into a delicious bissap juice. The complete artisanal manufacturing process.', '<h2>Récolte</h2><p>Les fleurs d\'hibiscus sont récoltées à maturité dans les jardins de nos membres. Seules les fleurs les plus belles sont sélectionnées.</p><h2>Séchage</h2><p>Les fleurs sont séchées à l\'ombre pendant 3 à 5 jours pour préserver leurs propriétés.</p><h2>Infusion</h2><p>Les fleurs séchées sont infusées dans de l\'eau chaude pour extraire leur couleur et leurs saveurs.</p>', '<h2>Harvest</h2><p>Hibiscus flowers are harvested at maturity in our members\' gardens. Only the most beautiful flowers are selected.</p><h2>Drying</h2><p>The flowers are dried in the shade for 3 to 5 days to preserve their properties.</p><h2>Infusion</h2><p>The dried flowers are infused in hot water to extract their color and flavors.</p>', NULL, '🌺', 1, '2026-02-28 09:00:00', 0, NULL, NULL, NULL, NULL, '2026-02-28 00:00:00', '2026-04-16 11:21:00'),
(5, 5, 1, '5 façons d\'utiliser le sirop de gingembre au quotidien', '5 ways to use ginger syrup daily', '5-facons-sirop-gingembre', 'Au-delà de la simple boisson, le sirop de gingembre peut agrémenter vos plats, desserts et soins. Découvrez nos astuces !', 'Beyond a simple drink, ginger syrup can enhance your dishes, desserts and wellness routines. Discover our tips!', '<h2>1. En boisson chaude</h2><p>Ajoutez 2 cuillères à soupe dans une tasse d\'eau chaude pour une infusion revigorante.</p><h2>2. Dans les vinaigrettes</h2><p>Mélangez avec du vinaigre et de l\'huile pour une vinaigrette épicée.</p><h2>3. Sur les desserts</h2><p>Arrosez une boule de glace vanille pour un dessert original.</p><h2>4. Dans les marinades</h2><p>Utilisez comme base pour mariner viandes et poissons.</p><h2>5. Pour la santé</h2><p>Prenez une cuillère pure au réveil pour stimuler votre digestion.</p>', '<h2>1. As a hot drink</h2><p>Add 2 tablespoons to a cup of hot water for an invigorating infusion.</p><h2>2. In salad dressings</h2><p>Mix with vinegar and oil for a spicy dressing.</p><h2>3. On desserts</h2><p>Drizzle over vanilla ice cream for an original dessert.</p><h2>4. In marinades</h2><p>Use as a base for marinating meats and fish.</p><h2>5. For health</h2><p>Take a spoonful pure upon waking to stimulate digestion.</p>', NULL, '🫚', 1, '2026-02-20 11:00:00', 0, NULL, NULL, NULL, NULL, '2026-02-20 00:00:00', '2026-04-16 11:21:00'),
(6, 6, 1, 'Témoignage : Awa, femme entrepreneure du GIE', 'Testimony: Awa, woman entrepreneur of the GIE', 'temoignage-awa-entrepreneure', 'Awa nous raconte son parcours au sein du GIE Sokhna Maï et comment la transformation des produits locaux a changé sa vie.', 'Awa tells us about her journey within GIE Sokhna Maï and how local product transformation changed her life.', '<p>Awa Diallo, 35 ans, mère de trois enfants, a rejoint le GIE Sokhna Maï il y a deux ans. Aujourd\'hui, elle dirige l\'unité de production des confitures.</p><h2>Un parcours inspirant</h2><p>\"Avant de rejoindre le GIE, je vendais des fruits sur le marché. Les revenus étaient irréguliers. Depuis que j\'ai appris la transformation, mes revenus ont triplé et je peux payer les études de mes enfants.\"</p><h2>Formation et autonomie</h2><p>Le GIE offre une formation continue à tous ses membres. Awa a appris la fabrication des confitures, la gestion des stocks et même les bases de la comptabilité.</p>', '<p>Awa Diallo, 35 years old, mother of three children, joined GIE Sokhna Maï two years ago. Today, she heads the jam production unit.</p><h2>An inspiring journey</h2><p>\"Before joining the GIE, I sold fruit at the market. Income was irregular. Since I learned processing, my income has tripled and I can pay for my children\'s education.\"</p><h2>Training and autonomy</h2><p>The GIE offers continuous training to all its members. Awa learned how to make jams, manage inventory, and even basic accounting.</p>', NULL, '👩🏾', 1, '2026-02-15 08:30:00', 0, NULL, NULL, NULL, NULL, '2026-02-15 00:00:00', '2026-04-16 11:21:00');

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name_fr` varchar(100) NOT NULL,
  `name_en` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `color` varchar(7) DEFAULT '#16a34a',
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `parent_id` int(11) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name_fr`, `name_en`, `slug`, `description_fr`, `description_en`, `icon`, `color`, `display_order`, `is_active`, `created_at`, `updated_at`, `parent_id`, `is_available`) VALUES
(1, 'Confitures', 'Jams', 'confitures', 'Confitures artisanales préparées avec des fruits frais du Sénégal', 'Artisanal jams made with fresh Senegalese fruits', 'fa-jar', '#16a34a', 1, 1, '2026-04-16 11:20:20', '2026-04-22 12:59:15', 1, 1),
(2, 'Jus', 'Juices', 'jus', 'Jus naturels pressés à froid', 'Cold-pressed natural juices', 'fa-glass-whiskey', '#d97706', 2, 1, '2026-04-16 11:20:20', '2026-04-22 12:59:05', 2, 1),
(3, 'Sirops', 'Syrups', 'sirops', 'Sirops concentrés pour boissons rafraîchissantes', 'Concentrated syrups for refreshing drinks', 'fa-wine-bottle', '#7c3aed', 3, 1, '2026-04-16 11:20:20', '2026-04-22 12:58:56', 3, 1),
(4, 'Céréales', 'Cereals', 'cereales', 'Céréales et poudres nutritives', 'Nutritious cereals and powders', 'fa-seedling', '#059669', 4, 1, '2026-04-16 11:20:20', '2026-04-22 12:58:46', 4, 1),
(6, 'Fruits Séchés', 'Dried Fruits', '', 'Texture Moelleuse. Sucré et tendre avec des notes tropicales intense', 'Soft texture. Sweet and tender with intense tropical notes', NULL, '#16a34a', 5, 1, '2026-04-22 12:51:36', '2026-04-22 12:58:24', 6, 1);

-- --------------------------------------------------------

--
-- Structure de la table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `replied_by` int(11) DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `reply_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `phone`, `subject`, `message`, `ip_address`, `is_read`, `replied_by`, `replied_at`, `reply_message`, `created_at`) VALUES
(1, 'Jean Dupont', 'jean.dupont@example.com', '+221 77 111 22 33', 'Demande de partenariat', 'Bonjour, je souhaite devenir distributeur de vos produits dans la région de Thiès. Pouvons-nous organiser un rendez-vous ?', '192.168.1.1', 0, NULL, NULL, NULL, '2024-01-20 09:15:00'),
(2, 'Marie Curie', 'marie.curie@example.com', NULL, 'Question sur les confitures', 'Quelle est la durée de conservation de vos confitures ? Sont-elles sans conservateurs ?', '192.168.1.2', 1, NULL, NULL, NULL, '2024-01-19 14:30:00'),
(3, 'Pierre Martin', 'pierre.martin@example.com', '+221 76 444 55 66', 'Commande en gros', 'Je souhaite passer une commande de 50 pots de confiture pour un événement. Avez-vous des tarifs dégressifs ?', '192.168.1.3', 0, NULL, NULL, NULL, '2024-01-18 11:00:00'),
(4, 'Jus de Moringa', 'admin@sokhnamai.sn', '774002020', 'avis sur une commande reçue', 'zhhdhsdshhshhjhjds', NULL, 0, NULL, NULL, NULL, '2026-04-22 11:06:20'),
(5, 'Awa Sarr', 'sarrawa12@gmail.com', '774002020', 'avis sur une commande reçue', 'cfgiojgvgoipj vhbbu', NULL, 1, NULL, '2026-04-22 11:20:35', NULL, '2026-04-22 11:14:48'),
(6, 'khadim gniome', 'niombamba55@gmail.com', '776238564', 'JE VEUX COMMANDER', 'Je voulais savoir s\'il y\'a possibilité de passer une grosse commande et le recevoir demain dans la matinée si possible.', NULL, 1, 1, '2026-04-22 11:26:24', 'Bonjour Mr oui bien sur c\'est possible. Je vous prie de nous contacter', '2026-04-22 11:24:13'),
(7, 'Saliou NDIAYE', 'ndiayesaliou05@gmail.com', '774022002', 'Avis sur vos produits', 'Je trouve vos produits intéressants', NULL, 1, NULL, '2026-04-22 12:03:57', NULL, '2026-04-22 12:00:49'),
(8, 'Khadim Gniome', 'niombamba55@gmail.com', '774000220', 'JE VEUX COMMANDER ET TESTER TOUS VOS PRODUITS', 'DVJC DBCDDNNCDC SBHDSBCNSDJCDS. CDS HCBDSJCLKDSC CSBHCSCKNC?C', NULL, 0, NULL, NULL, NULL, '2026-04-30 13:48:30'),
(9, 'Khadim Gniome', 'niombamba55@gmail.com', '774000220', 'dvbng cvdb', 'sdezfg tnklolo:l', NULL, 0, NULL, NULL, NULL, '2026-04-30 13:54:43'),
(10, 'Aliou FALL', 'fallaliou05@gmail.com', '774000220', 'sfbhhn,y tbtyn,yiku', 'vbnjg bgn, j,itiugj', NULL, 0, NULL, NULL, NULL, '2026-04-30 13:57:12'),
(11, 'Aliou FALL', 'fallaliou05@gmail.com', '774000220', 'sfbhhn,y tbtyn,yiku', 'vbnjg bgn, j,itiugj', NULL, 0, NULL, NULL, NULL, '2026-04-30 13:57:14');

-- --------------------------------------------------------

--
-- Structure de la table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `password_hash` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `address`, `city`, `notes`, `created_at`, `updated_at`, `password_hash`, `is_active`) VALUES
(1, 'Fatou Diallo', '+221 77 123 45 67', 'fatou@email.com', '123 Rue de la Paix', 'Dakar', 'Cliente régulière', '2024-01-10 00:00:00', '2026-04-16 11:21:00', NULL, 1),
(2, 'Amadou Sow', '+221 76 234 56 78', NULL, '456 Avenue Faidherbe', 'Thiès', 'Préfère les livraisons le weekend', '2024-01-11 00:00:00', '2026-04-16 11:21:00', NULL, 1),
(3, 'Marie Ndiaye', '+221 70 345 67 89', 'marie@email.com', '789 Boulevard du Centenaire', 'Dakar', '', '2024-01-12 00:00:00', '2026-04-16 11:21:00', NULL, 1),
(4, 'Ousmane Ba', '+221 78 456 78 90', NULL, '321 Rue des Almadies', 'Dakar', 'Client grossiste', '2024-01-13 00:00:00', '2026-04-16 11:21:00', NULL, 1),
(5, 'Aïda Fall', '+221 76 567 89 01', 'aida@email.com', '654 Avenue Cheikh Anta Diop', 'Rufisque', '', '2024-01-14 00:00:00', '2026-04-16 11:21:00', NULL, 1),
(6, 'khadim gniome', '776238564', NULL, 'UCAD', NULL, 'Avoir la livraison aujourd\'hui si possible', '2026-04-16 14:15:21', '2026-04-17 11:01:02', NULL, 1),
(7, 'Amy NIOME', '774002020', NULL, 'qzsedtgyjkolpkjh', NULL, '', '2026-04-20 17:20:01', '2026-04-28 13:41:07', NULL, 1),
(25, 'Awa Sarr', '+221 77 400 20 20', NULL, 'V FB HBHNJ', NULL, 'GGVGBHJ? HYNJN HBHHY', '2026-04-28 13:46:17', '2026-04-28 13:57:29', NULL, 1),
(26, 'Aliou FALL', '+221774000220', 'fallaliou05@gmail.com', 'FASS MEDINA', 'DAKAR', '', '2026-04-29 15:29:46', '2026-04-30 13:46:02', '$2y$10$84v/gDwx72PjqcUQVdEhp.cnrlg/VsnpyO8BL8lUJhaGGGDlIl3C6', 1),
(27, 'Aliou FALL', '774000220', NULL, 'FASS MEDINA', NULL, 'CGHDZHCJ GHCJKLCM BHJKLL', '2026-04-30 10:49:23', '2026-04-30 10:50:57', NULL, 1);

-- --------------------------------------------------------

--
-- Structure de la table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `status` enum('En attente','Confirmée','En préparation','En livraison','Livrée','Annulée') DEFAULT 'En attente',
  `total_amount` decimal(12,2) NOT NULL,
  `shipping_cost` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(12,2) NOT NULL,
  `delivery_address` text DEFAULT NULL,
  `delivery_city` varchar(100) DEFAULT NULL,
  `delivery_notes` text DEFAULT NULL,
  `payment_method` enum('Espèces','Mobile Money','Carte','Virement') DEFAULT 'Espèces',
  `payment_status` enum('En attente','Payée','Partielle','Remboursée') DEFAULT 'En attente',
  `whatsapp_sent` tinyint(1) DEFAULT 0,
  `processed_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `orders`
--

INSERT INTO `orders` (`id`, `order_number`, `customer_id`, `status`, `total_amount`, `shipping_cost`, `final_amount`, `delivery_address`, `delivery_city`, `delivery_notes`, `payment_method`, `payment_status`, `whatsapp_sent`, `processed_by`, `created_at`, `updated_at`) VALUES
(1, 'CMD-2024-001', 1, 'Livrée', 6000.00, 0.00, 0.00, '123 Rue de la Paix', 'Dakar', '', 'Mobile Money', 'Payée', 1, 1, '2024-01-15 10:30:00', '2026-04-16 11:21:00'),
(2, 'CMD-2024-002', 2, 'En préparation', 7500.00, 0.00, 0.00, '456 Avenue Faidherbe', 'Thiès', 'Livraison express', 'Espèces', 'En attente', 0, 1, '2024-01-15 14:20:00', '2026-04-16 11:21:00'),
(3, 'CMD-2024-003', 3, 'En attente', 3000.00, 0.00, 0.00, '789 Boulevard du Centenaire', 'Dakar', '', 'Mobile Money', 'En attente', 0, 1, '2024-01-16 09:15:00', '2026-04-16 11:21:00'),
(4, 'CMD-2024-004', 4, 'Confirmée', 12000.00, 0.00, 0.00, '321 Rue des Almadies', 'Dakar', '', 'Virement', 'Payée', 1, 1, '2024-01-16 11:45:00', '2026-04-16 11:21:00'),
(5, 'CMD-2024-005', 5, 'En livraison', 5000.00, 0.00, 0.00, '654 Avenue Cheikh Anta Diop', 'Rufisque', '', 'Mobile Money', 'Payée', 1, 1, '2024-01-16 15:30:00', '2026-04-16 11:21:00'),
(6, 'CMD-2026-1611', 6, 'En attente', 3000.00, 0.00, 0.00, 'UCAD', NULL, 'BBFHFHF BHHRER', 'Espèces', 'En attente', 0, NULL, '2026-04-16 14:15:21', '2026-04-16 14:15:21'),
(7, 'CMD-2026-4942', 6, 'En attente', 10000.00, 0.00, 0.00, 'UCAD', NULL, 'Si possible recevoir la commande aujourd\'hui', 'Espèces', 'En attente', 0, NULL, '2026-04-17 10:29:28', '2026-04-17 10:29:28'),
(8, 'CMD-2026-5039', 6, 'En attente', 10000.00, 0.00, 0.00, 'UCAD', NULL, 'Si possible recevoir la commande aujourd\'hui', 'Espèces', 'En attente', 0, NULL, '2026-04-17 10:36:04', '2026-04-17 10:36:04'),
(9, 'CMD-2026-6854', 6, 'En attente', 9600.00, 0.00, 0.00, 'UCAD', NULL, 'Recevoir la commande aujourd\'hui si possible', 'Espèces', 'En attente', 0, NULL, '2026-04-17 10:37:42', '2026-04-17 10:37:42'),
(10, 'CMD-2026-7716', 6, 'En attente', 6400.00, 0.00, 0.00, 'UCAD', NULL, 'Avoir la livraison aujourd\'hui si possible', 'Espèces', 'En attente', 0, NULL, '2026-04-17 11:01:02', '2026-04-17 11:01:02'),
(11, 'CMD-2026-4046', 7, 'En attente', 0.00, 0.00, 0.00, 'FASS MEDINA', NULL, '', 'Espèces', 'En attente', 0, NULL, '2026-04-20 17:20:01', '2026-04-20 17:20:01'),
(20, 'CMD-2026-8167', 7, 'En attente', 5000.00, 1500.00, 6500.00, 'qzsedtgyjkolpkjh', NULL, '', 'Espèces', 'En attente', 0, NULL, '2026-04-28 13:41:07', '2026-04-28 13:41:07'),
(21, 'CMD-2026-6298', 25, 'En attente', 5000.00, 1500.00, 6500.00, 'dgfhh bj', NULL, 'fcgn hk,,oiçuhjbjgh yghb', 'Espèces', 'En attente', 0, NULL, '2026-04-28 13:46:17', '2026-04-28 13:46:17'),
(22, 'CMD-2026-7228', 25, 'En attente', 5000.00, 1500.00, 6500.00, 'V FB HBHNJ', NULL, 'GGVGBHJ? HYNJN HBHHY', 'Espèces', 'En attente', 0, NULL, '2026-04-28 13:57:29', '2026-04-28 13:57:29'),
(23, 'CMD-2026-6969', 27, 'En attente', 10000.00, 0.00, 10000.00, 'FASS MEDINA', NULL, 'GHSHCJKDC CBGHCJJDKL', 'Espèces', 'En attente', 0, NULL, '2026-04-30 10:49:23', '2026-04-30 10:49:23'),
(24, 'CMD-2026-1661', 27, 'En attente', 7500.00, 0.00, 7500.00, 'FASS MEDINA', NULL, 'CGHDZHCJ GHCJKLCM BHJKLL', 'Espèces', 'En attente', 0, NULL, '2026-04-30 10:50:57', '2026-04-30 10:50:57'),
(25, 'CMD-2026-2950', 26, 'En attente', 7500.00, 0.00, 7500.00, 'FASS MEDINA', NULL, 'vsq sbvxbsj', 'Espèces', 'En attente', 0, NULL, '2026-04-30 11:22:50', '2026-04-30 11:22:50'),
(26, 'CMD-2026-6752', 26, 'En attente', 24000.00, 0.00, 24000.00, 'FASS MEDINA', NULL, 'je veux la livraison aujourd\'hui si possible', 'Espèces', 'En attente', 0, NULL, '2026-04-30 12:56:55', '2026-04-30 12:56:55'),
(27, 'CMD-2026-2833', 26, 'En attente', 5000.00, 1500.00, 6500.00, 'FASS MEDINA', NULL, 'sdsgfdh dsb,hg', 'Espèces', 'En attente', 0, NULL, '2026-04-30 13:10:16', '2026-04-30 13:10:16'),
(28, 'CMD-2026-1655', 26, 'En attente', 11500.00, 0.00, 11500.00, 'FASS MEDINA', NULL, '', 'Espèces', 'En attente', 0, NULL, '2026-04-30 13:46:02', '2026-04-30 13:46:02');

-- --------------------------------------------------------

--
-- Structure de la table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `unit_price`, `weight`, `subtotal`, `notes`, `created_at`) VALUES
(1, 1, 8, '', 2, 3000.00, '500ml', 6000.00, '', '2026-04-16 11:21:00'),
(2, 2, 1, '', 3, 2500.00, '250g', 7500.00, 'Cadeau pour anniversaire', '2026-04-16 11:21:00'),
(3, 3, 10, '', 1, 3000.00, '500ml', 3000.00, '', '2026-04-16 11:21:00'),
(4, 4, 8, '', 4, 3000.00, '500ml', 12000.00, 'Commande pour événement', '2026-04-16 11:21:00'),
(5, 5, 12, '', 2, 2500.00, '250g', 5000.00, '', '2026-04-16 11:21:00'),
(6, 6, 1, '', 2, 1500.00, NULL, 3000.00, 'BBFHFHF BHHRER', '2026-04-16 14:15:21'),
(7, 7, 1, '', 4, 2500.00, NULL, 10000.00, 'Si possible recevoir la commande aujourd\'hui', '2026-04-17 10:29:28'),
(8, 8, 1, '', 4, 2500.00, NULL, 10000.00, 'Si possible recevoir la commande aujourd\'hui', '2026-04-17 10:36:04'),
(9, 9, 1, '', 3, 3200.00, NULL, 9600.00, 'Recevoir la commande aujourd\'hui si possible', '2026-04-17 10:37:42'),
(10, 10, 1, '', 2, 3200.00, NULL, 6400.00, 'Avoir la livraison aujourd\'hui si possible', '2026-04-17 11:01:02'),
(11, 11, 1, '', 3, 0.00, NULL, 0.00, '', '2026-04-20 17:20:01'),
(12, 20, 22, 'Confiture de Papaye', 1, 2500.00, NULL, 2500.00, '', '2026-04-28 13:41:07'),
(13, 20, 4, 'Confiture de Tamarin', 1, 2500.00, NULL, 2500.00, '', '2026-04-28 13:41:07'),
(14, 21, 22, 'Confiture de Papaye', 1, 2500.00, NULL, 2500.00, 'fcgn hk,,oiçuhjbjgh yghb', '2026-04-28 13:46:17'),
(15, 21, 4, 'Confiture de Tamarin', 1, 2500.00, NULL, 2500.00, 'fcgn hk,,oiçuhjbjgh yghb', '2026-04-28 13:46:17'),
(16, 22, 22, 'Confiture de Papaye', 1, 2500.00, NULL, 2500.00, 'GGVGBHJ? HYNJN HBHHY', '2026-04-28 13:57:29'),
(17, 22, 4, 'Confiture de Tamarin', 1, 2500.00, NULL, 2500.00, 'GGVGBHJ? HYNJN HBHHY', '2026-04-28 13:57:29'),
(18, 23, 22, 'Confiture de Papaye', 1, 2500.00, NULL, 2500.00, 'GHSHCJKDC CBGHCJJDKL', '2026-04-30 10:49:23'),
(19, 23, 4, 'Confiture de Tamarin', 1, 2500.00, NULL, 2500.00, 'GHSHCJKDC CBGHCJJDKL', '2026-04-30 10:49:23'),
(20, 23, 5, 'Confiture de Citron', 1, 2500.00, NULL, 2500.00, 'GHSHCJKDC CBGHCJJDKL', '2026-04-30 10:49:23'),
(21, 23, 1, 'Confiture de Mangue', 1, 2500.00, NULL, 2500.00, 'GHSHCJKDC CBGHCJJDKL', '2026-04-30 10:49:23'),
(22, 24, 7, 'Confiture de Baobab', 1, 2500.00, NULL, 2500.00, 'CGHDZHCJ GHCJKLCM BHJKLL', '2026-04-30 10:50:57'),
(23, 24, 5, 'Confiture de Citron', 1, 2500.00, NULL, 2500.00, 'CGHDZHCJ GHCJKLCM BHJKLL', '2026-04-30 10:50:57'),
(24, 24, 1, 'Confiture de Mangue', 1, 2500.00, NULL, 2500.00, 'CGHDZHCJ GHCJKLCM BHJKLL', '2026-04-30 10:50:57'),
(25, 25, 7, 'Confiture de Baobab', 1, 2500.00, NULL, 2500.00, 'vsq sbvxbsj', '2026-04-30 11:22:50'),
(26, 25, 5, 'Confiture de Citron', 1, 2500.00, NULL, 2500.00, 'vsq sbvxbsj', '2026-04-30 11:22:50'),
(27, 25, 1, 'Confiture de Mangue', 1, 2500.00, NULL, 2500.00, 'vsq sbvxbsj', '2026-04-30 11:22:50'),
(28, 26, 7, 'Confiture de Baobab', 2, 2500.00, NULL, 5000.00, 'je veux la livraison aujourd\'hui si possible', '2026-04-30 12:56:55'),
(29, 26, 46, 'COCO, GINGEMBRE', 2, 1500.00, NULL, 3000.00, 'je veux la livraison aujourd\'hui si possible', '2026-04-30 12:56:55'),
(30, 26, 10, 'Jus de Gingembre', 1, 1500.00, NULL, 1500.00, 'je veux la livraison aujourd\'hui si possible', '2026-04-30 12:56:55'),
(31, 26, 14, 'Céréales : Mil, Mais, Sorgho...', 1, 2000.00, NULL, 2000.00, 'je veux la livraison aujourd\'hui si possible', '2026-04-30 12:56:55'),
(32, 26, 5, 'Confiture de Citron', 1, 2500.00, NULL, 2500.00, 'je veux la livraison aujourd\'hui si possible', '2026-04-30 12:56:55'),
(33, 26, 4, 'Confiture de Tamarin', 1, 2500.00, NULL, 2500.00, 'je veux la livraison aujourd\'hui si possible', '2026-04-30 12:56:55'),
(34, 26, 22, 'Confiture de Papaye', 1, 2500.00, NULL, 2500.00, 'je veux la livraison aujourd\'hui si possible', '2026-04-30 12:56:55'),
(35, 26, 3, 'Confiture de Pamplemousse', 1, 2500.00, NULL, 2500.00, 'je veux la livraison aujourd\'hui si possible', '2026-04-30 12:56:55'),
(36, 26, 1, 'Confiture de Mangue', 1, 2500.00, NULL, 2500.00, 'je veux la livraison aujourd\'hui si possible', '2026-04-30 12:56:55'),
(37, 27, 22, 'Confiture de Papaye', 1, 2500.00, NULL, 2500.00, 'sdsgfdh dsb,hg', '2026-04-30 13:10:16'),
(38, 27, 4, 'Confiture de Tamarin', 1, 2500.00, NULL, 2500.00, 'sdsgfdh dsb,hg', '2026-04-30 13:10:16'),
(39, 28, 19, 'Jus de Bouye', 1, 1500.00, NULL, 1500.00, '', '2026-04-30 13:46:02'),
(40, 28, 20, 'Sirop de Tamarin', 1, 5000.00, NULL, 5000.00, '', '2026-04-30 13:46:02'),
(41, 28, 1, 'Confiture de Mangue', 1, 2500.00, NULL, 2500.00, '', '2026-04-30 13:46:02'),
(42, 28, 3, 'Confiture de Pamplemousse', 1, 2500.00, NULL, 2500.00, '', '2026-04-30 13:46:02');

-- --------------------------------------------------------

--
-- Structure de la table `partners`
--

CREATE TABLE `partners` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `name_en` varchar(150) DEFAULT NULL,
  `type` enum('Partenaire','Événement','Sponsoring','Collaboration') DEFAULT 'Partenaire',
  `year` varchar(4) DEFAULT NULL,
  `date_fr` varchar(50) DEFAULT NULL,
  `date_en` varchar(50) DEFAULT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `website_url` varchar(255) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `partners`
--

INSERT INTO `partners` (`id`, `name`, `name_en`, `type`, `year`, `date_fr`, `date_en`, `description_fr`, `description_en`, `logo_url`, `website_url`, `display_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Foire de l\'Innovation', 'Innovation Fair', 'Événement', '2021', 'Décembre 2021', 'December 2021', 'Première participation à une foire majeure pour présenter nos produits au grand public.', 'First participation in a major fair to present our products to the general public.', 'foire-innovation.png', NULL, 1, 1, '2026-04-16 11:21:00', '2026-04-16 11:21:00'),
(2, 'Marché des Producteurs Locaux', 'Local Producers Market', 'Partenaire', '2022', 'Tous les samedis', 'Every Saturday', 'Présence régulière sur le marché des producteurs locaux de Dakar.', 'Regular presence at the local producers market in Dakar.', NULL, NULL, 2, 1, '2026-04-16 11:21:00', '2026-04-16 11:21:00'),
(3, 'HUMASOL', 'HUMASOL', 'Partenaire', '2023', 'Partenariat depuis 2023', 'Partnership since 2023', 'Collaboration avec HUMASOL pour le développement de projets solaires dans nos unités de production.', 'Collaboration with HUMASOL for the development of solar projects in our production units.', 'humasol-logo.png', 'https://humasol.be', 3, 1, '2026-04-16 11:21:00', '2026-04-16 11:21:00');

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `ingredients_fr` varchar(500) DEFAULT NULL,
  `ingredients_en` varchar(500) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `weight` varchar(20) DEFAULT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_available` tinyint(1) DEFAULT 1,
  `meta_title_fr` varchar(100) DEFAULT NULL,
  `meta_title_en` varchar(100) DEFAULT NULL,
  `meta_description_fr` varchar(255) DEFAULT NULL,
  `meta_description_en` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description_fr`, `description_en`, `ingredients_fr`, `ingredients_en`, `price`, `weight`, `stock_quantity`, `image_url`, `is_featured`, `is_available`, `meta_title_fr`, `meta_title_en`, `meta_description_fr`, `meta_description_en`, `created_at`, `updated_at`) VALUES
(1, 1, 'Confiture de Mangue', 'confiture-de-mangue', 'Confiture artisanale préparée avec des mangues fraîches du Sénégal. Un délice sucré au goût authentique.', 'Artisanal jam made with fresh Senegalese mangoes. A sweet delight with authentic taste.', 'Mangue, sucre, citron', 'Mango, sugar, lemon', 2500.00, '250g', 50, 'confiture-mangue.jpg', 1, 1, NULL, NULL, NULL, NULL, '2024-01-15 00:00:00', '2026-04-16 11:21:00'),
(3, 1, 'Confiture de Pamplemousse', 'confiture-de-pamplemousse', 'Confiture acidulée au pamplemousse, idéale pour le petit-déjeuner.', 'Tangy grapefruit jam, ideal for breakfast.', 'Pamplemousse, sucre', 'Grapefruit, sugar', 2500.00, '250g', 25, 'confiture-pomplemousse.jpg', 0, 1, NULL, NULL, NULL, NULL, '2024-01-15 00:00:00', '2026-04-25 13:35:01'),
(4, 1, 'Confiture de Tamarin', 'confiture-de-tamarin', 'Confiture exotique au tamarin, un goût unique qui surprendra vos papilles.', 'Exotic tamarind jam, a unique taste that will surprise your taste buds.', 'Tamarin, sucre, épices', 'Tamarind, sugar, spices', 2500.00, '250g', 20, 'confiture-tamarin.jpg', 0, 1, NULL, NULL, NULL, NULL, '2024-01-15 00:00:00', '2026-04-16 11:21:00'),
(5, 1, 'Confiture de Citron', 'confiture-de-citron', 'Confiture rafraîchissante au citron, parfaite pour accompagner vos thés.', 'Refreshing lemon jam, perfect to accompany your teas.', 'Citron, sucre, gingembre', 'Lemon, sugar, ginger', 2500.00, '250g', 35, 'confiture-citron.jpg', 0, 1, NULL, NULL, NULL, NULL, '2024-01-15 00:00:00', '2026-04-25 13:45:20'),
(7, 1, 'Confiture de Baobab', 'confiture-de-baobab', 'Confiture originale au fruit de baobab, riche en vitamine C.', 'Original jam made with baobab fruit, rich in vitamin C.', 'Pulpe de baobab, sucre', 'Baobab pulp, sugar', 2500.00, '250g', 38, 'confiture-baobab.jpg', 1, 1, NULL, NULL, NULL, NULL, '2024-01-15 00:00:00', '2026-04-25 13:35:23'),
(8, 2, 'Jus de Bissap Hibiscus', 'jus-de-bissap-hibiscus', 'Jus traditionnel à base d\'hibiscus, rafraîchissant et riche en antioxydants.', 'Traditional hibiscus-based juice, refreshing and rich in antioxidants.', 'Bissap, sucre, eau', 'Hibiscus, sugar, water', 1500.00, '500ml', 100, 'jus-bissap-JUS_BI02.jpg', 1, 1, NULL, NULL, NULL, NULL, '2024-01-15 00:00:00', '2026-04-28 11:58:35'),
(10, 2, 'Jus de Gingembre', 'jus-de-gingembre', 'Jus de gingembre piquant et revigorant, excellent pour la santé.', 'Spicy and invigorating ginger juice, excellent for health.', 'Gingembre, citron, sucre, eau', 'Ginger, lemon, sugar, water', 1500.00, '500ml', 90, 'jus-gingembre-JUS_GI02.jpg', 0, 1, NULL, NULL, NULL, NULL, '2024-01-15 00:00:00', '2026-04-21 12:19:22'),
(12, 3, 'Sirop de Bissap Hibiscus', 'sirop-de-bissap-hibiscus', 'Sirop concentré de bissap pour préparer de délicieuses boissons.', 'Concentrated hibiscus syrup for preparing delicious drinks.', 'Bissap, sucre, citron', 'Hibiscus, sugar, lemon', 5000.00, '500ml', 60, 'https://myesteval.com/wp-content/uploads/2016/05/SIROP-DE-BISSAP-HIBISCUS.jpg', 0, 1, NULL, NULL, NULL, NULL, '2024-01-15 00:00:00', '2026-04-28 11:59:11'),
(14, 4, 'Céréales : Mil, Mais, Sorgho...', 'c-r-ales-mil-mais-sorgho', 'Graines de Mil, Mais, Sorgho riches en oméga-3 et fibres.', 'Golden flax seeds, rich in omega-3 and fiber.', 'Mil, Mais, Sorgho', 'Flax seeds', 2000.00, '1kg', 50, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRVVpNajJ3jx-r_Xky6O8j6abUvJlKEZUxYmw&s', 0, 1, NULL, NULL, NULL, NULL, '2024-01-15 00:00:00', '2026-04-29 12:51:37'),
(15, 3, 'Sirop de mangue', 'sirop-de-mangue', 'Préparer avec précautions dans tous les normes de la qualité. Pret a etre utiliser pour vos jus smoothies', NULL, 'Mangue et Sucre', NULL, 5000.00, '1l', 50, 'https://boutiquedumonastere.com/client/cache/produit/628_775______sirop-mangue_109.png', 0, 1, NULL, NULL, NULL, NULL, '2026-04-17 12:52:21', '2026-04-29 12:49:09'),
(19, 2, 'Jus de Bouye', 'jus-bouye', 'Jus de baobab riche en vitamine C, naturellement sucré.', 'Baobab juice rich in vitamin C, naturally sweetened.', 'Pulpe de baobab, eau, sucre, citron', 'Baobab pulp, water, sugar, lemon', 1500.00, '1L', 40, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR4aKwn8bOK5tRBMDvAD7tMeEBkCgf6JFtFBw&s', 0, 1, NULL, NULL, NULL, NULL, '2026-04-17 16:37:02', '2026-04-29 12:45:53'),
(20, 3, 'Sirop de Tamarin', 'sirop-tamarin', 'Sirop sucré et acidulé à base de fruits de tamarin.', 'Sweet and tangy syrup made from tamarind fruits.', 'Fruits de tamarin, sucre, eau, cardamome', 'Tamarind fruits, sugar, water, cardamom', 5000.00, '1l', 25, 'https://myesteval.com/wp-content/uploads/2016/05/SIROP-DE-TAMARIN.jpg', 0, 1, NULL, NULL, NULL, NULL, '2026-04-17 16:37:02', '2026-04-29 12:46:21'),
(22, 1, 'Confiture de Papaye', 'confiture-papaye', 'Confiture douce et parfumée à la papaye mûre.', 'Sweet and fragrant papaya jam.', 'Papaye, sucre, jus de citron, gousse de vanille', 'Papaya, sugar, lemon juice, vanilla pod', 2500.00, '250g', 45, 'https://sunualimentation.com/wp-content/uploads/2019/03/confiture-papaye-CON_PA03.jpg', 0, 1, NULL, NULL, NULL, NULL, '2026-04-17 16:37:02', '2026-04-30 13:02:37'),
(23, 2, 'Jus de Ditakh', 'jus-ditakh', 'Jus traditionnel sénégalais à base de fruit du baobab.', 'Traditional Senegalese juice made from baobab fruit.', 'Pulpe de ditakh, eau, sucre, arôme naturel', 'Ditakh pulp, water, sugar, natural flavor', 1500.00, '1L', 25, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSdJrxiRU9Ae8JLL5J3B7Jj-mL6tDbez-ILXA&s', 0, 1, NULL, NULL, NULL, NULL, '2026-04-17 16:37:02', '2026-04-29 12:46:49'),
(24, 3, 'Sirop de Gingembre', 'sirop-gingembre', 'Sirop concentré au gingembre, parfait pour les boissons chaudes.', 'Concentrated ginger syrup, perfect for hot drinks.', 'Gingembre frais, sucre, eau, citron', 'Fresh ginger, sugar, water, lemon', 5000.00, '1l', 40, 'https://myesteval.com/wp-content/uploads/2016/05/SIROP-DE-GINGEMBRE.jpg', 0, 1, NULL, NULL, NULL, NULL, '2026-04-17 16:37:02', '2026-04-29 12:48:26'),
(34, 2, 'Jus de Tamarin', 'jus-tamarin', 'Jus sucré et acidulé de tamarin, rafraîchissant.', 'Sweet and tangy tamarind juice, refreshing.', 'Fruits de tamarin, eau, sucre, glace', 'Tamarind fruits, water, sugar, ice', 1500.00, '1L', 28, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQTTklcwf4upz27ZQeFq-5FshrngpC0LQuSAw&s', 0, 1, NULL, NULL, NULL, NULL, '2026-04-17 16:37:02', '2026-04-29 12:48:48'),
(43, 2, 'Jus de Moringa', 'jus-de-moringa', '100% Naturel', NULL, 'Poudre de moringa, sucre et eau', NULL, 1500.00, '1L', 28, 'https://senachat.com/public/uploads/all/8J3ioeqfbmGXXJgm7L8DP7XG1u4sm2imab74deMx.jpg', 0, 1, NULL, NULL, NULL, NULL, '2026-04-20 17:17:32', '2026-04-29 12:45:20'),
(44, 6, 'Mangue Séchée', 'mangue-s-ch-e', 'Morceaux Orange doré.  Riche en fibres et en vitamine A', NULL, 'Mangue Fraiche', NULL, 1500.00, '100g', 43, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQX7iufOq1TAvKuJZm_iw39l_joIISmOWKPaA&s', 0, 1, NULL, NULL, NULL, NULL, '2026-04-22 13:18:54', '2026-04-23 13:29:05'),
(45, 3, 'Sirop Ditakh', 'sirop-ditakh', 'Le sirop de Ditakh. Une boisson locale sénégalaise, naturelle et rafraîchissante, fabriquée à partir de la pulpe du fruit Detarium senegalense. Apprécié pour son goût acidulé, ce sirop est riche en vitamine C et antioxydants.', NULL, 'Ditakh et sucre', NULL, 5000.00, '1L', 13, 'https://boutiquedumonastere.com/client/cache/produit/628_775______sirop-duitakh_106.png', 0, 1, NULL, NULL, NULL, NULL, '2026-04-23 14:08:07', '2026-04-29 12:41:08'),
(46, 6, 'COCO, GINGEMBRE', 'coco-gingembre', 'Des Snacks Naturels Saveur Coco et de Gingembre. De quoi tromper votre faim et grignoter agréablement', NULL, 'Coco,Gingembre', NULL, 1500.00, '50g', 0, 'https://soreetul-live.odoo.com/web/image/product.product/2719/image_1024/%5BSNAC-COGI-BA10%5D%20Coco-Gingembre%20sech%C3%A9s%20100g?unique=fb7a19c', 0, 1, NULL, NULL, NULL, NULL, '2026-04-30 11:31:36', '2026-04-30 11:32:45'),
(47, 4, 'Thiakry Maïs', 'thiakry-ma-s', 'Thiakry à base de farine de maïs', NULL, 'Farine de maïs', NULL, 1000.00, '500g', 0, 'https://i0.wp.com/hgsdakar.com/wp-content/uploads/2023/01/46f5d7e0-173d-4645-9c48-985dc4ae24a6.jpeg?fit=632%2C858&ssl=1', 0, 1, NULL, NULL, NULL, NULL, '2026-04-30 12:37:45', '2026-04-30 12:38:24');

-- --------------------------------------------------------

--
-- Structure de la table `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `description` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `updated_at`) VALUES
(1, 'site_name', 'GIE Sokhna Maï', 'string', 'Nom du site', '2026-04-16 11:20:20'),
(2, 'site_email', 'gie.sokhnamai@gmail.com', 'string', 'Email de contact', '2026-04-16 11:20:20'),
(3, 'site_phone', '+221 77 446 04 74', 'string', 'Téléphone de contact', '2026-04-16 11:20:20'),
(4, 'site_address', 'Sénégal', 'string', 'Adresse', '2026-04-16 11:20:20'),
(5, 'whatsapp_number', '221774460474', 'string', 'Numéro WhatsApp (sans +)', '2026-04-16 11:20:20'),
(6, 'currency', 'FCFA', 'string', 'Devise', '2026-04-16 11:20:20'),
(7, 'maintenance_mode', '0', 'boolean', 'Mode maintenance', '2026-04-16 11:20:20'),
(8, 'items_per_page', '12', 'integer', 'Éléments par page', '2026-04-16 11:20:20');

-- --------------------------------------------------------

--
-- Structure de la table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `role_fr` varchar(100) NOT NULL,
  `role_en` varchar(100) DEFAULT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `role` varchar(255) NOT NULL DEFAULT 'Non défini',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `team_members`
--

INSERT INTO `team_members` (`id`, `name`, `role_fr`, `role_en`, `description_fr`, `description_en`, `photo_url`, `email`, `phone`, `display_order`, `is_active`, `created_at`, `updated_at`, `role`, `description`) VALUES
(1, 'Amy NIOME', 'Présidente', 'President', 'Leader visionnaire du GIE Sokhna Maï, elle guide l\'association vers l\'excellence.', 'Visionary leader of GIE Sokhna Maï, guiding the association toward excellence.', 'team-amy.jpg', 'amy@sokhnamai.sn', NULL, 1, 1, '2026-04-16 11:21:00', '2026-04-22 10:33:48', 'Présidente', 'Leader visionnaire du GIE, Amy coordonne les activités et porte la voix du groupement auprès des partenaires.'),
(2, 'Angélique TOURE', 'Vice-Présidente', 'Vice-President', 'Bras droit de la présidente, elle coordonne les opérations quotidiennes.', 'Right hand of the president, coordinating daily operations.', 'team-angelique.jpg', 'angelique@sokhnamai.sn', NULL, 2, 1, '2026-04-16 11:21:00', '2026-04-22 10:36:39', 'Vice-Présidente', 'Bras droit de la présidente, Angélique veille à la bonne marche des projets et à la cohésion de l\'équipe.'),
(3, 'Anna NDIAYE', 'Trésorière', 'Treasurer', 'Gestion rigoureuse des finances et transparence comptable.', 'Rigorous financial management and accounting transparency.', 'team-anna.jpg', 'anna@sokhnamai.sn', NULL, 3, 1, '2026-04-16 11:21:00', '2026-04-22 10:37:55', 'Trésorière', 'Anna assure une gestion rigoureuse et transparente des finances du groupement.'),
(4, 'Magou SAMB', 'Gérante de Boutique', 'Shop Manager', 'Service client au quotidien, elle assure la satisfaction de nos clients.', 'Daily customer service, ensuring client satisfaction.', 'team-magou.jpg', 'magou@sokhnamai.sn', NULL, 4, 1, '2026-04-16 11:21:00', '2026-04-22 10:39:33', 'Gérante de Boutique', 'Magou gère la boutique sur place et veille à la qualité du service client au quotidien.'),
(5, 'Ndeye Maguette', '', NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, '2026-04-22 10:42:36', '2026-04-22 10:42:36', 'Membre', 'Equipe hygiène et Qualité');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Index pour la table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `admin_settings`
--
ALTER TABLE `admin_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Index pour la table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `blog_categories`
--
ALTER TABLE `blog_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Index pour la table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `author_id` (`author_id`),
  ADD KEY `idx_published` (`is_published`,`published_at`);

--
-- Index pour la table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_is_available` (`is_available`);

--
-- Index pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `replied_by` (`replied_by`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Index pour la table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_phone` (`phone`);

--
-- Index pour la table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Index pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Index pour la table `partners`
--
ALTER TABLE `partners`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`);

--
-- Index pour la table `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Index pour la table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `admin_settings`
--
ALTER TABLE `admin_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT pour la table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT pour la table `blog_categories`
--
ALTER TABLE `blog_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `blog_posts`
--
ALTER TABLE `blog_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT pour la table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT pour la table `partners`
--
ALTER TABLE `partners`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT pour la table `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `blog_posts`
--
ALTER TABLE `blog_posts`
  ADD CONSTRAINT `blog_posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `blog_categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `blog_posts_ibfk_2` FOREIGN KEY (`author_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`replied_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
