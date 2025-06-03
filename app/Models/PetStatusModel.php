<?php

namespace App\Models;

use CodeIgniter\Model;

class PetStatusModel extends Model
{
    protected $table            = 'pet_status';
    protected $primaryKey       = 'status_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'pet_id',
        'affinity',
        'hunger_level',
        'happiness_level',
        'health_level',
        'energy_level',
        'cleanliness_level',
        'stress_level',
        'current_mood',
        'is_sick',
        'sickness_type',
        'sickness_severity',
        'last_hunger_update',
        'last_happiness_update',
        'last_health_update',
        'last_cleanliness_update',
        'last_energy_update',
        'last_status_calculation',
        'hibernation_mode',
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    //Functions
    public function getPetStatusByPetId($petId)
    {
        return $this->where('pet_id', $petId)->first();
    }
    public function updatePetAffinity($pet_id, array $data)
    {
        $query = $this->where('pet_id', $pet_id)->set($data)->update();
        return $query;
    }
    public function updateStatusChange($pet_id, $data, $multiplier, $subs_multiplier, $petLifeStageMultiplier)
    {
        // Validation rules
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

        // Flatten input data
        // $flattenedData = [];
        // foreach ($data as $entry) {
        //     if (is_array($entry)) {
        //         $flattenedData = array_merge($flattenedData, $entry);
        //     }
        // }



        // Validation
        if (!$validation->run($data)) {
            return [
                'error' => 'Validation failed',
                'messages' => $validation->getErrors(),
            ];
        }
        //get the pet status
        $petStatus = $this->getPetStatusByPetId($pet_id);
        if (!$petStatus) {
            return [
                'error' => 'Pet status not found for pet_id: ' . $pet_id,
            ];
        }

        $finalUpdateData = [];

        //log the the multiplier values
        log_message('debug', "Multipliers - Multiplier: $multiplier, Subscription Multiplier: $subs_multiplier, Pet Life Stage Multiplier: $petLifeStageMultiplier");
        foreach (['hunger_level', 'happiness_level', 'health_level', 'energy_level', 'cleanliness_level', 'stress_level'] as $key) {
            if (isset($data[$key])) {
                $multipliedValue = $data[$key] * $multiplier * $subs_multiplier * $petLifeStageMultiplier;
                $currentValue = isset($petStatus[$key]) ? (float)$petStatus[$key] : 0;
                $newValue = max(min($currentValue + $multipliedValue, 100), 0);
                $finalUpdateData[$key] = $newValue;
                log_message('debug', "Updating pet status: $key from $currentValue to $newValue for pet_id: $pet_id");
            }
        }

        // Add timestamps
        $now = date('Y-m-d H:i:s');
        if (isset($data['hunger_level'])) {
            $finalUpdateData['last_hunger_update'] = $now;
        }
        if (isset($data['happiness_level'])) {
            $finalUpdateData['last_happiness_update'] = $now;
        }
        if (isset($data['health_level'])) {
            $finalUpdateData['last_health_update'] = $now;
        }
        if (isset($data['energy_level'])) {
            $finalUpdateData['last_energy_update'] = $now;
        }
        if (isset($data['cleanliness_level'])) {
            $finalUpdateData['last_cleanliness_update'] = $now;
        }
        $finalUpdateData['last_status_calculation'] = $now;

        // Save to database
        $petStatusModel = new PetStatusModel();

        // Filter only allowed fields before save
        $saveData = array_intersect_key(
            $finalUpdateData,
            array_flip($petStatusModel->allowedFields)
        );



        $existingStatus = $petStatusModel->where('pet_id', $pet_id)->first();

        if ($existingStatus) {
            $petStatusModel->where('pet_id', $pet_id)->set($saveData)->update();
            return ['success' => true, 'updated' => true];
        } else {
            $saveData['pet_id'] = $pet_id;
            $petStatusModel->insert($saveData);
            return ['success' => true, 'created' => true];
        }
    }

}
