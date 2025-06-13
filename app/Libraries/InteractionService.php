<?php 
namespace App\Libraries;
use App\Models\PetModel;
use App\Models\PetStatusModel;
class InteractionService 
{
    public function __construct()
    {
        date_default_timezone_set('Asia/Manila'); // Set the default timezone to Asia/Manila

    }
    public function update_pet_status($pet_id, $data)
    {
        $validationRules = [
            'hunger_level' => 'permit_empty|decimal',
            'happiness_level' => 'permit_empty|decimal',
            'health_level' => 'permit_empty|decimal',
            'energy_level' => 'permit_empty|decimal',
            'cleanliness_level' => 'permit_empty|decimal',
            'stress_level' => 'permit_empty|decimal',
            'current_mood' => 'permit_empty|string',
            'is_sick' => 'permit_empty|in_list[0,1,true,false]',
            'sickness_type' => 'permit_empty|string',
            'sickness_severity' => 'permit_empty|decimal',
            'hibernation_mode' => 'permit_empty|in_list[0,1,true,false]',
        ];
        $validation = \Config\Services::validation();
        $validation->setRules($validationRules);
        $data['pet_id'] = (int) $pet_id;

        if (!$validation->run($data)) {
            return [
                'error' => 'Validation failed',
                'messages' => $validation->getErrors(),
            ];
        }

        $petStatusModel = new PetStatusModel(); 


        $updateData = array_intersect_key(
            $data,
            array_flip($petStatusModel->allowedFields)
        );

        if (isset($updateData['hunger_level'])) {
            $updateData['last_hunger_update'] = date('Y-m-d H:i:s');
        }
        if (isset($updateData['happiness_level'])) {
            $updateData['last_happiness_update'] = date('Y-m-d H:i:s');
        }
        if (isset($updateData['health_level'])) {
            $updateData['last_health_update'] = date('Y-m-d H:i:s');
        }
        if (isset($updateData['cleanliness_level'])) {
            $updateData['last_cleanliness_update'] = date('Y-m-d H:i:s');
        }
        if (isset($updateData['energy_level'])) {
            $updateData['last_energy_update'] = date('Y-m-d H:i:s');
        }

        $updateData['last_status_calculation'] = date('Y-m-d H:i:s');

        $existingStatus = $petStatusModel->where('pet_id', $pet_id)->first();


        
        // if ($existingStatus) {
        //     $petStatusModel->where('pet_id', $pet_id)->set($updateData)->update();
        //     return ['message' => 'Pet status updated successfully'];
        // } else {
        //     $updateData['pet_id'] = $pet_id;
        //     $petStatusModel->insert($updateData);
        //     return ['message' => 'Pet status created successfully'];
        // }
    }
}

?>