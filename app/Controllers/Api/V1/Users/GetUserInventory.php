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
        //auth check
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON([
                'message' => 'Unauthorized',
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $inventoryModel = new InventoryModel();
        $inventory = $inventoryModel->where('user_id', $userId)->first();
        $itemList= json_decode($inventory['items_list'], true);



        $itemModel = new ItemModel();
        foreach ($itemList as $key => $value) {
            // Get item details
            $itemDetails = $itemModel->getItemById($value['item_id']);
            
            // Merge basic inventory data with item details
            if ($itemDetails) {
                $itemList[$key] = array_merge($value, $itemDetails);
            } else {
                // If item not found, optionally keep or skip
                $itemList[$key]['error'] = 'Item not found';
            }
        }

        return $this->response->setJSON([
            'message' => 'Inventory retrieved successfully',
            'items_list' => $itemList,
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
