<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class UpdatePet extends BaseController
{
    public function index()
    {
        //
    }

    public function update($pet_id)
    {

        // Check if the pet ID is valid
        if (!is_numeric($pet_id)) {
            return $this->response->setJSON(['error' => 'Invalid pet ID'])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Content Type
        $contentType = $this->request->getHeaderLine('Content-Type');
        if ($contentType !== 'application/json') {
            return $this->response->setJSON(['error' => 'Invalid content type'])->setStatusCode(ResponseInterface::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }

        // Get the JWT token from the Authorization header
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);
        if (!$token) {
            return $this->response->setJSON(['error' => 'Token required'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Decode the JWT token
        try {
            $decoded = \Firebase\JWT\JWT::decode($token, new \Firebase\JWT\Key(getenv('JWT_SECRET'), 'HS256'));
            $userId = $decoded->user_id;
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Invalid token'])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        // Get the pet data from the request with the where clause of the user_id
        $petModel = new \App\Models\PetModel();
        $pet = $petModel->where('pet_id', $pet_id)->where('user_id', $userId)->first();
        if (!$pet) {
            return $this->response->setJSON(['error' => 'Pet not found'])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // Get the data from the request
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
            'breed' => [
                'rules' => 'permit_empty|max_length[50]',
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

        // Validate the input data
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'error' => 'Validation failed',
                'messages' => $this->validator->getErrors()
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Prepare the data for update
        $petData = [
            'user_id' => $data['user_id'] ?? $decoded->user_id,
            'name' => $data['name'],
            'species' => $data['species'],
            'breed' => $data['breed'],
            'appearance' => json_encode($data['appearance']),
            'personality' => $data['personality'],
            'status' => $data['status'],
            'level' => $data['level'],
            'experience' => $data['experience'],
            'abilities' => $data['abilities']
        ];

        // Update the pet in the database
        $petModel->update($pet_id, $petData);
        // Check if the update was successful
        if ($petModel->affectedRows() > 0) {
            return $this->response->setJSON([
                'success' => 'Pet updated successfully',
                'pet_id' => $pet_id,
                'pet' => $petData
            ])->setStatusCode(ResponseInterface::HTTP_OK);
        }
        return $this->response->setJSON(['error' => 'Failed to update pet'])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);

    }
}
