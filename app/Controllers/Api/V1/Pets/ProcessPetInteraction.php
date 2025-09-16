<?php
namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\PetInteractionModel;
use App\Models\InteractionTypeModel;
use App\Models\ItemModel;
use App\Models\PetModel;
use App\Models\PetStatusModel;
use App\Models\AffinityModel;
use App\Models\SubscriptionModel;
use App\Models\PetLifeStageModel;
use App\Models\InteractionCategoriesModel;
use App\Models\InventoryModel;
use App\Models\DefaultItemsModel;


use DateTime;


class ProcessPetInteraction extends BaseController
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila'); // Set the default timezone to Asia/Manila
        $this->petInteractionModel = new PetInteractionModel();
        $this->interactionsModel = new InteractionTypeModel();
        $this->petModel = new PetModel();
        $this->petStatusModel = new PetStatusModel();
        $this->affinityModel = new AffinityModel();
        $this->subscriptionModel = new SubscriptionModel();
        $this->petLifeStageModel = new PetLifeStageModel();
        $this->itemsModel = new ItemModel();
        $this->interactionCategoriesModel = new InteractionCategoriesModel();
        $this->inventoryModel = new InventoryModel();
        $this->defaultItemsModel = new DefaultItemsModel();
    }




    public function getInteraction($interaction_id){
        $interaction = $this->interactionsModel->getInteractionById($interaction_id);
        if (!$interaction) {
            return $this->response->setJSON(['error' => 'Interaction not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $interaction;
    }

    public function getItemUsed($item_used_id)
    {
        $items = $this->itemsModel->getItems($item_used_id);

        if (empty($items)) {
            return $this->response->setJSON(['error' => 'Items not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        return $this->response->setJSON([
            'success' => true,
            'items' => $items,
        ]);
    }
    public function getPet($pet_id){
        $pet = $this->petModel->getPetById($pet_id);
        if (!$pet) {
            return $this->response->setJSON(['error' => 'Pet not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $pet;
    }

    public function getPetStatus($pet_id){
        $petStatus = $this->petStatusModel->getPetStatusByPetId($pet_id);
        if (!$petStatus) {
            return $this->response->setJSON(['error' => 'Pet status not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $petStatus;
    }
    public function getPetAffinity(){
        $affinity = $this->affinityModel->getAfinityLevels();
        if (!$affinity) {
            return $this->response->setJSON(['error' => 'Affinity levels not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $affinity;
    }
    public function getSubscription($user_id){
        $subscription = $this->subscriptionModel->getUserSubscription($user_id);
        if (!$subscription) {
            return $this->response->setJSON(['error' => 'User subscription not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $subscription;
    }
    public function getPetLifeStage($life_stage_id){
        $petLifeStage = $this->petLifeStageModel->getLifeStageById($life_stage_id);
        if (!$petLifeStage) {
            return $this->response->setJSON(['error' => 'Pet life stage not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        if (is_string($petLifeStage)) {
            $petLifeStage = json_decode($petLifeStage, true);
        }
        return $petLifeStage;
    }
    public function getInteractionCategory($category_id, $interaction_id)
    {
        $interactionCategories = $this->interactionCategoriesModel->getInteractionCategoryById($category_id, $interaction_id);
        if (!$interactionCategories) {
            return $this->response->setJSON(['error' => 'Category not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $interactionCategories;
    }

    public function checkItemFromInventory($userId, $itemUsedId)
    {
        $item_ids = $itemUsedId;

        if (empty($item_ids)) {
            return [
                'success' => false,
                'error' => 'No item IDs provided',
                'code' => 400
            ];
        }

        $not_found = [];
        $found_items = [];

        foreach ($item_ids as $item_id) {
            $item = $this->inventoryModel->checkItemInInventory($userId, $item_id);
            if (!$item) {
                $not_found[] = $item_id;
            } else {
                $found_items[] = $item;
            }
        }

        if (!empty($not_found)) {
            return [
                'success' => false,
                'error' => 'Some items not found in inventory',
                'not_found' => $not_found,
                'code' => 404
            ];
        }

        return [
            'success' => true,
            'items' => $found_items,
            'code' => 200
        ];
    }

    public function deduceItemQuantity($userId, $itemUsedId, $quantity) 
    {
        //since itemusedid is array, loop each
        $result = [];
        foreach ($itemUsedId as $item_id) {
            //check the item's quantity first.
            $item = $this->inventoryModel->checkItemInInventory($userId, $item_id);
            if (!$item) {
                return [
                    'success' => false,
                    'error' => 'Item not found in inventory',
                    'code' => 404
                ];
            }
            //if the item quantity is 1, delete it from inventory
            //else, just reduce the quantity
            if ($item['quantity'] == 1) {
                // UNCOMMENT THIS PART IF YOU WANT TO DELETE THE ITEM. RIGHT NOW, ITs just for testing.
                // $this->inventoryModel->deleteItemFromInventory($userId, $item_id);
                $result[] = [
                    'item_id' => $item_id,
                    'old_quantity' => $item['quantity'],
                    'new_quantity' => 0
                ];
                continue;
            }else{
                $new_quantity = $item['quantity'] - $quantity;
                $this->inventoryModel->reduceItemQuantity($userId, $item_id, $quantity);
                $result[] = [
                    'item_id' => $item_id,
                    'old_quantity' => $item['quantity'],
                    'new_quantity' => $new_quantity
                ];
            }
        }
        return [
            'success' => true,
            'message' => 'Item quantity has been reduced',
            'code' => 200,
            'result' => $result
        ];
    }

    public function handleDefaultLifestageItems($newLifeStage){
        //get the default items for the new life stage
        $defaultItems = $this->defaultItemsModel->getDefaultItems($newLifeStage);
        if (empty($defaultItems)) {
            return [];
        }
        return $defaultItems;

    }

    public function addDefaultItemsToInventory($user_id, $defaultItems){
        foreach ($defaultItems as $item) {
            //check if the item already exists in the inventory
            $existingItem = $this->inventoryModel->getInventoryItem($user_id, $item['item_id']);
            if ($existingItem) {
                //if exists, just update the quantity
                $new_quantity = $existingItem['quantity'] + 1;
                $this->inventoryModel->updateItemQuantity($user_id, $item['item_id'], $new_quantity);
            }else{
                //if not exists, insert the item
                $inventoryData = [
                    'user_id' => $user_id,
                    'item_id' => $item['item_id'],
                    'quantity' => 1,
                    'acquired_from' => 'Life Stage Upgrade',
                    'acquired_at' => date('Y-m-d H:i:s'),
                ];
                $this->inventoryModel->addItemToInventory($inventoryData);
            }
        }
        return true;
        
    }

    public function index($pet_id)
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }
        // $userId = 43;
        //get the pet id from the url
        $pet_id = (int) $pet_id;
        if (!$pet_id) {
            return $this->response->setJSON(['error' => 'Pet ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        //get the payload
        $data = $this->request->getJSON(true);
        //handle the values of data
        // $itemUsedId = $data['item_used_id'] ?? null;
        $itemUsedId = $data['item_used_id'] ?? [];
        $itemUsedId = is_array($itemUsedId) ? $itemUsedId : [$itemUsedId];


        //get the interaction type
        $interaction = $this->getInteraction($data['interaction_id']);
        //get the interaction category
        $interactionCategory = $this->getInteractionCategory($interaction['category_id'], $data['interaction_id']);
        //get the item used in the interaction
        $item = $this->getItemUsed($itemUsedId);
        //get the pet 
        $pet = $this->getPet($pet_id);
        //get the pet status
        $petStatus = $this->getPetStatus($pet_id);
        //get the affinity
        $affinity = $this->getPetAffinity();
        //get the user current subscription
        $subscription = $this->getSubscription($userId);
        //get the pet life stage
        $petLifeStage = $this->getPetLifeStage($pet['life_stage_id']);
        //set the date and time
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');
        //start db transaction
        $db = \Config\Database::connect();
        $db->transStart();

        //-------------------------------------------------------------------------
        // CHECK IF THE INTERACTION NEEDS AN ITEM OR NOT
        //-------------------------------------------------------------------------
        if ($interactionCategory[0]['item_dependent'] === "1" && !$itemUsedId) {
            return $this->response->setJSON(['error' => 'Item used is required for ' . strtoupper($interactionCategory[0]['category_name']) . ' interaction'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }

        //-------------------------------------------------------------------------
        //CHECK IF THE USER HAS REACHED THE MAXIMUM DAILY COUNT FOR THE INTERACTION
        //-------------------------------------------------------------------------
        $todayUsage = $this->petInteractionModel->getTodayInteractionCountsByPet($pet_id);
        log_message('info', print_r($todayUsage, true));
        $usedCount = 0;

        if ($todayUsage === false) {
            $db->transRollback();
            return $this->response->setJSON([
                'error' => 'Failed to get pet interaction counts'
            ])->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }else{
            foreach ($todayUsage as $usage) {
                if ($usage['interaction_type_id'] == $data['interaction_id']) {
                    $usedCount = (int)$usage['count'];
                    break;
                }
            }
            $maxCount = (int)$interaction['max_daily_count'];
            $remaining = max(0, $maxCount - $usedCount);
            if ($remaining <= 0) {
                return $this->response->setJSON([
                    'error' => 'You have reached the maximum allowed for this interaction today.'
                ])->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
            }
        }


        //BACKLOG!!!! AFFINITY NOT SAVING TO THE INTERACTION LOG
        //-------------------------------------------------------------------------
        // FILTER IF THE INTERACTIONS HAVE ITEMS OR NOT
        //-------------------------------------------------------------------------
        if (!is_array($item) || empty($item)) {
            // No item used — use interaction's default effects
            $affinityGained = $interaction['affinity'];
            $interactionExperience = $interaction['experience'] ?? 0;
            $item_name = null;

            $updateData = [
                'hunger_level'       => $interaction['hunger_level'],
                'happiness_level'    => $interaction['happiness_level'],
                'health_level'       => $interaction['health_level'],
                'energy_level'       => $interaction['energy_level'],
                'cleanliness_level'  => $interaction['hygiene_level'],
                'stress_level'       => $interaction['stress_level'],
            ];
        } else {
            // At least one item is used — combine their effects
            $affinityGained = 0;
            $interactionExperience = 0;
            $item_name = []; // store multiple names
            $updateData = [
                'hunger_level'       => 0,
                'happiness_level'    => 0,
                'health_level'       => 0,
                'energy_level'       => 0,
                'cleanliness_level'  => 0,
                'stress_level'       => 0,
            ];

            foreach ($item as $item) {
                $affinityGained += $item['affinity'] ?? 0;
                $interactionExperience += $item['experience'] ?? 0;
                $item_name[] = $item['item_name'] ?? '';

                $updateData['hunger_level']      += $item['hunger_level'] ?? 0;
                $updateData['happiness_level']   += $item['happiness_level'] ?? 0;
                $updateData['health_level']      += $item['health_level'] ?? 0;
                $updateData['energy_level']      += $item['energy_level'] ?? 0;
                $updateData['cleanliness_level'] += $item['hygiene_level'] ?? 0;
                $updateData['stress_level']      += $item['stress_level'] ?? 0;
            }

            // Optionally: convert item_name array to string
            $item_name = implode(', ', array_filter($item_name));
        }



        //-------------------------------------------------------------------------
        // UPDATE THE PET STATUS AFFINITY BASED ON AFFINITY GAINED
        //-------------------------------------------------------------------------
        $newAffinity = $petStatus['affinity'] + $affinityGained;

        // Update the pet_status with the new affinity
        $result = $this->petStatusModel->updatePetAffinity($pet_id, [
            'affinity' => $newAffinity
        ]);

        if (!$result) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to update pet affinity'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        // BACKLOG
        // -------------------------------------------------------------------------
        // VERIFY IF THE USER HAS THE ITEM IN THEIR INVENTORY
        // IF YES, DEDUCE THE QUANTITY OF THE ITEM USED
        // -------------------------------------------------------------------------
        $ItemResult = [];
        
        // Only run deduction logic if item_used_id is a non-empty array with non-empty values
        if (is_array($itemUsedId) && !empty(array_filter($itemUsedId))) {
            $checkItems = $this->checkItemFromInventory($userId, $itemUsedId);
            if ($checkItems['success'] != true) {
                $db->transRollback();
                return $this->response->setJSON(['warning' => $checkItems])
                    ->setStatusCode($checkItems['code']);
            }

            $updateItemQuantity = $this->deduceItemQuantity($userId, $itemUsedId, 1);
            if ($updateItemQuantity['success'] != true) {
                $db->transRollback();
                return $this->response->setJSON(['warning' => $updateItemQuantity])
                    ->setStatusCode($updateItemQuantity['code']);
            }

            $ItemResult = $updateItemQuantity['result'];
        }
        
        // -------------------------------------------------------------------------
        // UPDATE THE PET EXPERIENCE BASED ON THE INTERACTION
        //-------------------------------------------------------------------------
        //get the current experience of the pet
        $currentExperience = $pet['experience'] ?? 0;

        //get the level of the pet
        $petLevel = $pet['level'] ?? null;
        if (!$petLevel) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Pet level not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //required experience for the next level
        $requiredExperience = null;
        $maxLifeStageReached = false;
        $nextLifeStage = $this->getPetLifeStage($pet['life_stage_id'] + 1);
        if ($nextLifeStage) {
            $requiredExperience = $nextLifeStage['experience_required'] ?? null;
        } else {
            $requiredExperience = null;
            $maxLifeStageReached = true;
        }
        $newExperience = $currentExperience + $interactionExperience;
        $newlevel = $petLevel;
        $newLifeStage = $pet['life_stage_id'] ?? null;
        $newLifeStageName = $petLifeStage['stage_name'] ?? null;
        
        //if the current experience is > the required experience, then level up the pet
        if ($newExperience >= $requiredExperience && !$maxLifeStageReached) {
            $newLevel = $petLevel + 1;
            $newLifeStage = $petLifeStage['stage_id'] + 1;
            $requiredExperience = $this->getPetLifeStage($newLifeStage)['experience_required'] ?? null;


            // Get pet species and breed
            $petSpecies = $pet['species'] ?? null;
            $petBreed = $pet['breed'] ?? null;
            $db = \Config\Database::connect();

            // Determine breeds table
            if (strtolower($petSpecies) === "dog") {
                $builder = $db->table('dogbreeds');
                $textureTableName = 'dog_texture';
            } else if (strtolower($petSpecies) === "cat") {
                $builder = $db->table('catbreeds');
                $textureTableName = 'cat_texture';
            } else {
                // handle unexpected species
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Unsupported pet species'])
                    ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
            }

            // Get breed ID
            $builder->where('LOWER(breed_name)', strtolower($petBreed));
            $query = $builder->get();
            $result = $query->getResult();
            $breed = !empty($result) ? $result[0] : null;
            $petBreedId = $breed->breed_id ?? null;

            // Get appearance
            $petAppearance = json_decode($pet['appearance'], true);

            // STEP 1: Get current texture row (by skin_id)
            $currentTextureId = $petAppearance['skin_id'] ?? null;
            $currentTextureRow = null;
            if ($currentTextureId) {
                $textureTable = $db->table($textureTableName);
                $textureTable->where('id', $currentTextureId);
                $currentTextureRow = $textureTable->get()->getRow();
            }

            // STEP 2: Extract color/pattern and breedNamePart from texture_name (flexible)
            $currentTextureName = $currentTextureRow->texture_name ?? null;
            $colorPattern = '';
            $breedNamePart = '';
            if ($currentTextureName) {
                $parts = explode('_', $currentTextureName);
                if (count($parts) >= 3) {
                    $breedNamePart = $parts[count($parts) - 1];
                    $colorPattern = implode('_', array_slice($parts, 1, count($parts) - 2));
                    // This will be 'BLACK', 'LIGHT_BROWN', etc.
                }
                else {
                    // fallback for unexpected formats
                    $colorPattern = '';
                    $breedNamePart = '';
                }
            }

            // STEP 3: Get the life stage name
            $lifeStageTable = $db->table('pet_life_stages');
            $lifeStageTable->where('stage_id', $newLifeStage);
            $lifeStageQuery = $lifeStageTable->get();
            $lifeStage = $lifeStageQuery->getRow();
            $stageName = $lifeStage->stage_name ?? null;

            // $stageName = $this->getLifeStageName($newLifeStage); // E.g. returns 'Teen'
            $expectedTextureName = "{$stageName}_{$colorPattern}_{$breedNamePart}";

            // STEP 4: Find the matching texture in new life stage with same color
            $textureTable = $db->table($textureTableName);
            $textureTable->where('breed_id', $petBreedId);
            $textureTable->where('life_stage_id', $newLifeStage);
            $textureTable->where('texture_name', $expectedTextureName);
            $newTextureRow = $textureTable->get()->getRow();

            // STEP 5: Update appearance
            if ($newTextureRow) {
                $petAppearance['skin_id'] = $newTextureRow->id;
            } else {
                // fallback: if not found, keep current skin_id or handle error
                // Optionally log this event
            }
            $newPetAppearance = json_encode($petAppearance);

            // additional: give the pet's default items for the new life stage
            $defaultItems = $this->handleDefaultLifestageItems($newLifeStage);

            if (!empty($defaultItems)) {
                $this->addDefaultItemsToInventory($userId, $defaultItems);
            }else{
                //rollback and return error
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to get default items for the new life stage'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }

            // STEP 6: Update the pet
            $result = $this->petModel->updatePet($pet_id, [
                'experience' => $newExperience,
                'level' => $newLevel,
                'life_stage_id' => $newLifeStage,
                'appearance' => $newPetAppearance
            ]);
            if (!$result) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to update pet level'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        
        else if ($currentExperience < $requiredExperience && !$maxLifeStageReached) {
            //if the current experience is less than the required experience, then just update the experience
            $result = $this->petModel->updatePet($pet_id, [
                'experience' => $newExperience,
            ]);
            if (!$result) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to update pet experience'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        
        else{
            //max level reached, do not update the level or experience
            $newLevel = $petLevel; // Keep the current level
            $newLifeStage = $pet['life_stage_id']; // Keep the current life stage
            $requiredExperience = null; // No next level available
        }

        //-------------------------------------------------------------------------
        // GET ALL THE MULTIPLIERS
        //-------------------------------------------------------------------------
        $affinityLevel = $this->affinityModel->getAffinityLevelByPoints($newAffinity);

        if (!$affinityLevel) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Affinity level not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $multiplier = $affinityLevel['multiplier'];

        if (!$multiplier) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Multiplier not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $subs_multiplier = $subscription['multiplier'];

        if (!$subs_multiplier) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Subscription multiplier not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        $petLifeStageMultiplier = $petLifeStage['multiplier']; 

        if (!$petLifeStageMultiplier) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Pet life stage multiplier not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //-------------------------------------------------------------------------
        // UPDATE THE PET STATUS BASED ON THE MULTIPLIERS AND EFFECTS
        //-------------------------------------------------------------------------
        $updatePetStatusResult = $this->petStatusModel->updateStatusChange($pet_id, $updateData, $multiplier, $subs_multiplier, $petLifeStageMultiplier);
        if (!$updatePetStatusResult) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to update pet status'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }



        //backlogs: 
        // 2. Personality Multipliers
        // 3. Quality Multiplier

        //-------------------------------------------------------------------------
        // BUILD THE DETAILS FOR INSERTION
        //-------------------------------------------------------------------------
        $log_data = [
            'log_id' => bin2hex(random_bytes(16)),
            'pet_id' => $pet_id,
            'user_id' => $userId,
            'interaction_type_id' => $data['interaction_id'],
            'interaction_category' => $interactionCategory[0]['category_name'] ?? null,
            'interaction_subcategory' => $interaction['subcategory'] ?? null,
            'item_used_id' => !empty($itemUsedId) ? json_encode($itemUsedId) : null,
            'item_used_name' => $item_name ?? null,
            'interaction_duration_seconds' => $data['interaction_duration_seconds'] ?? 0,
            'interaction_quality' => $data['quality'] ?? null,
            'base_points' => $data['base_points'] ?? 0,
            'multiplier_total' => $multiplier * $subs_multiplier * $petLifeStageMultiplier,
            'affinity_gained' => $affinityGained,
            'experience_gained' => $interactionExperience,
            'coins_earned' => $data['coins_earned'] ?? 0,
            'hunger_change' => $updateData['hunger_level'] ?? 0,
            'happiness_change' => $updateData['happiness_level'] ?? 0,
            'health_change' => $updateData['health_level'] ?? 0,
            'cleanliness_change' => $updateData['cleanliness_level'] ?? 0,
            'energy_change' => $updateData['energy_level'] ?? 0,
            'stress_change' => $updateData['stress_level'] ?? 0,
            'llm_response' => $data['llm_response'] ?? "no response",
            'llm_response_type' => $data['llm_response_type'] ?? null,
            't2m_animation_id' => $data['t2m_animation_id'] ?? null,
            'emotion_detected' => $data['emotion_detected'] ?? null,
            'platform' => $data['platform'] ?? 'mobile',
            'session_id' => $data['session_id'] ?? null,
            'client_timestamp' => $data['client_timestamp'] ?? date('Y-m-d H:i:s'),
            'affinity_gained' => $data['affinity_gained'] ?? 0,
        ];

        $inserted = $this->petInteractionModel->insert($log_data);

        if ($inserted === false) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to log interaction'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        //complete and commit the transaction
        $db->transCommit();

        $newPetStatus = $this->petStatusModel->getPetStatusByPetId($pet_id);
        if (!$newPetStatus) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to build the return for an updated pet status'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        $interactionSummary = [
            'remaining_interactions_today' => max(0, $maxCount - $usedCount - 1),
            'affinity_gained' => $affinityGained,
            'pet_level' => $newLevel ?? $pet['level'],
            'pet_life_stage' => $this->getPetLifeStage($newLifeStage)['stage_name'] ?? null,
            'experience_gained' => $interactionExperience,
            'current_experience' => $currentExperience,
            'new_experience' => $pet['experience'] + $interactionExperience,
            'required_experience_for_next_level' => $this->getPetLifeStage($newLifeStage + 1)['experience_required'] ?? $this->getPetLifeStage($newLifeStage)['experience_required'],
            'pet_abilities' => json_decode($pet['abilities'], true) ?? [],
            'pet_age' => (new DateTime($pet['birthdate']))->diff(new DateTime())->format('%y years, %m months, %d days'),
            'personality' => $pet['personality'] ?? null,
            'social_stats'=> "not implemented yet",
        ];
        return $this->response->setJSON([
            'message' => 'Pet interaction processed successfully',
            'item_status' => $ItemResult,
            'new_pet_status' => array_merge($interactionSummary, $newPetStatus),
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

}
