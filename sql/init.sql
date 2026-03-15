-- Base de données du lab de sécurité PHP
-- ⚠️  USAGE PÉDAGOGIQUE UNIQUEMENT - NE PAS DÉPLOYER EN PRODUCTION

CREATE DATABASE IF NOT EXISTS securite_lab;
USE securite_lab;

-- Table des utilisateurs (mots de passe en CLAIR - FAILLE volontaire)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,  -- FAILLE: stocké en clair
    email VARCHAR(100) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des articles du blog
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    author_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des commentaires
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des messages de contact
CREATE TABLE contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Données de démonstration
-- FAILLE: mots de passe en clair
INSERT INTO users (username, password, email, role) VALUES
('admin', 'admin123', 'admin@lab.local', 'admin'),
('alice', 'password', 'alice@lab.local', 'user'),
('bob', 'bob2024', 'bob@lab.local', 'user');

INSERT INTO articles (title, content, author_id) VALUES
('Bienvenue sur le blog', 'Ceci est un article de test. Laissez un commentaire !', 1),
('PHP et sécurité', 'La sécurité en PHP est un sujet important.', 1),
('Les injections SQL', 'Apprenons à nous défendre contre les injections SQL.', 2);

INSERT INTO comments (article_id, author_name, content) VALUES
(1, 'Alice', 'Super article !'),
(1, 'Bob', 'Merci pour ce contenu.'),
(2, 'Charlie', 'Très instructif.');
