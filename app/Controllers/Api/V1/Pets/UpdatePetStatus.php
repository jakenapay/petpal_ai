<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PetStatusModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class UpdatePetStatus extends BaseController
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila');
    }
    public function index($petId)
    {
        $userId = $this->authorizationCheck();
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Token required or invalid'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $data = $this->request->getJSON(true);

        $validationRules = [
            'hunger_level' => 'permit_empty|decimal',
            'happiness_level' => 'permit_empty|decimal',
            'health_level' => 'permit_empty|decimal',
            'energy_level' => 'permit_empty|decimal',
            'cleanliness_level' => 'permit_empty|decimal',
            'stress_level' => 'permit_empty|decimal',
            'current_mood' => 'permit_empty|string',
            'is_sick' => 'permit_empty|in_list[0,1,true,false]',
            'sickness_type' => 'permit_empty|string',
            'sickness_severity' => 'permit_empty|string',
            'hibernation_mode' => 'permit_empty|in_list[0,1,true,false]',
        ];

        if (!$this->validate($validationRules)) {
            return $this->response->setJSON([
                'error' => 'Validation failed',
                'messages' => $this->validator->getErrors(),
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        $data['pet_id'] = (int) $petId;

        $petStatusModel = new PetStatusModel(); 


        $updateData = array_intersect_key(
            $data,
            array_flip($petStatusModel->allowedFields)
        );

        if (isset($updateData['hunger_level'])) {
            $updateData['last_hunger_update'] = date('Y-m-d H:i:s');
        }
        if (isset($updateData['happiness_level'])) {
            $updateData['last_happiness_update'] = date('Y-m-d H:i:s');
        }
        if (isset($updateData['health_level'])) {
            $updateData['last_health_update'] = date('Y-m-d H:i:s');
        }
        if (isset($updateData['cleanliness_level'])) {
            $updateData['last_cleanliness_update'] = date('Y-m-d H:i:s');
        }
        if (isset($updateData['energy_level'])) {
            $updateData['last_energy_update'] = date('Y-m-d H:i:s');
        }

        $updateData['last_status_calculation'] = date('Y-m-d H:i:s');

        $existingStatus = $petStatusModel->where('pet_id', $petId)->first();

        if ($existingStatus) {
            $petStatusModel->where('pet_id', $petId)->set($updateData)->update();
            return $this->response->setJSON(['message' => 'Pet status updated successfully']);
        } else {
            $updateData['pet_id'] = $petId;
            $petStatusModel->insert($updateData);
            return $this->response->setJSON(['message' => 'Pet status created successfully']);
        }
    }


    public function authorizationCheck(){
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);
        if (!$token) {
            return null; 
        }
        try {
            $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
            return $decoded->user_id ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
