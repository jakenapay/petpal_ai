<?php

namespace App\Controllers\Api\V1\Auth;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class Logout extends BaseController
{
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
}
