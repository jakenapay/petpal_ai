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
use App\Libraries\InteractionService;


class ProcessPetInteraction extends BaseController
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila'); // Set the default timezone to Asia/Manila
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

        $data = $this->request->getJSON(true);

        //validate the data first
        
        
        
        //get the pet interaction history
        $petInteractionModel = new PetInteractionModel();
        $interactionHistory = $petInteractionModel->GetPetInteractionHistory($pet_id);
        // if (!$interactionHistory) {
        //     return $this->response->setJSON(['error' => 'No interaction history found for this pet'])
        //         ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        // }

        // Get what interaction is requested
        $interactionsModel = new InteractionTypeModel();
        $interaction = $interactionsModel->getInteractionById($data['interaction_id']);
        $allInteractions = $interactionsModel->findAll();
        if (!$interaction) {
            return $this->response->setJSON(['error' => 'Interaction not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }


        //get the item used in the interaction
        $itemsModel = new ItemModel();
        $item = $itemsModel->getItemById($data['item_used_id']);
        if (!$item) {
            return $this->response->setJSON(['error' => 'Item not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //get the pet 
        $petModel = new PetModel();
        $pet = $petModel->getPetById($pet_id);
        if (!$pet) {
            return $this->response->setJSON(['error' => 'Pet not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        // get the pet status
        $petStatusModel = new PetStatusModel();
        $petStatus = $petStatusModel->getPetStatusByPetId($pet_id);
        if (!$petStatus) {
            return $this->response->setJSON(['error' => 'Pet status not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

        //get the affinity table
        $affinityModel = new AffinityModel();
        $affinity = $affinityModel->getAfinityLevels();
        if (!$affinity) {
            return $this->response->setJSON(['error' => 'Affinity levels not found'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }
        
        //STEP 1: Check Subscription Requirements

        //STEP 2: Check if the interaction is allowed today
        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');

        $todayUsage = $petInteractionModel->getTodayInteractionCountsByPet($pet_id);
        $usedCount = 0;
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

        //STEP 3: GET AND UPDATE THE AFFINITY GAINED
        $affinityGained = $data['affinity_gained'] ?? 0;
        $newAffinity = $petStatus['affinity'] + $affinityGained;

        // Update the pet_status with the new affinity
        $result = $petStatusModel->updatePetAffinity($pet_id, [
            'affinity' => $newAffinity
        ]);

        if (!$result) {
            return $this->response->setJSON(['error' => 'Failed to update pet status'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        // STEP 4: Get the affinity level using the new affinity value
        $affinityLevel = $affinityModel->getAffinityLevelByPoints($newAffinity);

        //STEP 5: GET THE MULTIPLIERS from the affinity
        $multiplier = $affinityLevel['multiplier'];

        //STEP 6: CALCULATE THE PET STATUS EFFECTS WITH MULTIPLIERS
        $effects = $item['effects'];

        // Loop through each effect
        foreach ($effects as $effect) {
            // Decode the JSON string in 'effect_values'
            $effectValues = json_decode($effect['effect_values'], true);
        }

        $updateData = $effectValues;
        log_message('info', 'Update data: ' . json_encode($updateData));

        $updatePetStatusResult = $petStatusModel->updateStatusChange($pet_id, $updateData, $multiplier);
        if (!$updatePetStatusResult) {
            return $this->response->setJSON(['error' => 'Failed to update pet status'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        // STEP 7: MERON PA SI BOSS NA MARAMING MULTIPLIERS. BACKLOG NA LANG MUNA
        

        //extra: flatten the update data 
        $flatUpdateData = array_merge(...$updateData);

        //create data for the interaction log - FOR POST ONLY. NOT YET WORKING I JUST WANT TO SEE FIRST WHAT THE DATA LOOKS LIKE
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
            'llm_response' => $data['llm_response'] ?? null,
            'llm_response_type' => $data['llm_response_type'] ?? null,
            't2m_animation_id' => $data['t2m_animation_id'] ?? null,
            'emotion_detected' => $data['emotion_detected'] ?? null,
            'platform' => $data['platform'] ?? 'mobile',
            'session_id' => $data['session_id'] ?? null,
            'client_timestamp' => $data['client_timestamp'] ?? date('Y-m-d H:i:s'),
            'affinity_gained' => $data['affinity_gained'] ?? 0,
        ];

        $interactionLogModel = new PetInteractionModel();

        $inserted = $interactionLogModel->insert($log_data);

        if ($inserted === false) {
            log_message('error', 'DB error: ' . json_encode($interactionLogModel->errors()));
            return $this->response->setJSON(['error' => 'Failed to log interaction'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }
        // if (!$interactionLogModel->insert($log_data)) {
        //     return $this->response->setJSON(['error' => 'Failed to log interaction'])
        //         ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        // }


        //return is for testing only - so far.
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
                'effects_applied' => $flatUpdateData
            ]
        ])->setStatusCode(ResponseInterface::HTTP_OK);      
    }
}
