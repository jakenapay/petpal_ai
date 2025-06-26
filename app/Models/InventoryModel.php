<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\GachaItemModel;

class InventoryModel extends Model
{
    protected $table = 'user_inventory';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'user_id',
        'item_id',
        'acquisition_type_id',
        'quantity',
        'aquisition_date',
        'expiration_date',
        'is_equipped'
    ];

    protected $allowedFieldsForItemList = [
        'item_id',
        'acquisition_type_id',
        'aquisition_date',
        'expiration_date',
        'is_equipped',
        'quantity'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function getUserInventory($user_id)
    {
        $inventory = $this->where('user_id', $user_id)
            ->where('quantity >', 0)
            ->findAll();

        if (empty($inventory))
            return [];

        $itemModel = new \App\Models\ItemModel();

        foreach ($inventory as $key => $value) {
            $itemDetails = $itemModel->getItemById($value['item_id']);

            if ($itemDetails) {
                $inventory[$key] = array_merge($value, $itemDetails);
            } else {
                $inventory[$key]['category_name'] = 'Uncategorized';
            }
        }

        return $inventory;
    }

    public function categorizedItemsInInventory($user_id, $categoryId = null)
    {
        $userInventory = $this->getUserInventory($user_id);

        if ($categoryId !== null) {
            $userInventory = array_filter($userInventory, function ($item) use ($categoryId) {
                return isset($item['category_id']) && $item['category_id'] == $categoryId;
            });
        }

        $categorized = [];

        foreach ($userInventory as $item) {
            $categoryName = $item['category_name'] ?? 'Uncategorized';

            if (!isset($categorized[$categoryName])) {
                $categorized[$categoryName] = [];
            }

            $categorized[$categoryName][] = $item;
        }

        return $categorized;
    }


    public function checkItemInInventory($user_id, $item_id)
    {
        return $this->where('user_id', $user_id)
            ->where('item_id', $item_id)
            ->first();
    }
    public function updateUserInventory($updateItemlist)
    {
        $user_id = $updateItemlist['user_id'];
        $item_id = $updateItemlist['item_id'];
        $quantity = $updateItemlist['quantity'];
        $acquisition_date = $updateItemlist['acquisition_date'];
        $acquisition_type = $updateItemlist['acquisition_type_id'] ?? 1;

        $addData = [
            'user_id' => $user_id,
            'item_id' => $item_id,
            'quantity' => $quantity,
            'acquisition_type_id' => $acquisition_type,
            'acquisition_date' => $acquisition_date,
            'expiration_date' => null
        ];
        log_message('info', 'Add data: ' . json_encode($addData));
        $existing = $this->checkItemInInventory($user_id, $item_id);

        if ($existing) {
            $newQuantity = $existing['quantity'] + $quantity;
            try {
                $this->where('user_id', $user_id)
                    ->where('item_id', $item_id)
                    ->set('quantity', $newQuantity)
                    ->set('acquisition_date', $acquisition_date)
                    ->set('expiration_date', null)
                    ->update();
            } catch (\Exception $e) {
                log_message('error', 'Failed to update inventory: ' . $e->getMessage());
            }
        } else {
            try {
                $this->insert($addData);
            } catch (\Exception $e) {
                log_message('error', 'Failed to insert data: ' . $e->getMessage());
            }
        }

        return $this->getUserInventory($user_id);
    }

    // public function updateItemList($user_id, $item_list){
    //     log_message('debug', "updateItemList($user_id, $item_list)");
    //     $this->set('items_list', $item_list)
    //         ->where('user_id', $user_id)
    //         ->update();
    // }
    public function addItemsToInventory($user_id, $items)
    {
        $inventoryModel = new InventoryModel();
        $acquisition_type_id = 4; // Gacha

        foreach ($items as $item) {
            $item_id = $this->getItemIdFromItemCode($item['item_id']);
            if (!$item_id) {
                continue;
            }

            $now = date('Y-m-d H:i:s');
            $existing = $inventoryModel
                ->where('user_id', $user_id)
                ->where('item_id', $item_id)
                ->where('acquisition_type_id', $acquisition_type_id)
                ->first();

            if ($existing) {
                $inventoryModel->update($existing['id'], [
                    'quantity' => $existing['quantity'] + 1
                ]);
            } else {
                $inventoryModel->insert([
                    'user_id' => $user_id,
                    'item_id' => $item_id,
                    'acquisition_type_id' => $acquisition_type_id,
                    'acquisition_date' => $now,
                    'expiration_date' => null,
                    'is_equipped' => false,
                    'quantity' => 1
                ]);
            }
        }
        return true;
    }

    public function handleAddItemsToInventory($user_id, $items)
    {
        $inventoryModel = new InventoryModel();
        $acquisition_type_id = 4; // Gacha

        foreach ($items as $item) {
            $item_id = $this->getItemIdFromItemCode($item['item_id']);
            if (!$item_id) {
                continue;
            }

            $now = date('Y-m-d H:i:s');
            $existing = $inventoryModel
                ->where('user_id', $user_id)
                ->where('item_id', $item_id)
                ->where('acquisition_type_id', $acquisition_type_id)
                ->first();

            if ($existing) {
                $inventoryModel->update($existing['id'], [
                    'quantity' => $existing['quantity'] + 1
                ]);
            } else {
                $inventoryModel->insert([
                    'user_id' => $user_id,
                    'item_id' => $item_id,
                    'acquisition_type_id' => $acquisition_type_id,
                    'acquisition_date' => $now,
                    'expiration_date' => null,
                    'is_equipped' => false,
                    'quantity' => 1
                ]);
            }
        }
        return true;
    }

    private function getItemIdFromItemCode($item_code)
    {
        // $itemModel = new ItemModel(); 
        $itemModel = new GachaItemModel();
        $item = $itemModel->where('item_id', $item_code)->first();
        return $item ? $item['item_id'] : null;
    }

    public function addDefaultItems($user_id, $defaultItems)
    {
        $acquisition_type_id = 1; //Registration x Default
        $now = date('Y-m-d H:i:s');

        $dataToInsert = [];
        foreach ($defaultItems as $item) {
            $dataToInsert[] = [
                'user_id' => $user_id,
                'item_id' => $item['item_id'],
                'acquisition_type_id' => $acquisition_type_id,
                'acquisition_date' => $now,
                'expiration_date' => null,
                'is_equipped' => false,
                'quantity' => $item['quantity'] ?? 1
            ];
        }
        $this->insertBatch($dataToInsert);

        return $this->getUserInventory($user_id);
    }

    public function reduceItemQuantity($user_id, $item_id, $quantity){
        
    }


}
