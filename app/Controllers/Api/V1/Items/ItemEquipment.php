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
     * Title: Equip Pet Item(s)
     * URL: /api/equip-item
     * Method: POST
     * Params (JSON Body):
     *  - petId (int) – ID of the pet to equip items to (required)
     *  - itemId (array of objects) – List of items to equip (required)
     *      Each item object must contain:
     *          - itemId (int) – ID of the item to equip
     *          - breedName (string) – Pet breed name (for asset linking)
     *          - addressableUrl (string) – URL/identifier for item display
     *          - subCategory (string) – Subcategory of item (e.g., clothes, hat)
     *
     * Response:
     *  - 200 OK
     *    {
     *      "message": "Items equipped successfully",
     *      "equipped": [
     *          {
     *              "item_id": 165,
     *              "slot_type": "clothes",
     *              "breed_name": "golden retriever",
     *              "addressable_url": "Golden Retriever_Clothes 1",
     *              "sub_category": "clothes"
     *          }
     *      ]
     *    }
     *  - 400 Bad Request (validation or item-related errors)
     *  - 401 Unauthorized (missing or invalid token)
     *  - 500 Internal Server Error (on DB failure)
     *
     * Todo:
     *  - Validate pet ownership by user
     *  - Validate item ownership and equipped availability
     *  - Ensure per-slot uniqueness (only one item per slot per pet)
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
        $items = $json->itemId ?? [];
        $petId = $json->petId ?? null;
        $breedName = strtolower($item->breedName ?? '');
        $addressableUrl = $item->addressableUrl ?? '';
        $subCategory = strtolower($item->subCategory ?? '');

        if (!$userId) {
            return $this->response->setJSON([
                'message' => 'Unauthorized',
            ])->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (!is_array($items) || count($items) === 0) {
            return $this->response->setJSON([
                'message' => 'Missing or invalid payload.'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (!isset($breedName, $addressableUrl, $subCategory)) {
            return $this->response->setJSON([
                'message' => 'Missing breed information or item visuals'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $isPetOwned = $petModel->isPetOwnedByUser($userId, $petId);
        if (!$isPetOwned) {
            return $this->response->setJSON([
                'message' => 'Pet is not owned'
            ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $db->transStart();
        $equippedResults = [];

        foreach ($items as $item) {
            $itemId = $item->itemId ?? null;
            $breedName = strtolower($item->breedName ?? null);
            $addressableUrl = $item->addressableUrl ?? null;
            $subCategory = strtolower($item->subCategory ?? null);

            $equipCheck = $inventoryModel->isItemEquippable($userId, $itemId);
            if (!$equipCheck['equippable']) {
                return $this->response->setJSON([
                    'message' => $equipCheck['reason']
                ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }

            $check = $petModel->isCorrectBreedAndSpecies($petId, $itemId);
            if (!$check['valid']) {
                return $this->response->setJSON([
                    'message' => $check['reason']
                ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }

            $existing = $itemEquippedModel->checkEquippedItem($userId, $petId, $subCategory);

            if ($existing && $existing['item_id'] != $itemId) {
                $inventoryModel->where('user_id', $userId)
                    ->where('item_id', $existing['item_id'])
                    ->set('equipped_count', 'equipped_count - 1', false)
                    ->update();

                $itemEquippedModel->update($existing['id'], [
                    'item_id' => $itemId,
                    'breed_name' => trim($breedName),
                    'addressable_url' => trim($addressableUrl),
                    'sub_category' => trim($subCategory)
                ]);
            } elseif (!$existing) {
                $itemEquippedModel->insert([
                    'user_id' => $userId,
                    'pet_id' => $petId,
                    'breed_name' => trim($breedName),
                    'item_id' => $itemId,
                    'addressable_url' => trim($addressableUrl),
                    'sub_category' => trim($subCategory)
                ]);
            }

            if (!$existing || $existing['item_id'] != $itemId) {
                $inventoryModel->where('user_id', $userId)
                    ->where('item_id', $itemId)
                    ->set('equipped_count', 'equipped_count + 1', false)
                    ->update();
            }

            $equippedResults[] = [
                'item_id' => $itemId,
                'breed_name' => $breedName,
                'addressable_url' => $addressableUrl,
                'sub_category' => $subCategory,
            ];
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setJSON([
                'message' => 'Failed to equip one or more items.',
                'db_error' => $db->error()['message'] ?? 'Unknown DB error',
                'equip_errors' => $itemEquippedModel->errors() ?: null,
                'inventory_errors' => $inventoryModel->errors() ?: null,
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON([
            'message' => 'Items equipped successfully',
            'equipped' => $equippedResults
        ])->setStatusCode(ResponseInterface::HTTP_OK);

    }

}
