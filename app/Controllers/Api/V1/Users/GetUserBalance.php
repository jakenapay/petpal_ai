<?php

namespace App\Controllers\API\V1\Users;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
class GetUserBalance extends BaseController
{
    public function index()
    {
        //auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON([
                'message' => 'Unauthorized',
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        if (!$user) {
            return $this->response->setJSON([
                'message' => 'User not found',
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        $balance = $userModel->getUserBalance($userId);

        return $this->response->setJSON([
            'message' => 'User balance retrieved successfully',
            'balance' => $balance,
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
