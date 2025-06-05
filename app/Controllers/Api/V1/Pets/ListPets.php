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
    // public function index()
    // {
    //     // $userId = authorizationCheck($this->request);
    //     // if (!$userId) {
    //     //     return $this->response->setJSON(['error' => 'Token required'])
    //     //         ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
    //     // }
    //     $userId = 43; 
    //     $PetModel = new PetModel();
    //     $pets = $PetModel->getPetsByUserId($userId);
    //     if (empty($pets)) {
    //         return $this->response->setJSON(['error' => 'No pets found for this user'])
    //             ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
    //     }
    //     $petLifeStage = new PetLifeStageModel();
    //     //get the needed pet level to achieve the next level
    //     foreach ($pets as &$pet) {
    //         $currentLevel = $petLifeStage->getPetLifeStageByID($pet['level']);
    //         if (!$currentLevel) {
    //             return $this->response->setJSON(['error' => 'Life stage not found for pet'])
    //                 ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
    //         }
    //         $pet['level'] = [
    //             'stage_id' => $currentLevel['stage_id'],
    //             'stage_name' => $currentLevel['stage_name'],
    //             'experience_required' => $currentLevel['experience_required'],
    //             'multiplier' => $currentLevel['multiplier'],
    //         ];

    //         $nextLevel = $petLifeStage->getPetLifeStageByID($currentLevel['stage_id'] + 1);
    //         if (!$nextLevel) {
    //             $nextLevel = null; // No next level available
    //         }
    //         $pet['next_level'] = $nextLevel ? [
    //             'stage_id' => $nextLevel['stage_id'],
    //             'stage_name' => $nextLevel['stage_name'],
    //             'experience_required' => $nextLevel['experience_required'],
    //             'multiplier' => $nextLevel['multiplier'],
    //         ] : null;

    //         $pet['experience_to_next_level'] = $nextLevel
    //             ? max(0, $nextLevel['experience_required'] - (int)$pet['experience'])
    //             : null;
    //     }

    //     return $this->response->setJSON([
    //         'pets' => $pets
    //         ])->setStatusCode(ResponseInterface::HTTP_OK);
    // }
    public function index()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);

        if (!$token) {
            return $this->response->setJSON(['error' => 'Token required'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        try {
            $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
            $userId = $decoded->user_id;

            $petModel = new PetModel();
            $pets = $petModel->where('user_id', $userId)->findAll();
            
            return $this->response->setJSON(['pets' => $pets])->setStatusCode(ResponseInterface::HTTP_OK);

        } catch (Exception $e) {
            return $this->response->setJSON(['error' => 'Invalid token'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
    }
}
