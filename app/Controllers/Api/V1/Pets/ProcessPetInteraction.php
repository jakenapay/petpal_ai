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
        if (!$interactionHistory) {
            return $this->response->setJSON(['error' => 'No interaction history found for this pet'])
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND);
        }

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
        
        //FIRST STEP: Check if the interaction is allowed today

        $todayStart = date('Y-m-d 00:00:00');
        $todayEnd = date('Y-m-d 23:59:59');

        // // Fetch today's usage per interaction type for this pet
        // $todayUsage = $petInteractionModel->getTodayInteractionCountsByPet($pet_id);

        // // Map usage by interaction_type_id for easy access
        // $usageMap = [];
        // foreach ($todayUsage as $usage) {
        //     $usageMap[$usage['interaction_type_id']] = (int)$usage['count'];
        // }

        // // Build the final interaction summary
        // $interactionSummary = [];
        // $canProceed = false;
        // foreach ($allInteractions as $interactionType) {
        //     $interactionId = $interactionType['interaction_type_id'];
        //     $usedCount = $usageMap[$interactionId] ?? 0;
        //     $maxCount = (int)$interactionType['max_daily_count'];
        //     $remaining = max(0, $maxCount - $usedCount);

        //     $interactionSummary[] = [
        //         'interaction_type_id' => $interactionId,
        //         'interaction_name' => $interactionType['interaction_name'],
        //         'used_today' => $usedCount,
        //         'remaining_today' => $remaining
        //     ];

        //     // Check if this is the requested interaction
        //     if ($interactionId == $data['interaction_id']) {
        //         if ($remaining > 0) {
        //             $canProceed = true;
        //         }
        //     }
        // }

        // // If not allowed, return error
        // if (!$canProceed) {
        //     return $this->response->setJSON([
        //         'error' => 'You have reached the maximum allowed for this interaction today.'
        //     ])->setStatusCode(ResponseInterface::HTTP_FORBIDDEN);
        // }
        // Get used count for this specific interaction
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

        // STEP 1.1: Add the affinity gained to the pet's current affinity
        $affinityGained = $data['affinity_gained'] ?? 0;
        $newAffinity = $petStatus['affinity'] + $affinityGained;

        // Update the pet_status with the new affinity
        $result = $petStatusModel->updatePetStatus($pet_id, [
            'affinity' => $newAffinity
        ]);

        log_message('info', 'Updated pet status with new affinity: ' . json_encode($result));


        if (!$result) {
            return $this->response->setJSON(['error' => 'Failed to update pet status'])
                ->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        // STEP 1.2: Get the affinity level using the new affinity value
        $affinityLevel = $affinityModel->getAffinityLevelByPoints($newAffinity);

        //SECOND STEP: GET THE MULTIPLIERS 
        //Third STEP: Process the interaction
        // Calculate the effects of the interaction

        $effects = $item['effects'];

        // Loop through each effect
        foreach ($effects as $effect) {
            // Decode the JSON string in 'effect_values'
            $effectValues = json_decode($effect['effect_values'], true);
        }

        //send the effects to the interaction service
        $interactionService = new InteractionService();
        $updateData = $effectValues;
        // $result = $interactionService->update_pet_status($pet_id, $updateData, $multipliers);


        //create data for the interaction log - FOR POST ONLY. NOT YET WORKING I JUST WANT TO SEE FIRST WHAT THE DATA LOOKS LIKE
        $log_data = [
            'log_id' => bin2hex(random_bytes(16)), //amats ni sir
            // 'log_id' => null // auto-incremented by the database
            'pet_id' => $pet_id,
            'user_id' => $userId,
            'interaction_type_id' => $data['interaction_id'],
            'item_used_id' => $data['item_used_id'],
            'affinity_gained' => $data['affinity_gained'] ?? 0, // default to 0 if not provided
        ];


        //return is for testing only - so far.
        return 
            $this->response->setJSON([
                'status' => 'success',
                'message' => 'Interaction logged successfully',
                'new_affinity' => $newAffinity,
                'remaining_today' => $remaining,
                'effects' => $effects,
                'effectValues' => $effectValues,
                'affinityLevel' => $affinityLevel,
                // 'history' => $interactionHistory,
                // 'interaction' => $interaction, 
                // 'item' => $item,
                // 'pet' => $pet,
                'pet_status' => $petStatus,
                // 'affinity' => $affinity,

            ])->setStatusCode(ResponseInterface::HTTP_OK);

        
    }
}
