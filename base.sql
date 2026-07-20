CREATE TABLE clients (
    id primary key,
    numero_telephone VARCHAR(15) NOT NULL,
    solde DECIMAL(10, 2) NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
Create table prefixes(
    id integer primary key autoincrement,
    prefixe VARCHAR(10) NOT NULL
);
create table types_operations (
    id primary key,
    libelle VARCHAR(100) NOT NULL
);
create table baremes_frais (
    id primary key,
    type_operation_id VARCHAR(50) NOT NULL,
    montant_min DECIMAL(10, 2) NOT NULL,
    montant_max DECIMAL(10, 2) NOT NULL, 
    frais DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(type_operation_id)
);
create table transactions(
    id integer primary key AUTOINCREMENT,
    client_id integer NOT NULL,
    type_operation_id integer NOT NULL,
    montant DECIMAL(15, 2) NOT NULL,
    frais DECIMAL(15, 2) NOT NULL,
    date_transaction TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    client_destinataire_id INTEGER,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (type_operation_id) REFERENCES types_operations(type_operation_id),
    FOREIGN KEY (client_destinataire_id) REFERENCES clients(id)
);

-- =========================================
-- Données de base (seed data)
-- =========================================

-- Préfixes
INSERT INTO prefixes (prefixe) VALUES ('033');
INSERT INTO prefixes (prefixe) VALUES ('037');

-- Types d'opération
INSERT INTO types_operation (nom) VALUES ('depot');
INSERT INTO types_operation (nom) VALUES ('retrait');
INSERT INTO types_operation (nom) VALUES ('transfert');

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

-- (Le dépôt n'a généralement pas de frais, mais vous pouvez en ajouter si l'énoncé le demande)

-- Quelques clients de test
INSERT INTO clients (numero_telephone, solde) VALUES
('0331234567', 10000),
('0372345678', 5000),
('0339876543', 0);