<?php

namespace App\Models;

use CodeIgniter\Model;

class PetModel extends Model
{
    protected $table            = 'pets';
    protected $primaryKey       = 'pet_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'name', 'species', 'breed', 'gender', 'appearance', 'personality', 'birthdate', 'status', 'level', 'experience', 'abilities', 'created_at', 'updated_at', 'life_stage_id']; 

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

    // Functions

    public function getPetsByUserId($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }
    public function getPetById($petId)
    {
        if (!$petId) {
            return null;
        }
        return $this->find($petId);
    }

    public function updatePet($petId, array $data)
    {
        log_message('debug', 'Updating pet with ID: ' . $petId . ' and data: ' . json_encode($data));
        if (!$petId || empty($data)) {
            return false;
        }
        return $this->update($petId, $data);
    }

    public function getAllPets($userId){
        //get the tables needed.
        $pets = $this->where('user_id', $userId)->findAll();

        $dog_eye_color_table = $this->db->table('dogeyecolors');
        $cat_eye_color_table = $this->db->table('cateyecolors');
        $items_table = $this->db->table('items');
        $cat_skin_table = $this->db->table('cat_texture');
        $dog_skin_table = $this->db->table('dog_texture');

        //get all pets 

        foreach($pets as &$pet){

            //get the pet species
            $species = $pet['species'];
            // Decode the appearance JSON
            $appearance = json_decode($pet['appearance'], true);

            //get the eye color
            if ($species === "dog"){
                $eyeColor = $dog_eye_color_table->where('color_id', $appearance['eye_color_id'])->get()->getRowArray();
            }else if ($species === "cat"){
                $eyeColor = $cat_eye_color_table->where('color_id', $appearance['eye_color_id'])->get()->getRowArray();
            }
            //get the value of eye color
            $eyeColorName = $eyeColor['color_name'];
            $eyeColorValue = $eyeColor['value'];

            // get the accesory
            $accessory = $items_table->where('item_id', $appearance['item_ids'][0]['accesory_id'])->get()->getRowArray();
            //get only the image_url of the item
            $accessoryUrl = $accessory['image_url'];
            //get the clothes
            $clothes = $items_table->where('item_id', $appearance['item_ids'][0]['clothes_id'])->get()->getRowArray();
            //get only the image_url of the item
            $clothesUrl = $clothes['image_url'];

            //get the skin
            if ($species === "dog"){
                $skin = $dog_skin_table->where('id', $appearance['skin_id'])->get()->getRowArray();
            }else if ($species === "cat"){
                $skin = $cat_skin_table->where('id', $appearance['skin_id'])->get()->getRowArray();
            }
            //get only the image_url of the item
            $skinUrl = $skin['url'];
            $skinName = $skin['texture_name'];

            //add those to the $pets
            // $pet['eye_color_name'] = $eyeColorName;
            $pet['eye_color_value'] = $eyeColorValue ?? null;
            $pet['accesory_url'] = $accessoryUrl ?? null;
            $pet['clothes_url'] = $clothesUrl ?? null;
            $pet['skin_url'] = $skinUrl ?? null;
            // $pet['skin_name'] = $skinName;

            // $pet['accessory'] = $appearance['item_ids'][0]['accesory_id'];

            //remove the apperance from the return
            unset($pet['appearance']);
        }

        return $pets;


    }
}
