<?php

namespace App\Controllers\Api\V1\Store;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\CoinPackageModel;
use App\Models\CoinPackagesPurchaseHistoryModel;

class CoinPackagesTransaction extends BaseController
{
    public function index()
    {
        //user authorization
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;

        $coinPackagesPurchaseHistoryModel = new CoinPackagesPurchaseHistoryModel();
        $transactions = $coinPackagesPurchaseHistoryModel->getAllTransactions($userId);
        if (!$transactions) {
            return $this->response->setJSON(['error' => 'No Coin Package Transactions found'])
                ->setStatusCode(ResponseInterface::HTTP_OK);
        }
        $packageId = array_unique(array_column($transactions, 'package_id'));

        //get the package
        $coinPackageModel = new CoinPackageModel();
        $packages = $coinPackageModel->getCoinpackages();
        if (!$packages) {
            return $this->response->setJSON(['error' => 'No Coin Packages found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        $packageMap = [];
        foreach ($packages as $package) {
            $packageMap[$package['package_id']] = $package['package_name'];
        }

        foreach ($transactions as &$transaction) {
            $packageId = $transaction['package_id'];
            if (isset($packageMap[$packageId])) {
                $transaction['package'] = [
                    'package_name' => $packageMap[$packageId],
                ];
            }
        }

        return $this->response->setJSON([
            'message' => 'Coin Package Transactions retrieved successfully',
            'data' => $transactions
        ])
            ->setStatusCode(ResponseInterface::HTTP_OK);
        

    }
}
