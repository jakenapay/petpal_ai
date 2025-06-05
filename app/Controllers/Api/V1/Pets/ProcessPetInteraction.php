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

    }


    public function getInteraction($interaction_id){
        $interaction = $this->interactionsModel->getInteractionById($interaction_id);
        if (!$interaction) {
            return $this->response->setJSON(['error' => 'Interaction not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $interaction;
    }

    public function getItemUsed($item_used_id){
        $item = $this->itemsModel->getItemById($item_used_id);
        if (!$item) {
            return $this->response->setJSON(['error' => 'Item not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        return $item;
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


    public function index($pet_id)
    {
        $userId = authorizationCheck($this->request);
        // if (!$userId) {
        //     return $this->response->setJSON(['error' => 'Unauthorized'])
        //         ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        // }
        $userId = 43;
        //get the pet id from the url
        $pet_id = (int) $pet_id;
        if (!$pet_id) {
            return $this->response->setJSON(['error' => 'Pet ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        
        //get the payload
        $data = $this->request->getJSON(true);
        //handle the values of data
        $itemUsedId = $data['item_used_id'] ?? null;

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

        //-------------------------------------------------------------------------
        //FILTER IF THE INTERACTIONS HAVE ITEMS OR NOT
        //-------------------------------------------------------------------------
        if ($itemUsedId === null) {
            $affinityGained = $interaction['affinity'];
            $interactionExperience = $interaction['experience'] ?? 0;

            $item_name = null;
            $updateData = [
                'hunger_level' => $interaction['hunger_level'],
                'happiness_level' => $interaction['happiness_level'],
                'health_level' => $interaction['health_level'],
                'energy_level' => $interaction['energy_level'],
                'cleanliness_level' => $interaction['hygiene_level'],
                'stress_level' => $interaction['stress_level'],
            ];
        }else{
            $affinityGained = $item['affinity'];
            $item_name = $item['item_name'];
            $interactionExperience = $item['experience'] ?? 0;
            $currentExperience = $pet['experience'] ?? 0;

            $updateData = [
                'hunger_level' => $item['hunger_level'],
                'happiness_level' => $item['happiness_level'],
                'health_level' => $item['health_level'],
                'energy_level' => $item['energy_level'],
                'cleanliness_level' => $item['hygiene_level'],
                'stress_level' => $item['stress_level'],
            ];
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
            $maxLifeStageReached = true; // No next level available
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
            $result = $this->petModel->updatePet($pet_id, [
                'experience' => $newExperience,
                'level' => $newLevel,
                'life_stage_id' => $newLifeStage
            ]);
            if (!$result) {
                $db->transRollback();
                return $this->response->setJSON(['error' => 'Failed to update pet level'])
                    ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
            }
        }else if ($currentExperience < $requiredExperience && !$maxLifeStageReached) {
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
            'item_used_id' => $itemUsedId,
            'item_used_name' => $item_name ?? null,
            'interaction_duration_seconds' => $data['interaction_duration_seconds'] ?? 0,
            'interaction_quality' => $data['quality'] ?? null,
            'base_points' => $data['base_points'] ?? 0,
            'multiplier_total' => $multiplier * $subs_multiplier * $petLifeStageMultiplier,
            'affinity_gained' => $newAffinity,
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
            'required_experience_for_next_level' => $this->getPetLifeStage($newLifeStage + 1)['experience_required'] ?? null,
            'pet_abilities' => json_decode($pet['abilities'], true) ?? [],
            'pet_age' => (new DateTime($pet['birthdate']))->diff(new DateTime())->format('%y years, %m months, %d days'),
            'personality' => $pet['personality'] ?? null,
            'social_stats'=> "not implemented yet",
        ];
        return $this->response->setJSON([
            'message' => 'Pet interaction processed successfully',
            'new_pet_status' => array_merge($interactionSummary, $newPetStatus),
        ])->setStatusCode(ResponseInterface::HTTP_OK);
    }

}
