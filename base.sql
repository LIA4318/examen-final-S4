-- =========================================
-- Suppression des tables existantes (pour pouvoir relancer le script)
-- =========================================
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS baremes_frais;
DROP TABLE IF EXISTS types_operations;
DROP TABLE IF EXISTS prefixes;
DROP TABLE IF EXISTS clients;

-- =========================================
-- Création des tables
-- =========================================

CREATE TABLE clients (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    numero_telephone VARCHAR(15) NOT NULL UNIQUE,
    solde DECIMAL(10, 2) NOT NULL DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE prefixes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prefixe VARCHAR(10) NOT NULL UNIQUE
);

CREATE TABLE types_operations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    libelle VARCHAR(100) NOT NULL
);

CREATE TABLE baremes_frais (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER NOT NULL,
    montant_min DECIMAL(10, 2) NOT NULL,
    montant_max DECIMAL(10, 2) NOT NULL,
    frais DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id)
);

CREATE TABLE transactions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL,
    type_operation_id INTEGER NOT NULL,
    montant DECIMAL(15, 2) NOT NULL,
    frais DECIMAL(15, 2) NOT NULL,
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    client_destinataire_id INTEGER,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(id),
    FOREIGN KEY (client_destinataire_id) REFERENCES clients(id)
);

-- =========================================
-- Données de base (seed data)
-- =========================================

-- Préfixes
INSERT INTO prefixes (prefixe) VALUES ('033');
INSERT INTO prefixes (prefixe) VALUES ('037');

-- Types d'opération
INSERT INTO types_operations (libelle) VALUES ('depot');
INSERT INTO types_operations (libelle) VALUES ('retrait');
INSERT INTO types_operations (libelle) VALUES ('transfert');

-- Barèmes de frais pour RETRAIT (type_operation_id = 2)
INSERT INTO baremes_frais (type_operation_id, montant_min, montant_max, frais) VALUES
(2, 100, 1000, 50),
(2, 1001, 5000, 50),
(2, 5001, 10000, 100),
(2, 10001, 25000, 200),
(2, 25001, 50000, 400),
(2, 50001, 100000, 800),
(2, 100001, 250000, 1500),
(2, 250001, 500000, 1500),
(2, 500001, 1000000, 2500),
(2, 1000001, 2000000, 3000);

-- Barèmes de frais pour TRANSFERT (type_operation_id = 3)
INSERT INTO baremes_frais (type_operation_id, montant_min, montant_max, frais) VALUES
(3, 100, 1000, 50),
(3, 1001, 5000, 50),
(3, 5001, 10000, 100),
(3, 10001, 25000, 200),
(3, 25001, 50000, 400),
(3, 50001, 100000, 800),
(3, 100001, 250000, 1500),
(3, 250001, 500000, 1500),
(3, 500001, 1000000, 2500),
(3, 1000001, 2000000, 3000);

-- Quelques clients de test
INSERT INTO clients (numero_telephone, solde) VALUES
('0331234567', 10000),
('0372345678', 5000),
('0339876543', 0);