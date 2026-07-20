<?php

namespace App\Models;

use CodeIgniter\Model;

class ConfigurationModel extends Model
{
    protected $table            = 'configurations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields = ['cle', 'valeur', 'description'];

    /**
     * Récupérer les préfixes valides
     */
    public function getPrefixes()
    {
        $result = $this->where('cle', 'prefixes_valides')->first();
        if (!$result) {
            return ['033', '037']; // Préfixes par défaut
        }
        
        // Convertir la chaîne en tableau
        $prefixes = explode(',', $result['valeur']);
        return array_map('trim', $prefixes);
    }

    /**
     * Mettre à jour les préfixes
     */
    public function updatePrefixes($prefixes)
    {
        if (is_array($prefixes)) {
            $prefixes = implode(',', $prefixes);
        }
        
        return $this->where('cle', 'prefixes_valides')
                    ->set(['valeur' => $prefixes])
                    ->update();
    }

    /**
     * Récupérer une configuration
     */
    public function getConfig($key)
    {
        $result = $this->where('cle', $key)->first();
        return $result ? $result['valeur'] : null;
    }

    /**
     * Mettre à jour une configuration
     */
    public function setConfig($key, $value)
    {
        $exists = $this->where('cle', $key)->countAllResults() > 0;
        
        if ($exists) {
            return $this->where('cle', $key)->set(['valeur' => $value])->update();
        } else {
            return $this->insert([
                'cle' => $key,
                'valeur' => $value
            ]);
        }
    }
}