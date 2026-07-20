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
