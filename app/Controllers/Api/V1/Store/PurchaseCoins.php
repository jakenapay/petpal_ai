<?php

namespace App\Controllers\Api\V1\Store;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UserModel;
use App\Models\ItemModel;
use App\Models\InventoryModel;
use App\Models\CoinPackageModel;
use App\Models\CoinPackagesPurchaseHistoryModel;
class PurchaseCoins extends BaseController
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

        //get the package ID from the request
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

        //Get the current user balance
        $userModel = new UserModel();
        $user = $userModel->getUserBalance($user_id);
        if (!$user) {
            return $this->response->setJSON(['error' => 'User not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        $userCoins = $user['coins'];

        //get the package details
        $coinPackageModel = new CoinPackageModel();
        $package = $coinPackageModel->getCoinPackageById($package_id);
        if (!$package) {
            return $this->response->setJSON(['error' => 'Coin Package not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //perform the purchase
        // THE PURCHASE LOGIC SHOULD BE HERE
        //for testing, we will not be using any payment gateway

        //check how much the user has to pay
        $packagePrice = $package['real_price'] ?? 0;


        //add the coins to the user balance
        $totalCoins = $package['bonus_coins'] + $package['coin_amount'];
        //update the user balance
        $userCoins += $totalCoins;

        $db = \Config\Database::connect();
        $db->transStart();

        $updateCoins = $userModel->updateCoins($user_id, $userCoins);
        if(!$updateCoins){
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to update coins'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        //log the transaction
        $logData = [
            'user_id' => $user_id,
            'package_id' => $package_id,
            'coins_purchased' => $package['coin_amount'],
            'bonus_coins_received' => $package['bonus_coins'] ?? 0,
            'total_coins_received' => $totalCoins,
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

        $coinPackagePurchaseHistoryModel = new CoinPackagesPurchaseHistoryModel();
        $logResult = $coinPackagePurchaseHistoryModel->logPurchase($logData);
        log_message('debug', 'Purchase log result: ' . json_encode($logResult));
        if (!$logResult) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to log purchase'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        // Commit the transaction
        $db->transComplete();
        if ($db->transStatus() === false) {
            return $this->response->setJSON(['error' => 'Transaction failed'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        return $this->response->setJSON([
            'message' => 'Coins package purchased successfully',
            'coins_received' => $totalCoins,
            'new_balance' => $userModel->getUserBalance($user_id)['coins'],
        ])->setStatusCode(ResponseInterface::HTTP_OK);
        
    }
}
