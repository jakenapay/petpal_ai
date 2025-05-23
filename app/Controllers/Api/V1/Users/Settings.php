<?php

namespace App\Controllers\Api\V1\Users;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\Exceptions\DatabaseException;

class Settings extends BaseController
{
    public function index()
    {
        //
    }

    public function update(){
        
    }

    public function db()
    {
        try {
            \Config\Database::connect()->connect();
            return $this->response->setJSON(['status' => 'success', 'message' => 'Database connected'])->setStatusCode(200);
        } catch (DatabaseException $e) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database connection failed'])->setStatusCode(500);
        }
    }
}
