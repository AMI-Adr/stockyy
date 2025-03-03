CREATE DATABASE IF NOT EXISTS stockyy;
USE stockyy;

-- Création de la table Utilisateur
CREATE TABLE IF NOT EXISTS Utilisateur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'vendeur') DEFAULT 'vendeur',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table Produit
CREATE TABLE IF NOT EXISTS Produit (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    categorie VARCHAR(50) NOT NULL,
    reference VARCHAR(50) NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    quantite INT NOT NULL DEFAULT 0,
    fournisseur VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Création de la table Ventes
CREATE TABLE IF NOT EXISTS Ventes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantite INT NOT NULL,
    prix_total DECIMAL(10,2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES Utilisateur(id),
    FOREIGN KEY (product_id) REFERENCES Produit(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion des produits
INSERT INTO Produit (nom, categorie, reference, prix, quantite, fournisseur) VALUES
    ('Produit 1', 'Catégorie A', 'REF001', 10.99, 50, 'Fournisseur X'),
    ('Produit 2', 'Catégorie B', 'REF002', 15.49, 30, 'Fournisseur Y'),
    ('Produit 3', 'Catégorie C', 'REF003', 8.99, 20, 'Fournisseur Z'),
    ('Produit 4', 'Catégorie A', 'REF004', 12.99, 40, 'Fournisseur X'),
    ('Produit 5', 'Catégorie B', 'REF005', 9.99, 60, 'Fournisseur Y'),
    ('Produit 6', 'Catégorie C', 'REF006', 18.99, 25, 'Fournisseur Z'),
    ('Produit 7', 'Catégorie A', 'REF007', 22.99, 15, 'Fournisseur X'),
    ('Produit 8', 'Catégorie B', 'REF008', 6.99, 80, 'Fournisseur Y'),
    ('Produit 9', 'Catégorie C', 'REF009', 14.99, 35, 'Fournisseur Z'),
    ('Produit 10', 'Catégorie A', 'REF010', 19.99, 45, 'Fournisseur X');

-- Assurez-vous d'être connecté à la base de données "stockyy"
USE stockyy;

/* ------------------------------------------------------------
   Insertion des utilisateurs (administrateurs et vendeurs)
   ------------------------------------------------------------ */
INSERT INTO Utilisateur (nom, email, mot_de_passe, role, created_at) VALUES
    ('Admin One', 'admin1@gmail.com', MD5('password123'), 'admin', '2025-01-01 10:00:00'),
    ('Admin Two', 'admin2@gmail.com', MD5('password123'), 'admin', '2025-01-01 10:05:00'),
    ('Vendeur One', 'vendeur1@gmail.com', MD5('password123'), 'vendeur', '2025-01-02 08:00:00'),
    ('Vendeur Two', 'vendeur2@gmail.com', MD5('password123'), 'vendeur', '2025-01-02 08:05:00'),
    ('Vendeur Three', 'vendeur3@gmail.com', MD5('password123'), 'vendeur', '2025-01-02 08:10:00');

/* ------------------------------------------------------------
   Insertion des ventes Decembre
   ------------------------------------------------------------ */
INSERT INTO Ventes (user_id, product_id, quantite, prix_total, created_at) VALUES
    (3, 1, 2, 21.98, '2025-12-01 09:15:00'),  
    (4, 2, 1, 15.49, '2025-12-01 15:45:00'), 
    (5, 3, 3, 26.97, '2025-12-02 10:30:00'), 
    (3, 4, 2, 25.98, '2025-12-03 11:00:00'), 
    (4, 5, 5, 49.95, '2025-12-05 14:15:00'), 
    (5, 6, 1, 18.99, '2025-12-06 16:30:00'),
    (3, 7, 1, 22.99, '2025-12-07 09:00:00'), 
    (4, 8, 10, 69.90, '2025-12-08 12:00:00'),
    (5, 9, 2, 29.98, '2025-12-09 17:45:00'),  
    (3, 10, 3, 59.97, '2025-12-10 11:30:00'), 
    (4, 1, 4, 43.96, '2025-12-11 13:15:00'),  
    (5, 2, 2, 30.98, '2025-12-12 15:00:00'),  
    (3, 3, 6, 53.94, '2025-12-15 10:00:00'), 
    (4, 4, 1, 12.99, '2025-12-20 14:30:00'), 
    (5, 5, 2, 19.98, '2025-12-25 16:00:00');  

/* ------------------------------------------------------------
   Insertion des ventes Janvier
   ------------------------------------------------------------ */
INSERT INTO Ventes (user_id, product_id, quantite, prix_total, created_at) VALUES
    (3, 6, 2, 37.98, '2025-01-02 09:20:00'),
    (4, 7, 3, 68.97, '2025-01-03 10:30:00'),  
    (5, 8, 5, 34.95, '2025-01-04 11:45:00'), 
    (3, 9, 1, 14.99, '2025-01-05 12:00:00'), 
    (4, 10, 2, 39.98, '2025-01-06 14:00:00'), 
    (5, 1, 3, 32.97, '2025-01-07 15:30:00'),  
    (3, 2, 1, 15.49, '2025-01-08 16:45:00'),  
    (4, 3, 4, 35.96, '2025-01-09 10:15:00'), 
    (5, 4, 2, 25.98, '2025-01-10 09:30:00'), 
    (3, 5, 1, 9.99, '2025-01-11 11:00:00');  

/* ------------------------------------------------------------
   Insertion des ventes fevrier 
   ------------------------------------------------------------ */
INSERT INTO Ventes (user_id, product_id, quantite, prix_total, created_at) VALUES
    (4, 6, 2, 37.98, '2025-02-01 10:10:00'),  -- 2 x 18.99
    (5, 7, 1, 22.99, '2025-02-02 12:20:00'),  -- 1 x 22.99
    (3, 8, 3, 20.97, '2025-02-03 14:30:00'),  -- 3 x 6.99
    (4, 9, 4, 59.96, '2025-02-04 16:40:00'),  -- 4 x 14.99
    (5, 10, 2, 39.98, '2025-02-05 18:50:00'),  -- 2 x 19.99
    (3, 1, 5, 54.95, '2025-02-10 13:00:00'),   -- 5 x 10.99
    (4, 2, 2, 30.98, '2025-02-11 11:15:00'),   -- 2 x 15.49
    (5, 3, 3, 26.97, '2025-02-12 10:45:00'),   -- 3 x 8.99
    (3, 4, 1, 12.99, '2025-02-20 09:00:00'),   -- 1 x 12.99
    (4, 5, 2, 19.98, '2025-02-25 14:30:00');   -- 2 x 9.99 