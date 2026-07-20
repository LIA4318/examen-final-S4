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

    protected $validationRules = [
        'libelle' => 'required|max_length[100]'
    ];

    protected $validationMessages = [
        'libelle' => [
            'required' => 'Le libellé du type d\'opération est obligatoire',
            'max_length' => 'Le libellé ne doit pas dépasser 100 caractères'
        ]
    ];

    public function getActiveTypes()
    {
        return $this->findAll();
    }

    public function findByLibelle($libelle)
    {
        return $this->where('libelle', strtolower($libelle))->first();
    }

    public function typeExists($libelle)
    {
        return $this->where('libelle', strtolower($libelle))->countAllResults() > 0;
    }
    
    public function findByCode($code)
    {
        return $this->findByLibelle($code);
    }
}