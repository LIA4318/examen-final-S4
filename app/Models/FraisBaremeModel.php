<?php

namespace App\Models;

use CodeIgniter\Model;

class FraisBaremeModel extends Model
{
    protected $table            = 'baremes_frais';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'type_operation_id',
        'montant_min',
        'montant_max',
        'frais'
    ];

    // Validation
    protected $validationRules = [
        'type_operation_id' => 'required|integer|is_not_unique[types_operations.id]',
        'montant_min'       => 'required|decimal|greater_than_equal[0]',
        'montant_max'       => 'required|decimal|greater_than[montant_min]',
        'frais'             => 'required|decimal|greater_than_equal[0]'
    ];

    protected $validationMessages = [
        'type_operation_id' => [
            'required' => 'Le type d\'opération est obligatoire',
            'is_not_unique' => 'Le type d\'opération spécifié n\'existe pas'
        ],
        'montant_min' => [
            'required' => 'Le montant minimum est obligatoire',
            'greater_than_equal' => 'Le montant minimum doit être supérieur ou égal à 0'
        ],
        'montant_max' => [
            'required' => 'Le montant maximum est obligatoire',
            'greater_than' => 'Le montant maximum doit être supérieur au montant minimum'
        ]
    ];

    /**
     * Calculer les frais pour un montant et un type d'opération
     */
    public function calculerFrais($typeOperationId, $montant)
    {
        $bareme = $this->where('type_operation_id', $typeOperationId)
                       ->where('montant_min <=', $montant)
                       ->where('montant_max >=', $montant)
                       ->first();

        if (!$bareme) {
            return 0;
        }

        return (float) $bareme['frais'];
    }

    /**
     * Récupérer les barèmes pour un type d'opération
     */
    public function getByType($typeOperationId)
    {
        return $this->where('type_operation_id', $typeOperationId)
                    ->orderBy('montant_min', 'ASC')
                    ->findAll();
    }

    /**
     * Vérifier si les barèmes se chevauchent
     */
    public function hasOverlap($typeOperationId, $montantMin, $montantMax, $excludeId = null)
    {
        $builder = $this->where('type_operation_id', $typeOperationId)
                        ->groupStart()
                            ->where('montant_min <=', $montantMax)
                            ->where('montant_max >=', $montantMin)
                        ->groupEnd();

        if ($excludeId) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->countAllResults() > 0;
    }

    /**
     * Statistiques des frais par type d'opération
     */
    public function getStatsFrais()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                t.libelle as type_operation,
                COUNT(f.id) as nb_baremes,
                MIN(f.montant_min) as min_montant,
                MAX(f.montant_max) as max_montant,
                AVG(f.frais) as moyenne_frais
            FROM baremes_frais f
            JOIN types_operations t ON t.id = f.type_operation_id
            GROUP BY t.libelle
        ")->getResultArray();
    }
}