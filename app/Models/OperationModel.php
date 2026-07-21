<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationModel extends Model
{
    protected $table            = 'transactions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'client_id',
        'type_operation_id',
        'montant',
        'frais',
        'client_destinataire_id',
        'date_transaction'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'date_transaction';

    protected $validationRules = [
        'client_id' => 'required|integer',
        'type_operation_id' => 'required|integer',
        'montant' => 'required|numeric',
        'frais' => 'permit_empty|numeric',
        'client_destinataire_id' => 'permit_empty|integer'
    ];

    protected $validationMessages = [
        'client_id' => [
            'required' => 'Le client est obligatoire',
            'integer' => 'Le client doit être un nombre valide'
        ],
        'type_operation_id' => [
            'required' => 'Le type d\'opération est obligatoire',
            'integer' => 'Le type d\'opération doit être un nombre valide'
        ],
        'montant' => [
            'required' => 'Le montant est obligatoire',
            'numeric' => 'Le montant doit être un nombre valide'
        ],
        'frais' => [
            'numeric' => 'Le frais doit être un nombre valide'
        ]
    ];

    /**
     * Créer une nouvelle opération
     */
    public function createOperation($data)
    {
        // Récupérer le client
        $clientModel = new ClientModel();
        $client = $clientModel->find($data['client_id']);
        
        if (!$client) {
            return ['success' => false, 'message' => 'Client non trouvé'];
        }

        // Récupérer le type d'opération
        $typeOpModel = new TypeOperationModel();
        $typeOp = $typeOpModel->find($data['type_operation_id']);
        
        if (!$typeOp) {
            return ['success' => false, 'message' => 'Type d\'opération invalide'];
        }

        $typeLibelle = strtolower($typeOp['libelle']);
        $nouveauSolde = $client['solde'];
        $frais = $data['frais'] ?? 0;
        $totalMontant = $data['montant'] + $frais;

        // Préparer les données de la transaction
        $transactionData = [
            'client_id' => $data['client_id'],
            'type_operation_id' => $data['type_operation_id'],
            'montant' => $data['montant'],
            'frais' => $frais,
            'date_transaction' => date('Y-m-d H:i:s')
        ];

        // Gérer selon le type
        switch ($typeLibelle) {
            case 'depot':
                $nouveauSolde += $data['montant'];
                break;
                
            case 'retrait':
                if ($client['solde'] < $totalMontant) {
                    return ['success' => false, 'message' => 'Solde insuffisant'];
                }
                $nouveauSolde -= $totalMontant;
                break;
                
            case 'transfert':
                if ($client['solde'] < $totalMontant) {
                    return ['success' => false, 'message' => 'Solde insuffisant'];
                }
                if (!isset($data['client_destinataire_id']) || !$data['client_destinataire_id']) {
                    return ['success' => false, 'message' => 'Destinataire requis pour un transfert'];
                }
                // Vérifier que le destinataire existe
                $destinataire = $clientModel->find($data['client_destinataire_id']);
                if (!$destinataire) {
                    return ['success' => false, 'message' => 'Destinataire non trouvé'];
                }
                // Vérifier que ce n'est pas le même compte
                if ($data['client_destinataire_id'] == $data['client_id']) {
                    return ['success' => false, 'message' => 'Vous ne pouvez pas vous transférer à vous-même'];
                }
                $transactionData['client_destinataire_id'] = $data['client_destinataire_id'];
                $nouveauSolde -= $totalMontant;
                break;
                
            default:
                return ['success' => false, 'message' => 'Type d\'opération non pris en charge'];
        }

        // Démarrer la transaction
        $db = \Config\Database::connect();
        $db->transStart();

        // 1. Enregistrer la transaction
        $operationId = $this->insert($transactionData);
        
        if (!$operationId) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement de la transaction'];
        }

        // 2. Mettre à jour le solde du client
        $updated = $clientModel->update($data['client_id'], ['solde' => $nouveauSolde]);
        
        if (!$updated) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du solde'];
        }

        // 3. Pour les transferts, mettre à jour le solde du destinataire
        if ($typeLibelle === 'transfert' && isset($data['client_destinataire_id'])) {
            $destinataire = $clientModel->find($data['client_destinataire_id']);
            $nouveauSoldeDest = $destinataire['solde'] + $data['montant'];
            
            // Enregistrer la transaction de réception
            $this->insert([
                'client_id' => $data['client_destinataire_id'],
                'type_operation_id' => $data['type_operation_id'],
                'montant' => $data['montant'],
                'frais' => 0,
                'client_destinataire_id' => null,
                'date_transaction' => date('Y-m-d H:i:s')
            ]);
            
            $clientModel->update($data['client_destinataire_id'], ['solde' => $nouveauSoldeDest]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Erreur lors de la transaction'];
        }

        return [
            'success' => true,
            'message' => 'Opération réussie',
            'operation_id' => $operationId,
            'nouveau_solde' => $nouveauSolde,
            'frais_appliques' => $frais
        ];
    }

    /**
     * Récupérer l'historique d'un client
     */
    public function getClientHistory($clientId, $limit = 50, $offset = 0)
    {
        return $this->where('client_id', $clientId)
                    ->orderBy('date_transaction', 'DESC')
                    ->findAll($limit, $offset);
    }

    /**
     * Récupérer l'historique complet avec les noms des types
     */
    public function getClientHistoryWithType($clientId, $limit = 50)
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                t.*,
                ty.libelle AS type_libelle,
                c.numero_telephone AS destinataire_numero
            FROM transactions t
            JOIN types_operations ty ON ty.id = t.type_operation_id
            LEFT JOIN clients c ON c.id = t.client_destinataire_id
            WHERE t.client_id = ?
            ORDER BY t.date_transaction DESC
            LIMIT ?
        ", [$clientId, $limit])->getResultArray();
    }

    /**
     * Statistiques globales des opérations
     */
    public function getStats()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                ty.libelle as type_operation,
                COUNT(t.id) as nb_operations,
                SUM(t.montant) as total_montant,
                SUM(t.frais) as total_frais,
                AVG(t.montant) as montant_moyen
            FROM transactions t
            JOIN types_operations ty ON ty.id = t.type_operation_id
            GROUP BY ty.libelle
        ")->getResultArray();
    }

    /**
     * Récupérer les gains via les frais
     */
    public function getGainsFrais()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                ty.libelle as type_operation,
                COUNT(t.id) as nb_operations,
                SUM(t.frais) as total_gains,
                AVG(t.frais) as gain_moyen
            FROM transactions t
            JOIN types_operations ty ON ty.id = t.type_operation_id
            WHERE t.frais > 0
            GROUP BY ty.libelle
            ORDER BY total_gains DESC
        ")->getResultArray();
    }

    /**
     * Récupérer les transactions par jour
     */
    public function getTransactionsParJour($limit = 30)
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                DATE(date_transaction) as date,
                COUNT(*) as nb_operations,
                SUM(montant) as total_montant,
                SUM(frais) as total_frais
            FROM transactions
            GROUP BY DATE(date_transaction)
            ORDER BY date DESC
            LIMIT ?
        ", [$limit])->getResultArray();
    }
        /**
     * Récupérer les gains par opérateur (V2)
     */
      public function getGainsParOperateur()
    {
        $db = \Config\Database::connect();
        try {
            return $db->query("
                SELECT 
                    o.nom as operateur,
                    COUNT(t.id) as nb_transactions,
                    SUM(t.frais) as total_frais,
                    SUM(t.frais_commission) as total_commission,
                    SUM(t.frais + t.frais_commission) as total_gains
                FROM transactions t
                LEFT JOIN operateurs o ON o.id = t.operateur_destinataire_id
                WHERE (t.frais > 0 OR t.frais_commission > 0)
                GROUP BY o.nom
                ORDER BY total_gains DESC
            ")->getResultArray();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    /**
     * Récupérer les montants à envoyer à chaque opérateur (V2)
     */
      public function getMontantsAEnvoyer()
    {
        $db = \Config\Database::connect();
        try {
            return $db->query("
                SELECT 
                    o.nom as operateur,
                    o.prefixe,
                    SUM(t.montant) as montant_total,
                    SUM(t.frais_commission) as commission_totale,
                    COUNT(t.id) as nb_transactions
                FROM transactions t
                LEFT JOIN operateurs o ON o.id = t.operateur_destinataire_id
                WHERE t.operateur_destinataire_id IS NOT NULL
                GROUP BY o.nom
                ORDER BY montant_total DESC
            ")->getResultArray();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    /**
     * Créer un transfert avec opérateur destinataire (V2)
     */
    public function createTransfertWithOperateur($data)
    {
        $clientModel = new ClientModel();
        $operateurModel = new OperateurModel();
        
        $client = $clientModel->find($data['client_id']);
        if (!$client) {
            return ['success' => false, 'message' => 'Client non trouvé'];
        }

        $typeOpModel = new TypeOperationModel();
        $typeOp = $typeOpModel->find($data['type_operation_id']);
        if (!$typeOp) {
            return ['success' => false, 'message' => 'Type d\'opération invalide'];
        }

        $typeLibelle = strtolower($typeOp['libelle']);
        $nouveauSolde = $client['solde'];
        $frais = $data['frais'] ?? 0;
        $commission = 0;

        if ($typeLibelle === 'transfert' && isset($data['telephone_destinataire'])) {
            $telephoneDest = preg_replace('/[^0-9]/', '', $data['telephone_destinataire']);
            $operateurDest = $operateurModel->getOperateurByTelephone($telephoneDest);
            
            if ($operateurDest) {
                $data['operateur_destinataire_id'] = $operateurDest['id'];
                $commission = ($data['montant'] * $operateurDest['commission_pourcentage']) / 100;
                $data['frais_commission'] = $commission;
            }
            
            $destinataire = $clientModel->findByTelephone($telephoneDest);
            if ($destinataire) {
                $data['destinataire_id'] = $destinataire['id'];
            }
        }

        $totalADebiter = $data['montant'] + $frais + $commission;
        if ($client['solde'] < $totalADebiter) {
            return ['success' => false, 'message' => 'Solde insuffisant'];
        }
        $nouveauSolde -= $totalADebiter;

        $db = \Config\Database::connect();
        $db->transStart();

        $operationId = $this->insert($data);
        if (!$operationId) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement'];
        }

        $clientModel->update($data['client_id'], ['solde' => $nouveauSolde]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Erreur lors de la transaction'];
        }

        return [
            'success' => true,
            'message' => 'Transfert réussi',
            'operation_id' => $operationId,
            'nouveau_solde' => $nouveauSolde,
            'frais_appliques' => $frais,
            'commission' => $commission
        ];
    }
}