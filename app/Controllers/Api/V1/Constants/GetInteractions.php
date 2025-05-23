<?php

namespace App\Controllers\Api\V1\Constants;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\InteractionsModel;

class GetInteractions extends BaseController
{
    public function index()
    {
        $userId = authorizationCheck($this->request);
        
        $interactionModel = new InteractionsModel();
        $interactions = $interactionModel->findAll();
        if (!$interactions) {
            return $this->response->setJSON(['error' => 'No interactions found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Interactions retrieved successfully',
            'data' => $interactions
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
