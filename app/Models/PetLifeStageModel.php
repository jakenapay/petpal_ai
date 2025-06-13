<?php

namespace App\Models;

use CodeIgniter\Model;

class PetLifeStageModel extends Model
{
    protected $table            = 'pet_life_stages';
    protected $primaryKey       = 'stage_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [];

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

    // FUNCTIONS
    public function getLifeStageById($stageId)
    {
        if (!$stageId) {
            return json_encode([]);
        }
        $lifeStage = $this->where('stage_id', $stageId)->first();
        return json_encode($lifeStage ?: []);
    }

    public function getPetLifeStageByID($stageId){
        if (!$stageId) {
            return null;
        }
        $lifeStage = $this->where('stage_id', $stageId)->first();
        return $lifeStage ?: null;
    }

}
