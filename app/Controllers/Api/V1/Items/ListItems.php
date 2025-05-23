<?php

namespace App\Controllers\Api\V1\Items;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ItemModel;

class ListItems extends BaseController
{
    public function index()
    {
        // Check if request method is GET
        if ($this->request->getMethod() !== 'get') {
            return $this->response->setJSON(['error' => 'Invalid request method'])->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED);
        }

        // Return all items
        $itemModel = new ItemModel();
        $items = $itemModel->findAll();

        // Check if items are found
        if (empty($items)) {
            return $this->response->setJSON(['error' => 'No items found'])->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // Return items
        return $this->response->setJSON($items)->setStatusCode(ResponseInterface::HTTP_OK);
    }
}
