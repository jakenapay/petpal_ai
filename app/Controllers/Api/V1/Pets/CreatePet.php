<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use App\Models\PetModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class CreatePet extends BaseController
{
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
            'personality' => $data['personality']
        ];

        $petModel = new PetModel();
        $petModel->insert($petData);

        // insert in pet status table
        

        return $this->response->setJSON([
            'success' => 'Pet created successfully',
            'pet_id' => $petModel->getInsertID()
        ])->setStatusCode(ResponseInterface::HTTP_CREATED);


    }
}
