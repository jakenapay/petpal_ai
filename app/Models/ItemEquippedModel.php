<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemEquippedModel extends Model
{
    protected $table = 'equipped_items';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = ['id', 'user_id', 'pet_id', 'breed_name', 'item_id', 'slot_type', 'addressable_url', 'sub_category'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function checkEquippedItem($userId, $petId, $slotType)
    {
        $builder = $this->builder();
        $builder->where('user_id', $userId)
            ->where('pet_id', $petId)
            ->where('slot_type', $slotType);
        $query = $builder->get();
        if (!$query) {
            log_message('error', 'Failed to fetch equipped item for user_id: ' . $userId);
            return null;
        }

        return $query->getFirstRow('array'); // or 'object' if you prefer
    }
}
