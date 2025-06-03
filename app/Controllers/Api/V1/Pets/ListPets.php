<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use App\Models\PetModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use App\Models\PetLifeStageModel;

class ListPets extends BaseController
{
    public function index()
    {
        // $authHeader = $this->request->getHeaderLine('Authorization');
        // $token = str_replace('Bearer ', '', $authHeader);

        // if (!$token) {
        //     return $this->response->setJSON(['error' => 'Token required'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        // }
        //auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43; // For testing purposes, replace with actual user ID

        $userModel = new PetModel();
        $pets = $userModel->getPetsByUserId($userId);
        if (empty($pets)) {
            return $this->response->setJSON(['error' => 'No pets found for this user'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        $petLifeStage = new PetLifeStageModel();
        //get the needed pet level to achieve the next level
        foreach ($pets as &$pet) {
            $currentLevel = $petLifeStage->getPetLifeStageByID($pet['level']);
            if (!$currentLevel) {
                return $this->response->setJSON(['error' => 'Life stage not found for pet'])
                    ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
            }

            // Filter only desired fields
            $pet['level'] = [
                'stage_id' => $currentLevel['stage_id'],
                'stage_name' => $currentLevel['stage_name'],
                'experience_required' => $currentLevel['experience_required'],
                'multiplier' => $currentLevel['multiplier'],
            ];

            $nextLevel = $petLifeStage->getPetLifeStageByID($currentLevel['stage_id'] + 1);
            $pet['next_level'] = $nextLevel ? [
                'stage_id' => $nextLevel['stage_id'],
                'stage_name' => $nextLevel['stage_name'],
                'experience_required' => $nextLevel['experience_required'],
                'multiplier' => $nextLevel['multiplier'],
            ] : null;

            $pet['experience_to_next_level'] = $nextLevel
                ? max(0, $nextLevel['experience_required'] - (int)$pet['experience'])
                : null;}

        
        return $this->response->setJSON([
            'message' => 'Pets retrieved successfully',
            'data' => $pets
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
