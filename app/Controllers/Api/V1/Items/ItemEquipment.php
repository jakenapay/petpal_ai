<?php

namespace App\Controllers\Api\V1\Items;

use App\Controllers\BaseController;
use App\Models\ItemEquippedModel;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\InventoryModel;
use App\Models\ItemAccessoriesModel;
use App\Models\PetModel;

class ItemEquipment extends BaseController
{
    public function index()
    {
        //
    }

    /**
     * Title
     * URL: 
     * Method: 
     * Params: 
     * Response:  
     * 
     * Todo on monday: 
     * - validation for pet owned by user
     * - validation for item accessory owned by user
     * - validation for 
     */
    public function equipItem()
    {
        $db = \Config\Database::connect();
        $inventoryModel = new InventoryModel();
        $itemAccessoriesModel = new ItemAccessoriesModel();
        $itemEquippedModel = new ItemEquippedModel();
        $petModel = new PetModel();

        $userId = authorizationCheck($this->request);
        $json = $this->request->getJSON();
        $itemId = $json->itemId ?? null;
        $petId = $json->petId ?? null;
        $breedName = strtolower($json->breedName ?? null);
        $addressableUrl = $json->addressableUrl ?? null;
        $subCategory = strtolower($json->subCategory ?? null);

        if (!$userId) {
            return $this->response->setJSON([
                'message' => 'Unauthorized',
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }


        if (!$itemId || !$petId) {
            return $this->response->setJSON([
                'message' => 'Missing itemId or petId',
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        if (!is_numeric($itemId) || !is_numeric($petId)) {
            return $this->response->setJSON([
                'message' => 'Invalid itemId or petId format.',
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (!isset($breedName, $addressableUrl, $subCategory)) {
            return $this->response->setJSON([
                'message' => 'Missing breed info or item visuals'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $isPetOwned = $petModel->isPetOwnedByUser($userId, $petId);
        if (!$isPetOwned) {
            return $this->response->setJSON([
                'message' => 'Pet is not owned'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $equipCheck = $inventoryModel->isItemEquippable($userId, $itemId);
        if (!$equipCheck['equippable']) {
            return $this->response->setJSON([
                'message' => $equipCheck['reason']
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $slotType = $itemAccessoriesModel->getSlotTypeFromItem($itemId);
        if (!$slotType) {
            return $this->response->setJSON([
                'message' => 'Unable to determine item slot type',
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $db->transStart();

        $existing = $itemEquippedModel->checkEquippedItem($userId, $petId, $slotType);

        if ($existing) {
            // Only update if item is changing
            if ($existing['item_id'] != $itemId) {
                // Decrease equipped count for old item
                $inventoryModel->where('user_id', $userId)
                    ->where('item_id', $existing['item_id'])
                    ->set('equipped_count', 'equipped_count - 1', false)
                    ->update();

                // Update equipped_items with new item_id
                $itemEquippedModel->update($existing['id'], [
                    'item_id' => $itemId,
                    'breed_name' => trim($breedName ?? null),
                    'addressable_url' => trim($addressableUrl ?? null),
                    'sub_category' => trim($subCategory ?? null)
                ]);
            }
        } else {
            // Insert new equip record
            $itemEquippedModel->insert([
                'user_id' => $userId,
                'pet_id' => $petId,
                'breed_name' => trim($breedName ?? ''),
                'item_id' => $itemId,
                'slot_type' => $slotType,
                'addressable_url' => trim($addressableUrl ?? ''),
                'sub_category' => trim($subCategory ?? '')
            ]);
        }

        // Always increase equipped count for the new item
        if (!$existing || $existing['item_id'] != $itemId) {
            $inventoryModel->where('user_id', $userId)
                ->where('item_id', $itemId)
                ->set('equipped_count', 'equipped_count + 1', false)
                ->update();
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setJSON([
                'message' => 'Failed to equip item.',
                'db_error' => $db->error()['message'] ?? 'Unknown DB error',
                'equip_errors' => $itemEquippedModel->errors() ?: null,
                'inventory_errors' => $inventoryModel->errors() ?: null,
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON([
            'message' => 'Item equipped successfully',
            'equipped' => [
                'user_id' => $userId,
                'pet_id' => $petId,
                'item_id' => $itemId,
                'slot_type' => $slotType,
                'breed_name' => $breedName,
                'addressable_url' => $addressableUrl,
                'sub_category' => $subCategory,
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);

    }

}
