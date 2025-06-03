<?php

namespace App\Controllers\Api\V1\Store;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ItemModel;
use App\Models\InventoryModel;
use App\Models\UserModel;
use App\Models\ItemTransactionsModel;

class Purchase extends BaseController
{
    public function __construct() {
        date_default_timezone_set('Asia/Manila');
    }
    public function index()
    {
    }

    public function purchaseItem(){
        $user_id = authorizationCheck($this->request);
        if (!$user_id) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $user_id = 43; 
        $userModel = new UserModel();
        $user = $userModel->getUserBalance($user_id);
        if (!$user) {
            return $this->response->setJSON(['error' => 'User not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $data = $this->request->getJSON(true);


        $item_id = $data['item_id'];
        if (!$item_id) {
            return $this->response->setJSON(['error' => 'Item ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        //after getting the item id, get the item
        $itemModel = new ItemModel();
        $item = $itemModel->getItemById($item_id);
        if (!$item) {
            return $this->response->setJSON(['error' => 'Item not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $userInventoryModel = new InventoryModel();
        $userInventory = $userInventoryModel->getUserInventory($user_id);        
        if (!$userInventory) {
            return $this->response->setJSON(['error' => 'User inventory not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //first check
        //how much do the item cost. get the final price.
        $itemPrice = $item['final_price'];

        
        //second check, look for the user inventory's itemlist. if the item is stackable
        $itemStackable = $item['is_stackable'];

        if ($itemStackable === "0"){
            $quantity = 1;

        } else{
            $quantity = $data['quantity'];
        }

        //third check, balance check
        $userCoins = $user['coins'];
        $userDiamonds = $user['diamonds'];
        $totalItemPrice = $itemPrice * $quantity;

        $currency = $data['currency'];

        if ($currency === 'coins'){
            //check if the user has enough coins
            if ($userCoins < $totalItemPrice){
                return $this->response->setJSON(['error' => 'Not enough coins'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }
        }else if ($currency === 'diamonds'){
            //check if the user has enough diamonds
            if ($userDiamonds < $totalItemPrice){
                return $this->response->setJSON(['error' => 'Not enough diamonds'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }
        }else{
            return $this->response->setJSON(['error' => 'Invalid currency'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $updateItemlist = [
            'user_id' => $user_id,
            'item_id' => $item_id,
            'quantity' => $quantity,
            'acquisition_type_id' => 2, //2 for store since this is purchase
            'acquisition_date' => date('Y-m-d H:i:s'),
            'expiration_date' => null,
            'is_equipped' => false,
        ];
        
        // // loop through the itemlist to see if the item is already in the list
        // $itemFound = false;
        // foreach ($itemlist as $key => $value) {
        //     if ($value['item_id'] === $item_id){
        //         // if ($itemStackable === "0"){
        //         //     return $this->response->setJSON(['error' => 'You already have the item'])
        //         //         ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        //         // }
        //         // Update existing item: increase quantity and optionally update other fields
        //         $itemlist[$key]['quantity'] += $quantity;
        //         $itemlist[$key]['acquisition_type_id'] = 2; // corrected spelling
        //         $itemlist[$key]['acquisition_date'] = date('Y-m-d H:i:s');
        //         $itemlist[$key]['expiration_date'] = null;
        //         $itemlist[$key]['is_equipped'] = false;
        //         $itemFound = true;
        //         break;
        //     }
        // }

        // // If item not found in list, add it
        // if (!$itemFound) {
        //     $itemlist[] = [
        //         'item_id' => $item_id,
        //         'acquisition_type_id' => 2, // corrected spelling
        //         'acquisition_date' => date('Y-m-d H:i:s'),
        //         'expiration_date' => null,
        //         'is_equipped' => false,
        //         'quantity' => $quantity
        //     ];
        // }

        // Start DB transaction
        $db = \Config\Database::connect();
        $db->transStart();

        // Update the user inventory
        // $userInventoryModel->updateItemList($user_id, json_encode($itemlist));
        // if ($userInventoryModel->errors()) {
        //     $db->transRollback();
        //     return $this->response->setJSON(['error' => $userInventoryModel->errors()])
        //         ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        // }
        //add the item to the inventory
        $updateUserInventory = $userInventoryModel->updateUserInventory($updateItemlist);
        if($updateUserInventory === false || $updateUserInventory === null ){
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to update user inventory'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        // Update the user's coins and diamonds
        $userCoinsAfterPurchase = $userCoins - $totalItemPrice;
        $userDiamondsAfterPurchase = $userDiamonds - $totalItemPrice;
        if ($currency === 'coins') {
            $updateCoins = $userModel->updateCoins($user_id, $userCoinsAfterPurchase);
            if(!$updateCoins){
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to update coins'])
                    ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }
        } else {
            $updateDiamonds = $userModel->updateDiamonds($user_id, $userDiamondsAfterPurchase);
            if(!$updateDiamonds){
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to update diamonds'])
                    ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }
        }
        //Insert the transaction to transactionhistory
        $ItemTransactionsModel = new ItemTransactionsModel();
        $data = [
            'user_id' => $user_id,
            'item_id' => $item_id,
            'quantity' => $quantity,
            'transaction_type' => 2, 
            'coins_spent' => $totalItemPrice,
            'transaction_date' => date('Y-m-d H:i:s')
        ];
        $insert = $ItemTransactionsModel->insertTransaction($data);
        if (!$insert) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to insert transaction'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        $db->transComplete();

        //get the transaction and return it
        $transaction = $ItemTransactionsModel->getLastTransaction($user_id);
        if ($transaction === null) {
            return $this->response->setJSON(['error' => 'Failed to get transaction'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        return $this->response->setJSON([
            'message' => 'Successfully purchased item',
            'data' => $transaction
        ])->setStatusCode(ResponseInterface::HTTP_OK);


    }
}
