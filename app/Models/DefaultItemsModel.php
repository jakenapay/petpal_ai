<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\PetModel;

class DefaultItemsModel extends Model
{
    protected $table            = 'default_items';
    protected $primaryKey       = 'default_item_id';
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

    //Functions

    public function getDefaultItems($life_stage_id){
        // Get all default items from the database
        $defaultItems = $this->where('stage_id', $life_stage_id)
            ->findAll();
        // If no items found, return an empty array
        if (empty($defaultItems)) {
            return [];
        }
        // Return the default items
        return $defaultItems;
    }
}
