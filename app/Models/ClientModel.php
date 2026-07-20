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
        'numero_telephone' => 'required|min_length[10]|max_length[15]|is_unique[clients.numero_telephone]',
        'solde' => 'permit_empty|decimal|greater_than_equal[0]'
    ];

    protected $validationMessages = [
        'numero_telephone' => [
            'required' => 'Le numéro de téléphone est obligatoire',
            'is_unique' => 'Ce numéro de téléphone est déjà enregistré',
            'min_length' => 'Le numéro doit avoir au moins 10 caractères'
        ],
        'solde' => [
            'decimal' => 'Le solde doit être un nombre valide',
            'greater_than_equal' => 'Le solde ne peut pas être négatif'
        ]
    ];

    /**
     * Trouver un client par son numéro de téléphone
     */
    public function findByTelephone($telephone)
    {
        return $this->where('numero_telephone', $telephone)->first();
    }

    /**
     * Mettre à jour le solde d'un client
     */
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
            return false; // Solde insuffisant
        }

        return $this->update($clientId, ['solde' => $nouveauSolde]);
    }

    /**
     * Récupérer les statistiques des clients
     */
    public function getStats()
    {
        return [
            'total' => $this->countAll(),
            'total_solde' => $this->selectSum('solde')->get()->getRow()->solde ?? 0
        ];
    }
}