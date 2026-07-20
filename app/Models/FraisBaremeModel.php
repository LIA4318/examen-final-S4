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

    // Validation avec règles correctes
    protected $validationRules = [
        'type_operation_id' => 'required|integer',
        'montant_min'       => 'required|numeric',
        'montant_max'       => 'required|numeric',
        'frais'             => 'required|numeric'
    ];

    protected $validationMessages = [
        'type_operation_id' => [
            'required' => 'Le type d\'opération est obligatoire',
            'integer' => 'Le type d\'opération doit être un nombre'
        ],
        'montant_min' => [
            'required' => 'Le montant minimum est obligatoire',
            'numeric' => 'Le montant minimum doit être un nombre'
        ],
        'montant_max' => [
            'required' => 'Le montant maximum est obligatoire',
            'numeric' => 'Le montant maximum doit être un nombre'
        ],
        'frais' => [
            'required' => 'Le frais est obligatoire',
            'numeric' => 'Le frais doit être un nombre'
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