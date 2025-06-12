<?php

namespace App\Controllers\Api\V1\Store;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\ItemModel;
use App\Models\InventoryModel;
use App\Models\DiamondPackageModel;
use App\Models\DiamondPackagesPurchaseHistoryModel;


class PurchaseDiamonds extends BaseController
{
    public function __construct() {
    date_default_timezone_set('Asia/Manila');
    }
    public function index()
    {
        $user_id = authorizationCheck($this->request);
        if (!$user_id) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $user_id = 43; 
        $data = $this->request->getJSON(true);
        if (!$data) {
            return $this->response->setJSON(['error' => 'Invalid request data'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $package_id = $data['package_id'] ?? null;
        if (!$package_id) {
            return $this->response->setJSON(['error' => 'Package ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $userModel = new UserModel();
        $user = $userModel->getUserBalance($user_id);
        if (!$user) {
            return $this->response->setJSON(['error' => 'User not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $userDiamonds = $user['diamonds'];

        $diamondPackageModel = new DiamondPackageModel();
        $package = $diamondPackageModel->getDiamondPackageById($package_id);
        if (!$package) {
            return $this->response->setJSON(['error' => 'Diamond Package not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // Perform the purchase
        // THE PURCHASE LOGIC SHOULD BE HERE
        //for testing, we will not be using any payment gateway

        $packagePrice = $package['real_price'] ?? 0;

        $totalDiamonds = $package['bonus_diamonds'] + $package['diamond_amount'];
        $userDiamonds += $totalDiamonds;

        $db = \Config\Database::connect();
        $db->transStart();

        $updateDiamonds = $userModel->updateDiamonds($user_id, $userDiamonds);
        if (!$updateDiamonds) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to update user diamonds'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Log the purchase
        $logData = [
            'user_id' => $user_id,
            'package_id' => $package_id,
            'diamonds_purchased' => $package['diamond_amount'] ?? 0,
            'bonus_diamonds_received' => $package['bonus_diamonds'] ?? 0,
            'total_diamonds_received' => $totalDiamonds,
            'real_price_paid' => $packagePrice,
            'currency' => 'USD', 
            'payment_method' => 'test', //for testing purposes
            'payment_transaction_id' => 'test-' . time(), //for testing purposes
            'platform' => 'test', //for testing purposes
            'purchase_date' => date('Y-m-d H:i:s'),
            'status' => 'completed', //assuming the purchase is successful
            'receipt_data' => null, //no receipt data for testing
            // 'created_at' => date('Y-m-d H:i:s')
        ];

        $diamondPackagePurchaseHistoryModel = new DiamondPackagesPurchaseHistoryModel();
        $logPurchase = $diamondPackagePurchaseHistoryModel->logPurchase($logData);
        if (!$logPurchase) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to log purchase'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'Transaction failed'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->response->setJSON([
            'message' => 'Diamonds purchased successfully',
            'diamonds_received' => $totalDiamonds,
            'new_balance' => $userModel->getUserBalance($user_id)['diamonds'],
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
