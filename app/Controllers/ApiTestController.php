<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\TypeOperationModel;
use App\Models\FraisBaremeModel;
use App\Models\OperationModel;
use App\Models\ConfigurationModel;

class ApiTestController extends BaseController
{
    public function index()
    {
        echo "<h1>Test des Modèles Mobile Money</h1>";
        
        try {
            // 1. Test Configuration
            echo "<h2>1. Configuration</h2>";
            $configModel = new ConfigurationModel();
            $prefixes = $configModel->getPrefixes();
            echo "<p>✓ Préfixes valides : " . implode(', ', $prefixes) . "</p>";
            
            // 2. Test Types d'opérations
            echo "<h2>2. Types d'opérations</h2>";
            $typeModel = new TypeOperationModel();
            $types = $typeModel->findAll();
            echo "<p>✓ " . count($types) . " types trouvés</p>";
            echo "<ul>";
            foreach ($types as $type) {
                echo "<li>{$type['code']} : {$type['nom']}</li>";
            }
            echo "</ul>";
            
            // 3. Test Clients
            echo "<h2>3. Clients</h2>";
            $clientModel = new ClientModel();
            
            // Créer un client de test
            $testClient = [
                'numero_telephone' => '0331234567',
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'solde' => 100000
            ];
            
            // Supprimer si existe déjà
            $existing = $clientModel->findByTelephone('0331234567');
            if ($existing) {
                $clientModel->delete($existing['id']);
            }
            
            $clientId = $clientModel->insert($testClient);
            echo "<p>✓ Client créé avec ID : {$clientId}</p>";
            
            $client = $clientModel->find($clientId);
            echo "<p>✓ Client : {$client['prenom']} {$client['nom']}</p>";
            echo "<p>  - Solde : " . number_format($client['solde'], 2) . " Ar</p>";
            
            // 4. Test Frais
            echo "<h2>4. Barèmes de frais</h2>";
            $fraisModel = new FraisBaremeModel();
            $typeRetrait = $typeModel->findByCode('RETRAIT');
            
            if ($typeRetrait) {
                $montant = 5000;
                $frais = $fraisModel->calculerFrais($typeRetrait['id'], $montant);
                echo "<p>✓ Frais pour {$montant} Ar : {$frais} Ar</p>";
            } else {
                echo "<p>⚠️ Type RETRAIT non trouvé dans la base de données</p>";
            }
            
            // Nettoyer
            $clientModel->delete($clientId);
            echo "<p>✓ Données de test nettoyées</p>";
            
            echo "<h2 style='color:green'>✅ Tous les tests sont passés !</h2>";
            
        } catch (\Exception $e) {
            echo "<h2 style='color:red'>❌ Erreur</h2>";
            echo "<pre>" . $e->getMessage() . "</pre>";
            echo "Fichier : " . $e->getFile() . ":" . $e->getLine();
        }
    }
}