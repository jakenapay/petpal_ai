<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'item_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'item_id',
        'category_id',
        'item_name',
        'description',
        'image_url',
        'base_price',
        'rarity',
        'is_tradeable',
        'is_consumable',
        'is_stackable',
        'effect',
        'duration',
        'created_at',
        'korean_name'
    ];

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

    public function getItemsWithCategory()
    {
        return $this->db->query("
        SELECT 
            items.item_id,
            items.category_id,
            item_categories.category_name,
            items.item_name,
            items.description,
            items.image_url,
            items.base_price,
            items.rarity,
            items.is_tradable,
            items.is_consumable,
            items.is_stackable,
            items.effect,
            item_effects.effect_name,
            item_effects.effect_values,
            items.duration,
            items.created_at,
            items.korean_name
        FROM items 
        JOIN item_categories 
        ON items.category_id = item_categories.category_id
        JOIN item_effects
        ON items.effect = item_effects.effect_id
    ")->getResultArray();
    }

}
