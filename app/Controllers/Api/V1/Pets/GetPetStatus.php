<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PetStatusModel;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class GetPetStatus extends BaseController
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

        $petStatusModel = new PetStatusModel();
        $petStatus = $petStatusModel->where('pet_id', $petId)->first();

        if (!$petStatus) {
            return $this->response->setJSON(['error' => 'Pet status not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Pet status retrieved successfully',
            'data' => $petStatus])
            ->setStatusCode(ResponseInterface::HTTP_OK);
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
