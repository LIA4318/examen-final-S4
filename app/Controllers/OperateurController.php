<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\TypeOperationModel;
use App\Models\FraisBaremeModel;
use App\Models\OperationModel;
use App\Models\ConfigurationModel;

class OperateurController extends BaseController
{
    protected $clientModel;
    protected $typeOperationModel;
    protected $fraisBaremeModel;
    protected $operationModel;
    protected $configurationModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->typeOperationModel = new TypeOperationModel();
        $this->fraisBaremeModel = new FraisBaremeModel();
        $this->operationModel = new OperationModel();
        $this->configurationModel = new ConfigurationModel();
    }

    // ============ DASHBOARD ============
    public function index()
    {
        $data = [
            'title' => 'Dashboard Opérateur',
            'stats_clients' => $this->clientModel->getStats(),
            'stats_operations' => $this->operationModel->getStats(),
            'gains_frais' => $this->operationModel->getGainsFrais(),
            'total_gains' => array_sum(array_column($this->operationModel->getGainsFrais(), 'total_gains'))
        ];
        return view('operateur/dashboard', $data);
    }

    // ============ CONFIGURATION DES PREFIXES ============
    public function prefixes()
    {
        $data = [
            'title' => 'Gestion des Préfixes',
            'prefixes' => $this->configurationModel->getPrefixes()
        ];
        return view('operateur/prefixes', $data);
    }

    public function updatePrefixes()
    {
        $prefixes = $this->request->getPost('prefixes');
        $result = $this->configurationModel->updatePrefixes($prefixes);
        
        if ($result) {
            return redirect()->to('/operateur/prefixes')
                           ->with('success', 'Préfixes mis à jour avec succès');
        }
        return redirect()->to('/operateur/prefixes')
                       ->with('error', 'Erreur lors de la mise à jour des préfixes');
    }

    // ============ GESTION DES TYPES D'OPÉRATIONS ============
    public function typesOperations()
    {
        $data = [
            'title' => 'Types d\'Opérations',
            'types' => $this->typeOperationModel->findAll()
        ];
        return view('operateur/types_operations', $data);
    }

    public function createType()
    {
        $data = [
            'title' => 'Ajouter un Type d\'Opération'
        ];
        return view('operateur/type_create', $data);
    }

    public function storeType()
    {
        $data = [
            'nom' => $this->request->getPost('nom'),
            'code' => strtoupper($this->request->getPost('code')),
            'description' => $this->request->getPost('description'),
            'actif' => $this->request->getPost('actif') ? 1 : 0
        ];

        if ($this->typeOperationModel->save($data)) {
            return redirect()->to('/operateur/types-operations')
                           ->with('success', 'Type d\'opération ajouté avec succès');
        }
        return redirect()->back()
                       ->with('errors', $this->typeOperationModel->errors())
                       ->withInput();
    }

    public function editType($id)
    {
        $type = $this->typeOperationModel->find($id);
        if (!$type) {
            return redirect()->to('/operateur/types-operations')
                           ->with('error', 'Type non trouvé');
        }
        $data = [
            'title' => 'Modifier Type d\'Opération',
            'type' => $type
        ];
        return view('operateur/type_edit', $data);
    }

    public function updateType($id)
    {
        $data = [
            'nom' => $this->request->getPost('nom'),
            'code' => strtoupper($this->request->getPost('code')),
            'description' => $this->request->getPost('description'),
            'actif' => $this->request->getPost('actif') ? 1 : 0
        ];

        if ($this->typeOperationModel->update($id, $data)) {
            return redirect()->to('/operateur/types-operations')
                           ->with('success', 'Type d\'opération modifié avec succès');
        }
        return redirect()->back()
                       ->with('errors', $this->typeOperationModel->errors())
                       ->withInput();
    }

    public function deleteType($id)
    {
        if ($this->typeOperationModel->delete($id)) {
            return redirect()->to('/operateur/types-operations')
                           ->with('success', 'Type d\'opération supprimé');
        }
        return redirect()->to('/operateur/types-operations')
                       ->with('error', 'Erreur lors de la suppression');
    }

    // ============ GESTION DES BARÈMES DE FRAIS ============
    public function baremes()
    {
        $data = [
            'title' => 'Barèmes de Frais',
            'types' => $this->typeOperationModel->getActiveTypes(),
            'baremes' => $this->fraisBaremeModel->findAll(),
            'stats_frais' => $this->fraisBaremeModel->getStatsFrais()
        ];
        return view('operateur/baremes', $data);
    }

    public function createBareme()
    {
        $data = [
            'title' => 'Ajouter un Barème de Frais',
            'types' => $this->typeOperationModel->getActiveTypes()
        ];
        return view('operateur/bareme_create', $data);
    }

    public function storeBareme()
    {
        $data = [
            'type_operation_id' => $this->request->getPost('type_operation_id'),
            'montant_min' => $this->request->getPost('montant_min'),
            'montant_max' => $this->request->getPost('montant_max'),
            'frais_fixe' => $this->request->getPost('frais_fixe') ?? 0,
            'frais_pourcentage' => $this->request->getPost('frais_pourcentage') ?? 0,
            'actif' => $this->request->getPost('actif') ? 1 : 0
        ];

        // Vérifier les chevauchements
        if ($this->fraisBaremeModel->hasOverlap(
            $data['type_operation_id'],
            $data['montant_min'],
            $data['montant_max']
        )) {
            return redirect()->back()
                           ->with('error', 'Ce barème chevauche un barème existant')
                           ->withInput();
        }

        if ($this->fraisBaremeModel->save($data)) {
            return redirect()->to('/operateur/baremes')
                           ->with('success', 'Barème ajouté avec succès');
        }
        return redirect()->back()
                       ->with('errors', $this->fraisBaremeModel->errors())
                       ->withInput();
    }

    public function editBareme($id)
    {
        $bareme = $this->fraisBaremeModel->find($id);
        if (!$bareme) {
            return redirect()->to('/operateur/baremes')
                           ->with('error', 'Barème non trouvé');
        }
        $data = [
            'title' => 'Modifier Barème',
            'bareme' => $bareme,
            'types' => $this->typeOperationModel->getActiveTypes()
        ];
        return view('operateur/bareme_edit', $data);
    }

    public function updateBareme($id)
    {
        $data = [
            'type_operation_id' => $this->request->getPost('type_operation_id'),
            'montant_min' => $this->request->getPost('montant_min'),
            'montant_max' => $this->request->getPost('montant_max'),
            'frais_fixe' => $this->request->getPost('frais_fixe') ?? 0,
            'frais_pourcentage' => $this->request->getPost('frais_pourcentage') ?? 0,
            'actif' => $this->request->getPost('actif') ? 1 : 0
        ];

        // Vérifier les chevauchements en excluant le barème actuel
        if ($this->fraisBaremeModel->hasOverlap(
            $data['type_operation_id'],
            $data['montant_min'],
            $data['montant_max'],
            $id
        )) {
            return redirect()->back()
                           ->with('error', 'Ce barème chevauche un barème existant')
                           ->withInput();
        }

        if ($this->fraisBaremeModel->update($id, $data)) {
            return redirect()->to('/operateur/baremes')
                           ->with('success', 'Barème modifié avec succès');
        }
        return redirect()->back()
                       ->with('errors', $this->fraisBaremeModel->errors())
                       ->withInput();
    }

    public function deleteBareme($id)
    {
        if ($this->fraisBaremeModel->delete($id)) {
            return redirect()->to('/operateur/baremes')
                           ->with('success', 'Barème supprimé');
        }
        return redirect()->to('/operateur/baremes')
                       ->with('error', 'Erreur lors de la suppression');
    }

    // ============ SITUATION DES COMPTES CLIENTS ============
    public function clients()
    {
        $data = [
            'title' => 'Situation des Comptes Clients',
            'clients' => $this->clientModel->findAll(),
            'stats' => $this->clientModel->getStats()
        ];
        return view('operateur/clients', $data);
    }

    public function clientDetail($id)
    {
        $client = $this->clientModel->find($id);
        if (!$client) {
            return redirect()->to('/operateur/clients')
                           ->with('error', 'Client non trouvé');
        }

        $data = [
            'title' => 'Détail Client',
            'client' => $client,
            'historique' => $this->operationModel->getClientHistory($id)
        ];
        return view('operateur/client_detail', $data);
    }

    // ============ STATISTIQUES GLOBALES ============
    public function statistiques()
    {
        $db = \Config\Database::connect();
        
        $data = [
            'title' => 'Statistiques Globales',
            'stats_clients' => $this->clientModel->getStats(),
            'stats_operations' => $this->operationModel->getStats(),
            'gains_frais' => $this->operationModel->getGainsFrais(),
            'stats_frais' => $this->fraisBaremeModel->getStatsFrais(),
            'total_gains' => array_sum(array_column($this->operationModel->getGainsFrais(), 'total_gains')),
            'operations_par_jour' => $db->query("
                SELECT 
                    DATE(date_creation) as date,
                    COUNT(*) as nb_operations,
                    SUM(montant) as total_montant,
                    SUM(frais) as total_frais
                FROM operations
                WHERE statut = 'SUCCES'
                GROUP BY DATE(date_creation)
                ORDER BY date DESC
                LIMIT 30
            ")->getResultArray()
        ];
        return view('operateur/statistiques', $data);
    }
}