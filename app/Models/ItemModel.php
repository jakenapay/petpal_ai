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
        // 'effect',
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

    // Functions
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
            items.duration,
            items.created_at,
            items.korean_name
        FROM items 
        JOIN item_categories 
        ON items.category_id = item_categories.category_id
    ")->getResultArray();
    }


    public function getItemById($itemId)
    {
        /*
            i have item_id, category_id, item_name, description, image_url, base_price, rarity, is_tradeable, is_consumable, is_stackable, effect, duration, created_at
            i need to return the item with category_name from item_categories table
            i need to return the item with effects from the item_effects table
            i need to return the item rarity_name from item_rarity table
        */

        // Get the item with category and rarity info
        $builder = $this->db->table('items');
        $builder->select('
            items.*,
            item_categories.category_name,
            item_rarity.rarity_name
        ');
        $builder->join('item_categories', 'items.category_id = item_categories.category_id', 'left');
        $builder->join('item_rarity', 'items.rarity = item_rarity.rarity_id', 'left');
        $builder->where('items.item_id', $itemId);
        $item = $builder->get()->getRowArray();

        if (!$item) {
            return null;
        }

        // Get effects for the item
        $effects = $this->db->table('item_effects')
            ->where('effect_id', $item['effect_id'])
            ->get()
            ->getResultArray();

        $item['effects'] = $effects;

        return $item;
    }

}
