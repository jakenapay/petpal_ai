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
}
