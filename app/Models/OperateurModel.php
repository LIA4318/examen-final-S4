<?php

namespace App\Models;

use CodeIgniter\Model;

class OperateurModel extends Model
{
    protected $table = 'operateurs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['nom', 'code', 'prefixe', 'commission_pourcentage', 'actif'];

    protected $validationRules = [
        'nom' => 'required|max_length[100]',
        'code' => 'required|max_length[20]|is_unique[operateurs.code]',
        'prefixe' => 'required|max_length[10]',
        'commission_pourcentage' => 'permit_empty|decimal|greater_than_equal[0]'
    ];

    protected $validationMessages = [
        'nom' => ['required' => 'Le nom de l\'opérateur est obligatoire'],
        'code' => [
            'required' => 'Le code est obligatoire',
            'is_unique' => 'Ce code existe déjà'
        ],
        'prefixe' => ['required' => 'Le préfixe est obligatoire'],
        'commission_pourcentage' => [
            'decimal' => 'Le pourcentage doit être un nombre valide',
            'greater_than_equal' => 'Le pourcentage ne peut pas être négatif'
        ]
    ];

    public function getActiveOperateurs()
    {
        return $this->where('actif', 1)->findAll();
    }

    public function findByPrefixe($prefixe)
    {
        return $this->where('prefixe', $prefixe)->first();
    }

    public function findByCode($code)
    {
        return $this->where('code', strtoupper($code))->first();
    }

    public function getOperateurByTelephone($telephone)
    {
        $prefixe = substr($telephone, 0, 3);
        return $this->where('prefixe', $prefixe)->first();
    }

    public function getStats()
    {
        $db = \Config\Database::connect();
        return $db->query("
            SELECT 
                o.nom,
                o.code,
                o.prefixe,
                o.commission_pourcentage,
                COUNT(t.id) as nb_transactions,
                SUM(t.montant) as total_montant,
                SUM(t.frais) as total_frais,
                SUM(t.frais_commission) as total_commission,
                SUM(t.frais + t.frais_commission) as total_gains
            FROM operateurs o
            LEFT JOIN transactions t ON t.operateur_destinataire_id = o.id AND t.statut = 'SUCCES'
            GROUP BY o.id
            ORDER BY o.nom
        ")->getResultArray();
    }
}