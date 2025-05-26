<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PetInteractionModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GetPetInteractionHistory extends BaseController
{
    public function index($petId)
    {
        $userId = authorizationCheck($this->request);
        $petId = (int) $petId;
        if (!$petId) {
            return $this->response->setJSON(['error' => 'Pet ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        // Validate the pet ID
        $petModel = new \App\Models\PetModel();
        $pet = $petModel->getPetById($petId);
        if (!$pet) {
            return $this->response->setJSON(['error' => 'Pet not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        // Get the interaction history for the pet
        $logInteractionModel = new LogInteractionModel();
        $interactionHistory = $logInteractionModel->getInteractionHistoryByPetId($petId);
        

    }
}
