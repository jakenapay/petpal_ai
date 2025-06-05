<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PetStatusModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\PetModel;
use DateTime;
use App\Models\PetLifeStageModel;
class GetPetStatus extends BaseController
{
    public function index($petId)
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Token required or invalid'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43; // For testing purposes, replace with actual user ID retrieval logic

        $petId = (int) $petId;

        if (!$petId) {
            return $this->response->setJSON(['error' => 'Pet ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $petStatusModel = new PetStatusModel();
        $petStatus = $petStatusModel->getPetStatusByPetId($petId);
        if (!$petStatus) {
            return $this->response->setJSON(['error' => 'Pet status not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $petModel = new PetModel();
        $pet = $petModel->getPetById($petId);
        if (!$pet) {
            return $this->response->setJSON(['error' => 'Pet not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //get the pet level 

        $petLevel = $pet['level'] ?? "Not specified";

        //get the pet life stage name

        $petLifeStage = $petLifestageModel = new PetLifeStageModel();
        $petLifeStageData = $petLifestageModel->getPetLifeStageByID($pet['life_stage_id']);
        $petLifeStageName = $petLifeStageData['stage_name'] ?? "Not specified";
        //get the pet experience
        $petExperience = $pet['experience'] ?? 0;

        //get the required experience for next level
        $petLifeStageExperienceRequired = $petLifestageModel->getPetLifeStageByID($pet['life_stage_id'] +1 )['experience_required'] ?? "Max Level";

        /*
            "pet_level": "1",
            "pet_life_stage": "Baby",
            "current_experience": "100",
            "required_experience_for_next_level": "5000",
            "pet_abilities": {
                "learned_commands": [],
                "mastery_levels": []
            },
            "pet_age": "0 years, 0 months, 12 days",
            "personality": "Curious",
            "social_stats": "not implemented yet",
         */
        //get the 

        $petStatusDetails = [
            'pet_level' => $petLevel,
            'pet_life_stage' => $petLifeStageName,
            'current_experience' => $petExperience,
            'required_experience_for_next_level' => $petLifeStageExperienceRequired,
            'pet_abilities' => json_decode($pet['abilities'], true) ?? "Not specified",
            'pet_age' => (new DateTime($pet['birthdate']))->diff(new DateTime())->format('%y years, %m months, %d days'),
            'personality' => $pet['personality'] ?? "Not specified",
            'social_stats' => "not implemented yet", // Placeholder for future implementation

        ];


        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Pet status retrieved successfully',
            'data' => array_merge($petStatus, $petStatusDetails)

            ])
            ->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
