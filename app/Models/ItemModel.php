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
        'category_id',
        'item_name',
        'description',
        'image_url',
        'base_price',
        'rarity',
        'is_tradable',
        'is_buyable',
        'is_consumable',
        'is_stackable',
        'duration',
        'effect_id',
        'created_at',
        'korean_name',
        'tier_id',
        'real_price',
        'discount_percentage',
        'is_featured',
        'is_on_sale',
        'quantity_available',
        'release_date',
        'end_date',
        'thumbnail_url',
        'detail_images',
        'preview_3d_model',
        'attributes',
        'tags',
        'final_price'
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
    public function getItemsByCategory($categoryId)
    {
        $builder = $this->db->table('items');
        $builder->select('
            items.*,
            item_categories.category_name,
        ');
        $builder->join('item_categories', 'items.category_id = item_categories.category_id', 'left');
        $builder->where('items.category_id', $categoryId);
        $builder->where('items.is_buyable', 1);

        $items = $builder->get()->getResultArray();

        // Attach effects to each item
        foreach ($items as &$item) {
            $effects = $this->db->table('item_effects')
                ->where('effect_id', $item['effect_id'])
                ->get()
                ->getResultArray();
            $item['effects'] = $effects;
        }
        return $items;
    }

    public function searchItems($filters = [])
    {
        $builder = $this->db->table('items');
        $builder->select('
            items.*,
            item_categories.category_name,
        ');
        $builder->join('item_categories', 'items.category_id = item_categories.category_id', 'left');
        $builder->where('items.is_buyable', 1); // Only show buyable items

        // Search query
        if (!empty($filters['query'])) {
            $builder->groupStart();
            $builder->like('items.item_name', $filters['query']);
            // $builder->orLike('items.description', $filters['query']);
            $builder->groupEnd();
        }

        // Optional filters
        if (!empty($filters['category_id'])) {
            $builder->where('items.category_id', $filters['category_id']);
        }

        if (!empty($filters['min_price'])) {
            $builder->where('items.base_price >=', $filters['min_price']);
        }

        if (!empty($filters['max_price'])) {
            $builder->where('items.base_price <=', $filters['max_price']);
        }

        if (!empty($filters['tier'])) {
            $builder->where('items.tier_id', $filters['tier']);
        }

        if (!empty($filters['rarity'])) {
            $builder->where('items.rarity', $filters['rarity']);
        }

        // Sorting
        if (!empty($filters['sort_by'])) {
            switch ($filters['sort_by']) {
                case 'price_asc':
                    $builder->orderBy('items.base_price', 'ASC');
                    break;
                case 'price_desc':
                    $builder->orderBy('items.base_price', 'DESC');
                    break;
                case 'name':
                    $builder->orderBy('items.item_name', 'ASC');
                    break;
                case 'newest':
                    $builder->orderBy('items.created_at', 'DESC');
                    break;
            }
        }

        // Pagination
        $page = !empty($filters['page']) ? (int)$filters['page'] : 1;
        $limit = !empty($filters['limit']) ? (int)$filters['limit'] : 10;
        $offset = ($page - 1) * $limit;
        $builder->limit($limit, $offset);

        $results = $builder->get()->getResultArray();

        // Add effects to each item
        foreach ($results as &$item) {
            $effects = $this->db->table('item_effects')
                ->where('effect_id', $item['effect_id'])
                ->get()
                ->getResultArray();
            $item['effects'] = $effects;
        }

        return $results;
    }

    public function featuredItems(){
        $builder = $this->db->table('items');
        $builder->select('
            items.*,
            item_categories.category_name,
        ');
        $builder->join('item_categories', 'items.category_id = item_categories.category_id', 'left');
        $builder->where('items.is_buyable', 1);
        $builder->where('items.is_featured', 1);
        return $builder->get()->getResultArray();
    }



}
