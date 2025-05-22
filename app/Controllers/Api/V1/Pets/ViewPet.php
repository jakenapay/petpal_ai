<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ViewPet extends BaseController
{
    public function index()
    {
        //
    }

    public function show($id)
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = str_replace('Bearer ', '', $authHeader);
        if (!$token) {
            return $this->response->setJSON(['error' => 'Token required'])->setStatusCode(401);
        }

        try {
            $decoded = JWT::decode($token, new Key(getenv('JWT_SECRET'), 'HS256'));
            $userId = $decoded->user_id;
        } catch (\Exception $e) {
            return $this->response->setJSON(['error' => 'Invalid token'])->setStatusCode(401);
        }

        if (!is_numeric($id)) {
            return $this->response->setJSON(['error' => 'Invalid pet ID'])->setStatusCode(400);
        }

        $petModel = new \App\Models\PetModel();
        $pet = $petModel->where('pet_id', $id)->where('user_id', $userId)->first();

        if (!$pet) {
            return $this->response->setJSON(['error' => 'Pet not found'])->setStatusCode(404);
        }

        return $this->response->setJSON($pet);
    }

}
