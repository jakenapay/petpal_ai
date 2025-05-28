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
use App\Libraries\InteractionService;



class ProcessPetInteraction extends BaseController
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila'); // Set the default timezone to Asia/Manila
        $this->petInteractionModel = new PetInteractionModel();
        $this->interactionsModel = new InteractionTypeModel();
        $this->interactionService = new InteractionService();
        $this->petModel = new PetModel();
        $this->petStatusModel = new PetStatusModel();
        $this->affinityModel = new AffinityModel();
        $this->subscriptionModel = new SubscriptionModel();
        $this->petLifeStageModel = new PetLifeStageModel();
        $this->itemsModel = new ItemModel();

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


    public function index($pet_id)
    {
        $userId = authorizationCheck($this->request);
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])
                ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED);
        }

        //get the pet id from the url
        $pet_id = (int) $pet_id;
        if (!$pet_id) {
            return $this->response->setJSON(['error' => 'Pet ID is required'])
                ->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        }
        //get the payload
        $data = $this->request->getJSON(true);
        //get the interaction type
        $interaction = $this->getInteraction($data['interaction_id']);
        //get the item used in the interaction
        $item = $this->getItemUsed($data['item_used_id']);
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
        log_message('debug', print_r($todayUsage, true));
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
        // UPDATE THE PET STATUS AFFINITY BASED ON AFFINITY GAINED
        //-------------------------------------------------------------------------
        $affinityGained = $data['affinity_gained'] ?? 0;
        $newAffinity = $petStatus['affinity'] + $affinityGained;

        // Update the pet_status with the new affinity
        $result = $this->petStatusModel->updatePetAffinity($pet_id, [
            'affinity' => $newAffinity
        ]);

        if (!$result) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to update pet status'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
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
        // GET ALL THE EFFECTS OF THE ITEM
        //-------------------------------------------------------------------------
        $effects = $item['effects'];

        if (!$effects) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Effects not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        // Loop through each effect
        foreach ($effects as $effect) {
            $effectValues = json_decode($effect['effect_values'], true);
        }

        $updateData = $effectValues;
        //-------------------------------------------------------------------------
        // UPDATE THE PET STATUS BASED ON THE MULTIPLIERS AND EFFECTS
        //-------------------------------------------------------------------------
        $updatePetStatusResult = $this->petStatusModel->updateStatusChange($pet_id, $updateData, $multiplier, $subs_multiplier, $petLifeStageMultiplier);
        if (!$updatePetStatusResult) {
            $db->transRollback();
            return $this->response->setJSON(['error' => 'Failed to update pet status'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        //-------------------------------------------------------------------------
        // FLATTEN THE DATA
        //-------------------------------------------------------------------------
        $flatUpdateData = array_merge(...$updateData);

        //backlogs: 1. Personality Multipliers
        // 2. Quality Multiplier

        //-------------------------------------------------------------------------
        // BUILD THE DETAILS FOR INSERTION
        //-------------------------------------------------------------------------
        $log_data = [
            'log_id' => bin2hex(random_bytes(16)),
            'pet_id' => $pet_id,
            'user_id' => $userId,
            'interaction_type_id' => $data['interaction_id'],
            'interaction_category' => $interaction['category'],
            'interaction_subcategory' => $interaction['subcategory'] ?? null,
            'item_used_id' => $data['item_used_id'],
            'item_used_name' => $item['item_name'],
            'interaction_duration_seconds' => $data['interaction_duration_seconds'] ?? 0,
            'interaction_quality' => $data['quality'] ?? null,
            'base_points' => $data['base_points'] ?? 0,
            'multiplier_total' => $multiplier,
            'affinity_gained' => $newAffinity,
            'coins_earned' => $data['coins_earned'] ?? 0,
            'hunger_change' => $flatUpdateData['hunger_level'] ?? 0,
            'happiness_change' => $flatUpdateData['happiness_level'] ?? 0,
            'health_change' => $flatUpdateData['health_level'] ?? 0,
            'cleanliness_change' => $flatUpdateData['cleanliness_level'] ?? 0,
            'energy_change' => $flatUpdateData['energy_level'] ?? 0,
            'stress_change' => $flatUpdateData['stress_level'] ?? 0,
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

        return $this->response->setJSON([
            'message' => 'Pet interaction processed successfully',
            'interaction_summary' => [
                'interaction_type_id' => $data['interaction_id'],
                'interaction_name' => $interaction['interaction_name'],
                'interaction_category' => $interaction['category'],
                'item_used_id' => $data['item_used_id'],
                'item_used_name' => $item['item_name'],
                'affinity_gained' => $affinityGained,
                'new_affinity' => $newAffinity,
                'affinity_level' => $affinityLevel['level_name'],
                'multiplier' => $multiplier,
                'effects_applied' => $flatUpdateData,
                'pet_life_stage' => $petLifeStage
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);      
    }
}
