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

        if (!$userId) {
            return $this->response->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (!is_array($items) || count($items) === 0) {
            return $this->response->setJSON(['message' => 'Missing or invalid itemId array.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (!$petId || !is_numeric($petId)) {
            return $this->response->setJSON(['message' => 'Missing or invalid petId.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        if (!$petModel->isPetOwnedByUser($userId, $petId)) {
            return $this->response->setJSON(['message' => 'Pet is not owned by the user.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $db->transStart();
        $equippedResults = [];

        foreach ($items as $item) {
            $breedName = strtolower($item->breedName ?? '');
            $addressableUrl = $item->addressableUrl ?? '';
            $subCategory = strtolower($item->subCategory ?? '');

            if (!$breedName || !$subCategory) {
                return $this->response->setJSON([
                    'message' => 'Missing breedName or subCategory in one of the items.'
                ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }

            // Get item accessory by addressable URL (can be null = unequip)
            $data = $addressableUrl ? $itemAccessoriesModel->getItemByUrl($addressableUrl) : null;
            $itemId = $data['item_id'] ?? null;

            $existing = $itemEquippedModel->checkEquippedItem($userId, $petId, $subCategory);

            // Unequip if no itemId resolved
            if (!$itemId) {
                if ($existing) {
                    $inventoryModel->where('user_id', $userId)
                        ->where('item_id', $existing['item_id'])
                        ->set('equipped_count', 'equipped_count - 1', false)
                        ->update();

                    $itemEquippedModel->delete($existing['id']);
                }

                $equippedResults[] = [
                    'item_id' => null,
                    'addressable_url' => $addressableUrl,
                    'sub_category' => $subCategory,
                    'breed_name' => $breedName,
                ];
                continue;
            }

            // Equip validation
            $equipCheck = $inventoryModel->isItemEquippable($userId, $itemId);
            if (!$equipCheck['equippable']) {
                return $this->response->setJSON([
                    'message' => $equipCheck['reason'],
                    'item_id' => $itemId
                ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }

            // Check pet species & breed match
            $check = $petModel->isCorrectBreedAndSpecies($petId, $itemId);
            if (!$check['valid']) {
                return $this->response->setJSON([
                    'message' => $check['reason'],
                    'item_id' => $itemId
                ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }

            // Update existing or insert new equip
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

            // Update equipped count for the new item
            if (!$existing || $existing['item_id'] != $itemId) {
                $inventoryModel->where('user_id', $userId)
                    ->where('item_id', $itemId)
                    ->set('equipped_count', 'equipped_count + 1', false)
                    ->update();
            }

            $equippedResults[] = [
                'item_id' => $itemId,
                'addressable_url' => $addressableUrl,
                'sub_category' => $subCategory,
                'breed_name' => $breedName,
            ];
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setJSON([
                'message' => 'Failed to equip or unequip item(s).',
                'db_error' => $db->error()['message'] ?? 'Unknown DB error',
                'equip_errors' => $itemEquippedModel->errors() ?: null,
                'inventory_errors' => $inventoryModel->errors() ?: null,
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->response->setJSON([
            'message' => 'Items processed successfully',
            'equipped' => $equippedResults
        ])->setStatusCode(ResponseInterface::HTTP_OK);

    }

    public function getItemEquipped($petId)
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['message' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        if (!$petId || !is_numeric($petId)) {
            return $this->response->setJSON(['message' => 'Missing or invalid petId.'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        $petModel = new PetModel();
        if (!$petModel->isPetOwnedByUser($userId, $petId)) {
            return $this->response->setJSON(['message' => 'Pet with PetID: ' . $petId . ' is not owned by user ' . $userId])
                ->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
        }

        $itemEquippedModel = new ItemEquippedModel();
        $equippedItems = $itemEquippedModel
            ->where('user_id', $userId)
            ->where('pet_id', $petId)
            ->findAll();

        return $this->response->setJSON([
            'message' => 'Equipped items retrieved successfully.',
            'equipped_items' => $equippedItems
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

}
