<?php

namespace App\Controllers\Api\V1\Items;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ItemCategoriesModel;
class ListItemsCategories extends BaseController
{
    public function index()
    {
        // Check if request method is GET
        if ($this->request->getMethod() !== 'GET') {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED);
        }

        // Return all items categories
        $itemCategoriesModel = new ItemCategoriesModel();
        $categories = $itemCategoriesModel->findAll();

        // Check if categories are found
        if (empty($categories)) {
            return $this->response->setJSON(['error' => 'No categories found'])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // Return categories
        return $this->response->setJSON($categories)->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
