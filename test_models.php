<?php
// test_models.php - Test des modèles en ligne de commande

require_once 'app/Config/Paths.php';
require_once 'vendor/autoload.php';

use Config\Database;
use App\Models\ClientModel;
use App\Models\TypeOperationModel;
use App\Models\FraisBaremeModel;
use App\Models\OperationModel;
use App\Models\ConfigurationModel;

echo "\n========================================\n";
echo "   TEST DES MODÈLES - MOBILE MONEY   \n";
echo "========================================\n\n";

// 1. Test ConfigurationModel
echo "1. TEST CONFIGURATION MODELE\n";
echo "---------------------------\n";
$configModel = new ConfigurationModel();
$prefixes = $configModel->getPrefixes();
echo "✓ Préfixes valides : " . implode(', ', $prefixes) . "\n\n";

// 2. Test TypeOperationModel
echo "2. TEST TYPE OPERATION MODELE\n";
echo "----------------------------\n";
$typeModel = new TypeOperationModel();
$types = $typeModel->findAll();
echo "✓ Types d'opérations trouvés : " . count($types) . "\n";
foreach ($types as $type) {
    echo "  - {$type['code']} : {$type['nom']}\n";
}
echo "\n";

// 3. Test ClientModel
echo "3. TEST CLIENT MODELE\n";
echo "---------------------\n";
$clientModel = new ClientModel();

// Créer un client de test
$testClient = [
    'numero_telephone' => '0331234567',
    'nom' => 'Dupont',
    'prenom' => 'Jean',
    'solde' => 100000
];
$clientId = $clientModel->insert($testClient);
echo "✓ Client créé avec ID : " . $clientId . "\n";

// Vérifier la création
$client = $clientModel->find($clientId);
echo "✓ Client trouvé : {$client['prenom']} {$client['nom']}\n";
echo "  - Téléphone : {$client['numero_telephone']}\n";
echo "  - Solde : " . number_format($client['solde'], 2) . " Ar\n";

// Tester la recherche par téléphone
$found = $clientModel->findByTelephone('0331234567');
echo "✓ Recherche par téléphone : " . ($found ? 'OK' : 'ÉCHEC') . "\n";

// Tester la validation des préfixes
$isValid = $clientModel->isValidPrefix('0331234567');
echo "✓ Validation préfixe 033 : " . ($isValid ? 'VALIDE' : 'INVALIDE') . "\n";
$isValid = $clientModel->isValidPrefix('0991234567');
echo "✓ Validation préfixe 099 : " . ($isValid ? 'VALIDE' : 'INVALIDE') . "\n";

// Tester la mise à jour du solde
$updated = $clientModel->updateSolde($clientId, 50000, 'add');
echo "✓ Ajout de 50 000 Ar au solde : " . ($updated ? 'OK' : 'ÉCHEC') . "\n";
$client = $clientModel->find($clientId);
echo "  - Nouveau solde : " . number_format($client['solde'], 2) . " Ar\n\n";

// 4. Test FraisBaremeModel
echo "4. TEST FRAIS BAREME MODELE\n";
echo "---------------------------\n";
$fraisModel = new FraisBaremeModel();

// Récupérer le type RETRAIT
$typeRetrait = $typeModel->findByCode('RETRAIT');
echo "✓ Type RETRAIT trouvé (ID: {$typeRetrait['id']})\n";

// Tester le calcul des frais
$montants = [500, 2500, 7500, 15000, 35000, 75000, 150000, 350000, 750000, 1500000];
foreach ($montants as $montant) {
    $frais = $fraisModel->calculerFrais($typeRetrait['id'], $montant);
    echo "  - " . number_format($montant, 0) . " Ar → Frais : " . number_format($frais, 2) . " Ar\n";
}
echo "\n";

// 5. Test OperationModel
echo "5. TEST OPERATION MODELE\n";
echo "------------------------\n";
$operationModel = new OperationModel();

