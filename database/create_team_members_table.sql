-- Création de la table team_members pour la gestion de l'équipe
CREATE TABLE IF NOT EXISTS team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    role VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insertion des membres initiaux
INSERT INTO team_members (name, role, description, created_at) VALUES
('Amy NIOME', 'Présidente', 'Leader visionnaire du GIE', '2024-01-15 10:00:00'),
('Angélique TOURE', 'Vice-Présidente', 'Bras droit de la présidente', '2024-01-15 10:00:00'),
('Anna NDIAYE', 'Trésorière', 'Gestion rigoureuse des finances', '2024-01-15 10:00:00'),
('Magou SAMB', 'Gérante de Boutique', 'Service client au quotidien', '2024-01-15 10:00:00');
