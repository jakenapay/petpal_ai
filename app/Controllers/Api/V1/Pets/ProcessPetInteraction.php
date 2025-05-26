<?php

namespace App\Controllers\Api\V1\Pets;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\LogInteractionModel;
use App\Models\InteractionTypeModel;
use App\Models\ItemModel;
use App\Models\PetModel;
use App\Models\PetStatusModel;
use App\Models\AffinityModel;

class ProcessPetInteraction extends BaseController
{
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
        // $validationRules = [
        //     'pet_id' => 'required|integer',
        //     'interaction_id' => 'required|integer',
        //     'item_used' => 'required|integer',
        //     'affinity_gained' => 'required|decimal',
        // ];
        // if (!$this->validate($validationRules)) {
        //     return $this->response->setJSON([
        //         'error' => 'Validation failed',
        //         'messages' => $this->validator->getErrors(),
        //     ])->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST);
        // }

        // Get what interaction is requested
        $interactionsModel = new InteractionTypeModel();
        $interaction = $interactionsModel->getInteractionById($data['interaction_id']);
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


        //CALCULATION

        //create data for the interaction log
        $log_data = [
            'log_id' => bin2hex(random_bytes(16)), //amats ni sir
            // 'log_id' => null // auto-incremented by the database
            'pet_id' => $pet_id,
            'user_id' => $userId,
            'interaction_type_id' => $data['interaction_id'],
            'item_used_id' => $data['item_used_id'],
            'affinity_gained' => $data['affinity_gained'] ?? 0, // default to 0 if not provided
            
        ];


        return 
            $this->response->setJSON([
                'status' => 'success',
                'message' => 'Interaction logged successfully',
                'data' => $interaction, 
                'item' => $item,
                'pet' => $pet,
                'pet_status' => $petStatus,
                'affinity' => $affinity,

            ])->setStatusCode(ResponseInterface::HTTP_OK);

        
    }
}
