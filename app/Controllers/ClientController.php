<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\TypeOperationModel;
use App\Models\FraisBaremeModel;
use App\Models\OperationModel;

class ClientController extends BaseController
{
    protected $clientModel;
    protected $typeOperationModel;
    protected $fraisBaremeModel;
    protected $operationModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->typeOperationModel = new TypeOperationModel();
        $this->fraisBaremeModel = new FraisBaremeModel();
        $this->operationModel = new OperationModel();
        
        // Démarrer la session si ce n'est pas déjà fait
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Page de login automatique
     */
    public function login()
    {
        // Si déjà connecté, rediriger vers le dashboard
        if (isset($_SESSION['client_id'])) {
            return redirect()->to('/client/dashboard');
        }

        $data = [
            'title' => 'Connexion - Mobile Money'
        ];
        return view('client/login', $data);
    }

    /**
     * Traiter le login
     */
    public function doLogin()
    {
        $telephone = $this->request->getPost('telephone');
        
        if (empty($telephone)) {
            return redirect()->to('/client/login')->with('error', 'Veuillez entrer votre numéro de téléphone');
        }

        // Nettoyer le numéro
        $telephone = preg_replace('/[^0-9]/', '', $telephone);

        // Vérifier si le client existe
        $client = $this->clientModel->findByTelephone($telephone);
        
        if (!$client) {
            // Créer un nouveau client
            $data = [
                'numero_telephone' => $telephone,
                'solde' => 0
            ];
            $clientId = $this->clientModel->insert($data);
            $client = $this->clientModel->find($clientId);
        }

        // Mettre à jour la dernière connexion
        // Note: votre table n'a pas ce champ, on le fait juste pour la session

        // Stocker en session
        $_SESSION['client_id'] = $client['id'];
        $_SESSION['client_telephone'] = $client['numero_telephone'];
        $_SESSION['client_solde'] = $client['solde'];

        return redirect()->to('/client/dashboard');
    }

    /**
     * Dashboard client
     */
    public function dashboard()
    {
        if (!isset($_SESSION['client_id'])) {
            return redirect()->to('/client/login');
        }

        $client = $this->clientModel->find($_SESSION['client_id']);
        $historique = $this->operationModel->getClientHistory($_SESSION['client_id'], 10);

        $data = [
            'title' => 'Dashboard - Mobile Money',
            'client' => $client,
            'historique' => $historique,
            'solde' => number_format($client['solde'], 2, ',', ' ') . ' Ar'
        ];
        return view('client/dashboard', $data);
    }

    /**
     * Faire un dépôt
     */
    public function depot()
    {
        if (!isset($_SESSION['client_id'])) {
            return redirect()->to('/client/login');
        }

        $data = [
            'title' => 'Dépôt - Mobile Money'
        ];
        return view('client/depot', $data);
    }

