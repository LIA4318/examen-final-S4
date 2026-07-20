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
        'client_destinataire_id'
    ];

    // Pas de timestamps automatiques CodeIgniter : la colonne date_transaction
    // a un DEFAULT CURRENT_TIMESTAMP géré directement par SQLite.
    protected $useTimestamps = false;

    // Validation
    protected $validationRules = [
        'client_id'          => 'required|integer|is_not_unique[clients.id]',
        'type_operation_id'  => 'required|integer|is_not_unique[types_operations.id]',
        'montant'            => 'required|decimal|greater_than[0]',
        'frais'              => 'permit_empty|decimal|greater_than_equal_to[0]'
    ];

    protected $validationMessages = [
        'client_id' => [
            'required'    => 'Le client est obligatoire',
            'is_not_unique' => 'Le client spécifié n\'existe pas'
        ],
        'montant' => [
            'required'     => 'Le montant est obligatoire',
            'greater_than' => 'Le montant doit être supérieur à 0'
        ]
    ];

    /**
     * Créer une nouvelle opération (dépôt, retrait ou transfert)
     */
    public function createOperation($data)
    {
        $clientModel = new ClientModel();
        $client = $clientModel->find($data['client_id']);

        if (!$client) {
            return ['success' => false, 'message' => 'Client non trouvé'];
        }

        $typeOpModel = new TypeOperationModel();
        $typeOp = $typeOpModel->find($data['type_operation_id']);

        if (!$typeOp) {
            return ['success' => false, 'message' => 'Type d\'opération invalide'];
        }

        $frais = $data['frais'] ?? 0;
        $totalMontant = $data['montant'] + $frais;
        $nouveauSolde = $client['solde'];

        $typeKey = strtolower($typeOp['libelle']);

        switch ($typeKey) {
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
                if (empty($data['client_destinataire_id'])) {
                    return ['success' => false, 'message' => 'Destinataire requis pour un transfert'];
                }
                $destinataire = $clientModel->find($data['client_destinataire_id']);
                if (!$destinataire) {
                    return ['success' => false, 'message' => 'Destinataire non trouvé'];
                }
                $nouveauSolde -= $totalMontant;
                break;

            default:
                return ['success' => false, 'message' => 'Type d\'opération non pris en charge'];
        }

        // Démarrer la transaction SQL
        $db = \Config\Database::connect();
        $db->transStart();

        $insertData = [
            'client_id'         => $data['client_id'],
            'type_operation_id' => $data['type_operation_id'],
            'montant'           => $data['montant'],
            'frais'             => $frais,
        ];
        if (!empty($data['client_destinataire_id'])) {
            $insertData['client_destinataire_id'] = $data['client_destinataire_id'];
        }

        $operationId = $this->insert($insertData);

        if (!$operationId) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Erreur lors de l\'enregistrement de l\'opération'];
        }

        $updated = $clientModel->update($data['client_id'], ['solde' => $nouveauSolde]);

        if (!$updated) {
            $db->transRollback();
            return ['success' => false, 'message' => 'Erreur lors de la mise à jour du solde'];
        }

        // Pour un transfert : créditer le destinataire (sans frais pour lui)
        if ($typeKey === 'transfert') {
            $destinataire = $clientModel->find($data['client_destinataire_id']);
            $nouveauSoldeDest = $destinataire['solde'] + $data['montant'];
            $clientModel->update($data['client_destinataire_id'], ['solde' => $nouveauSoldeDest]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'message' => 'Erreur lors de la transaction'];
        }

        return [
            'success'          => true,
            'message'          => 'Opération réussie',
            'operation_id'     => $operationId,
            'nouveau_solde'    => $nouveauSolde,
            'frais_appliques'  => $frais
        ];
    }

    /**
     * Récupérer l'historique d'un client (avec le libellé du type d'opération
     * et le numéro du destinataire pour les transferts)
     */
    public function getClientHistory($clientId, $limit = 50, $offset = 0)
    {
        $db = \Config\Database::connect();
        return $db->table('transactions tr')
                   ->select('tr.id, tr.montant, tr.frais, tr.date_transaction, t.libelle as type_libelle, dest.numero_telephone as destinataire_numero')
                   ->join('types_operations t', 't.id = tr.type_operation_id')
                   ->join('clients dest', 'dest.id = tr.client_destinataire_id', 'left')
                   ->where('tr.client_id', $clientId)
                   ->orderBy('tr.date_transaction', 'DESC')
                   ->limit($limit, $offset)
                   ->get()
                   ->getResultArray();
    }

    /**
     * Statistiques globales des opérations
     */
    public function getStats()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT
                t.libelle as type_operation,
                COUNT(tr.id) as nb_operations,
                SUM(tr.montant) as total_montant,
                SUM(tr.frais) as total_frais,
                AVG(tr.montant) as montant_moyen,
                MIN(tr.montant) as min_montant,
                MAX(tr.montant) as max_montant
            FROM transactions tr
            JOIN types_operations t ON t.id = tr.type_operation_id
            GROUP BY t.libelle
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
                t.libelle as type_operation,
                COUNT(tr.id) as nb_operations,
                SUM(tr.frais) as total_gains,
                AVG(tr.frais) as gain_moyen
            FROM transactions tr
            JOIN types_operations t ON t.id = tr.type_operation_id
            WHERE tr.frais > 0
            GROUP BY t.libelle
            ORDER BY total_gains DESC
        ")->getResultArray();
    }
}