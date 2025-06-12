<?php

namespace App\Controllers\Api\V1\Store;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ItemCategoriesModel;

class GetItemCategories extends BaseController
{
    public function index()
    {
        //auth chech
        // $userId = authorizationCheck($this->request);
        // if (!$userId) {
        //     return $this->response->setJSON([
        //         'message' => 'Unauthorized',
        //     ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        // }
        $itemCategoriesModel = new ItemCategoriesModel();
        $itemCategories = $itemCategoriesModel->findAll();
        if (!$itemCategories) {
            return $this->response->setJSON([
                'message' => 'No item categories found',
            ])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $this->response->setJSON([
            'message' => 'Item categories retrieved successfully',
            'data' => $itemCategories,
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
