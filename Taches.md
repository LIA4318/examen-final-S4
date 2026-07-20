# examen-final-S4

## Binome
-Etudiant 1: D'ARVISENET Anjara Yrielle(ETU004164)
-Etudiant 2: RAKOTONDRINA Liantsoa(ETU004318)

## Mise en place de l'envionnement(OK)

### [Yrielle]
- Mise en place de l'environnement de développement (PHP, Composer, CodeIgniter 4)
- Installation et configuration du projet CodeIgniter 4 (`composer create-project codeigniter4/appstarter nom_du_projet`)
- Configuration de la base de données SQLite dans `.env` (`database.default.DBDriver = SQLite3`)
- Vérification de l'activation de l'extension SQLite3 dans PHP

## Conception de la base donnée(OK)

### [Liantsoa-Yrielle]
- Conception du schéma de la base de données (MCD/MLD)
- Rédaction du fichier `base.sql` à la racine du projet contenant :
  - Table `prefixes` (préfixes valables de l'opérateur)
  - Table `types_operations` (dépôt, retrait, transfert)
  - Table `baremes_frais` (barèmes de frais par tranche de montant, modifiables)
  - Table `comptes` (comptes clients : numéro de téléphone, solde)
  - Table `transactions` (historique des opérations)
- Test d'exécution du script SQL sur la base SQLite
