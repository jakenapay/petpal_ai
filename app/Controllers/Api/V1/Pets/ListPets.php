<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use App\Models\PetModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use CodeIgniter\HTTP\ResponseInterface;
use Exception;

class ListPets extends BaseController
{
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
