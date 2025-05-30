<?php

namespace App\Models;

use CodeIgniter\Model;

class InventoryModel extends Model
{
    protected $table            = 'user_inventory';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'items_list',
        'scene_bundles_list',
        'last_updated'
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
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function updateItemList($user_id, $item_list){
        log_message('debug', "updateItemList($user_id, $item_list)");
        $this->set('items_list', $item_list)
            ->where('user_id', $user_id)
            ->update();
    }
        public function addItemsToInventory($user_id, $items)
    {
        $inventoryModel = new InventoryModel();
        $acquisition_type_id = 4; // Gacha

        foreach ($items as $item) {
            $item_id = $this->getItemIdFromItemCode($item['item_id']); // E.g. map "pet_004_siamese_cat" to 1
            $now = date('Y-m-d H:i:s');

            // Check if user already has this item from gacha
            $existing = $inventoryModel
                ->where('user_id', $user_id)
                ->where('item_id', $item_id)
                ->where('acquisition_type_id', $acquisition_type_id)
                ->first();

            if ($existing) {
                // Just increase quantity
                $inventoryModel->update($existing['id'], [
                    'quantity' => $existing['quantity'] + 1
                ]);
            } else {
                // Create new inventory row
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
    }

    private function getItemIdFromItemCode($item_code)
    {
        $itemModel = new ItemModel(); 
        $item = $itemModel->where('item_code', $item_code)->first();
        return $item ? $item['item_id'] : null;
    }


}
