-- =========================================
-- Suppression des tables existantes 
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
INSERT INTO prefixes (prefixe) VALUES ('032');
INSERT INTO prefixes (prefixe) VALUES ('034');
INSERT INTO prefixes (prefixe) VALUES ('038');

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
('0372345678', 5000),
('0323456789', 30000),
('0371234567', 15000),
('0343456789', 20000),
('0341234567', 5000),
('0385686401', 10000),
('0331234567', 10000),
('0334567890', 15000);


-- ============================================
-- VERSION 2 - MOBILE MONEY
-- ============================================

-- Table des opérateurs
CREATE TABLE IF NOT EXISTS operateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    prefixe VARCHAR(10) NOT NULL,
    commission_pourcentage DECIMAL(5,2) DEFAULT 0,
    actif BOOLEAN DEFAULT 1,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Ajout des colonnes dans transactions
ALTER TABLE transactions ADD COLUMN operateur_id INTEGER;
ALTER TABLE transactions ADD COLUMN commission DECIMAL(15,2) DEFAULT 0;
ALTER TABLE transactions ADD COLUMN frais_inclus BOOLEAN DEFAULT 0;
ALTER TABLE transactions ADD COLUMN envoi_multiple_id INTEGER;

-- Table des envois multiples
CREATE TABLE IF NOT EXISTS envois_multiples (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    client_id INTEGER NOT NULL,
    montant_total DECIMAL(15,2) NOT NULL,
    nb_destinataires INTEGER DEFAULT 0,
    statut VARCHAR(20) DEFAULT 'EN_ATTENTE',
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id)
);

-- Table des détails d'envois multiples
CREATE TABLE IF NOT EXISTS envois_multiples_details (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    envoi_multiple_id INTEGER NOT NULL,
    destinataire_telephone VARCHAR(20) NOT NULL,
    montant DECIMAL(15,2) NOT NULL,
    statut VARCHAR(20) DEFAULT 'EN_ATTENTE',
    date_execution DATETIME,
    FOREIGN KEY (envoi_multiple_id) REFERENCES envois_multiples(id)
);

-- Insertion des opérateurs par défaut
INSERT INTO operateurs (nom, code, prefixe, commission_pourcentage, actif) VALUES 
('Orange Money', 'OM', '032', 2.5, 1),
('Orange Money', 'OM2', '037', 2.5, 1),
('Airtel Money', 'AM', '033', 2.5, 1),
('Telma Money', 'TM', '034', 2.0, 1),
('MVola', 'MV', '038', 2.0, 1);



DROP VIEW IF EXISTS v_stats_frais_operateur;
CREATE VIEW v_stats_frais_operateur AS
SELECT 
    o.nom as operateur,
    COUNT(t.id) as nb_transactions,
    SUM(t.montant) as total_montant,
    SUM(t.frais) as total_frais,
    SUM(t.frais_commission) as total_commission,
    SUM(t.frais + t.frais_commission) as total_gains
FROM transactions t
LEFT JOIN operateurs o ON o.id = t.operateur_id
WHERE t.frais > 0 OR t.frais_commission > 0
GROUP BY o.nom;

DROP VIEW IF EXISTS v_stats_frais_autres_operateurs;
CREATE VIEW v_stats_frais_autres_operateurs AS
SELECT 
    o.nom as operateur_destinataire,
    COUNT(t.id) as nb_transactions,
    SUM(t.montant) as total_montant,
    SUM(t.frais) as total_frais,
    SUM(t.frais_commission) as total_commission
FROM transactions t
LEFT JOIN operateurs o ON o.id = t.operateur_destinataire_id
WHERE t.operateur_destinataire_id IS NOT NULL
GROUP BY o.nom;

DROP VIEW IF EXISTS v_montants_a_envoyer;
CREATE VIEW v_montants_a_envoyer AS
SELECT 
    o.nom as operateur,
    SUM(t.montant) as montant_total,
    SUM(t.frais_commission) as commission_totale,
    COUNT(t.id) as nb_transactions
FROM transactions t
LEFT JOIN operateurs o ON o.id = t.operateur_destinataire_id
WHERE t.operateur_destinataire_id IS NOT NULL
AND t.statut = 'SUCCES'
GROUP BY o.nom;

ALTER TABLE transactions ADD COLUMN frais_commission DECIMAL(15,2) DEFAULT 0;
ALTER TABLE transactions ADD COLUMN operateur_destinataire_id INTEGER;