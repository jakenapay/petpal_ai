<?php

namespace App\Controllers\API\V1\Store;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ItemModel;

class Items extends BaseController
{
    public function index()
    {
        
    }
    public function getItemsbyCategory($categoryId)
    {
        // $userId = authorizationCheck($this->request);
        // if (!$userId) {
        //     return $this->response->setJSON([
        //         'message' => 'Unauthorized',
        //     ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        // }
        $itemModel = new ItemModel();
        $items = $itemModel->getItemsByCategory($categoryId);
        if (!$items) {
            return $this->response->setJSON([
                'message' => 'No items found',
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->response->setJSON([
            'message' => 'Items retrieved successfully',
            'data' => $items,
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function search()
    {
        // $userId = authorizationCheck($this->request);
        // if (!$userId) {
        //     return $this->response->setJSON(['message' => 'Unauthorized'])
        //         ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        // }

        $filters = $this->request->getGet(); 
        $itemModel = new ItemModel();
        $items = $itemModel->searchItems($filters);

        return $this->response->setJSON([
            'message' => 'Items retrieved successfully',
            'data' => $items,
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

    public function getFeaturedItems(){
        // $userId = authorizationCheck($this->request);
        // if (!$userId) {
        //     return $this->response->setJSON(['message' => 'Unauthorized'])
        //         ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        // }
        $itemModel = new ItemModel();
        $items = $itemModel->featuredItems();
        if (!$items) {
            return $this->response->setJSON([
                'message' => 'No items found',
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->response->setJSON([
            'message' => 'Featured Items retrieved successfully',
            'data' => $items,
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
