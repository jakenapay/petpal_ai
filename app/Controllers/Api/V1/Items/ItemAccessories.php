<?php

namespace App\Controllers\Api\V1\Items;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ItemAccessoriesModel;

class ItemAccessories extends BaseController
{
    public function index()
    {
        $model = new ItemAccessoriesModel();

        // AUTH BEARER TOKEN REMOVED
        // $userId = authorizationCheck($this->request);

        // if (!$userId) {
        //     return $this->response->setJSON(['error' => 'Token required or invalid'])
        //         ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        // }

        $accessories = $model->getAllAccessories();
        return $this->response->setJSON([
            'status' => 'success',
            'data' => $accessories
        ]);


    }
}
