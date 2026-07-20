<?php

namespace App\Models;

use CodeIgniter\Model;

class ClientModel extends Model
{
    protected $table            = 'clients';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = [
        'numero_telephone',
        'solde',
        'date_creation'
    ];

    protected $validationRules = [
        'numero_telephone' => 'required|min_length[10]|max_length[15]',
        'solde' => 'permit_empty|numeric'
    ];

    protected $validationMessages = [
        'numero_telephone' => [
            'required' => 'Le numéro de téléphone est obligatoire',
            'min_length' => 'Le numéro doit avoir au moins 10 caractères',
            'max_length' => 'Le numéro ne doit pas dépasser 15 caractères'
        ],
        'solde' => [
            'numeric' => 'Le solde doit être un nombre valide'
        ]
    ];

    public function findByTelephone($telephone)
    {
        return $this->where('numero_telephone', $telephone)->first();
    }

    public function updateSolde($clientId, $montant, $operation = 'add')
    {
        $client = $this->find($clientId);
        if (!$client) {
            return false;
        }

        $nouveauSolde = ($operation === 'add') 
            ? $client['solde'] + $montant 
            : $client['solde'] - $montant;

        if ($nouveauSolde < 0) {
            return false;
        }

        return $this->update($clientId, ['solde' => $nouveauSolde]);
    }

    public function getStats()
    {
        return [
            'total' => $this->countAll(),
            'total_solde' => $this->selectSum('solde')->get()->getRow()->solde ?? 0
        ];
    }
}