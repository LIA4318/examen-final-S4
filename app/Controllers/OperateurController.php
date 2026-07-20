<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\TypeOperationModel;
use App\Models\FraisBaremeModel;
use App\Models\OperationModel;
use App\Models\ConfigurationModel;
use App\Models\OperateurModel;

class OperateurController extends BaseController
{
    protected $clientModel;
    protected $typeOperationModel;
    protected $fraisBaremeModel;
    protected $operationModel;
    protected $configurationModel;
    protected $operateurModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->typeOperationModel = new TypeOperationModel();
        $this->fraisBaremeModel = new FraisBaremeModel();
        $this->operationModel = new OperationModel();
        $this->configurationModel = new ConfigurationModel();
        $this->operateurModel = new OperateurModel();
    }

    public function index()
    {
        $stats_clients = $this->clientModel->getStats();
        $stats_operations = $this->operationModel->getStats();
        $gains_frais = $this->operationModel->getGainsFrais();

        $data = [
            'title' => 'Dashboard Opérateur',
            'stats_clients' => $stats_clients,
            'stats_operations' => $stats_operations,
            'gains_frais' => $gains_frais,
            'total_gains' => array_sum(array_column($gains_frais, 'total_gains')) ?? 0
        ];
        return view('operateur/dashboard', $data);
    }

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
        $prefixesInput = $this->request->getPost('prefixes');

        if (is_string($prefixesInput)) {
            $prefixes = array_map('trim', explode(',', $prefixesInput));
        } else {
            $prefixes = $prefixesInput;
        }

        $result = $this->configurationModel->updatePrefixes($prefixes);

        if ($result) {
            return redirect()->to('/operateur/prefixes')
                           ->with('success', 'Préfixes mis à jour avec succès');
        }
        return redirect()->to('/operateur/prefixes')
                       ->with('error', 'Erreur lors de la mise à jour des préfixes');
    }

    // ============================================
    // VERSION 2 - GESTION DES OPÉRATEURS
    // ============================================
    
    public function operateurs()
    {
        $data = [
            'title' => 'Gestion des Opérateurs',
            'operateurs' => $this->operateurModel->findAll()
        ];
        return view('operateur/operateurs', $data);
    }

    public function createOperateur()
    {
        $data = ['title' => 'Ajouter un Opérateur'];
        return view('operateur/operateur_create', $data);
    }

    public function storeOperateur()
    {
        $data = [
            'nom' => $this->request->getPost('nom'),
            'code' => strtoupper($this->request->getPost('code')),
            'prefixe' => $this->request->getPost('prefixe'),
            'commission_pourcentage' => $this->request->getPost('commission_pourcentage') ?? 0,
            'actif' => $this->request->getPost('actif') ? 1 : 0
        ];

        if ($this->operateurModel->save($data)) {
            return redirect()->to('/operateur/operateurs')
                           ->with('success', 'Opérateur ajouté avec succès');
        }
        return redirect()->back()
                       ->with('errors', $this->operateurModel->errors())
                       ->withInput();
    }

    public function editOperateur($id)
    {
        $operateur = $this->operateurModel->find($id);
        if (!$operateur) {
            return redirect()->to('/operateur/operateurs')
                           ->with('error', 'Opérateur non trouvé');
        }
        $data = [
            'title' => 'Modifier Opérateur',
            'operateur' => $operateur
        ];
        return view('operateur/operateur_edit', $data);
    }

    public function updateOperateur($id)
    {
        $data = [
            'nom' => $this->request->getPost('nom'),
            'code' => strtoupper($this->request->getPost('code')),
            'prefixe' => $this->request->getPost('prefixe'),
            'commission_pourcentage' => $this->request->getPost('commission_pourcentage') ?? 0,
            'actif' => $this->request->getPost('actif') ? 1 : 0
        ];

        if ($this->operateurModel->update($id, $data)) {
            return redirect()->to('/operateur/operateurs')
                           ->with('success', 'Opérateur modifié avec succès');
        }
        return redirect()->back()
                       ->with('errors', $this->operateurModel->errors())
                       ->withInput();
    }

    public function deleteOperateur($id)
    {
        if ($this->operateurModel->delete($id)) {
            return redirect()->to('/operateur/operateurs')
                           ->with('success', 'Opérateur supprimé');
        }
        return redirect()->to('/operateur/operateurs')
                       ->with('error', 'Erreur lors de la suppression');
    }

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
        $data = ['title' => 'Ajouter un Type d\'Opération'];
        return view('operateur/type_create', $data);
    }

    public function storeType()
    {
        $data = ['libelle' => strtolower($this->request->getPost('libelle'))];

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
        $data = ['libelle' => strtolower($this->request->getPost('libelle'))];

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

    public function baremes()
    {
        $data = [
            'title' => 'Barèmes de Frais',
            'types' => $this->typeOperationModel->findAll(),
            'baremes' => $this->fraisBaremeModel->findAll(),
            'stats_frais' => $this->fraisBaremeModel->getStatsFrais()
        ];
        return view('operateur/baremes', $data);
    }

    public function createBareme()
    {
        $data = [
            'title' => 'Ajouter un Barème de Frais',
            'types' => $this->typeOperationModel->findAll()
        ];
        return view('operateur/bareme_create', $data);
    }

    public function storeBareme()
    {
        $data = [
            'type_operation_id' => $this->request->getPost('type_operation_id'),
            'montant_min' => $this->request->getPost('montant_min'),
            'montant_max' => $this->request->getPost('montant_max'),
            'frais' => $this->request->getPost('frais')
        ];

        if ($this->fraisBaremeModel->hasOverlap($data['type_operation_id'], $data['montant_min'], $data['montant_max'])) {
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
            'types' => $this->typeOperationModel->findAll()
        ];
        return view('operateur/bareme_edit', $data);
    }

    public function updateBareme($id)
    {
        $data = [
            'type_operation_id' => $this->request->getPost('type_operation_id'),
            'montant_min' => $this->request->getPost('montant_min'),
            'montant_max' => $this->request->getPost('montant_max'),
            'frais' => $this->request->getPost('frais')
        ];

        if ($this->fraisBaremeModel->hasOverlap($data['type_operation_id'], $data['montant_min'], $data['montant_max'], $id)) {
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

    // ============================================
    // VERSION 2 - SITUATION DES GAINS
    // ============================================
    
    public function situationGains()
    {
        $gains_principal = $this->operationModel->getStats();
        $gains_autres = $this->operationModel->getGainsParOperateur();
        $montants_a_envoyer = $this->operationModel->getMontantsAEnvoyer();

        $data = [
            'title' => 'Situation des Gains',
            'gains_principal' => $gains_principal,
            'gains_autres' => $gains_autres,
            'montants_a_envoyer' => $montants_a_envoyer
        ];
        return view('operateur/situation_gains', $data);
    }

    public function statistiques()
    {
        $stats_clients = $this->clientModel->getStats();
        $stats_operations = $this->operationModel->getStats();
        $gains_frais = $this->operationModel->getGainsFrais();
        $operations_par_jour = $this->operationModel->getTransactionsParJour(30);

        $data = [
            'title' => 'Statistiques Globales',
            'stats_clients' => $stats_clients,
            'stats_operations' => $stats_operations,
            'gains_frais' => $gains_frais,
            'stats_frais' => $this->fraisBaremeModel->getStatsFrais(),
            'total_gains' => array_sum(array_column($gains_frais, 'total_gains')) ?? 0,
            'operations_par_jour' => $operations_par_jour
        ];
        return view('operateur/statistiques', $data);
    }
}