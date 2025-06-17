<?php

namespace App\Controllers\Api\V1\Items;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\ItemTransactionsModel;
use App\Models\ItemModel;

class ItemTransactions extends BaseController
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

        $itemTransactionModel = new ItemTransactionsModel();

        $transactions = $itemTransactionModel->getAllTransactions($userId);
        if (!$transactions) {
            return $this->response->setJSON(['error' => 'No Item Transactions found'])
                ->setStatusCode(ResponseInterface::HTTP_OK);
        }
        $itemIds = array_unique(array_column($transactions, 'item_id'));

        // Get the items
        $itemModel = new ItemModel();
        $items = $itemModel->getItems($itemIds); // Returns an array of items
        if (!$items) {
            return $this->response->setJSON(['error' => 'No items found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        $itemMap = [];
        foreach ($items as $item) {
            $itemMap[$item['item_id']] = $item;
        }

        foreach ($transactions as &$transaction) {
            $itemId = $transaction['item_id'];
            if (isset($itemMap[$itemId])) {
                $transaction['item'] = [
                    'name' => $itemMap[$itemId]['item_name'],
                    'category' => $itemMap[$itemId]['category_name'],
                ];
            } else {
                $transaction['item'] = null; // Or a fallback
            }
            unset($transaction['item_id']); // Optional: hide raw ID
        }


        return $this->response->setJSON([
            'message' =>'Item transactions retrieved successfully',
            'transactions' => $transactions
        ])
            ->setStatusCode(ResponseInterface::HTTP_OK);

    }
}
