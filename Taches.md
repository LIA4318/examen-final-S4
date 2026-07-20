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

## Creation des modeles (OK)
### [Liantsoa]
- ClientModel.php : gestion des clients
- TypeOperationModel.php: Gestion des types d'opérations
-  FraisBaremeModel.php - Gestion des barèmes de frais
- OperationModel.php - Gestion des opérations
- ConfigurationModel.php - Gestion des configurations

##  Contrôleurs créés (3 contrôleurs) (ok)
### [LIANTSOA]
- OperateurController.php - Dashboard, types, barèmes, clients, statistiques
- ClientController.php - Login, dashboard, dépôt, retrait, transfert, historique
- ApiOperateur.php - API pour statistiques et calculs

## Routes configurées(ok)
### [LIANTSOA]
- Routes opérateur (/operateur/*)
- Routes client (/client/*)
- Routes API (/api/*)

## Vues client créées (ok)
### [LIANTSOA]
- login.php - Page de connexion automatique
- dashboard.php - Dashboard client avec solde
- depot.php - Formulaire de dépôt


## Base de données(ok)
### [LIANTSOA]
- Tables créées via base.sql
- Types d'opérations : depot, retrait, transfert
- Barèmes de frais configurés (retrait et transfert)
- Clients de test insérés

## Tests effectués(ok)
### [LIANTSOA]
- Test de connexion à la base de données
- Test des types d'opérations
- Test des barèmes de frais
- Test des calculs de frais
- Test des opérations (dépôt, retrait, transfert)
- Test des clients

### COTE CLIENT
## Authentification client (OK)
### [Yrielle]
- Page de login automatique par numéro de téléphone (`ClientController::login()`, vue `login.php`)
- Traitement de la connexion (`doLogin()`) : nettoyage du numéro, recherche du client
- Création automatique du compte si le numéro n'existe pas (pas d'inscription préalable requise)
- Stockage des infos client en session (`client_id`, `client_telephone`, `client_solde`)
- Redirection automatique vers le dashboard si déjà connecté
- Déconnexion (`logout()`)

## Consultation du solde (OK)
### [Yrielle]
- Affichage du solde sur le dashboard (`dashboard.php`)
- Endpoint API `getSolde()` pour récupérer le solde en JSON
- Mise à jour dynamique du solde en session après chaque opération

## Dépôt (OK)
### [Yrielle]
- Formulaire de dépôt intégré au dashboard (AJAX, sans rechargement de page)
- Traitement automatique du dépôt (`doDepot()`)
- Vérification du montant (non vide, positif)
- Enregistrement de l'opération via `OperationModel::createOperation()`
- Mise à jour du solde client et réponse JSON avec confirmation

## Retrait (OK)
### [Yrielle]
- Formulaire de retrait intégré au dashboard (AJAX)
- Traitement automatique du retrait (`doRetrait()`)
- Calcul automatique des frais via `FraisBaremeModel::calculerFrais()`
- Vérification du solde suffisant (montant + frais)
- Enregistrement de l'opération et mise à jour du solde
- Retour JSON avec solde mis à jour et frais appliqués

## Transfert (OK)
### [Yrielle]
- Formulaire de transfert intégré au dashboard (numéro destinataire + montant, AJAX)
- Traitement automatique du transfert (`doTransfert()`)
- Vérification de l'existence du compte destinataire
- Vérification qu'on ne se transfère pas à soi-même
- Calcul automatique des frais
- Vérification du solde suffisant
- Débit de l'expéditeur et crédit du destinataire (transaction SQL avec rollback en cas d'échec)
- Retour JSON avec confirmation, solde et frais

## Historique des opérations (OK)
### [Yrielle]
- Récupération de l'historique via `OperationModel::getClientHistory()`
- Affichage sur le dashboard (10 dernières opérations)
- Page dédiée `historique.php` (100 dernières opérations)
- Affichage type d'opération (badge coloré), montant, frais, date
- Affichage du destinataire pour les transferts
- Gestion de l'affichage si aucune transaction

## Vues client (OK)
### [Yrielle]
- login.php - Page de connexion automatique
- dashboard.php - Solde + formulaires dépôt/retrait/transfert en AJAX + accès historique
- historique.php - Tableau complet des transactions

## VERSION 2: COTE OPERATEUR (OK)

## [LIANTSOA]
1. Configuration des préfixes pour les autres opérateurs (OK)
- Table operateurs créée avec les colonnes : id, nom, code, prefixe, commission_pourcentage, actif
- CRUD complet des opérateurs (Liste, Ajout, Modification, Suppression)
- Gestion des préfixes (033, 032, 038, 034, etc.)
- Interface : /operateur/operateurs, /operateur/create, /operateur/edit/{id}, /operateur/delete/{id}

## [LIANTSOA]
2. Configuration des commissions pour transferts vers autres opérateurs  (OK)
- Colonne commission_pourcentage dans la table operateurs
- Colonne frais_commission dans la table transactions
- Colonne operateur_destinataire_id dans la table transactions
- Calcul automatique de la commission lors d'un transfert vers un autre opérateur
- Intégration dans ClientController::doTransfert()

## [LIANTSOA]
3. Situation des gains - Séparation opérateur / autres opérateurs (OK)
- Page /operateur/situation-gains avec deux sections :
- Opérateur Principal : gains via les frais (dépôt, retrait, transfert)
- Autres Opérateurs : gains via les commissions et frais
- Méthodes dans OperationModel :
- getGainsParOperateur() : gains par opérateur
- getMontantsAEnvoyer() : montants à envoyer

## [LIANTSOA]
4. Situation des montants à envoyer à chaque opérateur (OK)
- Affichage dans la page situation-gains :
      - Nom de l'opérateur
      - réfixe
      - Nombre de transactions
      - Montant total
      - Commission totale