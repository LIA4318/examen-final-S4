<?php

namespace App\Models;

use CodeIgniter\Model;

class TypeOperationModel extends Model
{
    protected $table            = 'types_operations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = ['libelle'];

    // Validation
    protected $validationRules = [
        'libelle' => 'required|max_length[100]|is_unique[types_operations.libelle]'
    ];

    protected $validationMessages = [
        'libelle' => [
            'required' => 'Le libellé du type d\'opération est obligatoire',
            'is_unique' => 'Ce libellé existe déjà'
        ]
    ];

    /**
     * Récupérer tous les types actifs
     * (Adapté à votre structure)
     */
    public function getActiveTypes()
    {
        return $this->findAll();
    }

    /**
     * Récupérer un type par son libellé
     */
    public function findByLibelle($libelle)
    {
        return $this->where('libelle', $libelle)->first();
    }

    /**
     * Vérifier si un type existe
     */
    public function typeExists($libelle)
    {
        return $this->where('libelle', $libelle)->countAllResults() > 0;
    }
    
    /**
     * Pour la compatibilité avec le code existant
     */
    public function findByCode($code)
    {
        return $this->findByLibelle($code);
    }
}