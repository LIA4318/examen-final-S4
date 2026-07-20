<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationModel extends Model
{
    protected $table            = 'operations';
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
        'solde_avant',
        'solde_apres',
        'reference',
        'destinataire_id',
        'description',
        'statut',
        'date_creation'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'date_creation';

    // Validation
    protected $validationRules = [
        'client_id' => 'required|integer|is_not_unique[clients.id]',
        'type_operation_id' => 'required|integer|is_not_unique[types_operations.id]',
        'montant' => 'required|decimal|greater_than[0]',
        'frais' => 'permit_empty|decimal|greater_than_equal[0]',
        'statut' => 'permit_empty|in_list[SUCCES,ECHEC,EN_ATTENTE]'
    ];

    protected $validationMessages = [
        'client_id' => [
            'required' => 'Le client est obligatoire',
            'is_not_unique' => 'Le client spécifié n\'existe pas'
        ],
        'montant' => [
            'required' => 'Le montant est obligatoire',
            'greater_than' => 'Le montant doit être supérieur à 0'
        ],
        'statut' => [
            'in_list' => 'Le statut doit être SUCCES, ECHEC ou EN_ATTENTE'
        ]
    ];

    /**
     * Créer une nouvelle opération
     */
    public function createOperation($data)
    {
        // Générer une référence unique
        $data['reference'] = 'REF-' . date('Ymd') . '-' . uniqid();
        
        // Récupérer le solde du client
        $clientModel = new ClientModel();
        $client = $clientModel->find($data['client_id']);
        
        if (!$client) {
            return ['success' => false, 'message' => 'Client non trouvé'];
        }

        $data['solde_avant'] = $client['solde'];
        
        // Calculer le nouveau solde selon le type d'opération
        $typeOpModel = new TypeOperationModel();
        $typeOp = $typeOpModel->find($data['type_operation_id']);
        
        if (!$typeOp) {
            return ['success' => false, 'message' => 'Type d\'opération invalide'];
        }

        $nouveauSolde = $client['solde'];
        $frais = $data['frais'] ?? 0;
        $totalMontant = $data['montant'] + $frais;

        switch ($typeOp['code']) {
            case 'DEPOT':
                $nouveauSolde += $data['montant'];
                break;
            case 'RETRAIT':
                if ($client['solde'] < $totalMontant) {
                    return ['success' => false, 'message' => 'Solde insuffisant'];
                }
                $nouveauSolde -= $totalMontant;
                break;
            case 'TRANSFERT':
                if ($client['solde'] < $totalMontant) {
                    return ['success' => false, 'message' => 'Solde insuffisant'];
                }
                if (!isset($data['destinataire_id']) || !$data['destinataire_id']) {
                    return ['success' => false, 'message' => 'Destinataire requis pour un transfert'];
                }
                // Vérifier que le destinataire existe
                $destinataire = $clientModel->find($data['destinataire_id']);
                if (!$destinataire) {
                    return ['success' => false, 'message' => 'Destinataire non trouvé'];
                }
                $nouveauSolde -= $totalMontant;
                break;
            default:
                return ['success' => false, 'message' => 'Type d\'opération non pris en charge'];
        }

        $data['solde_apres'] = $nouveauSolde;
        $data['statut'] = 'SUCCES';
        
        // Démarrer la transaction
        $db = \Config\Database::connect();
        $db->transStart();

        // Enregistrer l'opération
        $operationId = $this->insert($data);
        
        if (!$operationId) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement de l\'opération'];
        }

        // Mettre à jour le solde du client
        $updated = $clientModel->update($data['client_id'], ['solde' => $nouveauSolde]);
        
        if (!$updated) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du solde'];
        }

        // Pour les transferts, mettre à jour le solde du destinataire
        if ($typeOp['code'] === 'TRANSFERT' && isset($data['destinataire_id'])) {
            $destinataire = $clientModel->find($data['destinataire_id']);
            $nouveauSoldeDest = $destinataire['solde'] + $data['montant'];
            
            // Enregistrer l'opération de réception
            $this->insert([
                'client_id' => $data['destinataire_id'],
                'type_operation_id' => $data['type_operation_id'],
                'montant' => $data['montant'],
                'frais' => 0,
                'solde_avant' => $destinataire['solde'],
                'solde_apres' => $nouveauSoldeDest,
                'reference' => 'REC-' . date('Ymd') . '-' . uniqid(),
                'description' => 'Réception de transfert de ' . $client['numero_telephone'],
                'statut' => 'SUCCES'
            ]);
            
            $clientModel->update($data['destinataire_id'], ['solde' => $nouveauSoldeDest]);
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
                    ->orderBy('date_creation', 'DESC')
                    ->findAll($limit, $offset);
    }

    /**
     * Statistiques globales des opérations
     */
    public function getStats()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                t.nom as type_operation,
                COUNT(o.id) as nb_operations,
                SUM(o.montant) as total_montant,
                SUM(o.frais) as total_frais,
                AVG(o.montant) as montant_moyen,
                MIN(o.montant) as min_montant,
                MAX(o.montant) as max_montant
            FROM operations o
            JOIN types_operations t ON t.id = o.type_operation_id
            WHERE o.statut = 'SUCCES'
            GROUP BY t.nom
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
                t.nom as type_operation,
                COUNT(o.id) as nb_operations,
                SUM(o.frais) as total_gains,
                AVG(o.frais) as gain_moyen
            FROM operations o
            JOIN types_operations t ON t.id = o.type_operation_id
            WHERE o.statut = 'SUCCES' AND o.frais > 0
            GROUP BY t.nom
            ORDER BY total_gains DESC
        ")->getResultArray();
    }
}