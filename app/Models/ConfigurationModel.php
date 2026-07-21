<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfigurationModel extends Model
{
    protected $table = 'prefixes';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['prefixe'];

    public function getPrefixes()
    {
        try {
            $results = $this->findAll();
        } catch (\Exception $e) {
            return ['033', '037']; // Préfixes par défaut si la table n'existe pas encore
        }

        if (empty($results)) {
            return ['032', '033', '034', '037', '038']; // Préfixes par défaut
        }
        
        $prefixes = [];
        foreach ($results as $row) {
            // Gérer les préfixes multiples séparés par des virgules
            $parts = explode(',', $row['prefixe']);
            foreach ($parts as $part) {
                $prefixes[] = trim($part);
            }
        }
        return array_unique($prefixes);
    }

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