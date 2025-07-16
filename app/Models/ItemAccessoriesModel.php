<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemAccessoriesModel extends Model
{
    protected $table = 'item_accessories';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'id',
        'item_id',
        'subcategory_id',
        'breed_id',
        'species_id',
        'iconUrl',
        'AddressableURL',
        'RGBColor'
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


    public function getAllAccessories()
    {
        $db = \Config\Database::connect();

        // Fetch all dog breeds
        $dogBreeds = $db->table('dogbreeds')
            ->select('breed_id, breed_name')
            ->orderBy('breed_id', 'ASC')
            ->get()
            ->getResultArray();

        $dogResults = [];
        foreach ($dogBreeds as $breed) {
            // Query accessories for each dog breed
            $accessories = $db->table($this->table)
                ->select('item_accessories.id, 
                                  item_accessories.item_id, 
                                  items.item_name,
                                  item_accessories.subcategory_id, 
                                  item_accessories.breed_id, 
                                  item_accessories.species_id, 
                                  item_accessories.iconUrl, 
                                  item_accessories.AddressableURL, 
                                  item_accessories.RGBColor')
                ->join('items', 'items.item_id = item_accessories.item_id')
                ->where('item_accessories.species_id', 1) // Assuming 1 is for dogs
                ->where('item_accessories.breed_id', $breed['breed_id'])
                ->get()
                ->getResultArray();

            $dogResults[] = [
                'breed_id' => $breed['breed_id'],
                'breed_name' => $breed['breed_name'],
                'accessories' => $accessories
            ];
        }

        // Fetch all cat breeds
        $catBreeds = $db->table('catbreeds')
            ->select('breed_id, breed_name')
            ->orderBy('breed_id', 'ASC')
            ->get()
            ->getResultArray();

        $catResults = [];
        foreach ($catBreeds as $breed) {
            // Query accessories for each cat breed
            $accessories = $db->table($this->table)
                ->select('item_accessories.id, 
                                  item_accessories.item_id,
                                  items.item_name, 
                                  item_accessories.subcategory_id, 
                                  item_accessories.breed_id, 
                                  item_accessories.species_id, 
                                  item_accessories.iconUrl, 
                                  item_accessories.AddressableURL, 
                                  item_accessories.RGBColor')
                ->join('items', 'items.item_id = item_accessories.item_id')
                ->where('item_accessories.species_id', 2) // Assuming 2 is for cats
                ->where('item_accessories.breed_id', $breed['breed_id'])
                ->get()
                ->getResultArray();

            $catResults[] = [
                'breed_id' => $breed['breed_id'],
                'breed_name' => $breed['breed_name'],
                'accessories' => $accessories
            ];
        }

        return [
            'dogs' => $dogResults,
            'cats' => $catResults
        ];
    }

    public function getItemByUrl($addressableUrl) {
       return $this->where('LOWER(addressableUrl)', strtolower(trim($addressableUrl)))->first();
    }
}
