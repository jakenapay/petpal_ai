<?php

namespace App\Controllers\API\V1\Users;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\InventoryModel;
use App\Models\ItemModel;
class GetUserInventory extends BaseController
{
    public function index()
    {
        // auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON([
                'message' => 'Unauthorized',
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43; 

        $inventoryModel = new InventoryModel();
        $inventory = $inventoryModel->getUserInventory($userId);
        if (!$inventory) {
            return $this->response->setJSON([
                'message' => 'Inventory not found for this user',
            ])->setStatusCode(ResponseInterface::HTTP_OK);
        }
        return $this->response->setJSON([
            'message' => 'Inventory retrieved successfully',
            'items_list' => $inventory,
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getCategorizedItemsFromInventory($categoryId = null){
        // auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON([
                'message' => 'Unauthorized',
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;

        $inventoryModel = new InventoryModel();
        $inventory = $inventoryModel->categorizedItemsInInventory($userId, $categoryId);
        if (!$inventory) {
            return $this->response->setJSON([
                'message' => 'No items found for the category selected',
            ])->setStatusCode(ResponseInterface::HTTP_OK);
        }
        return $this->response->setJSON([
            'message' => 'Categorized Inventory retrieved successfully',
            'items_list' => $inventory
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
