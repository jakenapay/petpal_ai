<?php

namespace App\Controllers\API\V1\Store;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CoinPackageModel;
class CoinPackages extends BaseController
{
    public function index()
    {
        //AUTHORIZE FIRST. I WONT DO IT NOW FOR TESTING
        $model = new CoinPackageModel();
        $data = $model->getCoinpackages();
        if (!$data) {
            return $this->response->setJSON(['error' => 'No Coin Packages found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->response->setJSON([
            'message' => 'Coin Packages retrieved successfully',
            'data' => $data
        ]);
    }

}
