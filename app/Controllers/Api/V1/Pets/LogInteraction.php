<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\LogInteractionModel;

class LogInteraction extends BaseController
{
    public function index()
    {
        $userId = authorizationCheck($this->request);

        $data = $this->request->getJSON(true);
        $validationRules = [
            'pet_id' => 'required|integer',
            'interaction_id' => 'required|integer',
            'item_used' => 'required|integer',
            'affinity_gained' => 'required|decimal',
        ];
        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'error' => 'Validation failed',
                'messages' => $this->validator->getErrors(),
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $interactionModel = new LogInteractionModel();
        $interactionModel->insert($data);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Interaction logged successfully',
            'data' => [
                'interaction_id' => $interactionModel->insertID(),
                'pet_id' => $data['pet_id'],
                'interaction_id' => $data['interaction_id'],
                'item_used' => $data['item_used'],
                'affinity_gained' => $data['affinity_gained'],
            ]
        ])->setStatusCode(ResponseInterface::HTTP_CREATED);
        
    }
}
