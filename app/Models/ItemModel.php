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
        SELECT items.*, 
        item_categories.category_name
        FROM items 
        JOIN item_categories 
        ON items.category_id = item_categories.category_id
    ")->getResultArray();
    }

    // public function getItemsByIds(array $itemIds)
    // {
    //     if (empty($itemIds)) {
    //         return [];
    //     }

    //     $builder = $this->db->table('items');
    //     $builder->select('
    //         items.*,
    //         item_categories.category_name,
    //         item_rarity.rarity_name
    //     ');
    //     $builder->join('item_categories', 'items.category_id = item_categories.category_id', 'left');
    //     $builder->join('item_rarity', 'items.rarity = item_rarity.rarity_id', 'left');
    //     $builder->whereIn('items.item_id', $itemIds);

    //     return $builder->get()->getResultArray();
    // }

    public function getItems($itemIds)
    {
        $builder = $this->db->table('items');
        $builder->select('
            items.*,
            item_categories.category_name,
            item_rarity.rarity_name
        ');
        $builder->join('item_categories', 'items.category_id = item_categories.category_id', 'left');
        $builder->join('item_rarity', 'items.rarity = item_rarity.rarity_id', 'left');

        if (is_array($itemIds)) {
            $builder->whereIn('items.item_id', $itemIds);
            return $builder->get()->getResultArray();
        } else {
            $builder->where('items.item_id', $itemIds);
            return $builder->get()->getRowArray();
        }
    }

    //Same lang sila. Kaso gamit ko yung getItemById somewhere else so di ko muna tatanggalin.

    public function getItemById($itemId)
    {
        $builder = $this->db->table('items');
        $builder->select('
            items.*,
            item_categories.category_name,
            item_rarity.rarity_name,
            item_accessories.id as accessory_id,
            item_accessories.breed_id as accessory_breed_id,
            item_accessories.subcategory_id as accessory_subcategory_id,
            item_accessories.iconUrl as accessory_iconUrl,
            item_accessories.AddressableURL as accessory_AddressableURL,
            item_accessories.RGBColor as accessory_RGBColor,
            item_subcategories.name as accessory_subcategory_name,
            item_subcategories.id as accessory_subcategory_id,
            item_accessories.species_id as accessory_species_id,
  
        ');

        $builder->join('item_categories', 'items.category_id = item_categories.category_id', 'left');
        $builder->join('item_rarity', 'items.rarity = item_rarity.rarity_id', 'left');
        
        // Join with item_accessories only if category_id = 1
        $builder->join('item_accessories', 'items.item_id = item_accessories.item_id ', 'left');
        
        // Join with subcategories only if subcategory_id is present
        $builder->join('item_subcategories', 'item_accessories.subcategory_id = item_subcategories.id', 'left');
        

        $builder->where('items.item_id', $itemId);

        $item = $builder->get()->getRowArray();
        $speciesId = $item['accessory_species_id'] ?? null;
        $breedId = $item['accessory_breed_id'] ?? null;

        if ($speciesId == 1) {
            // Fetch from dogbreeds
            $breed = $this->db->table('dogbreeds')
                            ->where('breed_id', $breedId)
                            ->get()
                            ->getRowArray();
        } else {
            // Fetch from catbreeds
            $breed = $this->db->table('catbreeds')
                            ->where('breed_id', $breedId)
                            ->get()
                            ->getRowArray();
        }
        $item['breed_name'] = $breed['breed_name'] ?? null;
        if (!$item) {
            return null;
        }

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
        // foreach ($items as &$item) {
        //     $effects = $this->db->table('item_effects')
        //         ->where('effect_id', $item['effect_id'])
        //         ->get()
        //         ->getResultArray();
        //     $item['effects'] = $effects;
        // }
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

        // // Add effects to each item
        // foreach ($results as &$item) {
        //     $effects = $this->db->table('item_effects')
        //         ->where('effect_id', $item['effect_id'])
        //         ->get()
        //         ->getResultArray();
        //     $item['effects'] = $effects;
        // }

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
