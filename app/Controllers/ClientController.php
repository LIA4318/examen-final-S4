<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\TypeOperationModel;
use App\Models\FraisBaremeModel;
use App\Models\OperationModel;
use App\Models\ConfigurationModel;
use App\Models\OperateurModel;

class ClientController extends BaseController
{
    protected $clientModel;
    protected $typeOperationModel;
    protected $fraisBaremeModel;
    protected $operationModel;
    protected $configurationModel;
    protected $session;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->typeOperationModel = new TypeOperationModel();
        $this->fraisBaremeModel = new FraisBaremeModel();
        $this->operationModel = new OperationModel();
        $this->configurationModel = new ConfigurationModel();
        $this->session = \Config\Services::session();
    }

    public function login()
    {
        if ($this->session->has('client_id')) {
            return redirect()->to('/index.php/client/dashboard');
        }

        return view('client/login', [
            'title' => 'Connexion - Mobile Money',
            'prefixes' => $this->configurationModel->getPrefixes()
        ]);
    }

    public function doLogin()
    {
        $telephone = $this->request->getPost('telephone');

        if (empty($telephone)) {
            return redirect()->to('/index.php/client/login')->with('error', 'Veuillez entrer votre numéro de téléphone');
        }

        $telephone = preg_replace('/[^0-9]/', '', $telephone);

        if (empty($telephone) || strlen($telephone) < 8) {
            return redirect()->to('/index.php/client/login')->with('error', 'Numéro de téléphone invalide');
        }

        if (!$this->configurationModel->isValidPrefix($telephone)) {
            return redirect()->to('/index.php/client/login')->with('error', 'Numéro non valide pour cet opérateur');
        }

        $client = $this->clientModel->findByTelephone($telephone);

        if (!$client) {
            $clientId = $this->clientModel->insert([
                'numero_telephone' => $telephone,
                'solde' => 0,
            ]);
            $client = $this->clientModel->find($clientId);
        }

        $this->session->set([
            'client_id' => $client['id'],
            'client_telephone' => $client['numero_telephone'],
            'client_solde' => $client['solde'],
        ]);

        return redirect()->to('/index.php/client/dashboard');
    }

    public function dashboard()
    {
        if (!$this->session->has('client_id')) {
            return redirect()->to('/index.php/client/login');
        }

        $client = $this->clientModel->find($this->session->get('client_id'));
        $historique = $this->operationModel->getClientHistoryWithType($this->session->get('client_id'), 10);

        $data = [
            'title' => 'Dashboard - Mobile Money',
            'client' => $client,
            'historique' => $historique,
            'solde' => number_format($client['solde'], 2, ',', ' ') . ' Ar',
        ];

        return view('client/dashboard', $data);
    }

    public function depot()
    {
        if (!$this->session->has('client_id')) {
            return redirect()->to('/index.php/client/login');
        }

        return view('client/depot', ['title' => 'Dépôt - Mobile Money']);
    }

    public function doDepot()
    {
        if (!$this->session->has('client_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Non connecté']);
        }

        $montant = $this->request->getPost('montant');
        if (empty($montant) || $montant <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Montant invalide']);
        }

        $typeDepot = $this->typeOperationModel->findByLibelle('depot');
        if (!$typeDepot) {
            return $this->response->setJSON(['success' => false, 'message' => 'Type d\'opération non trouvé']);
        }

        $operationData = [
            'client_id' => $this->session->get('client_id'),
            'type_operation_id' => $typeDepot['id'],
            'montant' => $montant,
            'description' => 'Dépôt automatique',
        ];

        $result = $this->operationModel->createOperation($operationData);

        if ($result['success']) {
            $this->session->set('client_solde', $result['nouveau_solde']);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Dépôt effectué avec succès',
                'nouveau_solde' => number_format($result['nouveau_solde'], 2, ',', ' ') . ' Ar',
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => $result['message']]);
    }

    public function retrait()
    {
        if (!$this->session->has('client_id')) {
            return redirect()->to('/index.php/client/login');
        }

        return view('client/retrait', ['title' => 'Retrait - Mobile Money']);
    }

    public function doRetrait()
    {
        if (!$this->session->has('client_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Non connecté']);
        }

        $montant = $this->request->getPost('montant');
        if (empty($montant) || $montant <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Montant invalide']);
        }

        $typeRetrait = $this->typeOperationModel->findByLibelle('retrait');
        if (!$typeRetrait) {
            return $this->response->setJSON(['success' => false, 'message' => 'Type d\'opération non trouvé']);
        }

        $frais = $this->fraisBaremeModel->calculerFrais($typeRetrait['id'], $montant);

        $operationData = [
            'client_id' => $this->session->get('client_id'),
            'type_operation_id' => $typeRetrait['id'],
            'montant' => $montant,
            'frais' => $frais,
            'description' => 'Retrait automatique',
        ];

        $result = $this->operationModel->createOperation($operationData);

        if ($result['success']) {
            $this->session->set('client_solde', $result['nouveau_solde']);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Retrait effectué avec succès',
                'nouveau_solde' => number_format($result['nouveau_solde'], 2, ',', ' ') . ' Ar',
                'frais' => number_format($frais, 2, ',', ' ') . ' Ar',
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => $result['message']]);
    }

    public function transfert()
    {
        if (!$this->session->has('client_id')) {
            return redirect()->to('/client/login');
        }

        $data = ['title' => 'Transfert - Mobile Money'];
        return view('client/transfert', $data);
    }

    public function doTransfert()
    {
        if (!$this->session->has('client_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Non connecté']);
        }

        $montant = $this->request->getPost('montant');
        $destinataire = $this->request->getPost('destinataire');

        if (empty($montant) || $montant <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Montant invalide']);
        }

        if (empty($destinataire)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Numéro du destinataire requis']);
        }

        $destinataire = preg_replace('/[^0-9]/', '', $destinataire);

        if (empty($destinataire) || strlen($destinataire) < 8) {
            return $this->response->setJSON(['success' => false, 'message' => 'Numéro du destinataire invalide']);
        }

        // Vérifier si le destinataire est un autre opérateur
        $operateurModel = new OperateurModel();
        $prefixe = substr($destinataire, 0, 3);
        $operateurDest = $operateurModel->findByPrefixe($prefixe);

        // Vérifier les préfixes valides
        if (!$this->configurationModel->isValidPrefix($destinataire)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Préfixe du destinataire invalide']);
        }

        $destinataireClient = $this->clientModel->findByTelephone($destinataire);
        if (!$destinataireClient) {
            return $this->response->setJSON(['success' => false, 'message' => 'Destinataire non trouvé']);
        }

        if ($destinataire == $this->session->get('client_telephone')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Vous ne pouvez pas vous transférer à vous-même']);
        }

        $typeTransfert = $this->typeOperationModel->findByLibelle('transfert');
        if (!$typeTransfert) {
            return $this->response->setJSON(['success' => false, 'message' => 'Type d\'opération non trouvé']);
        }

        // Calculer les frais
        $frais = $this->fraisBaremeModel->calculerFrais($typeTransfert['id'], $montant);
        
        // Calculer la commission si vers un autre opérateur
        $commission = 0;
        $operateurDestId = null;
        if ($operateurDest && $operateurDest['id'] != 1) {
            $commission = ($montant * $operateurDest['commission_pourcentage']) / 100;
            $operateurDestId = $operateurDest['id'];
        }

        $operationData = [
            'client_id' => $this->session->get('client_id'),
            'type_operation_id' => $typeTransfert['id'],
            'montant' => $montant,
            'frais' => $frais,
            'client_destinataire_id' => $destinataireClient['id'],
            'operateur_destinataire_id' => $operateurDestId,
            'frais_commission' => $commission,
        ];

        $result = $this->operationModel->createOperation($operationData);

        if ($result['success']) {
            $this->session->set('client_solde', $result['nouveau_solde']);
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Transfert effectué avec succès',
                'nouveau_solde' => number_format($result['nouveau_solde'], 2, ',', ' ') . ' Ar',
                'frais' => number_format($frais, 2, ',', ' ') . ' Ar',
                'commission' => number_format($commission, 2, ',', ' ') . ' Ar',
            ]);
        }

        return $this->response->setJSON(['success' => false, 'message' => $result['message']]);
    }

    public function historique()
    {
        if (!$this->session->has('client_id')) {
            return redirect()->to('/index.php/client/login');
        }

        $historique = $this->operationModel->getClientHistoryWithType($this->session->get('client_id'), 100);

        return view('client/historique', [
            'title' => 'Historique - Mobile Money',
            'historique' => $historique,
        ]);
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/index.php/client/login');
    }

    public function getSolde()
    {
        if (!$this->session->has('client_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Non connecté']);
        }

        $client = $this->clientModel->find($this->session->get('client_id'));

        return $this->response->setJSON([
            'success' => true,
            'solde' => number_format($client['solde'], 2, ',', ' ') . ' Ar',
        ]);
    }
}