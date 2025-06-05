<?php

namespace App\Models;

use CodeIgniter\Model;

class PetModel extends Model
{
    protected $table            = 'pets';
    protected $primaryKey       = 'pet_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'name', 'species', 'breed', 'gender', 'appearance', 'personality', 'birthdate', 'status', 'level', 'experience', 'abilities', 'created_at', 'updated_at', 'life_stage_id']; 

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

    // Functions

    public function getPetsByUserId($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }
    public function getPetById($petId)
    {
        if (!$petId) {
            return null;
        }
        return $this->find($petId);
    }

    public function updatePet($petId, array $data)
    {
        log_message('debug', 'Updating pet with ID: ' . $petId . ' and data: ' . json_encode($data));
        if (!$petId || empty($data)) {
            return false;
        }
        return $this->update($petId, $data);
    }
}
