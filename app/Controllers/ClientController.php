<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\TypeOperationModel;
use App\Models\FraisBaremeModel;
use App\Models\OperationModel;
use App\Models\ConfigurationModel;

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
            return redirect()->to('/index.php/client/login');
        }

        return view('client/transfert', [
            'title' => 'Transfert - Mobile Money',
            'prefixes' => $this->configurationModel->getPrefixes(),
        ]);
    }

    public function doTransfert()
    {
        if (!$this->session->has('client_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Non connecté']);
        }

        $montant = $this->request->getPost('montant');
        $destinatairesRaw = $this->request->getPost('destinataires');
        $inclureFrais = $this->request->getPost('inclure_frais') ? true : false;

        if (empty($montant) || $montant <= 0) {
            return $this->response->setJSON(['success' => false, 'message' => 'Montant invalide']);
        }

        if (empty($destinatairesRaw)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Au moins un numéro destinataire est requis']);
        }

        $destinataires = $this->parseDestinataires($destinatairesRaw);
        $destinataires = array_unique($destinataires);

        if (empty($destinataires)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Aucun numéro destinataire valide trouvé']);
        }

        if (count($destinataires) > 10) {
            return $this->response->setJSON(['success' => false, 'message' => 'Maximum 10 numéros autorisés par transfert']);
        }

        $clientTelephone = $this->session->get('client_telephone');
        $clientId = $this->session->get('client_id');

        if (in_array($clientTelephone, $destinataires, true)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Vous ne pouvez pas inclure votre propre numéro parmi les destinataires']);
        }

        $destinataireClients = [];
        foreach ($destinataires as $destinataire) {
            if (empty($destinataire) || strlen($destinataire) < 8) {
                return $this->response->setJSON(['success' => false, 'message' => 'Numéro destinataire invalide : ' . esc($destinataire)]);
            }

            if (!$this->configurationModel->isValidPrefix($destinataire)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Préfixe du destinataire invalide : ' . esc($destinataire)]);
            }

            $destClient = $this->clientModel->findByTelephone($destinataire);
            if (!$destClient) {
                return $this->response->setJSON(['success' => false, 'message' => 'Destinataire non trouvé : ' . esc($destinataire)]);
            }

            if ($destClient['id'] === $clientId) {
                return $this->response->setJSON(['success' => false, 'message' => 'Vous ne pouvez pas vous transférer à vous-même']);
            }

            $destinataireClients[] = $destClient;
        }

        $typeTransfert = $this->typeOperationModel->findByLibelle('transfert');
        if (!$typeTransfert) {
            return $this->response->setJSON(['success' => false, 'message' => 'Type d\'opération non trouvé']);
        }

        $shares = $this->splitAmountByRecipients((float) $montant, count($destinataires));

        $fraisParDestinataire = [];
        $totalFrais = 0;
        foreach ($shares as $share) {
            $fee = $this->fraisBaremeModel->calculerFrais($typeTransfert['id'], $share);
            $fraisParDestinataire[] = $fee;
            $totalFrais += $fee;
        }

        $sender = $this->clientModel->find($clientId);
        $totalDebit = array_sum($shares);
        if ($inclureFrais) {
            $totalDebit += $totalFrais;
        }

        if ($sender['solde'] < $totalDebit) {
            return $this->response->setJSON(['success' => false, 'message' => 'Solde insuffisant pour effectuer ce transfert. Total débité : ' . number_format($totalDebit, 2, ',', ' ') . ' Ar']);
        }

        $result = $this->processMultipleTransfers($sender, $destinataireClients, $shares, $fraisParDestinataire, $typeTransfert['id'], $inclureFrais);

        if (!$result['success']) {
            return $this->response->setJSON(['success' => false, 'message' => $result['message']]);
        }

        $this->session->set('client_solde', $result['nouveau_solde']);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Transfert effectué avec succès vers ' . count($destinataires) . ' destinataire(s)',
            'nouveau_solde' => number_format($result['nouveau_solde'], 2, ',', ' ') . ' Ar',
            'frais' => number_format($totalFrais, 2, ',', ' ') . ' Ar',
            'total_debite' => number_format($totalDebit, 2, ',', ' ') . ' Ar',
            'inclure_frais' => $inclureFrais,
            'frais_pris_en_charge' => $inclureFrais,
        ]);
    }

    private function parseDestinataires(string $raw): array
    {
        $raw = str_replace(["\r", ';'], ["\n", ','], $raw);
        $parts = preg_split('/[\s,]+/', $raw, -1, PREG_SPLIT_NO_EMPTY);

        $destinataires = [];
        foreach ($parts as $part) {
            $telefono = preg_replace('/[^0-9]/', '', $part);
            if ($telefono !== '') {
                $destinataires[] = $telefono;
            }
        }

        return $destinataires;
    }

    private function splitAmountByRecipients(float $montant, int $count): array
    {
        $cents = (int) round($montant * 100);
        $base = intdiv($cents, $count);
        $remainder = $cents % $count;

        $shares = [];
        for ($i = 0; $i < $count; $i++) {
            $shareCents = $base + ($i < $remainder ? 1 : 0);
            $shares[] = $shareCents / 100;
        }

        return $shares;
    }

    private function processMultipleTransfers(array $sender, array $destinataires, array $shares, array $frais, int $typeOperationId, bool $inclureFrais): array
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $senderBalance = $sender['solde'];
        $transactions = $db->table('transactions');
        $clients = $db->table('clients');

        foreach ($destinataires as $index => $dest) {
            $amount = $shares[$index];
            $fee = $frais[$index];
            $senderTotal = $inclureFrais ? $amount + $fee : $amount;
            $recipientAmount = $inclureFrais ? $amount : $amount - $fee;

            if ($recipientAmount <= 0) {
                $db->transRollback();
                return ['success' => false, 'message' => 'Montant trop faible pour couvrir les frais sur le transfert'];
            }

            if ($senderBalance < $senderTotal) {
                $db->transRollback();
                return ['success' => false, 'message' => 'Solde insuffisant pendant le traitement du transfert'];
            }

            $senderTransaction = [
                'client_id' => $sender['id'],
                'type_operation_id' => $typeOperationId,
                'montant' => $amount,
                'frais' => $fee,
                'client_destinataire_id' => $dest['id'],
                'date_transaction' => date('Y-m-d H:i:s'),
            ];

            if (!$transactions->insert($senderTransaction)) {
                $db->transRollback();
                return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement du transfert'];
            }

            $senderBalance -= $senderTotal;
            if (!$clients->update(['solde' => $senderBalance], ['id' => $sender['id']])) {
                $db->transRollback();
                return ['success' => false, 'message' => 'Impossible de mettre à jour le solde du client'];
            }

            $recipientBalance = $dest['solde'] + $recipientAmount;
            $recipientTransaction = [
                'client_id' => $dest['id'],
                'type_operation_id' => $typeOperationId,
                'montant' => $recipientAmount,
                'frais' => 0,
                'client_destinataire_id' => null,
                'date_transaction' => date('Y-m-d H:i:s'),
            ];

            if (!$transactions->insert($recipientTransaction)) {
                $db->transRollback();
                return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement du transfert destinataire'];
            }

            if (!$clients->update(['solde' => $recipientBalance], ['id' => $dest['id']])) {
                $db->transRollback();
                return ['success' => false, 'message' => 'Impossible de mettre à jour le solde du destinataire'];
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Erreur lors de la transaction globale'];
        }

        return ['success' => true, 'nouveau_solde' => $senderBalance];
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