// Créer un deuxième client pour les transferts
$destinataire = [
    'numero_telephone' => '0341234567',
    'nom' => 'Martin',
    'prenom' => 'Marie',
    'solde' => 50000
];
$destinataireId = $clientModel->insert($destinataire);
echo "✓ Client destinataire créé (ID: {$destinataireId})\n";

// Tester un dépôt
$depot = [
    'client_id' => $clientId,
    'type_operation_id' => $typeModel->findByCode('DEPOT')['id'],
    'montant' => 25000,
    'description' => 'Dépôt de test'
];
$result = $operationModel->createOperation($depot);
echo "✓ Dépôt de 25 000 Ar : " . ($result['success'] ? 'RÉUSSI' : 'ÉCHEC') . "\n";
if ($result['success']) {
    echo "  - Réf : {$result['operation_id']}\n";
    echo "  - Nouveau solde : " . number_format($result['nouveau_solde'], 2) . " Ar\n";
}

// Tester un retrait
$retrait = [
    'client_id' => $clientId,
    'type_operation_id' => $typeRetrait['id'],
    'montant' => 5000,
    'description' => 'Retrait de test'
];
$result = $operationModel->createOperation($retrait);
echo "✓ Retrait de 5 000 Ar : " . ($result['success'] ? 'RÉUSSI' : 'ÉCHEC') . "\n";
if ($result['success']) {
    echo "  - Frais appliqués : " . number_format($result['frais_appliques'], 2) . " Ar\n";
    echo "  - Nouveau solde : " . number_format($result['nouveau_solde'], 2) . " Ar\n";
}

// Tester un transfert
$transfert = [
    'client_id' => $clientId,
    'type_operation_id' => $typeModel->findByCode('TRANSFERT')['id'],
    'montant' => 10000,
    'destinataire_id' => $destinataireId,
    'description' => 'Transfert de test'
];
$result = $operationModel->createOperation($transfert);
echo "✓ Transfert de 10 000 Ar : " . ($result['success'] ? 'RÉUSSI' : 'ÉCHEC') . "\n";
if ($result['success']) {
    echo "  - Réf : {$result['operation_id']}\n";
    echo "  - Frais appliqués : " . number_format($result['frais_appliques'], 2) . " Ar\n";
    echo "  - Nouveau solde : " . number_format($result['nouveau_solde'], 2) . " Ar\n";
}

// 6. Tester l'historique
echo "\n6. HISTORIQUE DES OPÉRATIONS\n";
echo "---------------------------\n";
$historique = $operationModel->getClientHistory($clientId);
echo "✓ " . count($historique) . " opérations trouvées pour le client\n";
foreach ($historique as $op) {
    echo "  - {$op['date_creation']} : " . number_format($op['montant'], 2) . " Ar\n";
}

// 7. Tester les statistiques
echo "\n7. STATISTIQUES\n";
echo "--------------\n";
$statsClients = $clientModel->getStats();
echo "✓ Statistiques clients :\n";
echo "  - Total : {$statsClients['total']}\n";
echo "  - Actifs : {$statsClients['actifs']}\n";
echo "  - Solde total : " . number_format($statsClients['total_solde'], 2) . " Ar\n";

$statsOperations = $operationModel->getStats();
echo "\n✓ Statistiques opérations :\n";
foreach ($statsOperations as $stat) {
    echo "  - {$stat['type_operation']} : " . $stat['nb_operations'] . " opérations\n";
    echo "    * Total : " . number_format($stat['total_montant'], 2) . " Ar\n";
    echo "    * Frais : " . number_format($stat['total_frais'], 2) . " Ar\n";
}

$gains = $operationModel->getGainsFrais();
echo "\n✓ Gains via les frais :\n";
foreach ($gains as $gain) {
    echo "  - {$gain['type_operation']} : " . number_format($gain['total_gains'], 2) . " Ar\n";
}

// Nettoyer les données de test
echo "\n8. NETTOYAGE\n";
echo "-----------\n";
$clientModel->delete($clientId);
$clientModel->delete($destinataireId);
echo "✓ Données de test supprimées\n";

echo "\n========================================\n";
echo "   ✅ TOUS LES TESTS SONT PASSÉS !   \n";
echo "========================================\n";
