<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfigurationModel extends Model
{
    protected $table            = 'prefixes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = ['prefixe'];

    /**
     * Récupérer les préfixes valides
     */
    public function getPrefixes()
    {
        try {
            $results = $this->findAll();
        } catch (\Exception $e) {
            return ['033', '037']; // Préfixes par défaut si la table n'existe pas encore
        }

        if (empty($results)) {
            return ['033', '037']; // Préfixes par défaut
        }
        
        $prefixes = [];
        foreach ($results as $row) {
            $prefixes[] = $row['prefixe'];
        }
        return $prefixes;
    }

    /**
     * Mettre à jour les préfixes
     */
    public function updatePrefixes($prefixes)
    {
        // Supprimer tous les préfixes existants
        $this->truncate();
        
        // Insérer les nouveaux préfixes
        $data = [];
        foreach ($prefixes as $prefix) {
            $data[] = ['prefixe' => trim($prefix)];
        }
        
        return $this->insertBatch($data);
    }

    /**
     * Vérifier si un numéro a un préfixe valide
     */
    public function isValidPrefix($telephone)
    {
        $prefixes = $this->getPrefixes();
        foreach ($prefixes as $prefix) {
            if (strpos($telephone, $prefix) === 0) {
                return true;
            }
        }
        return false;
    }
}