<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PetInteractionModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\PetModel;

class GetPetInteractionHistory extends BaseController
{
    public function index($petId)
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;
        $petId = (int) $petId;
        if (!$petId) {
            return $this->response->setJSON(['error' => 'Pet ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        // Validate the pet ID
        $petModel = new PetModel();
        $pet = $petModel->getPetById($petId);
        if (!$pet) {
            return $this->response->setJSON(['error' => 'Pet not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        // Get the interaction history for the pet
        $logInteractionModel = new PetInteractionModel();
        $interactionHistory = $logInteractionModel->GetPetInteractionHistory($petId);

        if (!$interactionHistory) {
            return $this->response->setJSON(['error' => 'No interaction history found for this pet'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        // Return the interaction history
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Pet interaction history retrieved successfully',
            'data' => [
                'history' => $interactionHistory
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);

        

    }
}
