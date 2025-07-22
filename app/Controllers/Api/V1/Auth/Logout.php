<?php

namespace App\Controllers\Api\V1\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Logout extends BaseController
{
    public function __construct(){
        date_default_timezone_set('Asia/Manila');
    }
    public function index()
    {
        // Destroy the session to log the user out
        session()->destroy();

        // Return JSON response confirming the logout
        return $this->response
            ->setStatusCode(ResponseInterface::HTTP_OK)
            ->setJSON([
                'status'  => 'success',
                'message' => 'Logged out successfully'
            ]);
    }
    
    public function userLogout(){
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        //update the user's logout_time
        $userModel = new \App\Models\UserModel();
        $userModel->update($userId, ['logout_time' => date('Y-m-d H:i:s')]);
        if (!$userModel->errors()) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_OK)
                ->setJSON([
                    'status'  => 'success',
                    'message' => 'Logged out successfully'
                ]);
        }
    }
}
