<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\LogInteractionModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GetPetInteraction extends BaseController
{
    public function index($petId)
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Token required or invalid'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $petId = (int) $petId;
        if (!$petId) {
            return $this->response->setJSON(['error' => 'Pet ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $interactionModel = new LogInteractionModel();

        $interactions = $interactionModel
            ->select('pet_interactions.*, interaction_types.name AS interaction_name, interaction_types.duration')
            ->join('interaction_types', 'interaction_types.interaction_type_id = pet_interactions.interaction_id')
            ->where('pet_interactions.pet_id', $petId)
            ->findAll();


        if (!$interactions) {
            return $this->response->setJSON(['error' => 'No interactions found for this pet'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Pet interactions retrieved successfully',
            'data' => $interactions
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
