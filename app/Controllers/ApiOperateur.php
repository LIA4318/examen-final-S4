<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\TypeOperationModel;
use App\Models\FraisBaremeModel;
use App\Models\OperationModel;
use CodeIgniter\API\ResponseTrait;

class ApiOperateur extends BaseController
{
    use ResponseTrait;

    protected $clientModel;
    protected $typeOperationModel;
    protected $fraisBaremeModel;
    protected $operationModel;

    public function __construct()
    {
        $this->clientModel = new ClientModel();
        $this->typeOperationModel = new TypeOperationModel();
        $this->fraisBaremeModel = new FraisBaremeModel();
        $this->operationModel = new OperationModel();
    }

    /**
     * Récupérer les statistiques en JSON
     */
    public function getStats()
    {
        return $this->respond([
            'success' => true,
            'data' => [
                'clients' => $this->clientModel->getStats(),
                'operations' => $this->operationModel->getStats(),
                'gains' => $this->operationModel->getGainsFrais()
            ]
        ]);
    }

    /**
     * Récupérer la situation d'un client
     */
    public function getClient($telephone = null)
    {
        if (!$telephone) {
            return $this->failValidationErrors('Téléphone du client requis');
        }
        $client = $this->clientModel->findByTelephone($telephone);
        if (!$client) {
            return $this->failNotFound('Client non trouvé');
        }

        return $this->respond([
            'success' => true,
            'data' => $client
        ]);
    }

    /**
     * Calculer les frais pour un montant et type
     */
    public function calculerFrais()
    {
        $typeId = $this->request->getPost('type_operation_id');
        $montant = $this->request->getPost('montant');

        if (!$typeId || !$montant) {
            return $this->failValidationErrors('Type d\'opération et montant requis');
        }

        $frais = $this->fraisBaremeModel->calculerFrais($typeId, $montant);
        
        return $this->respond([
            'success' => true,
            'frais' => $frais,
            'montant' => $montant,
            'total' => $montant + $frais
        ]);
    }
}