<?php

namespace App\Controllers\Api\V1\Store;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\DiamondPackageModel;
class DiamondPackages extends BaseController
{
    public function index()
    {
        $model = new DiamondPackageModel();
        $data = $model->getDiamondPackages();
        if (!$data) {
            return $this->response->setJSON(['error' => 'No Diamond Packages found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->response->setJSON([
            'message' => 'Diamond Packages retrieved successfully',
            'data' => $data
        ]);
    }
}
