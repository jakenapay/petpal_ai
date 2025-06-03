<?php

namespace App\Models;

use CodeIgniter\Model;

class InteractionTypeModel extends Model
{
    protected $table            = 'interaction_type';
    protected $primaryKey       = 'interaction_type_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'interaction_type_id', 'category', 'interaction_name', 'base_points', 'max_daily_count',
        'required_subscription', 'hunger_effect', 'happiness_effect', 'health_effect',
        'cleanliness_effect', 'energy_effect', 'stress_effect', 'description', 'created_at'
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

    public function getInteractions()
    {
        return $this->findAll();
    }
    public function getInteractionByCategory($category)
    {
        return $this->where('category', $category)->findAll();
    }
    public function getInteractionById($interactionId)
    {
        return $this->find($interactionId);
    }



}
