<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use App\Models\PetModel;
use App\Models\PetStatusModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;
use CodeIgniter\Database\Exceptions\DatabaseException;

class CreatePet extends BaseController
{
    public function createPetStatusDefault($petId)
    {

        $data = [
            'pet_id' => $petId,
            'hunger_level' => 100.00,
            'happiness_level' => 100.00,
            'health_level' => 100.00,
            'cleanliness_level' => 100.00,
            'energy_level' => 100.00,
            'stress_level' => 0.00,
            'current_mood' => null,
            'is_sick' => 0,
            'sickness_type' => null,
            'sickness_severity' => 0.00,
            'hibernation_mode' => 0
        ];
        log_message('info', 'Pet status data: ' . json_encode($data));
        $PetStatusModel = new PetStatusModel();

        try {
            $PetStatusModel->insert($data);
            return true;
        } catch (DatabaseException $e) {
            return $e->getMessage();
        }
    }


    public function index()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);
        if (!$token) {
            return $this->response->setJSON(['error' => 'Token required'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
        $userId = $decoded->user_id;

        $data = $this->request->getJSON(true);
        $rules = [
            'name' => [
                'rules' => 'required|min_length[2]|max_length[50]',
                'filters' => 'trim|strip_tags'
            ],
            'species' => [
                'rules' => 'required|in_list[dog,cat]',
                'filters' => 'trim|strip_tags'
            ],
            'appearance' => [
                'rules' => 'permit_empty',
                'filters' => 'trim|strip_tags'
            ],
            'personality' => [
                'rules' => 'permit_empty',
                'filters' => 'trim|strip_tags'
            ],
            'gender' => [
                'rules' => 'permit_empty',
                'filters' => 'trim|strip_tags'
            ]
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'error' => 'Validation failed',
                'messages' => $this->validator->getErrors()
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $petData = [
            'user_id' => $userId,
            'name' => $data['name'],
            'species' => $data['species'],
            'breed' => $data['breed'] ?? null,
            'appearance' => json_encode($data['appearance']),
            'personality' => $data['personality'],
            'gender' => $data['gender']
        ];

        $db = \Config\Database::connect();
        $db->transStart();

        $petModel = new PetModel();
        $petId = $petModel->insert($petData);
        log_message('info', 'Pet ID: ' . $petId);

        if (!$petId) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to insert pet data'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $petStatusCreated = $this->createPetStatusDefault($petId);
        if ($petStatusCreated !== true) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to create pet status'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $db->transComplete();
        
        return $this->response->setJSON([
            'success' => 'Pet created successfully',
            'pet_id' => $petModel->getInsertID(),
            'pet' => $petData
        ])->setStatusCode(ResponseInterface::HTTP_CREATED);


    }
}
