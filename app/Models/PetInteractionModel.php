<?php

namespace App\Models;

use CodeIgniter\Model;

class PetInteractionModel extends Model
{
    protected $table            = 'pet_interactions';
    protected $primaryKey       = 'log_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'log_id', 'pet_id', 'user_id', 'interaction_type_id', 'interaction_category',
        'interaction_subcategory', 'item_used_id', 'item_used_name', 'interaction_duration_seconds',
        'interaction_quality', 'base_points', 'multiplier_total', 'affinity_gained', 'coins_earned',
        'hunger_change', 'happiness_change', 'health_change', 'cleanliness_change', 'energy_change',
        'stress_change', 'llm_response', 't2m_animation_id', 'emotion_detected', 'platform',
        'session_id', 'client_timestamp', 'created_at'
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

    public function GetPetInteractionHistory($petId, $limit = 30)
    {
        if (!$petId) {
            return [];
        }
        return $this->where('pet_id', $petId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    public function getTodayInteractionCountsByPet($pet_id)
    {
        return $this->select('interaction_type_id, COUNT(*) as count')
            ->where('pet_id', $pet_id)
            ->where('created_at >=', date('Y-m-d 00:00:00'))
            ->where('created_at <=', date('Y-m-d 23:59:59'))
            ->groupBy('interaction_type_id')
            ->findAll();
    }

}