    /**
     * Traiter le dépôt
     */
    public function doDepot()
    {
        if (!isset($_SESSION['client_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Non connecté']);
        }

        $montant = $this->request->getPost('montant');
        
        if (empty($montant) || $montant <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Montant invalide']);
        }

        // Récupérer le type DEPOT
        $typeDepot = $this->typeOperationModel->findByLibelle('depot');
        if (!$typeDepot) {
            return $this->response->setJSON(['success' => false, 'message' => 'Type d\'opération non trouvé']);
        }

        // Créer l'opération
        $operationData = [
            'client_id' => $_SESSION['client_id'],
            'type_operation_id' => $typeDepot['id'],
            'montant' => $montant,
            'description' => 'Dépôt automatique'
        ];

        $result = $this->operationModel->createOperation($operationData);

        if ($result['success']) {
            // Mettre à jour le solde en session
            $_SESSION['client_solde'] = $result['nouveau_solde'];
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Dépôt effectué avec succès',
                'nouveau_solde' => number_format($result['nouveau_solde'], 2, ',', ' ') . ' Ar'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => $result['message']
        ]);
    }

    /**
     * Faire un retrait
     */
    public function retrait()
    {
        if (!isset($_SESSION['client_id'])) {
            return redirect()->to('/client/login');
        }

        $data = [
            'title' => 'Retrait - Mobile Money'
        ];
        return view('client/retrait', $data);
    }

    /**
     * Traiter le retrait
     */
    public function doRetrait()
    {
        if (!isset($_SESSION['client_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Non connecté']);
        }

        $montant = $this->request->getPost('montant');
        
        if (empty($montant) || $montant <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Montant invalide']);
        }

        // Récupérer le type RETRAIT
        $typeRetrait = $this->typeOperationModel->findByLibelle('retrait');
        if (!$typeRetrait) {
            return $this->response->setJSON(['success' => false, 'message' => 'Type d\'opération non trouvé']);
        }

        // Calculer les frais
        $frais = $this->fraisBaremeModel->calculerFrais($typeRetrait['id'], $montant);

        // Créer l'opération
        $operationData = [
            'client_id' => $_SESSION['client_id'],
            'type_operation_id' => $typeRetrait['id'],
            'montant' => $montant,
            'frais' => $frais,
            'description' => 'Retrait automatique'
        ];

        $result = $this->operationModel->createOperation($operationData);

        if ($result['success']) {
            $_SESSION['client_solde'] = $result['nouveau_solde'];
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Retrait effectué avec succès',
                'nouveau_solde' => number_format($result['nouveau_solde'], 2, ',', ' ') . ' Ar',
                'frais' => number_format($frais, 2, ',', ' ') . ' Ar'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => $result['message']
        ]);
    }

    /**
     * Faire un transfert
     */
    public function transfert()
    {
        if (!isset($_SESSION['client_id'])) {
            return redirect()->to('/client/login');
        }

        $data = [
            'title' => 'Transfert - Mobile Money'
        ];
        return view('client/transfert', $data);
    }

    /**
     * Traiter le transfert
     */
    public function doTransfert()
    {
        if (!isset($_SESSION['client_id'])) {
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

        // Nettoyer le numéro
        $destinataire = preg_replace('/[^0-9]/', '', $destinataire);

        // Vérifier que le destinataire existe
        $destinataireClient = $this->clientModel->findByTelephone($destinataire);
        if (!$destinataireClient) {
            return $this->response->setJSON(['success' => false, 'message' => 'Destinataire non trouvé']);
        }

        // Vérifier que ce n'est pas le même compte
        if ($destinataire == $_SESSION['client_telephone']) {
            return $this->response->setJSON(['success' => false, 'message' => 'Vous ne pouvez pas vous transférer à vous-même']);
        }

        // Récupérer le type TRANSFERT
        $typeTransfert = $this->typeOperationModel->findByLibelle('transfert');
        if (!$typeTransfert) {
            return $this->response->setJSON(['success' => false, 'message' => 'Type d\'opération non trouvé']);
        }

        // Calculer les frais
        $frais = $this->fraisBaremeModel->calculerFrais($typeTransfert['id'], $montant);

        // Créer l'opération
        $operationData = [
            'client_id' => $_SESSION['client_id'],
            'type_operation_id' => $typeTransfert['id'],
            'montant' => $montant,
            'frais' => $frais,
            'destinataire_id' => $destinataireClient['id'],
            'description' => 'Transfert vers ' . $destinataire
        ];

        $result = $this->operationModel->createOperation($operationData);

        if ($result['success']) {
            $_SESSION['client_solde'] = $result['nouveau_solde'];
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Transfert effectué avec succès',
                'nouveau_solde' => number_format($result['nouveau_solde'], 2, ',', ' ') . ' Ar',
                'frais' => number_format($frais, 2, ',', ' ') . ' Ar'
            ]);
        }

        return $this->response->setJSON([
            'success' => false,
            'message' => $result['message']
        ]);
    }

    /**
     * Voir l'historique complet
     */
    public function historique()
    {
        if (!isset($_SESSION['client_id'])) {
            return redirect()->to('/client/login');
        }

        $historique = $this->operationModel->getClientHistory($_SESSION['client_id'], 100);

        $data = [
            'title' => 'Historique - Mobile Money',
            'historique' => $historique
        ];
        return view('client/historique', $data);
    }

    /**
     * Déconnexion
     */
    public function logout()
    {
        session_destroy();
        return redirect()->to('/client/login');
    }

    /**
     * Voir le solde (API)
     */
    public function getSolde()
    {
        if (!isset($_SESSION['client_id'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Non connecté']);
        }

        $client = $this->clientModel->find($_SESSION['client_id']);
        return $this->response->setJSON([
            'success' => true,
            'solde' => number_format($client['solde'], 2, ',', ' ') . ' Ar'
        ]);
    }
}